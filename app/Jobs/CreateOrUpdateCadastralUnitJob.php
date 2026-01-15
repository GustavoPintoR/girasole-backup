<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\User;
use App\Models\CadastralUnit;
use App\Traits\HasNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use App\Notifications\BroadcastMessageNotification;

class CreateOrUpdateCadastralUnitJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasNotification;

    protected array $data;
    protected int $userId;
    protected ?int $cadastralUnitId;
    protected string $posList;

    public function __construct(array $data, int $userId, string $posList, ?int $cadastralUnitId = null)
    {
        $this->data = $data;
        $this->userId = $userId;
        $this->posList = $posList;
        $this->cadastralUnitId = $cadastralUnitId;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            Log::channel('cadastrals')->info('Creating/updating cadastral unit for user ' . $this->userId, $this->data);

            $city = City::with('province.region')->find($this->data['city_id']);
            if (! $city) {
                throw new \Exception('City not found');
            }

            if (is_null($this->posList) || (is_string($this->posList) && str_starts_with($this->posList, 'GML file not found'))) {
                throw new \Exception('Geometry not found in dataset');
            }

            $polygonJson = CadastralUnit::posListToGeojson($this->posList);
            $parser = app(GeojsonParser::class);
            $geometry = $parser->parse($polygonJson);

            DB::transaction(function () use ($geometry) {
                if ($this->cadastralUnitId) {
                    $unit = CadastralUnit::find($this->cadastralUnitId);
                    if (! $unit) {
                        throw new \Exception('Cadastral unit to update not found');
                    }
                    $unit->update($this->data);
                } else {
                    $this->data['user_id'] = $this->userId;
                    $unit = CadastralUnit::create($this->data);
                }

                $unit->geometry = $geometry;
                $unit->centroid = ST::centroid($geometry);
                $unit->cadastral_area = ST::area(ST::transform($geometry, 3035));
                $unit->geometry_source = 'DATASET';
                $unit->geometry_updated_at = now();
                $unit->save();

                $this->notifyUser($this->userId, __('ui.cadastral_unit_processed'), __('ui.cadastral_unit_processed_message'), $unit->id);

                Log::channel('cadastrals')->info('Cadastral unit processed', ['id' => $unit->id]);
            });
        } catch (\Exception $e) {
            Log::channel('cadastrals')->error('Cadastral unit processing failed: ' . $e->getMessage());

            try {
                $user = User::find($this->userId);
                if ($user instanceof User) {
                    $context = [
                        'title' => __('ui.cadastral_unit_processing_failed'),
                        'description' => $e->getMessage() ?? __('ui.cadastral_unit_processing_failed_generic'),
                        'cadastral_unit_id' => $this->cadastralUnitId,
                    ];

                    $user->notify(new BroadcastMessageNotification($context, false));
                }
            } catch (\Exception $notifyEx) {
                Log::error('Failed to notify user about cadastral unit processing failure: ' . $notifyEx->getMessage());
            }
            throw $e;
        }
    }
}
