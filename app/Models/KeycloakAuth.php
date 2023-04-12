<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeycloakAuth extends Model
{
    protected $connection= 'mysql-setting';

    protected $table = 'KEYCLOAK_AUTH';

    public $timestamps =false;
    use HasFactory;

}
