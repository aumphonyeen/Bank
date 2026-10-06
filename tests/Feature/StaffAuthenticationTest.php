<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_dashboard_api_redirect_or_reject_guests(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_active_employee_can_sign_in_with_employee_code_and_access_dashboard(): void
    {
        $employee = $this->createEmployee();

        $this->post('/login', [
            'login' => $employee->employee_code,
            'password' => 'BankLite@123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($employee);
        $this->get('/')->assertOk()->assertSee('ข้อมูลบัญชี ลูกค้า และธุรกรรมจากฐานข้อมูล MySQL');
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('staff.employeeCode', 'EMP0001');
    }

    public function test_active_employee_can_sign_in_with_email(): void
    {
        $employee = $this->createEmployee();

        $this->post('/login', [
            'login' => $employee->email,
            'password' => 'BankLite@123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($employee);
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        $this->createEmployee();

        $this->from('/login')->post('/login', [
            'login' => 'EMP0001',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_inactive_employee_cannot_sign_in(): void
    {
        $this->createEmployee(['status' => 'inactive']);

        $this->from('/login')->post('/login', [
            'login' => 'EMP0001',
            'password' => 'BankLite@123',
        ])->assertRedirect('/login')->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_employee_can_sign_out_and_protected_pages_require_login_again(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
        $this->get('/')->assertRedirect('/login');
    }

    private function createEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'employee_code' => 'EMP0001',
            'name' => 'เจ้าหน้าที่ทดลอง',
            'email' => 'staff@banklite.test',
            'position' => 'ผู้ดูแลระบบ',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'BankLite@123',
        ], $overrides));
    }
}
