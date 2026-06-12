<?php

class FluxRss extends Model
{
    protected $table = 'flux_rss';
    protected $primaryKey = 'id_fluxrss';

    protected $fillable = ['url', 'id_utilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressource::class,
            'associer',
            'id_fluxrss',
            'id_ressource'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'appartenir',
            'id_fluxrss',
            'id_tag'
        );
    }
}