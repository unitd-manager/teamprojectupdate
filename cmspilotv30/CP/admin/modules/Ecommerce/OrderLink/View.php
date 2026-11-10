<?
class CP_Admin_Modules_Ecommerce_OrderLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $listObj = Zend_Registry::get('listObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['order_amount'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['payment_method'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Order Amount', 'order_amount')}
        {$listObj->getListHeaderCell('Order Date', 'creation_date')}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Payment Method', 'payment_method')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }
}