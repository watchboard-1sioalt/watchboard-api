<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Syntheses;
use App\Models\Utilisateurs;
use App\Models\Tags;


class Ressources extends Model
{
    protected $table = 'ressources';
    protected $primaryKey = 'id_ressource';

    protected $fillable = [
        'type', 'resume', 'url',
        'nom_original', 'id_synthese', 'id_utilisateur',
    ];

    public function synthese(): BelongsTo
    {
        return $this->belongsTo(Syntheses::class, 'id_synthese');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'id_utilisateur');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tags::class,
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
