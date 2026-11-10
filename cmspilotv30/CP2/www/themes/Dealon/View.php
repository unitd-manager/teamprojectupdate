<?
class CP_Www_Themes_Dealon_View extends CP_Www_Lib_ThemeViewAbstract
{
    //var $jssKeys = array('jqForm-2.69');
    
    /*
     *
     */
    function getExtendedPanel() {
    }

    /*
     *
     */
    function getBodyPanel1() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');
        $subNav = Zend_Registry::get('subNav');
        $db = Zend_Registry::get('db');

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
                
        $SQL   = "
        SELECT c.title AS category_title
        FROM category c
        LEFT JOIN section s ON (c.section_id = s.section_id)
        WHERE s.section_type = 'Product'
        ";
        $result = $db->sql_query($SQL);
        $rows = $db->sql_numrows($result);
        
        $subCat = '';
        while ($row = $db->sql_fetchrow($result)) {
            $subCat .= $row['category_title'];
        }
        
        $text = "
        <div class='bodyPanel'>
            {$subNav->getWidget(array(
                'section_id'  => 13
            ))}
            {$clsInst->getController()}
        </div>
        ";
        return $text;
    }
      
    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSignupModalDisplayedAlready = $fn->getSessionParam('cpSignupModalDisplayedAlready');
        
        $mainNav = getCPWidgetObj('core_mainNav');
        $sectionsInFooter = " {$mainNav->getWidget(array(
            'btnPos' => 'Bottom'
           ,'class'=> 'footerSection'
        ))}";

        $text = "        
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
                {$sectionsInFooter}
            </div>
        </div>
        <input type='hidden' id='cpSignupModalDisplayedAlready' value='{$cpSignupModalDisplayedAlready}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getHeaderPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $subNav = getCpWidgetObj('core_subNav');
        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $tv = Zend_Registry::get('tv');

        $loginText = '';
        $cartText = '';
        $siteSearch = '';
        $topMostRooms = '';
        $langToggle  = '';
        $logoText = '';
        $socialIcons = '';
        
        if ($cpCfg['cp.showLoginInfoAtTheTop']){
            $pLogin = getCPPluginObj('member_login');
            if (isLoggedInWWW()){
                $loginText = "
                {$pLogin->view->getLoginInfoText()}
                ";
            }
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
        
        $wRecord = getCPWidgetObj('member_newsletterSignup');
        $newsletter = $wRecord->getWidget(array(
        ));                  

        $register = '';
        if (!isLoggedInWWW()){
            $register = "
            <div class='registerInfoText'>
                <a href='/eng/register/'>{$ln->gd('cp.register')}</a>
            </div>
            ";
        }

        $catFilter = '';
        if ($tv['sectionType'] = 'Basket'){
            $catFilter = "";
        } else {
            $catFilter = "
            <div class='catFilter'>
                {$this->getQuickSearch()}
            </div>
            ";
        }

        $text = "
        {$cartText}
        {$loginText}
        {$newsletter}
        {$siteSearch}
        {$logoText}
        {$socialIcons}
        {$subNav->getWidget(array(
            'section_id'  => 13
        ))}
        <div class='catFilter'>
            {$this->getQuickSearch()}
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
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $text = '';

            $SQL = "
            SELECT p.*
                  ,c.title As category_title
            FROM product p
            LEFT JOIN category c ON (c.category_id = p.category_id)            
            WHERE p.published = 1
            ORDER BY product_id
            limit 0,10
            ";
            $result = $db->sql_query($SQL);
            while($row = $db->sql_fetchrow($result)){
                $exp = array('zoomImage' => false, 'folder' => 'normal');
                $pic = $media->getMediaPicture('ecommerce_product', 'picture', $row['product_id'], $exp);
                $url = $cpUrl->getUrlByRecord($row, 'product_id', array('secType' => 'Product'));

                $text .= "
                <div class='moreDealProduct'>
                    <div class='save'>{$ln->gd('m.ecommerce.product.lbl.save')}<br>{$row['save_percent']}%</div>
                    <div class='productPic'><a href='{$url}'>{$pic}</a></div>
                    <div class='mb50'>{$row['title']}</div>
                </div>
                ";
            }

            $text = "
            <div class='moreDeals'>
            <h1 class='txtCenter mb30'>{$ln->gd('cp.lbl.moreDeal')}</h1>
            {$text}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        
        $SQLCategory = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN section s ON c.section_id = s.section_id
        WHERE s.section_type = 'Product'
        AND c.show_in_nav = 0
        AND c.published = 1
        ORDER BY c.title
        ";
        $result = $db->sql_query($SQLCategory);
        $row = $db->sql_fetchrow($result);
        
        $category = $dbUtil->getDropDownFromSQLCols2($db, $SQLCategory);
        
        $urlArray = array();

        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Product');
        $urlArray['section_title'] = $secRec['title'];
        $urlArray['category_id']    = $row['category_id'];
        $urlArray['category_title'] = $row['title'];
        $url = $cpUrl->make_seo_url($urlArray);
        
        $formAction = $url;
        
        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
        <div class='quickSearch'>
            <div class='category'>
                <select name='category_id'>
                    <option value=''>{$ln->gd('m.ecommerce.product.lbl.searchByCategory')}</option>
                    {$category}
                </select>
            </div>
        </div>
        </form>
        ";

        return $text;
    }

}