<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountLedger;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\TransactionCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_transaction_can_be_updated_approved_rejected_and_deleted_safely(): void
    {
        $user = User::create([
            'name' => 'Kế toán', 'username' => 'accountant', 'email' => 'accountant@example.com',
            'password' => Hash::make('password'), 'type' => 'user', 'status' => 'active',
        ]);
        $company = Company::create([
            'name' => 'Công ty test', 'address' => 'Hà Nội', 'phone' => '0900000000', 'owner_id' => $user->id,
        ]);
        $currency = Currency::create([
            'name' => 'Việt Nam đồng', 'code' => 'VND', 'symbol' => 'đ', 'exchange_rate' => 1, 'is_active' => true,
        ]);
        $user->update(['company_id' => $company->id]);
        $user->companies()->attach($company->id);
        $company->currencies()->attach($currency->id, ['is_default' => true]);
        $this->actingAs($user);
        $viewPermission = Permission::create([
            'name' => 'giao_dich.xem',
            'guard_name' => 'web',
            'description' => 'Xem giao dịch',
        ]);
        $createPermission = Permission::create([
            'name' => 'giao_dich.them', 'guard_name' => 'web', 'description' => 'Thêm giao dịch',
        ]);
        $updatePermission = Permission::create([
            'name' => 'giao_dich.sua', 'guard_name' => 'web', 'description' => 'Sửa giao dịch',
        ]);
        $user->givePermissionTo([$viewPermission, $createPermission, $updatePermission]);

        $category = TransactionCategory::create([
            'company_id' => $company->id, 'code' => 'THU_KHAC', 'name' => 'Thu khác', 'type' => 'income', 'status' => 'active',
        ]);
        $account = Account::create([
            'company_id' => $company->id, 'code' => 'TM01', 'name' => 'Tiền mặt', 'type' => 'cash',
            'currency_id' => $currency->id, 'opening_balance' => 0, 'current_balance' => 0, 'is_active' => true,
        ]);

        $service = app(TransactionService::class);
        $payload = [
            'type' => 'receipt', 'amount' => 100, 'currency_id' => $currency->id,
            'category_id' => $category->id, 'to_account_id' => $account->id,
            'transaction_date' => '2026-07-20', 'description' => 'Thu thử nghiệm',
        ];

        $transaction = $service->create($payload);
        $this->assertSame('pending', $transaction->status);
        $this->assertEquals(0, $account->fresh()->current_balance);

        $transaction = $service->update($transaction->id, [...$payload, 'amount' => 150, 'description' => 'Nội dung đã sửa']);
        $this->assertEquals(150, $transaction->amount);
        $this->assertSame('Nội dung đã sửa', $transaction->description);

        $transaction = $service->approve($transaction->id);
        $this->assertSame('approved', $transaction->status);
        $this->assertEquals(150, $account->fresh()->current_balance);
        $this->assertDatabaseHas('account_ledgers', ['transaction_id' => $transaction->id, 'debit' => 150]);
        $this->getJson("/api/accountant/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('id', $transaction->id)
            ->assertJsonPath('transaction_date', '2026-07-20')
            ->assertJsonPath('created_by.id', $user->id)
            ->assertJsonStructure(['currency', 'category', 'to_account', 'approved_by']);
        $this->assertThrows(fn () => $service->update($transaction->id, $payload), \RuntimeException::class);
        $this->assertThrows(fn () => $service->delete($transaction->id), \RuntimeException::class);

        $bank = Bank::create([
            'code' => 'VCB', 'name' => 'Vietcombank', 'status' => 'active',
        ]);
        $bankSource = Account::create([
            'company_id' => $company->id, 'code' => 'NH00', 'name' => 'Bank source', 'type' => 'bank',
            'bank_id' => $bank->id, 'bank_account_no' => '001000000001',
            'currency_id' => $currency->id, 'opening_balance' => 150, 'current_balance' => 150, 'is_active' => true,
        ]);
        $destination = Account::create([
            'company_id' => $company->id, 'code' => 'NH01', 'name' => 'Ngân hàng', 'type' => 'bank',
            'bank_id' => $bank->id, 'bank_account_no' => '001000000002',
            'currency_id' => $currency->id, 'opening_balance' => 0, 'current_balance' => 0, 'is_active' => true,
        ]);
        $transferCategory = TransactionCategory::create([
            'company_id' => $company->id, 'code' => 'CHUYEN_KHOAN', 'name' => 'Chuyển tiền nội bộ', 'type' => 'transfer', 'status' => 'active',
        ]);
        $supplierPaymentCategory = TransactionCategory::create([
            'company_id' => $company->id, 'code' => 'CHI_NCC', 'name' => 'Thanh toán nhà cung cấp',
            'type' => 'expense', 'status' => 'active',
        ]);
        $otherPaymentCategory = TransactionCategory::create([
            'company_id' => $company->id, 'code' => 'CHI_KHAC', 'name' => 'Chi khác',
            'type' => 'expense', 'status' => 'active',
        ]);

        $this->assertThrows(fn () => $service->create([
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 10,
            'category_id' => $supplierPaymentCategory->id, 'from_account_id' => $bankSource->id,
            'transaction_date' => '2026-07-20',
        ]), \InvalidArgumentException::class);

        $this->postJson('/api/accountant/transactions', [
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 10,
            'category_id' => $supplierPaymentCategory->id, 'from_account_id' => $bankSource->id,
            'transaction_date' => '2026-07-20',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Thanh toán nhà cung cấp bắt buộc phải chọn nhà cung cấp.');

        $legacyInvalidTransaction = Transaction::create([
            'company_id' => $company->id,
            'code' => 'GD-LEGACY-INVALID',
            'transaction_date' => '2026-07-20',
            'type' => 'payment',
            'payment_method' => 'bank_transfer',
            'category_id' => $supplierPaymentCategory->id,
            'currency_id' => $currency->id,
            'amount' => 10,
            'exchange_rate' => 1,
            'amount_base' => 10,
            'from_account_id' => $bankSource->id,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);
        $this->assertThrows(
            fn () => $service->approve($legacyInvalidTransaction->id),
            \InvalidArgumentException::class,
        );

        $this->assertThrows(fn () => $service->create([
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 151,
            'category_id' => $otherPaymentCategory->id, 'from_account_id' => $bankSource->id,
            'transaction_date' => '2026-07-20',
        ]), \InvalidArgumentException::class);

        $pendingPayment = $service->create([
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 10,
            'category_id' => $otherPaymentCategory->id, 'from_account_id' => $bankSource->id,
            'transaction_date' => '2026-07-20',
        ]);
        $this->putJson("/api/accountant/transactions/{$pendingPayment->id}", [
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 151,
            'category_id' => $otherPaymentCategory->id, 'from_account_id' => $bankSource->id,
            'transaction_date' => '2026-07-20',
        ])->assertStatus(422)
            ->assertJsonPath('message', "Số tiền vượt quá số dư khả dụng của tài khoản 'NH00'.");
        $service->delete($pendingPayment->id);

        $this->assertThrows(fn () => $service->create([
            ...$payload,
            'transaction_date' => now()->addDay()->toDateString(),
        ]), \InvalidArgumentException::class);

        $internalTransfer = $service->create([
            'type' => 'transfer', 'payment_method' => 'bank_transfer', 'amount' => 50,
            'currency_id' => $currency->id, 'category_id' => $transferCategory->id,
            'from_account_id' => $bankSource->id, 'to_account_id' => $destination->id,
            'transaction_date' => '2026-07-20',
        ]);
        $internalTransfer = $service->approve($internalTransfer->id);
        $this->assertSame('internal_transfer', $internalTransfer->purpose);
        $this->assertEquals(100, $bankSource->fresh()->current_balance);
        $this->assertEquals(50, $destination->fresh()->current_balance);
        $this->assertSame(2, AccountLedger::where('transaction_id', $internalTransfer->id)->count());

        $bankReceipt = $service->create([
            'type' => 'receipt', 'payment_method' => 'bank_transfer', 'amount' => 25,
            'currency_id' => $currency->id, 'category_id' => $category->id,
            'to_account_id' => $destination->id, 'transaction_date' => '2026-07-20',
        ]);
        $bankReceipt = $service->approve($bankReceipt->id);
        $this->assertSame('other_receipt', $bankReceipt->purpose);
        $this->assertEquals(75, $destination->fresh()->current_balance);
        $this->assertSame(1, AccountLedger::where('transaction_id', $bankReceipt->id)->count());

        $rejected = $service->create([...$payload, 'amount' => 20]);
        $rejected = $service->reject($rejected->id, 'Chứng từ chưa hợp lệ');
        $this->assertSame('rejected', $rejected->status);
        $this->assertSame('Chứng từ chưa hợp lệ', $rejected->rejection_reason);
        $this->assertNotNull($rejected->rejected_at);

        $deletable = $service->create([...$payload, 'amount' => 30]);
        $service->delete($deletable->id);
        $this->assertDatabaseMissing('transactions', ['id' => $deletable->id]);

        $customer = Customer::create([
            'company_id' => $company->id, 'code' => 'KH-TX', 'name' => 'Khách giao dịch',
            'phone' => '0911111111', 'currency_id' => $currency->id, 'opening_debt' => 100, 'status' => 'active',
        ]);
        $order = SalesOrder::create([
            'company_id' => $company->id, 'code' => 'SO-TX', 'customer_id' => $customer->id,
            'currency_id' => $currency->id, 'exchange_rate' => 1, 'total_amount' => 100,
            'status' => 'approved', 'created_by' => $user->id,
        ]);
        $this->assertThrows(fn () => $service->create([
            ...$payload,
            'amount' => 1,
            'customer_id' => $customer->id,
            'sales_order_id' => $order->id,
        ]), \RuntimeException::class);
    }
}
