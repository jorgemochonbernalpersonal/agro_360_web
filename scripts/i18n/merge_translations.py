#!/usr/bin/env python3
"""
Fusiona los lotes traducidos de scripts/i18n/missing/<locale>/ en resources/lang/<locale>.json.

Uso:
    python3 scripts/i18n/merge_translations.py [--locales ca eu gl] [--dry-run]

Comportamiento:
  - Lee todos los batch_*.json de cada carpeta de locale.
  - Sólo incorpora entradas donde el valor NO sea igual a la clave (es decir, el lote
    ha sido editado y tiene traducción real; ignora entradas sin traducir).
  - Ordena el JSON resultante igual que es.json (mismo orden de claves).
  - Hace una copia de seguridad del fichero original antes de escribir.

Flags:
  --dry-run   Muestra cuántas claves se añadirían sin escribir nada.
  --force     Importa también entradas cuyo valor coincide con la clave (sin traducir).
"""

import json
import shutil
import argparse
from pathlib import Path
from datetime import datetime

LANG_DIR  = Path(__file__).resolve().parents[2] / "resources" / "lang"
MISS_DIR  = Path(__file__).resolve().parent / "missing"

def load_json(path: Path) -> dict:
    with open(path, encoding="utf-8") as f:
        return json.load(f)

def save_json(path: Path, data: dict):
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
        f.write("\n")

def merge(locale: str, es_keys: list, dry_run: bool, force: bool):
    locale_dir = MISS_DIR / locale
    if not locale_dir.exists():
        print(f"  {locale}: carpeta missing/{locale}/ no existe, omitido")
        return

    batch_files = sorted(locale_dir.glob("batch_*.json"))
    if not batch_files:
        print(f"  {locale}: sin lotes encontrados")
        return

    # Recoger traducciones de los lotes
    new_entries: dict = {}
    skipped = 0
    for bf in batch_files:
        batch = load_json(bf)
        for key, val in batch.items():
            if not force and val == key:
                skipped += 1
                continue
            new_entries[key] = val

    if not new_entries:
        print(f"  {locale}: 0 entradas nuevas (todas sin traducir o ya presentes)")
        return

    if dry_run:
        print(f"  {locale}: añadiría {len(new_entries)} claves ({skipped} omitidas sin traducir)")
        return

    # Cargar JSON actual
    target_path = LANG_DIR / f"{locale}.json"
    target = load_json(target_path)

    added = {k: v for k, v in new_entries.items() if k not in target}
    updated = {k: v for k, v in new_entries.items() if k in target and target[k] != v}

    if not added and not updated:
        print(f"  {locale}: nada nuevo que fusionar")
        return

    # Backup
    ts = datetime.now().strftime("%Y%m%d_%H%M%S")
    backup = target_path.with_suffix(f".{ts}.bak")
    shutil.copy2(target_path, backup)

    # Merge manteniendo el orden de es.json
    merged: dict = {}
    target.update(added)
    target.update(updated)
    for key in es_keys:
        if key in target:
            merged[key] = target[key]
    # claves del target que no están en es (edge case)
    for key in target:
        if key not in merged:
            merged[key] = target[key]

    save_json(target_path, merged)
    print(f"  {locale}: +{len(added)} nuevas, {len(updated)} actualizadas "
          f"({skipped} omitidas sin traducir) — backup: {backup.name}")

def main():
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--locales", nargs="+", default=["ca", "eu", "gl"], metavar="LOCALE")
    parser.add_argument("--dry-run", action="store_true", help="Sólo muestra el resumen, no escribe")
    parser.add_argument("--force",   action="store_true", help="Importa entradas sin traducir (valor == clave)")
    args = parser.parse_args()

    es = load_json(LANG_DIR / "es.json")
    es_keys = list(es.keys())
    print(f"Referencia es.json: {len(es_keys)} claves\n")

    for locale in args.locales:
        merge(locale, es_keys, args.dry_run, args.force)

    if not args.dry_run:
        print("\nFusión completada. Comprueba los cambios con:")
        print("  git diff resources/lang/")

if __name__ == "__main__":
    main()
