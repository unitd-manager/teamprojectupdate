<?
class CP_Www_Themes_Directory_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jscrollpane-2.0');

    /**
     *
     */
    function getHeaderPanel(){
        $cpUrl = Zend_Registry::get('cpUrl');

        /** create an instance of the widget **/
        $pSiteSearch = getCPPluginObj('common_siteSearch');
        $pLogin = getCPPluginObj('member_login');
        $wLang = getCPWidgetObj('common_language');
        $wCountry = getCPWidgetObj('common_country');
        $wBanner = getCPWidgetObj('media_banner');
        $wRecordSocial = getCPWidgetObj('content_record');

        $searchFormAction = $cpUrl->getUrlBySecType('Business');

        if (isLoggedInWWW()){
            $loginText = $this->getLogoutLink();
        } else {
            $loginText = $this->getLoginLink();
        }

        $text = "
        <div id='topLinksWrapper'>
            <div class='floatbox'>
                <div class='float_right m0 mt5'>
                    {$loginText}
                </div>
                <div class='float_right m0 mr5 social'>
                    {$wRecordSocial->getWidget(array(
                         'contentType' => 'Social Media Icons'
                    ))}
                </div>
                <div class='float_right m0 mr5 mt5'>
                    {$wLang->getWidget()}
                </div>
                <div class='float_right m0 mr5 mt5'>
                    {$wCountry->getWidget(array(
                         'showAsMenu' => true
                        ,'ulClass' => ''
                    ))}
                </div>
            </div>
        </div>

        {$pSiteSearch->view->getSearchBox(array('url' => $searchFormAction))}
        {$wBanner->getWidget()}
        ";

        return $text;
    }

    /**
     *
     */
    function getLoginLink() {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $loginUrl = $cpUrl->getUrlBySecType('Login');
        $busLoginUrl = $cpUrl->getUrlBySecType('Business Login');
        $registerUrl = $cpUrl->getUrlBySecType('Register');

        $text = "
        <a class='btnLogin mr5' href='{$loginUrl}'>
            <span>{$ln->gd('w.member.loginForm.form.lbl.userLogin')}</span>
        </a>
        |&nbsp;
        <a class='btnLogin' href='{$busLoginUrl}'>
            <span>{$ln->gd('w.member.loginForm.form.lbl.businessLogin')}</span>
        </a>
        |&nbsp;
        <a class='btnLogin' href='{$registerUrl}'>
            <span>{$ln->gd('w.member.loginForm.btn.register')}</span>
        </a>
        ";
        return $text;
    }

    /**
     *
     */
    function getLogoutLink() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $logoutUrl = '/index.php?plugin=member_login&_spAction=logout';

        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        if ($userType == 'directory_contact'){
            $profileUrl = $cpUrl->getUrlByCatType('Dashboard');
        } else {
            $profileUrl = $cpUrl->getUrlByCatType('Business Dashboard');
        }

        $text = "
        {$ln->gd('p.member.login.lbl.welcome')} {$_SESSION['cpUserFullNameWWW']} |
        <a class='btnLogout' href='{$profileUrl}'>
            <span>{$ln->gd('cp.header.lbl.profile')}</span>
        </a> |
        <a class='btnLogout' href='{$logoutUrl}'>
            <span>{$ln->gd('logout')}</span>
        </a>
        ";
        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $subNav = Zend_Registry::get('subNav');
        $clsInst = Zend_Registry::get('currentModule');
        if (method_exists($clsInst, 'getLeftPanel')) {
            $text = $clsInst->getLeftPanel();
        } else {
            $subNav  = getCPWidgetObj('core_subNav');

            if ($tv['secType'] == 'Business Profile'){
                $text = $this->getBusinessProfileLeftPanel();
            } else if ($tv['secType'] == 'My Profile'){
                $text = $this->getProfileLeftPanel();

            } else if ($tv['secType'] == 'Business'){
                $text = $this->getBusinessLeftPanel();

            } else if ($tv['secType'] == 'Public Profile'){
                $text = getCPViewObj('directory_contact')->getPublicProfileLeftPanel();

            } else {
                $secRec = getCPModelObj('webBasic_section')->getRecordByType('Business');
                $text = "
                {$subNav->getWidget(array(
                     'section_id' => $secRec['section_id']
                    ,'title' => $ln->gd('w.core.subNav.lbl.byCategory')
                ))}
                <div class='leftPanelBox'>
                    <div class='boxTop'>
                        <div class='boxBtm'>
                            <div class='title'>Reserved for Ideas</div>
                        </div>
                    </div>
                </div>
                ";
            }

        }

        return $text;
    }


    /**
     *
     */
    function getBusinessLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $cpUrl = Zend_Registry::get('cpUrl');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $pager = Zend_Registry::get('pager');
        $mainNav = Zend_Registry::get('mainNav');
        $modelHelper = Zend_Registry::get('modelHelper');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');
        $area_id = $fn->getReqParam('area_id');
        $my_area_id = $fn->getReqParam('my_area_id');
        $cpContactId = $fn->getSessionParam('cpContactId');

        $filterKeyword = '';
        if ($tv['keyword'] != ''){
            $filterKeyword = "
            <div class='floatbox filter'>
                <div class='float_left'>{$tv['keyword']}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('keyword')}'></a></div>
            </div>
            ";
        }

        $filterCat = '';
        if ($tv['subRoom'] != ''){
            $filterCat = "
            <div class='floatbox filter'>
                <div class='float_left'>{$tv['catTitle']}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('subRoom')}'></a></div>
            </div>
            ";
        }

        $filterSubCat = '';
        if ($tv['subCat'] != ''){
            $filterSubCat = "
            <div class='floatbox filter'>
                <div class='float_left'>{$tv['subCatTitle']}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('subCat')}'></a></div>
            </div>
            ";
        }

        $filterState = '';
        if ($state_id != ''){
            $filterState = "
            <div class='floatbox filter'>
                <div class='float_left'>{$fn->getRecordTitleByID('state', 'state_id', $state_id)}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('state')}'></a></div>
            </div>
            ";
        }

        $filterArea = '';
        if ($area_id != ''){
            $filterArea = "
            <div class='floatbox filter'>
                <div class='float_left'>{$fn->getRecordTitleByID('area', 'area_id', $area_id)}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('area')}'></a></div>
            </div>
            ";
        }

        if ($my_area_id != ''){
            $condn = "contact_id = '{$cpContactId}' AND area_id = '{$my_area_id}'";
            $myAreaRec = $fn->getRecordByCondition('contact_area', $condn);
            $filterArea = "
            <div class='floatbox filter'>
                <div class='float_left'>{$myAreaRec['title']}</div>
                <div class='float_right removeFilter'><a href='{$this->getUrlBusinessSearch('my_area')}'></a></div>
            </div>
            ";
        }

        //*************************** CAT / SUB CAT ********************************//
        $catCounter = 0;
        $catRows = '';
        if ($tv['subRoom'] == ''){
            $catSubCatArr = $mainNav->model->getMenuDataArrayForCatSubCat($tv['room']);
            $categories   = $catSubCatArr['categories'];
            foreach($categories AS $catId => $rowCat) {
                $catCounter++;
                $subCategories = $fn->getIssetParam($rowCat, 'subCategories');

                $subCatRows = '';
                foreach($subCategories AS $subCatId => $rowSubCat) {
                    $url = $this->getUrlBusinessSearch('subCat', "_subRoom={$catId}&_subCat={$subCatId}");
                    $subCatRows .= "
                    <li>
                        <a href='{$url}'>{$rowSubCat['title']}</a>
                    </li>
                    ";
                }

                $subCatRows = ($subCatRows != '') ? "<ul>{$subCatRows}</ul>" : '';
                $url = $this->getUrlBusinessSearch('subRoom', "_subRoom={$catId}");

                $catRows .= "
                <li>
                    <a href='{$url}'>{$rowCat['title']}</a>
                    {$subCatRows}
                </li>
                ";
            }

            $catRows = "
            <span class='groupTitle'>Categories</span>
            <ul class='levels-two'>{$catRows}</ul>
            ";


        } else if ($tv['subRoom'] != '' && $tv['subCat'] == ''){
            $subCatArr = $mainNav->model->getMenuDataArrayForSubCat($tv['subRoom']);
            $subCategories = $subCatArr['subCategories'];

            foreach($subCategories AS $subCatId => $rowSubCat) {
                $url = $this->getUrlBusinessSearch('subCat', "_subRoom={$tv['subRoom']}&_subCat={$subCatId}");
                $catRows .= "
                <li>
                    <a href='{$url}'>{$rowSubCat['title']}</a>
                </li>
                ";
            }

            $catRows = "
            <span class='groupTitle'>Sub Categories</span>
            <ul class='levels-one'>{$catRows}</ul>
            ";
        }

        if ($catRows != ''){
            $catRows = "
            <div class='jsTreeMenu cpLoading' id='menuCatSubCat'>
                <ul>
                    <li id='menuCatSubCat_html'>
                        {$catRows}
                    </li>
                </ul>
            </div>
            ";
        }

        //*********************** BY LOCATION *************************//
        $myLocationRows = '';

        if ($this->controller->isLoggedInUser()){
            //$areaArr = $modelHelper->getLinkDataArrayByModule('directory_contact', 'directory_areaLink', $cpContactId);

            $SQL = "
            SELECT *
            FROM contact_area
            WHERE contact_id = '{$cpContactId}'
            ORDER BY title
            ";

            $areaResult = $db->sql_query($SQL);
            $areaArr = $dbUtil->getResultsetAsArray($areaResult);
            foreach($areaArr AS $areaRow){
                $url = $this->getUrlBusinessSearch('area', "my_area_id={$areaRow['area_id']}");
                $myLocationRows .= " <li class='myLocation'><a href='{$url}'>{$areaRow['title']}</a></li>";
            }
        }

        $locationRows = '';
        if ($state_id == '' && $my_area_id == ''){
            $stateArr = getCPModuleObj('directory_state')->model->getDataArrayForSearch();
            foreach($stateArr AS $rowState) {
                $areaArr = getCPModuleObj('directory_area')->model->getDataArrayForSearch($rowState['state_id']);

                $areaRows = '';
                foreach($areaArr AS $rowArea) {
                    $url = $this->getUrlBusinessSearch('area', "area_id={$rowArea['area_id']}&state_id={$rowState['state_id']}");
                    $areaRows .= "
                    <li>
                        <a href='{$url}'>{$rowArea['title']}</a>
                    </li>
                    ";
                }

                $areaRows = ($areaRows != '') ? "<ul>{$areaRows}</ul>" : '';
                $url = $this->getUrlBusinessSearch('state', "state_id={$rowState['state_id']}");
                $locationRows .= "
                <li>
                    <a href='{$url}'>{$rowState['title']}</a>
                    {$areaRows}
                </li>
                ";
            }

            $locationRows = "
            <span class='groupTitle'>Location</span>
            <ul class='levels-two'>
                {$myLocationRows}
                {$locationRows}
            </ul>
            ";

        } else if ($area_id == '' && $my_area_id == ''){
            $areaArr = getCPModuleObj('directory_area')->model->getDataArrayForSearch($state_id);

            $areaRows = '';
            foreach($areaArr AS $rowArea) {
                $url = $this->getUrlBusinessSearch('area', "area_id={$rowArea['area_id']}");
                $areaRows .= "
                <li>
                    <a href='{$url}'>{$rowArea['title']}</a>
                </li>
                ";
            }

            $locationRows = "
            <span class='groupTitle'>Area</span>
            <ul class='levels-one'>{$areaRows}</ul>
            ";
        }

        if ($locationRows != ''){
            $locationRows = "
            <div class='jsTreeMenu cpLoading' id='menuLocation'>
                <ul>
                    <li id='menuLocation_html'>
                        {$locationRows}
                    </li>
                </ul>
            </div>
            ";
        }
        //*********************** BY FEATURES *************************//
        $featuresRows = "
        {$this->getFeatureRow('ATM', 'feature_atm')}
        {$this->getFeatureRow('Wheelchair Access', 'feature_wheelchair_access')}
        {$this->getFeatureRow('Free WiFi', 'feature_wifi')}
        ";

        if ($tv['catType'] == 'Restaurants'){
            $featuresRows .= "
            {$this->getFeatureRow('Breakfast', 'feature_breakfast')}
            {$this->getFeatureRow('Tea', 'feature_tea')}
            {$this->getFeatureRow('Brunch', 'feature_brunch')}
            {$this->getFeatureRow('Lunch', 'feature_lunch')}
            {$this->getFeatureRow('Dinner', 'feature_dinner')}
            ";
        }
                
        $action = $_SERVER['REQUEST_URI'];
        $featuresRows = "
        <div class='menuFeatures' id='menuFeatures'>
            <span class='featureTitle'>Features</span>
            <form action='{$action}' method='get' id='frmFeatures'>
                <ul class='noDefault'>
                    {$featuresRows}
                </ul>
                {$cpUrl->getQstrInHiddenVars(array('_subRoom', '_subCat', 'state_id', 'area_id'))}
                <input type='hidden' name='searchDone' value='1'>
            </form>
        </div>
        ";

        //***********************************************************//
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jsTree-1.0'));

        $clearText = '';
        if ($tv['searchDone'] == 1 || $tv['keyword'] != ''){
            $secUrl = $cpUrl->getUrlBySecType('Business');
            $clearText = "<div id='clearSearch'><a href='{$secUrl}'>Clear Search</a></div>";
        }

        $text = "
        <div class='leftPanelBox mb10'>
            <div class='boxTop'>
                <div class='boxBtm'>
                    <div class='title'>{$ln->gd('w.core.subNav.lbl.refineSearch')}</div>
                    {$clearText}
                    <div class='menuWrapper'>
                        {$filterKeyword}
                        {$filterCat}
                        {$filterSubCat}
                        {$catRows}
                        {$filterState}
                        {$filterArea}
                        {$locationRows}
                        {$featuresRows}
                    </div>
                </div>
            </div>
        </div>
        ";
        return $text;
    }


    //==================================================================//
    function getFeatureRow($lbl, $fldName){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $inSearch  = $fn->getReqParam($fldName);
        $fld_name = 'cp.search.show.' . $fldName;
        
        if ($cpCfg[$fld_name] == 0){
            return;
        }
        
        $checked = ($inSearch !='') ? " checked='checked'" : '';
        $text = "
        <li id='{$fldName}' class='featureCbox'>
            <input type='checkbox' name='{$fldName}' value='1' id='fld_{$fldName}'{$checked}>
            <label for='fld_{$fldName}'>{$lbl}</label>
        </li>
		";

        return $text;
    }

    /**
     *
     */
    function getUrlBusinessSearch($toRemove = '', $append = '') {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        $state_id = $fn->getReqParam('state_id');
        $area_id = $fn->getReqParam('area_id');
        $my_area_id = $fn->getReqParam('my_area_id');

        $secUrl = $cpUrl->getUrlBySecType('Business');
        $qstr = 'searchDone=1&';

        if ($tv['subRoom'] != '' && $toRemove != 'subRoom'){
            $qstr .= "_subRoom={$tv['subRoom']}&";
        }

        if ($tv['subCat'] != '' && ($toRemove != 'subCat' && $toRemove != 'subRoom')){
            $qstr .= "_subCat={$tv['subCat']}&";
        }

        if ($state_id != '' && $toRemove != 'state'){
            $qstr .= "state_id={$state_id}&";
        }

        if ($area_id != '' && ($toRemove != 'state' && $toRemove != 'area')){
            $qstr .= "area_id={$area_id}&";
        }

        if ($my_area_id != '' && ($toRemove != 'state' && $toRemove != 'my_area')){
            $qstr .= "my_area_id={$my_area_id}&";
        }

        $qstr .= ($append != '') ? $append : '';

        $finalUrl = ($qstr != '') ? $secUrl . '?' . $qstr : $secUrl;
        return $finalUrl;
    }

    /**
     *
     */
    function getProfileLeftPanel(){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $subNav = Zend_Registry::get('subNav');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        $exp = array('limit' => 1, 'folder' => 'normal');
        $cpContactId = $fn->getSessionParam('cpContactId');
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $cpContactId);
        $pic = $media->getMediaPicture('directory_contact', 'picture', $cpContactId, $exp);

        if ($pic == ''){
            $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/contact-icon-big.png' />";
        }

        $pictureUrl = $cpUrl->getUrlByCatType('My Photos');
        $publicUrl = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$cpContactId}";

        $text = "
        <div class='leftPanelBox'>
            <div class='boxTop'>
                <div class='boxBtm'>
                    <div class='title'>{$contactRec['first_name']} {$contactRec['last_name']}</div>
                    <div class='inner'>
                        <div class='pic'>{$pic}</div>
                        <div class='floatbox'>
                            <div class='float_left contactName'>
                                    <a href='{$publicUrl}'>My Public Profile</a>
                            </div>
                            <div class='float_right changePic'>
                                <a href='{$pictureUrl}'>Change Picture</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {$subNav->getWidget(array(
            'title' => $ln->gd('w.core.subNav.lbl.byCategory')
        ))}
        ";
        return $text;
    }

    /**
     *
     */
    function getBusinessProfileLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $subNav = Zend_Registry::get('subNav');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $busObj = getCPModuleObj('directory_business');

        $exp = array('limit' => 1, 'folder' => 'normal');
        $cpBusinessId = $fn->getSessionParam('cpBusinessId');

        $pic = '';
        $title = '';
        if ($cpBusinessId != ''){
            $pic = $media->getMediaPicture('directory_business', 'picture', $cpBusinessId, $exp);
            $busRec = $fn->getRecordRowByID('business', 'business_id', $cpBusinessId);
        }


        if ($pic == ''){
            $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/business-icon.png' />";
        }

        $pictureUrl = $cpUrl->getUrlByCatType('Business Photos');

        $text = "
        <div class='leftPanelBox'>
            <div class='boxTop'>
                <div class='boxBtm'>
                    <div class='title'>Business in Focus</div>
                    <div class='inner'>
                        <div class='pic'>{$pic}</div>
                        <div class='floatbox'>
                            <div class='float_right changePic'>
                                <a href='{$pictureUrl}'>Change Picture</a>
                            </div>
                        </div>
                        <div class='busName'>{$busObj->view->getBusinessDropdown()}</div>
                    </div>
                </div>
            </div>
        </div>
        {$subNav->getWidget(array(
            'title' => 'Views'
        ))}
        ";
        return $text;
    }

    /**
     *
     */
    function getRightPanel(){
        return;

        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $subRoomsArray = Zend_Registry::get('subRoomsArray');
            $style = isset($subRoomsArray[$tv['subRoom']]) ? $subRoomsArray[$tv['subRoom']]['css_style_name'] : '';

            $text = "
            <img src='/www/images/home-ads.gif' />
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $mainNav = Zend_Registry::get('mainNav');
        $wNewsletterSignup = getCPWidgetObj('member_newsletterSignup');
        $wSocialTwitter = getCPWidgetObj('social_twitter');
        $currentYear = date('Y');
        $copyText = str_replace("[[current_year]]", $currentYear, $ln->gd('cp.footer.leftText'));
        
        $text = "
        <div class='subcolumns footerTop'>
            <div class='c33l quickLinks'>
                <div class='subcl'>
                    <h4>{$ln->gd('cp.footer.quickLinks')}</h4>
                    {$mainNav->getWidget(array('btnPos' => 'Bottom', 'class' => '', 'ulClass' => 'noDefault'))}
                </div>
            </div>
            <div class='c33l latestTweets'>
                <div class='subc'>
                    <h4>{$ln->gd('cp.footer.latestTweets')}</h4>
                    {$wSocialTwitter->getWidget(array(
                    ))}
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr'>
                    <h4>{$ln->gd('cp.footer.aboutCompany')}</h4>
                    <div>{$ln->gd('cp.footer.aboutCompanyText')}</div>
                    <h4 class='mt20'>{$ln->gd('cp.footer.subscribe')}</h4>
                    {$wNewsletterSignup->getWidget(array(
                         'showCaptcha'  => false
                        ,'showEmailOnly' => true
                    ))}
                </div>
            </div>
        </div>
        <div class='floatbox'>
            <div class='float_left'>
                {$copyText}
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
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $clsInst = Zend_Registry::get('currentModule');
        $action = Zend_Registry::get('action');

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
        if ($tv['secType'] != 'Home' && $tv['secType'] != 'Business'){
            $pageTitle = "
            <h1 class='shade'>{$fn->getPageTitle()}</h1>
            ";
        }

        $text = "
        <div class='bodyPanel'>
            <div class='bodyPanelTop'>
                <div class='bodyPanelBtm'>
                    {$pageTitle}
                    {$clsInst->getController()}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPagerPanel($linkRecType = '') {
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');

        $text = "
        <div class='floatbox'>
            {$pager->getNavButtons(5, '', $linkRecType)}
        </div>
        ";

        return $text;
    }

    function getActionButtons(){
        $action = Zend_Registry::get('action');
        $actionBtns = $action->getActionButtons();

        if ($actionBtns != '') {
            $actionBtns = "
            <div class='hlist actionBtns noBg'>
                {$actionBtns}
            </div>
            ";
        }

        $text = "
        {$actionBtns}
        ";

        return $text;
    }
}