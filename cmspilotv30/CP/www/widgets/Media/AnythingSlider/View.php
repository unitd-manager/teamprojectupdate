<?
class CP_Www_Widgets_Media_AnythingSlider_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqAnythingSlider1.5.6.4');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        $plPath = "{$cpCfg['cp.jssPath']}jquery/anythingslider-1.5.6.4/";

        /*** include the css file for the theme selected ***/
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array("jqAS1564Theme-{$c->theme}"));

        if ($c->rowsHTMLPassed){
            $rows = $this->rowsHTML;
        } else  {
            $rows = $this->getRowsHTML();
        }

        $text = "
        <ul id='{$c->handle}' class='{$c->appendCls}'>
            {$rows}
        </ul>
        ";


        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,showNav: {$cpUtil->getBoolStr($c->showNav)}
                ,showNavArrows: {$cpUtil->getBoolStr($c->showNavArrows)}
                ,theme: '{$c->theme}'
                ,speed: {$c->speed}
                ,width: {$c->width}
                ,height: {$c->height}
                ,resizeContents: {$cpUtil->getBoolStr($c->resizeContents)}
                ,autoPlay: '{$c->autoPlay}'
            }
            cpw.media.anythingSlider.run(exp);
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
                $caption = "<div class='caption-{$c->captionPosition}'>{$row['caption']}</div>";
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
