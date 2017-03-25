<?php

error_reporting(E_WARNING);

$class = new DocGenerate(date("Y/m/d H:m:i"));
$class->generate();

class DocGenerate
{
    private $publish_date;
    private $update_lock = '/tmp/hrm_api_doc_generate.lock';

    public function __construct($publish_date)
    {
        $this->publish_date = $publish_date;
    }

    public function generate()
    {
        $tests = $this->anaylseTest();
        $apis = ($this->anaylseApi($tests['classes']));
        $arrays = [];
        $content = ''; // API 正式接口，API.md
        $content_update = ''; // API 更新接口，API_UPDATE.md

        // 组合 api 和 test 文件
        foreach ($tests['classes'] as $class_name) {
            if (is_array(($apis[$class_name]))) {
                $arrays[$class_name] = array_merge($tests['contents'][$class_name], $apis[$class_name]);
            }
        }

        // 拼接头部
        $content .= $this->addHeader();
        $content_update .= $this->rowTitle(Date('Y-m-d H:m:i', time()));

//        // 拼接索引
//        foreach ($arrays as $class_name => $array) {
//            $content .= $this->rowIndex($array['top'], $class_name);
//        }

        // 拼接正文
        foreach ($arrays as $class_name => $array) {
            // 以类为单位
            $content .= $this->rowTitle($array['top'] . ' ' . $class_name);

            // 以方法为单位
            foreach ($array['method'] as $method) {
                // 跳过没有注释的方法
                if (!$array['desc'][$method]) {
                    continue;
                }

                // Node 时间修正
                $update_time = substr($array['update_time'][$method] ?: '0000', 0, -3);

                // 需要写进更新日志里的
                if ($update_time >= $this->read($this->update_lock)) {
                    $content_update .= $this->rowTitle($array['top'] . ' ' . $class_name);
                    $content_update .= $this->toMdMethod($method, $array['url'][$method], $array['http_method'][$method],
                        $array['desc'][$method], $array['status_code'][$method], $array['req_format'][$method],
                        $array['req_params'][$method], $array['res_format'][$method], $array['req_params'][$method]);
                }

                $content .= $this->toMdMethod($method, $array['url'][$method], $array['http_method'][$method],
                    $array['desc'][$method], $array['status_code'][$method], $array['req_format'][$method],
                    $array['req_params'][$method], $array['res_format'][$method], $array['req_params'][$method]);
            }
        }

        $this->write($content, 'API.md');
        $this->write($content_update . $this->read('API_UPDATE.md'), 'API_UPDATE.md');

        // 锁定更新日期
        $this->write(time(), $this->update_lock);

        echo '生成文档 ./API.md ./API_UPDATE.md 成功' . PHP_EOL;
    }

    private function toMdMethod($method, $url, $http_method, $desc, $status_code, $req_format, $req_params,
                                $res_format, $res_params)
    {
        $res = $this->rowChapter($method) .
            $this->rowUrl($url) .
            $this->rowText('请求') .
            $this->rowQuote($http_method) .
            $this->rowText('响应') .
            $this->rowQuote($status_code) .
            $this->rowText($desc);

        $res = $this->toMdCode($req_format, $req_params, $res, 'Resquest');
        $res = $this->toMdCode($res_format, $res_params, $res, 'Response');

        return $res;
    }

    private function toMdCode($format, $params, $res, $type)
    {
        $res_tmp = '';
        if (is_array($params)) {
            foreach ($params as $param) {
                $res_tmp = $res_tmp . $this->rowText('	        ' . $param);
            }
        } elseif (empty($params)) {

        }

        $res = $res
            . $this->rowCode("$type ($format)")
            . $this->rowBrackets(0)
            . $res_tmp;

        return $res . $this->rowBrackets(1);
    }

    private function addHeader()
    {
        return $this->rowTitle('HRM API 接口 (Alpha 1)') .
            $this->rowTag($this->publish_date) .
            $this->rowSubject('测试接口 url') .
            $this->rowQuote('http://103.36.173.189:3000');
//            $this->rowSubject('索引');
    }

    private function rowTitle($content)
    {
        return "# $content" . PHP_EOL;
    }

//    private function rowIndexTitle($content, $index)
//    {
//        return '<h3 id="' . $index . '">' . "$content $index" . '</h3>' . PHP_EOL . PHP_EOL;
//    }

//    private function rowIndex($content, $index)
//    {
//        return "> [$index $content](#$index)" . PHP_EOL;
//    }

    private function rowBrackets($dir)
    {
        $dir = $dir === 0 ? '{' : '}';
        return $this->rowText('        ' . $dir);
    }

    private function rowCode($content)
    {
        return " + $content" . PHP_EOL . PHP_EOL;
    }

    private function rowText($content)
    {
        return "$content" . PHP_EOL . PHP_EOL;
    }

    private function rowUrl($content)
    {
        return "* [$content]($content)" . PHP_EOL . PHP_EOL;
    }

    private function rowQuote($content)
    {
        return "    $content" . PHP_EOL . PHP_EOL;
    }

    private function rowSubject($content)
    {
        return "## $content" . PHP_EOL;
    }

    private function rowChapter($content)
    {
        return "### $content" . PHP_EOL . PHP_EOL;
    }

    private function rowTag($content, $replace = '')
    {
        $content = $content ?: $replace;
        return '_' . $content . '_' . PHP_EOL . PHP_EOL;
    }

    private function write($content, $file)
    {
        $myfile = fopen($file, "w");
        fwrite($myfile, $content);
        fclose($myfile);
    }

