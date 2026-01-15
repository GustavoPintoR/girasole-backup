<?php

namespace App\Actions;

use Closure;

class ImportCitiesPipe
{
    /**
     * Handle the import of cities in the pipeline.
     */
    public function handle(array $context, Closure $next): array
    {
        $isCommand = $context['isCommand'];
        $command = $context['command'];

        // Execute cities import
        $cityAction = app(ImportCitiesAction::class);
        $cityAction->handle($isCommand, $command);

        // Update the count in context
        $context['counts']['cities'] = $cityAction->citiesCount;

        return $next($context);
    }
}
