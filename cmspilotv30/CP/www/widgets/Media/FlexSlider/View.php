<?
class CP_Www_Widgets_Media_FlexSlider_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('flexSlider-2.1');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;


        /*** include the css file for the theme selected ***/

        if ($c->rowsHTMLPassed){
            $rows = $this->rowsHTML;
        } else  {
            $rows = $this->getRowsHTML();
        }

        $text = "
        <div id='{$c->handle}' class='flexslider'>
            <ul class='slides {$c->appendCls}'>
                {$rows}
            </ul>
        </div>
        ";


        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,slideshowSpeed: {$c->slideshowSpeed}
                ,animationSpeed: {$c->animationSpeed}
                ,animationType: '{$c->animationType}'
                ,direction: '{$c->direction}'
                ,showNav: {$cpUtil->getBoolStr($c->showNav)}
                ,showNavArrows: {$cpUtil->getBoolStr($c->showNavArrows)}
                ,autoPlay: '{$c->autoPlay}'
            }
            cpw.media.flexSlider.run(exp);
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
                $caption = "<div class='caption-bottom'>{$row['caption']}</div>";
            }

            if ($row['description'] != ''){
                $caption = ($row['caption'] != '') ? "<h1 class='caption'>{$row['caption']}</h1>" : '';

                $readMore = '';
                if ($c->showReadMore && $row['link'] != ''){
                    $readMore = "
                    <div class='readMore'>
                        <a href='{$row['link']}'>{$ln->gd($c->readMoreLbl)}</a>
                    </div>
                    ";
                }

                $inner = "
                <div class='subcolumns'>
                    <div class='{$c->subColLeft}'>
                        <div class='subcl'>
                            {$pic}
                        </div>
                    </div>
                    <div class='{$c->subColRight}'>
                        <div class='subcr'>
                            {$caption}
                            <div class='desc'>{$row['description']}</div>
                            {$readMore}
                        </div>
                    </div>
                </div>
                ";
            } else {
                $inner = "
                {$pic}
                {$caption}
                ";
            }

            $rows .="
            <li>
                {$inner}
            </li>
            ";
        }

        return $rows;
    }
}
