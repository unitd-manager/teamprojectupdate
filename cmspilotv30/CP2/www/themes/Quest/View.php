<?
class CP_Www_Themes_Quest_View extends CP_Www_Lib_ThemeViewAbstract
{
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
        $registerText = '';

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

        if ($cpCfg['cp.showRegisterText']){
            $wRecordRegister = getCPWidgetObj('content_record');
            $registerText = "
            <div class='registerText'>
                {$wRecordRegister->getWidget(array(
                     'contentType' => 'CPE Info'
                ))}
            </div>
            ";
            //$registerText = "<div class='registerText'>{$ln->gd('cp.registerText')}</div>";
        }

        $text = "
        {$registerText}
        {$topMostRooms}
        {$loginText}
        {$cartText}
        {$siteSearch}
        {$langToggle}
        {$logoText}
        {$socialIcons}
        ";

        return $text;
    }
    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        if ($cpCfg['cp.showMainNavPanelAtTop']){

            $extraClass = '';
            if ($cpCfg['cp.showNavAsMenu']){
                $superFish = getCPWidgetObj('menu_superFish');
                $widget = "{$superFish->getWidget(array(
                    'btnPos' => 'Top'
                ))}
                ";

                $extraClass = 'hasMenu clearfix';
                $text = "
                <div id='nav' class='hasMenu clearfix'>
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                </div>
                ";
            } else {
                $mainNav = Zend_Registry::get('mainNav');
                $hasSlidingDoorBtn = $cpCfg['w.core_mainNav.hasSlidingDoorBtn'];

                $widget = "{$mainNav->getWidget(array(
                     'btnPos' => 'Top'
                    ,'hasSlidingDoorBtn' => $hasSlidingDoorBtn
                ))}
                ";
            }

            if($cpCfg['cp.fullWidthTemplte'] && !$cpCfg['cp.placeNavInsideHeaderTag']){
                $text = "
                <div id='nav' role='navigation'>
                    <a id='navigation' name='navigation'></a>
                    <div class='page_margins'>
                        <div class='page'>
                            {$widget}
                        </div>
                    </div>
                </div>
                ";
            } else {
                $wBreadcrumb = getCPWidgetObj('common_breadcrumb');
                $themePath = CP_THEMES_PATH2_ALIAS . $cpCfg['cp.theme'] . '/images/';
                $seperator = "<img src='{$themePath}arrow_grey.png' />";
                $text = "
                <div id='nav' class='{$extraClass}'>
                    {$wBreadcrumb->getWidget(array(
                          'hideInHome' => true
                         ,'seperator' => $seperator
                    ))}
                    <a id='navigation' name='navigation'></a>
                    {$widget}
                </div>
                ";
            }

        }
        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');

        if (method_exists($clsInst, 'getLeftPanel')) {
            $text = $clsInst->getLeftPanel();
        } else {
            $mainNav = getCPWidgetObj('core_mainNav');
            $subNav  = getCPWidgetObj('core_subNav');
            $wRecordSocial = getCPWidgetObj('content_record');

            $calloutLeft = '';
            if ($tv['secType'] == 'Home'){
                $wRecord1 = getCPWidgetObj('content_record');
                $wRecord2 = getCPWidgetObj('content_record');
                $wRecord4 = getCPWidgetObj('content_record');
                $wRecordFav = getCPWidgetObj('content_record');

                //{$this->getAdBlock()}
                                
                $wLogin = getCPWidgetObj('member_loginForm');
                $loginForm = $wLogin->getWidget(array(
                     'hasRegiserInfo' => $cpCfg['m.membership.allowRegistration']
                    ,'loginTypeArr' => array('pms_contact' => 'Individual', 'pms_company' => 'Company')
                    ,'loginType' => 'pms_contact'
                ));
                
                $register = '';
                if (!isLoggedInWWW()){
                    $register = "<div class='mt10 home-registration'>{$loginForm}</div>";
                }

                $calloutLeft = "
                <div class='box calloutLeft'>
                    {$wRecord2->getWidget(array(
                         'contentType' => 'Callout Left'
                        ,'showDesc' => false
                        ,'showReadMore' => true
                    ))}
                </div>
                
                {$register}

                <div class='box newProgramme'>
                    {$wRecordFav->getWidget(array(
                         'contentType'    => 'Record'
                        ,'showDate'       => false
                        ,'specialFilter'  => 'Favourite'
                        ,'showShortDesc'  => true
                        ,'showDesc'       => false
                        ,'showPic'        => false
                        ,'heading'        => $ln->gd('w.content.record.newProgramme.heading')
                        ,'showReadMore'   => true
                    ))}
                </div>                
                ";
            }

            $text = "
            {$mainNav->getWidget(array(
                 'btnPos' => 'Left'
                ,'class'  => ''
            ))}
            {$calloutLeft}
            {$subNav->getWidget(array(
                'showSubCat' => false
            ))}
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

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_s3Slider');
        
        $homeRec = getCPModuleObj('webBasic_section')->model->getRecordByType('Home');
        $text = "
        <div class='bodyPanel'>
            {$wSlideshow->getWidget(array(
                 'sectionId' => $homeRec['section_id']
                ,'width' => 672
                ,'height' => 198
            ))}
            {$clsInst->getController()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMainBottomPanel(){
        $tv = Zend_Registry::get('tv');
        $wCarousel = getCPWidgetObj('media_carousel');
        
        $text = '';
        if ($tv['secType'] == 'Home'){
            $text = "
            <div class='ourSponsors floatbox'>
                {$wCarousel->getWidget(array(
                    'contentType' => 'Callout Bottom'
                   ,'mediaFolderThumb' => 'normal'
                ))}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $wRecord1 = getCPWidgetObj('content_record');

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_left socialMediaIcons'>
                {$wRecord1->getWidget(array(
                     'contentType' => 'Social Media Icons'
                ))}
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
    function getAdBlock() {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        
        $text = '';
        
        $SQL = "
        SELECT c.*
        FROM content c
        LEFT JOIN (section s)      ON (c.section_id       = s.section_id)
        WHERE s.section_type = 'Home'
        AND   c.content_type = 'Ad Block'
        ";
        $result = $db->sql_query($SQL);
        
        while($row = $db->sql_fetchrow($result)){
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id']);
            $wRecord1 = getCPWidgetObj('content_record');
            
            $wSlideshow = getCPWidgetObj('media_anythingSlider');
            $slideshow = $wSlideshow->getWidget(array(
                 'contentType'    => 'Ad Block'
                ,'width'          => 245
                ,'height'         => 117
                ,'showNav'        => false
                ,'showNavArrows'  => false
                ,'type'           => 'picByContentType'
            ));            
            
            if ($pic == '') {
                $text = "
                <div class='box ad'>
                    {$wRecord1->getWidget(array(
                         'contentType' => 'Ad Block'
                        ,'showDesc' => false
                        ,'showReadMore' => true
                    ))}
                </div>
                ";
            } else {
                $text = "
                <div class='box ad'>
                    {$slideshow}
                </div>
                ";
            }
        }
        return $text;
    }
}