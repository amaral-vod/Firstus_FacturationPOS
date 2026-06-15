<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProductsFromCsv extends Command
{
    protected $signature = 'import:products
                            {--categories= : Chemin vers product_categories.csv}
                            {--products= : Chemin vers products.csv}
                            {--replace : Supprimer les produits et catégories existants avant import}';

    protected $description = 'Importer les catégories et produits depuis les fichiers CSV';

    public function handle(): int
    {
        $categoriesPath = $this->option('categories')
            ?? '/home/frioldfr/Téléchargements/product_categories.csv';
        $productsPath = $this->option('products')
            ?? '/home/frioldfr/Téléchargements/products.csv';

        if (! file_exists($categoriesPath)) {
            $this->error("Fichier introuvable : {$categoriesPath}");

            return self::FAILURE;
        }

        if (! file_exists($productsPath)) {
            $this->error("Fichier introuvable : {$productsPath}");

            return self::FAILURE;
        }

        if ($this->option('replace')) {
            $this->warn('Suppression des produits et catégories existants...');
            DB::table('detail_ventes')->delete();
            DB::table('stocks')->delete();
            Product::query()->delete();
            Category::query()->delete();
        }

        $categoryMap = $this->importCategories($categoriesPath);
        $stats = $this->importProducts($productsPath, $categoryMap);

        $this->newLine();
        $this->info("✅ Import terminé : {$stats['imported']} produits importés, {$stats['skipped']} ignorés.");

        return self::SUCCESS;
    }

    private function importCategories(string $path): array
    {
        $map = [];
        $rows = $this->readCsv($path);
        $bar = $this->output->createProgressBar(count($rows));
        $bar->setFormat(' Catégories: %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();

        foreach ($rows as $row) {
            $uuid = $this->val($row['product_category_id'] ?? '');
            $label = $this->val($row['label'] ?? '');

            if (! $uuid || ! $label) {
                $bar->advance();
                continue;
            }

            $category = Category::updateOrCreate(
                ['slug' => $uuid],
                [
                    'name' => $label,
                    'description' => $this->val($row['description'] ?? null),
                    'is_active' => ($this->val($row['is_active'] ?? '1') === '1'),
                ]
            );

            $map[$uuid] = $category->id;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line('  → '.count($map).' catégorie(s) traitée(s).');

        return $map;
    }

    private function importProducts(string $path, array $categoryMap): array
    {
        $imported = 0;
        $skipped = 0;
        $rows = $this->readCsv($path);
        $bar = $this->output->createProgressBar(count($rows));
        $bar->setFormat(' Produits:   %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();

        foreach ($rows as $row) {
            if ($this->val($row['deleted_at'] ?? null)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $sku = $this->val($row['code'] ?? '');
            $name = $this->val($row['name'] ?? '');

            if (! $sku || ! $name) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $categoryUuid = $this->val($row['product_category_id'] ?? '');
            $categoryId = $categoryMap[$categoryUuid] ?? null;

            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'category_id' => $categoryId,
                    'description' => $this->val($row['description'] ?? null),
                    'price' => (float) ($this->val($row['unit_price'] ?? 0) ?: 0),
                    'cost' => (float) ($this->val($row['cost'] ?? 0) ?: 0),
                    'unit' => $this->val($row['unit_of_measure'] ?? 'pièce') ?: 'pièce',
                    'barcode' => $this->val($row['product_id'] ?? null),
                    'is_active' => ($this->val($row['is_active'] ?? '1') === '1'),
                ]
            );

            $minStock = (int) ($this->val($row['min_stock_level'] ?? 0) ?: 0);

            Stock::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => $product->stock?->quantity ?? 0,
                    'min_quantity' => max(0, $minStock),
                ]
            );

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return compact('imported', 'skipped');
    }

    private function readCsv(string $path): array
    {
        $content = file_get_contents($path);
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $lines = array_filter($lines, fn ($l) => trim($l) !== '');

        if (empty($lines)) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines), ';');
        $headers = array_map(fn ($h) => trim($h, '"'), $headers);
        $rows = [];

        foreach ($lines as $line) {
            $data = str_getcsv($line, ';');
            if (count($data) < count($headers)) {
                $data = array_pad($data, count($headers), null);
            }
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = $data[$i] ?? null;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function val(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value, '" ');

        if ($value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        return $value;
    }
}
