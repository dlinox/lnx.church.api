<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SacramentalApp extends Seeder
{



    public function run(): void
    {
        $people = file_get_contents(database_path('sql/inserts/people.sql'));
        DB::unprepared($people);
        $this->command->info('Tabla people inicializada con datos!');
        $parishes = file_get_contents(database_path('sql/inserts/parishes.sql'));
        DB::unprepared($parishes);
        $this->command->info('Tabla parishes inicializada con datos!');
        $ministers = file_get_contents(database_path('sql/inserts/ministers.sql'));
        DB::unprepared($ministers);
        $this->command->info('Tabla ministers inicializada con datos!');
        $sacrament_books = file_get_contents(database_path('sql/inserts/sacrament_books.sql'));
        DB::unprepared($sacrament_books);
        $this->command->info('Tabla sacrament_books inicializada con datos!');
        $sacraments = file_get_contents(database_path('sql/inserts/sacraments.sql'));
        DB::unprepared($sacraments);
        $this->command->info('Tabla sacraments inicializada con datos!');
        $sacrament_records = file_get_contents(database_path('sql/inserts/sacrament_records.sql'));
        DB::unprepared($sacrament_records);
        $this->command->info('Tabla sacrament_records inicializada con datos!');
        $sacrament_roles = file_get_contents(database_path('sql/inserts/sacrament_roles.sql'));
        DB::unprepared($sacrament_roles);
        $this->command->info('Tabla sacrament_roles inicializada con datos!');
        $family_relationships = file_get_contents(database_path('sql/inserts/family_relationships.sql'));
        DB::unprepared($family_relationships);
        $this->command->info('Tabla family_relationships inicializada con datos!');

    }
}