    private function read($file)
    {
        return file_get_contents($file);
    }

    private function anaylseTest()
    {
        $dir = "tests/apis";
        $files = scandir($dir);
        $file_content = [];
        $classes = [];

        foreach ($files as $file) {
            if (file_exists($dir . '/' . $file)) {
                $raws = file_get_contents($dir . '/' . $file);
                $rows = explode("\n", $raws);
                $class_name = str_replace('.ts', '', $file);
                $classes[] = $class_name;
                $file_content[$class_name] = $this->anaylseTestRow($rows);
            }
        }
        return ['contents' => $file_content, 'classes' => $classes];
    }

    private function commonReplace($content, $search, $search2)
    {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, '', $content);
            $content = str_replace($search2, '', $content);
            return trim($content);
        }
        return '';
    }

    private function anaylseTestRow($rows)
    {
        $res = [];
        $method_name = '';
        foreach ($rows as $row) {
            // 获取类名
//            $class = $this->commonReplace($row, "describe('", " 接口测试', function () {");

            // 方法名
            $value = $this->commonReplace($row, "it('", "', async function () {");
            if ($value) {
                $method_name = $value;
            }

            // 方法，url
            $value = $this->commonReplace($row, "await request.", "')");
            $values = explode("('", $value);
            if ($value) {
                $res['http_method'][$method_name] = trim($values[0]);
                $res['url'][$method_name] = trim($values[1]);
            }

            // 请求
            $value = $this->commonReplace($row, ".set('Accept', '", "')");
            if ($value) {
                $res['req_format'][$method_name] = $value;
            }

            // 响应
            $value = $this->commonReplace($row, ".expect('Content-Type', /", "/)");
            if ($value == 'json') {
                $res['res_format'][$method_name] = 'application/json';
            }

            // 响应 code
            if (preg_match('/.expect\((?=\d)/', $row) != 0) {
                $value = $row;
                $value = str_replace(".expect(", '', $value);
                $value = str_replace(")", '', $value);
                $value = str_replace(";", '', $value);
                $res['status_code'][$method_name] = trim($value);
            }
        }
        return $res;
    }

    private function anaylseApi($classes)
    {
        $files = [];
        $res = [];

        foreach ($classes as $class) {
            $files['class'][] = $class;
            $files['file'][] = 'api/controllers/' . $class . 'Controller.ts';
        }

        foreach ($files['file'] as $class_i => $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $rows = explode("\n", $content);
                $method_name = '';
                $class_name = $files['class'][$class_i];
                foreach ($rows as $line => $row) {
                    if (strpos($row, "index() {") !== false ||
                        strpos($row, "show() {") !== false ||
                        strpos($row, "store() {") !== false ||
                        strpos($row, "update() {") !== false ||
                        strpos($row, "delete() {") !== false ||
                        preg_match('/get[A-Z][a-z]+\(\) {/', $row) != 0 ||
                        preg_match('/post[A-Z][a-z]+\(\) {/', $row) != 0
                    ) {
                        $copy_row = $row;
                        $copy_row = str_replace("() {", '', $copy_row);
                        $method_name = trim($copy_row);
                        $res[$class_name]['method'][] = $method_name;
                    }

                    // 方法描述
                    if (strpos($row, "/**") !== false) {
                        if (strpos(trim($rows[$line + 3]), "@export") !== false) {
                            $res[$class_name]['top'] = str_replace("* ", '', trim($rows[$line + 1]));
                        } else {
                            $v = str_replace("* ", '', trim($rows[$line + 1]));
                            $res[$class_name]['desc'][$method_name] = $v;
                        }
                    }

                    if (strpos($row, "* @param {") !== false && strpos($row, "* @param {VaildStore") === false &&
                        strpos($row, "* @param {VaildUpdate") === false
                    ) {
                        $copy_row = str_replace("* @param {", '', $row);
                        $copy_row = str_replace(")", '', $copy_row);
                        $copy_rows = explode("} ", $copy_row);
                        $copy_rows_1 = explode(" ", trim($copy_rows[1])); // 0是参数 1是注释
                        $res[$class_name]['req_params'][$method_name][] = trim($copy_rows_1[0]) . " : " . trim($copy_rows[0]) . " // " . trim($copy_rows_1[1]);
                    }

                    // 方法更新时间
                    if (strpos($row, "* @updateTime ") !== false) {
                        $copy_row = str_replace("* @updateTime ", '', $row);
                        $res[$class_name]['update_time'][$method_name] = trim($copy_row);
                    }

                    if (strpos($row, "interface VaildStore {") !== false) {
                        $ii = 1;
                        do {
                            $copy_row = str_replace(";", '', $rows[$line + $ii]);
                            $copy_rows = explode(": ", $copy_row);
                            $res[$class_name]['req_params']['store'][] = trim($copy_rows[0]) . " : " . trim($copy_rows[1]);
                            $ii++;
                        } while (strpos($rows[$line + $ii], "}") === false);
                    }

                    if (strpos($row, "interface VaildUpdate {") !== false) {
                        $ii = 1;
                        do {
                            $copy_row = str_replace(";", '', $rows[$line + $ii]);
                            $copy_rows = explode(": ", $copy_row);
                            $res[$class_name]['req_params']['update'][] = trim($copy_rows[0]) . " : " . trim($copy_rows[1]);
                            $ii++;
                        } while (strpos($rows[$line + $ii], "}") === false);
                    }
                }
            }
        }
        return $res;
    }
}
