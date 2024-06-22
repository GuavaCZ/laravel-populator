<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete()
            ;
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
            ;
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
