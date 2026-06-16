#!/usr/bin/env python3
"""Upload un fichier unique vers facturation/ via FTP."""
from __future__ import annotations

import ftplib
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ENV_FILE = ROOT / "deploy" / "easygest-ftp.env"


def main() -> int:
    if len(sys.argv) < 3:
        print(f"Usage: {sys.argv[0]} <fichier_local> <nom_distant>")
        return 1

    local = Path(sys.argv[1])
    remote_name = sys.argv[2]
    cfg = {}
    for line in ENV_FILE.read_text().splitlines():
        if "=" in line and not line.strip().startswith("#"):
            k, v = line.split("=", 1)
            cfg[k.strip()] = v.strip()

    print(f"==> Upload {local.name} ({local.stat().st_size // 1024 // 1024} Mo) …")
    ftp = ftplib.FTP(cfg["FTP_HOST"], timeout=600)
    ftp.login(cfg["FTP_USER"], cfg["FTP_PASS"])
    ftp.set_pasv(True)
    ftp.cwd("facturation")
    with local.open("rb") as f:
        ftp.storbinary(f"STOR {remote_name}", f, blocksize=262144)
    ftp.quit()
    print("✅ OK")
    return 0


if __name__ == "__main__":
    sys.exit(main())
