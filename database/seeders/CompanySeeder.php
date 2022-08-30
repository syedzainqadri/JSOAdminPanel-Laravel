<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = [
            [
                'name' => 'R Systems International Ltd.',
            ],
            [
                'name' => 'facebook',
            ],
            [
                'name' => 'system ',
            ],
            [
                'name' => 'bigcompany',
            ]
        ];

        foreach ($pages as $page) {
            Company::create($page);
        }
    }
}
