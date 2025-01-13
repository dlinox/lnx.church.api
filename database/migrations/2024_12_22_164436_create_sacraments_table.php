<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sacraments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description')->nullable();
            $table->enum('type', [1, 2, 4]); //[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
            $table->boolean('is_external')->default(false);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('parish_id')->default(1);
            $table->unsignedBigInteger('minister_id')->nullable();
            $table->foreign('minister_id')->references('id')->on('ministers');
            $table->foreign('parish_id')->references('id')->on('parishes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacraments');
    }
};
