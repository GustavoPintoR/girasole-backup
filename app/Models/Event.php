<?php

namespace App\Models;

use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\RegionFactory> */
    use HasFactory, HasCustomFields;

    protected $fillable = [
        'start_date',
        'end_date',
        'start',
        'end',
        'all_day',
        'title',
        'description',
        'calendarId'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start' => 'datetime:Y-m-d H:i:s',
        'end' => 'datetime:Y-m-d H:i:s',
        'all_day' => 'boolean',
    ];

    // Shape the event for Schedule-X
    public function toCalendarArray(): array
    {
        if ($this->all_day) {
            return [
                'id' => $this->id,
                'start' => optional($this->start_date)->format('Y-m-d'),
                'end' => optional($this->end_date)->format('Y-m-d'),
                'title' => $this->title,
                'description' => $this->description,
                'calendarId' => $this->calendarId,
            ];
        }

        return [
            'id' => $this->id,
            'start' => optional($this->start)->utc()->format('Y-m-d H:i'),
            'end' => optional($this->end)->utc()->format('Y-m-d H:i'),
            'title' => $this->title,
            'description' => $this->description,
            'calendarId' => $this->calendarId,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function cadastralGroup(): BelongsTo
    {
        return $this->belongsTo(CadastralGroup::class);
    }

    /**
     * Get the users that belong to this event.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(CadastralGroup::class)->withTimestamps();
    }

    /**
     * Assign the event to all fields.
     */
    public function assignToAllFields(): void
    {
        $allCadastralGroupIds = CadastralGroup::pluck('id')->toArray();
        $this->attendees()->sync($allCadastralGroupIds);
    }

}
