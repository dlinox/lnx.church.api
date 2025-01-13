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
        Schema::create('sacrament_roles', function (Blueprint $table) {
            $table->id();
            $table->enum('role', [1, 2, 3, 4, 5, 6]);
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('sacrament_record_id');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('sacrament_record_id')->references('id')->on('sacrament_records');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacrament_roles');
    }
};


