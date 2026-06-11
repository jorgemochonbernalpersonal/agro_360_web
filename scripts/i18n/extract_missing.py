#!/usr/bin/env python3
"""
Extrae las claves que faltan en ca/eu/gl respecto a es.json y las divide en lotes.

Uso:
    python3 scripts/i18n/extract_missing.py [--batch-size N] [--locales ca eu gl]

Salida:
    scripts/i18n/missing/<locale>/batch_001.json  (claves → valor es, listo para traducir)

Cada fichero de lote tiene el formato:
    { "Clave española": "Clave española", ... }

El traductor reemplaza el valor con la traducción y luego ejecuta merge_translations.py.
"""

import json
import os
import argparse
from pathlib import Path

LANG_DIR = Path(__file__).resolve().parents[2] / "resources" / "lang"
OUT_DIR  = Path(__file__).resolve().parent / "missing"

def load(locale: str) -> dict:
    path = LANG_DIR / f"{locale}.json"
    with open(path, encoding="utf-8") as f:
        return json.load(f)

def save_batch(locale: str, idx: int, batch: dict) -> Path:
    dest = OUT_DIR / locale
    dest.mkdir(parents=True, exist_ok=True)
    path = dest / f"batch_{idx:03d}.json"
    with open(path, "w", encoding="utf-8") as f:
        json.dump(batch, f, ensure_ascii=False, indent=2)
    return path

def extract(locale: str, es: dict, batch_size: int):
    target = load(locale)
    missing_keys = [k for k in es if k not in target]

    if not missing_keys:
        print(f"  {locale}: nada que extraer ✓")
        return 0

    batches = [missing_keys[i:i+batch_size] for i in range(0, len(missing_keys), batch_size)]
    for idx, batch_keys in enumerate(batches, start=1):
        batch = {k: es[k] for k in batch_keys}
        path = save_batch(locale, idx, batch)
        print(f"  {locale} lote {idx:03d}: {len(batch_keys)} claves -> {path.relative_to(Path.cwd())}")

    print(f"  {locale}: {len(missing_keys)} claves en {len(batches)} lotes")
    return len(missing_keys)

def main():
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--batch-size", type=int, default=200, metavar="N",
                        help="Claves por lote (defecto: 200)")
    parser.add_argument("--locales", nargs="+", default=["ca", "eu", "gl"],
                        metavar="LOCALE", help="Idiomas a procesar (defecto: ca eu gl)")
    args = parser.parse_args()

    es = load("es")
    print(f"Referencia es.json: {len(es)} claves\n")

    total = 0
    for locale in args.locales:
        total += extract(locale, es, args.batch_size)

    print(f"\nTotal claves faltantes: {total}")
    print(f"Lotes guardados en:    scripts/i18n/missing/")
    print(f"\nUna vez traducidos, ejecuta:")
    print(f"  python3 scripts/i18n/merge_translations.py")

if __name__ == "__main__":
    main()
