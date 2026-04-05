<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'usuarios';
    protected $fillable = ['nombre','email','password','nombre_finca','departamento','municipio','telefono'];
    protected $hidden   = ['password'];
}
