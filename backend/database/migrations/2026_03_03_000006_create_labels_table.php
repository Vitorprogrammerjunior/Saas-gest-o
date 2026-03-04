<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#ef4444');
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('workspace_id');
        });

        Schema::create('card_label', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->primary(['card_id', 'label_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_label');
        Schema::dropIfExists('labels');
    }
};
