<?
class CP_Common_Lib_Url
{
    var $urlArray = array();

    /**
     * prepares a string to be included in an SEO friendly URL
     */
    function getUrlText($text) {
        $text = trim(mb_strtolower($text,'UTF-8'));

        $code_entities_match = array( '&quot;' ,'!' ,'@' ,'#' ,'$' ,'%' ,'^' ,'&' ,'*' ,'(' ,')' ,'+' ,'{' ,'}' ,'|' ,':' ,'"' ,'<' ,'>' ,'?' ,'[' ,']' ,'' ,';' ,"'" ,',' ,'.' ,'_' ,'/' ,'*' ,'+' ,'~' ,'`' ,'=' ,' ' ,'---' ,'--','--');
        $code_entities_replace = array('' ,'-' ,'-' ,'' ,'' ,'' ,'-' ,'-' ,'' ,'' ,'' ,'' ,'' ,'' ,'' ,'-' ,'' ,'' ,'' ,'' ,'' ,'' ,'' ,'' ,'' ,'-' ,'' ,'-' ,'-' ,'' ,'' ,'' ,'' ,'' ,'-' ,'-' ,'-','-');
        $text = str_replace($code_entities_match, $code_entities_replace, $text);
        return $text;
    }

    /**
     *
     */
    function make_seo_url($valueArray, $append = 0, $prependUrl = ""){
        $cpCfg = Zend_Registry::get('cpCfg');

        $countryCodeReq     = isset($valueArray['countryCodeReq'])     ?  $valueArray['countryCodeReq']     : "";
        $cp_site            = isset($valueArray['cp_site'])            ?  $valueArray['cp_site']            : "";
        $cp_year            = isset($valueArray['cp_year'])            ?  $valueArray['cp_year']            : "";
        $ln                 = isset($valueArray['lang'])               ?  $valueArray['lang']               : "";
        $siteType           = isset($valueArray['siteType'])           ?  $valueArray['siteType']           : "";
        $sitePfxId          = isset($valueArray['sitePfxId'])          ?  $valueArray['sitePfxId']          : "";


        $section_title      = isset($valueArray['section_title'])      ?  $valueArray['section_title']      : "";
        $section_id         = isset($valueArray['section_id'])         ?  $valueArray['section_id']         : "";
        $category_title     = isset($valueArray['category_title'])     ?  $valueArray['category_title']     : "";
        $category_id        = isset($valueArray['category_id'])        ?  $valueArray['category_id']        : "";
        $sub_category_title = isset($valueArray['sub_category_title']) ?  $valueArray['sub_category_title'] : "";
        $sub_category_id    = isset($valueArray['sub_category_id'])    ?  $valueArray['sub_category_id']    : "";
        $record_title       = isset($valueArray['record_title'])       ?  $valueArray['record_title']       : "";
        $record_id          = isset($valueArray['record_id'])          ?  $valueArray['record_id']          : "";
        $tab_name           = isset($valueArray['tab_name'])           ?  $valueArray['tab_name']           : "";
        $page_name          = isset($valueArray['page_name'])          ?  $valueArray['page_name']          : "";
        $page_no            = isset($valueArray['page_no'])            ?  $valueArray['page_no']            : "";
        $action_name        = isset($valueArray['action_name'])        ?  $valueArray['action_name']        : "";
        $special_title      = isset($valueArray['special_title'])      ?  $valueArray['special_title']      : "";
        $queryStr           = isset($valueArray['queryStr'])           ?  $valueArray['queryStr']           : "";
        $anchor             = isset($valueArray['anchor'])             ?  $valueArray['anchor']             : "";

        if ($append == 1){
            $url = $_SERVER['REQUEST_URI'];
        } else if ($prependUrl != ""){
            $url = $prependUrl;
        } else {
            $url = $cpCfg['cp.siteRootAlias'];
        }

        if ($countryCodeReq != ''){
            $clean_site = $this->getUrlText($countryCodeReq);
            $url .= "{$countryCodeReq}/";
        }

        if ($cp_site != ''){
            $clean_site = $this->getUrlText($cp_site);
            $url .= "{$clean_site}/";
        }

        if ($cp_year != ''){
            $url .= "{$cp_year}/";
        }

        if ($siteType != ""){
            $url .= "{$siteType}/"; // eg: edukite /kite/ or /controller/
        }

        if ($sitePfxId != ""){
            $url .= "{$sitePfxId}/"; // eg: edukite /kite/[student_id]/
        }

        if ($ln != ""){
            $url .= "{$ln}/";
        }

        if ($section_title != ""){
            $clean_section_title = $this->getUrlText($section_title);
            $url .= "{$clean_section_title}/";
        }

        if ($tab_name != ""){
            $clean_tab_title = $this->getUrlText($tab_name);
            $url .= "{$clean_tab_title}/";
        }

        if ($category_title != "" && $sub_category_title != ""){
            $clean_category_title = $this->getUrlText($category_title);
            $clean_sub_category_title = $this->getUrlText($sub_category_title);
            $url .= "{$clean_category_title}/{$clean_sub_category_title}/{$category_id}/{$sub_category_id}/";

        } else {
            if ($category_title != ""){
                $clean_category_title = $this->getUrlText($category_title);
                $url .= "{$clean_category_title}/{$category_id}/";
            }

            if ($sub_category_title != ""){
                $clean_sub_category_title = $this->getUrlText($sub_category_title);
                $url .= "{$clean_sub_category_title}/{$sub_category_id}/";
            }
        }

        if ($special_title != ""){
            $url .= $this->getUrlText($special_title) . "/";
        }

        if ($action_name != ""){
            $url .= $this->getUrlText($action_name) . "/";
        }

        if ($record_title != ""){
            $clean_record_title = $this->getUrlText($record_title);

            $clean_record_title = ($clean_record_title != "") ? $clean_record_title : "detail";
            $url .= "{$record_id}/{$clean_record_title}.html";
        } else if ($record_id > 0){
            $url .= "{$record_id}/";
        }

        if ($page_name != ""){
            $clean_page_title = $this->getUrlText($page_name);
            $url .= "{$clean_page_title}.html";
        }

        if ($page_no != ""){
            $clean_section_title = $this->getUrlText($section_title);
            $url .= "Page-{$page_no}/";
        }

        if ($queryStr != ""){
            $url = substr($url, 0, strlen($url) - strpos($url, "/"));
            $url = "{$url}?{$queryStr}";
        }

        if ($anchor != ""){
            $url .= "#{$anchor}";
        }

        return $url;
    }

