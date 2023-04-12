<?php

namespace App\Manager;

use App\Models\DistributorAddress;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDirector;
use App\Models\DistributorAdditionalInfo;

use App\Models\DistributorTempAddress;
use App\Models\DistributorTempDetailInfo;
use App\Models\DistributorTempRepresentative;
use App\Models\DistributorTempDirector;
use App\Models\DistributorTempAdditionalInfo;

use App\Models\DistributorTempDocument;

class DistributorProfileUpdate
{
    //Function Lists to save all Updated Datas of Distributors.

    // 1. Save Tab 1 Datas - Addresses
    public function updateDistributorAddress($distributorUpdateProfile, $newData){
        try{
            $fieldsAddresses =  [
                'DIST_ADDR_1',
                'DIST_ADDR_2',
                'DIST_ADDR_3',
                'DIST_POSTAL',
                'DIST_CITY',
                'DIST_STATE',
                'DIST_COUNTRY',
                'DIST_BIZ_ADDR_1',
                'DIST_BIZ_ADDR_2',
                'DIST_BIZ_ADDR_3',
                'DIST_BIZ_POSTAL',
                'DIST_BIZ_CITY',
                'DIST_BIZ_STATE',
                'DIST_BIZ_COUNTRY',
            ];

            $oldDistributorAddress = DistributorAddress::where('DIST_ID', $newData['DISTRIBUTOR_ID'])->first();
            $distributorUpdateAddress = DistributorTempAddress::where('DIST_TEMP_ID', $distributorUpdateProfile->DIST_TEMP_ID)->first();
            if (!$distributorUpdateAddress) {
                $distributorUpdateAddress = new DistributorTempAddress;
            }
            $distributorUpdateAddress->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;

            foreach($fieldsAddresses as $field){
                if ($oldDistributorAddress->$field != $newData[$field]) {
                    $distributorUpdateAddress->$field = $newData[$field];
                }
            }
            $distributorUpdateAddress->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    //2. Save Tab 2 Datas - Details Information
    public function updateDistributorDetails($distributorUpdateProfile, $newData){
        try{
            $fieldsDetails =  [
                'DIST_PAID_UP_CAPITAL',
                'DIST_TYPE_STRUCTURE',
                'DIST_MARKETING_APPROACH',
                'DIST_NUM_DIST_POINT',
                'DIST_NUM_CONSULTANT',
                'DIST_INSURANCE',
                //'DIST_EXPIRED_DATE',
            ];

            $oldDistributorDetails = DistributorDetailInfo::where('DIST_ID', $newData['DISTRIBUTOR_ID'])->first();
            $distributorUpdateDetails = DistributorTempDetailInfo::where('DIST_TEMP_ID', $distributorUpdateProfile->DIST_TEMP_ID)->first();
            if (!$distributorUpdateDetails) {
                $distributorUpdateDetails = new DistributorTempDetailInfo;
            }
            $distributorUpdateDetails->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;

            foreach($fieldsDetails as $field){
                if ($oldDistributorDetails->$field != $newData[$field]) {
                    $distributorUpdateDetails->$field = $newData[$field];
                }
            }
            $distributorUpdateDetails->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // 3. Save Tab 3 Datas - CEO And Director
    public function updateDistributorCEOandDirector($distributorUpdateProfile, $newData){
        try{
            // Save CEO Details
            $fieldsRepresentativesCeo =  [
                //'REPR_TYPE',
                'REPR_SALUTATION',
                'REPR_NAME',
                'REPR_POSITION',
                'REPR_CITIZEN',
                'REPR_NRIC',
                'REPR_PASS_NUM',
                'REPR_PASS_EXP',
                'REPR_MOBILE_NUMBER',
                'REPR_TELEPHONE',
                'REPR_PHONE_EXTENSION',
                'REPR_EMAIL',
            ];

            //$newData['REPR_TYPE'] = $newData['CEO_REPR_TYPE'];
            $newData['REPR_SALUTATION'] = $newData['CEO_REPR_SALUTATION'];
            $newData['REPR_NAME'] = $newData['CEO_REPR_NAME'];
            $newData['REPR_POSITION'] = $newData['CEO_REPR_POSITION'];
            $newData['REPR_CITIZEN'] = $newData['CEO_REPR_CITIZEN'];
            $newData['REPR_NRIC'] = $newData['CEO_REPR_NRIC'];
            $newData['REPR_PASS_NUM'] = $newData['CEO_REPR_PASS_NUM'];
            $newData['REPR_PASS_EXP'] = $newData['CEO_REPR_PASS_EXP'];
            $newData['REPR_MOBILE_NUMBER'] = $newData['CEO_REPR_MOBILE_NUMBER'];
            $newData['REPR_TELEPHONE'] = $newData['CEO_REPR_TELEPHONE'];
            $newData['REPR_PHONE_EXTENSION'] = $newData['CEO_REPR_PHONE_EXTENSION'];
            $newData['REPR_EMAIL'] = $newData['CEO_REPR_EMAIL'];

            $oldDistributorRepresentatives = DistributorRepresentative::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'REPR_TYPE' => 'CEO'
            ])->first();

            $distributorUpdateRepresentativeCeo = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'REPR_TYPE' => 'CEO'
            ])->first();

            if (!$distributorUpdateRepresentativeCeo) {
                $distributorUpdateRepresentativeCeo = new DistributorTempRepresentative;
            }
            $distributorUpdateRepresentativeCeo->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateRepresentativeCeo->REPR_TYPE = $newData['CEO_REPR_TYPE'];

            foreach($fieldsRepresentativesCeo as $field){
                if ($oldDistributorRepresentatives->$field != $newData[$field]) {
                    $distributorUpdateRepresentativeCeo->$field = $newData[$field];
                }
            }
            $distributorUpdateRepresentativeCeo->save();

            // Save Directors Details
            $fieldsDirectors =  [
                'DIR_SALUTATION',
                'DIR_NAME',
                'DIR_NRIC',
                'DIR_PASS_NUM',
                'DIR_PASS_EXPIRY',
                'DIR_DATE_EFFECTIVE',
                'DIR_DATE_END',
            ];

            $directors = json_decode($newData['DIR_LIST']);

            foreach ($directors as $item) {
                $oldDistributorDirectorData = DistributorDirector::where('DIST_DIR_ID', $item->DIST_DIR_ID)->first();

                $distributorUpdateDirector= DistributorTempDirector::where('DIST_TEMP_ID', $distributorUpdateProfile->DIST_TEMP_ID)->first();
                if (!$distributorUpdateDirector) {
                    $distributorUpdateDirector = new DistributorTempDirector;
                }
                $distributorUpdateDirector->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;

                foreach($fieldsDirectors as $field){
                    if ($oldDistributorDirectorData->$field != $item->$field) {
                        $distributorUpdateDirector->$field = $item->$field;
                    }
                }
                $distributorUpdateDirector->save();
            }
            
            // foreach (json_decode($request->DELETED_DIR) as $element) {
            //     $deletedDir = DistributorDirector::find($element->DIST_DIR_ID);
            //     $deletedDir->delete();
            // }
        }catch(\Exception $r){
            throw $r;
        }
    }

