<?php

namespace Database\Seeders;

use App\Models\AgreementType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgreementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AgreementType::create(['type'=>'Tecnica']);
        AgreementType::create(['type'=>'Ejecutiva']);
        AgreementType::create(['type'=>'PMO']);
        AgreementType::create(['type'=>'Operativa']);
    }
}
