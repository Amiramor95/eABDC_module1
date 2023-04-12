<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class GenerateColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:columns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate columns';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        include public_path('../modelGenerator.php');

        function find_line_number_by_string($controllersfile, $search, $case_sensitive = false)
        {

            $line_number = [];
            if ($file_handler = fopen(public_path('../app/Http/Controllers/' . $controllersfile . '.php'), "r")) {
                $i = 0;
                while ($line = fgets($file_handler)) {
                    $i++;
                    //case sensitive is false by default
                    if ($case_sensitive == false) {
                        $search = strtolower($search); //convert file and search string
                        $line = strtolower($line); //to lowercase
                    }
                    //find the string and store it in an array
                    if (strpos($line, $search) !== false) {
                        $line_number[] = $i;
                    }
                }
                fclose($file_handler);
            } else {
                return "File not exists, Please check the file path or filename";
            }
            //if no match found
            if (!empty($line_number)) {
                return json_encode($line_number);
            } else {
                return "No match found";
            }
        }

        foreach ($model as $key => $list) {

            $controllers = $list . 'Controller';
            $columnArray = array();

            $arr = preg_replace("([A-Z])", " $0", $list);
            $arr = explode(" ", trim($arr));

            $comma_separated = implode("_", $arr);
            $table = strtoupper($comma_separated);

            // $path = config('laradoc.basePath');
            $results = DB::select("DESCRIBE $table");

            $resultArray = json_decode(json_encode($results), true);
            // $d = json_decode($results,true);

            // print_r($resultArray);

            $countColumn = 0;
            foreach ($resultArray as $columnlist) {

                if ($columnlist['Key'] == 'PRI') {

                } else if ($columnlist['Key'] == 'MUL') {
                    $tableRef = substr($columnlist['Field'], 0, -3);

                    $columnArray[$countColumn]['Field'] = $columnlist['Field'];
                    $columnArray[$countColumn]['Type'] = $columnlist['Type'];
                    $columnArray[$countColumn]['Null'] = $columnlist['Null'];

                    $countColumn++;
                } else {
                    $columnArray[$countColumn]['Field'] = $columnlist['Field'];
                    $columnArray[$countColumn]['Type'] = $columnlist['Type'];
                    $columnArray[$countColumn]['Null'] = $columnlist['Null'];

                    $countColumn++;
                }

            }

            //change primary key
            $content2 = file(public_path('../app/Http/Controllers/' . $controllers . '.php'));

            foreach ($content2 as $line_num2 => $line2) {
                if (false === (strpos($line2, '$request->' . $list . '_ID'))) {
                    continue;
                }

                $content2[$line_num2] = "\t\t\t\$data = ".$list."::find(\$request->".$table."_ID); \n";

                // echo $line_num;
            }
            file_put_contents(public_path('../app/Http/Controllers/' . $controllers . '.php'), implode($content2));

            // $content2 = file(public_path('../app/Http/Controllers/'.$controllers.'.php'));

            $output = json_decode(find_line_number_by_string($controllers, 'all(), [ //fresh'), true);

            $content = file(public_path('../app/Http/Controllers/' . $controllers . '.php'));

            $count = 0;
            if (empty($output)) {

            } else {
                foreach ($output as $d) {

                    foreach ($content as $line_num => $line) {
                        if (false === (strpos($line, 'all(), [ //fresh'))) {
                            continue;
                        }

                        $content[$line_num] = "\$validator = Validator::make(\$request->all(), [ //" . $count . "\n";

                        break;
                        // echo $line_num;
                    }

                    // echo $count;
                    file_put_contents(public_path('../app/Http/Controllers/' . $controllers . '.php'), implode($content));
                    // break;
                    $count++;
                }
                $count = $count - 1;

                $config = public_path('../app/Http/Controllers/' . $controllers . '.php');

                $newline = "";

                $requestLine = "";

                $p = 0;

                // print_r($columnArray);
                foreach ($columnArray as $keya => $columnList) {
                    // print_r($aaa);
                    // print_r($columnList);

                    // foreach($columnList as $column) {
                    //     echo $column;
                    // }
                    // echo 'hi';
                    // print json_decode(json_encode($columnList),true);
                    $type = $columnArray[$p]['Type'];
                    $null = $columnArray[$p]['Null'];
                    $field = $columnArray[$p]['Field'];

                    if ($null === 'NO' && strpos($type, 'varchar') !== false) {
                        $var = 'required|string';
                    } else if ($null === 'NO' && strpos($type, 'int') !== false) {
                        $var = 'required|integer';
                    } else if ($null === 'YES' && strpos($type, 'varchar') !== false) {
                        $var = 'string|nullable';
                    } else if ($null === 'YES' && strpos($type, 'int') !== false) {
                        $var = 'integer|nullable';
                    }

                    // echo $var;
                    // $requestLine.= "'".$field[$p] . "' => '".$var."', \r\n";

                    if ($keya === array_key_last($columnArray)) {
                        $requestLine .= "\t\t\t'" . $field . "' => '" . $var . "' \r\n";
                    } else {
                        $requestLine .= "\t\t\t'" . $field . "' => '" . $var . "', \r\n";
                    }

                    $p++;
                }

                $insertPos = 0;

                for ($x = 0; $x <= $count; $x++) {
                    $file = fopen($config, "r+") or exit("Unable to open file!");
                    while (!feof($file)) {
                        $linex = fgets($file);
                        if (strpos($linex, "\$validator = Validator::make(\$request->all(), [ //" . $x) !== false) {
                            $insertPos = ftell($file);
                            $newline = $requestLine;
                            // echo $insertPos;
                        } else {
                            $newline .= $linex; // append existing data with new data of user
                        }
                    }
                    fseek($file, $insertPos); // move pointer to the file position where we saved above
                    fwrite($file, $newline);

                    fclose($file);
                }

                $content = file(public_path('../app/Http/Controllers/' . $controllers . '.php'));

                foreach ($content as $line_num => $line) {
                    if (false === (strpos($line, '$validator = Validator::make($request->all(), ['))) {
                        continue;
                    }

                    $content[$line_num] = "\$validator = Validator::make(\$request->all(), [ \n";

                    // echo $line_num;
                }

                // echo $count;
                file_put_contents(public_path('../app/Http/Controllers/' . $controllers . '.php'), implode($content));
            }

            // print "String(s) found in ".$output;

            // for ($x = 0; $x <= $i; $x++) {
            //     echo "The number is: $x <br>";
            // }
        }

        // print json_encode($columnArray,true);
    }
}
