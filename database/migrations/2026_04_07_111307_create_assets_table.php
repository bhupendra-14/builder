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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('asset_type');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('folder')->default('uncategorized');
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('asset_type');
            $table->index('folder');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
