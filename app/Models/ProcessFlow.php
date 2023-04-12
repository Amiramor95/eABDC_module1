<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessFlow extends Model
{
    protected $connection = 'module0';

    protected $table = 'PROCESS_FLOW';

    protected $primaryKey = 'PROCESS_FLOW_ID';

    public $timestamps = false;
}
