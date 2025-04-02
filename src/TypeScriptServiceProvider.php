<?php

namespace AlexStewartJa\TypeScript;

use AlexStewartJa\TypeScript\Commands\TypeScriptGenerateCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TypeScriptServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-typescript')
            ->hasConfigFile('laravel-typescript')
            ->hasCommand(TypeScriptGenerateCommand::class);
    }
}
