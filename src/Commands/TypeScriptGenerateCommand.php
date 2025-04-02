<?php

namespace AlexStewartJa\TypeScript\Commands;

use AlexStewartJa\TypeScript\TypeScriptGenerator;
use Illuminate\Console\Command;

class TypeScriptGenerateCommand extends Command
{
    public $signature = 'laravel-typescript:generate';

    public $description = 'Generate TypeScript interfaces/definitions from Laravel Eloquent Models';

    public function handle()
    {
        $generator = new TypeScriptGenerator(
            generators: config('laravel-typescript.generators', []),
            output: config('laravel-typescript.output_file_path', resource_path('js/types/models.d.ts')),
            autoloadDev: config('laravel-typescript.autoload_dev', false),
            paths: config('laravel-typescript.paths', []),
        );

        $generator->execute();

        $this->comment('TypeScript interfaces/definitions generated successfully!');
    }
}
