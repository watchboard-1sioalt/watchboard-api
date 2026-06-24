<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametres extends Model
{
    protected $table = 'parametres';
    protected $primaryKey = 'id_parametre';

    protected $fillable = ['name'];
}
