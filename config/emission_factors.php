<?php

/**
 * Carbon Emission Factors
 * Source: ADEME (Agence de l'Environnement et de la Maîtrise de l'Énergie) - France
 * Updated: 2025
 *
 * Factors in kg CO2eq per liter of fuel or kWh of electricity
 */

return [
    // Fuel Types (kg CO2eq per liter)
    'fuel_types' => [
        'diesel' => 2.67,           // Diesel
        'gasoline' => 2.28,          // Essence/Gasoline
        'lpg' => 1.64,               // GPL (Gaz de Pétrole Liquéfié)
        'cng' => 2.35,               // GNV (Gaz Naturel Véhicule)
        'e85' => 1.11,               // Bioéthanol E85
        'electric' => 0.052,         // Électricité (par kWh) - mix électrique français
        'hybrid_diesel' => 1.80,     // Hybride diesel
        'hybrid_gasoline' => 1.50,   // Hybride essence
        'hydrogen' => 0.00,          // Hydrogène (si production verte)
    ],

    // Vehicle Categories - Average emissions (kg CO2 per km)
    'vehicle_categories' => [
        'small_car' => 0.104,        // Petite voiture (< 1.4L)
        'medium_car' => 0.140,       // Voiture moyenne (1.4-2.0L)
        'large_car' => 0.209,        // Grande voiture (> 2.0L)
        'suv' => 0.192,              // SUV
        'van' => 0.257,              // Utilitaire léger
        'truck_small' => 0.850,      // Camion < 7.5T
        'truck_medium' => 1.200,     // Camion 7.5-19T
        'truck_large' => 1.800,      // Camion > 19T
        'bus' => 1.300,              // Bus/Autocar
        'motorcycle' => 0.084,       // Moto
    ],

    // Compensation Costs (€ per ton CO2)
    'compensation' => [
        'price_per_ton' => 25.00,    // Prix moyen compensation carbone
        'projects' => [
            'forest' => [
                'name' => 'Reforestation',
                'description' => 'Plantation d\'arbres',
                'cost_per_ton' => 20.00
            ],
            'renewable' => [
                'name' => 'Énergies Renouvelables',
                'description' => 'Projets éoliens et solaires',
                'cost_per_ton' => 25.00
            ],
            'efficiency' => [
                'name' => 'Efficacité Énergétique',
                'description' => 'Amélioration efficacité énergétique',
                'cost_per_ton' => 30.00
            ]
        ]
    ],

    // National Averages (France) for comparison
    'benchmarks' => [
        'avg_car_km_year' => 13000,  // km moyen par an
        'avg_car_co2_year' => 1.82,  // tonnes CO2 par an
        'avg_truck_km_year' => 50000,
        'avg_truck_co2_year' => 42.5,
    ],

    // Eco-driving tips impact (% reduction)
    'eco_driving_tips' => [
        'smooth_acceleration' => [
            'name' => 'Accélération progressive',
            'reduction_percent' => 10,
            'description' => 'Éviter les accélérations brutales'
        ],
        'anticipation' => [
            'name' => 'Anticipation du trafic',
            'reduction_percent' => 15,
            'description' => 'Anticiper pour éviter freinages brusques'
        ],
        'speed_limit' => [
            'name' => 'Respect des limitations',
            'reduction_percent' => 12,
            'description' => 'Réduire vitesse autoroute de 130 à 120 km/h'
        ],
        'tire_pressure' => [
            'name' => 'Pression des pneus',
            'reduction_percent' => 3,
            'description' => 'Maintenir pression optimale'
        ],
        'air_conditioning' => [
            'name' => 'Climatisation raisonnée',
            'reduction_percent' => 7,
            'description' => 'Utilisation modérée de la climatisation'
        ],
        'maintenance' => [
            'name' => 'Entretien régulier',
            'reduction_percent' => 5,
            'description' => 'Maintenance préventive régulière'
        ]
    ],

    // CSR Goals (Corporate Social Responsibility)
    'csr_goals' => [
        'eu_2030' => -55,   // Réduction -55% vs 1990 (objectif UE)
        'eu_2050' => -100,  // Neutralité carbone 2050
        'france_2030' => -40, // Objectif France
    ]
];
