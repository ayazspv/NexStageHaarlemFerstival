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
            $table->float('rate');
            $table->text('cta_text');
            $table->text('subheader_1')->nullable();
            $table->text('subheader_2')->nullable();

            $table->boolean('accessibility')->default(false);
            $table->boolean('vegan')->default(false);
            $table->boolean('gluten_free')->default(false);
            $table->boolean('halal')->default(false);

            $table->decimal('adult_price', 8, 2);
            $table->decimal('children_price', 8, 2);

            $table->text('location');
            $table->string('contact_number');

            $table->string('picture_1');
            $table->string('picture_2')->nullable();
            $table->string('picture_3')->nullable();

            $table->time('session_1_time');
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
