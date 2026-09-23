<?php

namespace Tests\Unit\Tools;

use App\Services\Tools\DiseaseRiskModel;
use PHPUnit\Framework\TestCase;

class DiseaseRiskModelTest extends TestCase
{
    public function test_mildew_three_tens_rule(): void
    {
        [$times, $temps, $rh, $rain] = $this->series([
            '2026-05-01' => ['temps' => $this->temps(14), 'rain' => 6],
            '2026-05-02' => ['temps' => $this->temps(14), 'rain' => 5],   // 11 mm en 48 h → alto
            '2026-05-03' => ['temps' => $this->temps(8), 'rain' => 12],   // mucha lluvia pero frío → bajo
            '2026-05-04' => ['temps' => $this->temps(8)],                 // 12 mm en 48 h, pero frío → bajo
            '2026-05-05' => ['temps' => $this->temps(18), 'rain' => 2],   // lluvia ligera templada → moderado
            '2026-05-06' => ['temps' => $this->temps(20), 'rh' => 40],    // seco → bajo
        ]);

        $days = (new DiseaseRiskModel)->evaluate($times, $temps, $rh, $rain, '2026-05-02');

        $this->assertSame(['2026-05-02', '2026-05-03', '2026-05-04', '2026-05-05', '2026-05-06'], array_column($days, 'date'));
        $this->assertSame(
            [DiseaseRiskModel::HIGH, DiseaseRiskModel::LOW, DiseaseRiskModel::LOW, DiseaseRiskModel::MODERATE, DiseaseRiskModel::LOW],
            array_column($days, 'mildew')
        );
        $this->assertEqualsWithDelta(11, $days[0]['rain_48h'], 0.01);
    }

    public function test_mildew_moderate_with_humid_hours_and_no_rain(): void
    {
        [$times, $temps, $rh, $rain] = $this->series([
            '2026-05-01' => ['temps' => $this->temps(16), 'rh' => 95],
        ]);

        $days = (new DiseaseRiskModel)->evaluate($times, $temps, $rh, $rain, '2026-05-01');

        $this->assertSame(DiseaseRiskModel::MODERATE, $days[0]['mildew']);
    }

    public function test_powdery_gubler_thomas_index(): void
    {
        $favourable = ['temps' => $this->temps(15, 8)];      // 8 h seguidas a 25 °C
        $unfavourable = ['temps' => $this->temps(15, 4)];    // solo 4 h
        $scorching = ['temps' => $this->temps(15, 8, 36)];   // calor extremo, sin horas 21–30

        [$times, $temps, $rh, $rain] = $this->series([
            '2026-06-01' => $favourable,     // 20 (arranque)
            '2026-06-02' => $unfavourable,   // se rompe la racha → 0
            '2026-06-03' => $favourable,     // 20
            '2026-06-04' => $favourable,     // 40
            '2026-06-05' => $favourable,     // 3 seguidos → epidemia, 60
            '2026-06-06' => $favourable,     // 80
            '2026-06-07' => $unfavourable,   // 70
            '2026-06-08' => $scorching,      // −10 −10 → 50
        ]);

        $days = (new DiseaseRiskModel)->evaluate($times, $temps, $rh, $rain, '2026-06-01');

        $this->assertSame([20, 0, 20, 40, 60, 80, 70, 50], array_column($days, 'powdery_index'));
        $this->assertSame(DiseaseRiskModel::LOW, $days[2]['powdery']);
        $this->assertSame(DiseaseRiskModel::MODERATE, $days[3]['powdery']);
        $this->assertSame(DiseaseRiskModel::HIGH, $days[4]['powdery']);
        $this->assertSame(DiseaseRiskModel::MODERATE, $days[7]['powdery']);
    }

    public function test_powdery_index_is_capped_and_uses_history(): void
    {
        $favourable = ['temps' => $this->temps(15, 8)];
        $series = [];
        for ($d = 1; $d <= 8; $d++) {
            $series[sprintf('2026-07-%02d', $d)] = $favourable;
        }
        [$times, $temps, $rh, $rain] = $this->series($series);

        // Solo se devuelven días desde "hoy", pero el índice arrastra el histórico.
        $days = (new DiseaseRiskModel)->evaluate($times, $temps, $rh, $rain, '2026-07-08');

        $this->assertCount(1, $days);
        $this->assertSame(100, $days[0]['powdery_index']);
    }

    public function test_incomplete_days_are_dropped(): void
    {
        [$times, $temps, $rh, $rain] = $this->series([
            '2026-05-01' => ['temps' => $this->temps(14)],
            '2026-05-02' => ['temps' => array_slice($this->temps(14), 0, 10)],
        ]);

        $days = (new DiseaseRiskModel)->evaluate($times, $temps, $rh, $rain, '2026-05-01');

        $this->assertSame(['2026-05-01'], array_column($days, 'date'));
    }

    /**
     * Serie horaria sintética: un array por día con [temps(24), rain total, humidity].
     *
     * @param array<string, array{temps: array<int, float>, rain?: float, rh?: float}> $days
     *
     * @return array{0: array<int, string>, 1: array<int, float>, 2: array<int, float>, 3: array<int, float>}
     */
    private function series(array $days): array
    {
        $times = $temps = $rh = $rain = [];
        foreach ($days as $date => $day) {
            foreach ($day['temps'] as $hour => $t) {
                $times[] = sprintf('%sT%02d:00', $date, $hour);
                $temps[] = $t;
                $rh[] = $day['rh'] ?? 50;
                $rain[] = ($day['rain'] ?? 0) / 24;
            }
        }

        return [$times, $temps, $rh, $rain];
    }

    /** 24 h: $hot horas seguidas a $hotTemp y el resto a $base. */
    private function temps(float $base, int $hot = 0, float $hotTemp = 25): array
    {
        $temps = array_fill(0, 24, $base);
        for ($h = 10; $h < 10 + $hot; $h++) {
            $temps[$h] = $hotTemp;
        }

        return $temps;
    }
}
