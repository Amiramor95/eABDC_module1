<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CandidateAcceptance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CANDIDATE_ACCEPTANCE';

    protected $primaryKey = 'CANDIDATE_ACCEPTANCE_ID';

    public $timestamps = false;

    protected $fillable = [
      'CANDIDATE_ACCETANCE_ID',
      'DISTRIBUTOR_ID',
      'REFERENCE_NO',
      'CREATE_BY',
      'TS_ID',
      'PUBLISH_STATUS'
    ];
}