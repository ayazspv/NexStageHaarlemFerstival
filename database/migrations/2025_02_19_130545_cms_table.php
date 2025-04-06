<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*Schema::create('cms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('festival_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            // The content will store an array (encoded as JSON) of HTML strings.
            $table->json('content')->nullable();
            $table->timestamps();
            $table->string('style_path')->nullable();

            $table->foreign('festival_id')
                ->references('id')
                ->on('festivals')
                ->onDelete('cascade');

            // If a subpage is deleted because its parent is removed, you may choose to cascade.
            $table->foreign('parent_id')
                ->references('id')
                ->on('cms')
                ->onDelete('cascade');
        });*/
    }

    public function down()
    {
        //Schema::dropIfExists('cms');
    }
};
