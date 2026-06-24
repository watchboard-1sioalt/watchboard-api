<?php 
namespace App\Models;

class Parametres extends Model
{

    protected $table = 'parametres';
    protected $primaryKey = 'id_parametre';

    protected $fillable = ['name'];


}