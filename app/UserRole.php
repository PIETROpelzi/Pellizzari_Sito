<?php

namespace App;

enum UserRole: string
{
    case Admin = 'Admin';
    case Doctor = 'Doctor';
    case Patient = 'Patient';
    case Caregiver = 'Caregiver';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }
}
