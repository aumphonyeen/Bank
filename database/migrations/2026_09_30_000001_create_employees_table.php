<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->string('name', 120);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('position', 80)->default('เจ้าหน้าที่ธนาคาร');
            $table->string('role', 30)->default('staff');
            $table->string('status', 20)->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
