<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('color', 7)->default('#6366f1'); // hex color
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['workspace_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
