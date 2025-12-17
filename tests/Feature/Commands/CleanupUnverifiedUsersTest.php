<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Tests\TestCase;

class CleanupUnverifiedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_unverified_users_after_24_hours(): void
    {
        // Usuario no verificado creado hace 25 horas
        $oldUser = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(25),
        ]);

        // Usuario no verificado creado hace 1 hora (no debe eliminarse)
        $recentUser = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHour(),
        ]);

        // Usuario verificado creado hace 25 horas (no debe eliminarse)
        $verifiedUser = User::factory()->create([
            'email_verified_at' => Carbon::now()->subHours(25),
            'created_at' => Carbon::now()->subHours(25),
        ]);

        Artisan::call('users:cleanup-unverified', ['--hours' => 24]);

        $this->assertDatabaseMissing('users', ['id' => $oldUser->id]);
        $this->assertDatabaseHas('users', ['id' => $recentUser->id]);
        $this->assertDatabaseHas('users', ['id' => $verifiedUser->id]);
    }

    public function test_command_deletes_user_relationships(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(25),
        ]);

        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(25),
        ]);

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(25),
        ]);

        // Crear relaciones
        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => 'own',
            'assigned_by' => $winery->id,
        ]);

        Artisan::call('users:cleanup-unverified', ['--hours' => 24]);

        // Verificar que las relaciones se eliminaron
        $this->assertDatabaseMissing('supervisor_winery', [
            'supervisor_id' => $supervisor->id,
        ]);

        $this->assertDatabaseMissing('winery_viticulturist', [
            'winery_id' => $winery->id,
        ]);

        // Verificar que los usuarios se eliminaron
        $this->assertDatabaseMissing('users', ['id' => $supervisor->id]);
        $this->assertDatabaseMissing('users', ['id' => $winery->id]);
        $this->assertDatabaseMissing('users', ['id' => $viticulturist->id]);
    }

    public function test_command_respects_custom_hours_option(): void
    {
        // Usuario creado hace 12 horas
        $user = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(12),
        ]);

        // Con --hours=10, debería eliminarse (tiene 12 horas, más de 10)
        Artisan::call('users:cleanup-unverified', ['--hours' => 10]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // Crear otro usuario para el segundo test (hace 5 horas)
        $user2 = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(5),
        ]);

        // Con --hours=10, no debería eliminarse (tiene 5 horas, menos de 10)
        Artisan::call('users:cleanup-unverified', ['--hours' => 10]);

        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    public function test_command_handles_no_unverified_users(): void
    {
        // Solo usuarios verificados
        User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $result = Artisan::call('users:cleanup-unverified');

        $this->assertEquals(0, $result);
    }

    public function test_command_outputs_correct_information(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'created_at' => Carbon::now()->subHours(25),
        ]);

        Artisan::call('users:cleanup-unverified', ['--hours' => 24]);

        $output = Artisan::output();
        
        $this->assertStringContainsString('eliminaron', strtolower($output));
    }
}

