<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('tickets', 'is_used')) {
                $table->boolean('is_used')->default(false);
            }

            if (!Schema::hasColumn('tickets', 'used_at')) {
                $table->timestamp('used_at')->nullable();
            }

            if (!Schema::hasColumn('tickets', 'redeemed_by')) {
                $table->string('redeemed_by')->nullable(); // Staff member who redeemed it
            }
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('tickets', 'is_used')) {
                $table->dropColumn('is_used');
            }

            if (Schema::hasColumn('tickets', 'used_at')) {
                $table->dropColumn('used_at');
            }

            if (Schema::hasColumn('tickets', 'redeemed_by')) {
                $table->dropColumn('redeemed_by');
            }
        });
    }
};
