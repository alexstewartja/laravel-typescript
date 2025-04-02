<?php

namespace AlexStewartJa\TypeScript\Transformers;

interface TypeTransformer
{
    public function transform(string $type): string;
}
