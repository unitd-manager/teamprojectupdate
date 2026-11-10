<?
class CP_Admin_Modules_Party_MessageLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $listObj = Zend_Registry::get('listObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['message_date'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['message_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Subject', 'title')}
        {$listObj->getListHeaderCell('Date', 'message_date')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }
}