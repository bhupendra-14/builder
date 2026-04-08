<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publish_histories', function (Blueprint $table) {
            // pending = scheduled but not yet executed
            // completed = executed successfully
            // failed = executed but threw an error
            $table->string('status')->default('completed')->after('environment');
            $table->text('error')->nullable()->after('release_notes');
            $table->timestamp('executed_at')->nullable()->after('scheduled_at');

            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('publish_histories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn(['status', 'error', 'executed_at']);
        });
    }
};
