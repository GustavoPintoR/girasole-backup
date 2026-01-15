<?php

namespace Database\Seeders;

use App\Actions\ImportRegionsAction;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ImportRegionsAction::run();

        // $regions = [
        //     ['id' => 1,  'name' => 'Sicilia',              'code' => 'SIC'],
        //     ['id' => 2,  'name' => 'Piemonte',             'code' => 'PIE'],
        //     ['id' => 3,  'name' => 'Marche',               'code' => 'MAR'],
        //     ['id' => 4,  'name' => "Valle d'Aosta",        'code' => 'VDA'],
        //     ['id' => 5,  'name' => 'Toscana',              'code' => 'TOS'],
        //     ['id' => 6,  'name' => 'Campania',             'code' => 'CAM'],
        //     ['id' => 7,  'name' => 'Puglia',               'code' => 'PUG'],
        //     ['id' => 8,  'name' => 'Veneto',               'code' => 'VEN'],
        //     ['id' => 9,  'name' => 'Lombardia',            'code' => 'LOM'],
        //     ['id' => 10, 'name' => 'Emilia-Romagna',       'code' => 'EMR'],
        //     ['id' => 11, 'name' => 'Trentino-Alto Adige',  'code' => 'TAA'],
        //     ['id' => 12, 'name' => 'Sardegna',             'code' => 'SAR'],
        //     ['id' => 13, 'name' => 'Molise',               'code' => 'MOL'],
        //     ['id' => 14, 'name' => 'Calabria',             'code' => 'CAL'],
        //     ['id' => 15, 'name' => 'Lazio',                'code' => 'LAZ'],
        //     ['id' => 16, 'name' => 'Liguria',              'code' => 'LIG'], // If separated from Piemonte
        //     ['id' => 17, 'name' => 'Umbria',               'code' => 'UMB'],
        //     ['id' => 18, 'name' => 'Abruzzo',              'code' => 'ABR'],
        //     ['id' => 19, 'name' => 'Friuli Venezia Giulia', 'code' => 'FVG'],
        //     ['id' => 20, 'name' => 'Basilicata',           'code' => 'BAS'],
        // ];

        // foreach ($regions as $region) {
        //     Region::create($region);
        // }
    }
}
