<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserParams extends Model
{
    protected $table = 'userParams';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_utilisateur', 'id_parametre', 'value_'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function parametre(): BelongsTo
    {
        return $this->belongsTo(Parametres::class, 'id_parametre');
    }
}
