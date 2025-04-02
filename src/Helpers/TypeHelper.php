<?php

namespace AlexStewartJa\TypeScript\Helpers;

use AlexStewartJa\TypeScript\Definitions\TypeScriptType;
use AlexStewartJa\TypeScript\Transformers\TypeTransformer;
use Doctrine\DBAL\Types\Types as DbalType;

class TypeHelper
{
    public static function getColumnType(string $type): string
    {
        return (app()->version() < 11) ? self::getDbalColumnType($type) : self::getStandardColumnType($type);
    }

    public static function getDbalColumnType(string $type): string|array
    {
        return match ($type) {
            DbalType::ASCII_STRING, DbalType::BINARY, DbalType::BLOB, DbalType::DATE_MUTABLE, DbalType::DATE_IMMUTABLE,
            DbalType::DATEINTERVAL, DbalType::DATETIME_MUTABLE, DbalType::DATETIME_IMMUTABLE,
            DbalType::DATETIMETZ_MUTABLE, DbalType::DATETIMETZ_IMMUTABLE, DbalType::ENUM, DbalType::GUID,
            DbalType::STRING, DbalType::TEXT => TypeScriptType::STRING,

            DbalType::BIGINT, DbalType::DECIMAL, DbalType::FLOAT, DbalType::INTEGER, DbalType::SMALLFLOAT,
            DbalType::SMALLINT, DbalType::TIME_MUTABLE, DbalType::TIME_IMMUTABLE => TypeScriptType::NUMBER,

            DbalType::JSON, DbalType::SIMPLE_ARRAY => [TypeScriptType::array(), TypeScriptType::ANY],

            DbalType::BOOLEAN => TypeScriptType::BOOLEAN,

            default => TypeScriptType::ANY,
        };
    }

    public static function getStandardColumnType(string $type): string|array
    {
        /** @var TypeTransformer|string $typeTransformer */
        $typeTransformer = DriverHelper::getTypeTransformer();

        return class_exists($typeTransformer) ? new $typeTransformer()->transform($type) : TypeScriptType::ANY;
    }
}
