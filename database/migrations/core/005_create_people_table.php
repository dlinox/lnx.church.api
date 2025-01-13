<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['01', '04', '06', '07', '11', '00'])->nullable();
            $table->string('document_number', 15)->nullable();
            $table->string('name');
            $table->string('paternal_last_name')->nullable();
            $table->string('maternal_last_name')->nullable();
            $table->enum('gender', [1, 2, 0])->default(0);
            $table->date('birth_date')->nullable();
            $table->char('birth_country', 3)->nullable();
            $table->char('birth_location', 6)->nullable();
            $table->string('birth_location_detail')->nullable();
            // $table->unique(['document_type', 'document_number']);
            $table->index(['document_type', 'document_number'], 'IDX_DOCUMENT');
            $table->index(['name', 'paternal_last_name', 'maternal_last_name'], 'IDX_PERSON_NAME');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
