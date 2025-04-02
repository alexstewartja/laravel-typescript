<?php

namespace AlexStewartJa\TypeScript\Contracts;

use ReflectionClass;

interface Generator
{
    public function generate(ReflectionClass $reflection): ?string;

    public function getDefinition(): ?string;
}
