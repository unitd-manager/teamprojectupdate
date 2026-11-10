<?
class CP_Common_Lib_ArrayMaster
{
    var $topVars = array();

    //==================================================================//
    function __construct(){
        $this->setTopVars();
    }

    //==================================================================//
    function setTopVars(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $tv = &$this->topVars;

        if (CP_SCOPE == 'www') {
            $tv['lang'] = $this->getInterfaceLanguage();
            $tv['countryCodeReq']  = strtolower($this->getCountryCode());
        } else { //admin
            $tv['lang']         = $this->getCurrentContentLanguage();
            $tv['langAdminInt'] = $this->getAdminInterfaceLanguage();
        }

        $tv['spAction']        = htmlspecialchars(getReqParam('_spAction', '', true));
        $tv['topRm']           = htmlspecialchars(getReqParam('_topRm', '', true));

        $tv['module']          = htmlspecialchars(getReqParam('module', '', true));
        list($tv['moduleGroup']) = explode('_', $tv['module']);

        $tv['room']            = htmlspecialchars(getReqParam('_room', '', true));
        $tv['subRoom']         = htmlspecialchars(getReqParam('_subRoom', '', true));
        $tv['subCat']          = htmlspecialchars(getReqParam('_subCat', '', true));

        $tv['action']          = htmlspecialchars(getReqParam('_action', 'list'));
        $tv['actionName']      = htmlspecialchars(getReqParam('_actionName'));
        $tv['dbSearhDone']     = htmlspecialchars(getReqParam('_dbSearhDone', '', true));
        $tv['tab']             = htmlspecialchars(getReqParam('_tab', '', true));
        $tv['sortOrder']       = htmlspecialchars(getReqParam('_sortOrder', '', true));
        $tv['showBodyOnly']    = htmlspecialchars(getReqParam('_showBodyOnly', 0, true));
        $tv['printWhat']       = htmlspecialchars(getReqParam('printWhat', 'list', true));
        $tv['record_id']       = htmlspecialchars(getReqParam('record_id', '', true));
        $tv['record_title']    = htmlspecialchars(getReqParam('record_title', '', true));

        $tv['section_id']      = htmlspecialchars(getReqParam('section_id', '', true));
        $tv['category_id']     = htmlspecialchars(getReqParam('category_id', '', true));
        $tv['sub_category_id'] = htmlspecialchars(getReqParam('sub_category_id', '', true));
        $tv['keyword']         = trim(htmlentities(getReqParam('keyword', '', true), ENT_QUOTES, 'UTF-8'));
        $tv['keywordDisp']     = trim(htmlentities(getReqParam('keyword'), ENT_QUOTES, 'UTF-8'));
        $tv['searchDone']      = getReqParam('searchDone', '', true);

        $tv['srcRoom']         = htmlspecialchars(getReqParam('srcRoom', '', true));
        $tv['lnkRoom']         = htmlspecialchars(getReqParam('lnkRoom', '', true));
        $tv['linkSingle']      = htmlspecialchars(getReqParam('linkSingle', 0, true));
        $tv['selectedIDs']     = '';
        $tv['linkMasterTableID'] = htmlspecialchars(getReqParam('linkMasterTableID', '', true));
        $tv['srcRoomId']         = htmlspecialchars(getReqParam('srcRoomId', '', true));

        $tv['staff_id']                 = htmlspecialchars(getReqParam('staff_id', '', true));
        $tv['special_search']           = htmlspecialchars(getReqParam('special_search', '', true));
        $tv['status']                   = htmlspecialchars(getReqParam('status', '', true));
        $tv['newRecord']                = htmlspecialchars(getReqParam('newRecord', '', true));
        $tv['disableSEOUrlTemporarily'] = htmlspecialchars(getReqParam('disableSEOUrlTemporarily', '', true));

        //for multi-year sites
        $tv['cp_year'] = htmlspecialchars(getReqParam('cp_year', '', true));
        //for multi unique site with url as site name
        $tv['cp_site'] = htmlspecialchars(getReqParam('cp_site', '', true));

        $tv['siteType'] = htmlspecialchars(getReqParam('siteType', '', true));
        $tv['sitePfxId'] = htmlspecialchars(getReqParam('sitePfxId', '', true));
        
        $tv['cfgKeys']            = array();
        $tv['pageCSSClassTop']    = '';

        $tv['sectionType']        = '';
        $tv['categoryType']       = '';
        $tv['categoryType']       = '';
        $tv['page']               = htmlspecialchars(getReqParam('_page', '', true));

        //exclude from admin login requirement
        $tv['protSiteSpActionExceptions'] = array(
             'updateLiveServer'
            ,'updateLangFiles'
            ,'updateConfFiles'
            ,'forgotPasswordForm'
            ,'forgotPasswordSubmit'
            ,'loginSubmit'
            ,'updateMissingSaltPasswords'
            ,'smartCardLoginForm'
        );

        // this is used when we need to pass a custom action name to form the url in make_seo_url
        if($tv['actionName'] != ''){
            $tv['action'] = $tv['actionName'];
        }

        if ($cpCfg['cp.useSEOUrl'] == 1){
            $tv['room_name'] = htmlspecialchars($fn->getReqParam('room_name', '', true));

            $tv['room'] = $this->getRoomID();
            $tv['content_title']      = htmlspecialchars($fn->getReqParam('content_title'));
            $tv['category_title']     = htmlspecialchars($fn->getReqParam('category_title'));
            $tv['sub_category_title'] = htmlspecialchars($fn->getReqParam('sub_category_title'));
        }

        //==================================================================//
        if($tv['lnkRoom'] != ""){
            $tv['linkName'] = $tv['srcRoom'] . "#" . $tv['lnkRoom'];
        } else {
            $tv['linkName'] = "";
        }

        //==================================================================//
        $tv['detailPageName'] = "index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=detail";

        if ($tv['module'] == 'common_broadcast'){
            $SERVER = $_SERVER['HTTP_HOST'];
            $cpCfg['cp.fckEditorMediaFolder'] = "http://{$SERVER}" . "{$cpCfg['cp.mediaFolderAlias']}fck/";
        }

        //--- this variable is used in ckfinder/config.php --//
        $isLoggedInAdmin = isset($_SESSION['isLoggedInAdmin']) ? $_SESSION['isLoggedInAdmin'] : false;
        $isLoggedInWWW = isset($_SESSION['isLoggedInWWW']) ? $_SESSION['isLoggedInWWW'] : false;

        if ($isLoggedInAdmin && $cpCfg['cp.allowCKFinderFileUpload']){
            $_SESSION['isAuthorizedForCKFinderUpload'] = true;
            $_SESSION['fckEditorMediaFolder']  = $cpCfg['cp.fckEditorMediaFolder'];
            $_SESSION['UserFilesAbsolutePath'] = $cpCfg['cp.UserFilesAbsolutePath'];
        }

        $tv['currentViewRecType'] = ''; /** ex: section_type, category_type, sub_category_type (used in www) **/
        $tv['forcedPageCSSClass'] = ''; /** set this when you dynamically need to change the pageCSSClass irrespective sec / cat type ***/
        $tv['bodyExtraClass'] = ''; /** set this when you need to append to body class ***/

        $tv['uriWithNoQstr'] = $cpUrl->getUriWithNoQstr();

        //used in Twig templates
        $tv['isLoggedInWWW']     = false;
        $tv['cpUserNameWWW']     = '';
        $tv['cpEmail']           = '';
        $tv['cpUserFullNameWWW'] = '';
        $tv['cpLoginTypeWWW']    = '';

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        $langKeysForJS   = array();
        $langKeysForJS[] = 'cp.lbl.confirm';
        $langKeysForJS[] = 'cp.lbl.submit';
        $langKeysForJS[] = 'cp.lbl.cancel';
        $langKeysForJS[] = 'cp.lbl.close';
        $langKeysForJS[] = 'cp.lbl.save';
        $langKeysForJS[] = 'cp.lbl.success';
        $langKeysForJS[] = 'cp.lbl.error';
        $langKeysForJS[] = 'cp.lbl.updatedSuccessfully';
        $langKeysForJS[] = 'cp.lbl.processing';
        $langKeysForJS[] = 'cp.lbl.pleaseWait';

        CP_Common_Lib_Registry::arrayMerge('langKeysForJS', $langKeysForJS);
    }

