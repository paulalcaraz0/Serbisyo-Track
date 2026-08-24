<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name_en', 150);
            $table->string('name_fil', 150);
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->index(['is_recurring', 'date']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('message_en', 500);
            $table->string('message_fil', 500);
            $table->string('level', 20)->default('info');
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('holidays');
    }
};
