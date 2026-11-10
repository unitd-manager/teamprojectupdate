<?
class CP_Www_Widgets_Media_Banner_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $c = $this->controller;

        $calloutText = '';
        if ($c->calloutContentType != '') {
            $rowCont = getCPModelObj('webBasic_content')->getRecordByType($c->calloutContentType);
            $calloutText = "
            <div class='callout'>
                {$rowCont['description_lang']}
            </div>
            ";
        }

        $text = '';
        if ($c->superSized){
            $json = $this->getRowsHTMLForSuperSized();

            $navItems = '';

            if ($c->showNavForSuperSized){
                $navItems = $this->getSuperSizedNavElements();
            }

            if ($json != ''){
                CP_Common_Lib_Registry::arrayMerge('jssKeys', array("jqSuperSized-3.2.5"));

                $text = "
                {$navItems}
                ";

                CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    exp = {
                         slidesObj: '{$json}'
                    }
                    cpw.media.banner.showSuperSizedImage(exp);
                "));
            }
        } else {

            if (count($this->model->dataArray) > 1 && $c->autoSlideShowForMultiBanners){
                $wSlideshow = getCPWidgetObj($c->autoSlideShowWidget);

                $counter = 0;
                $arr = array();

                $dataArray = $this->model->dataArray;
                foreach($dataArray AS $row) {
                    $arrTemp = &$arr[$counter];
                    $arrTemp['pic']     = "{$cpCfg['cp.mediaFolderAlias']}large/{$row['file_name']}";
                    $arrTemp['link']    = $cpUrl->getExtIntUrl($row);
                    $arrTemp['caption'] = $row['caption'];
                    $arrTemp['description'] = nl2br($row['description']);
                    $counter++;
                }

                $slideshowW = $c->autoSlideShowWidth  != '' ? $c->autoSlideShowWidth  : $dataArray[0]['large_w'];
                $slideshowH = $c->autoSlideShowHeight != '' ? $c->autoSlideShowHeight : $dataArray[0]['large_h'];
                $speed = $fn->getIssetParam($cpCfg, 'cp.slideshowSpeed', 5);

                $text = "
                {$wSlideshow->getWidget(array(
                     'dataArray' => $arr
                    ,'width' => $slideshowW
                    ,'height' => $slideshowH
                    ,'speed' => $speed
                    ,'captionPosition' => $c->captionPosition
                ))}
                ";
            } else {
                $rowsHTML = $this->getRowsHTML();
                if ($rowsHTML != ''){
                    $text = "
                    <ul class='noDefault'>
                        {$rowsHTML}
                    </ul>
                    {$calloutText}
                    ";
                }
            }
        }


        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $c = &$this->controller;
        $showCaption = $c->showCaption;
        $rows = '';
        foreach($this->model->dataArray AS $row) {
            $pic = "<img src='{$row['file_large']}'/>";

            $url = $cpUrl->getExtIntUrl($row);
            if ($url != '') {

                $zoomCls = '';
                $ext = strtolower(substr($url, strrpos($url, ".") + 1));
                if ($ext == 'jpg' || $ext == 'gif' || $ext == 'png'){
                    $zoomPlugin = 'jqColorbox-1.3.15';
                    CP_Common_Lib_Registry::arrayMerge('jssKeys', array($zoomPlugin));
                    $zoomCls = " class='cpZoom'";
                }
                $pic = "<a href='{$url}'{$zoomCls}>{$pic}</a>";
            }

            $caption = '';
            if ($row['caption'] != '' && $showCaption){
                $caption = "<div class='caption'>{$row['caption']}</div>";
            }

            if ($row['description'] != ''  && $showCaption){
                $caption  = ($row['caption'] != '')  ? "<h1 class='caption'>{$row['caption']}</h1>" : '';

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

            $bg_color = ($row['bg_color'] != '') ? "bgcolor='{$row['bg_color']}'" : '';
            $rows .="
            <li {$bg_color}>
                {$inner}
            </li>
            ";
        }

        if(count($this->model->dataArray) == 0 && $c->showDefaultBanner){
            $pic = "<img src='{$c->defaultBanner}'/>";

            $caption = '';
            if ($c->defaultBannerCaption != '' && $showCaption){
                $caption = "<div class='caption'>{$c->defaultBannerCaption}</div>";
            }

            $rows .="
            <li>
                {$pic}
                {$caption}
            </li>
            ";
        }

        return $rows;
    }

    //========================================================//
    function getRowsHTMLForSuperSized() {

        $rows = array();
        foreach($this->model->dataArray AS $row) {
            $rows[] = array('image' => $row['file_large']);
        }

        if (count($rows) > 0){
            return json_encode($rows);
        }
    }

    //========================================================//
    function getSuperSizedNavElements() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $text = "
    	<!--Thumbnail Navigation-->
    	<div id='prevthumb'></div>
    	<div id='nextthumb'></div>

    	<!--Arrow Navigation-->
    	<a id='prevslide' class='load-item'></a>
    	<a id='nextslide' class='load-item'></a>


    	<div id='thumb-tray' class='load-item'>
    		<div id='thumb-back'></div>
    		<div id='thumb-forward'></div>
    	</div>

    	<!--Time Bar-->
    	<div id='progress-back' class='load-item'>
    		<div id='progress-bar'></div>
    	</div>

    	<!--Control Bar-->

    	<div id='controls-wrapper' class='load-item'>
    		<div id='controls'>

    			<a id='play-button'><img id='pauseplay' src='img/pause.png'/></a>

    			<!--Slide counter-->
    			<div id='slidecounter'>
    				<span class='slidenumber'></span> / <span class='totalslides'></span>
    			</div>

    			<!--Slide captions displayed here-->

    			<div id='slidecaption'></div>

    			<!--Thumb Tray button-->
    			<a id='tray-button'><img id='tray-arrow' src='img/button-tray-up.png'/></a>

    			<!--Navigation-->
    			<ul id='slide-list'></ul>

    		</div>
    	</div>
        ";

        return $text;
    }
}
