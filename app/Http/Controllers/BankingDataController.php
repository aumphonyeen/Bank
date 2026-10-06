<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BankingDataController extends Controller
{
    public function storeCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_code' => ['required', 'string', 'max:20', 'unique:customers,customer_code'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $id = DB::table('customers')->insertGetId([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'เพิ่มข้อมูลลูกค้าแล้ว'], 201);
    }

    public function updateCustomer(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'customer_code' => ['required', 'string', 'max:20', Rule::unique('customers', 'customer_code')->ignore($id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($id)],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $updated = DB::table('customers')->where('id', $id)->update([
            ...$data,
            'updated_at' => now(),
        ]);

        abort_if($updated === 0 && ! DB::table('customers')->where('id', $id)->exists(), 404);

        return response()->json(['message' => 'บันทึกข้อมูลลูกค้าแล้ว']);
    }

    public function deleteCustomer(int $id): JsonResponse
    {
        $deleted = DB::table('customers')->where('id', $id)->delete();

        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'ลบข้อมูลลูกค้าและข้อมูลบัญชีที่เกี่ยวข้องแล้ว']);
    }

    public function storeAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'account_number' => ['required', 'string', 'max:20', 'unique:accounts,account_number'],
            'account_type' => ['required', Rule::in(['savings', 'current'])],
            'balance' => ['required', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'opened_at' => ['required', 'date'],
        ]);

        $id = DB::table('accounts')->insertGetId([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'เพิ่มข้อมูลบัญชีแล้ว'], 201);
    }

    public function updateAccount(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'account_number' => ['required', 'string', 'max:20', Rule::unique('accounts', 'account_number')->ignore($id)],
            'account_type' => ['required', Rule::in(['savings', 'current'])],
            'balance' => ['required', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'opened_at' => ['required', 'date'],
        ]);

        $updated = DB::table('accounts')->where('id', $id)->update([
            ...$data,
            'updated_at' => now(),
        ]);

        abort_if($updated === 0 && ! DB::table('accounts')->where('id', $id)->exists(), 404);

        return response()->json(['message' => 'บันทึกข้อมูลบัญชีแล้ว']);
    }

    public function deleteAccount(int $id): JsonResponse
    {
        $deleted = DB::table('accounts')->where('id', $id)->delete();

        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'ลบบัญชีและประวัติธุรกรรมที่เกี่ยวข้องแล้ว']);
    }

    public function storeTransaction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'transaction_type' => ['required', Rule::in(['deposit', 'withdrawal'])],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
        ]);

        $id = DB::transaction(function () use ($data): int {
            $account = DB::table('accounts')
                ->where('id', $data['account_id'])
                ->lockForUpdate()
                ->first();

            abort_if(! $account, 404);

            if ($account->status !== 'active') {
                throw ValidationException::withMessages([
                    'account_id' => 'ไม่สามารถบันทึกรายการในบัญชีที่ไม่ใช้งานได้',
                ]);
            }

            $impact = $this->impact($data['transaction_type'], $data['amount']);
            $balance = $this->checkedBalance($account->balance, $impact);

            DB::table('accounts')->where('id', $account->id)->update([
                'balance' => $balance,
                'updated_at' => now(),
            ]);

            return DB::table('transactions')->insertGetId([
                'account_id' => $account->id,
                'transaction_code' => 'TXN-'.now()->format('Ymd').'-'.Str::upper(Str::random(12)),
                'transaction_type' => $data['transaction_type'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_date' => Carbon::parse($data['transaction_date'])->format('Y-m-d H:i:s'),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['id' => $id, 'message' => 'เพิ่มรายการธุรกรรมแล้ว'], 201);
    }

    public function updateTransaction(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'transaction_type' => ['required', Rule::in(['deposit', 'withdrawal'])],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($data, $id): void {
            $transaction = DB::table('transactions')
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            $accountIds = collect([$transaction->account_id, $data['account_id']])
                ->unique()
                ->sort()
                ->values();
            $lockedAccounts = DB::table('accounts')
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $deltas = [];

            if ($transaction->status === 'completed') {
                $deltas[$transaction->account_id] = -$this->impact(
                    $transaction->transaction_type,
                    $transaction->amount
                );
            }

            $newImpact = $transaction->status === 'completed'
                ? $this->impact($data['transaction_type'], $data['amount'])
                : 0;
            $deltas[$data['account_id']] = ($deltas[$data['account_id']] ?? 0) + $newImpact;

            foreach ($deltas as $accountId => $delta) {
                $account = $lockedAccounts->get($accountId);

                if (! $account) {
                    abort(404);
                }

                if (
                    (int) $data['account_id'] !== (int) $transaction->account_id
                    && (int) $accountId === (int) $data['account_id']
                    && $account->status !== 'active'
                ) {
                    throw ValidationException::withMessages([
                        'account_id' => 'ไม่สามารถบันทึกรายการในบัญชีที่ไม่ใช้งานได้',
                    ]);
                }

                $balance = $this->checkedBalance($account->balance, $delta);
                DB::table('accounts')->where('id', $accountId)->update([
                    'balance' => $balance,
                    'updated_at' => now(),
                ]);
            }

            DB::table('transactions')->where('id', $id)->update([
                'account_id' => $data['account_id'],
                'transaction_type' => $data['transaction_type'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'transaction_date' => Carbon::parse($data['transaction_date'])->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['message' => 'บันทึกข้อมูลธุรกรรมแล้ว']);
    }

    public function deleteTransaction(int $id): JsonResponse
    {
        DB::transaction(function () use ($id): void {
            $transaction = DB::table('transactions')
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'completed') {
                $account = DB::table('accounts')
                    ->where('id', $transaction->account_id)
                    ->lockForUpdate()
                    ->first();

                abort_if(! $account, 404);

                $balance = $this->checkedBalance(
                    $account->balance,
                    -$this->impact($transaction->transaction_type, $transaction->amount)
                );

                DB::table('accounts')->where('id', $account->id)->update([
                    'balance' => $balance,
                    'updated_at' => now(),
                ]);
            }

            DB::table('transactions')->where('id', $id)->delete();
        });

        return response()->json(['message' => 'ลบรายการธุรกรรมและปรับยอดบัญชีแล้ว']);
    }

    private function impact(string $type, string|int|float $amount): int
    {
        $amountInCents = $this->toCents($amount);

        return $type === 'deposit' ? $amountInCents : -$amountInCents;
    }

    private function checkedBalance(string|int|float $balance, int $delta): string
    {
        $balanceInCents = $this->toCents($balance) + $delta;

        if ($balanceInCents < 0) {
            throw ValidationException::withMessages([
                'amount' => 'ยอดเงินคงเหลือไม่เพียงพอสำหรับรายการถอนนี้',
            ]);
        }

        if ($balanceInCents > 999999999999) {
            throw ValidationException::withMessages([
                'amount' => 'ยอดคงเหลือเกินขีดจำกัดของบัญชี',
            ]);
        }

        return sprintf('%d.%02d', intdiv($balanceInCents, 100), $balanceInCents % 100);
    }

    private function toCents(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
