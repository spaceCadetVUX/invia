<?php

namespace App\Enums;

enum EventType: string
{
    case Wedding    = 'wedding';
    case Birthday   = 'birthday';
    case Conference = 'conference';
    case Party      = 'party';
    case Other      = 'other';

    public function label(): string
    {
        return match($this) {
            self::Wedding    => 'Đám cưới',
            self::Birthday   => 'Sinh nhật',
            self::Conference => 'Hội nghị',
            self::Party      => 'Tiệc',
            self::Other      => 'Khác',
        };
    }
}
