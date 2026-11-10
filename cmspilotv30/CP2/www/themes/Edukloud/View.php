<?
class CP_Www_Themes_Edukloud_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('cufon', 'cufon-diavlo', 'jqNivoSlider-2.5.1');

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $topMostRooms = '';
        $text = '';
        
        if ($cpCfg['cp.showMainNavPanelAtTop']){

            $mainNav = Zend_Registry::get('mainNav');
            $hasSlidingDoorBtn = $cpCfg['w.core_mainNav.hasSlidingDoorBtn'];

            $widget = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top'
                ,'hasSlidingDoorBtn' => $hasSlidingDoorBtn
            ))}
            ";
        }

        $mainNav = getCPWidgetObj('core_mainNav');
        $topMostRooms = "{$mainNav->getWidget(array(
             'btnPos' => 'Top Most'
            ,'class'  => 'topList'
        ))}
        ";

        if($cpCfg['cp.fullWidthTemplte'] && !$cpCfg['cp.placeNavInsideHeaderTag']){
	    $pSiteSearch = getCPPluginObj('common_siteSearch');
            $text = "
            <nav id='nav' role='navigation'>
                <a id='navigation' name='navigation'></a>
                <div class='page_margins'>
                    <div class='page'>
                        {$widget}
                        {$pSiteSearch->view->getSearchBox()}
                        {$topMostRooms}            
                    </div>
                </div>
            </nav>
            ";
        } else {
            $text = "
            <nav id='nav' class='{$extraClass}'>
                <a id='navigation' name='navigation'></a>
                {$widget}
            </nav>
            ";
        }

        return $text;
    }
 
    /**
     *
     */

    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            print "<h3>{$clsName}->{$actionTemp} does not exist";
            exit();
        }

        $text = "
        <div class='bodyPanel'>
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');


		$text = "
        <div class='captionLogo'></div>
		";

		return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $text = "
            ";
        }

        return $text;
    }

}