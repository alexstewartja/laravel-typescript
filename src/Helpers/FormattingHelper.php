<?php

namespace AlexStewartJa\TypeScript\Helpers;

class FormattingHelper
{
    public static function indent(int $count = 1): string
    {
        return str_repeat(' ', (config('laravel-typescript.formatting.spaces') * $count));
    }
    public static function newLine(int $indent = 1): string {
        return PHP_EOL . self::indent($indent);
    }
}
