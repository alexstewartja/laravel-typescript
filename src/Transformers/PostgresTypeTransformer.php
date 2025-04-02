<?php

namespace AlexStewartJa\TypeScript\Transformers;

use AlexStewartJa\TypeScript\Definitions\TypeScriptType as TSType;
use Illuminate\Support\Str;

class PostgresTypeTransformer implements TypeTransformer
{

    public function transform(string $type): string
    {
        if (Str::startsWith($type, ['date', 'json', 'pg_', 'time'])) {
            return TSType::STRING;
        } elseif (Str::startsWith($type, ['float', 'int', 'reg'])) {
            return TSType::NUMBER;
        } else {
            return match ($type) {
                'bit', 'money', 'numeric', 'oid', 'varbit', 'xid', 'xid8' => TSType::NUMBER,
                'bool' => TSType::BOOLEAN,
                'bpchar', 'char', 'cidr', 'circle', 'inet', 'interval', 'lseg', 'macaddr', 'macaddr8', 'name',
                'numrange', 'path', 'point', 'polygon', 'text', 'tsrange', 'tstzrange', 'uuid', 'varchar',
                'xml' => TSType::STRING,
                default => TSType::ANY
            };
        }
    }
}
