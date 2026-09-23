<?php

namespace App\Services\Tools;

/**
 * Modelos de riesgo de mildiu y oídio de la vid a partir de meteo horaria.
 * Puro (sin HTTP ni BD) para poder testearlo con series inventadas.
 *
 * Mildiu (Plasmopara viticola) — "regla de los tres dieces": infección primaria
 * posible con ≥10 mm de lluvia en 48 h, temperatura ≥10 °C y brotes ≥10 cm
 * (esto último no lo sabemos: se avisa en el texto). Riesgo moderado cuando hay
 * humectación (lluvia o HR ≥90 % varias horas) con 10–30 °C: infecciones secundarias.
 *
 * Oídio (Erysiphe necator) — índice de Gubler-Thomas (UC Davis): tras 3 días
 * seguidos con ≥6 h consecutivas entre 21 y 30 °C la epidemia arranca (índice 60);
 * después +20 por cada día que lo cumple, −10 si no, −10 extra si se llega a 35 °C.
 * Índice 0–100: <40 bajo, 40–50 moderado, ≥60 alto.
 */
class DiseaseRiskModel
{
    public const LOW = 'low';

    public const MODERATE = 'moderate';

    public const HIGH = 'high';

    /**
     * @param array<int, string>     $times          ISO locales "Y-m-d\TH:i" (hora local de la parcela)
     * @param array<int, float|null> $temperatures   °C
     * @param array<int, float|null> $humidities     %
     * @param array<int, float|null> $precipitations mm
     * @param string                 $today          Y-m-d; los días anteriores solo sirven de histórico
     *
     * @return array<int, array{date: string, temp_mean: float, temp_max: float, rain: float, rain_48h: float, mildew: string, powdery_index: int, powdery: string}>
     */
    public function evaluate(array $times, array $temperatures, array $humidities, array $precipitations, string $today): array
    {
        $days = $this->groupByDay($times, $temperatures, $humidities, $precipitations);

        $result = [];
        $previousRain = 0.0;
        $powderyIndex = 0;
        $consecutiveFavourable = 0;
        $epidemicStarted = false;

        foreach ($days as $date => $hours) {
            $temps = array_column($hours, 't');
            $tempMean = array_sum($temps) / count($temps);
            $rain = array_sum(array_column($hours, 'p'));
            $rain48 = $rain + $previousRain;
            $previousRain = $rain;

            $favourable = $this->longestRun($temps, fn (float $t) => $t >= 21 && $t <= 30) >= 6;
            $scorching = max($temps) >= 35;

            if (! $epidemicStarted) {
                $consecutiveFavourable = $favourable ? $consecutiveFavourable + 1 : 0;
                if ($consecutiveFavourable >= 3) {
                    $epidemicStarted = true;
                    $powderyIndex = 60;
                } else {
                    $powderyIndex = $consecutiveFavourable * 20;
                }
            } else {
                $powderyIndex += $favourable ? 20 : -10;
                if ($scorching) {
                    $powderyIndex -= 10;
                }
                $powderyIndex = max(0, min(100, $powderyIndex));
            }

            if ($date < $today) {
                continue;
            }

            $result[] = [
                'date' => $date,
                'temp_mean' => round($tempMean, 1),
                'temp_max' => max($temps),
                'rain' => round($rain, 1),
                'rain_48h' => round($rain48, 1),
                'mildew' => $this->mildewLevel($tempMean, $rain, $rain48, $hours),
                'powdery_index' => $powderyIndex,
                'powdery' => match (true) {
                    $powderyIndex >= 60 => self::HIGH,
                    $powderyIndex >= 40 => self::MODERATE,
                    default => self::LOW,
                },
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array{t: float, h: float|null, p: float}> $hours
     */
    private function mildewLevel(float $tempMean, float $rain, float $rain48, array $hours): string
    {
        if ($rain48 >= 10 && $tempMean >= 10) {
            return self::HIGH;
        }

        $humidHours = count(array_filter($hours, fn (array $h) => $h['h'] !== null && $h['h'] >= 90));
        if ($tempMean >= 10 && $tempMean <= 30 && ($rain >= 1 || $humidHours >= 4)) {
            return self::MODERATE;
        }

        return self::LOW;
    }

    /**
     * Agrupa la serie horaria por día descartando horas sin temperatura.
     * Días con menos de 18 horas válidas se descartan (medias poco fiables).
     *
     * @return array<string, array<int, array{t: float, h: float|null, p: float}>>
     */
    private function groupByDay(array $times, array $temperatures, array $humidities, array $precipitations): array
    {
        $days = [];
        foreach ($times as $i => $time) {
            if (! isset($temperatures[$i])) {
                continue;
            }
            $days[substr($time, 0, 10)][] = [
                't' => (float) $temperatures[$i],
                'h' => isset($humidities[$i]) ? (float) $humidities[$i] : null,
                'p' => (float) ($precipitations[$i] ?? 0),
            ];
        }

        ksort($days);

        return array_filter($days, fn (array $hours) => count($hours) >= 18);
    }

    /** @param array<int, float> $values */
    private function longestRun(array $values, callable $predicate): int
    {
        $best = 0;
        $current = 0;
        foreach ($values as $value) {
            $current = $predicate($value) ? $current + 1 : 0;
            $best = max($best, $current);
        }

        return $best;
    }
}
