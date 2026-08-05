<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('target_business_days')->default(3);
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable()->index();

            $table->index(['assigned_to', 'status']);
        });

        Schema::create('request_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 32)->index();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->text('public_message_en')->nullable();
            $table->text('public_message_fil')->nullable();
            $table->text('private_details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['service_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_activities');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['due_at']);
            $table->dropIndex(['closed_at']);
            $table->dropColumn(['assigned_to', 'assigned_at', 'due_at', 'closed_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('target_business_days');
        });
    }
};
