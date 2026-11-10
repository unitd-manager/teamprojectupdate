<?
class CP_Www_Widgets_Media_Supersized_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqSuperSized-3.2.5');
    //========================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $text = '';
        $json = $this->getRowsHTML();
        $navItems = '';
        
        if (!$c->hideAllNav){
            $count = count($this->model->dataArray);
            $navItems = $this->getNavElements($count);
        }

        $slideCaptionDiv = ($c->showCaptionOutsideControlBar) ? '#slidecaptionOuter' : '#slideCaption';
        
        if ($json != ''){
            $text = "
            {$navItems}
            ";

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {
                     slidesObj: '{$json}'
                    ,slideCaptionDiv: '{$slideCaptionDiv}'
                    ,thumbTrayDiv: '{$c->thumbTrayDiv}'
                    ,autoPlay: $c->autoPlay
                }
                cpw.media.supersized.run(exp);
            "));
        }

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = array();
        $counter = 0;
        foreach($this->model->dataArray AS $row) {
            $rows[$counter] = array('image' => $row['file_large']);
            
            if ($row['url'] != ''){
//                $rows[$counter]['url'] = $row['url'];
                $rows[$counter]['url'] = $fn->replaceUrlVars($row['url']);
            }

            if ($row['caption'] != ''){
                $caption = $row['caption'];
                
                if ($row['url'] != ''){
                    $url = $fn->replaceUrlVars($row['url']);
                    /*** do not add sinle quote for url and class to avoid parse error, proper solution required ***/
                    $caption .= "&nbsp;&nbsp;<a href={$url} class=url><span>{$ln->gd('cp.lbl.more')}</span></a>";
                }
                
                $rows[$counter]['title'] = $caption;
            }

            $counter++;
        }

        if (count($rows) > 0){
            return json_encode($rows);
        }
    }

    //========================================================//
    function getNavElements($count) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $wImgPath = "{$cpCfg['cp.jssPath']}jquery/supersized-3.2.5/images/";

        $arrows      = '';
        $thumbnails  = '';
        $progressBar = '';
        $controlBar  = '';
        $caption     = '';

        if ($c->showArrows && $count > 1){
            $arrows = "
        	<!--Arrow Navigation-->
        	<a id='prevslide' class='load-item'></a>
        	<a id='nextslide' class='load-item'></a>
        	";
        }

        if ($c->showThumbnail){
            $thumbnails = "
        	<!--Thumbnail Navigation-->
        	<div id='prevthumb'></div>
        	<div id='nextthumb'></div>

        	<div id='thumb-tray' class='load-item'>
        		<div id='thumb-back'></div>
        		<div id='thumb-forward'></div>
        	</div>
        	";
        }

        if ($c->showProgressBar){
            $progressBar = "
        	<!--Time Bar-->
        	<div id='progress-back' class='load-item'>
        		<div id='progress-bar'></div>
        	</div>
        	";
        }

        if ($c->showCaptionOutsideControlBar){
            $caption = "
			<div id='slidecaptionOuter'></div>
        	";
        }

        if ($c->showControlBar){
            $thumbTrayBtn = '';
            if ($c->showThumbnail){
                $thumbTrayBtn = "
    			<!--Thumb Tray button-->
    			<a id='tray-button'><img id='tray-arrow' src='{$wImgPath}button-tray-up.png'/></a>
                ";
            }

            $controlBar = "
        	<!--Control Bar-->
        	<div id='controls-wrapper' class='load-item'>
        		<div id='controls'>
        			<a id='play-button'><img id='pauseplay' src='{$wImgPath}pause.png'/></a>

        			<!--Slide counter-->
        			<div id='slidecounter'>
        				<span class='slidenumber'></span> / <span class='totalslides'></span>
        			</div>

        			<div id='slidecaption'></div>
                    {$thumbTrayBtn}

        			<!--Navigation-->
        			<ul id='slide-list'></ul>
        		</div>
        	</div>
        	";
        }

        $text = "
        {$arrows}
        {$caption}
        {$thumbnails}
        {$progressBar}
        {$controlBar}
        ";

        return $text;
    }
}
