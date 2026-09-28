<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->decimal('price', 10, 2);
            $table->integer('mileage');
            $table->string('fuel_type');
            $table->enum('transmission', ['manual', 'automatic']);
            $table->string('color');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('contact_phone');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};






































//zaid shiekh akrim
//tim ibrahem