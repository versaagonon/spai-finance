<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('donor_name');
            $table->string('bank_receiver')->nullable(); // Bank Penerima
            $table->decimal('amount', 15, 2); // Uang Masuk
            $table->decimal('amil_percentage', 5, 2)->default(0); // Persentase Hak Amil
            $table->decimal('amil_amount', 15, 2)->default(0); // Nilai Hak Amil
            $table->decimal('managed_fund', 15, 2)->default(0); // Dana Kelola (Net)
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('program')->nullable(); // Program specific info if needed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
