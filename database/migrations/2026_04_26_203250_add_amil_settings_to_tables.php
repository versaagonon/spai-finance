<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add amil_percentage to programs
        Schema::table('programs', function (Blueprint $table) {
            $table->decimal('amil_percentage', 5, 2)->default(0)->after('description');
        });

        // Add amil_percentage and toggle to projects
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('amil_percentage', 5, 2)->default(0)->after('description');
            $table->boolean('use_custom_amil')->default(false)->after('amil_percentage');
        });

        // Create app_settings table
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        \Illuminate\Support\Facades\DB::table('app_settings')->insert([
            ['key' => 'global_amil_percentage', 'value' => '0'],
            ['key' => 'enable_amil_auto_calculation', 'value' => '1'],
        ]);
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('amil_percentage');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['amil_percentage', 'use_custom_amil']);
        });

        Schema::dropIfExists('app_settings');
    }
};
