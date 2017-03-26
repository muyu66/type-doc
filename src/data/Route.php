<?php

namespace Typedoc\Data;

class Route extends Base
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

    private function getResource($row)
    {
        $url_ctl = $this->get->getSearch($row, "route.resource('", '', ["route.resource('", "');"]);
        $url_ctls = explode("', '", $url_ctl);
        return ['url' => $url_ctls[0], 'ctl' => $url_ctls[1]];
    }

    private function getStandard($row)
    {
        foreach (['get', 'post'] as $method) {
            $url_ctl = $this->get->getSearch(
                $row,
                "route.$method('", '', ["route.$method('", "');"]
            );
            if ($url_ctl) {
                $url_ctls = explode("', '", $url_ctl);
                $ctl_methods = explode('@', $url_ctls[1]);
                return [
                    'url' => $url_ctls[0],
                    'ctl' => $ctl_methods[0],
                    'method' => $ctl_methods[1]
                ];
            }
        }
        return [];
    }

    private function rowHandle(array $rows)
    {
        $res = [];

        foreach ($rows as $line => $row) {
            /**
             * 获取 Resource 路由
             */
            $tmp = $this->getResource($row);
            if ($tmp['url'] && $tmp['ctl']) {
                $res['resource_url'][] = $tmp['url'];
                $res['resource_ctl'][] = $tmp['ctl'];
            }

            /**
             * 获取 标准 路由
             */
            $tmp = $this->getStandard($row);
            if ($tmp['url'] && $tmp['ctl']) {
                $res['standard_url'][] = $tmp['url'];
                $res['standard_ctl'][] = $tmp['ctl'];
                $res['standard_method'][] = $tmp['method'];
            }
        }

        return $res;
    }
}