<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_attachments', function (Blueprint $table) {
            $table->foreignId('request_activity_id')
                ->nullable()
                ->after('service_request_id')
                ->constrained('request_activities')
                ->nullOnDelete();

            $table->index(['request_activity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('request_attachments', function (Blueprint $table) {
            $table->dropIndex(['request_activity_id', 'created_at']);
            $table->dropConstrainedForeignId('request_activity_id');
        });
    }
};
