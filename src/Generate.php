<?php

namespace Typedoc;

error_reporting(E_WARNING);

require_once __DIR__ . '/../vendor/autoload.php';

createData();

function createData()
{
    $classes = getClasses(__DIR__ . '/../../hrm_api/tests/apis/');
    $ctls = getClasses(__DIR__ . '/../../hrm_api/tests/apis/', 1);

    $data_api = new Data(
        $ctls,
        $classes,
        __DIR__ . '/../../hrm_api/api/controllers/'
    );
    $apis = $data_api->getApi();

    $data_route = new Data(
        ['Route'],
        ['Route'],
        __DIR__ . '/../../hrm_api/api/routes/'
    );
    $routes = $data_route->getRoute();

    $data_test = new Data(
        $classes,
        $classes,
        __DIR__ . '/../../hrm_api/tests/apis/'
    );
    $tests = $data_test->getTest();

    $data_model = new Data(
        $classes,
        $classes,
        __DIR__ . '/../../hrm_api/api/models/'
    );
    $models = $data_model->getModel();

    $datas = [];
    foreach ($classes as $class) {
        $data['class_name'] = $class;
        $data['class_desc'] = $apis[$class]['desc']['class'][0];
        $data['method_name'] = $apis[$class]['method'];
        $data['method_desc'] = $apis[$class]['desc']['method'];
        $data['method_param_name'] = $apis[$class]['param'];
        $data['method_param_type'] = $apis[$class]['param'];
        $data['method_param_require'] = '';
        $data['method_param_desc'] = $apis[$class]['param'];
        $data['method_res_code'] = $tests[$class]['res_code'];
        $data['method_res_example'] = 'sdadasd';
        $data['method_res_model'] = 'asdasdda';
        $data['method_http_type'] = $tests[$class]['http_method'];
        $data['method_http_url'] = $tests[$class]['http_url'];
        $datas[] = $data;
    }

    createTheme($datas);
}

function createTheme($datas)
{
    $theme = new Theme();
    $content = $theme->useTheme($datas, 'Swagger');
    write($content, 'a.html');
}

function write($content, $file)
{
    $myfile = fopen($file, "w");
    fwrite($myfile, $content);
    fclose($myfile);
}

function getClasses($dir, $need_controller = 0)
{
    $files = scandir($dir);
    array_shift($files);
    array_shift($files);
    foreach ($files as $k => $file) {
        if ($need_controller) {
            $files[$k] = str_replace('.ts', 'Controller', $file);
        } else {
            $files[$k] = str_replace('.ts', '', $file);
        }
    }
    return $files;
}