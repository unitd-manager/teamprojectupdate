<?
class CP_Common_Lib_Bootstrap
{
    function startSession(){
        ob_start();
        $cpCfg = Zend_Registry::get('cpCfg');
        $sessionID = isset($_REQUEST['sessionIDCP']) ? $_REQUEST['sessionIDCP']  : "";

        if ($sessionID != ""){
            session_id ($sessionID);
            session_start();

        } else if (session_id() == "") {
            session_start();
        }
    }

    function startFirePhpForDebugging(){
        /*** this is included first so that FB::log() could be used ***/
        require_once("FirePHPCore/fb.php");
    }

    function initializeCfgObj(){
        /*** used to control the error display based on the CP_ENV **/
        $cpCfgObj = includeCPClass('Lib', 'Cfg', 'Cfg');

        Zend_Registry::set('cpCfgObj',$cpCfgObj);
    }

    function connectToDb(){
        $cpCfg = Zend_Registry::get('cpCfg');

        //============= CONNETION TO DB BASED ON CP_ENV (set in siteroot/conf.php) ==============//
        $dbConf = $cpCfg[CP_ENV]['db'];

        $password = isset($dbConf['password']) ? $dbConf['password'] : '';
        if ($cpCfg['cp.hasEncryptedDbPassword']) {
            $combine = getenv('MYSQL_PASS_HASH') . $dbConf['dbkey'];
            $password = hash('sha256', $combine);
        }
        $args = array(
             $dbConf['host']
            ,$dbConf['username']
            ,$password
            ,$dbConf['dbname']
            , false
        );

        $db = includeCPClass('Lib', 'MySql', 'MySql', true, array('args' => $args));

        Zend_Registry::set('db',$db);

        if(!$db->db_connect_id) {
            print "Sorry! Process terminated due to a [database error] occured. Please contact the administrator</a>";
            exit();
        }

        $dbz = Zend_Db::factory('Pdo_Mysql', array(
             'host'     => $dbConf['host']
            ,'username' => $dbConf['username']
            ,'password' => $password
            ,'dbname'   => $dbConf['dbname']
        ));
        Zend_Registry::set('dbz',$dbz);
        $dbz->query("SET NAMES 'utf8'");

        if ($cpCfg['cp.runSetNamesUtf8']) {
            $db->sql_query("SET NAMES 'utf8'");
        } else {
            $db->sql_query("SET NAMES 'latin1'");
        }

        $dbUtil = includeCPClass('Lib', 'DbUtil', 'DbUtil', true, array('args' => $db));
        $dbUtil->setReplaceForDbValue(1);
        Zend_Registry::set('dbUtil', $dbUtil);
    }

    function initializeCommonClassObjects(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = includeCPClass('Lib', 'Util', 'Util');
        Zend_Registry::set('cpUtil', $cpUtil);

        $dateUtil = includeCPClass('Lib', 'DateUtil', 'DateUtil');
        Zend_Registry::set('dateUtil', $dateUtil);

        $validate = includeCPClass('Lib', 'Validate', 'Validate');
        Zend_Registry::set('validate', $validate);

        $pager = includeCPClass('Lib', 'Pager', 'Pager');
        $pager->setSearchQueryString();
        Zend_Registry::set('pager', $pager);

        if (file_exists("{$cpCfg['cp.siteRoot']}cp_conf.php")) {
            require_once("{$cpCfg['cp.siteRoot']}cp_conf.php");
        } else {
            $incPath = $cpUtil->getFilePathInIncludePath('cp_conf/cp_conf.php');
            if ($incPath){
                require_once($incPath);
            }
        }

        includeCPClass('Lib', 'Smtp', 'SMTP', false);
        includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', false);

        $cpPaths = includeCPClass('Lib', 'Paths', 'Paths');
        Zend_Registry::set('cpPaths',$cpPaths);

        $jssArr = includeCPClass('Lib', 'JssArray', 'JssArray');
        Zend_Registry::set('jssArr', $jssArr);

        $formObj = includeCPClass('Lib', 'FormObj', 'FormObj');
        Zend_Registry::set('formObj', $formObj);
        Zend_Registry::set('frmObj', $formObj);

        $listObj = includeCPClass('Lib', 'ListObj', 'ListObj');
        Zend_Registry::set('listObj', $listObj);

        $listObj2 = includeCPClass('Lib', 'ListObj2', 'ListObj2');
        Zend_Registry::set('listObj2', $listObj2);

        $action = includeCPClass('Lib', 'Action', 'Action');
        Zend_Registry::set('action', $action);

        $fn = includeCPClass('Lib', 'Functions', 'Functions');
        Zend_Registry::set('fn',$fn);

        $cpUrl = includeCPClass('Lib', 'Url', 'Url');
        Zend_Registry::set('cpUrl', $cpUrl);

        $viewHelper = includeCPClass('Lib', 'ViewHelper');
        Zend_Registry::set('viewHelper', $viewHelper);

        $modelHelper = includeCPClass('Lib', 'ModelHelper');
        Zend_Registry::set('modelHelper', $modelHelper);

    }

