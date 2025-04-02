<?php

namespace AlexStewartJa\TypeScript\Generators;

use AlexStewartJa\TypeScript\Definitions\TypeScriptProperty;
use AlexStewartJa\TypeScript\Definitions\TypeScriptType;
use AlexStewartJa\TypeScript\Helpers\FormattingHelper;
use AlexStewartJa\TypeScript\Helpers\TypeHelper;
use AlexStewartJa\TypeScript\Transformers\TypeTransformer;
use Doctrine\DBAL\Schema\Column as DbalColumn;
use Doctrine\DBAL\Types\Types as DbalType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class ModelGenerator extends AbstractGenerator
{
    protected Model $model;
    /** @var Collection<DbalColumn|array> */
    protected Collection $columns;

    public function __construct()
    {
        if (app()->version() < 11) {
            // Enums aren't supported by earlier versions of DBAL, so map enum columns to string.
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }
    }

    public function getDefinition(): ?string
    {
        return collect([
            $this->getProperties(),
            $this->getRelations(),
            $this->getManyRelations(),
            $this->getAccessors(),
        ])
            ->unique()
            ->filter(fn(string $part) => !empty($part))
            ->join(FormattingHelper::newLine(2));
    }

    protected function getProperties(): string
    {
        return $this->columns->map(function (DbalColumn|array $column) {
            return (string)new TypeScriptProperty(
                name: is_array($column) ? $column['name'] : $column->getName(),
                types: TypeHelper::getColumnType(is_array($column) ? $column['type_name'] : $column->getType()->getName()),
                nullable: is_array($column) ? $column['nullable'] : !$column->getNotnull()
            );
        })
            ->join(FormattingHelper::newLine(2));
    }

    protected function getRelations(): string
    {
        return $this->getRelationMethods()
            ->map(function (ReflectionMethod $method) {
                return (string)new TypeScriptProperty(
                    name: Str::snake($method->getName()),
                    types: $this->getRelationType($method),
                    optional: true,
                    nullable: true
                );
            })
            ->join(FormattingHelper::newLine(2));
    }

    protected function getRelationMethods(): Collection
    {
        return $this->getMethods()
            ->filter(function (ReflectionMethod $method) {
                try {
                    return $method->invoke($this->model) instanceof Relation;
                } catch (Throwable) {
                    return false;
                }
            })
            // [TODO] Resolve trait/parent relations as well (e.g. DatabaseNotification)
            // skip traits for awhile
            ->filter(function (ReflectionMethod $method) {
                return collect($this->reflection->getTraits())
                    ->filter(function (ReflectionClass $trait) use ($method) {
                        return $trait->hasMethod($method->name);
                    })
                    ->isEmpty();
            });
    }

    protected function getMethods(): Collection
    {
        return collect($this->reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn(ReflectionMethod $method) => $method->isStatic())
            ->reject(fn(ReflectionMethod $method) => $method->getNumberOfParameters());
    }

    protected function getRelationType(ReflectionMethod $method): string
    {
        $relationReturn = $method->invoke($this->model);
        $related = str_replace('\\', '.', get_class($relationReturn->getRelated()));

        if ($this->isManyRelation($method)) {
            return TypeScriptType::array($related);
        }

        if ($this->isOneRelation($method)) {
            return $related;
        }

        return TypeScriptType::ANY;
    }

    protected function isManyRelation(ReflectionMethod $method): bool
    {
        $relationType = get_class($method->invoke($this->model));

        return in_array(
            $relationType,
            [
                HasMany::class,
                BelongsToMany::class,
                HasManyThrough::class,
                MorphMany::class,
                MorphToMany::class,
            ]
        );
    }

    protected function isOneRelation(ReflectionMethod $method): bool
    {
        $relationType = get_class($method->invoke($this->model));

        return in_array(
            $relationType,
            [
                HasOne::class,
                BelongsTo::class,
                MorphOne::class,
                HasOneThrough::class,
            ]
        );
    }

    protected function getManyRelations(): string
    {
        return $this->getRelationMethods()
            ->filter(fn(ReflectionMethod $method) => $this->isManyRelation($method))
            ->map(function (ReflectionMethod $method) {
                return (string)new TypeScriptProperty(
                    name: Str::snake($method->getName()) . '_count',
                    types: TypeScriptType::NUMBER,
                    optional: true,
                    nullable: true
                );
            })
            ->join(FormattingHelper::newLine(2));
    }

    protected function getAccessors(): string
    {
        $relationsToSkip = $this->getRelationMethods()
            ->map(function (ReflectionMethod $method) {
                return Str::snake($method->getName());
            });

        return $this->getMethods()
            ->filter(fn(ReflectionMethod $method) => Str::startsWith($method->getName(), 'get'))
            ->filter(fn(ReflectionMethod $method) => Str::endsWith($method->getName(), 'Attribute'))
            ->mapWithKeys(function (ReflectionMethod $method) {
                $property = (string)Str::of($method->getName())
                    ->between('get', 'Attribute')
                    ->snake();

                return [$property => $method];
            })
            ->reject(function (ReflectionMethod $method, string $property) {
                return $this->columns->contains(fn(DbalColumn|array $column) => (is_array($column) ? $column['name'] : $column->getName()) == $property);
            })
            ->reject(function (ReflectionMethod $method, string $property) use ($relationsToSkip) {
                return $relationsToSkip->contains($property);
            })
            ->map(function (ReflectionMethod $method, string $property) {
                return (string)new TypeScriptProperty(
                    name: $property,
                    types: TypeScriptType::fromMethod($method),
                    optional: true,
                    readonly: true
                );
            })
            ->join(FormattingHelper::newLine(2));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \ReflectionException
     */
    protected function boot(): void
    {
        $this->model = $this->reflection->newInstance();

        if (app()->version() < 11) {
            $columns = $this->model->getConnection()
                ->getDoctrineSchemaManager()
                ->listTableColumns($this->model->getConnection()->getTablePrefix() . $this->model->getTable());
        } else {
            $columns = Schema::getColumns($this->model->getTable());
        }

        $this->columns = collect($columns);
    }
}
