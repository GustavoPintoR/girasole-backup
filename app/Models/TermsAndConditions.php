<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TermsAndConditions extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'version',
        'description',
        'summary',
        'is_active',
        'active_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['description_html', 'summary_html'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getSummaryHtmlAttribute(): string
    {
        return Str::of($this->summary)->markdown();
    }

    public function getDescriptionHtmlAttribute(): string
    {
        return Str::of($this->description)->markdown();
    }
}
