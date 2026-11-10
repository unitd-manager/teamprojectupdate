<?
class CP_Admin_Modules_Hms_Receipt_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['receipt_code'])}
            {$listObj->getListDataCell($row['billed_to'])}
            {$listObj->getListDataCell($row['amount'], 'right')}
            {$listObj->getListDataCell($row['mode_of_payment'])}
            {$listObj->getListDataCell($row['receipt_status'], 'center')}
            {$listObj->getListDateCell($row['date'], 'center')}
            {$listObj->getListDataCell($row['order_id'], 'center')}
            {$listObj->getListDataCell($row['receipt_id'], 'center')}
            {$listObj->getListRowEnd($row['receipt_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Receipt Code', 'r.receipt_code')}
        {$listObj->getListHeaderCell('Billed To', 'billed_to')}
        {$listObj->getListHeaderCell('Amount', 'r.amount', 'txtRight')}
        {$listObj->getListHeaderCell('Mode of Payment', 'r.mode_of_payment')}
        {$listObj->getListHeaderCell('Status', 'r.receipt_status', 'txtCenter')}
        {$listObj->getListHeaderCell('Date', 'r.date', 'txtCenter')}
        {$listObj->getListHeaderCell('Order ID', 'order_id', 'txtCenter')}
        {$listObj->getListHeaderCell('Receipt ID', 'receipt_id', 'txtCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $_SESSION['selectedInvoiceIds'] = array();

        $rows = '';
        $today = date('Y-m-d');
        
        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.related_invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount_group
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }


        $invoice_amount = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $invoice_amount = $row['invoice_amount'] - $row['discount'];

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']}({$invoice_amount})</div>
                    <div class=''>Paid:{$row['prev_inv_amount_group']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $SQLDues = "
        SELECT i.invoice_id
              ,i.invoice_amount
              ,i.discount
              ,i.invoice_code
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.related_invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE o.patient_visit_id < {$patient_visit_id}
        AND o.patient_information_id = {$patient_information_id}
        AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $resultDues  = $db->sql_query($SQLDues);
        $numRowsDues = $db->sql_numrows($resultDues);

        if($numRowsDues > 0){
            $invoice_amount = '';
            $count = 1;
            while ($rowDues = $db->sql_fetchrow($resultDues)) {
                $invoice_amount = $rowDues['invoice_amount'] - $rowDues['prev_inv_amount'] - $rowDues['discount'] ;
                $invoice_amount = number_format($invoice_amount, 2);
                $rows .= "
                <div class='order_item_type_title'>Other Dues:</div>
                <div class='form-row-wrapper'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='checkbox' name='invoiceCode[]' value='{$rowDues['invoice_code']}' class='invoiceCode'>
                        </div>
                        <div class='float_left'>{$rowDues['invoice_code']}({$invoice_amount})</div>
                        <div class=''>Paid:{$rowDues['prev_inv_amount']}</div>
                    </div>
                </div>
                ";
                $count++;
            }
        }

        $formAction = "index.php?_topRm=finance&module=hms_order&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getEdit($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
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