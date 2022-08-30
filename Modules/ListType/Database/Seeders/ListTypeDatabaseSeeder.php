<?php

namespace Modules\ListType\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\ListType\Entities\ListType;

class ListTypeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard(); 
        $ListTypes = [
            [
                'name' => 'Buy',
            ],
            [
                'name' => 'Sell'
            ],
            [
                'name' => 'Rent'
            ],
            [
                'name' => 'To-Let'
            ],
            [
                'name' => 'Job'
            ],
        ];

        foreach ($ListTypes as $ListType) {
            ListType::create($ListType);
        }
        // $this->call("OthersTableSeeder");
    }
}
