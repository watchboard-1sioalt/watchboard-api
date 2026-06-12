<?php

class Ressource extends Model
{
    protected $table = 'ressources';
    protected $primaryKey = 'id_ressource';

    protected $fillable = [
        'type', 'resume', 'url',
        'nom_original', 'id_synthese', 'id_utilisateur',
    ];

    public function synthese(): BelongsTo
    {
        return $this->belongsTo(Synthese::class, 'id_synthese');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'catégoriser',        // table pivot
            'id_ressource',
            'id_tag'
        );
    }

    public function fluxRss(): BelongsToMany
    {
        return $this->belongsToMany(
            FluxRss::class,
            'associer',
            'id_ressource',
            'id_fluxrss'
        );
    }
}
