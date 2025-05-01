<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $plants = [
            [
                'scientific_name' => 'Ocimum basilicum',
                'common_name' => 'Basil',
                'description' => 'A fragrant herb used in cooking.',
                'water_requirements' => 'Moderate watering, keep soil moist.',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'Low',
                'growth_min_months' => 1,
                'growth_max_months' => 3,
                'image' => 'images/basil.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Solanum lycopersicum',
                'common_name' => 'Tomato',
                'description' => 'Widely cultivated for its edible fruits.',
                'water_requirements' => 'Regular watering',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, rich in organic matter',
                'co2_absorption' => 'High',
                'cancer_risk_impact' => 'Low',
                'growth_min_months' => 2,
                'growth_max_months' => 4,
                'image' => 'images/tomato.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Daucus carota',
                'common_name' => 'Carrot',
                'description' => 'Root vegetable, usually orange in color.',
                'water_requirements' => 'Moderate',
                'sun_requirements' => 'Full sun to partial shade',
                'suitable_soil_types' => 'Sandy, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'None',
                'growth_min_months' => 2,
                'growth_max_months' => 3,
                'image' => 'images/carrot.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Mentha',
                'common_name' => 'Mint',
                'description' => 'Aromatic herb often used in tea.',
                'water_requirements' => 'High',
                'sun_requirements' => 'Partial sun',
                'suitable_soil_types' => 'Moist, rich soil',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'None',
                'growth_min_months' => 1,
                'growth_max_months' => 2,
                'image' => 'images/mint.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Allium cepa',
                'common_name' => 'Onion',
                'description' => 'Popular root vegetable used in cooking.',
                'water_requirements' => 'Moderate',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'Low',
                'growth_min_months' => 3,
                'growth_max_months' => 5,
                'image' => 'images/onion.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Lactuca sativa',
                'common_name' => 'Lettuce',
                'description' => 'Leafy green vegetable used in salads.',
                'water_requirements' => 'High',
                'sun_requirements' => 'Partial sun',
                'suitable_soil_types' => 'Loamy, moist',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'None',
                'growth_min_months' => 1,
                'growth_max_months' => 2,
                'image' => 'images/lettuce.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Zea mays',
                'common_name' => 'Corn',
                'description' => 'Tall cereal plant grown for its kernels.',
                'water_requirements' => 'High',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, rich soil',
                'co2_absorption' => 'High',
                'cancer_risk_impact' => 'Low',
                'growth_min_months' => 3,
                'growth_max_months' => 5,
                'image' => 'images/corn.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Pisum sativum',
                'common_name' => 'Pea',
                'description' => 'Small spherical seeds eaten as vegetables.',
                'water_requirements' => 'Moderate',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'None',
                'growth_min_months' => 2,
                'growth_max_months' => 3,
                'image' => 'images/pea.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Capsicum annuum',
                'common_name' => 'Bell Pepper',
                'description' => 'Sweet pepper used in many cuisines.',
                'water_requirements' => 'Moderate to high',
                'sun_requirements' => 'Full sun',
                'suitable_soil_types' => 'Loamy, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'Low',
                'growth_min_months' => 2,
                'growth_max_months' => 4,
                'image' => 'images/bell_pepper.jpg',  // إضافة الصورة
            ],
            [
                'scientific_name' => 'Spinacia oleracea',
                'common_name' => 'Spinach',
                'description' => 'Leafy green vegetable rich in iron.',
                'water_requirements' => 'Regular watering',
                'sun_requirements' => 'Partial shade',
                'suitable_soil_types' => 'Moist, well-drained',
                'co2_absorption' => 'Medium',
                'cancer_risk_impact' => 'None',
                'growth_min_months' => 1,
                'growth_max_months' => 2,
                'image' => 'images/spinach.jpg',  // إضافة الصورة
            ],
        ];

        foreach ($plants as $plant) {
            Plant::create($plant);
        }
    }
}
