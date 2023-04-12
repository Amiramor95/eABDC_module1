<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BankruptcySearch extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'BANKRUPTCY_SEARCH';

    protected $primaryKey = 'BANKRUPTCY_SEARCH_ID';

    public $timestamps = false;
}