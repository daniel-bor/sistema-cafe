<?php

namespace App\Enums;

enum TipoLicencia: int
{
    case A = 1;
    case B = 2;
    case C = 3;

    public static function labels(): array
    {
        return [
            self::A->value => 'Licencia A',
            self::B->value => 'Licencia B',
            self::C->value => 'Licencia C',
        ];
    }

    // Get the label for the enum value
    public function label(): string
    {
        return self::labels()[$this->value];
    }
    public function toString(): string
    {
        return $this->label();
    }
}
