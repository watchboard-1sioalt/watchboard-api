<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FluxRss extends Model
{
    protected $table = 'flux_rss';
    protected $primaryKey = 'id_fluxrss';

    protected $fillable = ['url', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressources::class, 'id_fluxrss');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tags::class,
            'appartenir',
            'id_fluxrss',
            'id_tag'
        );
    }
}
