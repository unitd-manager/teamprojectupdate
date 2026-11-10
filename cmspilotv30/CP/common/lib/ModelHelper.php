<?
class CP_Common_Lib_ModelHelper
{
    /**
     *
     */
    function getModuleDataArray($clsInst='', $exp = array()){
        $tv          = Zend_Registry::get('tv');
        $fn          = Zend_Registry::get('fn');
        $db          = Zend_Registry::get('db');
        $dbUtil      = Zend_Registry::get('dbUtil');
        $searchVar   = Zend_Registry::get('searchVar');
        $pagerForced = $fn->getIssetParam($exp, 'pagerForced');

        if (is_object($pagerForced)){
            $pager = $pagerForced;
        } else {
            $pager = Zend_Registry::get('pager');
        }

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }

        $module = $clsInst->name;
        $model = $clsInst->model;
        $id = $fn->getReqParam('id');

        if ($tv['lnkRoom'] != '' && $id != ''){
            return;
        }

        $sqlForPager = "getSQLForPager";
        if (!method_exists($model, 'getSQL')) {
            /** for front modules with no sql (ex: contactUs module), consider content as default **/
            if (CP_SCOPE == 'www') {
                $contentObj = getCPModuleObj('webBasic_content');
                $model = $contentObj->model;
            } else {
                return;
            }
        }

        $SQL = $model->getSQL();
        if ($SQL == '') {
            return;
        }

        $modulesArr = Zend_Registry::get('modulesArr');
        $moduleArr = $modulesArr[$module];
        $gotoLastPageByDefault = $moduleArr['gotoLastPageByDefault'];

        $SQL .= $searchVar->getSearchVar($clsInst, 1, '', $exp);

        $total = 0;
        $showRecordCount = $fn->getReqParam('showRecordCount');
        if ($showRecordCount || $moduleArr['showRecordCount']) {
            $showRecordCount = true;
        }
        if ($showRecordCount) {
            if (method_exists($model, $sqlForPager)) {
                $SQLCount  = $model->$sqlForPager();
                $SQLCount .= $searchVar->getSearchVar($clsInst, 0);
                $resCount = $db->sql_query($SQLCount);
                //print "<pre class='sql'>" . $SQLCount . '</pre>';
                $row   = $db->sql_fetchrow($resCount);
                $total = $row[0];

                //$total = 10000;
                $pager->setPagerData($total, $pager->getNumRecordsPerPage(), $gotoLastPageByDefault);

            } else {
                $pager->setPagerData($pager->getTotalRecords($SQL),
                                     $pager->getNumRecordsPerPage('', $module),
                                     $gotoLastPageByDefault);
            }
        } else {
            $total = 10000;
            $pager->setPagerData($total, $pager->getNumRecordsPerPage(), $gotoLastPageByDefault);
        }

        $noLimit = $fn->getIssetParam($exp, 'noLimit', 0);
        $limit = $fn->getIssetParam($exp, 'limit');

        if ($noLimit == 0){
            $SQL = $pager->addLimitToSql($SQL, $module, $limit);
        }

