<?
class CP_Admin_Modules_Ecard_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows       = '';
        $rowCounter = 0;
        
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'contact_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
