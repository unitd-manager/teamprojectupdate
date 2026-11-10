<?
class CP_Admin_Modules_Gdj_DiamondLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows    = '';
        $count   = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $row['product_id_rel'] = $row['product_id'];
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $count)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['product_id'])}
            ";
            $count ++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Title', 'p.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Category', 'c.title')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