    // 4. Save Tab 4 Datas - AR and AAR
    public function updateDistributorAR($distributorUpdateProfile, $newData){
        try{
            // Save AR Details
            $fieldsRepresentativesAR =  [
                //'REPR_TYPE',
                'REPR_SALUTATION',
                'REPR_NAME',
                'REPR_POSITION',
                'REPR_CITIZEN',
                'REPR_NRIC',
                'REPR_PASS_NUM',
                'REPR_PASS_EXP',
                'REPR_MOBILE_NUMBER',
                'REPR_TELEPHONE',
                'REPR_PHONE_EXTENSION',
                'REPR_EMAIL',
            ];

            //$newData['REPR_TYPE'] = $newData['AR_REPR_TYPE'];
            $newData['REPR_SALUTATION'] = $newData['AR_REPR_SALUTATION'];
            $newData['REPR_NAME'] = $newData['AR_REPR_NAME'];
            $newData['REPR_POSITION'] = $newData['AR_REPR_POSITION'];
            $newData['REPR_CITIZEN'] = $newData['AR_REPR_CITIZEN'];
            $newData['REPR_NRIC'] = $newData['AR_REPR_NRIC'];
            $newData['REPR_PASS_NUM'] = $newData['AR_REPR_PASS_NUM'];
            $newData['REPR_PASS_EXP'] = $newData['AR_REPR_PASS_EXP'];
            $newData['REPR_MOBILE_NUMBER'] = $newData['AR_REPR_MOBILE_NUMBER'];
            $newData['REPR_TELEPHONE'] = $newData['AR_REPR_TELEPHONE'];
            $newData['REPR_PHONE_EXTENSION'] = $newData['AR_REPR_PHONE_EXTENSION'];
            $newData['REPR_EMAIL'] = $newData['AR_REPR_EMAIL'];

            $oldDistributorRepresentativeAR = DistributorRepresentative::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'REPR_TYPE' => 'AR'
            ])->first();

