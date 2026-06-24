<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Syntheses extends Model
{
    protected $table = 'syntheses';
    protected $primaryKey = 'id_synthese';

    protected $fillable = ['synthese', 'date_creation', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressources::class,
            'synthetiser',
            'id_synthese',
            'id_ressource'
        );
    }
}
