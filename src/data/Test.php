<?php

namespace Typedoc\Data;

class Test extends Base
{
    public function handle(array $file_paths, array $classes)
    {
        $rows_s = parent::handle($file_paths, $classes);

        $res = [];

        foreach ($rows_s as $rows) {
            $res[] = $this->rowHandle($rows);
        }

        return $res;
    }

    private function getMethodName($row)
    {
        return $this->get->getSearch($row, "it('", "', async function () {");
    }

    private function getHttp($row)
    {
        $value = $this->get->getSearch($row, 'await request.', '', ["')", 'await request.']);
        $values = explode("('", $value);
        return [
            'method' => trim($values[0]),
            'url' => trim($values[1]),
        ];
    }

    private function getRequestFormat($row)
    {
        return $this->get->getSearch($row, ".set('Accept', '", "')");
    }

    private function getResponseFormat($row)
    {
        $value = $this->get->getSearch($row, ".expect('Content-Type', /", "/)");
        if ($value == 'json') {
            return 'application/json';
        }
        return '';
    }

    private function getResponseCode($row)
    {
        return $this->get->getRuleArray($row, '/.expect\((?=\d)/', [
            '.expect(', ')', ';'
        ]);
    }

    private function rowHandle(array $rows)
    {
        $res = [];

        foreach ($rows as $line => $row) {

            /**
             * 获取方法名
             */
            $tmp = $this->getMethodName($row);
            if ($tmp) {
                $res['method'][] = $tmp;
            }

            /**
             * 获取HTTP方法，URL
             */
            $tmp = $this->getHttp($row);
            if ($tmp['method'] && $tmp['url']) {
                $res['http_method'][] = $tmp['method'];
                $res['http_url'][] = $tmp['url'];
            }

            /**
             * 获取请求的格式
             */
            $tmp = $this->getRequestFormat($row);
            if ($tmp) {
                $res['req_format'][] = $tmp;
            }

            /**
             * 获取响应的格式
             */
            $tmp = $this->getResponseFormat($row);
            if ($tmp) {
                $res['res_format'][] = $tmp;
            }

            /**
             * 获取响应的状态码
             */
            $tmp = $this->getResponseCode($row);
            if ($tmp) {
                $res['res_code'][] = $tmp;
            }
        }

        return $res;
    }
}