<?php

namespace App\Actions;

use Closure;

class ImportProvincesPipe
{
    /**
     * Handle the import of provinces in the pipeline.
     */
    public function handle(array $context, Closure $next): array
    {
        $isCommand = $context['isCommand'];
        $command = $context['command'];

        // Execute provinces import
        $provinceAction = app(ImportProvincesAction::class);
        $provinceAction->handle($isCommand, $command);

        // Update the count in context
        $context['counts']['provinces'] = $provinceAction->provincesCount ?? 0;

        return $next($context);
    }
}
