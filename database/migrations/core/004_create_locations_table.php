<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->char('id', 6)->primary();
            $table->string('district', 80);
            $table->string('province', 80);
            $table->string('department', 80);
            $table->index(['district', 'province', 'department'], 'IDX_location_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
