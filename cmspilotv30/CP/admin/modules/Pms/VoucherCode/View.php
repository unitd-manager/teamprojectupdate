<?
class CP_Admin_Modules_Pms_VoucherCode_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;
        

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['voucher_code'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['order_id'])}
            {$listObj->getListDataCell($row['voucher_code_id'], 'center')}
            {$listObj->getListRowEnd($row['voucher_code_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Campaign Name', 'v.voucher_code')}
        {$listObj->getListHeaderCell('Amount', 'v.amount')}
        {$listObj->getListHeaderCell('Order ID', 'v.order_id')}
        {$listObj->getListHeaderCell('ID', 'v.voucher_code_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Campaign Name', 'voucher_code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        
        $exp = array('isEditable' => 0);

        $fielset1 = "
        {$formObj->getTBRow('Campaign Name', 'voucher_code', $row['voucher_code'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'], $exp)}
        {$formObj->getTBRow('From Serial No.', 'from_no', $row['from_no'])}
        {$formObj->getTBRow('To Serial No.', 'to_no', $row['to_no'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Voucher Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text ="
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