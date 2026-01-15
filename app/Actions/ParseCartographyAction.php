<?php

namespace App\Actions;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Saloon\XmlWrangler\XmlReader;
use ZipArchive;

use function Psl\Str\Byte\uppercase;

class ParseCartographyAction
{
    use AsAction;
    use AsCommand;

    public string $commandSignature = 'cartography:polygon {region} {province} {city_code} {sheet} {parcel=2}';

    public string $commandDescription = 'Unzip and parse cartography XML for a given region, province, city_code, parcel, sheet and returns a polygon.';

    public function handle(string $region, string $province, string $cityCode, string $sheet, string $parcel = '2'): null|string
    {
        $disk = Storage::disk('local');
        $basePath = 'cartography';
        $slugRegion = $this->slugifyRegion($region);
        $originalRegionDir = $basePath.DIRECTORY_SEPARATOR.$slugRegion;

        $targetDir = Str::uuid();

        $regionDir = $targetDir.DIRECTORY_SEPARATOR.$slugRegion;

        // Unzip region if not already
        $this->extractDir($disk, $originalRegionDir, $regionDir);

        $provinceDir = $regionDir.DIRECTORY_SEPARATOR.uppercase($this->checkProvince($province));

        // Unzip province if not already
        $this->extractDir($disk, $provinceDir, $provinceDir);

        $provinceDirPath = $disk->path($provinceDir);

        $cityCodeFile = $provinceDir.DIRECTORY_SEPARATOR.$cityCode;

        if (is_dir($provinceDirPath)) {
            $files = $disk->files($provinceDir);
            foreach ($files as $file) {
                if (Str::contains($file, $cityCode)) {
                    $disk->move($file, $cityCodeFile.'.zip');
                    break;
                }
            }
        }

        // Unzip city if not already
        $this->extractDir($disk, $cityCodeFile, $cityCodeFile);

        $plaFileDir = $disk->path($cityCodeFile);

        if (is_dir($plaFileDir)) {
            $files = $disk->files($cityCodeFile);
            foreach ($files as $file) {
                if (Str::contains($file, '_ple.gml')) {
                    $disk->move($file, $cityCodeFile.DIRECTORY_SEPARATOR.$cityCode.'_ple.gml');
                    break;
                }
            }
        }

        $plaFileDirPath = $disk->path($cityCodeFile.DIRECTORY_SEPARATOR.$cityCode.'_ple.gml');

        // Stream-parse the GML
        if (! file_exists($plaFileDirPath)) {
            throw new Exception('GML file not found: '.$plaFileDirPath);
        }

        $formatOptions = [
                str_pad((int)$sheet * 100, 6, '0', STR_PAD_LEFT), // 26 → 002600
                str_pad($sheet, 6, '0', STR_PAD_LEFT),            // 26 → 000026
                $sheet                                            // 26 → 26
        ];

        $posList = null;
        foreach ($formatOptions as $formatSheet) {
            $gmlId = "CadastralParcel.IT.AGE.PLA.{$cityCode}_{$formatSheet}.{$parcel}";

            try {
                $xmlReader = new \XMLReader();

                if (! $xmlReader->open($plaFileDirPath)) {
                    Log::warning('XMLReader failed to open file: ' . $plaFileDirPath);
                    continue;
                }

                while ($xmlReader->read()) {
                    if ($xmlReader->nodeType === \XMLReader::ELEMENT && $xmlReader->localName === 'CadastralParcel') {
                        // trying common attr names/qualifiers
                        $attr = $xmlReader->getAttribute('gml:id') ?? $xmlReader->getAttribute('id') ?? $xmlReader->getAttribute('gml_id');

                        if ($attr === $gmlId) {
                            $outerXml = $xmlReader->readOuterXml();
                            if ($outerXml !== false) {
                                try {
                                    $sxe = @simplexml_load_string($outerXml);
                                    if ($sxe !== false) {
                                        // using local-name() to find posList regardless of namespace prefix
                                        $nodes = $sxe->xpath('.//*[local-name()="posList"]');
                                        if ($nodes && isset($nodes[0])) {
                                            $posList = trim((string) $nodes[0]);
                                        }
                                    }
                                } catch (\Throwable $ex) {
                                    Log::warning('Failed to parse parcel subtree for GML ID: ' . $gmlId . ' in file: ' . $plaFileDirPath . ' with error: ' . $ex->getMessage());
                                }
                            }

                            break;
                        }
                    }
                }

                $xmlReader->close();
            } catch (Exception $e) {
                Log::warning('Failed to stream-parse GML ID: ' . $gmlId . ' in file: ' . $plaFileDirPath . ' with error: ' . $e->getMessage());
                continue;
            }

            if ($posList) {
                break;
            }
        }

        $disk->deleteDirectory($targetDir);

        if ($posList) {
            return $posList;
        }

        return null;
    }

    public function asCommand(Command $command): void
    {
        $region = $command->argument('region');
        $province = $command->argument('province');
        $city_code = $command->argument('city_code');
        $parcel = $command->argument('parcel');
        $sheet = $command->argument('sheet');
        $command->info("Parsing cartography for {$region}, {$province}, {$city_code}, {$sheet}, {$parcel}...");
        $result = $this->handle($region, $province, $city_code, $sheet, $parcel);
        if (is_null($result)) {
            $command->error('Failed to parse cartography.');

            return;
        }
        if (is_string($result)) {
            $command->warn($result);

            return;
        }
        $command->info('A Polygon has been found in cartography');
    }

    protected function extractDir($disk, string $dir, string $toFolder): void
    {

        if (! $disk->exists($toFolder)) {
            $disk->makeDirectory($toFolder, 0755, true);
        }
        $zipPath = $disk->path($dir.'.zip');
        $extractPath = $disk->path($toFolder);
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            Log::warning('Failed to unzip region: '.$zipPath);
        }

    }

    /**
     * Slugifies a region name for use in file paths.
     */
    private function slugifyRegion(string $region): string
    {
        $v = explode('/', $region)[0];
        $slug = Str::slug($v);
        $slug = Str::replace('valle-daosta', 'valle-aosta', $slug);

        return Str::upper($slug);
    }

    /**
     * If province is Barletta-Andria-Trani, change to Bari.
     */
    private function checkProvince(string $province): string
    {
        return Str::replace('BT', 'BA', $province);
    }
}
