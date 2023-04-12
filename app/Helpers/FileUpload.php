<?php

namespace App\Helpers;

use App\Models\DistributorDocument;
class FileUpload

{

    public function __construct()
    {

    }

    public function upload($parameter)
    {
        try {
            $file = $parameter;

            // Get the contents of the file
            $contents = $file->openFile()->fread($file->getSize());
        
            // Store the contents to the database
            $user = App\User::find($id);
            $user->avatar = $contents;
            $user->save();

        } catch (\Exception $e) {

            if ($e->getCode() == 401) {
                $response->expired = true;
            }
        }
    }

    public function uploadDistributorDocument($request)
    {

        // dd($request);
        try {
            $file = $request->file('image');
            // Get the contents of the file
            $contents = $file->openFile()->fread($file->getSize());
            // dd($contents);
            $doc = new DistributorDocument;
            $doc->DIST_ID = $request->DIST_ID;
            $doc->DOCU_FILETYPE = 'f';
            $doc->DOCU_FILENAME = 'fs';
            $doc->DOCU_BLOB = $contents;
            $doc->DOCU_DESCRIPTION = 'fs';
            $doc->save();

        } catch (\Exception $e) {
            // dd($e);
            if ($e->getCode() == 401) {
                $response->expired = true;
            }
        }
    }

    public function uploadBLOB($request)
    {
        try {
            $file = $request->file('image');
            // Get the contents of the file
            $contents = $file->openFile()->fread($file->getSize());
        
            $doc = new DistributorDocument;
            $doc->DIST_ID = '1';
            $doc->DOCU_FILETYPE = 'f';
            $doc->DOCU_FILENAME = 'fs';
            $doc->DOCU_BLOB = $contents;
            $doc->save();

        } catch (\Exception $e) {

            if ($e->getCode() == 401) {
                $response->expired = true;
            }
        }
    }
}