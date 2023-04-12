<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIpBlock extends Model
{
    protected $table = 'USER_IP_BLOCK';

    protected $primaryKey = 'BLOCK_ID';

    public $timestamps = false;
}