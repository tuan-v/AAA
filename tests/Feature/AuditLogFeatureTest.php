<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_hides_view_logs_and_presents_actions_in_vietnamese(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'action' => 'view',
            'model_type' => Warehouse::class,
            'model_id' => 1,
            'description' => 'Xem kho',
        ]);
        $createdLog = ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'action' => 'them',
            'model_type' => Warehouse::class,
            'model_id' => 1,
            'description' => 'Thêm kho hàng',
        ]);

        $response = $this->actingAs($user)->getJson('/api/audit-logs?action=create');

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $createdLog->id)
            ->assertJsonPath('data.0.action_key', 'create')
            ->assertJsonPath('data.0.action_label', 'Thêm mới')
            ->assertJsonPath('data.0.model_label', 'Kho hàng');
    }

    public function test_view_request_is_not_recorded_but_update_is_recorded_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $warehouse = Warehouse::where('company_id', $user->company_id)->firstOrFail();
        $this->actingAs($user);
        $initialLogCount = ActivityLog::count();

        $this->getJson("/api/warehouses/{$warehouse->id}")->assertOk();
        $this->assertDatabaseCount('activity_logs', $initialLogCount);

        $this->patchJson("/api/warehouses/{$warehouse->id}/status")->assertOk();

        $this->assertDatabaseCount('activity_logs', $initialLogCount + 1);
        $this->assertDatabaseHas('activity_logs', [
            'company_id' => $user->company_id,
            'model_type' => Warehouse::class,
            'model_id' => $warehouse->id,
            'action' => 'lock',
        ]);
    }
}
