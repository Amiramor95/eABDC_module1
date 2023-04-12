<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Route;

class APIDocs
{
    public $swagger;
    public $info;
    public $host;
    public $basePath;
    public $tags;
    public $schemes;
    public $paths;
    public $definitions;
}
/**
 * Class generateDocs
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class GenerateDocs extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "generate:docs";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate docs";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        global $app;
        $success = true;
        $e = new APIDocs;
        $e->swagger = "2.0";
        $e->info['description'] = config('laradoc.desc');
        $e->info['version'] = config('laradoc.version');
        $e->info['title'] = config('laradoc.title');
        $e->info['contact']['email'] = config('laradoc.email');
        $e->host = config('laradoc.host');
        $e->basePath = config('laradoc.basePath');

        $tagArray = array();

        $e->definitions['errorUnauthorized']['properties']['message']['type'] = 'string';
        $e->definitions['errorUnauthorized']['properties']['message']['example'] = 'Unauthenticated user.';
        $e->definitions['errorUnauthorized']['properties']['errorCode']['type'] = 'integer';
        $e->definitions['errorUnauthorized']['properties']['errorCode']['format'] = 'int32';
        $e->definitions['errorUnauthorized']['properties']['errorCode']['example'] = 4002;

        $e->definitions['errorConnection']['properties']['message']['type'] = 'string';
        $e->definitions['errorConnection']['properties']['message']['example'] = 'Database connection error.';
        $e->definitions['errorConnection']['properties']['errorCode']['type'] = 'integer';
        $e->definitions['errorConnection']['properties']['errorCode']['format'] = 'int32';
        $e->definitions['errorConnection']['properties']['errorCode']['example'] = 4001;

        function strtoarray($a, $t = '')
        {
            $arr = [];
            $a = ltrim($a, '[');
            $a = ltrim($a, 'array(');
            $a = rtrim($a, ']');
            $a = rtrim($a, ')');
            $tmpArr = explode(",", $a);
            foreach ($tmpArr as $v) {
                if ($t == 'keys') {
                    $tmp = explode("=>", $v);
                    $k = $tmp[0];
                    $nv = $tmp[1];
                    $k = trim(trim($k), "'");
                    $k = trim(trim($k), '"');
                    $nv = trim(trim($nv), "'");
                    $nv = trim(trim($nv), '"');
                    $arr[$k] = $nv;
                } else {
                    $v = trim(trim($v), "'");
                    $v = trim(trim($v), '"');
                    $arr[] = $v;
                }
            }
            return $arr;
        }

        foreach (Route::getRoutes() as $route) {

            // echo json_encode($route);

            $example = array();
            // echo json_encode($route);
            $url = json_decode(json_encode($route->uri), true);
            if (str_contains($url, '_ignition')) {
                // echo 's';
            } else {
                $methods = json_decode(json_encode($route->methods), true);
                $tag = json_decode(json_encode($route->action['tag'] ?? 'undefined'), true);
                $as = json_decode(json_encode($route->action['as'] ?? 'undefined'), true);

                $tagArray[] = $tag;

                $methods = preg_replace("/[^A-Z]+/", "", $methods);

                $methods = strtolower($methods[0]);
                $data = json_decode(json_encode($route->action['controller']), true);

                $function = substr($data, strpos($data, "@") + 1);

                $arr = explode("@", $data, 2);
                // // // $homepage = file_get_contents($arr[0].'.php');

                $search = "public function " . $function . "(";
                $line_number = false;

                // $dd = getcwd();
                // echo $arr[0];
                $controllers = substr($arr[0], strrpos($arr[0], '\\') + 1);
                // echo $dd;
                // echo "aa";
                if ($handle = fopen(public_path('../app/Http/Controllers/' . $controllers . '.php'), "r")) {
                    $count = 0;
                    while (($line = fgets($handle, 4096)) !== false and !$line_number) {
                        $count++;
                        $line_number = (strpos($line, $search) !== false) ? $count : $line_number;
                    }
                    fclose($handle);
                }

                $requestLine = $line_number + 2;
                $nextLine = $line_number + 3;

                $lines = file(public_path('../app/Http/Controllers/' . $controllers . '.php'));

                $firstLine = $lines[$requestLine];

                $onError = true;
                $haveErrorReturn = false;

                $lineIncrementError = 1;
                $errorArray = null;

                while ($onError === true) {

                    if (isset($lines[$line_number + $lineIncrementError])) {

                        if (strpos($lines[$line_number + $lineIncrementError], 'public function')) {

                            $haveErrorReturn = false;
                            $onError = false;
                            break;
                        }
                        if (strpos($lines[$line_number + $lineIncrementError], 'http_response_code(400);')) {

                            $lineError = $line_number + $lineIncrementError;

                            $errorArray = '[';

                            $errorArray .= $lines[$lineError + 2];

                            $nextLineError = $lineError + 3;

                            while (!strpos($lines[$nextLineError], ']')) {

                                $errorArray .= $lines[$nextLineError];
                                $nextLineError++;

                            }

                            $errorArray .= ']';

                            $haveErrorReturn = true;

                        }
                        $lineIncrementError++;

                    } else {
                        $onError = false;
                        break;
                    }

                }

                $errorArr = strtoarray($errorArray);
                $countError = 0;

                $check = true;
                $haveSuccessReturn = false;
                //while loop check response 200

                //response
                if (!empty($errorArray)) {

                    // echo 'saa';
                    foreach ($errorArr as $dError) {
                        $eaError = $errorArr[$countError];

                        $requestError = explode("' => '", $eaError, 2);

                        if (1 === preg_match('~[0-9]~', $eaError)) {
                            $requestError = explode("' => ", $eaError, 2);
                        }

                        if ($requestError[0] === 'errorCode') {
                            $e->definitions[$tag . '_' . $function . '_error']['properties'][$requestError[0]]['type'] = 'integer';
                            $e->definitions[$tag . '_' . $function . '_error']['properties'][$requestError[0]]['format'] = 'int32';
                            $e->definitions[$tag . '_' . $function . '_error']['properties'][$requestError[0]]['example'] = preg_replace("/\r|\n/", "", $requestError[1]);

                        } else if ($requestError[0] === 'message') {
                            $e->definitions[$tag . '_' . $function . '_error']['properties'][$requestError[0]]['type'] = 'string';
                            $e->definitions[$tag . '_' . $function . '_error']['properties'][$requestError[0]]['example'] = preg_replace("/\r|\n/", "", $requestError[1]);

                        }

                        $countError++;
                    }
                }

                $lineIncrement = 1;
                $successArray = null;

                while ($check === true) {

                    if (isset($lines[$line_number + $lineIncrement])) {

                        if (strpos($lines[$line_number + $lineIncrement], 'public function')) {

                            $haveSuccessReturn = false;
                            $check = false;
                            break;
                        }
                        if (strpos($lines[$line_number + $lineIncrement], 'http_response_code(200);')) {

                            $lineSuccess = $line_number + $lineIncrement;

                            $successArray = '[';

                            $successArray .= $lines[$lineSuccess + 2];

                            $nextLineSuccess = $lineSuccess + 3;

                            while (!strpos($lines[$nextLineSuccess], ']);')) {

                                $successArray .= $lines[$nextLineSuccess];
                                $nextLineSuccess++;

                            }

                            $successArray .= ']';

                            $haveSuccessReturn = true;

                        }
                        $lineIncrement++;

                    } else {
                        $check = false;
                        break;
                    }

                }

                $successArr = strtoarray($successArray);
                $countSuccess = 0;

                //response
                if (!empty($successArray)) {

                    // echo 'saa';
                    foreach ($successArr as $dSuccess) {
                        $eaSuccess = $successArr[$countSuccess];

                        if (str_contains($eaSuccess, '$')) {
                            $requestSuccess = explode("' => ", $eaSuccess, 2);
                        } else {
                            $requestSuccess = explode("' => '", $eaSuccess, 2);
                        }

                        if (1 === preg_match('~[0-9]~', $eaSuccess)) {
                            $requestSuccess = explode("' => ", $eaSuccess, 2);
                        }

                        $e->definitions[$tag . '_' . $function . '_successData']['properties']['Success Data Array 1']['type'] = 'string';
                        $e->definitions[$tag . '_' . $function . '_successData']['properties']['Success Data Array 1']['example'] = 'Success Data Value Sample';

                        $e->definitions[$tag . '_' . $function . '_successData']['properties']['Success Data Array 2']['type'] = 'string';
                        $e->definitions[$tag . '_' . $function . '_successData']['properties']['Success Data Array 2']['example'] = 'Success Data Value Sample';

                        if ($requestSuccess[0] === 'message') {
                            $e->definitions[$tag . '_' . $function . '_success']['properties'][$requestSuccess[0]]['type'] = 'string';
                            $e->definitions[$tag . '_' . $function . '_success']['properties'][$requestSuccess[0]]['example'] = preg_replace("/\r|\n/", "", $requestSuccess[1]);

                        } else if ($requestSuccess[0] === 'data') {
                            $e->definitions[$tag . '_' . $function . '_success']['properties'][$requestSuccess[0]]['$ref'] = "#/definitions/" . $tag . '_' . $function . '_successData';
                        }

                        $countSuccess++;
                    }
                }
                //

                if (strpos($lines[$line_number + 1], '$validator = Validator::make($request->all()') !== false) {

                    $secondLine = $lines[$nextLine];

                    if (strpos($firstLine, '//') !== false) {
                        $example[] = explode('//', $firstLine, 2)[1];
                    } else {
                        $example[] = "";
                    }

                    $limit = 0;
                    $havemore = strpos($secondLine, ']);') !== false;

                    $bodyArray = array();

                    $array = '[';

                    if (strpos($firstLine, '//') !== false) {
                        $array .= explode('//', $firstLine, 2)[0];
                    } else {
                        $array .= $firstLine;
                    }

                    while (!strpos($lines[$nextLine], ']);') && $limit < 50) {

                        if (strpos($lines[$nextLine], '//') !== false) {
                            $example[] = explode('//', $lines[$nextLine], 2)[1];

                            $array .= explode('//', $lines[$nextLine], 2)[0];
                        } else {
                            $example[] = "";
                            $array .= $lines[$nextLine];
                        }
                        $nextLine++;

                        $limit++;
                    }

                    $array .= ']';
                    $ifcomma = substr($array, -2, 1);

                    // if($ifcomma == ','){

                    // }

                    $arr = strtoarray($array);

                    $count = 0;

                    $arrayParam = array();

                    foreach ($arr as $d) {

                        $ea = $arr[$count];

                        $request = explode("' => '", $ea, 2);
                        // print_r($request);
                        if (isset($request[1])) {
                            $pieces = explode("|", $request[1]);
                            // print_r($pieces);

                            if (in_array("required", $pieces)) {
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['type'] = 'string';
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['example'] = preg_replace("/\r|\n/", "", $example[$count]);
                            }

                            if (in_array("string", $pieces)) {

                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['type'] = 'string';
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['example'] = preg_replace("/\r|\n/", "", $example[$count]);
                            }

                            if (in_array("integer", $pieces)) {
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['type'] = 'integer';
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['format'] = 'int32';
                                $e->definitions[$tag . '_' . $function]['properties'][$request[0]]['example'] = preg_replace("/\r|\n/", "", $example[$count]);
                            }

                            if (in_array("file", $pieces)) {

                                $e->paths[$url][$methods]['consumes'][] = "multipart/form-data";

                                $e->paths[$url][$methods]['parameters'][0]['in'] = 'formData';
                                $e->paths[$url][$methods]['parameters'][0]['name'] = $request[0];
                                $e->paths[$url][$methods]['parameters'][0]['type'] = 'file';
                                $e->paths[$url][$methods]['parameters'][0]['description'] = 'The file to upload.';

                            } else {
                                $e->paths[$url][$methods]['consumes'][] = "application/json";
                            }
                            $count++;

                        } else {
                            echo 'error di controller :' . $tag . '_' . $function;
                            $success = false;
                            break;
                        }

                    }
                }
                $fullUrl = $url;
                // $url = '/'.substr($url, strrpos($url, '/') + 1);

                $last = explode("/", $url, 3);

                $url = '/' . $last[2];

                $e->paths[$url][$methods]['tags'][] = $tag;
                $e->paths[$url][$methods]['summary'] = $as;
                $e->paths[$url][$methods]['security'][0]['Bearer'] = [];

                $e->paths[$url][$methods]['produces'][] = "application/json";

                if ($methods == 'get') {
                    if (strpos($fullUrl, '{') !== false) {

                        preg_match_all('/{(.*?)}/', $fullUrl, $matches);

                        $p = 0;
                        foreach ($matches[1] as $pathname) {
                            $e->paths[$url][$methods]['parameters'][$p]['in'] = 'path';
                            $e->paths[$url][$methods]['parameters'][$p]['name'] = $pathname;

                            // $e->paths[$url][$methods]['parameters'][0]['description'] = '';
                            // $e->paths[$url][$methods]['parameters'][0]['required'] = true;
                            // $e->paths[$url][$methods]['parameters'][0]['schema']['$ref'] = "#/definitions/".$tag.'_'.$function;

                            $p++;
                        }
                    }
                } else {
                    $e->paths[$url][$methods]['parameters'][0]['in'] = 'body';
                    $e->paths[$url][$methods]['parameters'][0]['name'] = 'body';

                    $e->paths[$url][$methods]['parameters'][0]['description'] = '';
                    $e->paths[$url][$methods]['parameters'][0]['required'] = true;
                    $e->paths[$url][$methods]['parameters'][0]['schema']['$ref'] = "#/definitions/" . $tag . '_' . $function;

                }

                $e->paths[$url][$methods]['responses']['200']['description'] = 'Success';
                $e->paths[$url][$methods]['responses']['200']['schema']['$ref'] = "#/definitions/" . $tag . '_' . $function . '_success';
                $e->paths[$url][$methods]['responses']['400']['description'] = 'Bad Request';
                $e->paths[$url][$methods]['responses']['400']['schema']['$ref'] = "#/definitions/" . $tag . '_' . $function . '_error';

                $e->paths[$url][$methods]['responses']['401']['description'] = 'Unauthorized';
                $e->paths[$url][$methods]['responses']['401']['schema']['$ref'] = "#/definitions/errorUnauthorized";

                $e->paths[$url][$methods]['responses']['500']['description'] = 'Database Error';
                $e->paths[$url][$methods]['responses']['500']['schema']['$ref'] = "#/definitions/errorConnection";
                $e->definitions[$tag . '_' . $function]['type'] = 'object';
            }

        }

        $i = 0;

        foreach ($tagArray as $tags) {

            $e->tags[$i]['name'] = $tags;
            $e->tags[$i]['description'] = 'Info related to ' . $tags;
            $i++;
        }
        $e->schemes = ["http", "https"];
        $e->securityDefinitions['Bearer']['type'] = "apiKey";
        $e->securityDefinitions['Bearer']['name'] = "Authorization";
        $e->securityDefinitions['Bearer']['in'] = "header";

        // $e->paths['/userprofiles'] = "User Profile";

        $data = json_encode($e);

        $myfile = fopen(public_path('api-docs/' . config('laradoc.moduleJSON')), "w") or die("Unable to open file!");
        fwrite($myfile, $data);

        if($success){
            echo 'generate berjaya';
        }
    }
}
