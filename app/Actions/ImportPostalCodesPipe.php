<?php

namespace App\Actions;

use Closure;

class ImportPostalCodesPipe
{
    /**
     * Handle the import of postal codes in the pipeline.
     */
    public function handle(array $context, Closure $next): array
    {
        $isCommand = $context['isCommand'];
        $command = $context['command'];

        // Execute postal codes import
        $postalCodeAction = app(ImportPostalCodesAction::class);
        $postalCodeAction->handle($isCommand, $command);

        // Update the count in context
        $context['counts']['postal_codes'] = $postalCodeAction->postalCodesCount;

        return $next($context);
    }
}
