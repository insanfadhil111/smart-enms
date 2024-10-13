<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\GoodweData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GoodweDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Start date: 3 Days ago
        $startDate = Carbon::now()->subDays(2);

        // Number of data points
        $totalDataPoints = 500; // Adjust this number as needed

        for ($i = 0; $i < $totalDataPoints; $i++) {
            GoodweData::factory()->create([
                'created_at' => $startDate->copy(),
                'updated_at' => $startDate->copy(),
            ]);

            // Increment the date by 5 minutes for each iteration
            $startDate->addMinutes(5);
        }
    }
}
