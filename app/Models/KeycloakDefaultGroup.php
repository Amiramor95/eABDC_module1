<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeycloakDefaultGroup extends Model
{
    protected $connection= 'mysql-setting';

    protected $table = 'KEYCLOAK_DEFAULT_GROUP';

    public $timestamps =false;
    use HasFactory;

}
