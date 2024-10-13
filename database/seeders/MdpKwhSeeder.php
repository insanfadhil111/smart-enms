<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\MdpKwh;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MdpKwhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* Inject Data Bulanan PLN */
        $path = database_path('seeders/kwh_inject.sql');
        DB::unprepared(File::get($path));

        /* Seeding Dummy Data */
        $startDate = Carbon::now()->subDays(3);
        $totalDataPoints = 500; // Adjust this number as needed

        for ($i = 0; $i < $totalDataPoints; $i++) {
            MdpKwh::factory()->create([
                'created_at' => $startDate->copy(),
                'updated_at' => $startDate->copy(),
            ]);
            $startDate->addMinutes(5); // Interval
        }
    }
}
