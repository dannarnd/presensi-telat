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
        Schema::table('counseling_logs', function (Blueprint $table) {
            $table->unsignedInteger('delay_count')->nullable()->after('counselor_name');
            $table->string('stage')->nullable()->after('delay_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_logs', function (Blueprint $table) {
            $table->dropColumn(['delay_count', 'stage']);
        });
    }
};
