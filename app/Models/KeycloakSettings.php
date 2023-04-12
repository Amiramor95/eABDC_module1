<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeycloakSettings extends Model
{
    protected $connection= 'mysql-setting';

    protected $table = 'KEYCLOAK_SETTINGS';

    public $timestamps =false;
    use HasFactory;

    protected $fillable = [
        'KEYCLOAK_TOKEN_URL',
        'KEYCLOAK_BASE_URL'
    ];
}
