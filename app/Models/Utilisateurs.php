<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Utilisateurs extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom', 'prenom', 'email',
        'password', 'validation', 'admin',
    ];

    protected $hidden = ['password'];

    public function tags(): HasMany
    {
        return $this->hasMany(Tags::class, 'id_utilisateur');
    }

    public function fluxRss(): HasMany
    {
        return $this->hasMany(FluxRss::class, 'id_utilisateur');
    }

    public function syntheses(): HasMany
    {
        return $this->hasMany(Syntheses::class, 'id_utilisateur');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressources::class, 'id_utilisateur');
    }

    public function ressourcesPartagees(): BelongsToMany
    {
        return $this->belongsToMany(
            Ressources::class,
            'partager',
            'id_utilisateur',
            'id_ressource'
        );
    }

    public function parametres(): BelongsToMany
    {
        return $this->belongsToMany(
            Parametres::class,
            'userParams',
            'id_utilisateur',
            'id_parametre'
        )->withPivot('value_');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'validation' => 'boolean',
            'admin' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'admin' => $this->admin,
        ];
    }
}
