<?php

namespace App\Manager;

use App\Models\DistributorAdditionalInfo;
use App\Models\DistributorTempAdditionalInfo;
use App\Models\DistributorDocument;
use App\Models\DistributorTempDocument;

class DistributorGetProfileUpdateDatas
{
    //Function Lists to get all Updated Datas of Distributors.

    //1. Get Tab 1 Datas - Distributor Profile
    public function copyUpdateProfileDataToDistributor($data, $distributorUpdateProfileData)
    {
        try{
            $fieldsMapped =  [
                'DIST_NAME',
                'DIST_REGI_NUM1',
                'DIST_REGI_NUM2',
                'DIST_TYPE_SETUP',
                'DIST_PHONE_NUMBER',
                'DIST_PHONE_EXTENSION',
                'DIST_MOBILE_NUMBER',
                'DIST_FAX_NUMBER',
                'DIST_EMAIL',
                'DIST_COMPANY_WEBSITE',
            ];

            foreach($fieldsMapped as $field) {
                if(!is_null($distributorUpdateProfileData->$field)){
                    $data->$field = $distributorUpdateProfileData->$field;
                }
            }
        }catch(\Exception $r){
            throw $r;
        }
    }

    //1. Get Tab 1 Datas - Addresses
    public function copyUpdateDistributorAddressData($data, $distributorUpdateAddressesData)
    {
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

            foreach($fieldsAddresses as $field) {
                if(!is_null($distributorUpdateAddressesData->$field) && $distributorUpdateAddressesData->$field!="-" && $distributorUpdateAddressesData->$field!=""){
                    $data->$field = $distributorUpdateAddressesData->$field;
                }
            }

        }catch(\Exception $r){
            throw $r;
        }
    }

    //2. Get Tab 2 Datas - Details Information
    public function copyUpdateDistributorDetailsData($data, $distributorUpdateDetailsData){
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

            foreach($fieldsDetails as $field){
                if(!is_null($distributorUpdateDetailsData->$field) && $distributorUpdateDetailsData->$field!="-" && $distributorUpdateDetailsData->$field!=""){
                    $data->$field = $distributorUpdateDetailsData->$field;
                }
            }
        }catch(\Exception $r){
            throw $r;
        }
    }

    // 3. Save Tab 3 Datas - CEO And Director
    //CEO
    public function copyUpdateDistributorCeoRprData($data, $distributorUpdateCeoRprData, $dataRepr){
        try{
            $fieldsRepresentatives =  [
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

            foreach($dataRepr as $item){
                if($item->REPR_TYPE == 'CEO'){
                    foreach($fieldsRepresentatives as $field){
                        if(!is_null($distributorUpdateCeoRprData->$field) && $distributorUpdateCeoRprData->$field!="-" && $distributorUpdateCeoRprData->$field!=""){
                            $item->$field = $distributorUpdateCeoRprData->$field;
                        }
                    }
                }
            }

            $data->DATAREPR = $dataRepr;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // 4. Get Tab 4 Datas - AR and AAR
    //AR
    public function copyUpdateDistributorArRprData($data, $distributorUpdateArRprData, $dataRepr){
        try{
            $fieldsRepresentativeAR =  [
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

            foreach($dataRepr as $item){
                if($item->REPR_TYPE == 'AR'){
                    foreach($fieldsRepresentativeAR as $field){
                        if(!is_null($distributorUpdateArRprData->$field) && $distributorUpdateArRprData->$field!="-" && $distributorUpdateArRprData->$field!=""){
                            $item->$field = $distributorUpdateArRprData->$field;
                        }
                    }
                }
            }

            $data->DATAREPR = $dataRepr;
        }catch(\Exception $r){
            throw $r;
        }
    }

    
    // AAR
    public function copyUpdateDistributorAarRprData($data, $distributorUpdateAarRprData, $dataRepr){
        try{
            $fieldsRepresentativeAAR =  [
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

            foreach($dataRepr as $item){
                if($item->REPR_TYPE == 'AAR'){
                    foreach($fieldsRepresentativeAAR as $field){
                        if(!is_null($distributorUpdateAarRprData->$field) && $distributorUpdateAarRprData->$field!="-" && $distributorUpdateAarRprData->$field!=""){
                            $item->$field = $distributorUpdateAarRprData->$field;
                        }
                    }
                }
            }

            $data->DATAREPR = $dataRepr;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // 5. Get Tab 5 Datas - HOD_COMPL, HOD_UTS, HOD_PRS and HOD_TRAIN
    // HOD_COMPL
    public function copyUpdateDistributorHOD_COMPLData($data, $distributorUpdateHOD_COMPLData, $dataAI){
        try{
            $fieldsHOD_COMPL =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            foreach($dataAI as $item){
                if($item->ADD_TYPE == 'HOD_COMPL'){
                    foreach($fieldsHOD_COMPL as $field){
                        if(!is_null($distributorUpdateHOD_COMPLData->$field) && $distributorUpdateHOD_COMPLData->$field!="-" && $distributorUpdateHOD_COMPLData->$field!=" "){
                            $item->$field = $distributorUpdateHOD_COMPLData->$field;
                        }
                    }
                }
            }

            $data->DATAAI = $dataAI;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // HOD_UTS
    public function copyUpdateDistributorHOD_UTSData($data, $distributorUpdateHOD_UTSData, $dataAI){
        try{
            $fieldsHOD_UTS =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            foreach($dataAI as $item){
                if($item->ADD_TYPE == 'HOD_UTS'){
                    foreach($fieldsHOD_UTS as $field){
                        if(!is_null($distributorUpdateHOD_UTSData->$field) && $distributorUpdateHOD_UTSData->$field!="-" && $distributorUpdateHOD_UTSData->$field!=""){
                            $item->$field = $distributorUpdateHOD_UTSData->$field;
                        }
                    }
                }
            }

            $data->DATAAI = $dataAI;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // HOD_PRS
    public function copyUpdateDistributorHOD_PRSData($data, $distributorUpdateHOD_PRSData, $dataAI){
        try{
            $fieldsHOD_PRS =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            foreach($dataAI as $item){
                if($item->ADD_TYPE == 'HOD_PRS'){
                    foreach($fieldsHOD_PRS as $field){
                        if(!is_null($distributorUpdateHOD_PRSData->$field) && $distributorUpdateHOD_PRSData->$field!="-" && $distributorUpdateHOD_PRSData->$field!=""){
                            $item->$field = $distributorUpdateHOD_PRSData->$field;
                        }
                    }
                }
            }

            $data->DATAAI = $dataAI;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // HOD_TRAIN
    public function copyUpdateDistributorHOD_TRAINData($data, $distributorUpdateHOD_TRAINData, $dataAI){
        try{
            $fieldsHOD_TRAIN =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            foreach($dataAI as $item){
                if($item->ADD_TYPE == 'HOD_TRAIN'){
                    foreach($fieldsHOD_TRAIN as $field){
                        if(!is_null($distributorUpdateHOD_TRAINData->$field) && $distributorUpdateHOD_TRAINData->$field!="-" && $distributorUpdateHOD_TRAINData->$field!=""){
                            $item->$field = $distributorUpdateHOD_TRAINData->$field;
                        }
                    }
                }
            }

            $data->DATAAI = $dataAI;
        }catch(\Exception $r){
            throw $r;
        }
    }

    // Get all Uploaded Updated Documents
    public function copyUpdatedUploadedDoc($user, $distributorUpdatedFormDoc, $dataDoc, $docGroup){
        try{
            foreach($dataDoc as $item){
                if($item->DOCU_GROUP == $docGroup){
                    $item->DOCU_MIMETYPE = $distributorUpdatedFormDoc->DOCU_MIMETYPE;
                    $item->DOCU_FILETYPE = $distributorUpdatedFormDoc->DOCU_FILETYPE;
                    $item->DOCU_ORIGINAL_NAME = $distributorUpdatedFormDoc->DOCU_ORIGINAL_NAME;
                    $item->DOCU_BLOB = $distributorUpdatedFormDoc->DOCU_BLOB ;
                    $item->DOCU_FILESIZE = $distributorUpdatedFormDoc->DOCU_FILESIZE;
                    $item->DOCU_GROUP = $distributorUpdatedFormDoc->DOCU_GROUP;
                }
            }

            $dataPrevDoc = DistributorDocument::where('DIST_ID', $user->USER_DIST_ID)
                ->where('DOCU_GROUP', $docGroup)
                ->first();
            if(!$dataPrevDoc){
                $dataDoc[] = $distributorUpdatedFormDoc;
            }
            foreach ($dataDoc as $element) {
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };

            $data['DATADOC'] = $dataDoc;
        }catch(\Exception $r){
            throw $r;
        }
    }
}