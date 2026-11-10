<?
class CP_Www_Themes_Party_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqForm-3.15', 'jqReject-0.7-Beta', 'jqNivoSlider-2.7.1');

    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');

        $remindMeText = '';
        $loginText = '';
        $cartText = '';
        $siteSearch = '';
        $topMostRooms = '';
        $langToggle  = '';
        $logoText = '';
        $socialIcons = '';

        $remindMeText = $this->getRemindMe();
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
                'hideCurrLang' => $cpCfg['cp.hideCurrentLang']
            ))}";
        }

        if ($cpCfg['cp.showSocialIconsInHeader']){
            $wRecordSocial = getCPWidgetObj('content_record');
            $socialIcons = "
            <div class='socialMediaIcons'>
                {$wRecordSocial->getWidget(array(
                     'contentType' => 'Social Media Icons'
                ))}
            </div>
            ";
        }

        if ($cpCfg['cp.showLogoText']){
            $logoText = "<div class='logoText'>{$ln->gd('cp.logoText')}</div>";
        }

        $text = "
        {$topMostRooms}
        {$loginText}
        {$remindMeText}
        {$cartText}
        {$siteSearch}
        {$langToggle}
        {$logoText}
        {$socialIcons}
        ";

        return $text;
    }

    function getRemindMe() {
        $ln = Zend_Registry::get('ln');

        $urlRemindMe = '';
        $text = "
        <div class='remindme'>
            <a href='{$urlRemindMe}'
            class='btn-remindme'>
                <span>{$ln->gd('cp.btn.remindMe')}</span>
            </a>
        </div>
        ";
        return $text;
    }
    
    function getLeftPanel(){
        $subNav = Zend_Registry::get('subNav');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $subNav = getCPWidgetObj('core_subNav');
        if($tv['secType'] == 'Charity') {
            $text = '';
            $modCharity = getCPModuleObj('party_charity');
            $view = $modCharity->view->getList(false);

            $text = "
            {$view}
            ";
        } else {
            $text = "
            {$subNav->getWidget(array(
                'autoSelectFirstSubCategory' => true
            ))}
            ";
        }

        return $text;
    }

    function getHowItWorks(){
        $wSlideshow = getCPWidgetObj('media_carousel');
        $slideshow = $wSlideshow->getWidget(array(
              'type' => 'picture'
             ,'items' => 1
             ,'thumbnailNav' => true
             ,'fx' => 'crossfade'
        ));
        $wRecord = getCPWidgetObj('content_record');

        $text = "
        {$slideshow}

        {$wRecord->getWidget(array(
            'contentType' => 'How It Works Detail'
            ,'recordTitleHeadTag' => 'h1'
            ,'useDivContainer' => true
        ))}
        ";
        return $text;
    }

}
