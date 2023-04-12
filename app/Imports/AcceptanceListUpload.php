<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Imports\AcceptanceUploadSheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;



class AcceptanceListUpload implements WithMultipleSheets, SkipsUnknownSheets
{
    protected $acceptance_id;
    protected $company_id;

    public function __construct(int $acceptance_id, $company_id)
    {
       
        $this->acceptance_id = $acceptance_id;
        $this->company_id = $company_id;

    }

  
    
    public function sheets(): array
    {
        
        return [ 
             //indexing start dari 0
            1 => new AcceptanceUploadSheet($this->acceptance_id, $this->company_id),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
