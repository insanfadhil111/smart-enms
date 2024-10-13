<?php

namespace Database\Factories;

use App\Models\MdpKwh;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MdpKwh>
 */
class MdpKwhFactory extends Factory
{
    public function definition(): array
    {
        static $dailyTotals;
        $dailyTotals = MdpKwh::latest()->first()?->kwh_1 ?? 0;

        $increment = $this->faker->numberBetween(1, 10);
        $dailyTotals += $increment;
        return [
            'kwh_1' => $dailyTotals,
            'kwh_2' => $dailyTotals,
            'kwh_3' => $dailyTotals,
            'kwh_4' => $dailyTotals,
            'kwh_5' => $dailyTotals,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
