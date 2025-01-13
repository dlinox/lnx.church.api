<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('parishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->char('country', 3)->default('UNK');
            $table->char('location', 6)->default('000000');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parishes');
    }
};
