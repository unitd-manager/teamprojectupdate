<?
class CP_Www_Widgets_Media_S3Slider_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqS3slider-1.0');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $rows = $this->getRowsHTML();

        $text = "
        <div id='{$c->handle}'>
        <ul id='{$c->handle}Content' class='noDefault'>
            {$rows}
            <div class='clear {$c->handle}Image'></div>
        </ul>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,speed: '{$c->speed}'
                ,width: '{$c->width}'
                ,height: '{$c->height}'
            }
            cpw.media.s3Slider.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $showCaption = $this->controller->showCaption;

        $rows = '';
        foreach($this->model->dataArray AS $row) {
            $pic = '';
            if ($row['pic'] != ''){
                $pic = "<img src='{$row['pic']}'/>";
            }

            if ($pic != '' && $row['link'] != '') {
                $pic = "<a href='{$row['link']}'>{$pic}</a>";
            }

            $caption = '';
            if ($row['caption'] != '' && $showCaption){
                $caption = "<span class='caption-bottom'><strong>{$row['caption']}</strong></span>";
            } else {
                $caption = "<span></span>";
            }

            $rows .="
            <li class='{$c->handle}Image'>
                {$pic}
                {$caption}
            </li>
            ";
        }

        return $rows;
    }
}
