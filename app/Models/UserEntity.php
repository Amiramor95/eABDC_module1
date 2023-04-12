<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEntity extends Model
{
    protected $connection = 'keyclock';

    protected $table = 'USER_ENTITY';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'EMAIL',
        'FIRST_NAME',
        'LAST_NAME',
        'REALM_ID',
        'USERNAME',
    ];
}
