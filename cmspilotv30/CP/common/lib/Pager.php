<?
class CP_Common_Lib_Pager
{
    /**
     *
     */
    var $nextRecordsAvailable = false;
    var $prevRecordsAvailable = false;
    var $searchQueryString;
    var $urlStringOnly;
    var $queryStringOnly;

    var $recordOffset = 0;
    var $numRecordsPerPage;
    var $page;
    var $totalPages;
    var $totalRecords;
    var $startRecordNo;
    var $endRecordNo;
    var $searchDone = 0;

    /**
     *
     */
    function setSearchQueryString($scope = 'BOTH', $excludeArray = array()){
        $cpCfg = Zend_Registry::get('cpCfg');

        $this->searchQueryString = "";
        $searchQueryString = "";

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl'])  ? $cpCfg['cp.useSEOUrl']   : 0;

        if ($scope == 'BOTH' || $scope == 'GET'){
            foreach ($_GET as $key => $value) {

                if (in_array($key, $excludeArray)) {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $arrValue) {
                        $searchQueryString .= "&{$key}[]={$arrValue}";
                    }
                } else {
                    $searchQueryString .= "&{$key}=" . urlencode($value);
                }
            }
        }

        if ($scope == 'BOTH' || $scope == 'POST'){
            foreach ($_POST as $key => $value) {
                if (in_array($key, $excludeArray)) {
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $arrValue) {
                        $searchQueryString .= "&{$key}[]={$arrValue}";
                    }
                } else {
                    $searchQueryString .= "&{$key}=" . urlencode($value);
                }
            }
        }

        if ($cpCfg['cp.useSEOUrl'] == 1){
           $this->searchQueryString = $_SERVER['REQUEST_URI'];
           $this->searchDone        = isset($_REQUEST['searchDone']) ? $_REQUEST['searchDone'] : 0;

           $urlArr = explode('?', $this->searchQueryString);
           $this->urlStringOnly   = $urlArr[0];
           $this->queryStringOnly = isset($urlArr[1]) ? $urlArr[1] : '';

        } else {
           $searchQueryString       = substr($searchQueryString, 1);
           $this->searchQueryString = $_SERVER['PHP_SELF'] . "?" . $searchQueryString;
           $this->searchQueryString = $this->removeQueryString(array("PHPSESSID"));
           $this->urlStringOnly     = $_SERVER['PHP_SELF'];
           $this->queryStringOnly   = $searchQueryString;
        }
    }

    /**
     *
     */
    function setPagerData($totalRecords, $numRecordsPerPage, $gotoLastPageByDefault = false, $forcedPage = '') {

        if ($forcedPage != ''){
            $page = $forcedPage;
        } else {
            $page = isset( $_REQUEST['_page'] ) ? $_REQUEST['_page'] : "";
        }
        $goToLastPage = isset($_REQUEST['goToLastPage']) ? $_REQUEST['goToLastPage'] : "";
        $currentPage = isset($_REQUEST['currentPage']) ? $_REQUEST['currentPage'] : "";

        $totalRecords       = (int) $totalRecords;
        $numRecordsPerPage  = max((int) $numRecordsPerPage, 1);
        $page               = (int) $page;
        $totalPages         = ceil($totalRecords / $numRecordsPerPage);

        if (($gotoLastPageByDefault || $goToLastPage == 1) && $page == '') {
            $page = $totalPages;
        }

        if ($currentPage != '') {
            $page = $currentPage;
        }

        //*** if page = 0, then set as page = 1 ***//
        $page = max($page, 1);

        //*** if page is typed > numPages, then set page = numPages ***//
        $page = min($page, $totalPages);

        $recordOffset = ($page - 1) * $numRecordsPerPage;

        //--------------------------------------------------------//
        if ($page == 0) {
          $recordOffset = 0;
        }

        //print $page . "<br>";

        if ($page > 1) {
           $this->prevRecordsAvailable = true;
        }

        if ($page < $totalPages) {
           $this->nextRecordsAvailable = true;
        }
        //--------------------------------------------------------//

        $this->recordOffset       = $recordOffset > 0 ? $recordOffset : abs($recordOffset);
        $this->numRecordsPerPage  = $numRecordsPerPage;
        $this->page               = $page;
        $this->totalPages         = $totalPages;
        $this->totalRecords       = $totalRecords;
        $this->startRecordNo      = $recordOffset + 1;
        $this->endRecordNo        = min((int)($this->startRecordNo + $numRecordsPerPage - 1), $this->totalRecords);
        $this->startRecordNo      = $this->startRecordNo;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function addLimitToSql($SQL, $module = '', $limit = ''){
        $tv = Zend_Registry::get('tv');

        if ($limit == ''){
            if ($module == ''){
                $module = $tv['module'];
            }
            $limit = $this->getNumRecordsPerPage('', $module);
        }

        $tempSQLStr = $SQL . " LIMIT {$this->recordOffset}, {$limit}" ;

        return $tempSQLStr;
    }

    /**
     *
     * @return <type>
     */
    function getPageNumbersDropDown(){
        $tv = Zend_Registry::get('tv');

        $text = "";

        $url = $this->removeQueryString(array("_page"));
        $text .= "<form name=\"frmPageNos\" method=\"post\">";
        $text .= "<select onchange=\"javascript:DBUtil.goToPage(this, '{$url}')\">";

        for ($i = 1; $i <= $this->totalPages; $i++)
        {
           if ($i == $this->page){
             $text .= "<option selected value=\"{$i}\">{$i}</option>\n";
           } else {
             $text .= "<option value=\"{$i}\">{$i}</option>\n";
           }
        }

        $text .= "</select>";
        $text .= "</form>";

        return $text;
    }

    /**
     *
     * @param <type> $start
     * @param <type> $end
     * @return <type>
     */
    function getPageNumbersLinks($start = 1, $end= '', $appendText = '', $linkRecType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);
        $returnUrlOnly = $fn->getIssetParam($exp, 'returnUrlOnly', false);

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl'])  ? $cpCfg['cp.useSEOUrl']   : 0;

        $useSEOUrl = 0;

        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        $text = '';

        $url = $this->removeQueryString(array("_page"));

        if ($end == '' || $end > $this->totalPages){
            $end = $this->totalPages;
        }

        $links = array();

        for ($i = $start; $i <= $end; $i++){
            if($linkHasOnlyPageNo){
                $linkUrl = $i;
            } else {
                $linkArr = explode("?", $url);
                $hasQtnMark = count($linkArr) > 1 ? true : false;
                $queryStrStart = $hasQtnMark ? '&' : '?';
                $linkStringSEO  = (count($linkArr) == 2) ? $linkArr[0] . "Page-{$i}/?" . $linkArr[1] : $linkArr[0] . "Page-{$i}/" ;
                $pageUrl = ($useSEOUrl == 1 && $this->searchDone == 0) ? $linkStringSEO : $url . "{$queryStrStart}_page={$i}";
                $pageUrl .= ($linkRecType != '') ? "&linkRecType={$linkRecType}&showHTML=0" : '';
                $linkUrl = $pageUrl;
            }

            if ($returnUrlOnly){
                $links[$i] = $linkUrl;
            } else if ($i == $this->page){
                $text .= "<span class='currentPage'>{$i}</span>\n";
            } else if($linkHasOnlyPageNo){
                $text .= "<a href='javascript:void(0);' link='{$linkUrl}' class='pageNoLink'>{$i}</a>{$appendText}\n";
            } else {
                if ($linkRecType != ''){
                    $text .= "<a href='javascript:void(0);' link='{$linkUrl}' class='pageNoLink'>{$i}</a>{$appendText}\n";
                } else {
                    $text .= "<a href='{$linkUrl}' class='pageNoLink'>{$i}</a>{$appendText}\n";
                }
                $links[$i] = $pageUrl;
            }
        }
        
        if ($returnUrlOnly){
            return $links;
        } else {
            return $text;
        }
    }

    /**
     *
     * @param <type> $linkText
     * @param <type> $noLinkText
     * @param <type> $linkClass
     * @return <type>
     */
    function getNextRecordsText($linkText, $noLinkText = "", $linkClass = "", $linkRecType = '', $exp=array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);
        $returnUrlOnly = $fn->getIssetParam($exp, 'returnUrlOnly', false);

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl']) ? $cpCfg['cp.useSEOUrl'] : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        if ($noLinkText == "") $noLinkText = $linkText;

        if ($this->nextRecordsAvailable){
            $page = $this->page + 1;

            if ($linkHasOnlyPageNo){
                $text = "<a {$linkClass} href='javascript:void(0);' link='{$page}'><span>{$linkText}</span></a>";
            } else {
                if ($linkClass != ""){
                    $linkClass = " class='{$linkClass}' ";
                }

                $linkString = $this->removeQueryString(array("_page"));

                $linkArr = explode("?", $linkString);
                $hasQtnMark = count($linkArr) > 1 ? true : false;
                $queryStrStart = $hasQtnMark ? '&' : '?';
                $linkStringSEO  = (count($linkArr) == 2) ? $linkArr[0] . "Page-{$page}/?" . $linkArr[1] : $linkArr[0] . "Page-{$page}/" ;

                $linkString = ($useSEOUrl == 1 && $this->searchDone == 0) ? $linkStringSEO : $linkString . "{$queryStrStart}_page={$page}";
                $linkString .= ($linkRecType != '') ? "&linkRecType={$linkRecType}&showHTML=0" : '';

                if ($returnUrlOnly){
                    return $linkString;
                } else if ($linkRecType != ''){
                    $text = "<a {$linkClass} href='javascript:void(0);' link='{$linkString}'><span>{$linkText}</span></a>";
                } else {
                    $text = "<a {$linkClass} href='{$linkString}'><span>{$linkText}</span></a>";
                }
            }
        } else {
            if ($returnUrlOnly){
                return '';
            } else {
                $text = "<span class='noLink'>{$noLinkText}</span>";
            }
        }

        return $text;
    }

    /**
     *
     * @param <type> $linkText
     * @param <type> $noLinkText
     * @param <type> $linkClass
     * @return <type>
     */
    function getPrevRecordsText($linkText, $noLinkText= "", $linkClass = "", $linkRecType = '', $exp=array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);
        $returnUrlOnly = $fn->getIssetParam($exp, 'returnUrlOnly', false);

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl']) ? $cpCfg['cp.useSEOUrl'] : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        if ($noLinkText == ""){
             $noLinkText = $linkText;
        }

        if ($this->prevRecordsAvailable){
            $page = $this->page - 1;
            if ($linkHasOnlyPageNo){
                $text = "<a {$linkClass} href='javascript:void(0);' link='{$page}'><span>{$linkText}</span></a>";
            } else {
                if ($linkClass != ""){
                    $linkClass = " class='{$linkClass}' ";
                }

                $linkString  = $this->removeQueryString(array("_page"));

                $linkArr = explode("?", $linkString);
                $hasQtnMark = count($linkArr) > 1 ? true : false;
                $queryStrStart = $hasQtnMark ? '&' : '?';

                $linkStringSEO = (count($linkArr) == 2) ? $linkArr[0] . "Page-{$page}/?" . $linkArr[1] : $linkArr[0]. "Page-{$page}/" ;;
                $linkString = ($useSEOUrl == 1 && $this->searchDone == 0) ? $linkStringSEO : $linkString . "{$queryStrStart}_page={$page}";
                $linkString .= ($linkRecType != '') ? "&linkRecType={$linkRecType}&showHTML=0" : '';

                if ($returnUrlOnly){
                    return $linkString;
                } else if ($linkRecType != ''){
                    $text = "<a {$linkClass} href='javascript:void(0);' link='{$linkString}'><span>{$linkText}</span></a>";
                } else {
                    $text = "<a {$linkClass} href='{$linkString}'><span>{$linkText}</span></a>";
                }
            }
        } else {
            if ($returnUrlOnly){
                return '';
            } else {
                $text = "<span class='noLink'>{$noLinkText}</span>";
            }
        }

        return $text;
    }

    /**
     *
     * @param <type> $linkText
     * @param <type> $noLinkText
     * @param <type> $linkClass
     * @return <type>
     */
    function getFirstRecordsText($linkText, $noLinkText= "", $linkClass = ""){

        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl']) ? $cpCfg['cp.useSEOUrl'] : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        if ($noLinkText == "") $noLinkText = $linkText;

        if ($linkClass != ""){
            $linkClass = " class='{$linkClass}' ";
        }

        $linkString  = $this->removeQueryString(array("_page"));
        $page = 1;

        $linkArr = explode("?", $linkString);
        $hasQtnMark = count($linkArr) > 1 ? true : false;
        $queryStrStart = $hasQtnMark ? '&' : '?';

        $linkStringSEO  = (count($linkArr) == 2) ? $linkArr[0] . "Page-{$page}/?" . $linkArr[1] : $linkArr[0]. "Page-{$page}/" ;;
        $linkString     = ($useSEOUrl == 1 && $this->searchDone == 0) ? $linkStringSEO : $linkString . "{$queryStrStart}_page={$page}";

        if ($this->prevRecordsAvailable){
            return "<a {$linkClass} href=\"{$linkString}\">{$linkText}</a>";
        } else {
            return "<span{$linkClass}>{$noLinkText}</span>";
        }

    }

    /**
     *
     * @param <type> $linkText
     * @param <type> $noLinkText
     * @param <type> $linkClass
     * @return <type>
     */
    function getLastRecordsText($linkText, $noLinkText = "", $linkClass = ""){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl']) ? $cpCfg['cp.useSEOUrl'] : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        if ($noLinkText == "") $noLinkText = $linkText;

        if ($linkClass != ""){
            $linkClass = " class='{$linkClass}' ";
        }

        $linkString  = $this->removeQueryString(array("_page"));

        $page = $this->totalPages;

        $linkArr = explode("?", $linkString);
        $hasQtnMark = count($linkArr) > 1 ? true : false;
        $queryStrStart = $hasQtnMark ? '&' : '?';
        $linkStringSEO  = (count($linkArr) == 2) ? $linkArr[0] . "Page-{$page}/?" . $linkArr[1] : $linkArr[0] . "Page-{$page}/" ;

        $linkString = ($useSEOUrl == 1 && $this->searchDone == 0) ? $linkStringSEO : $linkString . "{$queryStrStart}_page={$page}";

        if ($this->nextRecordsAvailable){
          return "<a {$linkClass} href=\"{$linkString}\">{$linkText}</a>";
        } else {
          return "<span{$linkClass}>{$noLinkText}</span>";
        }
    }


    /**
     *
     * @param <type> $linkText
     * @param <type> $rowCounter
     * @param <type> $linkClass
     * @return <type>
     */
    function getGoToDetailText($linkText, $rowCounter, $linkClass = "", $row = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $page        = $rowCounter + $this->startRecordNo;
        $linkString  = str_replace("&_action=list", "", $this->queryStringOnly);
        $linkString  = @preg_replace("~&_page=[0-9]+~", "", $linkString);
        $linkString  = @preg_replace("~&currentId=[0-9]+~", "", $linkString);

        $modulesArr = Zend_Registry::get('modulesArr');
        $moduleArr = $modulesArr[$tv['module']];
        $keyField = $moduleArr['keyField'];

        if ($cpCfg['cp.useSEOUrl'] == 1){
            $recId = $row[$keyField];
            $linkString = ($linkString != '') ? '?' . $linkString : '';
            $urlStringOnly = preg_replace('/\/(Page)-[0-9]+\//', '/', $this->urlStringOnly);
            $linkString  = $urlStringOnly . 'detail/' . $recId . '/' . $linkString;
        } else {
            $keyFieldText = '';
            if ($moduleArr['useRecordIdForDetailEditLink']) {
                $keyFieldText = "&record_id={$row[$keyField]}";
            }

            $linkString  = $this->urlStringOnly . '?' . $linkString . "&_action=detail&_page=" . $page . $keyFieldText;
        }

        if ($linkClass != "") {
            $linkClass = " class={$linkClass} ";
        }

        return "<a ". $linkClass . "href=\"" . $linkString . "\">" . $linkText . "</a>";
    }

    /**
     *
     */
    function getGoToDetailLink($rowCounter) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $cpCfg['cp.useSEOUrl']    = isset($cpCfg['cp.useSEOUrl'])  ? $cpCfg['cp.useSEOUrl']   : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        $page        = $rowCounter + $this->startRecordNo;
        $linkString  = str_replace("&_action=list", "", $this->searchQueryString);
        $linkString  = @preg_replace("~&_page=[0-9]+~", "", $linkString);
        $linkString  = @preg_replace("~&currentId=[0-9]+~", "", $linkString);

        if ($useSEOUrl == 1){
           $linkString  = $linkString  . "detail/" . $page . "/";
        } else {
           $linkString  = $linkString  . "&_action=detail&_page=" . $page;
        }

        return $linkString;
    }

    /**
     *
     */
    function getGoToEditText($linkText, $rowCounter, $linkClass = '', $row = array()) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $page = $rowCounter + $this->startRecordNo;

        $linkString  = str_replace("&_action=list", "", $this->queryStringOnly);
        $linkString  = @preg_replace("~&_page=[0-9]+~", "", $linkString);
        $linkString  = @preg_replace("~&currentId=[0-9]+~", "", $linkString);

        $modulesArr = Zend_Registry::get('modulesArr');
        $moduleArr = $modulesArr[$tv['module']];
        $keyField = $moduleArr['keyField'];

        $keyFieldText = '';
        if ($cpCfg['cp.useSEOUrl'] == 1){
            $recId = $row[$keyField];
            $linkString = ($linkString != '') ? '?' . $linkString : '';
            $urlStringOnly = preg_replace('/\/(Page)-[0-9]+\//', '/', $this->urlStringOnly);
            $linkString  = $urlStringOnly . 'edit/' . $recId . '/' . $linkString;
        } else {
            $keyFieldText = '';
            if ($moduleArr['useRecordIdForDetailEditLink']) {
                $keyFieldText = "&record_id={$row[$keyField]}";
            }
            $linkString  = $this->urlStringOnly . '?' . $linkString . "&_action=edit&_page=" . $page . $keyFieldText;
        }

        if ($linkClass != ""){
            $linkClass = " class={$linkClass} ";
        }

       return "<a {$linkClass} href='{$linkString}'>" . $linkText . "</a>";
    }
    /**
     *
     * @return <type>
     */
    function getReturnToListLinkAfterDelete(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');
        $listLimit = '';

        if ($modulesArr[$tv['module']]){
            $listLimit  =  $modulesArr[$tv['module']]['listLimit'];
        }

        $page = ceil($this->startRecordNo / $listLimit);

        if ($cpCfg['cp.useSEOUrl'] == 1){
            $searchQueryString = $this->urlStringOnly;
            $searchQueryString = preg_replace('/\/(detail|edit)\/[0-9]+\//', '/', $searchQueryString);
        } else {
            $searchQueryString = $this->removeQueryString(array('_page', '_action', 'record_id'));
            $searchQueryString = $searchQueryString . "&_action=list&_page=" . $page;
        }


        return $searchQueryString;
    }

    /**
     *
     * @param <type> $rowCounter
     * @return <type>
     */
    function getGoToEditLink($rowCounter) {
        $tv = Zend_Registry::get('tv');

        $url = "index.php?_topRm=". $tv['topRm'] . "&module=" . $tv['module'] . "&_action=edit" ;
        $rowCounter = $rowCounter + $this->startRecordNo ;
        $linkString = "\"javascript:DBUtil.goToEditRecord('$rowCounter', '$url');\"" ;

        return $linkString;
    }

    /**
     *
     * @param <type> $action
     * @return <type>
     */
    function getNumRecordsPerPage($action="", $module = '') {
        $modulesArr = Zend_Registry::get('modulesArr');
        $tv = Zend_Registry::get('tv');
        $linksArray = Zend_Registry::get('linksArray');

        $tv = &$tv;
        $listLimitOverride  = isset($tv['listLimitOverride'])  ? $tv['listLimitOverride']   : "";

        if ($listLimitOverride != ""){
           return $listLimitOverride;
        }

        if ($action == ""){
           $action = $tv['action'];
        }

        if ($module == ""){
           $module = $tv['module'];
        }
        $print  = isset($_REQUEST['print']) ? $_REQUEST['print'] : 0;
        $export = isset($_REQUEST['export']) ? $_REQUEST['export'] : 0;

        if ($export == 1) {
           return 100000;
        } else if ($action == "list" || $action == ""){
            if ($tv['spAction'] == "link"){
                return $linksArray[$tv['linkName']]['listLimit'];
            } else {
                if (isset($module) && isset($modulesArr[$module]['listLimit'])){ /*** if front end **/
                    return $modulesArr[$module]['listLimit'];

                } else if (isset($modulesArr[$module]['listLimit'])){ /*** if admin **/
                    return $modulesArr[$module]['listLimit'];

                } else {
                    return 1000;
                }
            }
        } else if ($action == "detail" || $action == "edit") {
           return 1;

        } else if (($action == "print" && $tv['printWhat'] == "list" ) || $print == 1) {
           return 100000;

        } else if ($action == "print" && $tv['printWhat'] == "detail"){
           return 1;

        } else {
           return 1;
        }

    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getTotalRecords($SQL){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');


        //$countSQL = substr_replace(trim($SQL), "SELECT SQL_CALC_FOUND_ROWS", 0, 6);
        //$result = $db->sql_query($countSQL);
        //
        //$SQLCount = "SELECT FOUND_ROWS() AS totalRecords";
        //$resCount = $db->sql_query($SQLCount);
        //$row      = $db->sql_fetchrow($resCount);
        //return $row['totalRecords'];

        $countSQL = "SELECT count(*) AS totalRecords FROM ($SQL) x1" ;
        $result = $db->sql_query($countSQL);
        $row    = $db->sql_fetchrow($result);
        $total  = $row['totalRecords'];

        return $total;
    }

    /**
     *
     * @param <type> $removeArray
     * @return <type>
     */
    function removeQueryString($removeArray = array()){
        $cpCfg = Zend_Registry::get('cpCfg');

        $searchQueryString = $this->searchQueryString;

        $cpCfg['cp.useSEOUrl'] = isset($cpCfg['cp.useSEOUrl'])  ? $cpCfg['cp.useSEOUrl']   : 0;

        foreach ($removeArray as $value){

           if ($value == "_page"){
              $searchQueryString  = @preg_replace("~&_page=[0-9]+~", "", $searchQueryString);

              if ($cpCfg['cp.useSEOUrl'] == 1){
                 $searchQueryString  = @preg_replace("~Page-[0-9]+/~", "", $searchQueryString);
              }

           } else  if ($value == "record_id"){
              $searchQueryString  = @preg_replace("~&record_id=[0-9]+~", "", $searchQueryString);

           } else if ($value == "_action"){
              $searchQueryString = @preg_replace("~&_action=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "_spAction"){
              $searchQueryString = @preg_replace("~&_spAction=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              $searchQueryString = @preg_replace("~_spAction=[a-zA-Z0-9\\. _,]+&?~", "", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "lang"){
              $searchQueryString = @preg_replace("~&lang=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "_tab"){
              $searchQueryString = @preg_replace("~&_tab=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "logged_in"){
              $searchQueryString = str_replace("logged_in=1", "", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "module"){
              $searchQueryString = @preg_replace("~&module=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              $searchQueryString = @preg_replace("~module=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }

           else if ($value == "PHPSESSID"){
              $searchQueryString = @preg_replace("~&PHPSESSID=[a-zA-Z0-9\\. _,]+&?~", "&", $searchQueryString);
              if (substr($searchQueryString, -1) == "&") {
                 $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
              }
           }
        }

        return $searchQueryString;
    }


    /**
     *
     * @return <type>
     */
    function getBackButton($returnUrlOnly = false){
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');

        if ($cpCfg['cp.useSEOUrl'] == 1){
            $url = "javascript:history.back();";
        } else {
            $listLimit  =  $modulesArr[$tv['module']]['listLimit'];
            $page = ceil($this->startRecordNo / $listLimit);
            $searchQueryString = $this->removeQueryString(array("_page", "_action", 'record_id'));
            $url = $searchQueryString . $cpUrl->getQnMarkForUrl($searchQueryString)
                                      . $cpUrl->getAmpForUrl($searchQueryString)
                                      . "_action=list&_page=" . $page;
        }

        if ($returnUrlOnly){
            return $url;
        } else {
            $text = "
            <a href='{$url}'>
                {$ln->gd("cp.lbl.backToList")}
            </a>
            ";
            return $text;
        }
    }

    /**
     *
     * @param <type> $numPages
     * @return <type>
     */
    function getNavButtons($numPages, $action = '', $linkRecType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module  = Zend_Registry::get('currentModule')->name;

        $moduleArr = $modulesArr[$module];

        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);

        $text = "";

        $numPages = $numPages - 1;
        $action = ($action !== "") ? $action : $tv['action'];

        if ($this->page == ''){
            return;
        }

        if ($action == 'list' || $action == 'detail' || ($action == 'edit' && $moduleArr['hasAutoSave']) ){
            $startRange = $this->page;
            $endRange   = ($startRange + $numPages) > $this->totalPages ? $this->totalPages : ($startRange + $numPages);

            if ($endRange - $startRange <= $numPages){
                $startRange = ($endRange - $numPages) <= 0 ? 1 : ($endRange - $numPages);
            }

            $firstPage     = $this->getPageNumbersLinks(1, 1, '...&nbsp;', $linkRecType, $exp);
            $firstPageText = ($startRange > 1) ? $firstPage : '';
            $lastPage      = $this->getPageNumbersLinks($this->totalPages, '', '', $linkRecType, $exp);
            $lastPageText  = ($endRange < $this->totalPages) ? "...{$lastPage}" : "";

            $backToList = '';

            if ($action == "detail"){
                $backToList = "
                <div class='float_right backToList'>
                    {$this->getBackButton()}
                </div>
                ";
            }

            $totalRecordsText = $this->totalRecords;
            $showRecordCount = $fn->getReqParam('showRecordCount');
            if ($showRecordCount || $moduleArr['showRecordCount']) {
                $showRecordCount = true;
            }
            if (!$showRecordCount) {
                $totalRecordsText = 'Not counted';
            }

            $separator = '&';
            $urlViewAll = $_SERVER['REQUEST_URI'];
            if (strpos($urlViewAll, '?') === false) {
                $separator = '?';
            }
            $urlViewAll .= $separator . 'showAll=1';

            $text = "
            <div class='pagelinks'>
                <div class='floatbox'>
                    <!--
                    <div class='float_left viewAll'>
                        <a class='viewAll' href='{$urlViewAll}'>view all</a>
                    </div>
                    -->
                    <div class='float_left preBtn'>
                        {$this->getPrevRecordsText($ln->gd('cp.pager.previous', 'Previous'), '', '', $linkRecType, $exp)}
                    </div>
                    <div class='float_left linkNos'>
                        {$firstPageText}
                        {$this->getPageNumbersLinks($startRange, $endRange, '', $linkRecType, $exp)}
                        {$lastPageText}
                    </div>
                    {$backToList}
                    <div class='float_left nxtBtn'>
                        {$this->getNextRecordsText($ln->gd('cp.pager.next', 'Next'), '', '', $linkRecType, $exp)}
                    </div>
                    <div class='float_right totalRecs'>
                        {$ln->gd('cp.pager.lbl.totalRecords', 'Total Records')}: {$totalRecordsText}
                    </div>
                </div>
            </div>
          ";
        }

        return $text;
    }

    /**
     *
     * @param <type> $numPages
     * @return <type>
     */
    function getNavButtonsData($numPages = 5, $action = '', $linkRecType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module  = Zend_Registry::get('currentModule')->name;
        $moduleArr = $modulesArr[$module];
        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);
        $numPages = $numPages - 1;
        $action = ($action !== "") ? $action : $tv['action'];

        if ($this->page == ''){
            return;
        }

        $arr = array(
            'totalRecs'  => '',
            'firstPage'  => '',
            'lastPage'   => '',
            'prevPage'   => '',
            'nextPage'   => '',
            'backToList' => '',
            'pageLinks'  => '',
            'viewAll'    => '',
            'currentPage'=> $this->page,
            'startRecordNo'=> $this->startRecordNo,
            'endRecordNo'=> $this->endRecordNo,
        );

        if ($action == 'list' || $action == 'detail' || ($action == 'edit' && $moduleArr['hasAutoSave']) ){
            $exp['returnUrlOnly'] = true;
            $startRange = $this->page;
            $endRange   = ($startRange + $numPages) > $this->totalPages ? $this->totalPages : ($startRange + $numPages);

            if ($endRange - $startRange <= $numPages){
                $startRange = ($endRange - $numPages) <= 0 ? 1 : ($endRange - $numPages);
            }

            $arr['firstPage'] = $this->getPageNumbersLinks(1, 1, '', $linkRecType, $exp);
            $arr['lastPage']  = $this->getPageNumbersLinks($this->totalPages, '', '', $linkRecType, $exp);

            if ($action == "detail"){
                $arr['backToList'] = $this->getBackButton(true);
            }

            $arr['totalRecs'] = $this->totalRecords;

            $separator = '&';
            $urlViewAll = $_SERVER['REQUEST_URI'];
            if (strpos($urlViewAll, '?') === false) {
                $separator = '?';
            }
            $urlViewAll .= $separator . 'showAll=1';
            $arr['viewAll'] = $urlViewAll;

            $arr['prevPage'] = $this->getPrevRecordsText('', '', '', $linkRecType, $exp);
            $arr['nextPage'] = $this->getNextRecordsText('', '', '', $linkRecType, $exp);
            $arr['pageLinks'] = $this->getPageNumbersLinks($startRange, $endRange, '', $linkRecType, $exp);
        }

        return $arr;
    }
}