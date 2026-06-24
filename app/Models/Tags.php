<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Ressources;
use App\Models\FluxRss;

class Tags extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id_tag';

    protected $fillable = ['tag', 'public'];

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressources::class,
            'catégoriser',
            'id_tag',
            'id_ressource'
        );
    }

    public function fluxRss(): BelongsToMany
    {
        return $this->belongsToMany(
            FluxRss::class,
            'appartenir',
            'id_tag',
            'id_fluxrss'
        );
    }
}
