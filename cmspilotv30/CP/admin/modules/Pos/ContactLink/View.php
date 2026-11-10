<?
class CP_Admin_Modules_Pos_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['member_no'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'First Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Last Name', 'last_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }
}
