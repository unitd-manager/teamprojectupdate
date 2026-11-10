<?
class CP_Www_Themes_Restaurant_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqColorbox-1.3.15');
    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        $text = '';
        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $wRecord = getCPWidgetObj('content_record');
            $calloutRight = $wRecord->getWidget(array(
                 'contentType'    => 'Callout Right'
                ,'global'         => false
                ,'showPic'        => FALSE
            ));

            if ($calloutRight != ''){
                if ($tv['secType'] != 'Home'){
                    $tv['forcedPageCSSClass'] = 'hidenone';
                    CP_Common_Lib_Registry::arrayMerge('tv', $tv);
                }

                $text = "
                <div class='calloutRight'>
                    {$calloutRight}
                </div>
                ";
            }
        }

        return $text;
    }

    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');

        $loginText = '';
        $cartText = '';
        $siteSearch = '';
        $topMostRooms = '';
        $langToggle  = '';
        $logoText = '';
        $socialIcons = '';

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

        $wNewsletterSignup = getCPWidgetObj('member_newsletterSignup');

        $newsletter = "
        <div class='signup'>
            {$ln->gd('w.member.newsletterSignup.heading')}
            {$wNewsletterSignup->getWidget(array(
                 'showCaptcha' => false
                ,'subscribeToMailChimp' => true
            ))}
        </div>
        ";

        $text = "
        {$topMostRooms}
        {$loginText}
        {$cartText}
        {$siteSearch}
        {$langToggle}
        {$logoText}
        {$socialIcons}
        <div id='weatherInfo'>
            {$this->getWeatherData()}
        </div>
        <div id='signupTop'>
            {$this->getSignUpForm()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSignUpForm(){
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = "
        <form id='frmSignupTop' method='post' action='{$cpUrl->getUrlByCatType('Newsletter Signup')}'>
            {$ln->gd('w.member.newsletterSignup.heading')}
            <input type='text' name='email' value=''>
            <a href='#' class='send'>{$ln->gd('w.member.newsletterSignup.send')}<a/>
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $clsInst = Zend_Registry::get('currentModule');
        $ln = Zend_Registry::get('ln');

        $actionName = ($tv['action']) != '' ? ucfirst($tv['action']) : 'List';
        $actionTemp  = "get{$actionName}";  //eg: getList

        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);
            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                     'clsName' => $clsName
                    ,'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $pageTitle = '';
        if (($tv['secType'] == 'News' && $tv['action'] == 'list') || $tv['catType'] == 'Picture Grid' ||
           ($tv['secType'] == 'Product' && $tv['action'] == 'list' && $tv['subRoom'] != '')
           ){
            $pageTitle = "<h1 class='pageTitle'>{$fn->getPageTitle()}</h1>";
        }

        $content = $clsInst->getController();

        $text = "
        <div class='bodyPanel'>
            {$pageTitle}
            {$content}
        </div>
        ";

        if ($tv['catType'] == 'Wine' || $tv['catType'] == 'Cheese'){
            $tv['forcedPageCSSClass'] = 'hidecol2';
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                $('.w-media-banner').hide();
            "));
        }

        return $text;
    }


    /**
     *
     */
    function getMainBottomPanel(){
        $ln = Zend_Registry::get('ln');

        $text = "
        {$ln->gd('cp.shopLocations')}
        ";

        return $text;
    }

    /**
     *
     */
    function getWeatherData() {
        $cpUtil = Zend_Registry::get('cpUtil');
        return;
        $date = date('l d-M-y G:i:s');

        $data = $cpUtil->getGoogleWeatherData('Hong Kong');
        
        
        $text = "
        <!-- Weather -->
        <b>{$date}</b>
        &nbsp;&nbsp; Hong Kong Weather: 
        {$data['current_temperature']}&#176;C &nbsp;
        {$data['current_humidity']}
        ";
        
        return $text;
    }
}