    //==================================================================//
    function getRoomID(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $tv = &$this->topVars;

        $tv['room_name'] = htmlspecialchars($fn->getReqParam('room_name', '', true));

        $section_id = '';
        if ($tv['room_name'] != ""){
            $condArr = array();
            $condArr[] = "seo_title='{$tv['room_name']}'";
            if ($cpCfg['cp.hasMultiYears']) {
                $condArr[] = "cp_year = '{$tv['cp_year']}'";
            }
            if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_year'] != '') {
                $condArr[] = "cp_year = '{$tv['cp_year']}'";
            }
            if ($cpCfg['cp.hasMemberOnlySections'] == 1 && $cpCfg['cp.excludeRestrictedNavItems'] && !isLoggedInWWW()){
                $condArr[] = "member_only != 1";
            }

            $condition = join(' AND ', $condArr);

            $section_row = $fn->getRecordByCondition('section',$condition);
            if (is_array($section_row)){
                $section_id = $section_row['section_id'];
            }
        }

        return $section_id;
    }

    //==================================================================//
    function getGoogleLangKey($lang){
        $langArr =
        array(
             'chi' => 'zh'
            ,'ind' => 'id'
            ,'vie' => 'vi'
            ,'hin' => 'hi'
            ,'tha' => 'th'
        );

        $key = isset($langArr[$lang]) ? $langArr[$lang] : "";

        return $key;
    }

    /**
     *
     */
    function setArrays() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');

        $this->setJSFilesArrayCommon();
        $this->setStylesheetsArray();
        $this->setStylesheetsPatchArray();
    }

    /**
     *
     */
    function setJSFilesArrayCommon() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $jssKeys = array();
        $jsFilesArr = array();

        if($cpCfg['cp.hasModernizer']){
            array_push($jssKeys, 'modernizr-2.0.6');
        }

        if($cpCfg['cp.hasRespondJS']){
            array_push($jssKeys, 'respond-1.1.0');
        }

        if($cpCfg['cp.cssFramework'] == 'bootstrap' && !$cpCfg['cp.doNotLoadCommonBootstrapFiles']){
            array_push($jssKeys, 'html5shiv-3.6.2-ltIE9');
            array_push($jssKeys, 'respond-1.1.0-ltIE9');
        }

        array_push($jssKeys, "jq-{$cpCfg['cp.jqVersion']}");

        if($cpCfg['cp.cssFramework'] == 'bootstrap' && !$cpCfg['cp.doNotLoadCommonBootstrapFiles']){
            array_push($jssKeys, "bootstrap-{$cpCfg['cp.bootstrapVersion']}");
        }

        $jqTools = ($cpCfg['cp.hasJqTools']) ? "jqTools-{$cpCfg['cp.jqToolsVersion']}" : '';
        $jqUi = ($cpCfg['cp.hasJqUi']) ? "jqUI-{$cpCfg['cp.jqUiVersion']}" : '';
        $jqCpUtil = ($cpCfg['cp.hasJqCpUtil']) ? "jqCpUtil" : '';
        $jqLiveQuery = ($cpCfg['cp.hasJqLiveQuery']) ? "jqLiveQuery-1.03" : '';

        if (CP_SCOPE == 'admin'){
            array_push($jssKeys
                      ,$jqTools
                      ,$jqLiveQuery
                      ,'jqForm-3.15'
                      ,$jqCpUtil
                      ,$jqUi
                      ,'jqElastic-1.6.4'
                      ,'jqUISelectMenu'
                      ,'jeditable'
            );

            if ($tv['action'] == 'edit'){
                if ($cpCfg['cp.uploadTechnology'] == 'html'){
                    array_push($jssKeys, 'uploadifive-1.1.2');
                } else {
                    array_push($jssKeys, 'jqUploadify3.2');
                }
            }

        } else if (CP_SCOPE == 'www'){
            array_push($jssKeys
                ,$jqLiveQuery
                ,$jqTools
                ,$jqUi
                ,$jqCpUtil
            );
        }

        if ($cpCfg['cp.hasAngularApp']) {
            array_push($jssKeys, $cpCfg['cp.angularVersion']);
        }


        if ($cpUtil->isIEBrowser()){
            array_push($jssKeys, "jqReject-0.7-Beta");
        }

        if($cpCfg['cp.cssFramework'] == 'bootstrap'){
            $jsFilesArr[] = CP_PATH_ALIAS . "common/js/functions-bs.js";

        } else {
            $jsFilesArr[] = CP_PATH_ALIAS . "common/js/functions.js";
            $jsFilesArr[] = CP_PATH_ALIAS . "common/js/load_ready.js";
            $jsFilesArr[] = CP_MASTER_PATH_ALIAS . "js/functions.js";

            $jsLocal = CP_LOCAL_PATH . 'js/functions.js';
            if (file_exists($jsLocal)){
                $jsFilesArr[] = CP_LOCAL_PATH_ALIAS . "js/functions.js";
            }
        }

        CP_Common_Lib_Registry::arrayMerge('jsFilesArr', $jsFilesArr);
        CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
    }


    /**
     *
     * @return <type>
     */
    function setStylesheetsArray() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpPaths = Zend_Registry::get('cpPaths');

        $cssFilesArr = array();
        $cssFilesArrFirst = array();
        $cssFilesArrLast = array();
        $jssKeys = array();

        if($cpCfg['cp.cssFramework'] == 'yaml'){
            $cssFilesArr[] = "{$cpCfg['cp.jssPath']}yaml/{$cpCfg['cp.yamlVersion']}/core/base.css";

            if ($cpCfg['cp.loadCommonCssFiles']){
                $cssCommon = CP_COMMON_PATH . 'css/style.css';
                $cssMaster = CP_MASTER_PATH . 'css/style.css';
                $cssLocal  = CP_LOCAL_PATH  . 'css/style.css';

                if (file_exists($cssCommon)){
                    $cssFilesArr[] = CP_COMMON_PATH_ALIAS . 'css/style.css';
                }

                if (file_exists($cssMaster)){
                    $cssFilesArr[] = CP_MASTER_PATH_ALIAS . 'css/style.css';
                }

                if (file_exists($cssLocal)){
                    $cssFilesArr[] = CP_LOCAL_PATH_ALIAS . 'css/style.css';
                }
            }

        } else if($cpCfg['cp.cssFramework'] == 'bootstrap' && !$cpCfg['cp.doNotLoadCommonBootstrapFiles']){
            $cssFilesArr[] = CP_COMMON_PATH_ALIAS . 'css/style-bs.css';

             // remove this condition after converting all solutions to 3.0
            if ($cpCfg['cp.bootstrapVersion'] == '2.3.2'){
                if($cpCfg['cp.useCdn'] && $cpCfg['cp.loadResponsiveCSS']){
                    if ($cpCfg['cp.loadFontAwesome']){
                        $cssFilesArrFirst[] = "{$cpCfg['cp.protocol']}://netdna.bootstrapcdn.com/twitter-bootstrap/{$cpCfg['cp.bootstrapVersion']}/css/bootstrap-combined.no-icons.min.css";
                        $cssFilesArrFirst[] = "{$cpCfg['cp.protocol']}://netdna.bootstrapcdn.com/font-awesome/{$cpCfg['cp.fontAwesomeVersion']}/css/font-awesome.min.css";
                    } else {
                        $cssFilesArrFirst[] = "{$cpCfg['cp.protocol']}://netdna.bootstrapcdn.com/twitter-bootstrap/{$cpCfg['cp.bootstrapVersion']}/css/bootstrap-combined.min.css";
                    }
                } else {
                    $cssFilesArrFirst[] = "{$cpCfg['cp.jssPath']}bootstrap/core/{$cpCfg['cp.bootstrapVersion']}/css/bootstrap.min.css";
                    if ($cpCfg['cp.loadResponsiveCSS']){
                        $cssFilesArrFirst[] = "{$cpCfg['cp.jssPath']}bootstrap/core/{$cpCfg['cp.bootstrapVersion']}/css/bootstrap-responsive.min.css";
                    }
                }
            } else {
                if ($cpCfg['cp.loadFontAwesome']){
                    array_push($jssKeys, "bootstrap-{$cpCfg['cp.bootstrapVersion']}-useFontAwesome");
                } else {
                    array_push($jssKeys, "bootstrap-{$cpCfg['cp.bootstrapVersion']}-css");
                }
            }
        }

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
        CP_Common_Lib_Registry::arrayMerge('cssFilesArrFirst', $cssFilesArrFirst);
        CP_Common_Lib_Registry::arrayMerge('cssFilesArr', $cssFilesArr);
        CP_Common_Lib_Registry::arrayMerge('cssFilesArrLast', $cssFilesArrLast);
    }

    /**
     *
     * @return <type>
     */
    function setStylesheetsPatchArray() {
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $cssPatchFilesArr = array();

            $cssPatchFilesArr[] = array('if lte IE 7', "{$cpCfg['cp.jssPath']}yaml/{$cpCfg['cp.yamlVersion']}/core/iehacks.css");
            $cssPatchFilesArr[] = array('if lte IE 7', CP_MASTER_PATH_ALIAS . 'css/patch_ie.css');

            /** this is not required since we are using modenizer -- commented out, review and delete later
            if ($cpCfg['cp.useCdn']) {
                $cssPatchFilesArr[] = array('if lt IE 9', 'http://html5shiv.googlecode.com/svn/trunk/html5.js');

            } else {
                $cssPatchFilesArr[] = array('if lt IE 9', CP_LIBRARY_PATH_ALIAS . 'jss/html5.js');

            }
            ***/

            CP_Common_Lib_Registry::arrayMerge('cssPatchFilesArr', $cssPatchFilesArr);
        }
    }
}