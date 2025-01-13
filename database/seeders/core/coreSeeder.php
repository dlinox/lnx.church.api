<?php

namespace Database\Seeders\core;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class coreSeeder extends Seeder
{

    public function run(): void
    {
        $countriesInserts = file_get_contents(database_path('sql/core/countriesInserts.sql'));
        DB::unprepared($countriesInserts);

        $locationsInserts = file_get_contents(database_path('sql/core/locationsInserts.sql'));
        DB::unprepared($locationsInserts);

        $permissionsInserts = file_get_contents(database_path('sql/core/permissionsInserts.sql'));
        DB::unprepared($permissionsInserts);
    }
}
