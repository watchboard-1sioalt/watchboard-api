<?php

class Synthese extends Model
{
    protected $table = 'syntheses';
    protected $primaryKey = 'id_synthese';

    protected $fillable = ['synthese', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressource::class, 'id_synthese');
    }
}