        $result = $db->sql_query($SQL);
       //fb::log($SQL);
       //print "<pre class='sql'>" . $SQL . '</pre>';
       //exit();
        $dataArray = $dbUtil->getResultsetAsArray($result);
        return $dataArray;
    }

    /**
     *
     */
    function setModuleDataArray($clsInst='', $exp = array()){

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }

        $dataArray = $this->getModuleDataArray($clsInst, $exp);
        $model = $clsInst->model;
        $model->dataArray = $dataArray;
    }

    /**
     *
     */
    function getDataArrayByModule($module, $exp = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $pagerForced = includeCPClass('Lib', 'Pager', 'Pager');

        $modObj = getCPModuleObj($module);
        $limit = $fn->getIssetParam($exp, 'limit');

        if ($limit != ''){
            $tv['listLimitOverride'] = $limit;
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
            $dataArray = $this->getModuleDataArray($modObj, $exp);

            $tv['listLimitOverride'] = $limit;
            CP_Common_Lib_Registry::arrayMerge('tv', $tv);
        } else {
            $exp['pagerForced'] = $pagerForced;
            $dataArray = $this->getModuleDataArray($modObj, $exp);
        }

        return $dataArray;
    }

    /**
     *
     */
    function getModuleLinkDataArray($clsInst, $linkRecType, $exp = array()){
        $pager = Zend_Registry::get('pager');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }
        $model = $clsInst->model;

        $searchVar->sqlSearchVar = array();

        $SQL = $sqlMaster->getSQL($clsInst, $linkRecType, $exp);
        $SQL .= $searchVar->getSearchVar($clsInst, 1, $linkRecType, $exp);
        $SQL .= $searchVar->getHavingVar($clsInst, 1, $linkRecType, $exp);

        $sqlForPager = "";
        if (method_exists($model, "getSQLForPager")) {
            $sqlForPager = $model->getSQLForPager($clsInst);
        }

        if ($sqlForPager != ''){
            $SQLCount = $sqlForPager;
            $SQLCount .= $searchVar->getSearchVar($clsInst, 0, $linkRecType);
            $resCount = $db->sql_query($SQLCount);
            $row      = $db->sql_fetchrow($resCount);
            $total    = $row[0];
            $pager->setPagerData($total, $pager->getNumRecordsPerPage());
        } else {
            $pager->setPagerData($pager->getTotalRecords($SQL), $pager->getNumRecordsPerPage());
        }

        $sqlMaster->SQL = $SQL;

        $SQL = $pager->addLimitToSql($SQL);
        $sqlMaster->SQLWithLimit = $SQL;

        //fb::log($SQL);
        //print "<pre class='sql'>" . $SQL . '</pre>';
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;
    }

    /**
     *
     */
    function setModuleLinkDataArray($clsInst, $linkRecType, $exp = array()) {
        $tv = Zend_Registry::get('tv');

        if (!is_object($clsInst)){
            $clsInst = Zend_Registry::get('currentModule');
        }

        $dataArray = $this->getModuleLinkDataArray($clsInst, $linkRecType, $exp);
        $model = $clsInst->model;
        $model->dataArray = $dataArray;
    }

    /**
     *
     */
    function getLinkDataArrayByModule($srcRoom, $lnkRoom, $linkMasterTableID, $exp = array()) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $clsInst = getCPModuleObj($lnkRoom);
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        $limit = $fn->getIssetParam($exp, 'limit', 100);

        $srcObj = getCPModuleObj($srcRoom);
        $funcName = "setLinksArray";
        if (method_exists($srcObj->fns, $funcName)) {
            $srcObj->fns->$funcName($arrayMasterLink);
            Zend_Registry::set('linksArray', $arrayMasterLink->linksArray);
        }

        $tv['listLimitOverride'] = $limit;
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        $linkedIds = $fn->getLinkedIDs($srcRoom, $lnkRoom, $linkMasterTableID);
        Zend_Registry::set('linkedIds', $linkedIds);
        $exp['expForSearchVar']['relationalDataOnly'] = true;
        $dataArray = $this->getModuleLinkDataArray($clsInst, 'linked', $exp);
        Zend_Registry::set('linkedIds', '');

        $tv['listLimitOverride'] = '';
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        return $dataArray;
    }

    /**
     *
     */
    function getWidgetDataArray($clsInst, $widget){
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $dbUtil = Zend_Registry::get('dbUtil');

        $searchVar = $clsInst->model->searchVar;

        $searchVar->sqlSearchVar = array();
        $exp['fileType'] = 'Widget';

        $SQL = $sqlMaster->getSQL($clsInst, '', $exp);
        $SQL .= $searchVar->getSearchVar($clsInst, 1, '', $exp);

        if (isset($clsInst->displayLimit)){
            $SQL .= " limit 0, {$clsInst->displayLimit}";
        }

        $result = $db->sql_query($SQL);
       //fb::log($SQL);
       //print "<pre class='sql'>" . $widget . '</pre>';
       //print "<pre class='sql'>" . $SQL . '</pre>';
        $dataArray = $dbUtil->getResultsetAsArray($result);
        return $dataArray;
    }

    /**
     *
     */
    function setPluginDataArray($clsInst, $plugin){
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $db = Zend_Registry::get('db');
        $searchVar = $clsInst->model->searchVar;
        $dbUtil = Zend_Registry::get('dbUtil');

        $searchVar->sqlSearchVar = array();
        $exp['fileType'] = 'Plugin';

        $SQL = $sqlMaster->getSQL($clsInst, '', $exp);
        $SQL .= $searchVar->getSearchVar($clsInst, 1, '', $exp);
        if (isset($clsInst->displayLimit)){
            $SQL .= " limit 0, {$clsInst->displayLimit}";
        }

        $result = $db->sql_query($SQL);

        //fb::log($SQL);
        //print $SQL;
        $dataArray = $dbUtil->getResultsetAsArray($result);
        return $dataArray;
    }
}
