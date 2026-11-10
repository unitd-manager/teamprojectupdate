<?
class CP_Admin_Modules_Project_ProjectLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['project_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['project_id'])}
            ";
            
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Code"           , "a.project_code"    )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title"          , "a.title"           )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Company"        , "b.contact_id"      )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Key Contact"    , "b.contact_id"      )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Staff"          , "a.staff_id"        )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Status"         , "a.status"          )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
