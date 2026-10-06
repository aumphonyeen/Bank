<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BankingDataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutation_endpoints_require_authentication(): void
    {
        $this->postJson('/api/customers', [])->assertUnauthorized();
        $this->putJson('/api/accounts/1', [])->assertUnauthorized();
        $this->postJson('/api/transactions', [])->assertUnauthorized();
        $this->deleteJson('/api/customers/1')->assertUnauthorized();
        $this->deleteJson('/api/accounts/1')->assertUnauthorized();
        $this->deleteJson('/api/transactions/1')->assertUnauthorized();
    }

    public function test_employee_can_create_and_update_customer(): void
    {
        $this->actingAs($this->createEmployee());

        $this->postJson('/api/customers', [
            'customer_code' => 'CUS9001',
            'first_name' => 'สมชาย',
            'last_name' => 'ใจดี',
            'email' => 'somchai@example.test',
            'phone' => '0812345678',
            'birth_date' => '1990-01-02',
        ])->assertCreated()->assertJsonPath('message', 'เพิ่มข้อมูลลูกค้าแล้ว');

        $customerId = DB::table('customers')->where('customer_code', 'CUS9001')->value('id');
        $this->putJson("/api/customers/{$customerId}", [
            'customer_code' => 'CUS9002',
            'first_name' => 'สมชาย',
            'last_name' => 'เปลี่ยนชื่อ',
            'email' => 'somchai.updated@example.test',
            'phone' => '0898765432',
            'birth_date' => '1991-03-04',
        ])->assertOk();

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'customer_code' => 'CUS9002',
            'last_name' => 'เปลี่ยนชื่อ',
        ]);
    }

    public function test_employee_can_create_and_update_account(): void
    {
        $this->actingAs($this->createEmployee());
        $customerId = $this->createCustomer();

        $this->postJson('/api/accounts', [
            'customer_id' => $customerId,
            'account_number' => '900-1-00001-1',
            'account_type' => 'savings',
            'balance' => '120.50',
            'status' => 'active',
            'opened_at' => '2026-01-01',
        ])->assertCreated();

        $accountId = DB::table('accounts')->where('account_number', '900-1-00001-1')->value('id');
        $this->putJson("/api/accounts/{$accountId}", [
            'customer_id' => $customerId,
            'account_number' => '900-1-00001-2',
            'account_type' => 'current',
            'balance' => '150.75',
            'status' => 'inactive',
            'opened_at' => '2026-02-01',
        ])->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id' => $accountId,
            'account_number' => '900-1-00001-2',
            'account_type' => 'current',
            'balance' => '150.75',
            'status' => 'inactive',
        ]);
    }

    public function test_creating_and_editing_transaction_adjusts_account_balances(): void
    {
        $this->actingAs($this->createEmployee());
        $customerId = $this->createCustomer();
        $firstAccountId = $this->createAccount($customerId, '900-1-00001-1', '100.00');
        $secondAccountId = $this->createAccount($customerId, '900-1-00002-2', '200.00');

        $this->postJson('/api/transactions', [
            'account_id' => $firstAccountId,
            'transaction_type' => 'deposit',
            'amount' => '50.00',
            'description' => 'ฝากเงิน',
            'transaction_date' => '2026-09-01T10:30',
        ])->assertCreated();

        $transactionId = DB::table('transactions')->where('account_id', $firstAccountId)->value('id');
        $this->assertDatabaseHas('accounts', ['id' => $firstAccountId, 'balance' => '150.00']);
        $this->assertDatabaseHas('transactions', ['id' => $transactionId, 'transaction_date' => '2026-09-01 10:30:00']);

        $this->putJson("/api/transactions/{$transactionId}", [
            'account_id' => $secondAccountId,
            'transaction_type' => 'withdrawal',
            'amount' => '30.00',
            'description' => 'ถอนเงิน',
            'transaction_date' => '2026-09-02T11:45',
        ])->assertOk();

        $this->assertDatabaseHas('accounts', ['id' => $firstAccountId, 'balance' => '100.00']);
        $this->assertDatabaseHas('accounts', ['id' => $secondAccountId, 'balance' => '170.00']);
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'account_id' => $secondAccountId,
            'transaction_type' => 'withdrawal',
            'amount' => '30.00',
            'transaction_date' => '2026-09-02 11:45:00',
        ]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('accounts.0.id', $firstAccountId)
            ->assertJsonPath('transactions.0.id', $transactionId)
            ->assertJsonPath('transactions.0.account.id', $secondAccountId);
    }

    public function test_withdrawal_cannot_overdraw_account_or_create_transaction(): void
    {
        $this->actingAs($this->createEmployee());
        $customerId = $this->createCustomer();
        $accountId = $this->createAccount($customerId, '900-1-00001-1', '10.00');

        $this->postJson('/api/transactions', [
            'account_id' => $accountId,
            'transaction_type' => 'withdrawal',
            'amount' => '10.01',
            'description' => 'ถอนเกินยอด',
            'transaction_date' => '2026-09-01T10:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');

        $this->assertDatabaseHas('accounts', ['id' => $accountId, 'balance' => '10.00']);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_deleting_completed_transaction_reverses_its_account_balance(): void
    {
        $this->actingAs($this->createEmployee());
        $customerId = $this->createCustomer();
        $accountId = $this->createAccount($customerId, '900-1-00001-1', '150.00');
        $transactionId = DB::table('transactions')->insertGetId([
            'account_id' => $accountId,
            'transaction_code' => 'TXN-DELETE-001',
            'transaction_type' => 'deposit',
            'amount' => '50.00',
            'description' => 'รายการทดสอบ',
            'transaction_date' => '2026-09-01 10:30:00',
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson("/api/transactions/{$transactionId}")
            ->assertOk()
            ->assertJsonPath('message', 'ลบรายการธุรกรรมและปรับยอดบัญชีแล้ว');

        $this->assertDatabaseMissing('transactions', ['id' => $transactionId]);
        $this->assertDatabaseHas('accounts', ['id' => $accountId, 'balance' => '100.00']);
    }

    public function test_deleting_customer_cascades_to_accounts_and_transactions(): void
    {
        $this->actingAs($this->createEmployee());
        $customerId = $this->createCustomer();
        $accountId = $this->createAccount($customerId, '900-1-00001-1', '100.00');
        $transactionId = DB::table('transactions')->insertGetId([
            'account_id' => $accountId,
            'transaction_code' => 'TXN-DELETE-002',
            'transaction_type' => 'deposit',
            'amount' => '20.00',
            'description' => 'รายการทดสอบ',
            'transaction_date' => '2026-09-01 10:30:00',
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson("/api/customers/{$customerId}")
            ->assertOk()
            ->assertJsonPath('message', 'ลบข้อมูลลูกค้าและข้อมูลบัญชีที่เกี่ยวข้องแล้ว');

        $this->assertDatabaseMissing('customers', ['id' => $customerId]);
        $this->assertDatabaseMissing('accounts', ['id' => $accountId]);
        $this->assertDatabaseMissing('transactions', ['id' => $transactionId]);
    }

    private function createEmployee(): Employee
    {
        return Employee::create([
            'employee_code' => 'EMP0001',
            'name' => 'เจ้าหน้าที่ทดลอง',
            'email' => 'staff@banklite.test',
            'position' => 'ผู้ดูแลระบบ',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'BankLite@123',
        ]);
    }

    private function createCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'customer_code' => 'CUS9001',
            'first_name' => 'สมชาย',
            'last_name' => 'ใจดี',
            'email' => 'somchai@example.test',
            'phone' => '0812345678',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAccount(int $customerId, string $number, string $balance): int
    {
        return DB::table('accounts')->insertGetId([
            'customer_id' => $customerId,
            'account_number' => $number,
            'account_type' => 'savings',
            'balance' => $balance,
            'status' => 'active',
            'opened_at' => '2026-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
