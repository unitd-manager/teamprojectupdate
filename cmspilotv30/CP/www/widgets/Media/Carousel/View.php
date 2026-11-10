<?
class CP_Www_Widgets_Media_Carousel_View extends CP_Common_Lib_WidgetViewAbstract
{

    var $jssKeys = array('carouFredSel-5.5.0');

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $rows = $this->getRowsHTML();

        $nav = '';
        $pager = '';

        if ($c->showNav){
            $nav = "
            <a class='prev' id='{$c->handle}_prev' href='#'><span>prev</span></a>
            <a class='next' id='{$c->handle}_next' href='#'><span>next</span></a>
            ";
        }

        if ($c->showPager){
            $pager = "
            <div class='pagination' id='{$c->handle}_pag'></div>
            ";
        }

        $text = "
        <div id='{$c->handle}' class='carousel {$c->appendCssClass}'>
            <ul>
                {$rows}
            </ul>
            <div class='clear'></div>
            {$nav}
            {$pager}
        </div>
        ";

        $anchorBuilder = '';
        if ($c->thumbnailNav) {
            $anchorBuilder = "
            ,anchorBuilder: function(nr, item) {
                var src = $('img', item).attr('src');
                src = src.replace( '/large/', '/thumb/');
                return '<img src=\"' + src + '\" />';
            }
            ";
        }

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle    : '{$c->handle}'
                ,fx        : '{$c->fx}'
                ,items     : '{$c->items}'
                ,showNav   : '{$c->showNav}'
                ,showPager : '{$c->showPager}'
                ,speed     : '{$c->speed}'
                ,autoStart : '{$c->autoStart}'
                ,width     : '{$c->width}'
                ,height    : '{$c->height}'
                {$anchorBuilder}
            }
            cpw.media.carousel.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        
        if ($c->rowsHTMLPassed){
            return $this->rowsHTML;
        }
        
        $showCaption = $this->controller->showCaption;

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray AS $row) {
            $pic = '';
            $cls = '';
            if ($count == 1) {
                $cls = 'first';
            }
            if ($row['pic'] != ''){
                $pic = "<img src='{$row['pic']}' width='{$row['imgWidth']}' height='{$row['imgHeight']}' class='{$cls}'>";
            }

            if ($pic != '' && $row['link'] != '') {
                $pic = "<a href='{$row['link']}'>{$pic}</a>";
            }

            $caption = '';
            if ($row['caption'] != '' && $showCaption){
                $caption = "
                <span class='caption'>{$row['caption']}</span>
                ";
            }

            $rows .="
            <li>
                {$pic}
                {$caption}
            </li>
            ";
            $count++;

        }

        return $rows;
    }
}