    /**
     *
     * @return <type>
    */
    function getUrlBySecType($secType, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $urlArray = array();


        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($tv['siteType'] != ''){
            $urlArray['siteType'] = $tv['siteType'];
        }

        if ($tv['sitePfxId'] != ''){
            $urlArray['sitePfxId'] = $tv['sitePfxId'];
        }

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        $secRec = getCPModelObj('webBasic_section')->getRecordByType($secType);

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){
            $urlArray['cp_site'] = $tv['cp_site'];
        }

        $urlArray['section_title'] = $secRec['title'];

        $append     = $fn->getIssetParam($exp, 'append', 0);
        $prependUrl = $fn->getIssetParam($exp, 'prependUrl');

        $url = $this->make_seo_url($urlArray, $append, $prependUrl);

        return $url;
    }

    /**
     *
    */
    function getUrlByCatType($catType, $secType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $urlArray = array();

        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){
            $urlArray['cp_site'] = $tv['cp_site'];
        }

        $catRec = getCPModelObj('webBasic_category')->getRecordByType($catType, $secType);

        if(is_array($catRec)){
            $urlArray['section_title']  = $catRec['section_title'];
            $urlArray['category_id']    = $catRec['category_id'];
            $urlArray['category_title'] = $catRec['category_title'];
        }

        $queryStr = $fn->getIssetParam($exp, 'queryStr');
        if ($queryStr != '') {
            $urlArray['queryStr'] = $queryStr;
        }

        $append     = $fn->getIssetParam($exp, 'append', 0);
        $prependUrl = $fn->getIssetParam($exp, 'prependUrl');
        $url = $this->make_seo_url($urlArray, $append, $prependUrl);

        return $url;
    }

    /**
     *
    */
    function getUrlBySubCatType($subCatType, $catType = '', $secType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $urlArray = array();

        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        $rec = getCPModelObj('webBasic_subCategory')
               ->getSubCatRecordByType($subCatType, $catType, $secType);

        if(is_array($rec)){
            $urlArray['section_title']      = $rec['section_title'];
            $urlArray['category_id']        = $rec['category_id'];
            $urlArray['category_title']     = $rec['category_title'];
            $urlArray['sub_category_id']    = $rec['sub_category_id'];
            $urlArray['sub_category_title'] = $rec['sub_category_title'];
        }

        $record_id = $fn->getIssetParam($exp, 'record_id');
        if($record_id != ''){
            $urlArray['record_id'] = $record_id;
        }

        $record_title = $fn->getIssetParam($exp, 'record_title');
        if($record_title != ''){
            $urlArray['record_title'] = $record_title;
        }

        $append     = $fn->getIssetParam($exp, 'append', 0);
        $prependUrl = $fn->getIssetParam($exp, 'prependUrl');
        $url = $this->make_seo_url($urlArray, $append, $prependUrl);

        return $url;
    }

    function getUrlByRecord($row, $keyFldName, $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $secType = $fn->getIssetParam($exp, 'secType');
        $catType = $fn->getIssetParam($exp, 'catType');
        $useTopVars = $fn->getIssetParam($exp, 'useTopVars', true);
        $strictlyUseTopVars = $fn->getIssetParam($exp, 'strictlyUseTopVars', false);

        if (isset($row['internal_link']) && trim($row['internal_link']) != ''){
            $url = $fn->replaceUrlVars($row['internal_link']);
            return $url;
        }

        if (isset($row['external_link']) && trim($row['external_link']) != ''){
            $url = $fn->replaceUrlVars($row['external_link']);
            return $url;
        }

        $urlArray = array();

        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($tv['siteType'] != ''){
            $urlArray['siteType'] = $tv['siteType'];
        }

        if ($tv['sitePfxId'] != ''){
            $urlArray['sitePfxId'] = $tv['sitePfxId'];
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){
            $urlArray['cp_site'] = $tv['cp_site'];
        }

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        /** if content **/
        if ($secType != ''){
            $secRec = getCPModelObj('webBasic_section')->getRecordByType($secType);
            $urlArray['section_title'] = $secRec['title'];
        } else if ($catType != ''){
            $catRec = getCPModelObj('webBasic_category')->getRecordByType($catType, $secType);

            if(is_array($catRec)){
                $urlArray['section_title']  = $catRec['section_title'];
                $urlArray['category_id']    = $catRec['category_id'];
                $urlArray['category_title'] = $catRec['category_title'];
            }

        } else {
            $urlArray['section_title'] = isset($row['section_title']) ? $row['section_title'] : $tv['room_name'];
        }

        if ($strictlyUseTopVars){
            if ($tv['subRoom'] != ''){
                $urlArray['category_id']    = $tv['subRoom'];
                $urlArray['category_title'] = $tv['catTitle'];
            }

        } else if (isset($row['category_id']) && $row['category_id'] != ''){
            $urlArray['category_id']    = $row['category_id'];
            $urlArray['category_title'] = $row['category_title'];

        } else if ($tv['subRoom'] != '' && $useTopVars){
            $urlArray['category_id']    = $tv['subRoom'];
            $urlArray['category_title'] = $tv['catTitle'];
        }

        if ($strictlyUseTopVars){
            if ($tv['subCat'] != ''){
                $urlArray['sub_category_id']    = $tv['subCat'] ;
                $urlArray['sub_category_title'] = $tv['subCatTitle'];
            }

        } else if (isset($row['sub_category_id']) && $row['sub_category_id'] != ''){
            $urlArray['sub_category_id']    = $row['sub_category_id'];
            $urlArray['sub_category_title'] = $row['sub_category_title'];

        } else if ($tv['subCat'] != '' && $useTopVars){
            $urlArray['sub_category_id']    = $tv['subCat'] ;
            $urlArray['sub_category_title'] = $tv['subCatTitle'];
        }

        $urlArray['record_id']    = $row[$keyFldName];
        $recTitle = isset($row['title']) ? $row['title'] : '';
        $urlArray['record_title'] = $fn->getIssetParam($exp, 'record_title', $recTitle);

        $append     = $fn->getIssetParam($exp, 'append', 0);
        $prependUrl = $fn->getIssetParam($exp, 'prependUrl');

        $url = $this->make_seo_url($urlArray, $append, $prependUrl);

        return $url;
    }

    /**
     *
     * @return <type>
     */
    function getCurrentCategoryUrl($exp = array()){
        $tv = Zend_Registry::get('tv');

        if ($tv['subRoom'] != ''){
            $wCat = getCPWidgetObj('core_subNav');
            $catRec = $wCat->getWidget(array(
                 'category_id' => $tv['subRoom']
                ,'returnDataOnly' => true
            ));

            if (is_array($catRec)){
                return $catRec[$tv['subRoom']]['url'];
            }
        }
    }

    /**
     *
     */
    function getUrlWithAction($val) {
        $arr['queryStr'] = "_action={$val}";

        return $this->make_seo_url($arr, 1);
    }

    /**
     *
     */
    function getUriWithNoQstr() {
        $url = $_SERVER['REQUEST_URI'];
        $arr = explode('?', $url);
        return $arr[0];
    }

    /**
     *
     */
    function getQstrInHiddenVariable($removeArr = array()) {
        $url = $_SERVER['REQUEST_URI'];
        $arr = explode('?', $url);

        $hiddenVars = '';
        if (count($arr) > 1){
            $qstr = $arr[1];
            $qstrArr = explode('&', $qstr);
            foreach($qstrArr AS $qstrParam){
                $qstrIndiv = explode('=', $qstrParam);
                if (!in_array($qstrIndiv[0], $removeArr)){
                    if (count($qstrIndiv) > 1){
                        $hiddenVars .= "<input type='hidden' name='{$qstrIndiv[0]}' value='{$qstrIndiv[1]}' />\n";
                    }
                }
            }
        }

        return $hiddenVars;
    }

    /**
     *
     */
    function getUriWithNoLang() {
        $url = $_SERVER['REQUEST_URI'];
        return substr($url, strpos($url, '/', 1));
    }

    /**
     *
     */
    function getQnMarkForUrl($url) {
        $tempArr = explode('?', $url);
        if (count($tempArr) == 1){
            return '?';
        }
    }

    /**
     *
     */
    function getAmpForUrl($url) {
        $tempArr = explode('?', $url);
        if (count($tempArr) > 1 && $tempArr[1] != ''){
            return '&';
        }
    }

    /**
     *
     */
    function getRequestURI(){
        $request_uri = $_SERVER['REQUEST_URI'];
        return $request_uri;
    }

    /**
     *
     */
    function getCurrentURL($removeArr = array()){

        $requestUri = $_SERVER['REQUEST_URI'];

        $reqUriArr = explode('&', $requestUri);
        $requestUriNew = '';
        foreach($reqUriArr AS $qstrParam){
            $qstrInd = explode('=', $qstrParam);
            $parName = $qstrInd[0];
            $value   = isset($qstrInd[1]) ? $qstrInd[1] : '';

            if (!in_array($parName, $removeArr)){
                if (count($qstrInd) > 1){ //params not like xyz: index.php?a=123&b=zzz&xyz
                    $requestUriNew .= '&' . $parName . '=' . $value;
                } else {
                    $requestUriNew .= '&' . $parName;
                }
            }
        }
        if (substr($requestUriNew, 0, 1) == '&') {
            $requestUriNew = substr($requestUriNew, 1);
        }

        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {$pageURL .= 's';}
        $pageURL .= '://';
        if ($_SERVER['SERVER_PORT'] != '80') {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $requestUriNew;
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'] . $requestUriNew;
        }
        return $pageURL;

    }

    /**
     *
     */
    function getURLPrefix(){
        $url = 'http';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {$url .= 's';}
        $url .= '://';
        if ($_SERVER['HTTP_HOST'] != '') {
            $url .= $_SERVER['HTTP_HOST'];
        } else {
            if ($_SERVER['SERVER_PORT'] != '80') {
                $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
            } else {
                $url .= $_SERVER['SERVER_NAME'];
            }
        }
        return $url;
    }

    /**
     *
     */
    function getQstrInHiddenVars($onlyInArray = ''){
        $uri = $_SERVER['REQUEST_URI'];
        $uriArr = explode('?', $uri);

        $hiddenVars = '';
        if (count($uriArr) > 1){
            $qstr = $uriArr[1];
            $qstrArr = explode('&', $qstr);
            foreach ($qstrArr AS $value) {
                $keyValArr = explode('=', $value);

                if(is_array($onlyInArray)){
                    if(in_array($keyValArr[0], $onlyInArray)){
                        $hiddenVars .= "<input type='hidden' name='{$keyValArr[0]}' value='{$keyValArr[1]}' />\n";
                    }
                } else {
                    $hiddenVars .= "<input type='hidden' name='{$keyValArr[0]}' value='{$keyValArr[1]}' />\n";
                }
            }
        }

        return $hiddenVars;
    }
}
