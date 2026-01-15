<?php

namespace App\Actions;

use Closure;

class ImportRegionsPipe
{
    /**
     * Handle the import of regions in the pipeline.
     */
    public function handle(array $context, Closure $next): array
    {
        $isCommand = $context['isCommand'];
        $command = $context['command'];

        // Execute regions import
        $regionAction = app(ImportRegionsAction::class);
        $regionAction->handle($isCommand, $command);

        // Update the count in context
        $context['counts']['regions'] = $regionAction->regionsCount ?? 0;

        return $next($context);
    }
}
