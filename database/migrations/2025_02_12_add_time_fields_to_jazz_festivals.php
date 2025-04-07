<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jazz_festivals', function (Blueprint $table) {
            $table->string('start_time')->nullable()->after('performance_datetime');
            $table->string('end_time')->nullable()->after('start_time');
        });
    }

    public function down()
    {
        Schema::table('jazz_festivals', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};