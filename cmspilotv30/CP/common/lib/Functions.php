<?
class CP_Common_Lib_Functions
{
    /**
     *
     */
    function getRecordCount($tableName, $condn = "", $exp = array()){
        $db = Zend_Registry::get('db');
        $includeSiteId = $this->getIssetParam($exp, 'includeSiteId', true); // set as false for history table

        if ($condn != ""){
            $condn = " WHERE {$condn}";
        }

        $appendSiteIdSQL = '';

        if ($includeSiteId){
            $appendSiteIdSQL = $this->appendSiteIdToCondn($condn);
        } else {
            $appendSiteIdSQL = $condn;
        }

        $SQL = "
        SELECT COUNT(*)
        FROM `{$tableName}`
        {$appendSiteIdSQL}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        return $row[0];
    }

    /**
     *
     */
    function getRecordCountBySQL($fromSQL, $condn = ""){
        $db = Zend_Registry::get('db');
        if ($condn != ""){
            $condn = " WHERE {$condn}";
        }

        $SQL = "
        SELECT COUNT(*)
        FROM {$fromSQL}
        {$this->appendSiteIdToCondn($condn)}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        return $row[0];
    }

    /**
     * Copied From Old functions.php
     */
    function getSaveExcelReport($objPHPExcel){
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getNextSortOrder($tableName, $condn = '') {
        $db = Zend_Registry::get('db');
        if ($condn != '') {
            $condn = " WHERE {$condn}";
        }

        $SQL    = "
        SELECT MAX(sort_order)
        FROM `{$tableName}`
        {$this->appendSiteIdToCondn($condn)}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0]+1;
    }
    /**
     *
     */
    function getRecordRowByID($tableName, $key_field_name, $id, $exp = array()) {
        $db = Zend_Registry::get('db');

        $globalForAllSites = $this->getIssetParam($exp, 'globalForAllSites', false);
        $fetchType = $this->getIssetParam($exp, 'fetchType', MYSQL_BOTH);

        if ($id == ''){
            return;
        }

        $condn = $this->getIssetParam($exp, 'condn');
        if (is_array($condn)) {
            $condn = $this->addSiteIdToWhereCondn();
        }

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        WHERE {$key_field_name} = {$id}
        {$condn}
        ";

        //print $SQL .'<hr>';

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result, $fetchType);

