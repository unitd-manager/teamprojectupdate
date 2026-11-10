<?
class CP_Www_Widgets_Media_ImagesSlider_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqGalleria-1.2.3');

    //========================================================//
    function getWidget() {
        $c = &$this->controller;
        $rows = $this->getRowsHTML();

        $text = '';

        if ($rows != ''){
            $cls = '';

            if ($c->totalRecords == 1){
                $c->thumbnails = '';
                $cls = " single";
            }

            $text = "
            <div id='{$c->handle}' class='jqGalleriaSlider{$cls}'>
                {$rows}
            </div>
            ";

            $script = "
            exp = {
                 handle: '{$c->handle}'
                ,width: '{$c->width}'
                ,height: '{$c->height}'
                ,autoplay: '{$c->autoplay}'
                ,speed: '{$c->speed}'
                ,zoom: '{$c->zoom}'
                ,showCaption: '{$c->showCaption}'
                ,thumbnails: '{$c->thumbnails}'
            }
            cpw.media.relatedImages.run(exp);
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

        foreach($this->model->dataArray AS $row) {
            $imgUrl   = isset($row['media_url']) && $row['media_url'] != '' ? $row['media_url'] : $row['file_thumb'];
            $largeUrl = isset($row['media_url']) && $row['media_url'] != '' ? $row['media_url'] : $row['file_large'];
            $rows .= "
            <a href='{$largeUrl}'>
            	<img title='{$row['caption']}'
            	     alt='{$row['alt_tag_data']}'
            	     src='{$imgUrl}'>
        	</a>
            ";
        }

        return $rows;
    }
}
