#!/usr/bin/env python3
"""Déploie le module stock complet vers Hostinger."""
from __future__ import annotations

import ftplib
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ENV_FILE = ROOT / "deploy" / "easygest-ftp.env"
BASE = "facturation"

FILES = [
    "app/Console/Commands/AlertStockCommand.php",
    "app/Http/Controllers/Stock/InventorySessionController.php",
    "app/Http/Controllers/Stock/StockController.php",
    "app/Http/Controllers/Admin/ProductController.php",
    "app/Http/Controllers/Rapport/RapportController.php",
    "app/Models/InventoryLine.php",
    "app/Models/InventorySession.php",
    "app/Models/Stock.php",
    "app/Services/InventoryService.php",
    "app/Services/StockAnalyticsService.php",
    "app/Services/StockImportService.php",
    "app/Services/NotificationService.php",
    "bootstrap/app.php",
    "config/permissions.php",
    "database/migrations/2026_06_16_120000_create_inventory_tables.php",
    "database/migrations/2026_06_16_140000_add_max_quantity_to_stocks_table.php",
    "resources/views/admin/products/index.blade.php",
    "resources/views/layouts/app.blade.php",
    "resources/views/rapports/index.blade.php",
    "resources/views/stock/index.blade.php",
    "resources/views/stock/mouvements.blade.php",
    "resources/views/stock/analyse.blade.php",
    "resources/views/stock/inventories/index.blade.php",
    "resources/views/stock/inventories/create.blade.php",
    "resources/views/stock/inventories/show.blade.php",
    "resources/views/stock/inventories/report.blade.php",
    "routes/web.php",
    "deploy/_deploy_full_stock.php",
]


def load_env() -> dict[str, str]:
    cfg: dict[str, str] = {}
    for line in ENV_FILE.read_text().splitlines():
        if "=" in line and not line.strip().startswith("#"):
            k, v = line.split("=", 1)
            cfg[k.strip()] = v.strip()
    return cfg


class Ftp:
    def __init__(self, ftp: ftplib.FTP) -> None:
        self.ftp = ftp
        self.root = ftp.pwd()

    def mkdirs(self, remote_dir: str) -> None:
        self.ftp.cwd(self.root)
        for part in remote_dir.split("/"):
            if not part:
                continue
            try:
                self.ftp.cwd(part)
            except ftplib.error_perm:
                self.ftp.mkd(part)
                self.ftp.cwd(part)

    def upload(self, local: Path, rel: str) -> None:
        remote_path = f"{BASE}/{rel}"
        remote_dir = str(Path(remote_path).parent).replace("\\", "/")
        self.mkdirs(remote_dir)
        with local.open("rb") as f:
            self.ftp.storbinary(f"STOR {Path(remote_path).name}", f)


def main() -> int:
    cfg = load_env()
    ftp = ftplib.FTP(cfg["FTP_HOST"], timeout=180)
    ftp.login(cfg["FTP_USER"], cfg["FTP_PASS"])
    ftp.set_pasv(True)
    print("Connecté:", ftp.pwd())
    deploy = Ftp(ftp)
    ok = err = 0
    for rel in FILES:
        local = ROOT / rel
        if not local.exists():
            print("SKIP (absent):", rel)
            continue
        try:
            if rel == "deploy/_deploy_full_stock.php":
                deploy.upload(local, "public/_deploy_full_stock.php")
            else:
                deploy.upload(local, rel)
            print("OK", rel)
            ok += 1
        except Exception as exc:
            print("ERR", rel, exc, file=sys.stderr)
            err += 1
    ftp.quit()
    print(f"\n{ok} fichiers OK, {err} erreurs")
    return 0 if err == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
