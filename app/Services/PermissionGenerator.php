<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\Permission\Models\Permission;

class PermissionGenerator
{
    protected array $actions = ['create', 'read', 'update', 'delete', 'store'];

    /**
     * Discover all Eloquent models in app/Models.
     */
    protected function discoverModels(array $excludeModels): array
    {
        $models = [];
        $modelPath = app_path('Models');
        if (! is_dir($modelPath)) {
            return $models;
        }

        $files = File::allFiles($modelPath);
        foreach ($files as $file) {
            $class = 'App\\Models\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                if ($reflection->isSubclassOf(Model::class) && ! $reflection->isAbstract()) {
                    $modelName = Str::snake(class_basename($class));
                    if (! in_array($modelName, $excludeModels)) {
                        $models[$modelName] = $class;
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Generate permissions for all models and non-model-specific permissions.
     */
    public function generate(): array
    {
        $permissions = [];
        $customPermissions = config('permission.custom_permissions', []);
        $excludeModels = config('permission.exclude_models', []);
        $modelNameOverrides = config('permission.model_name_overrides', []);
        $models = $this->discoverModels($excludeModels);

        // Generate model-specific permissions
        foreach ($models as $modelName => $modelClass) {
            $modelName = $modelNameOverrides[$modelName] ?? $modelName;

            foreach ($this->actions as $action) {
                $permission = Str::slug("$action $modelName", '_');
                $permissions[] = $permission;
            }

            if (isset($customPermissions[$modelName])) {
                foreach ($customPermissions[$modelName] as $customAction) {
                    $permission = Str::slug("$customAction $modelName", '_');
                    $permissions[] = $permission;
                }
            }
        }

        if (isset($customPermissions['general'])) {
            foreach ($customPermissions['general'] as $customPermission) {
                $permissions[] = Str::slug($customPermission, '_');
            }
        }

        return array_unique($permissions);
    }

    /**
     * Seed permissions to the database.
     */
    public function seed(): void
    {
        foreach ($this->generate() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}
