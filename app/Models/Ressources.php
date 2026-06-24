<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ressources extends Model
{
    protected $table = 'ressources';
    protected $primaryKey = 'id_ressource';

    protected $fillable = [
        'type', 'resume', 'url',
        'nom_original', 'id_utilisateur', 'id_fluxrss',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function fluxRss(): BelongsTo
    {
        return $this->belongsTo(FluxRss::class, 'id_fluxrss');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tags::class,
            'catégoriser',
            'id_ressource',
            'id_tag'
        );
    }

    public function syntheses(): BelongsToMany
    {
        return $this->belongsToMany(
            Syntheses::class,
            'synthetiser',
            'id_ressource',
            'id_synthese'
        );
    }

    public function partages(): BelongsToMany
    {
        return $this->belongsToMany(
            Utilisateurs::class,
            'partager',
            'id_ressource',
            'id_utilisateur'
        );
    }
}
