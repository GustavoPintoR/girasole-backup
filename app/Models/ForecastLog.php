<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastLog extends Model
{
    use HasFactory;

    protected $fillable = ['field_id', 'status', 'data', 'ran_at', 'parameters'];

    protected $casts = [
        'data' => 'array',
        'ran_at' => 'datetime'
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(CadastralGroup::class, 'field_id');
    }
}
