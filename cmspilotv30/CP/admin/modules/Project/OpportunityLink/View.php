<?
class CP_Admin_Modules_Project_OpportunityLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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
            {$listObj->getListDataCell($row['opportunity_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDateCell($row['enquiry_date'])}
            {$listObj->getListDateCell($row['follow_up_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['chance'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['opportunity_id'])}
            ";
            
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Code"           , "a.opportunity_id"  )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title"          , "a.title"           )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Company"        , "b.contact_id"      )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Key Contact"    , "b.contact_id"      )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Enquiry Date"   , "a.enquiry_date"    )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Follow up Date" , "a.follow_up_date"  )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Status"         , "a.status"          )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Chance"         , "a.chance"          )}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
