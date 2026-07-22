<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\TransactionCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCategoryCompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_cannot_read_update_or_delete_another_company_transaction_category(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $foreignOwner = User::factory()->create(['company_id' => null, 'status' => 'active']);
        $foreignCompany = Company::create([
            'name' => 'Công ty khác',
            'address' => 'Đà Nẵng',
            'phone' => '0909000000',
            'owner_id' => $foreignOwner->id,
        ]);
        $foreignOwner->update(['company_id' => $foreignCompany->id]);

        $category = TransactionCategory::create([
            'company_id' => $foreignCompany->id,
            'code' => 'LGD-FOREIGN',
            'name' => 'Loại giao dịch công ty khác',
            'type' => 'income',
            'status' => 1,
        ]);

        $this->actingAs($owner)
            ->getJson("/api/accountant/transaction-categories/{$category->id}")
            ->assertNotFound();

        $this->actingAs($owner)
            ->putJson("/api/accountant/transaction-categories/{$category->id}", [
                'name' => 'Không được sửa',
                'description' => null,
                'status' => 1,
            ])
            ->assertNotFound();

        $this->actingAs($owner)
            ->deleteJson("/api/accountant/transaction-categories/{$category->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('transaction_categories', [
            'id' => $category->id,
            'name' => 'Loại giao dịch công ty khác',
        ]);
    }
}
