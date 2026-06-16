<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class StockImportService
{
  /**
   * @return array{updated:int, skipped:int, errors:array<int, string>}
   */
  public static function import(UploadedFile $file, ?int $siteId = null): array
  {
    $rows = self::readFile($file);
    if (empty($rows)) {
      throw new \RuntimeException('Le fichier est vide ou illisible.');
    }

    $updated = 0;
    $skipped = 0;
    $errors = [];

    foreach ($rows as $line => $row) {
      $lineNo = $line + 2;
      $idValue = self::value($row['id'] ?? null);
      $nameValue = self::value($row['name'] ?? null);
      $stockRaw = self::value($row['stock'] ?? null);

      if ($idValue === null && $nameValue === null) {
        $skipped++;

        continue;
      }

      if ($stockRaw === null || ! is_numeric($stockRaw)) {
        $errors[] = "Ligne {$lineNo} : quantité stock invalide.";
        $skipped++;

        continue;
      }

      $quantity = (int) $stockRaw;
      if ($quantity < 0) {
        $errors[] = "Ligne {$lineNo} : le stock ne peut pas être négatif.";
        $skipped++;

        continue;
      }

      $product = self::findProduct($idValue, $nameValue);
      if (! $product) {
        $label = $idValue ?? $nameValue;
        $errors[] = "Ligne {$lineNo} : produit introuvable ({$label}).";
        $skipped++;

        continue;
      }

      StockService::adjust(
        $product,
        $quantity,
        'inventaire',
        'IMPORT',
        'Mise à jour stock via fichier',
        null,
        $siteId
      );
      $updated++;
    }

    return compact('updated', 'skipped', 'errors');
  }

  /**
   * @return array<int, array<string, string|null>>
   */
  private static function readFile(UploadedFile $file): array
  {
    $extension = strtolower($file->getClientOriginalExtension());

    return match ($extension) {
      'csv', 'txt' => self::parseCsv($file->getRealPath()),
      'xlsx' => self::parseXlsx($file->getRealPath()),
      default => throw new \RuntimeException('Format non supporté. Utilisez CSV ou Excel (.xlsx).'),
    };
  }

  /**
   * @return array<int, array<string, string|null>>
   */
  private static function parseCsv(string $path): array
  {
    $content = file_get_contents($path);
    if ($content === false) {
      return [];
    }

    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    $lines = preg_split('/\r\n|\r|\n/', $content);
    $lines = array_values(array_filter($lines, fn ($l) => trim($l) !== ''));

    if (empty($lines)) {
      return [];
    }

    $delimiter = substr_count($lines[0], ';') >= substr_count($lines[0], ',') ? ';' : ',';
    $headers = str_getcsv(array_shift($lines), $delimiter);
    $map = self::headerMap($headers);
    $rows = [];

    foreach ($lines as $line) {
      $data = str_getcsv($line, $delimiter);
      if (count($data) < count($headers)) {
        $data = array_pad($data, count($headers), null);
      }

      $row = ['id' => null, 'name' => null, 'stock' => null];
      foreach ($map as $index => $key) {
        $row[$key] = $data[$index] ?? null;
      }
      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * @return array<int, array<string, string|null>>
   */
  private static function parseXlsx(string $path): array
  {
    if (! class_exists(ZipArchive::class)) {
      throw new \RuntimeException('Extension ZIP requise pour lire les fichiers Excel.');
    }

    $zip = new ZipArchive;
    if ($zip->open($path) !== true) {
      throw new \RuntimeException('Fichier Excel invalide.');
    }

    $shared = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml) {
      $xml = simplexml_load_string($sharedXml);
      if ($xml) {
        foreach ($xml->si as $si) {
          if (isset($si->t)) {
            $shared[] = (string) $si->t;
          } elseif (isset($si->r)) {
            $text = '';
            foreach ($si->r as $run) {
              $text .= (string) $run->t;
            }
            $shared[] = $text;
          } else {
            $shared[] = '';
          }
        }
      }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if (! $sheetXml) {
      throw new \RuntimeException('Feuille Excel introuvable.');
    }

    $sheet = simplexml_load_string($sheetXml);
    if (! $sheet) {
      return [];
    }

    $sheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $cells = [];
    foreach ($sheet->sheetData->row as $row) {
      foreach ($row->c as $cell) {
        $ref = (string) $cell['r'];
        $col = preg_replace('/\d+/', '', $ref);
        $rowIndex = (int) preg_replace('/\D+/', '', $ref);
        $type = (string) ($cell['t'] ?? '');
        $value = isset($cell->v) ? (string) $cell->v : '';
        if ($type === 's' && $value !== '' && isset($shared[(int) $value])) {
          $value = $shared[(int) $value];
        }
        $cells[$rowIndex][$col] = $value;
      }
    }

    if (empty($cells)) {
      return [];
    }

    ksort($cells);
    $headerRow = array_shift($cells);
    if (! $headerRow) {
      return [];
    }

    $columns = array_keys($headerRow);
    sort($columns);
    $headers = [];
    foreach ($columns as $col) {
      $headers[] = $headerRow[$col] ?? '';
    }

    $map = self::headerMap($headers);
    $rows = [];

    foreach ($cells as $rowCells) {
      $row = ['id' => null, 'name' => null, 'stock' => null];
      foreach ($map as $index => $key) {
        $col = $columns[$index] ?? null;
        $row[$key] = $col ? ($rowCells[$col] ?? null) : null;
      }
      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * @param  array<int, string|null>  $headers
   * @return array<int, string>
   */
  private static function headerMap(array $headers): array
  {
    $map = [];
    foreach ($headers as $index => $header) {
      $normalized = self::normalizeHeader((string) $header);
      $map[$index] = match (true) {
        in_array($normalized, ['idproduit', 'id', 'productid', 'code', 'sku', 'reference'], true) => 'id',
        in_array($normalized, ['nomduproduit', 'nom', 'name', 'produit', 'libelle'], true) => 'name',
        in_array($normalized, ['stock', 'quantite', 'quantity', 'qty', 'qte'], true) => 'stock',
        default => 'ignore',
      };
    }

    return array_filter($map, fn ($key) => $key !== 'ignore');
  }

  private static function normalizeHeader(string $header): string
  {
    $header = mb_strtolower(trim($header));
    $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header) ?: $header;

    return preg_replace('/[^a-z0-9]/', '', $header) ?? '';
  }

  private static function value(?string $value): ?string
  {
    if ($value === null) {
      return null;
    }

    $value = trim((string) $value);
    if ($value === '' || strtoupper($value) === 'NULL') {
      return null;
    }

    return $value;
  }

  private static function findProduct(?string $idValue, ?string $nameValue): ?Product
  {
    if ($idValue !== null) {
      if (ctype_digit($idValue)) {
        $byId = Product::find((int) $idValue);
        if ($byId) {
          return $byId;
        }
      }

      $bySku = Product::where('sku', $idValue)->first();
      if ($bySku) {
        return $bySku;
      }

      $byBarcode = Product::where('barcode', $idValue)->first();
      if ($byBarcode) {
        return $byBarcode;
      }
    }

    if ($nameValue !== null) {
      return Product::whereRaw('LOWER(name) = ?', [mb_strtolower($nameValue)])->first();
    }

    return null;
  }
}
