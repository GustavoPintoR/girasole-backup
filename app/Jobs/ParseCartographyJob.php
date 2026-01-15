<?php

namespace App\Jobs;

use App\Traits\HasNotification;
use Exception;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\CreateOrUpdateCadastralUnitJob;

class ParseCartographyJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasNotification;

    public ?string $posList = null;

    public function __construct(
        public string $region,
        public string $province,
        public string $cityCode,
        public int $cityId,
        public string $sheet,
        public string $parcel = '2',
        public int $userId,
        public ?int $cadastralUnitId = null
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $log = Log::channel('cadastrals');
        $disk = Storage::disk('local');
        $basePath = 'cartography';

        $context = [
            'user_id' => $this->userId,
            'region' => $this->region,
            'province' => $this->province,
            'city_code' => $this->cityCode,
            'city_id' => $this->cityId,
            'sheet' => $this->sheet,
            'parcel' => $this->parcel,
            'cadastral_unit_id' => $this->cadastralUnitId,
        ];

        $log->info('Starting ParseCartographyJob', $context);

        try {
            $slugRegion = $this->slugifyRegion($this->region);
            $originalRegionDir = $basePath . DIRECTORY_SEPARATOR . $slugRegion;
            $targetDirUuid = (string) Str::uuid();
            $regionDir = $targetDirUuid . DIRECTORY_SEPARATOR . $slugRegion;

            $context['slug_region'] = $slugRegion;
            $context['original_region_dir'] = $originalRegionDir;
            $context['target_dir'] = $targetDirUuid;
            $context['region_dir'] = $regionDir;

            $log->info('Extracting region ZIP', $context + ['zip_path' => $disk->path($originalRegionDir . '.zip')]);

            $this->extractDir($disk, $originalRegionDir, $regionDir);
            if (! $disk->exists($regionDir)) {
                $log->error('Region directory not created after extraction', $context);
                return;
            }
            $log->info('Region extracted successfully', $context);

            $provinceCode = $this->uppercase($this->checkProvince($this->province));
            $provinceDir = $regionDir . DIRECTORY_SEPARATOR . $provinceCode;
            $context['province_code'] = $provinceCode;
            $context['province_dir'] = $provinceDir;

            $log->info('Extracting province ZIP', $context + ['zip_path' => $disk->path($provinceDir . '.zip')]);

            $this->extractDir($disk, $provinceDir, $provinceDir);
            if (! is_dir($disk->path($provinceDir))) {
                $log->error('Province directory not found after extraction', $context);
                $this->cleanup($disk, $targetDirUuid, $context);
                return;
            }
            $log->info('Province extracted successfully', $context);

            $cityCodeFile = $provinceDir . DIRECTORY_SEPARATOR . $this->cityCode;
            $context['city_code_file'] = $cityCodeFile;

            $zipFound = false;
            $files = $disk->files($provinceDir);
            foreach ($files as $file) {
                if (Str::contains($file, $this->cityCode)) {
                    $targetZip = $cityCodeFile . '.zip';
                    $disk->move($file, $targetZip);
                    $zipFound = true;
                    $context['moved_zip'] = $targetZip;
                    $log->info('City ZIP file moved', $context);
                    break;
                }
            }

            if (! $zipFound) {
                $log->error('City ZIP file not found in province directory', $context + ['available_files' => $files]);
                $this->cleanup($disk, $targetDirUuid, $context);
                return;
            }

            $log->info('Extracting city ZIP', $context + ['zip_path' => $disk->path($cityCodeFile . '.zip')]);
            $this->extractDir($disk, $cityCodeFile, $cityCodeFile);

            $plaFileDir = $disk->path($cityCodeFile);
            if (! is_dir($plaFileDir)) {
                $log->error('City extraction directory not created', $context);
                $this->cleanup($disk, $targetDirUuid, $context);
                return;
            }
            $log->info('City extracted successfully', $context);

            $gmlFilePath = null;
            $gmlTargetPath = $cityCodeFile . DIRECTORY_SEPARATOR . $this->cityCode . '_ple.gml';
            $files = $disk->files($cityCodeFile);
            foreach ($files as $file) {
                if (Str::contains($file, '_ple.gml')) {
                    $disk->move($file, $gmlTargetPath);
                    $gmlFilePath = $disk->path($gmlTargetPath);
                    $context['gml_file'] = $gmlTargetPath;
                    $log->info('GML file moved', $context);
                    break;
                }
            }

            if (! $gmlFilePath || ! file_exists($gmlFilePath)) {
                $log->error('GML file not found after extraction', $context + ['expected' => $gmlTargetPath, 'available' => $files]);
                $this->cleanup($disk, $targetDirUuid, $context);
                throw new Exception('GML file not found: ' . $gmlTargetPath);
            }
            $log->info('GML file ready for parsing', $context);

            $formatOptions = [
                str_pad((int)$this->sheet * 100, 6, '0', STR_PAD_LEFT),
                str_pad($this->sheet, 6, '0', STR_PAD_LEFT),
                $this->sheet
            ];

            $posList = null;
            $gmlIdSearched = [];

            foreach ($formatOptions as $formatSheet) {
                $gmlId = "CadastralParcel.IT.AGE.PLA.{$this->cityCode}_{$formatSheet}.{$this->parcel}";
                $gmlIdSearched[] = $gmlId;

                $log->info('Searching for parcel in GML', $context + ['gml_id' => $gmlId]);

                try {
                    $xmlReader = new \XMLReader();
                    if (! $xmlReader->open($gmlFilePath)) {
                        $log->warning('XMLReader failed to open GML file', $context + ['gml_id' => $gmlId]);
                        continue;
                    }

                    $found = false;
                    while ($xmlReader->read()) {
                        if (
                            $xmlReader->nodeType === \XMLReader::ELEMENT &&
                            $xmlReader->localName === 'CadastralParcel'
                        ) {
                            $attr = $xmlReader->getAttribute('gml:id') ??
                                $xmlReader->getAttribute('id') ??
                                $xmlReader->getAttribute('gml_id');

                            if ($attr === $gmlId) {
                                $outerXml = $xmlReader->readOuterXml();
                                if ($outerXml !== false) {
                                    try {
                                        $sxe = @simplexml_load_string($outerXml);
                                        if ($sxe !== false) {
                                            $nodes = $sxe->xpath('.//*[local-name()="posList"]');
                                            if ($nodes && isset($nodes[0])) {
                                                $posList = trim((string) $nodes[0]);
                                                $log->info('posList found for parcel', $context + [
                                                        'gml_id' => $gmlId,
                                                        'posList_length' => strlen($posList),
                                                        'sample' => substr($posList, 0, 100) . '...'
                                                    ]);
                                                $found = true;
                                            }
                                        }
                                    } catch (\Throwable $ex) {
                                        $log->warning('Failed to parse parcel XML subtree', $context + [
                                                'gml_id' => $gmlId,
                                                'error' => $ex->getMessage()
                                            ]);
                                    }
                                }
                                break;
                            }
                        }
                    }

                    $xmlReader->close();

                    if ($found) {
                        break;
                    } else {
                        $log->debug('Parcel not found with this format', $context + ['gml_id' => $gmlId]);
                    }
                } catch (Exception $e) {
                    $log->warning('XMLReader exception during streaming', $context + [
                            'gml_id' => $gmlId,
                            'error' => $e->getMessage()
                        ]);
                }
            }

            if (! $posList) {
                $log->warning('No posList found after trying all sheet formats', $context + [
                        'tried_gml_ids' => $gmlIdSearched,
                    ]);

                $this->notifyUser(
                    $this->userId,
                    __('ui.cadastral_unit_failed'),
                    __('ui.cadastral_unit_failed_message', [
                        'city'   => $this->cityCode,
                        'sheet'  => $this->sheet,
                        'parcel' => $this->parcel,
                    ])
                );

                $this->cleanup($disk, $targetDirUuid, $context);
                return;
            }

            $this->posList = $posList;

            // Dispatch next job
            $dispatchContext = [
                'city_id' => $this->cityId,
                'sheet' => $this->sheet,
                'parcel' => $this->parcel,
            ];

            $log->info('Creating/updating cadastral unit for user', array_merge($context, $dispatchContext, [
                'posList_length' => strlen($posList)
            ]));

            CreateOrUpdateCadastralUnitJob::dispatch(
                $dispatchContext,
                $this->userId,
                $posList,
                $this->cadastralUnitId
            );

            $log->info('ParseCartographyJob completed successfully', $context);

        } catch (\Throwable $e) {
            $log->error('Unexpected error in ParseCartographyJob', array_merge($context, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));
            throw $e;
        } finally {
            $this->cleanup($disk, $targetDirUuid ?? null, $context ?? [], $log);
        }
    }

    private function cleanup($disk, ?string $targetDir, array $context, $log = null): void
    {
        if (!$targetDir) return;

        if (!$log) $log = Log::channel('cadastrals');

        if ($disk->exists($targetDir)) {
            $disk->deleteDirectory($targetDir);
            $log->info('Temporary directory cleaned up', array_merge($context, ['temp_dir' => $targetDir]));
        }
    }

    private function extractDir($disk, string $dir, string $toFolder): void
    {
        $log = Log::channel('cadastrals');
        $context = ['zip_path' => $disk->path($dir . '.zip'), 'extract_to' => $disk->path($toFolder)];

        if (! $disk->exists($toFolder)) {
            $disk->makeDirectory($toFolder, 0755, true);
        }

        $zipPath = $disk->path($dir . '.zip');
        $extractPath = $disk->path($toFolder);
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            $log->debug('ZIP extracted successfully', $context);
        } else {
            $log->warning('Failed to open or extract ZIP', $context);
        }
    }

    private function slugifyRegion(string $region): string
    {
        $v = explode('/', $region)[0];
        $slug = Str::slug($v);
        $slug = Str::replace('valle-daosta', 'valle-aosta', $slug);
        return Str::upper($slug);
    }

    private function checkProvince(string $province): string
    {
        return Str::replace('BT', 'BA', $province);
    }

    private function uppercase(string $str): string
    {
        return mb_strtoupper($str, 'UTF-8');
    }
}
