<?
class CP_Admin_Modules_ManPower_InvoiceLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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
            {$listObj->getListDataCell($row['invoice_sequence'])}
            {$listObj->getListDataCell($row['invoice_type'])}
            {$listObj->getListDataCell($row['invoice_amount'])}
            {$listObj->getListDataCell($row['invoice_due_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['invoice_id'])}
            ";
            
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Invoice" , "a.invoice_sequence")}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Invoice Type" , "a.invoice_type")}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Amount" , "a.invoice_amount")}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Due Date" , "a.invoice_due_date")}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Status" , "a.status")}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
