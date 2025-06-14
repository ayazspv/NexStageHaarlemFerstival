<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->string('redeemed_by')->nullable(); // Staff member who redeemed it
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['is_used', 'used_at', 'redeemed_by']);
        });
    }
};
