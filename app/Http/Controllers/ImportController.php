<?php

namespace App\Http\Controllers;

use App\Actions\ImportCitiesAction;
use App\Actions\ImportPostalCodesAction;
use App\Actions\ImportProvincesAction;
use App\Actions\ImportRegionsAction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function imports()
    {
        return Inertia::render('Imports', [
            'imports' => [
                'regions' => route('imports.regions'),
                'provinces' => route('imports.provinces'),
                'cities' => route('imports.cities'),
            ],
        ]);
    }

    public function regions(Request $request)
    {
        $path = $this->storeCsv($request);
        ImportRegionsAction::dispatch(Storage::disk('imports')->path($path));

        return response()->json([
            'message' => __('ui.import_regions_dispatched'),
        ]);
    }

    public function provinces(Request $request)
    {
        $path = $this->storeCsv($request);
        ImportProvincesAction::dispatch(Storage::disk('imports')->path($path));

        return response()->json([
            'message' => __('ui.import_provinces_dispatched'),
        ]);
    }

    public function cities(Request $request)
    {
        $path = $this->storeCsv($request);

        ImportCitiesAction::dispatch(Storage::disk('imports')->path($path));

        return response()->json([
            'message' => __('ui.import_cities_dispatched'),
        ]);
    }

    public function postal_codes(Request $request)
    {
        $path = $this->storeCsv($request);

        ImportPostalCodesAction::dispatch(Storage::disk('imports')->path($path));

        return response()->json([
            'message' => __('ui.import_postal_codes_dispatched'),
        ]);
    }

    private function storeCsv(Request $request): string
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/plain,text/csv', 'max:10240'], // 10MB
        ]);

        $file = $request->file('file');
        if (! $file) {
            throw new Exception(__('ui.no_file_uploaded'));
        }

        return Storage::disk('imports')->putFileAs(
            '',
            $file,
            now()->format('Ymd_His').'_'.uniqid().'.csv'
        );
    }
}
