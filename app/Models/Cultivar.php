<?php

namespace App\Models;

use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cultivar extends Model
{
    /** @use HasFactory<\Database\Factories\CultivationFactory> */
    use HasFactory, HasCustomFields;

    /**
     * I campi che possono essere assegnati in massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'cultivation_id',
    ];

    /**
     * Ottieni la coltivazione a cui appartiene questa cultivar.
     */
    public function cultivation(): BelongsTo
    {
        return $this->belongsTo(Cultivation::class);
    }
}
