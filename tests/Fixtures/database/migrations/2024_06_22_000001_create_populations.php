<?php

namespace Tests\Fixtures;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('populations', static function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('populator');
            $table->string('bundle');
            $table->string('key');
            $table->morphs('populatable');
            //            $table->unique(['populatable_type', 'populatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('populations');
    }
};
