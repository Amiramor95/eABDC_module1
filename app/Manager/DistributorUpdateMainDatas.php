<?php

namespace App\Manager;

use App\Models\Distributor;
use App\Models\DistributorAddress;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDirector;
use App\Models\DistributorAdditionalInfo;

use App\Models\DistributorTemp;
use App\Models\DistributorTempAddress;
use App\Models\DistributorTempDetailInfo;
use App\Models\DistributorTempRepresentative;
use App\Models\DistributorTempDirector;
use App\Models\DistributorTempAdditionalInfo;

class DistributorUpdateMainDatas
{
    //Function Lists to save all Updated Datas of Distributors.

    // 1. Update Tab 1 Datas - Main Info and Addresses
    public function updateDistributorMainInfoAdress($distributorTempData){
        try{
            $fieldsProfile =  [
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

            $oldDistributorInfo= Distributor::where('DISTRIBUTOR_ID', $distributorTempData['DIST_ID'])->first();
            $oldDistributorAddress = DistributorAddress::where('DIST_ID', $distributorTempData['DIST_ID'])->first();

            $distributorUpdatedInfo = DistributorTemp::where('DIST_TEMP_ID', $distributorTempData->DIST_TEMP_ID)->first();
            $distributorUpdatedAddress = DistributorTempAddress::where('DIST_TEMP_ID', $distributorTempData->DIST_TEMP_ID)->first();

            //Update Distributor Info in Main Table
            foreach($fieldsProfile as $field){
                if ($oldDistributorInfo->$field != $distributorUpdatedInfo->$field) {
                    if(!is_null($distributorUpdatedInfo->$field) && $distributorUpdatedInfo->$field!="-" && $distributorUpdatedInfo->$field!=""){
                        $oldDistributorInfo->$field= $distributorUpdatedInfo->$field;
                    }
                }
            }
            $oldDistributorInfo->save();

            //Update Distributor Address in Main Table
            foreach($fieldsAddresses as $field){
                if ($oldDistributorAddress->$field != $distributorUpdatedAddress->$field) {
                    if(!is_null($distributorUpdatedAddress->$field) && $distributorUpdatedAddress->$field!="-" && $distributorUpdatedAddress->$field!=""){
                        $oldDistributorAddress->$field = $distributorUpdatedAddress->$field;
                    }
                }
            }
            $oldDistributorAddress->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // 2. Update Tab 2 Datas - Main Details Information
    public function updateDistributorMainDetails($distributorTempData){
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

            $oldDistributorDetails = DistributorDetailInfo::where('DIST_ID', $distributorTempData['DIST_ID'])->first();
            $distributorUpdatedDetails = DistributorTempDetailInfo::where('DIST_TEMP_ID', $distributorTempData['DIST_TEMP_ID'])->first();

            foreach($fieldsDetails as $field){
                if ($oldDistributorDetails->$field != $distributorUpdatedDetails->$field) {
                    if(!is_null($distributorUpdatedDetails->$field) && $distributorUpdatedDetails->$field!="-" && $distributorUpdatedDetails->$field!=""){
                        $oldDistributorDetails->$field = $distributorUpdatedDetails->$field;
                    }
                }
            }
            $oldDistributorDetails->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // 3. Update Tab 3 Datas - Main CEO And Director
    public function updateDistributorMainCEOandDirector($distributorTempData){
        try{
            // Update CEO Details
            $fieldsRepresentativesCeo =  [
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

            $oldDistributorRepresentativeCeo = DistributorRepresentative::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'REPR_TYPE' => 'CEO'
            ])->first();

            $distributorUpdatedRepresentativeCeo = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'REPR_TYPE' => 'CEO'
            ])->first();

            foreach($fieldsRepresentativesCeo as $field){
                if ($oldDistributorRepresentativeCeo->$field != $distributorUpdatedRepresentativeCeo->$field) {
                    if(!is_null($distributorUpdatedRepresentativeCeo->$field) && $distributorUpdatedRepresentativeCeo->$field!="-" && $distributorUpdatedRepresentativeCeo->$field!=""){
                        $oldDistributorRepresentativeCeo->$field = $distributorUpdatedRepresentativeCeo->$field;
                    }
                }
            }
            $oldDistributorRepresentativeCeo->save();

            // Update Directors Details
            // $fieldsDirectors =  [
            //     'DIR_SALUTATION',
            //     'DIR_NAME',
            //     'DIR_NRIC',
            //     'DIR_PASS_NUM',
            //     'DIR_PASS_EXPIRY',
            //     'DIR_DATE_EFFECTIVE',
            //     'DIR_DATE_END',
            // ];

            // $directors = json_decode($newData['DIR_LIST']);

            // foreach ($directors as $item) {
            //     $oldDistributorDirectorData = DistributorDirector::where('DIST_DIR_ID', $item->DIST_DIR_ID)->first();

            //     $distributorUpdateDirector= DistributorTempDirector::where('DIST_TEMP_ID', $distributorUpdateProfile->DIST_TEMP_ID)->first();
            //     if (!$distributorUpdateDirector) {
            //         $distributorUpdateDirector = new DistributorTempDirector;
            //     }
            //     $distributorUpdateDirector->DIST_TEMP_ID = $distributorUpdateProfile->DIST_TEMP_ID;

            //     foreach($fieldsDirectors as $field){
            //         if ($oldDistributorDirectorData->$field != $item->$field) {
            //             $distributorUpdateDirector->$field = $item->$field;
            //         }
            //     }
            //     $distributorUpdateDirector->save();
            // }
        }catch(\Exception $r){
            throw $r;
        }
    }

    // 4. Update Tab 4 Datas - AR and AAR
    public function updateDistributorMainAR($distributorTempData){
        try{
            // Update AR Details
            $fieldsRepresentativesAR =  [
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

            $oldDistributorRepresentativeAR = DistributorRepresentative::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'REPR_TYPE' => 'AR'
            ])->first();

            $distributorUpdatedRepresentativeAR = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'REPR_TYPE' => 'AR'
            ])->first();

