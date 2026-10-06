<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_code', 30)->unique();
            $table->string('transaction_type', 20);
            $table->decimal('amount', 12, 2);
            $table->string('description', 255)->nullable();
            $table->dateTime('transaction_date');
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index(['account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
