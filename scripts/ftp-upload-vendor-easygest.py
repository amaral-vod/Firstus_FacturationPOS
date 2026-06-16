#!/usr/bin/env python3
"""Upload vendor/ uniquement (compléter déploiement FTP Hostinger)."""
from __future__ import annotations

import ftplib
import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ENV_FILE = ROOT / "deploy" / "easygest-ftp.env"
REMOTE_BASE = "facturation"


def load_env(path: Path) -> dict[str, str]:
    env: dict[str, str] = {}
    for line in path.read_text().splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        k, v = line.split("=", 1)
        env[k.strip()] = v.strip()
    return env


class FtpDeploy:
    def __init__(self, ftp: ftplib.FTP) -> None:
        self.ftp = ftp
        self.root = ftp.pwd()

    def cd_root(self) -> None:
        self.ftp.cwd(self.root)

    def mkdirs(self, remote: str) -> None:
        self.cd_root()
        for part in remote.split("/"):
            if not part:
                continue
            try:
                self.ftp.cwd(part)
            except ftplib.error_perm:
                self.ftp.mkd(part)
                self.ftp.cwd(part)

    def upload(self, local: Path, remote_rel: str) -> None:
        remote_dir = str(Path(remote_rel).parent).replace("\\", "/")
        if remote_dir in (".", ""):
            self.cd_root()
        else:
            self.mkdirs(remote_dir)
        with local.open("rb") as f:
            self.ftp.storbinary(f"STOR {Path(remote_rel).name}", f)


def main() -> int:
    cfg = load_env(ENV_FILE)
    vendor = ROOT / "vendor"
    if not vendor.is_dir():
        print("❌ vendor/ absent — lancez composer install --no-dev", file=sys.stderr)
        return 1

    print(f"==> Connexion {cfg['FTP_HOST']} …")
    ftp = ftplib.FTP(cfg["FTP_HOST"], timeout=180)
    ftp.login(cfg["FTP_USER"], cfg["FTP_PASS"])
    ftp.set_pasv(True)
    deploy = FtpDeploy(ftp)

    ok = err = 0
    print("==> Upload vendor/ …")
    for dirpath, _, filenames in os.walk(vendor):
        for name in filenames:
            local = Path(dirpath) / name
            rel = local.relative_to(ROOT).as_posix()
            remote = f"{REMOTE_BASE}/{rel}"
            try:
                deploy.upload(local, remote)
                ok += 1
                if ok % 200 == 0:
                    print(f"  … {ok} fichiers", flush=True)
            except Exception as exc:
                print(f"  ✗ {rel}: {exc}", file=sys.stderr)
                err += 1

    ftp.quit()
    print(f"\n✅ vendor : {ok} OK, {err} erreurs")
    return 0 if err == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
