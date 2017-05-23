<?php

namespace Spatie\TinkerTools;

class ShortClassNames
{
    /** @var \Illuminate\Support\Collection */
    public $classes;

    public static function register(string $classMapPath = null)
    {
        $classMapPath = $classMapPath ?? base_path('vendor/composer/autoload_classmap.php');

        (new static($classMapPath))->registerAutoloader();
    }

    public function __construct(string $classMapPath)
    {
        $classFiles = include $classMapPath;

        $this->classes = collect($classFiles)
            ->map(function (string $path, string $fqcn) {
                $name = last(explode('\\', $fqcn));
                return compact('fqcn', 'name');
            })
            ->filter()
            ->values();
    }

    public function registerAutoloader()
    {
        if (implode('.', array_slice(explode('.', app()::VERSION), 0, 2)) >= '5.3') {
            spl_autoload_register([$this, 'aliasClass']);
        } else {
            spl_autoload_register([$this, 'aliasClass52']);
        }
    }

    public function aliasClass($findClass)
    {
        $class = $this->classes->first(function ($class) use ($findClass) {
            return $class['name'] === $findClass;
        });

        if (! $class) {
            return;
        }

        class_alias($class['fqcn'], $class['name']);
    }

    public function aliasClass52($findClass)
    {
        $class = $this->classes->first(function ($key, $class) use ($findClass) {
            return $class['name'] === $findClass;
        });

        if (! $class) {
            return;
        }

        class_alias($class['fqcn'], $class['name']);
    }
}
