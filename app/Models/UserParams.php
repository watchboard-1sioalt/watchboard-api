<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Utilisateurs;
use App\Models\Parametres;

class UserParams extends Model
{
    protected $primaryKey = 'id_parametre';
    protected $table = 'userParams';

    protected $fillable = ['value', 'id_utilisateur', 'id_parametre'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function parametres(): BelongsTo
    {
        return $this->belongsTo(Parametres::class, 'id_parametre');
    }
}