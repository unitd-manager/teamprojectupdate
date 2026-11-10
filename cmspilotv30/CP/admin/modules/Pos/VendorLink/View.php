<?
class CP_Admin_Modules_Pos_VendorLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['code'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Vendor Code', 'a.code')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Vendor Name', 'a.title')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

}