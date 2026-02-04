<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseDateHelper
{
    /**
     * Returns SQL expression for year-month (e.g. 2025-01) - SQLite & MySQL compatible.
     */
    public static function yearMonth(string $column = 'created_at'): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "strftime('%Y-%m', {$column})",
        };
    }

    /**
     * Returns SQL expression for full date (YYYY-MM-DD) - SQLite & MySQL compatible.
     */
    public static function date(string $column = 'created_at'): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            default => "strftime('%Y-%m-%d', {$column})",
        };
    }

    /**
     * Returns SQL expression for date with custom format - SQLite & MySQL compatible.
     * $format: 'year-month' (%Y-%m), 'date' (%Y-%m-%d)
     */
    public static function format(string $column, string $format): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "DATE_FORMAT({$column}, '{$format}')",
            default => "strftime('{$format}', {$column})",
        };
    }

    /**
     * Returns SQL expression for hour (0-23) - SQLite & MySQL compatible.
     */
    public static function hour(string $column = 'created_at'): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "HOUR({$column})",
            default => "CAST(strftime('%H', {$column}) AS INTEGER)",
        };
    }

    /**
     * Returns SQL expression for day of week (0=Sunday, 1=Monday, ... 6=Saturday) - SQLite & MySQL compatible.
     */
    public static function dayOfWeek(string $column = 'created_at'): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "DAYOFWEEK({$column}) - 1",
            default => "CAST(strftime('%w', {$column}) AS INTEGER)",
        };
    }
}
