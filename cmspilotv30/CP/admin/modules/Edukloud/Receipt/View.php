<?
class CP_Admin_Modules_Edukloud_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['receipt_code'])}
            {$listObj->getListDataCell($row['amount'], 'right')}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['mode_of_payment'])}
            {$listObj->getListDataCell($row['receipt_status'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['order_id'], 'center')}
            {$listObj->getListDataCell($row['receipt_id'], 'center')}
            {$listObj->getListRowEnd($row['receipt_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Receipt Code', 'r.receipt_code')}
        {$listObj->getListHeaderCell('Amount', 'r.amount', 'headerRight')}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Mode of Payment', 'r.mode_of_payment')}
        {$listObj->getListHeaderCell('Status', 'r.receipt_status')}
        {$listObj->getListHeaderCell('Date', 'r.date')}
        {$listObj->getListHeaderCell('Order ID', 'order_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Receipt ID', 'receipt_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $expNoEdit = array('isEditable' => 0);
        
        if ($row['contact_name']){
            $contact = $formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name']);
        } else {
            $contact = $formObj->getTBRow('Name', 'title', $row['title']);
        }

        $receipt_date = $dateUtil->formatDate($row['date'], 'DD MMM YYYY');

        $fielset1 = "
        {$contact}
        {$formObj->getTBRow('Receipt Code', 'receipt_code', $row['receipt_code'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Mode of Payment', 'mode_of_payment', $row['mode_of_payment'])}
        {$formObj->getTBRow('Status', 'receipt_status', $row['receipt_status'])}
        {$formObj->getDateRow('Receipt Date', 'date', $receipt_date)}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Receipt Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'edukloud_receipt', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $receipt_date1 = $fn->getReqParam('receipt_date1');
        $receipt_date2 = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');

        $yearEnd = date('Y') + 10;

        $paymentType = $fn->getValueListSQL('paymentType');

        $text = "
        <td class='dateRange'>
            Receipt Date:
            <input type='text' allowEdit='1' name='receipt_date1' class='fld_date' 
                   id='fld_receipt_date1' value='{$receipt_date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='receipt_date2' class='fld_date' 
                   id='fld_receipt_date2' value='{$receipt_date2}' yearEnd='{$yearEnd}' />
        </td>

        <td>
            <select name='mode_of_payment'>
                <option value=''>Mode of Payment</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $paymentType, $mode_of_payment)}
            </select>
        </td>
        ";        
        
        return $text;
    }
}