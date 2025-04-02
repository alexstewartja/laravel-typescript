<?php

namespace AlexStewartJa\TypeScript\Generators;

use AlexStewartJa\TypeScript\Contracts\Generator;
use AlexStewartJa\TypeScript\Helpers\FormattingHelper;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class AbstractGenerator implements Generator
{
    protected ReflectionClass $reflection;

    public function generate(ReflectionClass $reflection): ?string
    {
        $this->reflection = $reflection;
        $this->boot();
        $indent = FormattingHelper::indent();
        $newLineNoIndent = FormattingHelper::newLine(0);
        $newLineSingleIndent = FormattingHelper::newLine();
        $newLineDoubleIndent = FormattingHelper::newLine(2);

        if (empty(trim($definition = $this->getDefinition()))) {
            return "{$indent}export interface {$this->tsClassName()} {}" . PHP_EOL;
        }

        return "{$indent}export interface {$this->tsClassName()} {{$newLineDoubleIndent}$definition{$newLineSingleIndent}}$newLineNoIndent";
    }

    protected function boot(): void
    {
        //
    }

    protected function tsClassName(): string
    {
        return str_replace('\\', '.', $this->reflection->getShortName());
    }
}
