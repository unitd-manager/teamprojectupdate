<?
class CP_Www_Widgets_Media_NivoSlider_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqNivoSlider-2.5.1');

    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $rows = $this->getRowsHTML();

        if ($rows == ''){
            return;
        }

        $text = "
        <div class='nivo-slider-wrapper'>
            <div id='{$c->handle}' class='nivoSlider {$c->appendCls}'>
                {$rows}
            </div>
            <div class='nivo-html-caption'>
            </div>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,speed: '{$c->speed}'
                ,controlNav: {$fn->boolToStr($c->controlNav)}
                ,directionNav: {$fn->boolToStr($c->directionNav)}
                ,effect: '{$c->effect}'
            }
            cpw.media.nivoSlider.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $showCaption = $this->controller->showCaption;

        $rows = '';
        $count = 1;
        foreach($this->model->dataArray AS $row) {
            $caption = '';
            if ($showCaption){
                $caption = " title='{$row['caption']}'";
            }

            $pic = '';
            if ($row['pic'] != ''){
                $cls = '';
                if ($count == 1) {
                    $cls = 'first';
                }
                $pic = "<img src='{$row['pic']}' {$caption} class='{$cls}'>";
            }

            if ($pic != '' && $row['link'] != '') {
                $pic = "<a href='{$row['link']}'>{$pic}</a>";
            }

            $rows .="
            {$pic}
            ";
            $count++;
        }

        return $rows;
    }
}
