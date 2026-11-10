<?
class CP_Admin_Modules_Project_Service_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows  = "";
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['service_type'])}
            {$listObj->getListDataCell($row['service_id'] , "center")}
            {$listObj->getListRowEnd($row['service_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell("Title"         , "a.title"         )}
        {$listObj->getListHeaderCell("Service Type"  , "a.service_type"  )}
        {$listObj->getListHeaderCell("ID"            , "a.service_id"    , "headerCenter")}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getDetail($row){

        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$dh->getHeaderRow("Service Details")}
        {$dh->getTBRow("Title"         , "title"         , $row['title']        )}
        {$dh->getTBRow("Service Type"  , "service_type"  , $row['service_type'] )}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row){

        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $sqlCombo = "SELECT value FROM valuelist WHERE key_text = 'serviceType' ORDER BY sort_order";

        $text = "
        {$dh->getHeaderRow("Service Details")}
        {$dh->getTBRow("Title"         , "title"         , $row['title']        )}
        {$dh->getDDRowBySQL($sqlCombo, "Service Type", "service_type", $row['service_type'], "OneField")}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){

        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');

        $sqlCombo = "SELECT value FROM valuelist WHERE key_text = 'serviceType' ORDER BY sort_order";

        $text = " 
        {$dh->getHeaderRow("Service Details")}
        {$dh->getTBRow("Title"       , "title")}
        {$dh->getDDRowBySQL($sqlCombo, "Service Type", "service_type", "", "OneField")}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){

        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = $dh->getRightPanelEmpty();

        return $text;
    }

    //==================================================================//
    function setFields(){

        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------------//
        $fa = &$this->fieldsArray;

        //-----------------------------------------------------------------------//
        $fa['title']                = $fn->getPostParam('title');
        $fa['service_type']         = $fn->getPostParam('service_type');
        /*$fa['description']          = $fn->getPostParam('description');
        $fa['unit']                 = $fn->getPostParam('unit');*/
        
    }

    //==================================================================//
    //==================================================================//
    //========================================================//
    //========================================================//
    //==================================================================//
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    //==================================================================//

}