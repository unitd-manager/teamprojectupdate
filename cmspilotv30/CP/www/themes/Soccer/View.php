<?
class CP_Www_Themes_Soccer_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('cufon', 'cufon-diavlo', 'jqNivoSlider-2.5.1');

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

		$text = '';
		if ($cpCfg['cp.showMainNavPanelAtTop']){
		    $mainNav = Zend_Registry::get('mainNav');
            $widget = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top'
                ,'animate' => true
            ))}
            ";

			$text = "
	        <div id='nav'>
	            <a id='navigation' name='navigation'></a>
	            {$widget}
	        </div>
			";
		}
        return $text;
    }
    
    /**
     *
     */
    function getHeaderPanel(){
        $tv = Zend_Registry::get('tv');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        
        $slideshow = '';
        if ($tv['secType'] == 'Home'){
            $wSlideshow= getCPWidgetObj('media_nivoSlider');
            $slideshow = $wSlideshow->getWidget();
        }

        $text = "
        <div class='inner'>
            {$pSiteSearch->view->getSearchBox()}
            {$slideshow}
        </div>
        ";

        return $text;
    }

}