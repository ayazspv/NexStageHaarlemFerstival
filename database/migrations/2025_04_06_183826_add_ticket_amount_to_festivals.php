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
        Schema::table('festivals', function (Blueprint $table) {
            // Only add the ticket_amount column if it doesn't exist
            if (!Schema::hasColumn('festivals', 'ticket_amount')) {
                $table->integer('ticket_amount')->default(0)->after('isGame');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('festivals', function (Blueprint $table) {
            $table->dropColumn('ticket_amount');
        });
    }
};
