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
        Schema::create('ministers', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['01', '04', '06', '07', '11', '00'])->default('01');
            $table->string('document_number', 15)->nullable();
            $table->string('name');
            $table->string('paternal_last_name')->nullable();
            $table->string('maternal_last_name')->nullable();
            $table->enum('gender', [1, 2, 0])->default(0);
            $table->date('birth_date')->nullable();
            $table->char('birth_country', 3)->nullable();
            $table->char('birth_location', 6)->default('000000');
            $table->string('birth_location_detail')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('email')->nullable();
            $table->char('type', 3);
            $table->boolean('status')->default(true);
            $table->unique(['document_type', 'document_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ministers');
    }
};
