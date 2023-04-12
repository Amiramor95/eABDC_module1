<?php

namespace App\Helpers;

use Config;
use App\Models\SmsLog;
use Storage;
use File;
use Response as Gambar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Image;

class Files//extends Model

{

    public function __construct()
    {

    }

    public function getFile($parameter)
    {
        try {
            $fileObject = $this->createFileObject(storage_path('app/public/event-document/85_1 ADMINISTRATOR MODULE 0.4.pdf'));
            return $fileObject;
          // dd($fileObject);

        } catch (\Exception $e) {

            if ($e->getCode() == 401) {
                $response->expired = true;
            }
        }
    }

    

    public function createFileObject($url){
  
        $path_parts = pathinfo($url);
        
        $imgInfo = getimagesize($url);
        $file = new UploadedFile(
            $url,
            $path_parts['basename'],
            $imgInfo['mime'],
            filesize($url),
            true,
            TRUE
        );
  
        return $file;
    }

    public function resizeImage($parameter)
    {
        try {
            $image_quality = 100;
            $max_size = 2000000;//parameterize
            $img = Image::make($parameter);
            
            for($image_quality = 100; Image::make($img)->filesize() > $max_size; $image_quality--){
                $img = Image::make($img)->stream($parameter->getClientOriginalExtension(), $image_quality);
            }
            return $img;

        } catch (\Exception $e) {

            if ($e->getCode() == 401) {
                // $response->expired = true;
            }
        }
    }
}
