<?
class CP_Common_Lib_Template
{
    /**
     *
     * @return <type>
     */
    function getHTMLHeaderTags(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $jssArr = Zend_Registry::get('jssArr');
        $fn = Zend_Registry::get('fn');

        $jssArr->setJSSFilesText();

        if(CP_SCOPE == 'admin'){
            $tv = Zend_Registry::get('tv');
            $modulesArr = Zend_Registry::get('modulesArr');

            //$topRoom = $cpCfg['cp.topRooms'][$tv['topRm']]['title'];

            $title = $cpCfg['cp.siteTitle'];
            if ($tv['module'] != '') {
                $title = $modulesArr[$tv['module']]['title'] . ' : ' .
                         //$topRoom . ' : ' .
                         $cpCfg['cp.siteTitle'];
            }


            $text = "
            <meta charset='utf-8' />
            {$this->getMetaRobotsText()}
            {$this->getFavIcon()}
            <title>{$title}</title>
            <meta name='viewport' content='width=device-width, initial-scale=1.0, shrink-to-fit=no'>
            {$jssArr->allCssText}
            {$jssArr->allJsTextTop}
            ";

        } else {
            $this->getMetaTitleDescKeyword();
            $tv = Zend_Registry::get('tv'); //need to get this after running the above

            $fbTags = $this->getFBTags();
            $text = "
            <meta charset='utf-8' />
            {$this->getMetaRobotsText()}
            {$this->getFavIcon()}
            <title>{$tv['metaTitle']}</title>
            <meta name='description' content='{$tv['metaDescription']}' />
            <meta name='keywords' content='{$tv['metaKeyword']}' />
            <meta name='viewport' content='width=device-width, initial-scale=1.0, shrink-to-fit=no'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            {$fbTags}
            {$this->getCustomMetaTags()}
            {$this->getSiteMetaTags()}
            {$jssArr->allCssText}
            {$jssArr->allJsTextTop}
            {$fn->getAnalyticsCode()} 
            {$fn->getGoogleTagManagerCode()}
            {$fn->getHotJarTrackingCode()}
            ";
        }
        return $text;
    }

    /**
     *
     * @return <type>
     */
    function getMetaRobotsText(){

        if(CP_ENV != 'production'){
            return "<meta name='robots' content='noindex, nofollow' />";
        }
    }

