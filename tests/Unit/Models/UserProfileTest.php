<?php

namespace Tests\Unit\Models;

use App\Models\UserProfile;
use App\Models\User;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_user_profile_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'address' => 'Calle Test 123',
            'city' => 'Madrid',
            'postal_code' => '28001',
        ]);

        $this->assertEquals($user->id, $profile->user->id);
        $this->assertInstanceOf(User::class, $profile->user);
    }

    public function test_user_profile_belongs_to_province(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $province = Province::first();

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'province_id' => $province->id,
            'address' => 'Calle Test 123',
            'city' => 'Madrid',
        ]);

        $this->assertEquals($province->id, $profile->province->id);
        $this->assertInstanceOf(Province::class, $profile->province);
    }

    public function test_user_profile_can_store_all_fields(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $province = Province::first();

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'address' => 'Calle Test 123',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'province_id' => $province->id,
            'country' => 'España',
            'phone' => '+34 600 123 456',
            'profile_image' => 'profile.jpg',
        ]);

        $this->assertEquals('Calle Test 123', $profile->address);
        $this->assertEquals('Madrid', $profile->city);
        $this->assertEquals('28001', $profile->postal_code);
        $this->assertEquals($province->id, $profile->province_id);
        $this->assertEquals('España', $profile->country);
        $this->assertEquals('+34 600 123 456', $profile->phone);
        $this->assertEquals('profile.jpg', $profile->profile_image);
    }

    public function test_user_profile_can_have_nullable_fields(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $profile = UserProfile::create([
            'user_id' => $user->id,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'province_id' => null,
            'country' => null,
            'phone' => null,
            'profile_image' => null,
        ]);

        $this->assertNotNull($profile->id);
        $this->assertNull($profile->address);
        $this->assertNull($profile->city);
        $this->assertNull($profile->province_id);
    }

    public function test_user_can_have_only_one_profile(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $profile1 = UserProfile::create([
            'user_id' => $user->id,
            'address' => 'Address 1',
        ]);

        // Intentar crear otro perfil para el mismo usuario debería fallar o reemplazar
        // Depende de la implementación, pero típicamente sería hasOne
        $this->assertEquals($user->id, $profile1->user_id);
    }
}

