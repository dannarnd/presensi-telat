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
        Schema::table('delay_logs', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deleter_name', 100)->nullable()->after('reporter_name');
            $table->text('deletion_reason')->nullable()->after('deleter_name');
            $table->string('deletion_ip', 45)->nullable()->after('deletion_reason');
            $table->text('deletion_user_agent')->nullable()->after('deletion_ip');
            $table->string('deletion_device', 150)->nullable()->after('deletion_user_agent');
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('deletion_device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delay_logs', function (Blueprint $table) {
            $table->dropForeign(['deleted_by_user_id']);
            $table->dropColumn([
                'deleted_at',
                'deleter_name',
                'deletion_reason',
                'deletion_ip',
                'deletion_user_agent',
                'deletion_device',
                'deleted_by_user_id',
            ]);
        });
    }
};
