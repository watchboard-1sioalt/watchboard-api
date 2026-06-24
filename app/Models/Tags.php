<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tags extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id_tag';

    protected $fillable = ['tag', 'public', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

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
