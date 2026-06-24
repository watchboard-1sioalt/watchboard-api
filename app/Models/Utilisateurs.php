<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ressource;
use App\Models\Parametres;
use App\Models\Synthese;
use App\Models\FluxRss;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['nom', 'prenom', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

class Utilisateurs extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom', 'prenom', 'email',
        'password', 'validation', 'admin',
    ];

    protected $hidden = ['password'];

    public function parametres(): HasMany
    {
        return $this->hasMany(Parametres::class, 'id_utilisateur');
    }

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




    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'admin' => $this->admin
            ];
    }
}