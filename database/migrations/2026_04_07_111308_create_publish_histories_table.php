<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publish_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('published_by')->nullable();
            $table->string('environment'); // dark, live
            $table->json('snapshot');
            $table->text('release_notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('environment');
            $table->index('created_at');
            $table->index('published_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_histories');
    }
};
