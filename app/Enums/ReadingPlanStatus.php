<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case In_Progress = 'in_progress';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::In_Progress => '読書中',
            self::Completed => '読了',
            self::Overdue => '期限超過',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::In_Progress => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800',
        };
    }
}
