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
        Schema::table('lots', function (Blueprint $table) {
            $table->boolean('is_dispatched')->default(false)->after('current_stage')->index();
            $table->timestamp('dispatched_at')->nullable()->after('is_dispatched');
            $table->string('dispatch_notes', 500)->nullable()->after('dispatched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn(['is_dispatched', 'dispatched_at', 'dispatch_notes']);
        });
    }
};
