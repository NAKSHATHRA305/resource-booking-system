<?php

namespace App\Services;

class PricingService
{
    // SQL expression for the suggested nightly rate of inventory row i joined to room type rt.
    // Rules: occupancy tier x weekend surcharge, rounded to the nearest 100.
    public static function expr(): string
    {
        return "ROUND(rt.base_rate
            * CASE WHEN i.booked / i.total >= 0.9 THEN 1.30
                   WHEN i.booked / i.total >= 0.7 THEN 1.15
                   WHEN i.booked / i.total >= 0.4 THEN 1.00
                   ELSE 0.90 END
            * CASE WHEN DAYOFWEEK(i.date) IN (6, 7) THEN 1.10 ELSE 1.00 END, -2)";
    }
}