<?php

namespace Tests\Unit\i18n;

use PHPUnit\Framework\TestCase;

/**
 * Translation coverage regression tests.
 *
 * These tests do NOT require all locales to be 100% complete.
 * Instead they record a baseline and FAIL if the gap grows — i.e.,
 * you add a key to es.json without adding it to the other locales.
 *
 * To update a baseline after intentionally adding Spanish-only keys:
 *   php artisan test tests/Unit/i18n/ --no-coverage
 * and raise the constant to the new count.
 */
class TranslationCoverageTest extends TestCase
{
    // ── Baselines (updated 2026-05-28) ─────────────────────────────────────────
    // Raise these only when intentionally accepting new untranslated keys.
    private const BASELINE_EN = 0;

    private const BASELINE_CA = 7468;

    private const BASELINE_EU = 7272;

    private const BASELINE_GL = 8342;

    // ── Tests ─────────────────────────────────────────────────────────────────

    public function test_en_coverage_has_not_regressed(): void
    {
        $missing = $this->missingKeys('en');
        $count = count($missing);

        $this->assertLessThanOrEqual(
            self::BASELINE_EN,
            $count,
            sprintf(
                "en.json coverage regressed: was %d missing, now %d missing (+%d new gaps).\nNew missing keys:\n%s",
                self::BASELINE_EN,
                $count,
                $count - self::BASELINE_EN,
                implode("\n", array_map(fn ($k) => "  - \"{$k}\"", array_slice($missing, 0, 20)))
                .($count - self::BASELINE_EN > 20 ? "\n  ... and more" : '')
            )
        );
    }

    public function test_ca_coverage_has_not_regressed(): void
    {
        $missing = $this->missingKeys('ca');
        $count = count($missing);

        $this->assertLessThanOrEqual(
            self::BASELINE_CA,
            $count,
            sprintf(
                "ca.json coverage regressed: was %d missing, now %d missing (+%d new gaps).\nNew missing keys:\n%s",
                self::BASELINE_CA,
                $count,
                $count - self::BASELINE_CA,
                implode("\n", array_map(fn ($k) => "  - \"{$k}\"", array_slice($missing, 0, 20)))
                .($count - self::BASELINE_CA > 20 ? "\n  ... and more" : '')
            )
        );
    }

    public function test_eu_coverage_has_not_regressed(): void
    {
        $missing = $this->missingKeys('eu');
        $count = count($missing);

        $this->assertLessThanOrEqual(
            self::BASELINE_EU,
            $count,
            sprintf(
                "eu.json coverage regressed: was %d missing, now %d missing (+%d new gaps).\nNew missing keys:\n%s",
                self::BASELINE_EU,
                $count,
                $count - self::BASELINE_EU,
                implode("\n", array_map(fn ($k) => "  - \"{$k}\"", array_slice($missing, 0, 20)))
                .($count - self::BASELINE_EU > 20 ? "\n  ... and more" : '')
            )
        );
    }

    public function test_gl_coverage_has_not_regressed(): void
    {
        $missing = $this->missingKeys('gl');
        $count = count($missing);

        $this->assertLessThanOrEqual(
            self::BASELINE_GL,
            $count,
            sprintf(
                "gl.json coverage regressed: was %d missing, now %d missing (+%d new gaps).\nNew missing keys:\n%s",
                self::BASELINE_GL,
                $count,
                $count - self::BASELINE_GL,
                implode("\n", array_map(fn ($k) => "  - \"{$k}\"", array_slice($missing, 0, 20)))
                .($count - self::BASELINE_GL > 20 ? "\n  ... and more" : '')
            )
        );
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function loadJson(string $locale): array
    {
        $path = dirname(__DIR__, 3)."/resources/lang/{$locale}.json";

        return json_decode(file_get_contents($path), true);
    }

    private function missingKeys(string $locale): array
    {
        $esKeys = array_keys($this->loadJson('es'));
        $locKeys = array_keys($this->loadJson($locale));

        return array_values(array_diff($esKeys, $locKeys));
    }
}
