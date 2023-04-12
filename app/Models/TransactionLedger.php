<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TransactionLedger  extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'TRANSACTION_LEDGER';

    protected $primaryKey = 'LEDGER_ID';

    public $timestamps = false;
    protected $connection = "finance_management";
}
