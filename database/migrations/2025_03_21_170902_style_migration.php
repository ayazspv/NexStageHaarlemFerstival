<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('styles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_id')->nullable();
            $table->string('name');
            $table->text('content');
            $table->timestamps();

            $table->foreign('cms_id')->references('id')->on('cms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('styles');
    }
};
