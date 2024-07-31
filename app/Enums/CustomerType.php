<?php

namespace App\Enums;

enum CustomerType: int
{
    case Normal = 0;
    case Regular = 1;  
    public function label(): string
    { 
        return match ($this) {
            self::Normal => __('Normal User'),
            self::Regular => __('Regular User'), 
        };
    }
}
