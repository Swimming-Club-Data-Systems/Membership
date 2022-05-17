<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant1 = \App\Models\Central\Tenant::firstOrCreate(['id' => 'f9bd4843-3113-4e46-ac44-9be515826758']);
        $tenant1->domains()->firstOrCreate(['domain' => 'testclub.localhost']);
    }
}
