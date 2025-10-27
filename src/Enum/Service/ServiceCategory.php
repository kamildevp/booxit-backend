<?php

declare(strict_types=1);

namespace App\Enum\Service;

use App\Enum\Trait\ValuesTrait;

enum ServiceCategory: string
{
    use ValuesTrait;

    case HAIRDRESSER = 'Hairdresser';
    case BARBER = 'Barber';
    case SKINCARE_SPA = 'Skin Care & Spa';
    case NAILS = 'Nails';
    case LASHES_AND_BROWS = 'Brows & Lashes';
    case COSMETIC_TREATMENTS = 'Cosmetic Treatments';
    case WELLNESS = 'Wellness';
    case HEALTHCARE = 'Healthcare';
    case FITNESS_AND_SPORTS = 'Fitness & Sports';
    case SPORTS_FACILITIES_RENTAL = 'Sports Facilities Rental';
    case ACCOMMODATION = 'Accommodation';
    case RENTALS = 'Rentals';
    case TRAVEL = 'Travel';
    case RESTAURANTS = 'Restaurants';
    case CATERING = 'Catering';
    case CLEANING = 'Cleaning';
    case HOME_MAINTENANCE = 'Home Maintenance';
    case FACILITIES_RENTAL = 'Facilities Rental';
    case EDUCATION = 'Education';
    case AUTOMOTIVE = 'Automotive';
    case FINANCE = 'Finance';
    case LEGAL = 'Legal';
    case BUSINESS = 'Business';
    case PET_CARE = 'Pet Care';
    case OTHER = 'Other';
}