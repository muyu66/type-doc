<?php

namespace Typedoc;

error_reporting(E_WARNING);

require_once __DIR__ . '/../vendor/autoload.php';

$data = new Data(
    ['User'],
    ['User'],
    __DIR__ . '/../../hrm_api/tests/apis/'
);

//$data = new Data(
//    ['UserController'],
//    ['User'],
//    __DIR__ . '/../../hrm_api/api/controllers/'
//);

dump($data->get());


function write($content, $file)
{
    $myfile = fopen($file, "w");
    fwrite($myfile, $content);
    fclose($myfile);
}