<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('public_reference', 24)->unique();
            $table->string('tracking_pin_hash');
            $table->string('status', 32)->index();
            $table->string('locale', 5)->default('en');
            $table->text('resident_name');
            $table->text('contact_email')->nullable();
            $table->text('contact_phone')->nullable();
            $table->string('preferred_contact', 12);
            $table->text('general_location')->nullable();
            $table->text('request_details');
            $table->timestamp('consented_at');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['service_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('request_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('preferred_date');
            $table->string('preferred_time_window', 16);
            $table->text('resident_note')->nullable();
            $table->string('status', 32)->index();
            $table->timestamp('confirmed_start_at')->nullable();
            $table->timestamps();
        });

        Schema::create('request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('disk', 32)->default('local');
            $table->string('path');
            $table->text('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index(['service_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_attachments');
        Schema::dropIfExists('request_appointments');
        Schema::dropIfExists('service_requests');
    }
};
