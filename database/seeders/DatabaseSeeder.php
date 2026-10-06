<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            ['กิตติพงษ์', 'วัฒนากุล', 'kittipong.w', '1042'],
            ['ณัฐธิดา', 'ศรีสุข', 'nattida.s', '2186'],
            ['ปวีณา', 'จันทร์เพ็ญ', 'paweena.j', '3075'],
            ['ธนกร', 'ภูมิรักษ์', 'thanakorn.p', '4193'],
            ['ศิริพร', 'แสงทอง', 'siriporn.s', '5271'],
            ['ภาคิน', 'วงศ์ประเสริฐ', 'phakhin.w', '6384'],
            ['พิมพ์ชนก', 'นาคเจริญ', 'phimchanok.n', '7462'],
            ['อนุชา', 'คงมั่น', 'anucha.k', '8529'],
            ['วรัญญา', 'บุญเรือง', 'waranya.b', '9641'],
            ['ชยพล', 'เกษมสุข', 'chayaphon.k', '0753'],
            ['กมลวรรณ', 'รัตนชัย', 'kamonwan.r', '1860'],
            ['จักรพันธ์', 'สุวรรณดี', 'jakkaphan.s', '2948'],
            ['อรทัย', 'มณีรัตน์', 'orathai.m', '3057'],
            ['ธีรภัทร', 'สมบูรณ์', 'theeraphat.s', '4176'],
            ['มนัสวี', 'ชูศักดิ์', 'manatsawi.c', '5283'],
            ['ปกรณ์', 'ตั้งเจริญ', 'pakorn.t', '6391'],
            ['นภัสสร', 'พูนผล', 'napatsorn.p', '7405'],
            ['เอกภพ', 'อินทร์ทอง', 'ekkapop.i', '8514'],
            ['สุภาวดี', 'สายใจ', 'suphawadi.s', '9637'],
            ['รณชัย', 'คำแสน', 'ronnachai.k', '2749'],
        ];

        DB::transaction(function () use ($profiles): void {
            DB::table('customers')
                ->whereIn('customer_code', array_map(
                    static fn (int $number): string => sprintf('CUS%04d', $number),
                    range(1, count($profiles))
                ))
                ->delete();

            $now = now();
            $accountIds = [];

            foreach ($profiles as $index => [$firstName, $lastName, $emailPrefix, $phoneSuffix]) {
                $number = $index + 1;
                $customerId = DB::table('customers')->insertGetId([
                    'customer_code' => sprintf('CUS%04d', $number),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => "{$emailPrefix}@example.test",
                    'phone' => "081-XXX-{$phoneSuffix}",
                    'birth_date' => $now->copy()
                        ->subYears(22 + (($number * 7) % 35))
                        ->subDays(($number * 13) % 365)
                        ->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $openedAt = $now->copy()->startOfDay()->subMonthsNoOverflow(3 + (($number * 7) % 100));
                $openingDeposit = 20000 + (2750 * $number);
                $salaryDeposit = 3200 + (($number * 2375) % 28000);
                $withdrawal = 450 + (($number * 791) % 10500);
                $accountIds[$number] = DB::table('accounts')->insertGetId([
                    'customer_id' => $customerId,
                    'account_number' => sprintf(
                        '102-%d-%05d-%d',
                        ($number % 9) + 1,
                        12000 + (379 * $number),
                        ($number * 7) % 10
                    ),
                    'account_type' => $number % 5 === 0 ? 'current' : 'savings',
                    'balance' => $openingDeposit + $salaryDeposit - $withdrawal,
                    'status' => 'active',
                    'opened_at' => $openedAt->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $sequence = 1;

            foreach ($accountIds as $number => $accountId) {
                $openedAt = $now->copy()->startOfDay()->subMonthsNoOverflow(3 + (($number * 7) % 100));
                $transactions = [
                    [
                        'type' => 'deposit',
                        'amount' => 20000 + (2750 * $number),
                        'description' => 'เปิดบัญชี • ฝากเงินสด',
                        'date' => $openedAt,
                    ],
                    [
                        'type' => 'deposit',
                        'amount' => 3200 + (($number * 2375) % 28000),
                        'description' => 'เงินเดือน • โอนเข้า',
                        'date' => $now->copy()->subDays(1 + ($number % 20)),
                    ],
                    [
                        'type' => 'withdrawal',
                        'amount' => 450 + (($number * 791) % 10500),
                        'description' => ['ชำระค่าสินค้า • Mobile Banking', 'ถอนเงินสด • ATM', 'ชำระค่าสาธารณูปโภค • Mobile Banking'][$number % 3],
                        'date' => $now->copy()->subDays($number % 6),
                    ],
                ];

                foreach ($transactions as $transaction) {
                    DB::table('transactions')->insert([
                        'account_id' => $accountId,
                        'transaction_code' => sprintf('TXN-%s-%06d', $now->format('Ymd'), $sequence),
                        'transaction_type' => $transaction['type'],
                        'amount' => $transaction['amount'],
                        'description' => $transaction['description'],
                        'transaction_date' => $transaction['date'],
                        'status' => 'completed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $sequence++;
                }
            }
        });

        $this->call(EmployeeSeeder::class);
    }
}
