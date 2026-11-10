<?
class CP_Admin_Modules_Project_ServiceLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['service_type'])}
            {$listObj->getListDataCell($row['service_id'] , "center")}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['service_id'])}
            ";
            
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title"         , "a.title"         )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Service Type"  , "a.service_type"  )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"ID"            , "a.service_id"    , "headerCenter")}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
