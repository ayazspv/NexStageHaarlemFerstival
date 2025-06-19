<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('festivals', function (Blueprint $table) {
            if (!Schema::hasColumn('festivals', 'price')) {
                $table->decimal('price', 8, 2)->default(0.00)->after('ticket_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('festivals', function (Blueprint $table) {
            if (Schema::hasColumn('festivals', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};