    function initializeArrays(){
        $cpPaths = Zend_Registry::get('cpPaths');
        $formObj = Zend_Registry::get('formObj');

        /*** need to declare this before the array master class in initiated ***/
        Zend_Registry::set('tv', array());

        Zend_Registry::set('langKeysForJS', array());
        $arrayMaster = includeCPClass('Lib', 'ArrayMaster', 'ArrayMaster');
        Zend_Registry::set('arrayMaster', $arrayMaster);
        Zend_Registry::set('am', $arrayMaster);

        Zend_Registry::set('jssKeys', array());
        Zend_Registry::set('jsFilesArr', array());
        Zend_Registry::set('jsFilesArrLocal', array());
        Zend_Registry::set('jsFilesArrLast', array());

        Zend_Registry::set('cssFiles', array());
        Zend_Registry::set('cssFilesArr', array());
        Zend_Registry::set('cssFilesArrLocal', array());
        Zend_Registry::set('cssFilesArrFirst', array());
        Zend_Registry::set('cssFilesArrLast', array());
        Zend_Registry::set('cssPatchFilesArr', array());

        Zend_Registry::set('widgetsOnPage', array());

        Zend_Registry::set('inlineScripts', array());

        $tv = Zend_Registry::get('tv');
        $formObj->mode = $tv['action'];

        Zend_Registry::set('metaTagsArr', array());

    }

