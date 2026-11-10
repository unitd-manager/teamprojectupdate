<?
class CP_Www_Themes_Rugby_View extends CP_Www_Lib_ThemeViewAbstract
{
    /**
     *
     */

    function getFooterPanel(){
        $ln       = Zend_Registry::get('ln');
        $navPanel = $this->getNavPanel();
        $wRecord1 = getCPWidgetObj('content_record');

        $text = "
        <div class='floatbox'>
            {$navPanel}
        <div class='socialIcons floatbox'>
            {$wRecord1->getWidget(array(
                'contentType' => 'Social Icons'
                ,'showDesc'  => FALSE
            ))}
        </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $wRecordTestimonials = getCPWidgetObj('content_record');

        $loginText = '';
        $cartText = '';
        $siteSearch = '';
        $topMostRooms = '';
        $langToggle  = '';

        if ($cpCfg['cp.showLoginInfoAtTheTop']){
            $pLogin = getCPPluginObj('member_login');
            $loginText = "
            {$pLogin->view->getLoginInfoText()}
            ";
        }

        if ($cpCfg['cp.showViewCartAtTheTop']){
            $wBasket = getCPWidgetObj('ecommerce_addToCart');
            $cartText = "
            {$wBasket->view->getViewBasketText()}
            ";
        }


        if ($cpCfg['cp.showSiteSearchAtTheTop']){
            $siteSearch = "
            {$pSiteSearch->view->getSearchBox()}
            ";
        }

        if ($cpCfg['cp.showTopMostSections']){
            $mainNav = getCPWidgetObj('core_mainNav');
            $topMostRooms = "{$mainNav->getWidget(array(
                 'btnPos' => 'Top Most'
            ))}
            ";
        }

        if ($cpCfg['cp.showLangToggleAtTheTop']){
            $wLang = getCPWidgetObj('common_language');
            $langToggle = "{$wLang->getWidget(array(
            ))}";
        }
        $mediaExp = array(
            'folder' => 'large'                
        );

        $wSlideshow = getCPWidgetObj('media_simpleFadeSlideshow');
        $slideshow = $wSlideshow->getWidget(array(
             'width' => '900'
            ,'height' => '300'
        ));
        $text = "
        {$topMostRooms}
        {$loginText}
        {$cartText}
        {$siteSearch}
        {$langToggle}
        <div class='headerContent floatbox'>
            {$slideshow}
    	</div>
        ";

        return $text;
    }
    /**
     *
     */
    
}