    function getSiteMetaTags(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
            $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);
            $text = $fn->getIssetParam($siteRec, 'additional_meta_tags');
        }

        return $text;
    }

    function getCustomMetaTags(){
        $metaTagsArr = Zend_Registry::get('metaTagsArr');

        $text = '';
        foreach ($metaTagsArr as $name => $tagArr) {
            $content = $tagArr['content'];
            $text .= "
            <meta name='{$name}' content='{$content}'>
            ";
        }

        return $text;
    }

    function getMetaTitleDescKeyword(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $hasMultiLang = $cpCfg['cp.hasMultiLangForMetaData'];

        if($cpCfg['cp.hasMultiUniqueSites']) {
            $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite', false)->model;
            $row = $wMultiUniqueSite->getSiteRecord();

            $title = $hasMultiLang ? $ln->gfv($row, 'meta_title')       : $fn->getIssetParam($row, 'meta_title');
            $desc  = $hasMultiLang ? $ln->gfv($row, 'meta_description') : $fn->getIssetParam($row, 'meta_description');
            $keyw  = $hasMultiLang ? $ln->gfv($row, 'meta_keyword')     : $fn->getIssetParam($row, 'meta_keyword');

            $siteMetaTitle       = ($title != "" ) ? $title : '';
            $siteMetaDescription = ($desc  != '' ) ? $desc  : '';
            $siteMetaKeyword     = ($keyw  != '' ) ? $keyw  : '';

            if ($siteMetaTitle != ''){
                $cpCfg['cp.siteTitle'] = $siteMetaTitle;
            } else if ($row['title'] != '') {
                //overwrite siteTitle with title from site table
                $cpCfg['cp.siteTitle'] = $row['title'];
            }

            if ($siteMetaDescription != ''){
                $cpCfg['cp.metaDescription'] = $siteMetaDescription;
            }

            if ($siteMetaKeyword != ''){
                $cpCfg['cp.metaKeyword'] = $siteMetaKeyword;
            }
        }
        $metaTitle             = "";
        $metaDescription       = "";
        $metaKeyword           = "";

        $metaTitleSec          = "";
        $metaDescriptionSec    = "";
        $metaKeywordSec        = "";

        $metaTitleCat          = "";
        $metaDescriptionCat    = "";
        $metaKeywordCat        = "";

        $metaTitleSubCat       = "";
        $metaDescriptionSubCat = "";
        $metaKeywordSubCat     = "";

        $metaTitleRec          = "";
        $metaDescriptionRec    = "";
        $metaKeywordRec        = "";

        if ($tv['room'] != "" && is_numeric($tv['room'])){
            $sectionRow = $fn->getRecordRowByID("section", "section_id", $tv['room']);

            $title = $hasMultiLang ? $ln->gfv($sectionRow, 'meta_title')       : $sectionRow['meta_title'];
            $desc  = $hasMultiLang ? $ln->gfv($sectionRow, 'meta_description') : $sectionRow['meta_description'];
            $keyw  = $hasMultiLang ? $ln->gfv($sectionRow, 'meta_keyword')     : $sectionRow['meta_keyword'];

            $metaTitleSec       = ($title != "" ) ? $title : $sectionRow['title'] . " :: {$cpCfg['cp.siteTitle']}";
            $metaDescriptionSec = ($desc  != '' ) ? $desc  : '';
            $metaKeywordSec     = ($keyw  != '' ) ? $keyw  : '';
        }

        if ($tv['subRoom'] != "" && is_numeric($tv['subRoom'])){
            $categoryRow = $fn->getRecordRowByID("category", "category_id", $tv['subRoom']);

            $title = $hasMultiLang ? $ln->gfv($categoryRow, 'meta_title')       : $categoryRow['meta_title'];
            $desc  = $hasMultiLang ? $ln->gfv($categoryRow, 'meta_description') : $categoryRow['meta_description'];
            $keyw  = $hasMultiLang ? $ln->gfv($categoryRow, 'meta_keyword')     : $categoryRow['meta_keyword'];

            $metaTitleCat       = ($title != "" ) ? $title : $sectionRow['title'] . " | " . $categoryRow['title'] . " :: {$cpCfg['cp.siteTitle']}";
            $metaDescriptionCat = ($desc  != '' ) ? $desc  : '';
            $metaKeywordCat     = ($keyw  != '' ) ? $keyw  : '';
        }

        if ($tv['subCat'] != "" && is_numeric($tv['subCat'])){
            $subCatRow = $fn->getRecordRowByID("sub_category", "sub_category_id", $tv['subCat']);

            $title = $hasMultiLang ? $ln->gfv($subCatRow, 'meta_title')       : $subCatRow['meta_title'];
            $desc  = $hasMultiLang ? $ln->gfv($subCatRow, 'meta_description') : $subCatRow['meta_description'];
            $keyw  = $hasMultiLang ? $ln->gfv($subCatRow, 'meta_keyword')     : $subCatRow['meta_keyword'];

            $metaTitleSubCat       = ($title != "" ) ? $title : $sectionRow['title'] . " | " . $categoryRow['title'] . " | " . $subCatRow['title'] . " :: {$cpCfg['cp.siteTitle']}";
            $metaDescriptionSubCat = ($desc  != '' ) ? $desc  : '';
            $metaKeywordSubCat     = ($keyw  != '' ) ? $keyw  : '';
        }

        if ($tv['record_id'] != "" && is_numeric($tv['record_id'])){
            $modulesArr = Zend_Registry::get('modulesArr');

            $keyField  = $modulesArr[$tv['module']]["keyField"];
            $tableName = $modulesArr[$tv['module']]["tableName"];
            $titleField = $modulesArr[$tv['module']]["titleField"];

            if (is_numeric ($tv['record_id']) ){
                $rec = $fn->getRecordRowByID($tableName, $keyField, $tv['record_id']);

                if (is_array($rec)){
                    $metaTitleRec       = $hasMultiLang ? $ln->gfv($rec, 'meta_title')       : $fn->getIssetParam($rec, 'meta_title');
                    $metaDescriptionRec = $hasMultiLang ? $ln->gfv($rec, 'meta_description') : $fn->getIssetParam($rec, 'meta_description');
                    $metaKeywordRec     = $hasMultiLang ? $ln->gfv($rec, 'meta_keyword')     : $fn->getIssetParam($rec, 'meta_keyword');
                }
            }
        }

        //----------------------------------------------------------------//
        if ( $tv['record_id'] != "" && $metaTitleRec != ""){
            $metaTitle = $metaTitleRec;
        
        } else if ($tv['subCat'] != "" && $metaTitleSubCat != ""){
            $metaTitle = $metaTitleSubCat;

        } else if ($tv['subRoom'] != "" && $metaTitleCat != ""){
            $metaTitle = $metaTitleCat;

        } else if ($tv['room'] != "" && is_numeric($tv['room']) && $metaTitleSec != ""){
            $metaTitle = $metaTitleSec;


        } else {
            $metaTitle = $cpCfg['cp.siteTitle'];
        }


        //----------------------------------------------------------------//
        if ($tv['record_id'] != "" && $metaDescriptionRec != ""){
            $metaDescription = $metaDescriptionRec;
        
        } else if ($tv['subCat'] != "" && $metaDescriptionSubCat != ""){
            $metaDescription = strip_tags($metaDescriptionSubCat);

        } else if ($tv['subRoom'] != "" && $metaDescriptionCat != ""){
            $metaDescription = strip_tags($metaDescriptionCat);

        } else if ($tv['room'] != "" && is_numeric($tv['room']) && $metaDescriptionSec != ""){
            $metaDescription = strip_tags($metaDescriptionSec);

        } else {
            $metaDescription = strip_tags($cpCfg['cp.metaDescription']);
        }

        //----------------------------------------------------------------//
        if ( $tv['record_id'] != "" && $metaKeywordRec != ""){
            $metaKeyword = $metaKeywordRec;

        } else if ($tv['subCat'] != "" && $metaKeywordSubCat != ""){
            $metaKeyword = $metaKeywordSubCat;

        } else if ($tv['subRoom'] != "" && $metaKeywordCat != ""){
            $metaKeyword = $metaKeywordCat;

        } else if ($tv['room'] != "" && is_numeric($tv['room']) && $metaKeywordSec != ""){
            $metaKeyword = $metaKeywordSec;

        } else {
            $metaKeyword = $cpCfg['cp.metaKeyword'];
        }

        $tv['metaTitle']       = $metaTitle;
        $tv['metaDescription'] = $metaDescription;
        $tv['metaKeyword']     = $metaKeyword;

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    function getFBTags(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $mediaArray = Zend_Registry::get('mediaArray');
        $media= Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';

        if ($cpCfg['cp.showFBShareTagsInDetail']) {
            $modulesArr = Zend_Registry::get('modulesArr');
            $module = $tv['module'];

            $keyField  = $modulesArr[$module]['keyField'];
            $tableName = $modulesArr[$module]['tableName'];
            $titleField = $modulesArr[$module]['titleField'];

            $recordType = 'picture';
            $mediaArr1 = &$mediaArray[$module][$recordType];

            if (is_numeric ($tv['record_id']) ){
                $row = $fn->getRecordRowByID($tableName, $keyField, $tv['record_id']);

                if (is_array($row)){
                    $attArr = $media->model
                              ->getFirstMediaRecord($tv['module'], 'picture', $tv['record_id']);
                    $attLink = '';

                    $fbMetaUrl = $cpUrl->getCurrentURL();
                    $fbMetaTitle = $row['title'];
                    $fbMetaDesc = strip_tags($row['description']);
                    $fbMetaImage = '';

                    if (count($attArr) > 0){
                        $fbMetaImage = $cpUrl->getURLPrefix() . $mediaArr1['thumbFolderAlias']  . $attArr['file_name'];
                    }

                    $text .= "
                    <meta property='og:url' content='{$fbMetaUrl}'/>
                    <meta property='og:title' content='{$fbMetaTitle}'/>
                    <meta property='og:description' content='{$fbMetaDesc}'/>
                    <meta property='og:image' content='{$fbMetaImage}'/>
                    ";
                }
            }
        }

        return $text;
    }

    //==================================================================//
    function getFavIcon() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $favIconText = ($cpCfg['cp.hasShortcutIcon'] == 1) ?
                        "<link rel='shortcut icon' href='{$cpCfg['cp.localImagesPathAlias']}favicon.ico'
                        type='image/x-icon' />\n"
                        : '';

        return $favIconText;
    }

    //==================================================================//
    function getHeader() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "<!DOCTYPE html>
        <!--[if lt IE 7]><html dir='ltr' lang='en-US' class='ie6'><![endif]-->
        <!--[if IE 7]><html dir='ltr' lang='en-US' class='ie7'><![endif]-->
        <!--[if IE 8]><html dir='ltr' lang='en-US' class='ie8'><![endif]-->
        <!--[if gt IE 8]><!--><html dir='ltr' lang='en-US'><!--<![endif]-->
        <html>
            <head>
                {$this->getHTMLHeaderTags()}
            </head>
        ";

        return $text;
    }

    //==================================================================//
    function getBody($content) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');

        $pageCSSClassTop = $viewHelper->getPageCSSClassTop();

        $module      = "m-{$tv['module']}";
        $moduleGroup = "mg-{$tv['moduleGroup']}";
        $action      = "v-{$tv['action']}";

        $bodyClass  = $fn->getIssetParam($tv, 'bodyClass');
        $bodyClass .= " {$module} {$moduleGroup} {$pageCSSClassTop} {$action}";
        $bodyClass .= " cpln-{$tv['lang']}";

        if ($tv['cp_year'] != '') {
            $bodyClass .= " cpyr-{$tv['cp_year']}";
        }

        if(($cpCfg['cp.hasMultiSites'] || $cpCfg['cp.hasMultiUniqueSites']) && CP_SCOPE == 'www'){
            $bodyClass .= " cpsite-{$cpCfg['cp.siteKey']}";
        }

        if ($cpCfg['cp.fullWidthTemplte']) {
            $bodyClass .= " cpFullWidth";
        }

        if ($tv['bodyExtraClass'] != ''){
            $bodyClass .= " {$tv['bodyExtraClass']}";
        }

        if (CP_SCOPE == 'www' && isLoggedInWww()){
            $userType = $fn->getSessionParam('cpLoginTypeWWW');
            $bodyClass .= " loggedin lt-{$userType}";
        }

        $bodyClass = " class='{$bodyClass}'";

        $angularAppAttr = '';
        if ($cpCfg['cp.hasAngularApp']) {
            $angularAppAttr = "ng-app";
        }

        $getGoogleTagManagerBodyCode = '';
        if (CP_SCOPE == 'www' && CP_ENV == 'production') {
            $getGoogleTagManagerBodyCode = $fn->getGoogleTagManagerBodyCode();
        }

        $text  = "
        <body{$this->getBodyClass()}>
            {$getGoogleTagManagerBodyCode} 
            {$content}
            {$this->getHiddenPageValues()}
        ";

        return $text;
    }

    //==================================================================//
    function getBodyClass() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');

        $pageCSSClassTop = $viewHelper->getPageCSSClassTop();

        $module      = "m-{$tv['module']}";
        $moduleGroup = "mg-{$tv['moduleGroup']}";
        $action      = "v-{$tv['action']}";

        $bodyClass  = $fn->getIssetParam($tv, 'bodyClass');
        $bodyClass .= " {$module} {$moduleGroup} {$pageCSSClassTop} {$action}";
        $bodyClass .= " cpln-{$tv['lang']}";

        if ($tv['cp_year'] != '') {
            $bodyClass .= " cpyr-{$tv['cp_year']}";
        }

        if ($tv['topRm'] != '') {
            $bodyClass .= " tpRm-{$tv['topRm']}";
        }

        if(($cpCfg['cp.hasMultiSites'] || $cpCfg['cp.hasMultiUniqueSites']) && CP_SCOPE == 'www'){
            $bodyClass .= " cpsite-{$cpCfg['cp.siteKey']}";
        }

        if ($cpCfg['cp.fullWidthTemplte']) {
            $bodyClass .= " cpFullWidth";
        }

        if ($tv['bodyExtraClass'] != ''){
            $bodyClass .= " {$tv['bodyExtraClass']}";
        }

        if (CP_SCOPE == 'www' && isLoggedInWww()){
            $userType = $fn->getSessionParam('cpLoginTypeWWW');
            $bodyClass .= " loggedin lt-{$userType}";
        }

        $bodyClass = " class='{$bodyClass}'";

        $angularAppAttr = '';
        if ($cpCfg['cp.hasAngularApp']) {
            $angularAppAttr = "ng-app";
        }

        $text  = "{$bodyClass} {$angularAppAttr}";

        return $text;
    }

    /**
     *
     */
    function getFooter($exp = array()) {

        $text  = "
        {$this->getFooterScripts($exp)}
        </body>
        </html>
        ";

        return $text;
    }

    /**
     *
     */
    function getFooterScripts($exp = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $widgetsOnPage = Zend_Registry::get('widgetsOnPage');
        $jssArr = Zend_Registry::get('jssArr');
        $inlineScripts = Zend_Registry::get('inlineScripts');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module = $tv['module'];

        $jsScripts = $fn->getIssetParam($exp, 'jsScripts', '');

        $initWidgets = '';
        foreach($widgetsOnPage as $widget){
            $jsObjName = 'cpw.' . str_replace('_', '.', $widget);
            $jsInitName = $jsObjName . '.init';

            $initWidgets .= "
            Util.createCPObject('{$jsObjName}');
            if({$jsInitName}){
                {$jsInitName}();
            }
            ";
        }

        $inlineScriptsText = '';
        foreach($inlineScripts as $script){
            $inlineScriptsText .= "
            {$script}
            ";
        }

        //*** theme js init ***/
        $themeName = ($cpCfg['cp.theme'] == 'Default') ? 'Def' : $cpCfg['cp.theme'];

        $jsObjNameT = "cpt.". lcfirst($themeName);
        $jsInitNameT = $jsObjNameT . '.init';
        $initTheme = "
            Util.createCPObject('{$jsObjNameT}');
            if({$jsInitNameT}){
                {$jsInitNameT}();
            }
        ";

        $initModule = '';
        if ($tv['module'] != ''){
            $jsObjName = 'cpm.' . str_replace('_', '.', $tv['module']);
            $jsInitName = $jsObjName . '.init';

            $initModule = "
            Util.createCPObject('{$jsObjName}');
            if({$jsInitName}){
                {$jsInitName}();
            }
            ";
        }

        $wwwOnly = '';
        $adminOnlyJS = '';
        if(CP_SCOPE == 'www'){
            $wwwOnly = "
            {$fn->setLogoForMultiUniqueSite()}
            ";
        } else {

            if (($tv['action'] == 'list' && $cpCfg['cp.fixMainPanelHeightInList']) ||
                ($tv['action'] != 'list' && $cpCfg['cp.fixMainPanelHeightInDetail'])){
                $adminOnlyJS = "
                LoadReady.makeScrollableTable();
                ";
            }
        }

        $loadReadyText = '';
        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $loadReadyText = "
            LoadReady.showHideRightPanels();
            LoadReady.setupLinks();
            ";
        }

        $text  = "
        {$fn->getLangKeys()}
        {$fn->getCfgKeys()}
        {$jssArr->allJsText}
        <script>
        $(function(){
            {$adminOnlyJS}
            {$initModule}
            {$initWidgets}
            {$initTheme}
            {$loadReadyText}
            {$inlineScriptsText}
        });
        </script>
        {$wwwOnly}
        {$jsScripts}
        <script>var cp={}; cp.urlParams =" . json_encode($_GET) . "</script>
        ";

        return $text;
    }


    /**
     *
     */
    function getHiddenPageValues() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module = $modulesArr[$tv['module']];
        $cp_path_alias = CP_PATH_ALIAS;
        $cpCSRFToken = $fn->getSessionParam('cpCSRFToken');

        $scope = CP_SCOPE;
        $adminVars = '';
        if(CP_SCOPE == 'admin'){
            $currentUrlNoLang = $cpUrl->getCurrentURL(array('langAdminInt'));
            $adminVars = "
            <input type='hidden' id='cpTopRm' value='{$tv['topRm']}'>
            <input type='hidden' id='currentUrlNoLang' value='{$currentUrlNoLang}'>
            <input type='hidden' id='hasAutoSave' value='{$module['hasAutoSave']}'>
            ";
        }

        $text = "
        <input type='hidden' id='cpAction' value='{$tv['action']}' />
        <input type='hidden' id='cpRoom' value='{$tv['module']}' />
        <input type='hidden' id='cpCSRFToken' value='{$cpCSRFToken}' />
        <input type='hidden' id='libraryPathAlias' value='{$cpCfg['cp_library_path_alias']}' />
        <input type='hidden' id='jssPath' value='{$cpCfg['cp.jssPath']}' />
        <input type='hidden' id='masterRootPathAlias' value='{$cp_path_alias}' />
        <input type='hidden' id='masterPathAlias' value='{$cpCfg['cp.masterPathAlias']}' />
        <input type='hidden' id='siteRootAlias' value='{$cpCfg['cp.siteRootAlias']}' />
        <input type='hidden' id='scopeRootAlias' value='{$cpCfg['cp.scopeRootAlias']}' />
        <input type='hidden' id='cpScope' value='{$scope}' />
        <input type='hidden' id='cpLang' value='{$tv['lang']}' />
        <input type='hidden' id='cpCurrentViewRecType' value='{$tv['currentViewRecType']}' />
        <input type='hidden' id='cpCurrentUriWithNoQstr' value='{$cpUrl->getUriWithNoQstr()}' />
        {$adminVars}
        ";

        return $text;
    }
}
