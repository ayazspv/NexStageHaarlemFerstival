<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jazz_festivals', function (Blueprint $table) {
            $table->integer('performance_day')->nullable()->after('performance_datetime');
        });
    }

    public function down()
    {
        Schema::table('jazz_festivals', function (Blueprint $table) {
            $table->dropColumn('performance_day');
        });
    }
};