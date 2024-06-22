<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id');
            $table->morphs('taggable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