            $distributorUpdateRepresentativeAR = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'REPR_TYPE' => 'AR'
            ])->first();

            if (!$distributorUpdateRepresentativeAR) {
                $distributorUpdateRepresentativeAR = new DistributorTempRepresentative;
            }
            $distributorUpdateRepresentativeAR->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateRepresentativeAR->REPR_TYPE = $newData['AR_REPR_TYPE'];

            foreach($fieldsRepresentativesAR as $field){
                if ($oldDistributorRepresentativeAR->$field != $newData[$field]) {
                    $distributorUpdateRepresentativeAR->$field = $newData[$field];
                }
            }
            $distributorUpdateRepresentativeAR->save();
        }catch(\Exception $r){
            throw $r;
        }
    }

    public function updateDistributorAAR($distributorUpdateProfile, $newData){
        try{
            // Save AAR Details
            $fieldsRepresentativesAAR =  [
                //'REPR_TYPE',
                'REPR_SALUTATION',
                'REPR_NAME',
                'REPR_POSITION',
                'REPR_CITIZEN',
                'REPR_NRIC',
                'REPR_PASS_NUM',
                'REPR_PASS_EXP',
                'REPR_MOBILE_NUMBER',
                'REPR_TELEPHONE',
                'REPR_PHONE_EXTENSION',
                'REPR_EMAIL',
            ];

            //$newData['REPR_TYPE'] = $newData['AAR_REPR_TYPE'];
            $newData['REPR_SALUTATION'] = $newData['AAR_REPR_SALUTATION'];
            $newData['REPR_NAME'] = $newData['AAR_REPR_NAME'];
            $newData['REPR_POSITION'] = $newData['AAR_REPR_POSITION'];
            $newData['REPR_CITIZEN'] = $newData['AAR_REPR_CITIZEN'];
            $newData['REPR_NRIC'] = $newData['AAR_REPR_NRIC'];
            $newData['REPR_PASS_NUM'] = $newData['AAR_REPR_PASS_NUM'];
            $newData['REPR_PASS_EXP'] = $newData['AAR_REPR_PASS_EXP'];
            $newData['REPR_MOBILE_NUMBER'] = $newData['AAR_REPR_MOBILE_NUMBER'];
            $newData['REPR_TELEPHONE'] = $newData['AAR_REPR_TELEPHONE'];
            $newData['REPR_PHONE_EXTENSION'] = $newData['AAR_REPR_PHONE_EXTENSION'];
            $newData['REPR_EMAIL'] = $newData['AAR_REPR_EMAIL'];

            $oldDistributorRepresentativeAAR = DistributorRepresentative::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'REPR_TYPE' => 'AAR'
            ])->first();

            $distributorUpdateRepresentativeAAR = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'REPR_TYPE' => 'AAR'
            ])->first();

            if (!$distributorUpdateRepresentativeAAR) {
                $distributorUpdateRepresentativeAAR = new DistributorTempRepresentative;
            }
            $distributorUpdateRepresentativeAAR->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateRepresentativeAAR->REPR_TYPE = $newData['AAR_REPR_TYPE'];

            foreach($fieldsRepresentativesAAR as $field){
                if ($oldDistributorRepresentativeAAR->$field != $newData[$field]) {
                    $distributorUpdateRepresentativeAAR->$field = $newData[$field];
                }
            }
            $distributorUpdateRepresentativeAAR->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // 5. Save Tab 5 Datas - HOD_COMPL, HOD_UTS, HOD_PRS and HOD_TRAIN

    // Save HOD_COMPL Details
    public function updateDistributorHOD_COMPL($distributorUpdateProfile, $newData){
        try{
            $fieldsHodCompl =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $newData['ADD_SALUTATION'] = $newData['COMPL_SALUTATION'];
            $newData['ADD_NAME'] = $newData['COMPL_NAME'];
            $newData['ADD_TELEPHONE'] = $newData['COMPL_TELEPHONE'];
            $newData['ADD_PHONE_EXTENSION'] = $newData['COMPL_PHONE_EXTENSION'];
            $newData['ADD_EMAIL'] = $newData['COMPL_EMAIL'];
            $newData['ADD_MOBILE_NUMBER'] = $newData['COMPL_MOBILE_NUMBER'];

            $oldDistributorHOD_COMPL = DistributorAdditionalInfo::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'ADD_TYPE' => $newData['COMPL_TYPE']
            ])->first();

            $distributorUpdateHOD_COMPL = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'ADD_TYPE' => $newData['COMPL_TYPE']
            ])->first();

            if (!$distributorUpdateHOD_COMPL) {
                $distributorUpdateHOD_COMPL = new DistributorTempAdditionalInfo;
            }
            $distributorUpdateHOD_COMPL->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateHOD_COMPL->ADD_TYPE = $newData['COMPL_TYPE'];

            foreach($fieldsHodCompl as $field){
                if ($oldDistributorHOD_COMPL->$field != $newData[$field]) {
                    $distributorUpdateHOD_COMPL->$field = $newData[$field];
                }
            }
            $distributorUpdateHOD_COMPL->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Save HOD_UTS Details
    public function updateDistributorHOD_UTS($distributorUpdateProfile, $newData){
        try{
            $fieldsHodUTC =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $newData['ADD_SALUTATION'] = $newData['UTS_SALUTATION'];
            $newData['ADD_NAME'] = $newData['UTS_NAME'];
            $newData['ADD_TELEPHONE'] = $newData['UTS_TELEPHONE'];
            $newData['ADD_PHONE_EXTENSION'] = $newData['UTS_PHONE_EXTENSION'];
            $newData['ADD_EMAIL'] = $newData['UTS_EMAIL'];
            $newData['ADD_MOBILE_NUMBER'] = $newData['UTS_MOBILE_NUMBER'];

            $oldDistributorHOD_UTS = DistributorAdditionalInfo::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'ADD_TYPE' => $newData['UTS_TYPE']
            ])->first();

            $distributorUpdateHOD_UTS = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'ADD_TYPE' => $newData['UTS_TYPE']
            ])->first();

            if (!$distributorUpdateHOD_UTS) {
                $distributorUpdateHOD_UTS = new DistributorTempAdditionalInfo;
            }
            $distributorUpdateHOD_UTS->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateHOD_UTS->ADD_TYPE = $newData['UTS_TYPE'];

            foreach($fieldsHodUTC as $field){
                if ($oldDistributorHOD_UTS->$field != $newData[$field]) {
                    $distributorUpdateHOD_UTS->$field = $newData[$field];
                }
            }
            $distributorUpdateHOD_UTS->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Save HOD_PRS Details
    public function updateDistributorHOD_PRS($distributorUpdateProfile, $newData){
        try{
            $fieldsHodPRS =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $newData['ADD_SALUTATION'] = $newData['PRS_SALUTATION'];
            $newData['ADD_NAME'] = $newData['PRS_NAME'];
            $newData['ADD_TELEPHONE'] = $newData['PRS_TELEPHONE'];
            $newData['ADD_PHONE_EXTENSION'] = $newData['PRS_PHONE_EXTENSION'];
            $newData['ADD_EMAIL'] = $newData['PRS_EMAIL'];
            $newData['ADD_MOBILE_NUMBER'] = $newData['PRS_MOBILE_NUMBER'];

            $oldDistributorHOD_PRS = DistributorAdditionalInfo::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'ADD_TYPE' => $newData['PRS_TYPE']
            ])->first();

            $distributorUpdateHOD_PRS = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'ADD_TYPE' => $newData['PRS_TYPE']
            ])->first();

            if (!$distributorUpdateHOD_PRS) {
                $distributorUpdateHOD_PRS = new DistributorTempAdditionalInfo;
            }
            $distributorUpdateHOD_PRS->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateHOD_PRS->ADD_TYPE = $newData['PRS_TYPE'];

            foreach($fieldsHodPRS as $field){
                if ($oldDistributorHOD_PRS->$field != $newData[$field]) {
                    $distributorUpdateHOD_PRS->$field = $newData[$field];
                }
            }
            $distributorUpdateHOD_PRS->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Save HOD_TRAIN Details
    public function updateDistributorHOD_TRAIN($distributorUpdateProfile, $newData){
        try{
            $fieldsHodTRAIN =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $newData['ADD_SALUTATION'] = $newData['TRAIN_SALUTATION'];
            $newData['ADD_NAME'] = $newData['TRAIN_NAME'];
            $newData['ADD_TELEPHONE'] = $newData['TRAIN_TELEPHONE'];
            $newData['ADD_PHONE_EXTENSION'] = $newData['TRAIN_PHONE_EXTENSION'];
            $newData['ADD_EMAIL'] = $newData['TRAIN_EMAIL'];
            $newData['ADD_MOBILE_NUMBER'] = $newData['TRAIN_MOBILE_NUMBER'];

            $oldDistributorHOD_TRAIN = DistributorAdditionalInfo::where([
                'DIST_ID'=> $newData['DISTRIBUTOR_ID'],
                'ADD_TYPE' => $newData['TRAIN_TYPE']
            ])->first();

            $distributorUpdateHOD_TRAIN = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorUpdateProfile->DIST_TEMP_ID,
                'ADD_TYPE' => $newData['TRAIN_TYPE']
            ])->first();

            if (!$distributorUpdateHOD_TRAIN) {
                $distributorUpdateHOD_TRAIN = new DistributorTempAdditionalInfo;
            }
            $distributorUpdateHOD_TRAIN->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $distributorUpdateHOD_TRAIN->ADD_TYPE = $newData['TRAIN_TYPE'];

            foreach($fieldsHodTRAIN as $field){
                if ($oldDistributorHOD_TRAIN->$field != $newData[$field]) {
                    $distributorUpdateHOD_TRAIN->$field = $newData[$field];
                }
            }
            $distributorUpdateHOD_TRAIN->save();

        }catch(\Exception $r){
            throw $r;
        }
    }


    // Upload Documents
    public function uploadDocumentFiles($docFile, $docGroup, $distributorUpdateProfile){
        try{
            DistributorTempDocument::where('DOCU_GROUP', $docGroup)->where('DIST_TEMP_ID', $distributorUpdateProfile->DIST_TEMP_ID)->delete();

            $content = $docFile->openFile()->fread($docFile->getSize()); //convert to blob
            $doc = new DistributorTempDocument;
            $doc->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;
            $doc->DOCU_MIMETYPE = $docFile->getMimeType();
            $doc->DOCU_FILETYPE = $docFile->getClientOriginalExtension();
            $doc->DOCU_ORIGINAL_NAME = $docFile->getClientOriginalName();
            $doc->DOCU_FILESIZE = $docFile->getSize();
            $doc->DOCU_GROUP = $docGroup;
            $doc->DOCU_BLOB = $content;
            $doc->save();
        }catch(\Exception $r){
            throw $r;
        }
    }

}