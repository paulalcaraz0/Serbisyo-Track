<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_settings', function (Blueprint $table) {
            $table->id();
            $table->string('office_name_en', 150);
            $table->string('office_name_fil', 150);
            $table->string('contact_email', 150);
            $table->string('contact_phone', 50);
            $table->string('address_en', 255);
            $table->string('address_fil', 255);
            $table->unsignedSmallInteger('retention_days')->default(730);
            $table->timestamps();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->string('subject_type', 32)->index();
            $table->string('subject_identifier', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['action', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['subject_type', 'subject_identifier']);
        });

        DB::table('office_settings')->insert([
            'id' => 1,
            'office_name_en' => 'Barangay Haraya Service Desk',
            'office_name_fil' => 'Tanggapan ng Serbisyo ng Barangay Haraya',
            'contact_email' => 'help@barangayharaya.test',
            'contact_phone' => '(02) 8123 4567',
            'address_en' => 'Fictional Municipal Hall, Barangay Haraya',
            'address_fil' => 'Kathang-isip na Munisipyo, Barangay Haraya',
            'retention_days' => 730,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('office_settings');
    }
};
