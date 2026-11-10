<?
class CP_Admin_Modules_Party_PartySetupLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $listObj = Zend_Registry::get('listObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['event_name'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['party_setup_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Event Name', 'event_name')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }
}