            foreach($fieldsRepresentativesAR as $field){
                if ($oldDistributorRepresentativeAR->$field != $distributorUpdatedRepresentativeAR->$field) {
                    if(!is_null($distributorUpdatedRepresentativeAR->$field) && $distributorUpdatedRepresentativeAR->$field!="-" && $distributorUpdatedRepresentativeAR->$field!=""){
                        $oldDistributorRepresentativeAR->$field = $distributorUpdatedRepresentativeAR->$field;
                    }
                }
            }
            $oldDistributorRepresentativeAR->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    public function updateDistributorMainAAR($distributorTempData){
        try{
            // Update AAR Details
            $fieldsRepresentativesAAR =  [
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

            $oldDistributorRepresentativeAAR = DistributorRepresentative::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'REPR_TYPE' => 'AAR'
            ])->first();

            $distributorUpdatedRepresentativeAAR = DistributorTempRepresentative::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'REPR_TYPE' => 'AAR'
            ])->first();

            foreach($fieldsRepresentativesAAR as $field){
                if ($oldDistributorRepresentativeAAR->$field != $distributorUpdatedRepresentativeAAR->$field) {
                    if(!is_null($distributorUpdatedRepresentativeAAR->$field) && $distributorUpdatedRepresentativeAAR->$field!="-" && $distributorUpdatedRepresentativeAAR->$field!=""){
                        $oldDistributorRepresentativeAAR->$field = $distributorUpdatedRepresentativeAAR->$field;
                    }
                }
            }
            $oldDistributorRepresentativeAAR->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // 5. Update Tab 5 Datas - HOD_COMPL, HOD_UTS, HOD_PRS and HOD_TRAIN
    // Update HOD_COMPL Details
    public function updateDistributorMainHOD_COMPL($distributorTempData){
        try{
            $fieldsHodCompl =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $oldDistributorHOD_COMPL = DistributorAdditionalInfo::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'ADD_TYPE' => 'HOD_COMPL'
            ])->first();

            $distributorUpdatedHOD_COMPL = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'ADD_TYPE' => 'HOD_COMPL'
            ])->first();

            foreach($fieldsHodCompl as $field){
                if ($oldDistributorHOD_COMPL->$field != $distributorUpdatedHOD_COMPL->$field) {
                    if(!is_null($distributorUpdatedHOD_COMPL->$field) && $distributorUpdatedHOD_COMPL->$field!="-" && $distributorUpdatedHOD_COMPL->$field!=""){
                        $oldDistributorHOD_COMPL->$field = $distributorUpdatedHOD_COMPL->$field;
                    }
                }
            }
            $oldDistributorHOD_COMPL->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Update HOD_UTS Details
    public function updateDistributorMainHOD_UTS($distributorTempData){
        try{
            $fieldsHodUTC =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $oldDistributorHOD_UTS = DistributorAdditionalInfo::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'ADD_TYPE' => 'HOD_UTS'
            ])->first();

            $distributorUpdatedHOD_UTS = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'ADD_TYPE' => 'HOD_UTS'
            ])->first();

            foreach($fieldsHodUTC as $field){
                if ($oldDistributorHOD_UTS->$field != $distributorUpdatedHOD_UTS->$field) {
                    if(!is_null($distributorUpdatedHOD_UTS->$field) && $distributorUpdatedHOD_UTS->$field!="-" && $distributorUpdatedHOD_UTS->$field!=""){
                        $oldDistributorHOD_UTS->$field = $distributorUpdatedHOD_UTS->$field;
                    }
                }
            }
            $oldDistributorHOD_UTS->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Update HOD_PRS Details
    public function updateDistributorMainHOD_PRS($distributorTempData){
        try{
            $fieldsHodPRS =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $oldDistributorHOD_PRS = DistributorAdditionalInfo::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'ADD_TYPE' => 'HOD_PRS'
            ])->first();

            $distributorUpdatedHOD_PRS = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'ADD_TYPE' => 'HOD_PRS'
            ])->first();

            foreach($fieldsHodPRS as $field){
                if ($oldDistributorHOD_PRS->$field != $distributorUpdatedHOD_PRS->$field) {
                    if(!is_null($distributorUpdatedHOD_PRS->$field) && $distributorUpdatedHOD_PRS->$field!="-" && $distributorUpdatedHOD_PRS->$field!=""){
                        $oldDistributorHOD_PRS->$field = $distributorUpdatedHOD_PRS->$field;
                    }
                }
            }
            $oldDistributorHOD_PRS->save();

        }catch(\Exception $r){
            throw $r;
        }
    }

    // Update HOD_TRAIN Details
    public function updateDistributorMainHOD_TRAIN($distributorTempData){
        try{
            $fieldsHodTRAIN =  [
                'ADD_SALUTATION',
                'ADD_NAME',
                'ADD_TELEPHONE',
                'ADD_PHONE_EXTENSION',
                'ADD_EMAIL',
                'ADD_MOBILE_NUMBER',
            ];

            $oldDistributorHOD_TRAIN = DistributorAdditionalInfo::where([
                'DIST_ID'=> $distributorTempData['DIST_ID'],
                'ADD_TYPE' => 'HOD_TRAIN'
            ])->first();

            $distributorUpdatedHOD_TRAIN = DistributorTempAdditionalInfo::where([
                'DIST_TEMP_ID'=> $distributorTempData['DIST_TEMP_ID'],
                'ADD_TYPE' => 'HOD_TRAIN'
            ])->first();

            foreach($fieldsHodTRAIN as $field){
                if ($oldDistributorHOD_TRAIN->$field != $distributorUpdatedHOD_TRAIN->$field) {
                    if(!is_null($distributorUpdatedHOD_TRAIN->$field) && $distributorUpdatedHOD_TRAIN->$field!="-" && $distributorUpdatedHOD_TRAIN->$field!=""){
                        $oldDistributorHOD_TRAIN->$field = $distributorUpdatedHOD_TRAIN->$field;
                    }
                }
            }
            $oldDistributorHOD_TRAIN->save();

        }catch(\Exception $r){
            throw $r;
        }
    }
}