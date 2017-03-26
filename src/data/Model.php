<?php

namespace Typedoc\Data;

class Model extends Base
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

    private function getCanNullable($row, $rows, $line)
    {
        return $this->get->getSearchNext($row, ['@Column', 'nullable: true'], $rows, $line);
    }

    private function rowHandle(array $rows)
    {
        $res = [];

        foreach ($rows as $line => $row) {

            /**
             * 获取可以为null的字段
             */
            $tmp = $this->getCanNullable($row, $rows, $line);
            if ($tmp) {
                $res['can_null'][] = $tmp;
            }

        }

        return $res;
    }
}