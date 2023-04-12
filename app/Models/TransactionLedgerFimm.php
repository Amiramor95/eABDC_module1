<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TransactionLedgerFimm   extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'TRANSACTION_LEDGER_FIMM';

    protected $primaryKey = 'LEDGER_ID_FIMM';

    public $timestamps = false;
    protected $connection = "finance_management";
}