    function addSettingsDataToCfg(){
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT *
        FROM setting
        ";
        $result = $db->sql_query($SQL);

        $cfg = array();
        while ($row = $db->sql_fetchrow($result)) {
            $cfg[$row['key_text']] = $row['value'];
        }

        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cfg);

    }

    function setSiteIdInCfgForMultiSite(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($cpCfg['cp.hasMultiSites'] || $cpCfg['cp.hasMultiUniqueSites']){
            $cp_site = $fn->getReqParam('cp_site');
            // if each site is based on the the url prefix but just one domain
            //this cp_site is controlled via .htaccess
            if ($cp_site != '') {
                $cp_host = $cp_site;
            } else { //sites based on multiple domains or sub domains
                $cp_host = CP_HOST;
            }

            $aliasSql = '';
            if($dbUtil->getColumnExists('site', 'site_url_alias')){
                $aliasSql = " OR site_url_alias LIKE '%{$cp_host}%'";
            }

            // if id is set by changing the site in the dropdown ref: CP_Www_Widgets_Common_Site_Functions
            // temporarily commented due to some issues with posh, check this later..
            //$cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $cpSiteIdSession = '';

            if ($cpSiteIdSession != ''){
                $SQL = "
                SELECT *
                FROM site
                WHERE site_id = '{$cpSiteIdSession}'
                  AND published = 1
                ";
            } else {
                $SQL = "
                SELECT *
                FROM site
                WHERE (site_url LIKE '%{$cp_host}%'{$aliasSql})
                  AND published = 1
                ";
            }
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows == 0){
                exit('Site setup is incomplete (url missing in site table), please contact the administrator');
            }

            $site_id = '';
            while ($row = $db->sql_fetchrow($result)) {
                $site_url_arr = explode(',', $row['site_url']);
                if ($aliasSql != ''){
                    $site_url_alias_arr = explode(',', $row['site_url_alias']);
                    $site_url_arr = array_merge($site_url_arr, $site_url_alias_arr);
                }

                foreach($site_url_arr AS $site){
                    if (trim($site) == $cp_host){
                        $cpCfg['cp.siteRow'] = $row;
                        $cpCfg['cp.site_id'] = $row['site_id'];
                        $cpCfg['cp.theme'] = $row['theme'];
                        $cpCfg['cp.skin'] = $row['skin'];
                        $cpCfg['cp.siteDefaultLang'] = $row['default_language'];
                        $cpCfg['cp.siteKey'] = isset($row['site_key']) ? $row['site_key'] : '';
                        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
                        return;
                    }
                }
            }

            if (@$cpCfg['cp.site_id'] == ''){
                exit('Site setup is incomplete (url missing in site table), please contact the administrator');
            }
        }
    }

    function includeConfigFiles(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpCfgObj = Zend_Registry::get('cpCfgObj');
        $cpPaths = Zend_Registry::get('cpPaths');

        $arr = $cpCfg['cp.availableModGroups'];

        foreach ($arr as $modGrp){
            $modGrp = ucfirst($modGrp);
            $cpCfgObj->setConfigFiles('modGrp', $modGrp);
        }

        $cpCfgObj->setConfigFiles('theme', $cpCfg['cp.theme']);

        //-------------------------//
        $arr = $cpCfg['cp.availablePlugins'];

        //ex: common_login
        foreach ($arr as $plugin){
            list($pluginGrp, $plugin) = explode('_', $plugin);
            $pluginGrp = ucfirst($pluginGrp);
            $plugin = ucfirst($plugin);
            $pluginPath = $pluginGrp . '/' . $plugin;
            //ex: Common/Login
            $cpCfgObj->setConfigFiles('plugin', $pluginPath);
        }

        //-------------------------//
        $arr = $cpCfg['cp.availableWidgets'];

        //ex: common_login
        foreach ($arr as $widget){
            list($widgetGrp, $widget) = explode('_', $widget);
            $widgetGrp = ucfirst($widgetGrp);
            $widget = ucfirst($widget);
            $widgetPath = $widgetGrp . '/' . $widget;
            //ex: Common/Login
            $cpCfgObj->setConfigFiles('widget', $widgetPath);
        }

    }

    /**
     *
     */
    function setRedirections(){
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.hasAdminOnly']){
            header('Location: ' . $cpCfg['cp.siteUrl'] . 'admin/');
        }
    }

    /**
     *
     */
    function setHTTPSRedirections(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');

        $arr = $cpCfg['cp.redirectToHTTPSByTypeArr'];
        if( !isHttps() &&
            ( in_array($tv['secType'], $arr) ||
              in_array($tv['catType'], $arr) ||
              in_array($tv['subCatType'], $arr)
            )
           ){
            $returnUrl = "https://".CP_HOST.$cpUrl->getRequestURI();
            header ('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $returnUrl);
        }
    }

    /**
     *
     */
    function setHTTPRedirectionFromHTTPS(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');

        $arr = $cpCfg['cp.redirectToHTTPSByTypeArr'];
        if( $cpCfg['cp.redirectBackToHTTPFromHTTPS'] &&
            isHttps() &&
            $tv['spAction'] == '' &&
            ( !in_array($tv['secType'], $arr) &&
              !in_array($tv['catType'], $arr) &&
              !in_array($tv['subCatType'], $arr)
            )
           ){
            $returnUrl = "http://".CP_HOST.$cpUrl->getRequestURI();
            header ('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $returnUrl);
        }
    }

    /**
     * set the CSRF(Cross Site Request Forgery)
     */
    function setCSRFToken(){
        $fn = Zend_Registry::get('fn');
        $cpCSRFToken = $fn->getSessionParam('cpCSRFToken');
        if ($cpCSRFToken == ''){
            $fn->setCSRFToken();
        }
    }

    function setReferrerUrl(){
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpLastButOneReferrer = $fn->getSessionParam('cpLastButOneReferrer');
        $cpLastReferrer    = $fn->getSessionParam('cpReferrer');
        $cpCurrentReferrer = $cpUrl->getRequestURI();

        if($cpCurrentReferrer != $cpLastReferrer){
            $_SESSION['cpReferrer'] = $cpCurrentReferrer;
            $_SESSION['cpLastButOneReferrer'] = $cpLastReferrer;
        }
    }

    function initializeModules(){
        $arrayMaster = Zend_Registry::get('arrayMaster');

        Zend_Registry::set('modulesArr', array());
        $modules = includeCPClass('Lib', 'Modules', 'Modules');
        Zend_Registry::set('modules', $modules);
        Zend_Registry::set('modulesArrAccess', array());
        Zend_Registry::set('topRoomsArrAccess', array());
        $arrayMaster->setArrays();
    }

    function initializeWidgets(){
        Zend_Registry::set('widgetsArr', array());
        $widgets = includeCPClass('Lib', 'Widgets', 'Widgets');
        Zend_Registry::set('widgets', $widgets);
    }

    function initializePlugins(){
        Zend_Registry::set('pluginsArr', array());
        $plugins = includeCPClass('Lib', 'Plugins', 'Plugins');
        Zend_Registry::set('plugins', $plugins);
    }

    function initializeCommonClassObjects2(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        /** this will not run for front end because we don't
        * have the module name yet based on the section / category id (see www/index.php)
        */

        if ($tv['lnkRoom'] != ''){
            $modObj = getCPModuleObj($tv['lnkRoom']);
            Zend_Registry::set('currentModule', $modObj);
        } else if ($tv['module'] != ''){
            $modObj = getCPModuleObj($tv['module'], true);
            Zend_Registry::set('currentModule', $modObj);
        }

        $themeObj = getCPThemeObj($cpCfg['cp.theme'], false);
        Zend_Registry::set('currentTheme', $themeObj);

        $ln = includeCPClass('Lib', 'Lang', 'Lang');
        Zend_Registry::set('ln', $ln);

        $actionsArray = includeCPClass('Lib', 'ActionsArray', 'ActionsArray');
        Zend_Registry::set('actionsArray', $actionsArray);

        $sqlMaster = includeCPClass('Lib', 'SqlMaster', 'SqlMaster');
        Zend_Registry::set('sqlMaster', $sqlMaster);

        $searchHTML = includeCPClass('Lib', 'SearchHtml', 'SearchHtml');
        Zend_Registry::set('searchHTML', $searchHTML);

        $searchVar = includeCPClass('Lib', 'SearchVar', 'SearchVar');
        Zend_Registry::set('searchVar', $searchVar);

        $panel = includeCPClass('Lib', 'panel', 'Panel');
        Zend_Registry::set('panel', $panel);

        $media = getCPPluginObj('common_media');
        Zend_Registry::set('media', $media);
    }

    function initializeLinkObjects(){
        /************************ LINKS ************************/
        $listLinkObj = includeCPClass('Lib', 'ListLinkObj');
        Zend_Registry::set('listLinkObj', $listLinkObj);

        $displayLinkData = includeCPClass('Lib', 'DisplayLinkData');
        Zend_Registry::set('displayLinkData', $displayLinkData);

        $searchHTMLLink  = includeCPClass('Lib', 'SearchHtmlLink');
        Zend_Registry::set('searchHTMLLink', $searchHTMLLink);

        $arrayMasterLink = includeCPClass('Lib', 'ArrayMasterLink');
        Zend_Registry::set('arrayMasterLink', $arrayMasterLink);

        $linksArray = $arrayMasterLink->linksArray;
        Zend_Registry::set('linksArray', $linksArray);
        Zend_Registry::set('linkedIds', '');
    }

    function initializeCommonClassObjects3(){
        $actionsArray = Zend_Registry::get('actionsArray');

        $actionButArray = $actionsArray->actionsArr;
        Zend_Registry::set('actionButArray', $actionButArray);

        Zend_Registry::set('dataArray', array());
    }

    function setNavigationArraysWww(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');

        /************************************************************/
        $mainNav = getCPWidgetObj('core_mainNav');
        Zend_Registry::set('mainNav', $mainNav);

        // always set the available section / category / sub-category into arrays expect spActions
        $roomsArray  = ($tv['spAction'] == '') ? $mainNav->model->getDataArray() : array();
        Zend_Registry::set('roomsArray', $roomsArray);
        $tv['room'] = $fn->getVariable($tv['room'], $fn->getFirstRoom());

        if ($tv['room'] == '' && $tv['spAction'] == ''){
            exit('no sections configured for this site');
        }

        $tv['secType'] = ($tv['spAction'] == '')? $fn->getIssetParam($roomsArray[$tv['room']], 'section_type') : '';
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        /************************************************************/
        $subNav  = getCPWidgetObj('core_subNav');
        Zend_Registry::set('subNav', $subNav);
        $subRoomsArray = ($tv['spAction'] == '') ? $subNav->model->getDataArray() : array();
        Zend_Registry::set('subRoomsArray', $subRoomsArray);

        if ($tv['spAction'] == '' && $tv['subRoom'] != '' && !$fn->getIssetParam($subRoomsArray, $tv['subRoom'])){
            // if the record is member only send to login page
            $catRecord = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);
            if (is_array($catRecord) && @$catRecord['member_only'] == 1){
                $url = $cpUrl->getUrlBySecType('Login');
                $cpUtil->redirect($url);
            } else {
                exit('Sorry.. no pages associated for this category. Please contact the administrator');
            }
        }

        $tv['catType'] = ($tv['spAction'] == '' && $tv['subRoom'] != '') ?  $fn->getIssetParam($subRoomsArray[$tv['subRoom']], 'category_type') :  '';

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        /************************************************************/
        $subCat  = getCPWidgetObj('core_subCat');
        Zend_Registry::set('subCat', $subCat);

        $subCatArray   = ($tv['spAction'] == '') ? $subCat->model->getDataArray() : array();
        Zend_Registry::set('subCatArray', $subCatArray);

        $tv['subCatType'] = ($tv['spAction'] == '' && $tv['subCat']  != '') ? $fn->getIssetParam($subCatArray[$tv['subCat']], 'sub_category_type'): '';
        /************************************************************/

        $tv['secTitle']   = ($tv['spAction'] == '') ? $fn->getIssetParam($roomsArray[$tv['room']], 'title') : '';
        $tv['catTitle']   = ($tv['spAction'] == '' && $tv['subRoom'] != '')  ? $fn->getIssetParam($subRoomsArray[$tv['subRoom']], 'title') : '';
        $tv['subCatTitle']= ($tv['spAction'] == '' && $tv['subCat'] != '')   ? $fn->getIssetParam($subCatArray[$tv['subCat']], 'title')    : '';
        $tv['pageTitle'] = $fn->getPageTitle($tv);

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }


    function setCurrentModuleWww(){
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $arrayMaster->setRoomIDInGlobal();
        $arrayMaster->setModuleNameInGlobal();

        /** the below declaration should not be moved up since the $tv['module'] is set in
         *   $arrayMaster->setModuleNameInGlobal()
        **/
        $tv = Zend_Registry::get('tv');
        if ($tv['module'] != ''){
            $modObj = getCPModuleObj($tv['module'], true);
            Zend_Registry::set('currentModule', $modObj);
        }
    }

    /**
     *
     */
    function setArraysByPageWww(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pageType = $tv['currentViewRecType'];

        $pageArr = $fn->getIssetParam($cpCfg['cp.assetsByPageTypeArr'], $pageType);
        $pageArrView = $fn->getIssetParam($cpCfg['cp.assetsByPageTypeArr'], $pageType . '-' . $tv['action']);

        if($pageArr){
            $jssKeys = $fn->getIssetParam($pageArr, 'jssKeys');

            if (is_array($jssKeys)){
                CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
            }
        }

        if($pageArrView){
            $jssKeys = $fn->getIssetParam($pageArrView, 'jssKeys');

            if (is_array($jssKeys)){
                CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
            }
        }
    }

    function setHTTPProtocol(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpCfg['cp.protocol'] = isHttps() ? 'https' : 'http';
        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
    }

    function loadTheLastConfigFile(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cfgLast = require_once($cpCfg['cp.localPath'] . 'lib/config-last.php');
        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cfgLast);
    }


    function initializeMediaArray(){
        Zend_Registry::set('mediaArray', array());
        $mediaArrayObj = includeCPClass('Lib', 'MediaArray');
        Zend_Registry::set('mediaArrayObj', $mediaArrayObj);
        Zend_Registry::set('mediaArray', $mediaArrayObj->mediaArray);
    }

    function loadTheOverrideFiles(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpCfgObj = Zend_Registry::get('cpCfgObj');

        $cpCfgObj->loadAnyFile('themes', $cpCfg['cp.theme'], 'override.php');
        $cpCfgObj->loadAnyFile('lib', '', 'override.php');
    }

    function setCurrentTheme(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $themeObj = getCPThemeObj($cpCfg['cp.theme']);
        Zend_Registry::set('currentTheme', $themeObj);
    }

    function checkAccess(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        if($tv['module'] != '' && !in_array($tv['module'], $cpCfg['cp.availableModules'])){ //module has access
            print "Invalid Module Access";
            exit();
        }
    }

    function outputTheTemplateWww(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        //============= Request Vars used in index.php ==================//
        $plugin = getReqParam('plugin');
        $widget = getReqParam('widget');
        $theme = getReqParam('_theme');

        if ($plugin != ''){
            require_once("{$cpCfg['cp.masterPath']}plugin.php");

        } else if ($widget != ''){
            require_once("{$cpCfg['cp.masterPath']}widget.php");

        } else if ($theme != ''){
            require_once("{$cpCfg['cp.masterPath']}theme.php");

        } else if ($tv['spAction'] != ""){
            if ($tv['spAction'] == "link") {
                $clsInst = getCPModuleObj($tv['srcRoom']);
                $clsInst = $clsInst->fns;
                $funcName = "setLinksArray";
                $clsInst->$funcName($arrayMasterLink);
                $linksArray = $arrayMasterLink->linksArray;
                Zend_Registry::set('linksArray', $linksArray);

                $linkedIds = $fn->getLinkedIDs($tv['srcRoom'], $tv['lnkRoom']);
                Zend_Registry::set('linkedIds', $linkedIds);

                include("{$cpCfg['cp.masterPath']}tpl_main.php");

            } else {
                include("{$cpCfg['cp.masterPath']}spAction.php");
            }

        } else {
            /**
             * compare the incoming url against the system generated one
             * and 301 redirect if necessary
             */
            $cpUrl = Zend_Registry::get('cpUrl');
            $cpUrl->compareTheUrlAndRedirect();

            include("{$cpCfg['cp.masterPath']}tpl_main.php");
        }
    }

    function setNavigationArraysAdmin(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $tv['secType']    = '';
        $tv['catType']    = '';
        $tv['subCatType'] = '';

        if($tv['category_id'] != ''){
            $catRecord = $fn->getRecordRowByID('category', 'category_id', $tv['category_id']);
            $tv['catType'] = isset($catRecord['category_type']) ? $catRecord['category_type'] : '';
        }

        if($tv['sub_category_id'] != ''){
            $subCatRecord = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['sub_category_id']);
            $tv['subCatType'] = isset($subCatRecord['sub_category_type']) ? $subCatRecord['sub_category_type'] : '';
        }

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    function outputTheTemplateAdmin(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $cpCfg = Zend_Registry::get('cpCfg');

        //============= Request Vars used in index.php ==================//
        $plugin = getReqParam('plugin');
        $widget = getReqParam('widget');
        $theme = getReqParam('_theme', '', true);

        if ($plugin != ''){
            require_once("{$cpCfg['cp.masterPath']}plugin.php");

        } else if ($widget != ''){
            require_once("{$cpCfg['cp.masterPath']}widget.php");

        } else if ($theme != ''){
            require_once("{$cpCfg['cp.masterPath']}theme.php");

        } else if ($tv['spAction'] != ""){
            if (isLoggedInAdmin() || (!isLoggedInAdmin() && in_array($tv['spAction'], $tv['protSiteSpActionExceptions']))){
                if ($tv['spAction'] == "link") {
                    //$clsInst  = includeCPClass('ModuleFns', $tv['srcRoom']);
                    $clsInst = getCPModuleObj($tv['srcRoom']);
                    $clsInst = $clsInst->fns;
                    $funcName = "setLinksArray";
                    $clsInst->$funcName($arrayMasterLink);
                    $linksArray = $arrayMasterLink->linksArray;
                    Zend_Registry::set('linksArray', $linksArray);

                    $linkedIds = $fn->getLinkedIDs($tv['srcRoom'], $tv['lnkRoom']);
                    Zend_Registry::set('linkedIds', $linkedIds);
                    include("{$cpCfg['cp.masterPath']}tpl_main.php");

                } elseif ($tv['spAction'] == "moduleEditor") {
                    include("{$cpCfg['cp.masterPath']}tpl_main.php"); 
                } else {
                    include("{$cpCfg['cp.masterPath']}spAction.php");
                }
            } else {
                include("{$cpCfg['cp.masterPath']}tpl_main.php");
            }

        } else {
            if ($tv['topRm']  == ''){
                $tv['topRm'] = $arrayMaster->getFirstTopRoom();
            }

            if ($tv['module'] == '' && $tv['topRm'] != ''){
                $arr = $cpCfg['cp.topRooms'][$tv['topRm']];
                $tv['module'] = $arr['default'];
            }

            CP_Common_Lib_Registry::arrayMerge('tv', $tv);

            if (isLoggedInAdmin()){
                redirectToFirstRoom();
            }

            include("{$cpCfg['cp.masterPath']}tpl_main.php");
        }
    }
}
