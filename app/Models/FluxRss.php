<?php

namespace App\Models;

use App\Models\Ressources;
use App\Models\Utilisateurs;
use App\Models\Tags;

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

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressources::class,
            'associer',
            'id_fluxrss',
            'id_ressource'
        );
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