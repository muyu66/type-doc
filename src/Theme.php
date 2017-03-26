<?php

namespace Typedoc;

use Typedoc\Theme\Swagger;

class Theme
{
    public function useTheme($datas, $theme)
    {
        if (!in_array($theme, ['Swagger'])) {
            dump('无此主题文件');
        }

        return $this->getTheme($datas, $theme);
    }

    private function getTheme($datas, $theme)
    {
        $class = new Swagger();
        return $class->wrapHtml($datas);
    }
}