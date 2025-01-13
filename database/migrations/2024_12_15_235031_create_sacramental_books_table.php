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
        Schema::create('sacrament_books', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->integer('folios_number');
            $table->char('year_start', 4);
            $table->char('year_finish', 4)->nullable();
            $table->integer('acts_per_folio')->default(3);
            $table->enum('sacrament_type', [1, 2, 4]);
            $table->unique(['number', 'sacrament_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacrament_books');
    }
};
