<?
class CP_Www_Widgets_Media_SimpleFadeSlideshow_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqSimpleFadeSlideshow-2.0.0');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $rows = $this->getRowsHTML();

        $text = "
        <ul id='{$c->handle}' class='simpleFadeSlideshow noDefault'>
            {$rows}
        </ul>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,speed: '{$c->speed}'
                ,width: '{$c->width}'
                ,height: '{$c->height}'
            }
            cpw.media.simpleFadeSlideshow.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $showCaption = $this->controller->showCaption;
        $showdescription = $this->controller->showdescription;

        $rows = '';
        foreach($this->model->dataArray AS $row) {
            $pic = '';
            if ($row['pic'] != ''){
                $pic = "<div class='pic'><img src='{$row['pic']}'/></div>";
            }

            if ($pic != '' && $row['link'] != '') {
                $pic = "<div class='pic'><a href='{$row['link']}'>{$pic}</a></div>";
            }

            $caption = '';
            if ($row['caption'] != '' && $showCaption){
                $caption = "<div class='caption'>{$row['caption']}</div>";
            }

            $description = '';
            if ($row['description'] != '' && $showdescription){
                $description = "<div class='desc'>{$row['description']}</div>";
            }

            $rows .="
            <li>
                {$pic}
                {$caption}
                {$description}
            </li>
            ";
        }

        return $rows;
    }
}
