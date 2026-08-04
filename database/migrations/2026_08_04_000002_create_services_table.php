<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('name_fil');
            $table->text('description_en');
            $table->text('description_fil');
            $table->text('eligibility_en');
            $table->text('eligibility_fil');
            $table->unsignedInteger('fee_centavos')->default(0);
            $table->string('processing_time_en');
            $table->string('processing_time_fil');
            $table->string('office_hours_en');
            $table->string('office_hours_fil');
            $table->json('procedure_steps_en');
            $table->json('procedure_steps_fil');
            $table->boolean('appointment_required')->default(false)->index();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'archived_at']);
        });

        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_fil');
            $table->text('details_en')->nullable();
            $table->text('details_fil')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requirements');
        Schema::dropIfExists('services');
    }
};
