#!/usr/bin/env python3
"""Upload Firstus POS vers Hostinger via FTP (public_html/facturation)."""
from __future__ import annotations

import ftplib
import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ENV_FILE = ROOT / "deploy" / "easygest-ftp.env"

SKIP_DIR_NAMES = {".git", ".cursor", ".idea", ".vscode", "node_modules"}
SKIP_FILES = {".env", ".env.backup", ".DS_Store", "easygest-ftp.env", "easygest.env", "index.html", "firstus-deploy.zip"}


def load_env(path: Path) -> dict[str, str]:
    env: dict[str, str] = {}
    for line in path.read_text().splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        k, v = line.split("=", 1)
        env[k.strip()] = v.strip()
    return env


def should_skip(rel: str) -> bool:
    if rel in SKIP_FILES or Path(rel).name in SKIP_FILES:
        return True
    if rel.startswith("storage/logs/"):
        return True
    if "/storage/logs/" in rel:
        return True
    return False


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
    if not ENV_FILE.exists():
        print(f"❌ Fichier manquant : {ENV_FILE}", file=sys.stderr)
        return 1

    cfg = load_env(ENV_FILE)
    host = cfg.get("FTP_HOST", "ftp.easygest.org")
    user = cfg["FTP_USER"]
    password = cfg["FTP_PASS"]
    remote_base = cfg.get("FTP_REMOTE_DIR", "facturation")

    print(f"==> Connexion FTP {host} …")
    ftp = ftplib.FTP(host, timeout=180)
    ftp.login(user, password)
    ftp.set_pasv(True)
    print(f"    Racine FTP : {ftp.pwd()}")

    deploy = FtpDeploy(ftp)
    deploy.mkdirs(remote_base)

    ok = err = 0
    print(f"==> Upload vers {remote_base}/ …")

    for dirpath, dirnames, filenames in os.walk(ROOT):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIR_NAMES]
        for name in filenames:
            local = Path(dirpath) / name
            rel = local.relative_to(ROOT).as_posix()
            if should_skip(rel):
                continue
            remote = f"{remote_base}/{rel}"
            try:
                deploy.upload(local, remote)
                ok += 1
                if ok % 100 == 0:
                    print(f"  … {ok} fichiers", flush=True)
            except Exception as exc:
                print(f"  ✗ {rel}: {exc}", file=sys.stderr)
                err += 1

    htaccess = ROOT / "deploy" / "htaccess-racine-hostinger.txt"
    if htaccess.exists():
        try:
            deploy.upload(htaccess, f"{remote_base}/.htaccess")
            print("    .htaccess racine OK")
        except Exception as exc:
            print(f"    ⚠ .htaccess : {exc}", file=sys.stderr)

    # Supprimer index.html parasite (redirection IP locale)
    try:
        deploy.cd_root()
        deploy.mkdirs(remote_base)
        ftp.delete("index.html")
        print("    index.html supprimé")
    except ftplib.error_perm:
        pass

    env_upload = ROOT / "deploy" / ".env.production.upload"
    if env_upload.exists():
        try:
            deploy.upload(env_upload, f"{remote_base}/.env")
            print("    .env production OK")
        except Exception as exc:
            print(f"    ⚠ .env : {exc}", file=sys.stderr)

    ftp.quit()
    print(f"\n✅ {ok} fichiers envoyés, {err} erreurs")
    return 0 if err == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
