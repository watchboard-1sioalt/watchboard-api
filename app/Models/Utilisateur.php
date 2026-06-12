<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom', 'prenom', 'email',
        'password', 'validation', 'role',
    ];

    protected $hidden = ['password'];

    public function syntheses(): HasMany
    {
        return $this->hasMany(Synthese::class, 'id_utilisateur');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressource::class, 'id_utilisateur');
    }

    public function fluxRss(): HasMany
    {
        return $this->hasMany(FluxRss::class, 'id_utilisateur');
    }
}