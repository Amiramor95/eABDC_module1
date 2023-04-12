<?php 

return [
  'name' => env('MODULE_NAME', ''),
  'desc' => env('MODULE_DESC', ''),
  'version' => env('MODULE_VERSION', '1.0.0'),
  'title' => env('MODULE_DOC_NAME', ''),
  'email' => env('DEV_EMAIL', 'araken@vn.my'),
  'host' => env('MODULE_HOST', 'localhost'),
  'basePath' => env('MODULE_PATH', '/api/module1'),
  'moduleJSON' => env('MODULE_API_DOC', 'module1.json')
];