<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('rate')->default(3);
            $table->string('cta_text')->default('Click the button');
            $table->string('subheader_1')->nullable();
            $table->string('subheader_2')->nullable();
            $table->text('description')->nullable();
            $table->integer('seats')->default(10);
            $table->boolean('accessibility')->default(false);
            $table->boolean('vegan')->default(false);
            $table->boolean('gluten_free')->default(false);
            $table->boolean('halal')->default(false);
            $table->decimal('adult_price', 8, 2)->default(10);
            $table->decimal('children_price', 8, 2)->default(10);
            $table->string('location')->default('Amsterdam');
            $table->string('contact_number')->default('+3166884558');
            $table->string('picture_1')->default('default.jpg');
            $table->string('picture_2')->nullable();
            $table->string('picture_3')->nullable();
            $table->string('picture_4')->nullable();
            $table->time('session_1_time')->default('14:00:00');
            $table->time('session_2_time')->nullable();
            $table->time('session_3_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
