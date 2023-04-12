<?php

namespace App\Imports;

use App\Models\Consultant;
use App\Models\DistributorScheme;
use App\Models\AcceptanceDetails;
use App\Models\AcceptanceDetailsRejected;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;

class AcceptanceUploadSheet implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    protected $acceptance_id;
    protected $company_id;

    public function __construct(int $acceptance_id, $company_id)
    {
        $this->acceptance_id = $acceptance_id;
        $this->company_id = $company_id;
    }
    /**
     * @param Collection $collection
     */

    public function collection(Collection $rows)
    {
        $accept = array();

        foreach ($rows as $row) {
            if (isset($row['name'])) {
                array_push($accept, $row);
                $nric = str_replace("-", "", $row['nric_number']);
                $phoneNo = str_replace("-", "", $row['phone_number']);

                //1. check distributor scheme with license candidate
                $distLicense = DB::table('distributor_management.DISTRIBUTOR_TYPE AS DM')
                ->select('AM.SCHEME')
                ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS AM', 'AM.DISTRIBUTOR_TYPE_ID', '=', 'DM.DIST_TYPE')
                ->where('DM.DIST_ID', $this->company_id)
                ->where('AM.SCHEME', $row['license_type'])
                ->get();

                if ($distLicense->first()) {

                    //2. check consultant record existed + active license

                    $checkConsultant = DB::table('consultant_management.CONSULTANT_LICENSE AS CS')
                    ->select('CS.CONSULTANT_TYPE_ID')
                    ->leftJoin('consultant_management.CONSULTANT AS C', 'C.CONSULTANT_ID', '=', 'CS.CONSULTANT_ID');

                    if ($nric !="" || $nric != null) {
                        $checkConsultant->where('C.CONSULTANT_NRIC', $nric);
                    }
                    if ($row['passport_number'] !="" || $row['passport_number'] != null) {
                        $checkConsultant->where('C.CONSULTANT_PASSPORT_NO', $row['passport_number']);
                    }

                    $checkConsultant->where('CS.CONSULTANT_TYPE_ID', $row['license_type'])
                    ->where('CS.TS_ID', '=', 3)
                    ->get();

                    if ($checkConsultant->first()) {

                        //if active consultant + existed license
                        AcceptanceDetailsRejected::create([
                            'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                            'CANDIDATE_NAME' => $row['name'],
                            'CANDIDATE_NRIC' => $nric,
                            'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                            'CANDIDATE_EMAIL' => $row['email'],
                            'CANDIDATE_PHONENO' => $phoneNo,
                            'LICENSE_TYPE' => $row['license_type'],
                            'STAFF_OR_AGENT' => $row['staff_or_agent'],
                            'REASON' => 'ACTIVE CONSULTANT',

                         ]);
                    } else {

                        //3.check if license status pending

                        $checkPending = DB::table('consultant_management.CONSULTANT_LICENSE AS CS')
                            ->select('CS.CONSULTANT_TYPE_ID')
                            ->leftJoin('consultant_management.CONSULTANT AS C', 'C.CONSULTANT_ID', '=', 'CS.CONSULTANT_ID');

                        if ($nric !="" || $nric != null) {
                            $checkPending->where('C.CONSULTANT_NRIC', $nric);
                        }
                        if ($row['passport_number'] !="" || $row['passport_number'] != null) {
                            $checkPending->where('C.CONSULTANT_PASSPORT_NO', $row['passport_number']);
                        }

                        $checkPending->where('CS.CONSULTANT_TYPE_ID', $row['license_type'])
                            ->where('CS.TS_ID', '=', 15)
                            ->get();

                        if ($checkPending->first()) {
                            AcceptanceDetails::create([
                                    'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                                    'CANDIDATE_NAME' => $row['name'],
                                    'CANDIDATE_NRIC' => $nric,
                                    'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                                    'CANDIDATE_EMAIL' => $row['email'],
                                    'CANDIDATE_PHONENO' => $phoneNo,
                                    'LICENSE_TYPE' => $row['license_type'],
                                    'STAFF_OR_AGENT' => $row['staff_or_agent'],
                                    'TS_ID' => 1,
                                    ]);
                        } else {

                                //4. check if selfregister without further action (here)





                            //5.check if consultant record existed with ca record

                            $queryCheckCA = DB::table('consultant_management.CONSULTANT AS C')
                                ->select('CRD.CA_CLASSIFICATION')
                                ->leftJoin('consultantAlert_management.CA_RECORD AS CR', 'CR.CONSULTANT_ID', '=', 'C.CONSULTANT_ID')
                                ->leftJoin('consultantAlert_management.CA_RECORD_DETAILS AS CRD', 'CRD.CA_RECORD_ID', '=', 'CR.CA_RECORD_ID');

                            if ($nric !="" || $nric != null) {
                                $queryCheckCA->where('C.CONSULTANT_NRIC', $nric);
                            }
                            if ($row['passport_number'] !="" || $row['passport_number'] != null) {
                                $queryCheckCA->where('C.CONSULTANT_PASSPORT_NO', $row['passport_number']);
                            }

                            $checkCA=$queryCheckCA->orderBy('CRD.CA_RECORD_DETAILS_ID', 'DESC')
                                ->first();

                            if ($checkCA) {

                                    //6. check if ca_classification is whitelist or watchlist

                                if ($checkCA->CA_CLASSIFICATION == 263 || $checkCA->CA_CLASSIFICATION == 269) {
                                    AcceptanceDetails::create([
                                            'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                                            'CANDIDATE_NAME' => $row['name'],
                                            'CANDIDATE_NRIC' => $nric,
                                            'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                                            'CANDIDATE_EMAIL' => $row['email'],
                                            'CANDIDATE_PHONENO' => $phoneNo,
                                            'LICENSE_TYPE' => $row['license_type'],
                                            'STAFF_OR_AGENT' => $row['staff_or_agent'],
                                            'CA_CLASSIFICATION' => $checkCA->CA_CLASSIFICATION,
                                            'TS_ID' => 1,
                                            ]);
                                } else {
                                    AcceptanceDetailsRejected::create([
                                            'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                                            'CANDIDATE_NAME' => $row['name'],
                                            'CANDIDATE_NRIC' => $nric,
                                            'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                                            'CANDIDATE_EMAIL' => $row['email'],
                                            'CANDIDATE_PHONENO' => $phoneNo,
                                            'LICENSE_TYPE' => $row['license_type'],
                                            'STAFF_OR_AGENT' => $row['staff_or_agent'],
                                            'REASON' => 'PLEASE CONTACT FIMM REGISTRATION DEPARTMENT',

                                        ]);
                                }
                            } else {
                                AcceptanceDetails::create([
                                    'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                                    'CANDIDATE_NAME' => $row['name'],
                                    'CANDIDATE_NRIC' => $nric,
                                    'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                                    'CANDIDATE_EMAIL' => $row['email'],
                                    'CANDIDATE_PHONENO' => $phoneNo,
                                    'LICENSE_TYPE' => $row['license_type'],
                                    'STAFF_OR_AGENT' => $row['staff_or_agent'],
                                    'TS_ID' => 1,
                                    ]);
                            }
                        }
                    }
                } else {
                    AcceptanceDetailsRejected::create([
                        'CANDIDATE_ACCEPTANCE_ID' => $this->acceptance_id,
                        'CANDIDATE_NAME' => $row['name'],
                        'CANDIDATE_NRIC' => $nric,
                        'CANDIDATE_PASSPORT_NO' => $row['passport_number'],
                        'CANDIDATE_EMAIL' => $row['email'],
                        'CANDIDATE_PHONENO' => $phoneNo,
                        'LICENSE_TYPE' => $row['license_type'],
                        'STAFF_OR_AGENT' => $row['staff_or_agent'],
                        'REASON' => 'WRONG LICENSE TYPE',

                     ]);
                }
            }
        }
    }
}
