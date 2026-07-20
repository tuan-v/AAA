<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_paginated_vietnamese_permissions(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('quyen.xem');

        $response = $this->actingAs($user)->getJson('/api/permissions?per_page=50');

        $response->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page'])
            ->assertJsonPath('per_page', 50);

        $this->assertSame(Permission::count(), $response->json('total'));
        $this->assertNotEmpty($response->json('data.0.group'));
        $this->assertNotEmpty($response->json('data.0.description'));
    }
}
