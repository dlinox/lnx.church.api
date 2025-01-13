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
        Schema::create('sacrament_records', function (Blueprint $table) {
            $table->id();
            $table->integer('folio_number')->nullable();
            $table->integer('act_number')->nullable();
            $table->text('observation')->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->boolean('canonical')->default(true);
            $table->boolean('status')->default(true); //anulado  o activo    
            $table->unsignedBigInteger('sacrament_book_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('sacrament_id')->constrained();
            $table->foreign('sacrament_book_id')->references('id')->on('sacrament_books');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['folio_number', 'act_number'], 'IDX_FOLIO_ACT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacrament_records');
    }
};
