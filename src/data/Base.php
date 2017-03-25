<?php

namespace Typedoc\Data;

class Base
{
    protected $get;

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
        $rows_s = [];

        /**
         * 处理每个文件
         */
        foreach ($file_paths as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $rows = explode("\n", $content);
                $rows_s[] = $rows;
            } else {
                dump("不存在文件 $file");
            }
        }

        return $rows_s;
    }

}