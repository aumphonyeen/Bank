<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $customers = DB::table('customers')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $customer): array => [
                'number' => $customer->id,
                'code' => $customer->customer_code,
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'name' => "{$customer->first_name} {$customer->last_name}",
                'email' => $customer->email,
                'phone' => $customer->phone,
                'birthDate' => $customer->birth_date,
            ]);

        $accounts = DB::table('accounts')
            ->join('customers', 'customers.id', '=', 'accounts.customer_id')
            ->orderBy('accounts.id')
            ->get([
                'accounts.id',
                'accounts.account_number',
                'accounts.account_type',
                'accounts.balance',
                'accounts.status',
                'accounts.opened_at',
                'customers.id as customer_id',
                'customers.customer_code',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.phone',
            ])
            ->map(static fn (object $account): array => [
                'id' => $account->id,
                'customer' => [
                    'number' => $account->customer_id,
                    'code' => $account->customer_code,
                    'firstName' => $account->first_name,
                    'lastName' => $account->last_name,
                    'name' => "{$account->first_name} {$account->last_name}",
                    'email' => $account->email,
                    'phone' => $account->phone,
                ],
                'number' => $account->account_number,
                'type' => $account->account_type,
                'balance' => (float) $account->balance,
                'status' => $account->status,
                'opened' => $account->opened_at,
            ]);

        $transactions = DB::table('transactions')
            ->join('accounts', 'accounts.id', '=', 'transactions.account_id')
            ->join('customers', 'customers.id', '=', 'accounts.customer_id')
            ->orderByDesc('transactions.transaction_date')
            ->orderByDesc('transactions.id')
            ->get([
                'transactions.id',
                'transactions.account_id',
                'transactions.transaction_code',
                'transactions.transaction_type',
                'transactions.amount',
                'transactions.description',
                'transactions.transaction_date',
                'transactions.status as transaction_status',
                'accounts.account_number',
                'accounts.account_type',
                'accounts.balance',
                'accounts.status as account_status',
                'accounts.opened_at',
                'customers.id as customer_id',
                'customers.customer_code',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.phone',
            ])
            ->map(static fn (object $transaction): array => [
                'id' => $transaction->id,
                'code' => $transaction->transaction_code,
                'type' => $transaction->transaction_type,
                'status' => $transaction->transaction_status,
                'amount' => (float) $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->transaction_date,
                'account' => [
                    'id' => $transaction->account_id,
                    'customer' => [
                        'number' => $transaction->customer_id,
                        'code' => $transaction->customer_code,
                        'firstName' => $transaction->first_name,
                        'lastName' => $transaction->last_name,
                        'name' => "{$transaction->first_name} {$transaction->last_name}",
                        'email' => $transaction->email,
                        'phone' => $transaction->phone,
                    ],
                    'number' => $transaction->account_number,
                    'type' => $transaction->account_type,
                    'balance' => (float) $transaction->balance,
                    'status' => $transaction->account_status,
                    'opened' => $transaction->opened_at,
                ],
            ]);

        return response()->json([
            'staff' => [
                'employeeCode' => auth()->user()->employee_code,
                'name' => auth()->user()->name,
                'role' => auth()->user()->role,
                'position' => auth()->user()->position,
            ],
            'customers' => $customers,
            'accounts' => $accounts,
            'transactions' => $transactions,
        ]);
    }
}
