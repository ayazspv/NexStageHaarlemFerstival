<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jazz_festivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->onDelete('cascade');
            $table->string('band_name');
            $table->dateTime('performance_datetime');
            $table->decimal('ticket_price', 8, 2);
            $table->text('band_description');
            $table->text('band_details');
            $table->string('band_image')->nullable();
            $table->string('second_image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jazz_festivals');
    }
};
