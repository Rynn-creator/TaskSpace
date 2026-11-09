<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->string('category')->default('General'); // <-- Kolom Baru untuk Tag Kategori
        $table->enum('priority', ['Low', 'Medium', 'High'])->default('Low');
        $table->date('due_date')->nullable();
        $table->boolean('is_completed')->default(false);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
