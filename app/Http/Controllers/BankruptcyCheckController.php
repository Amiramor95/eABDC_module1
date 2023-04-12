<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Ixudra\Curl\Facades\Curl;
use Validator;
use DB;

class BankruptcyCheckController extends Controller
{
    public function checkBankruptcy() {
        $response = curl::to('https://b2buat.experian.com.my/index.php/fimm/nrvb/')
        ->withHeader('')
        ->withAuthorization('Basic RklNTUIyQlBSUzpINmozUzc=')
        ->withContentType('application/xml')
        ->withdata('')
        ;

        $response = Http::withHeaders([
            'Authorization' => 'Basic RklNTUIyQlBSUzpINmozUzc=',
            'Cookie' => 'incap_ses_1136_2263877=9DSUS1PW50D5vsCjaOLDD0NVdmEAAAAAJwfs6VlGXHkfKOComQXf8g==; visid_incap_2263877=FiTAvxT0TpaBQU6AT8l6YNIzYWEAAAAAQUIPAAAAAAAFCzUb6qApPLqzRWcVyogT',
            'Content-Type' => 'application/xml'
        ])->send("POST", "https://b2buat.experian.com.my/index.php/fimm/nrvb/", [
            "body" => '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                            <soap:Body>
                                <SaveData xmlns="http://tempuri.org/">
                                    <pParam>
                                        Value
                                    </pParam>
                                </SaveData>
                            </soap:Body>
                        </soap:Envelope>'
        ]);
    }

}
