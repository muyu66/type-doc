<?php

namespace Typedoc\Data;

class Api
{
    private $get;

    /**
     * 抓 api 里面的数据
     * Api constructor.
     */
    public function __construct()
    {
        $this->get = new Get();
    }

    /**
     * @param array $file_paths
     * @param array $classes
     * @return array
     */
    public function handle(array $file_paths, array $classes)
    {
        $res = [];

        /**
         * 处理每个文件
         */
        foreach ($file_paths as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $rows = explode("\n", $content);
                $res[] = $this->rowHandle($rows);
            } else {
                dump("不存在文件 $file");
            }
        }

        return $res;
    }

    /**
     * @param $row
     * @return string
     */
    private function getMethodName($row)
    {
        $methods = ['index', 'show', 'store', 'update', 'delete'];
        $tmp = '';

        foreach ($methods as $method) {
            $m = $this->wrapMethod($method);
            $tmp = $this->get->getSearch($row, $m['name'], '', $m['end']) ?: $tmp;
        }

        $get = $this->wrapMethod('get');
        $tmp = $this->get->getRule($row, $get['name'], $get['end']) ?: $tmp;

        $post = $this->wrapMethod('post');
        $tmp = $this->get->getRule($row, $post['name'], $post['end']) ?: $tmp;

        return $tmp;
    }

    private function wrapMethod($method)
    {
        if (in_array($method, ['get', 'post'])) {
            return ['name' => '/' . $method . '[A-Z][a-z]+\(\) {/', 'end' => '() {'];
        }
        return ['name' => "$method() {", 'end' => '() {'];
    }

    private function getDescription($row, $rows, $line)
    {
        $class = $this->get->getClassDesc($row, $rows, $line);
        $method = $this->get->getMethodDesc($row, $rows, $line);
        return $class ? ['k' => 'class', 'v' => $class] : ['k' => 'method', 'v' => $method];
    }

    private function getParams($row)
    {
        return $this->get->getParams($row);
    }

    private function getInterfaceParams($row, $rows, $line)
    {
        $store = $this->get->getInterfaceParams($row, $rows, $line, 'VaildStore');
        $update = $this->get->getInterfaceParams($row, $rows, $line, 'VaildUpdate');
        return $store ? ['k' => 'store', 'v' => $store] : ['k' => 'update', 'v' => $update];
    }

    private function getUpdateTime($row)
    {
        return $this->get->getUpdateTime($row);
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
             * 获取类、方法描述
             */
            $tmp = $this->getDescription($row, $rows, $line);
            if ($tmp['v']) {
                $res['desc'][$tmp['k']][] = $tmp['v'];
            }

            /**
             * 获取非接口参数
             */
            $tmp = $this->getParams($row);
            if ($tmp) {
                $res['param'][] = $tmp;
            }

            /**
             * 获取方法更新时间
             */
            $tmp = $this->getUpdateTime($row);
            if ($tmp) {
                $res['update_time'][] = $tmp;
            }

            /**
             * 获取接口参数
             */
            $tmp = $this->getInterfaceParams($row, $rows, $line);
            if ($tmp['v']) {
                $res['interface'][$tmp['k']][] = $tmp['v'];
            }
        }

        return $res;
    }
}