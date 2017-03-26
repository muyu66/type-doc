<?php

namespace Typedoc\Theme;

class Swagger
{
    public function wrapHtml($datas)
    {
        $head = $this->getHtml('/head.html');
        $foot = $this->getHtml('/foot.html');

        $navs = [];
        $classes = '';
        foreach ($datas as $data) {
            $class_start = $this->replaceHtml($data, '', $this->getHtml('/class.start.html'));
            $class_end = $this->getHtml('/class.end.html');

            preg_match('/[A-Z][a-z]+(?=[A-Z])/', $data['class_name'], $like_class);
            $navs[$like_class[0]][$data['class_name']] = $data['class_desc'];

            $methods = '';
            foreach ($data['method_name'] as $method_name) {
                /**
                 * 空方法跳过
                 */
                if (!$data['method_desc'][$method_name]) {
                    continue;
                }

                $params = '';
                foreach ($data['method_param_name'][$method_name] as $method_param) {
                    $param = $this->replaceHtml($method_param, $method_name, $this->getHtml('/params.html'));
                    $params = $params . $param;
                }
                $codes = $this->replaceHtml($data, $method_name, $this->getHtml('/code.html'));
                $method = $this->replaceHtml($data, $method_name, $this->getHtml('/method.html'));
                $method = $this->replace($params, $codes, $method);
                $methods = $methods . $method;
            }

            $classes = $classes . $class_start . $methods . $class_end;
        }
        dump($navs);


        $nav = '';
        foreach ($navs as $big_name => $array) {
            if ($big_name === '') {
                $big_name = 'Top Catalog';
            }
            $nav2 = '';
            foreach ($array as $name => $desc) {
                $nav2 = $nav2 . '<li><a href="#' . $name . '">' . $name . ' ' . $desc . '</a></li>';
            }
            $nav = $nav . $this->replaceHtml(['big' => $big_name, 'small' => $nav2], '', $this->getHtml('/nav.html'));
        }

        $layout = $this->replaceHtml($nav, '', $this->getHtml('/layout.html'));

        return $head . $layout . $classes . $foot;
    }

    private function replaceHtml($datas, $method, $html)
    {
        $html = str_replace('<!--sign::导航类别-->', $datas['big'], $html);
        $html = str_replace('<!--sign::导航按钮-->', $datas['small'], $html);
        $html = str_replace('<!--sign::导航-->', $datas, $html);

        $html = str_replace('<!--sign::类名-->', $datas['class_name'], $html);
        $html = str_replace('<!--sign::类名描述-->', $datas['class_desc'], $html);
        $html = str_replace('<!--sign::方法名-->', $datas['method_name'][$method], $html);
        $html = str_replace('<!--sign::方法描述-->', $datas['method_desc'][$method], $html);
        $html = str_replace('<!--sign::HTTP动作-->', strtoupper($datas['method_http_type'][$method]), $html);
        $html = str_replace('<!--sign::URL-->', $datas['method_http_url'][$method], $html);
        $html = str_replace('<!--sign::参数名-->', $datas['param'], $html);
        $html = str_replace('<!--sign::参数类型-->', $datas['type'], $html);
        $html = str_replace('<!--sign::参数是否必需-->', $datas['method_param_require'], $html);
        $html = str_replace('<!--sign::参数描述-->', $datas['desc'], $html);
        $html = str_replace('<!--sign::响应状态码-->', $datas['method_res_code'][$method], $html);
        $html = str_replace('<!--sign::响应用例-->', $datas['method_res_example'][$method], $html);
        $html = str_replace('<!--sign::Model-->', $datas['method_res_model'][$method], $html);
        $html = str_replace('sign__cid', 'uuid_' . uniqid(), $html);
        return $html;
    }

    private function replace($target1, $target2, $source)
    {
        $source = str_replace('<!--sign::参数列表-->', $target1, $source);
        $source = str_replace('<!--sign::响应状态码列表-->', $target2, $source);
        $source = str_replace('<!--sign::参数导航-->', $target1, $source);
        return $source;
    }

    private function getHtml($file)
    {
        if (file_exists(__DIR__ . $file)) {
            return file_get_contents(__DIR__ . $file);
        } else {
            dump('组件文件丢失 ' . __DIR__ . $file);
        }
    }
}