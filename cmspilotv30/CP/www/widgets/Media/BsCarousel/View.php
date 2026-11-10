<?
class CP_Www_Widgets_Media_BsCarousel_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqBsCarousel');

    //========================================================//
    function getWidget() {
        $c = &$this->controller;
        $rows = $this->getRowsHTML();


        $text = '';

        if ($rows != ''){
            $cls = '';

            $text = "
            <div id='{$c->handle}' class='carousel slide' data-ride='carousel'>
                {$rows}
                <!-- Controls -->
                <a class='left carousel-control' href='#bsCarousel1' role='button' data-slide='prev'>
                  <span class='glyphicon glyphicon-chevron-left'><img src='/cmspilotv30/CP/www/themes/Kite/images/arrow-left-detail.png'/ title=''></span>
                </a>
                <a class='right carousel-control' href='#bsCarousel1' role='button' data-slide='next'>
                  <span class='glyphicon glyphicon-chevron-right'><img src='/cmspilotv30/CP/www/themes/Kite/images/arrow-right-detail.png'/ title=''></span>
                </a>
            </div>
            ";

            $script = "
            exp = {
                 handle: '{$c->handle}'
                ,autoplay: '{$c->autoplay}'
                ,speed: '{$c->speed}'
            }
            cpw.media.bsCarousel.run(exp);
            ";

            if ($c->executeScript){
                CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    {$script}
                "));
            }
        }

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $cols = '';
        $text = '';
        $count = 0;
        foreach($this->model->dataArray AS $row) {
            $imgUrl   = isset($row['media_url']) && $row['media_url'] != '' ? $row['media_url'] : $row['file_large'];
            $largeUrl = isset($row['media_url']) && $row['media_url'] != '' ? $row['media_url'] : $row['file_large'];
            $media_id = $row['media_id'];
            $mediaRec    = $fn->getRecordRowByID('media', 'media_id', $media_id );
            $noticeRec   = $fn->getRecordRowByID('notice', 'notice_id', $mediaRec['record_id'] );

            if($row['caption'] == ''){
                $title = htmlspecialchars($noticeRec['title'], ENT_QUOTES);
            } else {
                $title = $row['caption'];
            }

            if($row['description'] == ''){
                $desc = htmlspecialchars($noticeRec['description'], ENT_QUOTES);
                $desc = nl2br($desc);
            } else {
                $desc = htmlspecialchars($row['description'], ENT_QUOTES);
                $desc = nl2br($desc);
            }

            $class = '';
            if($count == 0) {
                $class = 'active';
            }

            $cols .= "
            <li data-target='#bsCarousel1' data-slide-to='{$count}' class=''></li>
            ";
            $rows .= "
            <div class='item {$class}'>
                <img title='{$row['caption']}' alt='{$row['alt_tag_data']}' src='{$imgUrl}'>
                <div class='carousel-caption'>
                  <h1>{$title}</h1>
                  <div>{$desc}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $text = "
        <!-- Indicators -->
        <ol class='carousel-indicators'>
        </ol>
        <!-- Wrapper for slides -->
        <div class='carousel-inner'>
            {$rows}
        </div>
        ";

        return $text;
    }
}
