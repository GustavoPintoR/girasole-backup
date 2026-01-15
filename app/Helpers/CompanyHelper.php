<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompanyHelper
{
    const CACHE_KEY = 'girasole.cache_minutes.company';

    /**
     * Returns the current logged-in user
     */
    public static function getCurrentUser(): ?User
    {
        return User::where('id', auth()->user()?->id)->first();
    }

    /**
     * Returns the current company id string for the user
     */
    public static function getCurrentCompanyId(): ?string
    {
        return self::getCurrentUser()?->current_company_id;
    }

    /**
     * Returns the current company for the user
     */
    public static function getCurrentCompany(): ?Company
    {
        if(!is_null(self::getCurrentCompanyId())){
            $rememberKey = Str::slug(self::getCurrentCompanyId());
            $minutes = config(self::CACHE_KEY);
            return Cache::remember($rememberKey, $minutes, function () {
                try{
                    return Company::whereId(self::getCurrentCompanyId())->first();
                }catch(\Exception $e){
                    Log::warning("Company not found, {$e->getMessage()}");
                    return null;
                }
            });
        }

        return null;
    }

    /**
     * Returns a collection of classes that can be companyable
     */
    public static function getCompanyableClasses(): Collection
    {
        return collect(config('app.company.classes'))->map(function ($class) {
            return app($class);
        });
    }

    /**
     * This method is used to inject a list of classes that implement the Companyable class
     */
    public static function setCompanyClassesConfig(): void
    {
        config()->set('app.company.classes', self::getClassesInFolder("Models", "App\Interfaces\Companyable")->toArray());
    }

    /**
     * This method returns a collection of classes strings that are companyable
     */
    public static function getClassesInFolder(string $folder, string $implements): Collection
    {
        $models = collect(File::allFiles(app_path($folder)))
            ->map(function ($item) use ($folder) {

                $path = $item->getRelativePathName();
                return sprintf(
                    '\%s%s\%s',
                    Container::getInstance()->getNamespace(),
                    $folder,
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );
            })
            ->filter(function ($class) use ($implements) {
                $valid = false;

                if (class_exists($class)) {

                    $reflection = new \ReflectionClass($class);
                    $reflectionImplements = new \ReflectionClass($implements);
                    $valid = $reflection->isSubclassOf($reflectionImplements) &&
                        !$reflection->isAbstract();
                }

                return $valid;
            });

        return $models->values();
    }

    /**
     * Returns a collection of companies for the current user
     */
    public static function getCompanies(): Collection
    {
        return Cache::remember('companies', 60 * 60 * 30, function () {
            return Company::all();
        });
    }
}
