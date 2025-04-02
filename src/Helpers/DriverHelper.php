<?php

namespace AlexStewartJa\TypeScript\Helpers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class DriverHelper
{
    public static function getDbConnectionName(): string
    {
        return config('laravel-typescript.db_connection');
    }
    public static function getDbConnectionConfig(): array
    {
        return config('database.connections')[self::getDbConnectionName()];
    }
    public static function getDbConnectionObject(): Connection
    {
        return DB::connection(self::getDbConnectionName());
    }
    public static function getDriverName(): string
    {
        return self::getDbConnectionConfig()['driver'];
    }
    public static function getTypeTransformer(?string $driver = null): ?string
    {
        $driver = $driver ?? self::getDriverName();
        return self::isDriverTransformable($driver) ? config('laravel-typescript.type_transformers')[$driver] : null;
    }

    public static function isDriverTransformable(?string $driver = null): bool
    {
        $driver = $driver ?? self::getDriverName();
        return $driver && in_array($driver, array_keys(config('laravel-typescript.type_transformers')));
    }
}
