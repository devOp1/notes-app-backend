<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Benutzerbezug
            $table->timestamps(); // Erstellt created_at und updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
