# Laravel TypeScript

[![Latest Stable Version](http://poser.pugx.org/alexstewartja/laravel-typescript/v)](https://packagist.org/packages/alexstewartja/laravel-typescript)
[![Total Downloads](http://poser.pugx.org/alexstewartja/laravel-typescript/downloads)](https://packagist.org/packages/alexstewartja/laravel-typescript)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/alexstewartja/laravel-typescript/php-cs-fixer.yml?label=code%20style)](https://github.com/alexstewartja/laravel-typescript/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![License](http://poser.pugx.org/alexstewartja/laravel-typescript/license)](https://packagist.org/packages/alexstewartja/laravel-typescript)

[![PHP Versions Supported](http://poser.pugx.org/alexstewartja/laravel-typescript/require/php)](https://packagist.org/packages/alexstewartja/laravel-typescript)
[![Laravel Versions Supported](https://img.shields.io/packagist/dependency-v/alexstewartja/laravel-typescript/illuminate/contracts?label=laravel)](https://packagist.org/packages/alexstewartja/laravel-typescript)

[![Buy Me A Coffee](https://img.shields.io/badge/Buy_Me-A_Coffee-orange?logo=buy-me-a-coffee)](https://buymeacoffee.com/alexstewartja)

A Laravel package which allows you to quickly generate TypeScript interfaces/definitions for your Eloquent models.

## Features

- :white_check_mark: Database columns
- :white_check_mark: Model relations
- :white_check_mark: Model accessors
- :hourglass_flowing_sand: Model casts
- :hourglass_flowing_sand: Inherited relations (Traits/Mixins, etc.)

## DBMS Compatibility (Laravel 11+)

- :white_check_mark: pgsql (PostgresSQL)
- :hourglass_flowing_sand: mysql (MySQL)
- :hourglass_flowing_sand: mariadb (MariaDB)
- :hourglass_flowing_sand: sqlsrv (Microsoft SQL Server)
- :hourglass_flowing_sand: sqlite (SQLite)

## Installation

You can install the package via composer:

```bash
composer require alexstewartja/laravel-typescript
```

## Configuration

Publish the config file (`config/laravel-typescript.php`) with:
```bash
php artisan vendor:publish --provider="AlexStewartJa\TypeScript\TypeScriptServiceProvider" --tag="typescript-config"
```

## Usage

Generate TypeScript interfaces for your Eloquent Models:
```bash
php artisan laravel-typescript:generate
```

### Example

#### Eloquent Model

As an example, the following Product model is defined in an eCommerce app:

```php
class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
    ];
        
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
}
```

#### TypeScript Interface

Laravel TypeScript will generate the following TypeScript interface:

```typescript
declare namespace App.Models {
    export interface Product {
        id: number;
        category_id: number;
        name: string;
        price: number;
        created_at: string | null;
        updated_at: string | null;
        category?: App.Models.Category | null;
        features?: Array<App.Models.Feature> | null;
    }
}
```

#### TS Interface Usage

This is an example usage with Vue 3:

```typescript
import { defineComponent, PropType } from "vue";

export default defineComponent({
    props: {
        product: {
            type: Object as PropType<App.Models.Product>,
            required: true,
        },
    },
});
```

And another Vue 3 example for InertiaJS:

```typescript
interface CartPageProps {
    products?: Array<App.Models.Product> | null;
    coupon_code?: string;
}

defineProps<CartPageProps>();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

A [Lando](https://lando.dev/) file is included in the repo to get up and running quickly:

```bash
lando start
```
Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more details.

## Security

Please see [SECURITY](.github/SECURITY.md) for more details.

## Credits

- [Alex Stewart](https://github.com/alexstewartja)
- [Boris Lepikhin](https://github.com/lepikhinb) - For developing [the foundation](https://github.com/lepikhinb/laravel-typescript) on which this package is "based" :drum:
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
