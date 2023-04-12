<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TaskStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'TASK_STATUS';

    protected $primaryKey = 'TS_ID';

    protected $connection = 'module0';

    protected $fillable = [
        'TS_PARAM',
        'TS_CODE',
        'TS_REMARK',
        'TS_DESCRIPTION',
        'TS_INDEX'
    ];

    public $timestamps = false;
}
