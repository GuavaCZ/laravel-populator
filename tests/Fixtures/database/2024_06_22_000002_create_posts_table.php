<?php

namespace Tests\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('posts', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->text('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
