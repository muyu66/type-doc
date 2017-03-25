<?php

namespace Typedoc\Data;

class Get
{
    public function __construct()
    {

    }

    /**
     * @param $source
     * @param $search
     * @param string $search2
     * @param array $replace
     * @return string
     */
    public function getSearch($source, $search, $search2 = '', $replace = [])
    {
        if (!is_array($replace)) {
            $replace = [$replace];
        }

        if (count($replace) === 0) {
            $replace = [$search];
        }

        if (strpos($source, $search) !== false) {
            $source = str_replace($replace, '', $source);
            if ($search2) {
                $source = str_replace($search2, '', $source);
            }
            return trim($source);
        }
        return '';
    }

    /**
     * @param $source
     * @param string $rule 正则
     * @param $replace
     * @return string
     */
    public function getRule($source, $rule, $replace)
    {
        if (preg_match($rule, $source) != 0) {
            $source = str_replace($replace, '', $source);
            return trim($source);
        }
        return '';
    }

    public function getRuleArray($source, $rule, array $replace)
    {
        if (preg_match($rule, $source) != 0) {
            $source = str_replace($replace, '', $source);
            return trim($source);
        }
        return '';
    }

    private function getDesc($source)
    {
        return strpos($source, "/**") !== false ? true : false;
    }

    public function getClassDesc($source, $rows, $line)
    {
        if ($this->getDesc($source)) {
            /**
             * 判断是 Class 的描述
             */
            if (strpos(trim($rows[$line + 3]), "@export") !== false) {
                return str_replace("* ", '', trim($rows[$line + 1]));
            }
        }
        return '';
    }

    public function getMethodDesc($source, $rows, $line)
    {
        if ($this->getDesc($source)) {
            /**
             * 判断是 Method 的描述
             */
            if (strpos(trim($rows[$line + 3]), "@export") === false) {
                return str_replace("* ", '', trim($rows[$line + 1]));
            }
        }
        return '';
    }

    public function getParams($source)
    {
        /**
         * 排除 接口参数 (另外去取)
         */
        if (strpos($source, "* @param {") !== false && strpos($source, "* @param {VaildStore") === false &&
            strpos($source, "* @param {VaildUpdate") === false
        ) {
            $source = str_replace("* @param {", '', $source);
            $source = str_replace(")", '', $source);
            $type_and_param_desc = explode("} ", $source); // 0是类型 1是参数和注释
            $param_and_desc = explode(" ", trim($type_and_param_desc[1])); // 0是参数 1是注释
            return [
                'type' => trim($type_and_param_desc[0]),
                'param' => $param_and_desc[0],
                'desc' => trim($param_and_desc[1])
            ];
        }
        return '';
    }

    public function getInterfaceParams($source, $rows, $line, $interface)
    {
        $res = [];
        if (strpos($source, "interface $interface {") !== false) {
            $count = 1;
            do {
                $source = str_replace(";", '', $rows[$line + $count]);
                $param_type = explode(": ", $source); // 0参数 1参数类型
                $res[] = trim($param_type[0]) . " : " . trim($param_type[1]);
                $count++;
            } while (strpos($rows[$line + $count], "}") === false);
        }
        return $res;
    }

    public function getUpdateTime($row)
    {
        return $this->getSearch($row, '* @updateTime ');
    }
}