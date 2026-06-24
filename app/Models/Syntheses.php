<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Ressources;
use App\Models\Utilisateurs;

class Syntheses extends Model
{
    protected $table = 'syntheses';
    protected $primaryKey = 'id_synthese';

    protected $fillable = ['synthese', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressources::class, 'id_synthese');
    }
}