        return $row;
    }
    /**
     *
     */
    function getRecordTitleByID($tableName, $key_field_name, $id, $title_fld_name = 'title') {
        $db = Zend_Registry::get('db');
        if ($id == ''){
            return;
        }

        $SQL = "
        SELECT {$title_fld_name}
        FROM `{$tableName}`
        WHERE {$key_field_name} = {$id}
        {$this->addSiteIdToWhereCondn()}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row[$title_fld_name];
        }
    }

    /**
     *
     */
    function getSQL($SQL, $condnArr = array(), $exp = array()) {
        $dbUtil = Zend_Registry::get('dbUtil');
        return $dbUtil->getSQL($SQL, $condnArr, $exp);
    }

    /**
     *
     */
    function getArrBySQL($SQL, $condnArr = array(), $exp = array()){

        $condnArr = !is_array($condnArr) ? array() : $condnArr; // if $condnArr is passed as empty or null

        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fetchType = $this->getIssetParam($exp, 'fetchType', MYSQL_ASSOC);
        $forFormElement = $this->getIssetParam($exp, 'forFormElement', false);

        $SQL = $dbUtil->getSQL($SQL, $condnArr, $exp);
        $result = $db->sql_query($SQL);

        if ($forFormElement){
            return $dbUtil->getResultsetAsArrayForForm($result);
        } else {
            return $dbUtil->getResultsetAsArray($result, $fetchType);
        }
    }

    /**
     *
     */
    function getRecordByCondition($tableName, $condn = '', $orderBy = '', $module = '') {
        $db = Zend_Registry::get('db');
        if ($condn != '') {
            $condn = " WHERE {$condn}";
        }

        $orderBy = ($orderBy != '') ? " ORDER BY {$orderBy}" : '';

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        {$this->appendSiteIdToCondn($condn, $module)}
        {$orderBy}
        LIMIT 0, 1
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row;
        }
    }

    /**
     *
     */
    function getRecordByConditionForHistoryTable($tableName, $condn = '', $orderBy = '', $module = '') {
        $db = Zend_Registry::get('db');
        if ($condn != '') {
            $condn = " WHERE {$condn}";
        }

        $orderBy = ($orderBy != '') ? " ORDER BY {$orderBy}" : '';

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        {$condn}
        {$orderBy}
        LIMIT 0, 1
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row;
        }
    }

    function getRecordBySQL($SQL, $fetchType = MYSQL_BOTH) {
        $db = Zend_Registry::get('db');
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = null;
        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result, $fetchType);
        }
        return $row;
    }

    function getSettingsValueByKey($keyText) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = '{$keyText}'
        {$this->addSiteIdToWhereCondn()}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row   = $db->sql_fetchrow($result);
            $value = $row['value'];
        } else {
            $value = '';
        }
        return $value;
    }

    function getSettingsRowByKey($keyText) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT *
        FROM setting
        WHERE key_text = '{$keyText}'
        {$this->addSiteIdToWhereCondn()}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row   = $db->sql_fetchrow($result);
            return $row;
        }
    }

    function setSettingsValueByKey($keyText, $value) {
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE setting
        SET value = '{$value}'
        WHERE key_text = '{$keyText}'
        {$this->addSiteIdToWhereCondn()}
        ";
        $result = $db->sql_query($SQL);
    }

    function getSequenceFromSettings($key, $increment = 1) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        //-------------------------------------------------------//
        $db->sql_query('LOCK TABLES setting WRITE');
        $seq = $fn->getSettingsValueByKey($key);
        $SQL = "
        UPDATE setting SET value = (value+{$increment})
        WHERE key_text = '{$key}'
        ";
        $result = $db->sql_query($SQL);
        $db->sql_query('UNLOCK TABLES');

        return $seq;
    }

    function incrementSettingsValue($key, $increment = 1) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        //-------------------------------------------------------//
        // $db->sql_query('LOCK TABLES');
        $SQL = "
        UPDATE setting SET value = (value+{$increment})
        WHERE key_text = '{$key}'
        ";
        $result = $db->sql_query($SQL);
        //$db->sql_query('UNLOCK TABLES');

    }

    function getYesNo($value) {
        if ($value == 1){
            return "Yes";
        } else {
            return "No";
        }
    }

    function getYesNo2($value) {
        if ($value == '1'){
            return "Yes";
        } else if ($value == '0') {
            return "No";
        } else {
            return '-';
        }
    }

    /**
     *
     */
    function getSortText($sortText, $text, $linkRecType = '', $arrow = '') {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        $previousSortOrder = $tv['sortOrder'];
        $newSortOrder      = '';

        if ($previousSortOrder != '') {

            $currentSortArray = explode(",", $sortText);

            foreach($currentSortArray as $key => $currentSortColumn) {

                $pos1 = strpos($previousSortOrder, $currentSortColumn);
                $pos2 = strpos($previousSortOrder, $currentSortColumn . " asc");
                $pos3 = strpos($previousSortOrder, $currentSortColumn . " desc");

                if ($pos1 !== false || $pos2 !== false || $pos3 !== false) {
                    $posAsc  = strpos($previousSortOrder, $currentSortColumn . " asc");
                    $posDesc = strpos($previousSortOrder, $currentSortColumn . " desc");

                    if ($posAsc !== false) {
                        $newSortOrderTemp  = $currentSortColumn . " desc,";
                    } else if ($posDesc !== false) {
                        $newSortOrderTemp  = $currentSortColumn . " asc,";
                    } else {
                        $newSortOrderTemp  = $currentSortColumn . " desc,";
                    }

                    $newSortOrderTemp  = str_replace(" asc desc", " desc", $newSortOrderTemp);
                    $newSortOrderTemp  = str_replace(" desc asc", " asc", $newSortOrderTemp);
                    $newSortOrderTemp  = str_replace(" asc asc", " desc", $newSortOrderTemp);
                    $newSortOrderTemp  = str_replace(" desc desc", " asc", $newSortOrderTemp);
                    $newSortOrder      .= $newSortOrderTemp;

                } else {
                    $newSortOrder .= $currentSortColumn .",";
                }

            }
        } else {
            $newSortOrder = $sortText;
        }

        if (substr($newSortOrder, -1) == ",") {
            $newSortOrder = substr($newSortOrder, 0, strlen($newSortOrder)-1);
        }

        //$searchQueryString = urldecode($pager->searchQueryString);
        //urdecode exposes XSS attack
        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/_sortOrder=[a-zA-Z0-9\. _,+]+&?/', '', $searchQueryString);
        $linkString = $searchQueryString . $cpUrl->getQnMarkForUrl($searchQueryString)
                                         . $cpUrl->getAmpForUrl($searchQueryString)
                                         . "_sortOrder=" . urlencode($newSortOrder);


        if ($linkRecType != ''){
            $linkString .= "&linkRecType={$linkRecType}&showHTML=0";
            $text = "<a class='btnSort' href='javascript:void(0);' link='{$linkString}'>{$text}</a>";

        } else {
            $text = "<a href='{$linkString}'>{$text}{$arrow}</a>";
        }

        return $text ;
    }

    /**
     *
     */
    function returnAfterNewSave($currentID = 0, $returnTo="list", $ajaxRedirect = true) {
        $validate = Zend_Registry::get('validate');
        $returnUrl = $this->getPostParam('returnUrl');

        if ($returnUrl != ''){
            return $validate->getSuccessMessageXML($returnUrl);
        }

        $modulesArr = Zend_Registry::get('modulesArr');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        $apply = $this->getPostParam('apply');

        $pager->setSearchQueryString('GET', array("_page", "_action", "_spAction", 'logged_in', 'showHTML', 'record_id'));
        $searchQueryString = $pager->searchQueryString;

        $page      = $this->getReqParam('_page', 1);
        $keyField  = $modulesArr[$tv['module']]['keyField'];
        $tableName = $modulesArr[$tv['module']]['tableName'];
        $titleField = $modulesArr[$tv['module']]['titleField'];

        if ($tv['spAction'] == "add") {
            $returnTo  = "editFromNew";
        }

        if ($returnTo == "list") {
            $numRecordsPerPage = $pager->getNumRecordsPerPage("list");
            $page              = ceil($page / $numRecordsPerPage);

            $currentIDText = $currentID != 0 ? "&currentId={$currentID}" : '' ;
            $returnUrl     = "{$searchQueryString}&_action=list&_page={$page}{$currentIDText}";

        } else if ($returnTo == "detailFromNew" || $returnTo == "detailFromEdit") {
            $currentIDText = $currentID != 0 ? "&currentId={$currentID}" : '' ;
            $returnUrl = "{$searchQueryString}&_action=detail&record_id={$currentID}{$currentIDText}";

        } else if ($returnTo == "editFromNew") {

            if (CP_SCOPE == "www") {
                $editUrl = str_replace('/new/', '/edit/', $this->getPostParam('returnUrlPfx'));
                $returnUrl =  $editUrl . $currentID . '/';
            } else {
                $returnUrl = "{$searchQueryString}&_action=edit&record_id={$currentID}&newRecord=1";
            }

        } else {
            //TODO need to fix pager stuff here
            $recordIdText = '';
            if ($currentID != 0) {
                $recordIdText = "&record_id={$currentID}";
            }
            $returnUrl = "{$searchQueryString}&_action={$returnTo}&_page={$page}{$recordIdText}";
        }

        if ($apply == 1){
            $returnUrl = '';
        }

        if($ajaxRedirect == false){
            $cpUtil->redirect($returnUrl);
        }
        print $validate->getSuccessMessageXML($returnUrl);
    }

    /**
     *
     */
    function saveRecord($fieldsArray, $tableName = '', $keyField = '', $keyFieldVal = '', $exp = array()) {
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $customWhereCondn  = $this->getIssetParam($exp, 'customWhereCondn');

        if ($tableName == '') {
            if ($tv['module'] != ''){
                $keyField    = $modulesArr[$tv['module']]["keyField"];
                $tableName   = $modulesArr[$tv['module']]["tableName"];
            } else if ($tv['lnkRoom'] != ''){
                if ($tv['spAction'] == 'saveGridItem'){
                    $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
                    $clsInst  = getCPModuleObj($tv['srcRoom']);
                    $clsInst->fns->setLinksArray($arrayMasterLink);
                    $linksArray = $arrayMasterLink->linksArray;
                    $keyField  = $linksArray[$tv['linkName']]["historyTableKeyField"];
                    $tableName = $linksArray[$tv['linkName']]["historyTableName"];
                } else {
                    $keyField  = $modulesArr[$tv['lnkRoom']]["keyField"];
                    $tableName = $modulesArr[$tv['lnkRoom']]["tableName"];
                }
            } else {
                exit('Room Name missing in: $fn->saveRecord');
            }
        }

        $keyFieldVal = ($keyFieldVal != '') ? $keyFieldVal : $this->getPostParam($keyField, '', TRUE);
        $fieldsArray = $this->addModificationDetailsToFieldsArray($fieldsArray, $tableName);

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tableName != 'site' && $this->isDeveloper()){
            $fieldsArray = $this->addToFieldsArray($fieldsArray, 'site_id');
        }

        if ($customWhereCondn != ''){
            $whereCondition = "WHERE {$customWhereCondn}";
        } else {
            $whereCondition = "WHERE {$keyField} = '{$keyFieldVal}'";
        }
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fieldsArray, $tableName, $whereCondition);
        $db->sql_query($SQL);
        //fb::log($SQL);
        //print $SQL;
        return $keyFieldVal;
    }

    function isDeveloper(){
        return false;
    }

    /**
     *
     */
    function addModificationDetailsToFieldsArray($fieldsArray, $tableName = '') {
        $dbUtil = Zend_Registry::get('dbUtil');

        if($dbUtil->getColumnExists($tableName, 'modification_date')){
            $fieldsArray['modification_date'] = date("Y-m-d H:i:s");
        }

        if($dbUtil->getColumnExists($tableName, 'modified_by')){
            $fieldsArray['modified_by'] = $this->getSessionParam('userName');
        }

        return $fieldsArray;
    }

    /**
     *
     */
    function addRecord($fieldsArray, $tableName = '', $excludeFldsArr = array()) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tableName == '') {
            if ($tv['module'] != ''){
                $tableName   = $modulesArr[$tv['module']]['tableName'];
            } else if ($tv['lnkRoom'] != ''){
                $tableName   = $modulesArr[$tv['lnkRoom']]['tableName'];
            } else {
                exit('Room Name missing in: $fn->addRecord');
            }
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tableName != 'site'){
            if($dbUtil->getColumnExists($tableName, 'site_id')){
                $fieldsArray['site_id'] = $this->getSessionParam('cp_site_id');
            }
        }

        if ($cpCfg['cp.hasMultiYears']){
            if($dbUtil->getColumnExists($tableName, 'cp_year')){
                $fieldsArray['cp_year'] = $fn->getSessionParam('cp_year');
            }
        }

        $fieldsArray = $this->addCreationDetailsToFieldsArray($fieldsArray, $tableName);
        //-----------------------------------------------------------------------//
        $SQL    = $dbUtil->getInsertSQLStringFromArray($fieldsArray, $tableName, $excludeFldsArr);
        //fb::log($SQL);
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        return $id;
    }
    /**
     *
     */
    function addCreationDetailsToFieldsArray($fieldsArray, $tableName = '') {
        $dbUtil = Zend_Registry::get('dbUtil');
        if($dbUtil->getColumnExists($tableName, 'creation_date')){
            $fieldsArray['creation_date'] = date('Y-m-d H:i:s');
        }

        if($dbUtil->getColumnExists($tableName, 'created_by')){
            $fieldsArray['created_by'] = $this->getSessionParam('userName');
        }

        return $fieldsArray;
    }
    /**
     *
     */
    function addRecordAndReturnToEdit() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');

        $keyField    = $modulesArr[$tv['module']]["keyField"];
        $tableName   = $modulesArr[$tv['module']]["tableName"];

        $fieldsArray['creation_date'] =  date("Y-m-d H:i:s");

        //-----------------------------------------------------------------------//
        $SQL         = $dbUtil->getInsertSQLStringFromArray($fieldsArray, $tableName);
        $result      = $db->sql_query($SQL);
        $record_id   = $db->sql_nextid();

        //----------------------------------------------------//
        if (file_exists("{$cpCfg['cp.masterPath']}pages/main/{$tv['module']}.php")) {
            require_once("{$cpCfg['cp.masterPath']}pages/main/{$tv['module']}.php");
        }

        require_once("{$cpCfg['localAdminPath']}pages/main/{$tv['module']}.php");

        $moduleName         = strtoupper(substr($tv['module'],0,1)) . substr($tv['module'],1);
        $clsNameTemp      = "{$moduleName}Local";  //eg: AlbumLocal
        $$tv['module'] = new $clsNameTemp();  //eg: $album = new AlbumLocal

        if (method_exists($$tv['module'], "setDefaultOnNewRecordCreation")) {
            $$tv['module']->setDefaultOnNewRecordCreation($record_id);
        }

        //-----------------------------------------------------------------------//
        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        $searchQueryString = preg_replace('/&{$keyField}=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        $searchQueryString = preg_replace('/&newRecord=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
            $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
        }
        $returnUrl = "{$searchQueryString}&_action=edit&{$keyField}={$record_id}&newRecord=1";
        $cpUtil->redirect($returnUrl);
    }
    /**
     *
     */
    function addToFieldsArray($arr, $key, $defaultValue = '', $hasLang = false, $actualFldName = ''){
        $ln = Zend_Registry::get('ln');

        $fldName = ($actualFldName != '') ? $actualFldName : $key;
        $arrKey = $hasLang ? $ln->getFieldPrefix() . $fldName : $fldName;
        if(isset($_POST[$key])){
            $value = $this->getPostParam($key, $defaultValue);

            if (is_array($value)){
                $value = join(', ', $value);
            }

            $arr[$arrKey] = $value;
        } else if ($defaultValue !== ''){
            $arr[$arrKey] = $defaultValue;
        }

        return $arr;
    }

    /**
     *
     */
    function addToFldsArrBySrcArr($srcArr, $fa, $key, $defaultValue = '', $hasLang = false, $actualFldName = ''){
        $ln = Zend_Registry::get('ln');

        $fldName = ($actualFldName != '') ? $actualFldName : $key;
        $arrKey = $hasLang ? $ln->getFieldPrefix() . $fldName : $fldName;

        if(isset($srcArr[$key])){
            $value = $srcArr[$key];

            if (is_array($value)){
                $value = join(', ', $value);
            }

            $fa[$arrKey] = $value;
        } else if ($defaultValue !== ''){
            $fa[$arrKey] = $defaultValue;
        }

        return $fa;
    }

    /**
     *
     */
    function addSessionToFieldsArray($arr, $key, $defaultValue = '', $hasLang = false, $actualFldName = ''){
        $ln = Zend_Registry::get('ln');

        $fldName = ($actualFldName != '') ? $actualFldName : $key;
        $arrKey = $hasLang ? $ln->getFieldPrefix() . $fldName : $fldName;

        if(isset($_SESSION[$key])){
            $value = $this->getSessionParam($key, $defaultValue);

            if (is_array($value)){
                $value = join(', ', $value);
            }

            $arr[$arrKey] = $value;
        } else if ($defaultValue !== ''){
            $arr[$arrKey] = $defaultValue;
        }

        return $arr;
    }

    /**
     * To remove the selected search vars from the registry
     * @param array $varKeys
     */
    function removeSearchVars($varKeys = array()){
        $searchVar = Zend_Registry::get('searchVar');
        foreach($varKeys as $key) {
            if(array_key_exists($key, $searchVar->sqlSearchVar)){
                unset($searchVar->sqlSearchVar[$key]);
            }
        }
    }

    /**
     *
     */
    function getFullHourValueFromDD($prefix = '') {
        $field1 = $prefix . "_dd_hour";
        $field2 = $prefix . "_dd_minute";

        //---------------------------------------------------------//
        $dd_hour      = isset($_POST[$field1])  ? $_POST[$field1] : '';
        $dd_minute    = isset($_POST[$field2])  ? $_POST[$field2] : '';

        if ($dd_hour != '' && $dd_minute != '') {
            $timeValue = $dd_hour . ":" . $dd_minute;
        } else {
            $timeValue = '' ;
        }

        return $timeValue;
    }
    /**
     *
     */
    function getValueListSQL($valuelist, $orderBy = '', $exp = array()) {
        $fn = Zend_Registry::get('fn');

        $orderBy = ($orderBy != '') ? $orderBy : 'sort_order, value';

        $globalForAllSites = $fn->getIssetParam($exp, 'globalForAllSites', false);
        $condn = "WHERE key_text = '{$valuelist}'";

        if (!$globalForAllSites){
            $condn = $fn->appendSiteIdToCondn($condn);
        }

        $text = "
        SELECT value
        FROM valuelist
        {$condn}
        ORDER BY {$orderBy}
        ";

        return $text;
    }

    function hasValueInValueList($valuelist, $value) {
        $dbz = Zend_Registry::get('dbz');

        $SQL = "
        SELECT value
        FROM valuelist
        WHERE key_text = ?
          AND value = ?
        ";
        $stmt = $dbz->query($SQL, array($valuelist, $value));

        $retVal = false;
        $row = $stmt->fetch();
        if ($row) {
            $retVal = true;
        }

        return $retVal;
    }

    /**
     *
     */
    function getValuelistValueAsArray($valuelist, $orderBy = '') {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL    = $this->getValueListSQL($valuelist, $orderBy = '');
        $result = $db->sql_query($SQL);
        $arr    = $dbUtil->getResultsetAsArrayForForm($result);

        return $arr;
    }

    /**
     * To extratct the distinct years from the content date
     */
    function getContentYearSQL($extraParam = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $exp = $extraParam;

        $content_type  = $this->getIssetParam($exp, 'content_type', 'Record');
        $section_type  = $this->getIssetParam($exp, 'section_type');
        $category_type = $this->getIssetParam($exp, 'category_type');
        $addSearchCond = $this->getIssetParam($exp, 'addSearchCond');

        $sectionType  = ($section_type  != '') ? " AND s.section_type = '{$section_type}'"    : '';
        $categoryType = ($category_type != '') ? " AND ca.category_type = '{$category_type}'" : '';

        $multiSitesCond = "";
        if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
            $multiSitesCond = "
            AND c.content_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_content'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        $SQL= "
        SELECT DISTINCT YEAR(c.content_date) AS content_year
        FROM content c
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        LEFT JOIN (category ca) ON (ca.category_id = c.category_id)
        WHERE c.published = 1
          AND c.content_date IS NOT NULL
          AND c.content_type = '{$content_type}'
          {$sectionType}
          {$categoryType}
          {$multiSitesCond}
          {$addSearchCond}
        ORDER BY content_year
        ";

        return $SQL;
    }

    /*
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        //$fn->getSaveList();
    }

    /**
     *
     */
    function addLangKey($key) {
        $langKeysForJS = array();
        if (is_array($key)) {
            foreach ($key as $value) {
                $langKeysForJS[] = $value;
            }
        } else {
            $langKeysForJS[] = $key;
        }

        CP_Common_Lib_Registry::arrayMerge('langKeysForJS', $langKeysForJS);
    }

    /**
     *
     */
    function addCfgKey($key, $value) {
        $tv = Zend_Registry::get('tv');

        $tv['cfgKeys'][$key] = $value;

        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    function getLangKeys() {
        $ln = Zend_Registry::get('ln');
        $langKeysForJS = Zend_Registry::get('langKeysForJS');

        $arr1 = array();

        foreach ($langKeysForJS as $langKey) {
            $langKey2 = str_replace('.', '_', $langKey);
            $arr1[$langKey2] = $ln->gd2($langKey);
        }

        $data = json_encode($arr1);
        $data = str_replace("\",", "\",\n", $data);

        $text = "
        <script>
            $(function(){
                Lang.data = $data;
            });
        </script>
        ";

        return $text;
    }

    function getCfgKeys() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $arr1 = array();

        foreach ($tv['cfgKeys'] as $cpCfgKey) {
            $arr1[$cpCfgKey] = $ln->gd($cpCfgKey);
        }

        $data = json_encode($tv['cfgKeys']);
        $data = str_replace("\",", "\",\n", $data);

        $text = "
        <script>
            $(function(){
                Cfg.data = {$data};
            });
        </script>
        ";

        return $text;
    }

    function isModuleExist($module) {
        $modulesArr = Zend_Registry::get('modulesArr');

        if (isset($modulesArr[$module])){
            return true;
        } else {
            return false;
        }
    }

    function getLinkedIDs($srcRoom, $lnkRoom, $linkMasterTableID = '') {
        $tv = Zend_Registry::get('tv');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $modulesArr = Zend_Registry::get('modulesArr');
        $db = Zend_Registry::get('db');

        if ($tv['linkSingle']){
            return;
        }

        if ($linkMasterTableID == ''){
            $linkMasterTableID = $tv['linkMasterTableID'];
        }

        $linksArray   = $arrayMasterLink->linksArray;
        $linkName         = $srcRoom . "#" . $lnkRoom;
        $mainRoomKeyField = $modulesArr[$srcRoom]["keyField"];
        $linkRoomKeyField = $modulesArr[$lnkRoom]["keyField"];
        $historyTableName = $linksArray[$linkName]["historyTableName"];
        $keyFieldForLinking = $linksArray[$linkName]["keyFieldForLinking"];
        $mainRoomKeyFldNameInHistTbl = $linksArray[$linkName]["mainRoomKeyFldNameInHistTbl"];
        $linkRoomKeyFldNameInHistTbl = $linksArray[$linkName]["linkRoomKeyFldNameInHistTbl"];

        //changed by Ahmad on 02-12-2010. to be reviewed
        if ($mainRoomKeyFldNameInHistTbl != ""){
            $mainRoomKeyField  = $mainRoomKeyFldNameInHistTbl;
        }

        if ($linkRoomKeyFldNameInHistTbl != ""){
            $linkRoomKeyField  = $linkRoomKeyFldNameInHistTbl;
        }

        // deprecated.. kept for backward compatibility use mainRoomKeyFldNameInHistTbl instead
        if ($keyFieldForLinking != ""){
            $linkRoomKeyField  = $keyFieldForLinking;
        }

        $query  = "SET SESSION group_concat_max_len = 1000000";
        $result = $db->sql_query($query);

        /*** check the availability of the function in the module level ***/
        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);

        //ex: getPmsBatchPmsTeacherLinkLinkedIdsSQL
        $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                    ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) . "LinkedIdsSQL";

        $modObj = getCPModuleObj($srcRoom);
        if (method_exists($modObj->model, $funcName)) {
            $SQL = $modObj->model->$funcName();
        } else {
            $SQL = "
            SELECT (
                        SELECT GROUP_CONCAT({$linkRoomKeyField} SEPARATOR '#')
                        FROM {$historyTableName} WHERE {$mainRoomKeyField} = '{$linkMasterTableID}'
                    )
            AS selectedIDs";
        }

        //print "<pre class='sql'>" . $SQL . '</pre>';

        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        if ($row['selectedIDs'] != ""){
            $selectedIds = "#" . $row['selectedIDs'] . "#";
            return $selectedIds;
        }
    }

    function setSearchVarForLinkData($searchVar, $linkRecType, $fldName) {
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');

        $linkedIds = Zend_Registry::get('linkedIds');

        if ($linkRecType == 'linked'){
            if ($linkedIds != "") {
                $ids = $cpUtil->getStringAsCommaSeperated($linkedIds, "#");
                $searchVar->sqlSearchVar[] = "{$fldName} IN ({$ids})";
            } else {
                $searchVar->sqlSearchVar[] = "{$fldName} IN ('0')";
            }

        } else if ($linkRecType == 'notLinked'){
            if ($linkedIds != "") {
                $ids = $cpUtil->getStringAsCommaSeperated($linkedIds, "#");
                $searchVar->sqlSearchVar[] = "{$fldName} NOT IN ({$ids})";
            }
        }
    }

    function setSearchVarForSpecialSearch($searchVar, $prefix) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $special_search  = $fn->getReqParam('special_search');

        if ($special_search != '' ) {
            if ($special_search == 'Published') {
                $searchVar->sqlSearchVar[] = "{$prefix}.published = 1";
            }

            if ($special_search == 'Not-Published') {
                $searchVar->sqlSearchVar[] = "{$prefix}.published = 0 OR {$prefix}.published IS NULL OR {$prefix}.published = ''";
            }

            if ($special_search == 'Flag' ) {
                $searchVar->sqlSearchVar[] = "{$prefix}.flag = 1";
            }
        }
    }

    function getOpenLinkUrl($srcRoom, $lnkRoom, $fldName, $exp = array()) {
        $fn = Zend_Registry::get('fn');

        $surroundHyperLinkTag = $fn->getIssetParam($exp, 'surroundHyperLinkTag', false);
        $width = $fn->getIssetParam($exp, 'width', '');

        $url = "index.php?srcRoom={$srcRoom}&lnkRoom={$lnkRoom}&_action=list" .
               "&_spAction=link&fldName={$fldName}&linkSingle=1&showHTML=0";
        $text = '';
        if ($surroundHyperLinkTag) {
            $text = "
            <a class='editLinkSingle' link='{$url}' href='{$url}' w='{$width}'>Choose</a>
            ";
        } else {
            $text = $url;
        }
        return $text;
    }

    function getSrcRoomKeyFldName() {
        $modulesArr = Zend_Registry::get('modulesArr');
        $tv = Zend_Registry::get('tv');

        return $modulesArr[$tv['srcRoom']]["keyField"];
    }

    function getEditFromListUrl($row) {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $keyFld = $modulesArr[$tv['module']]["keyField"];

        return "index.php?module={$tv['module']}&_spAction=editFromList&id={$row[$keyFld]}&showHTML=0";
    }

    function getGeoCountrySQL() {
        $SQL = "
        SELECT country_code
              ,name
        FROM geo_country
        ";

        $condnArr = array();

        if (CP_SCOPE == 'www'){
            $condnArr[] = "published = 1";
        }

        return $this->getSQL($SQL, $condnArr, array('orderBy' => 'name'));
    }

    function getIssetParam($arr, $key, $defaultValue = '', $pattern = '') {
        $retVal = '';
        $value = isset($arr[$key]) ? $arr[$key] : '';

        if($value !== '') {
            $retVal = $value;
            if ($pattern != '') {
                $retVal = sprintf($pattern, $retVal);
            }
        } else {
            $retVal = $defaultValue;
        }

        return $retVal;
    }

    function getVariable($value, $defaultValue = '', $pattern = '') {
        $retVal = '';

        if($value != '') {
            $retVal = $value;
            if ($pattern != '') {
                $retVal = sprintf($pattern, $retVal);
            }
        } else {
            $retVal = $defaultValue;
        }

        return $retVal;
    }

    function getReqParam($name, $defaultValue = '', $qstr = false, $is_Array = false, $exp = array()) {
        $retVal = '';

        if($is_Array){
            $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : array();
        } else {
            $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
        }

        if ($qstr && !is_array($value)){
            $value = qstr($value);
        }

        $strip_tags = $this->getIssetParam($exp, 'strip_tags');
        if (((CP_SCOPE == 'www' && $strip_tags == '') || $strip_tags == true) && !is_array($value)){
            $value = strip_tags($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }

        return $retVal;
    }

    /**
     * @param <type> $name
     * @param <type> $defaultValue
     * @return <type>
     */
    function getPostParam($name, $defaultValue = '', $qstr = false, $is_Array = false, $exp = array()) {
        $retVal = '';
        if($is_Array){
            $value = isset($_POST[$name]) ? $_POST[$name] : array();
        } else {
            $value = isset($_POST[$name]) ? $_POST[$name] : '';
        }

        if ($qstr && !is_array($value)){
            $value = qstr($value);
        }

        $strip_tags = $this->getIssetParam($exp, 'strip_tags');
        if (((CP_SCOPE == 'www' && $strip_tags == '') || $strip_tags == true) && !is_array($value)){
            $value = strip_tags($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }
        return $retVal;
    }

    /**
     * @param <type> $name
     * @param <type> $defaultValue
     * @return <type>
     */
    function getGetParam($name, $defaultValue = '', $qstr = false, $is_Array = false, $exp = array()) {
        $retVal = '';

        if($is_Array){
            $value = isset($_GET[$name]) ? $_GET[$name] : array();
        } else {
            $value = isset($_GET[$name]) ? $_GET[$name] : '';
        }


        if ($qstr && !is_array($value)){
            $value = qstr($value);
        }

        $strip_tags = $this->getIssetParam($exp, 'strip_tags');
        if (((CP_SCOPE == 'www' && $strip_tags == '') || $strip_tags == true) && !is_array($value)){
            $value = strip_tags($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }
        return $retVal;
    }

    function hasReqParam($name) {
        $hasParam = isset($_REQUEST[$name]) ? true : false;
        return $hasParam;
    }

    /**
     * @param <type> $name
     * @param <type> $defaultValue
     * @return <type>
     */
    function getSessionParam($name, $defaultValue = '', $qstr = false, $is_Array = false) {
        $retVal = '';

        if($is_Array){
            $value = isset($_SESSION[$name]) ? $_SESSION[$name] : array();
        } else {
            $value = isset($_SESSION[$name]) ? $_SESSION[$name] : '';
        }

        if ($qstr){
            $value = qstr($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }
        return $retVal;
    }


    /**
     * @param <type> $name
     * @param <type> $defaultValue
     * @return <type>
     */
    function getCookieParam($name, $defaultValue = '', $qstr = false, $is_Array = false) {
        $retVal = '';

        if($is_Array){
            $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : array();
        } else {
            $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
        }

        if ($qstr){
            $value = qstr($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }
        return $retVal;
    }

    /**
     * @param <type> $name
     * @param <type> $defaultValue
     * @return <type>
     */
    function getServerParam($name, $defaultValue = '', $qstr = false) {
        $retVal = '';
        $value = isset($_SERVER[$name]) ? $_SERVER[$name] : '';

        if ($qstr){
            $$value = qstr($value);
        }

        if($value != ''){
            $retVal = $value;
        } else {
            $retVal = $defaultValue;
        }
        return $retVal;
    }

    function setSessionParam($name, $value = '') {
        $_SESSION[$name] = $value;
    }


    /**
     *
     */
    function getLinkRoomTitleText() {
        $tv = Zend_Registry::get('tv');
        $linksArray = Zend_Registry::get('linksArray');
        $text = "";

        if (isset($linksArray[$tv["linkName"]]['listTitle'])) {
            return "<h1>{$linksArray[$tv['linkName']]['listTitle']}</h1>";
        }
    }

    /*
     * https://gist.github.com/349273    - equivalent function for 5.2
     */
    function getCPDate($timeString, $format = '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($timeString == ''){
            return;
        }

        $toTimeZone = $this->getSessionParam('cpUserTimeZone', $cpCfg['cp.TimeZone']);

        if(isset($toTimeZone)){

            $d = new DateTime($timeString);
            if(!method_exists($d, 'getTimestamp')){//php 5.2
                include_once(CP_LIBRARY_PATH.'lib_php/datetime_52.php');
                $d = new DateTime_52($timeString);
            }

            if ($d) {
                $d->setTimeZone(new DateTimeZone($toTimeZone));

                if ($format == ''){
                    $format = $cpCfg['cp.dateDisplayFormat'];
                }

                if($cpCfg['cp.locale'] != ''){
                    setlocale(LC_TIME, $cpCfg['cp.locale']);
                    $ts = $d->getTimestamp(); // this available in PHP >= 5.3
                    $newDate = strftime($format, $ts);
                } else {
                    $newDate = $d->format($format);
                }

                return $newDate;
            }
        }
        return null;
    }

    /*** to trim off extraneous zeros and  the decimal, leaving important numbers intact: **/
    function getTrimmedDecimals($num){
        return rtrim($num, '0');
    }

    /**
     *
     */
    function getFnNameByAction() {
        $tv = Zend_Registry::get('tv');

        $actionName = ucfirst($tv['action']);
        $actionName = "get{$actionName}";

        return $actionName;
    }

    /**
     *
     */
    function getFnNameByViewRecType() {
        $tv = Zend_Registry::get('tv');
        $text = 'get' . str_replace(' ', '', $tv['currentViewRecType']);

        return $text;
    }


    /**
     *
     */
    function removeTrailingZeros($number) {
        //remove trailing decimal point and zeros
        $number = trim($number, '0');
        if ($number >= 1) {
            $number = trim($number, '.');
        }
        return $number;
    }

    /**
     * Formats a number based on $displayDecimalLength.
     */
    function getFormatNumber($number, $displayDecimalLength = '', $hasThousandSep = true, $exp = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($number == '') {
            return '';
        }

        $forceNegative = null;
        $defOptsArr = array(
            'forceNegative' => false
        );
        $exp = array_merge($defOptsArr, $exp);
        extract($exp);

        if ($displayDecimalLength == '') {
            $displayDecimalLength = $cpCfg['cp.displayDecimalLength'];
        }

        if ($forceNegative) {
            $number = abs($number);
        }

        if ($hasThousandSep) {
            $number = number_format($number, $displayDecimalLength);
        } else {
            $number = number_format($number, $displayDecimalLength, '.', '');
        }

        if ($forceNegative) {
            $negSignPre = '(';
            $negSignPost = ')';
            $number = $negSignPre . $number . $negSignPost;
        }

        return $number;
    }

    function getFormatNumberNegative($number, $displayDecimalLength = '', $hasThousandSep = true) {
        $exp = array('forceNegative' => true);
        return $this->getFormatNumber($number, $displayDecimalLength, $hasThousandSep, $exp);
    }

    /**
     *
     */
    function getDDSql($module, $exp = array()) {
        $modulesArr = Zend_Registry::get('modulesArr');

        if (!isset($modulesArr[$module])){
            return;
        }

        $titleFld          = $this->getIssetParam($exp, 'titleFld', $modulesArr[$module]['titleField']);
        $keyField          = $this->getIssetParam($exp, 'keyField', $modulesArr[$module]['keyField']);

        $sortBy            = $this->getIssetParam($exp, 'sortBy', $titleFld);
        $extraFlds         = $this->getIssetParam($exp, 'extraFlds');
        $globalForAllSites = $this->getIssetParam($exp, 'globalForAllSites', false);

        $tableName = $modulesArr[$module]['tableName'];

        //sort by ex: sort_order, title
        if ($sortBy != $titleFld) {
            $sortBy = $sortBy . ',' . $titleFld;
        }
        $extraFlds = ($extraFlds != '') ? ",{$extraFlds}" : '';

        $condn = $this->getIssetParam($exp, 'condn');
        if (is_array($condn)) {
            $condn = join(' AND ', $condn);
        }
        $condn = ($condn != '') ? "WHERE {$condn}" : '';


        if (!$globalForAllSites){
            $condn = $this->appendSiteIdToCondn($condn);
        }

        $SQL = "
        SELECT {$keyField}
              ,{$titleFld} AS title
              {$extraFlds}
        FROM {$tableName}
        {$condn}
        ORDER BY {$sortBy}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getDdDataAsArray($module, $exp = array()) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        if(!isset($modulesArr[$module])){
            print "{$module} is not set in modulesArray";
        }

        $SQL    = $this->getDDSql($module, $exp);
        $result = $db->sql_query($SQL);
        $arr    = $dbUtil->getResultsetAsArrayForForm($result);

        return $arr;
    }

    /**
     *
     */
    function getRecordIdByTitleWithAutoCreate($module, $title, $exp = array()){
        if (trim($title) == ''){
            return;
        }

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        $titleQ = escapeForDb($title);

        $tableName = $modulesArr[$module]["tableName"];
        $keyField  = $modulesArr[$module]["keyField"];
        $titleFld  = $modulesArr[$module]["titleField"];
        $titleFld = $this->getIssetParam($exp, 'titleField', $titleFld);

        $extraFldsInSqlCondnArr = $this->getIssetParam($exp, 'extraFldsInSqlCondnArr', array());
        $extraFldsOnCreationArr = $this->getIssetParam($exp, 'extraFldsOnCreationArr', array());

        $appendSQL = '';
        foreach($extraFldsInSqlCondnArr AS $fld => $val){
            $appendSQL .= " AND {$fld} = '{$val}'";
        }
        $whereCond = "{$titleFld} = '{$titleQ}' {$appendSQL}";

        $rec = $fn->getRecordByCondition($tableName, $whereCond);

        if(!is_array($rec)){
            $fa = array();
            $fa[$titleFld] = $title;
            $fa['published'] = 1;
            $fa['creation_date'] = date('Y-m-d H:i:s');

            foreach($extraFldsOnCreationArr AS $fld => $val){
                $fa[$fld] = $val;
            }

            $sql = $dbUtil->getInsertSQLStringFromArray($fa, $tableName);
            $result = $db->sql_query($sql);
            $record_id = $db->sql_nextid();
        } else {
            $record_id = $rec[$keyField];
        }

        return $record_id;
    }


    /**
     *
     */
    function getRecordIdByTitle($module, $title, $exp = array()){
        if (trim($title) == ''){
            return;
        }

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        $titleQ = escapeForDb($title);

        $tableName = $modulesArr[$module]["tableName"];
        $keyField  = $modulesArr[$module]["keyField"];
        $titleFld  = $modulesArr[$module]["titleField"];
        $titleFld = $this->getIssetParam($exp, 'titleField', $titleFld);

        $extraFldsInSqlCondnArr = $this->getIssetParam($exp, 'extraFldsInSqlCondnArr', array());

        $appendSQL = '';
        foreach($extraFldsInSqlCondnArr AS $fld => $val){
            $appendSQL .= " AND {$fld} = '{$val}'";
        }

        $rec = $fn->getRecordByCondition($tableName, "{$titleFld} = '{$titleQ}' {$appendSQL}");

        $record_id = null;
        if(is_array($rec)){
            $record_id = $rec[$keyField];
        }

        return $record_id;
    }

    function getFlaggedRecordCount($module, $exp = array()) {
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');

        $tableName = $modulesArr[$module]['tableName'];
        $keyField  = $modulesArr[$module]['keyField'];

        $SQL = "
        SELECT COUNT(*) AS count
        FROM {$tableName}
        WHERE flag = 1
        ";
        $row = $fn->getRecordBySQL($SQL);

        return $row['count'];
    }

    /**
     *This func is copied from V2.9.5 (Syed June 11th)
     * @return string
     */
    function getRecordDetailLink($room, $fieldName, $fieldValue, $extraParam = array()) {

        $text = $this->getRecordLink($room, $fieldName, $fieldValue, 'detail', $extraParam);

        return $text;
    }

    /**
     *
     * @return string
     */
    function getRecordLink($module, $fieldName, $fieldValue, $action = '', $extraParam = array()) {
        $displayText = $this->getIssetParam($extraParam, 'displayText', $fieldValue);
        $topRm       = $this->getIssetParam($extraParam, 'topRm');
        $target      = $this->getIssetParam($extraParam, 'target', '');
        if ($topRm == '') {
            $topRm = $this->getTopRoomName($module);
        }

        $text = "<a href='index.php?_topRm={$topRm}&module={$module}&_action={$action}" .
                "&{$fieldName}={$fieldValue}' target='{$target}'>{$displayText}</a>";

        return $text;
    }

    /**
     *
     */
    function getSiteIdForWhereCondn($tblPrefix = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tblPrefix = ($tblPrefix != '') ? ".{$tblPrefix}" : '';

        if ($cpCfg['cp.hasMultiUniqueSites']){
            if (CP_SCOPE == 'admin'){
                $site_id = @$_SESSION['cp_site_id'];
            } else {
                $site_id = $cpCfg['cp.site_id'];
            }

            if ($site_id != ''){
                return "site_id = {$tblPrefix}{$site_id}";
            }
        }
    }

    /**
     *
     */
    function addSiteIdToWhereCondn($tblPrefix = '', $appendToQuery = true) {
        $condn = $this->getSiteIdForWhereCondn($tblPrefix);

        if ($condn != '' && $appendToQuery){
            $condn = " AND {$condn}";
        }

        return $condn;
    }

    /**
     *
     */
    function appendSiteIdToCondn($condn, $module= '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        $siteCondn = $this->getSiteIdForWhereCondn();

        if ($module != '' & in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
            $siteCondn = '';
        }

        $condn .= ($condn != '' && $siteCondn != '') ? " AND {$siteCondn}"  : ($siteCondn != '' ? " WHERE {$siteCondn}" : '');

        return $condn;
    }

    function getClassTextFromArr($arr) {
        $arr = array_filter($arr);
        if (count($arr) == 0) {
            return '';
        }
        $class = " class='" . join(' ', $arr) . "'";

        return $class;
    }

    function replaceUrlVars($url) {
        $tv = Zend_Registry::get('tv');

        $searchArr = array(
            '[lang]'
        );
        $replaceArr = array(
            $tv['lang']
        );
        $url = str_replace($searchArr, $replaceArr, $url);

        return $url;
    }

    function replaceLangKeys($text) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        $regex = '/\[[A-Za-z0-9\[\]].*?\]/';
        $matches = array();
        $result = preg_match_all($regex, $text, $matches);
        if (!$result) {
            return $text;
        } else {
            foreach ($matches[0] as $key) {
                $langKey = substr($key, 1, -1);
                $langVal = $ln->gd($langKey);
                if ($langVal == $langKey) {
                    continue;
                }
                $text = str_replace($key, $langVal, $text);
            }
        }

        return $text;
    }

    function getReferrerPageUrl(){
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpLastButOneReferrer = $fn->getSessionParam('cpLastButOneReferrer');
        $cpLastReferrer    = $fn->getSessionParam('cpReferrer');
        $cpReqURI = $cpUrl->getRequestURI();

        $referrerUrl = '';
        if($cpLastReferrer != $cpReqURI){
            $referrerUrl = $cpLastReferrer;
        } else if ($cpLastReferrer == $cpReqURI){
            $referrerUrl = $cpLastButOneReferrer;
        }

        return $referrerUrl;
    }

    function getShortDescription($row) {
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');

        $short_desc = $ln->gfv($row, 'description_short');
        $desc = $ln->gfv($row, 'description');

        if ($short_desc != ''){
            if ($cpUtil->hasAnyHtmlTags($short_desc)){
                $text = $short_desc;
            } else {
                $text = nl2br($short_desc);
            }
        } else {
            $text = $desc;
        }

        return $text;
    }

    function sessionRegenerate() { //for security reasons regenerate the sessions
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $old_sessionid = session_id();
        @session_regenerate_id(true);
        $new_sessionid = session_id();

        if($dbUtil->isTableExists('basket') && $dbUtil->getColumnExists('basket', 'session_id')){
            $fa = array();
            $fa['session_id']    = $new_sessionid;
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'basket');

            $whereCondition = "WHERE session_id = '{$old_sessionid}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'basket', $whereCondition);
            $result = $db->sql_query($SQL);
        }

        if($dbUtil->isTableExists('login_history') && $dbUtil->getColumnExists('login_history', 'session_id')){
            $fa = array();
            $fa['session_id']    = $new_sessionid;
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'login_history');

            $whereCondition = "WHERE session_id = '{$old_sessionid}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'login_history', $whereCondition);
            $result = $db->sql_query($SQL);
        }

    }

    /**
     *
     * @param type $user_name
     */
    function deleteAllRememberMeTokens($user_name){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $table_name = 'remember_me_token';
        $SQL = "
        DELETE
        FROM {$table_name}
        WHERE user_name = '{$user_name}'";
        $result = $db->sql_query($SQL);

        $fn->setCookie("cpWWWRememberMeTokenC", "", time() - 1209600);
    }

    /**
     *
     * @param type $token
     */
    function setCSRFToken($token = ''){
        $cpCSRFToken = ($token != '') ? $token : hash('sha256', uniqid(mt_rand(), true));
        $_SESSION['cpCSRFToken'] = $cpCSRFToken;
        return $cpCSRFToken;
    }

    /**
     *
     * @param type $token
     */
    function setCookie($cookieName, $value, $exp = array()){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expiry   = $fn->getIssetParam($exp, 'expiry', $cpCfg['cp.cookieExpiryInSeconds']);
        $path     = $fn->getIssetParam($exp, 'path', '/');
        $domain   = $fn->getIssetParam($exp, 'domain', NULL);
        $secure   = $fn->getIssetParam($exp, 'secure', $cpCfg['cp.cookieSecure']);
        $httpOnly = $fn->getIssetParam($exp, 'httpOnly', TRUE);

        setcookie($cookieName, $value, $expiry, $path, $domain, $secure, $httpOnly);
    }
    /**
     *
     * @param type $token
     */
    function resetCookie($cookieName,  $exp = array()){
        $exp['expiry'] = time()-1209600;
        $this->setCookie($cookieName, '', $exp);
    }

    /**
     *
     * @return type
     */
    function isValidCSRFToken(){
        $fn = Zend_Registry::get('fn');
        $cpCSRFTokenPost = $fn->getPostParam('cpCSRFToken');
        $cpCSRFTokenSession = $fn->getSessionParam('cpCSRFToken');
        if($cpCSRFTokenPost != $cpCSRFTokenSession){
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @return type
     */
    function getCurrentTimestamp(){
        return date('Y-m-d H:i:s');
    }

    /**
     *
     * @return type
     */
    function getCurrentDate(){
        return date('Y-m-d');
    }

    /**
     *
     */
    function getSWFFile($file_name, $width, $height){

        $text  = "";

        $text .= "
        <object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0\" width=\"{$width}\" height=\"{$height}\">
            <param name=\"movie\" value=\"{$file_name}\">
            <param name=\"quality\" value=\"high\">
            <embed src=\"{$file_name}\" wmode=\"transparent\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"{$width}\" height=\"{$height}\"></embed>
        </object>
        ";

        return $text;
    }

    /**
     * To find the details about the client device
     */
    function wurfl(){
        include_once 'wurfl-php-1.4.1/wurfl_config_standard.php';
        $requestingDevice = $wurflManager->getDeviceForHttpRequest($_SERVER);
        print "
        <li>ID: {$requestingDevice->id} </li>
        <li>Brand Name: {$requestingDevice->getCapability('brand_name')} </li>
        <li>Model Name: {$requestingDevice->getCapability('model_name')} </li>
        <li>Marketing Name: {$requestingDevice->getCapability('marketing_name')} </li>
        <li>Preferred Markup: {$requestingDevice->getCapability('preferred_markup')} </li>
        <li>Resolution Width: {$requestingDevice->getCapability('resolution_width')} </li>
        <li>Resolution Height: {$requestingDevice->getCapability('resolution_height')} </li>
        ";
    }

    /**
     *
     */
     function getTableRowsColumns($result, $rows, $columns, $exp = array()) {
        $db = Zend_Registry::get('db');
        $text = "";
        $trColumns = '';
        $tdColumns = '';
        $trRows = '';
        $tdrows = '';

        $style  = $this->getIssetParam($exp, 'style');

        while ($row = $db->sql_fetchrow($result)) {
            for ($i = 0; $i < count($rows) ; $i++){
                $tdrows .= "<td>{$row[$rows[$i]]}</td>";
            }
            $trRows .="
                <tr>{$tdrows}</tr>
            ";
            $tdrows = '';
        }

        // To get Columns
        for ($i = 0; $i < count($columns) ; $i++){
            $tdColumns .= "<th>{$columns[$i]}</th>";
        }

        $trColumns ="
            <tr>{$tdColumns}</tr>
        ";

        $text ="
        <table class='thinlist {$style}'>
            {$trColumns}
            {$trRows}
        </table>
        ";

        return $text;
    }

    /**
     *
     * @param type $name
     * @param type $content
     */
    function addMetaTag($name, $content = '') {
        $metaTagsArr = Zend_Registry::get('metaTagsArr');

        $metaTagsArr[$name] = array(
            'name' => $name
           ,'content' => $content
        );

        Zend_Registry::set('metaTagsArr', $metaTagsArr);
    }

    /**
     *
     * @param type $var
     * @return string
     */
    function boolToStr($var) {
       if(is_bool($var)) {
          if($var) {
             return 'true';
          } else {
             return 'false';
          }
       } else {
          return $var;
       }
    }

    /**
     *
     * @param type $rowCounter
     * @return type
     */
    function getRowClass($rowCounter) {
        $rowCls = $rowCounter%2 ? 'odd' : 'even';
        return $rowCls;
    }


    /**
     *
     */
    function getBrowserDefaultLang(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $HTTP_ACCEPT_LANGUAGE = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''; // when search bot access
        $lang5Char = substr($HTTP_ACCEPT_LANGUAGE, 0, 5); //zh-tw, zh-hk, en-us
        $lang2Char = substr($HTTP_ACCEPT_LANGUAGE, 0, 2); //zh, en,..

        $lang = '';
        if($lang5Char != '' && array_key_exists($lang5Char, $cpCfg['cp.browserLangCodeToCPLangCode'])){//zh-tw, zh-hk, en-us
            $lang = $cpCfg['cp.browserLangCodeToCPLangCode'][$lang5Char];
        } else if($lang2Char != '' && array_key_exists($lang2Char, $cpCfg['cp.browserLangCodeToCPLangCode'])){//zh, en,..
            $lang = $cpCfg['cp.browserLangCodeToCPLangCode'][$lang2Char];
        }

        return $lang;
    }

    /**
     *
     * @param array $exp
     * @return type
     */
    function getJsonForDropdown($exp = array()) {
        $dbz = Zend_Registry::get('dbz');
        $fn  = Zend_Registry::get('fn');
        $ln  = Zend_Registry::get('ln');

        $srcFld    = $fn->getIssetParam($exp, 'srcFld');
        $srcFldVal = $fn->getIssetParam($exp, 'srcFldVal');
        $table     = $fn->getIssetParam($exp, 'table');
        $keyFld    = $fn->getIssetParam($exp, 'keyFld');
        $titleFld  = $fn->getIssetParam($exp, 'titleFld');
        $condition = $fn->getIssetParam($exp, 'condition');

        $json = array();

        if ($srcFldVal == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $exp = array('condn' => "{$srcFld} = '{$srcValue}'");
        $SQL = "
        SELECT {$keyFld}
              ,{$titleFld}
        FROM {$table}
        WHERE {$srcFld} = ?
        ";
        $stmt = $dbz->query($SQL, $srcFldVal);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $stmt->fetch()){
            $json[] = array("value" => $row[$keyFld], "caption" => $row[$titleFld]);
        }

        return json_encode($json);
    }

    /**
     *
     * @param type $number
     * @return boolean
     */
    function convertNumberToWords($number) {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                '$this->convertNumberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        return $string;
    }

    /**
     * extractEmail("ice.cream.bob@gmail.com"); // outputs ice.cream.bob@gmail.com
     * extractEmail(Ice Cream Bob <ice.cream.bob@gmail.com>"); // outputs ice.cream.bob@gmail.com
     */
    function extractEmail($email_string) {
        preg_match("/<?([^<]+?)@([^>]+?)>?$/", $email_string, $matches);
        return $matches[1] . "@" . $matches[2];
    }

    function printRTextToArray($str) {
        //Initialize arrays
        $keys = array();
        $values = array();
        $output = array();

        //Is it an array?
        if( substr($str, 0, 5) == 'Array' ) {

            //Let's parse it (hopefully it won't clash)
            $array_contents = substr($str, 7, -2);
            $array_contents = str_replace(array(' ', '[', ']', '=>'), array('', '#!#', '#?#', ''), $array_contents);
            $array_fields = explode("#!#", $array_contents);

            //For each array-field, we need to explode on the delimiters I've set and make it look funny.
            for($i = 0; $i < count($array_fields); $i++ ) {

                //First run is glitched, so let's pass on that one.
                if( $i != 0 ) {

                    $bits = explode('#?#', $array_fields[$i]);
                    if( $bits[0] != '' ) $output[$bits[0]] = $bits[1];

                }
            }

            //Return the output.
            return $output;

        } else {

            //Duh, not an array.
            echo 'The given parameter is not an array.';
            return null;
        }
    }

    function getFilesArrOrganized(array $_files, $top = TRUE) {
        $files = array();
        foreach ($_files as $name => $file){
            if ($top) {
                $sub_name = $file['name'];
            } else {
                $sub_name = $name;
            }

            if(is_array($sub_name)){
                foreach(array_keys($sub_name) as $key){
                    $files[$name][$key] = array(
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key],
                    );
                    $files[$name] = $this->getFilesArrOrganized($files[$name], FALSE);
                }
            }else{
                $files[$name] = $file;
            }
        }
        return $files;
    }

    function downloadFile($url, $path) {
        $newfname = $path;
        $file = fopen ($url, "rb");
        if ($file) {
          $newf = fopen ($newfname, "wb");

          if ($newf)
          while(!feof($file)) {
            fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
          }
        }

        if ($file) {
          fclose($file);
        }

        if ($newf) {
          fclose($newf);
        }
    }

    function fbParseSignedRequest($signed_request, $secret) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = base64_url_decode($encoded_sig);
        $data = json_decode(base64_url_decode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            error_log('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }
        return $data;
    }

    function fbBase64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Search a string in an array
     */
    function striposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $key => $needle) {
            $res = stripos($haystack, $needle, $offset);
            if ($res !== false) {
                return array($key => $needle);
            }
        }
        return false;
    }

    function aes_128_decrypt($encrypted_text,$password)
    {
        $iv = str_repeat("\0", 16);
        $plaintext = openssl_decrypt($encrypted_text, 'aes-128-cbc', $password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        return $plaintext === false ? false : rtrim($plaintext, "\0");
    }

    function decrypt($string, $key) {
        $string = base64_decode($string);
        $decryptedString = openssl_decrypt($string, 'des-ede3', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, '');
        return $decryptedString === false ? false : trim($decryptedString);
    }

    /**
     *
     */
    function getRatingValue($rating){
        $cpUtil = Zend_Registry::get('cpUtil');

        $rating = $cpUtil->ceilWithSignificance($rating, 0.5);
        $rating = str_replace('.', '', $rating * 10);
        $rating = $rating > 0 ? $rating : 0;

        return $rating;
    }

    /**
     *
     */
    function validateAccess($module, $action, $id = '', $exp = array()){
        $arr = array();
        $valid = 1;
        $arr['status'] = 1;

        return array('valid' => $valid, 'data' => json_encode($arr));
    }

    function in_array_r($needle, $haystack) {
        $found = false;
        foreach ($haystack as $item) {
        if ($item === $needle) {
                $found = true;
                break;
            } elseif (is_array($item)) {
                $found = $this->in_array_r($needle, $item);
                if($found) {
                    break;
                }
            }
        }
        return $found;
    }

    function array_search_recursive($needle, $haystack) {
        foreach($haystack as $key=>$value) {
            $current_key=$key;
            if($needle===$value OR (is_array($value) && $this->array_search_recursive($needle,$value))) {
                return $current_key;
            }
        }
        return false;
    }
}