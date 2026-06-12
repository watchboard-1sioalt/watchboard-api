<?php

class Tag extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id_tag';

    protected $fillable = ['tag'];

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressource::class,
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
