<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // If true, the section appears in the public site's top navigation
            // menu. Defaults to false so admins opt in.
            $table->boolean('show_in_nav')->default(false)->after('label');

            // Optional override for the nav button text. Falls back to the
            // section label if blank.
            $table->string('nav_label')->nullable()->after('show_in_nav');

            $table->index('show_in_nav');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex(['show_in_nav']);
            $table->dropColumn(['show_in_nav', 'nav_label']);
        });
    }
};
