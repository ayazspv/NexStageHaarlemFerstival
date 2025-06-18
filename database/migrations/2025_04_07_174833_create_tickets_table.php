<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('ticket_type');
            $table->json('ticket_details')->nullable();
            $table->foreignId('festival_id')->constrained()->onDelete('cascade');
            $table->string('qr_code')->unique();
            $table->integer('quantity')->default(1);
            $table->decimal('price_per_ticket', 8, 2);
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('jazz_festivals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};