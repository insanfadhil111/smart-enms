<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MppData;
use Carbon\Carbon;

class MppDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Start date: 3 days ago
        $startDate = Carbon::now()->subDays(2);

        // Number of data points
        $totalDataPoints = 500; // Adjust this number as needed

        for ($i = 0; $i < $totalDataPoints; $i++) {
            MppData::factory()->create([
                'created_at' => $startDate->copy(),
                'updated_at' => $startDate->copy(),
            ]);

            // Increment the date by 5 minutes for each iteration
            $startDate->addMinutes(5);
        }
    }
}
