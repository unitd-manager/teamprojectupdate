<?
class CP_Admin_Modules_Hms_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

        $rows .="
		    {$listObj->getListRowHeader($row, $count)}
		    {$listObj->getGoToDetailText($count, $row['invoice_code'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListDataCell($row['billed_to'])}
		    {$listObj->getListDateCell($row['invoice_date'], 'center')}
		    {$listObj->getListDataCell($row['status'], 'center')}
		    {$listObj->getListDataCell($row['invoice_amount'] ,'right')}
		    {$listObj->getListDataCell($row['order_id'], 'center')}
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$count++;
		}

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice Code', 'invoice_code')}
        {$listObj->getListHeaderCell('Bill To', 'o.bill_type')}
        {$listObj->getListHeaderCell('Billed To', 'billed_to')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date', 'txtCenter')}
        {$listObj->getListHeaderCell('Status', 'i.status', 'txtCenter')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'txtRight')}
        {$listObj->getListHeaderCell('Order Id', 'i.order_id', 'txtCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
       	";

        return $text;
    }

    /**
     *
     */
     function getGenerateInvoiceForm1() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';

        $order_id = $fn->getReqParam('order_id');
        $date     = $fn->getCurrentDate();
        $due_date = date('Y-m-d', strtotime("+14 days"));
        $qty_balance = '';

        $sqlOrderItem = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOrderItem = $db->sql_query($sqlOrderItem);
        while ($rowOI = $db->sql_fetchrow($resultOrderItem)) {
            $sqlQty = "
            SELECT SUM(it.qty) AS qty_invoiced
            FROM invoice_item it
            JOIN invoice i ON (i.invoice_id = it.invoice_id)
            WHERE i.order_id = {$order_id}
             AND it.record_id = {$rowOI['record_id']}
             AND i.status != 'Cancelled'
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $selling_price = $rowOI['unit_price'] * $rowOI['qty'];

            $qty_balance = $rowOI['qty'] - $rowQty['qty_invoiced'];

            $inputRow = '';
            $qtyRow = '';

            if ($rowQty['qty_invoiced'] != $rowOI['qty']) {
                $pfx = $rowOI['order_item_id'] . '_' ;
                $inputRow = "<input class='orderItemId' type='checkbox' name='orderItemId[]' value='{$rowOI['order_item_id']}'>";
                $qtyRow = "<input type='text' value='{$qty_balance}' id='fld_qty' class='text w50' name='{$pfx}qty'>";
            }

            $rows .= "
            <tr orderRowItem[] = {$rowOI['order_item_id']}>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowOI['item_title']}</td>
                <td class='sellingPrice'>{$rowOI['unit_price']}</td>
                <td class=''>{$rowOI['qty']}</td>
                <td class=''>{$qtyRow}</td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$rowQty['qty_invoiced']}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=hms_order&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);

        $cst = '';
        if($orderRec['vat'] != 1){
            $cst ="
            {$formObj->getTBRow('Add CST(%)', 'cst_value')}
            ";
        }

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $date)}
            {$formObj->getDateRow('Due Date', 'invoice_due_date', $due_date)}
            {$formObj->getTARow('Terms', 'invoice_terms', $orderRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'notes', $orderRec['notes'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            {$cst}
            {$formObj->getTBRow('Add Frieght(%)', 'frieght')}
            {$formObj->getTBRow('Add P & F(%)', 'p_f')}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

            <table class='thinlist room-order-table'>
                <thead>
                    <th class='click-all-top'>
                        <a href='#' class='check-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                        </a>
                        <a href='#' class='uncheck-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                        </a>
                    </th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th class=''>Qty (Current Invoice)</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Invoiced)</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getGenerateInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $order_id = $fn->getReqParam('order_id');
        $receipt  = $fn->getReqParam('receipt');
        $expEdit  = array('isEditable' => 0);

        $appendSql = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND o.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT o.*
              ,(SELECT  SUM(oi.unit_price)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type = 'Doctor/Nurse'
                )AS consultation_fees
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
        FROM `order`o
        WHERE o.order_id = {$order_id}
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $order_items_Details = '';

        $SQLOrderItem = "
        SELECT  record_type
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        $Total_Amount = '';
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                        ,(unit_price * qty) AS qty_total
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($numRowsList > 0){
                    $List = '';
                    $List_Amount = '';
                    while($rowList    = $db->sql_fetchrow($resultList)){
                       $List_Amount = $rowList['unit_price'];
                       
                       $disabledInput = '';
                       if($rowOrderItem['record_type'] == 'Inventory'){
                            $List_Amount   = $rowList['qty_total'];
                            $disabledInput = "disabled = ''";
                       }

                       $List_fees_input            = "<input name='list_fees_{$count}' class='order_item_type_value list_fees_{$count}'  value='{$List_Amount}' {$disabledInput}/>";
                       $List_orderItemHidden_input = "<input name='list_orderItemId_{$count}' type='hidden' value='{$rowList['order_item_id']}' />";

                       $List .= " <tr>
                                    <td width='80%'>{$rowList['item_title']}</td>
                                    <td width='20%' class='txtRight'>{$List_fees_input}</td>
                                    {$List_orderItemHidden_input}
                                 <tr>
                                ";
                        $count++;

                        $Total_Amount += $List_Amount;
                    }

                    if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                        $rowOrderItem['record_type'] = 'Consultation Fees';
                    }

                    $order_items_Details .="<div class='order_item_type_title'>{$rowOrderItem['record_type']}</div>
                                            <table class='thinlist InvoiceFormTable'>
                                                {$List}
                                            </table>
                                            ";
                }

            }
        }

        $sub_Total    = number_format($Total_Amount, 2);
        $Total_Amount = $Total_Amount - $row['discount'];
        $Total_Amount_hidden = $Total_Amount;
        $Total_Amount = number_format($Total_Amount, 2);
        $Discount     = "<input name='discount' class='order_item_type_value invoice_discount_amount' value='{$row['discount']}' />";
        $sub_Total    = "<input name='sub_Total' disabled class='order_item_type_value invoice_sub_total_amount' value='{$sub_Total}' />";
        $Total_Amount_input = "<input name='Total_Amount' disabled class='order_item_type_value invoice_total_amount' value='{$Total_Amount}' />";
        $order_items_Details .="<div class='order_item_type_title'></div>
                                <table class='thinlist order_item_type_table'>
                                    <tr>
                                        <th width='75%'>Sub Total</th>
                                        <th width='35%' class='txtRight invoice_subtotal_amount'>{$sub_Total}</th>
                                     <tr>
                                     <tr>
                                        <th width='75%'>Discount</th>
                                        <th width='35%' class='txtRight'>{$Discount}</th>
                                     <tr>
                                     <tr>
                                        <th width='75%'>Invoice Amount</th>
                                        <th width='35%' class='txtRight invoice_total_amount'>{$Total_Amount_input}</th>
                                     <tr>
                                </table>
                                ";

        $Due_items_Details = '';
        if($receipt == 1){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = " AND i.site_id = {$cpSiteIdSession}";
            }

            $_SESSION['selectedInvoiceIds'] = array();
            $SQLDues = "
            SELECT i.invoice_id
                  ,i.invoice_amount
                  ,i.discount
                  ,i.invoice_code
                ,(
                SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                ) as prev_inv_amount
            FROM invoice i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE o.patient_visit_id < {$row['patient_visit_id']}
            AND o.patient_information_id = {$row['patient_information_id']}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
            AND o.order_status != 'Cancelled'
            {$appendSql}
            ";
            $resultDues  = $db->sql_query($SQLDues);
            $numRowsDues = $db->sql_numrows($resultDues);
            $checkboxInvoice = '';
            if($numRowsDues > 0){
                $invoice_amount = '';
                $Due_items_Details = '';
                while ($rowDues = $db->sql_fetchrow($resultDues)) {
                    $invoice_amount = $rowDues['invoice_amount'] - $rowDues['prev_inv_amount'] - $rowDues['discount'] ;
                    $invoice_amount = number_format($invoice_amount, 2);
                    $checkboxInvoice .= "
                    <tr>
                        <td colspan='2'>
                            <div class='form-row-wrapper'>
                                <div class='floatbox'>
                                    <div class='float_left'>
                                        <input type='checkbox' name='invoiceCode[]' value='{$rowDues['invoice_code']}' class='dueInvoiceCode'>
                                    </div>
                                    <div class='float_left'>{$rowDues['invoice_code']}</div>
                                    <div class=''>Amount: {$invoice_amount}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    ";
                }
            }

                $Due_items_Details .="<div class='order_item_type_title'>Other Due Invoice(s):</div>
                                        <table class='thinlist'>
                                                {$checkboxInvoice}
                                            <tr>
                                                <td colspan='2'>
                                                    {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType', 'Cash')}
                                                    {$formObj->getTBRow('', "error_box1", '', $expEdit)}
                                                 </td>
                                            </tr>
                                            <tr class='due_amount_table_invoice due_amount_table_disable'>
                                                <th width='75%'>Other Due Invoice(s) Amount</th>
                                                <th width='35%' class='txtRight invoice_due_amount_total'>
                                                    <input name='due_amount' id='fld_due_amount' value='0' disabled/>
                                                </th>
                                            </tr>
                                            <tr class='due_amount_table_invoice due_amount_table_disable'>
                                                <th width='75%'>Overall Total (Other Due Invoice(s) Amount + Invoice Amount)</th>
                                                <th width='35%' class='txtRight invoice_due_amount_total'>
                                                    <input name='overall_Total_invoice' id='fld_overall_Total_invoice' value='{$Total_Amount}' disabled/>
                                                </th>
                                            </tr>
                                            <tr class='due_amount_table_invoice'>
                                                <th width='75%'>Amount Paid Now</th>
                                                <th width='35%' class='txtRight invoice_due_amount_total'>
                                                    <input name='due_receipt_amount' id='fld_due_receipt_amount' value='0.00'/>
                                                </th>
                                            </tr>
                                            <tr class='due_amount_table_invoice'>
                                                <th width='75%'>Balance</th>
                                                <th width='35%' class='txtRight invoice_due_amount_total'>
                                                    <input name='balance_Total_invoice' id='balance_Total_invoice' value='{$Total_Amount}' disabled/>
                                                </th>
                                            </tr>
                                        </table>
                                        ";
        }else{

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = " AND i.site_id = {$cpSiteIdSession}";
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
                WHERE invHist.invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                ) as prev_inv_amount
            FROM invoice i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE o.patient_visit_id < {$row['patient_visit_id']}
            AND o.patient_information_id = {$row['patient_information_id']}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
            AND o.order_status != 'Cancelled'
            {$appendSql}
            ";
            $resultDues  = $db->sql_query($SQLDues);
            $numRowsDues = $db->sql_numrows($resultDues);

            if($numRowsDues > 0){
                $invoice_amount = '';
                $Total_Dues_Amount = '';
                while ($rowDues = $db->sql_fetchrow($resultDues)) {
                    $invoice_amount = $rowDues['invoice_amount'] - $rowDues['prev_inv_amount'] - $rowDues['discount'] ;
                    $Total_Dues_Amount += $invoice_amount;
                    $invoice_amount = number_format($invoice_amount, 2);
                }
                $Total_Dues_Amount = number_format($Total_Dues_Amount, 2);
                $order_items_Details .="<div class='order_item_type_title'>Other Due Invoice(s):</div>
                                                    <table class='thinlist'>
                                                        <tr>
                                                            <td width='75%'>Total Due Amount</td>
                                                            <td width='25%' class='txtRight totalDueAmountInvoice'><b>{$Total_Dues_Amount}</b></td>
                                                        </tr>
                                                    </table>
                                                    ";
            }

        }

        $formAction = "index.php?_topRm=finance&module=hms_order&_spAction=generateInvoiceFormSubmit&showHTML=0";
        $count = $count-1;
        $text = "
        <form id='portalForm' class='yform columnar invoiceForm generateInvoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box2", '', $expEdit)}
            {$order_items_Details}
            {$Due_items_Details}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' id='total_count' name='total_count' value='{$count}' />
            <input type='hidden' id='receipt' name='receipt' value='{$receipt}' />
            <input type='hidden' name='overall_Total_invoice_hidden' id='overall_Total_invoice_hidden' value='{$Total_Amount_hidden}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';

        $order_id = $fn->getReqParam('order_id');
        $invoice_id = $fn->getReqParam('invoice_id');
        $date     = $fn->getCurrentDate();

        $invoiceRec = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);

        $sqlInvoiceItem = "
        SELECT * FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);
        while ($rowII = $db->sql_fetchrow($resultInvoiceItem)) {
            $sqlQty = "
            SELECT SUM(it.qty) AS qty_invoiced
            FROM invoice_item it
            JOIN invoice i ON (i.invoice_id = it.invoice_id)
            WHERE (i.order_id = {$order_id} AND i.status != 'Cancelled')
              AND it.record_id = {$rowII['record_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $sqlOrderItem = "
            SELECT * FROM order_item
            WHERE order_id = {$order_id}
              AND record_id = {$rowII['record_id']}
            ";
            $resultOrdertem = $db->sql_query($sqlOrderItem);
            $rowOI = $db->sql_fetchrow($resultOrdertem);

            $selling_price = $rowII['unit_price'] * $rowII['qty'];

            $qty_balance = $rowOI['qty'] - $rowQty['qty_invoiced'];
            $qty_invoiced = $rowQty['qty_invoiced'] - $rowII['qty'];

            $inputRow = '';
            //if ($rowQty['qty_invoiced'] != $rowII['qty']) {
                $inputRow = "<input class='invoiceItemId' type='checkbox' name='invoiceItemId[]' value='{$rowII['invoice_item_id']}'>";
            //}

            $rows .= "
            <tr>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowII['item_title']}</td>
                <td class='sellingPrice'>{$rowII['unit_price']}</td>
                <td class=''>{$rowOI['qty']}</td>
                <td class=''><input type='text' value='{$rowII['qty']}' id='fld_qty' class='text w50' name='qty[]'></td>
                <td class='qtyBalance'>{$qty_balance}</td>
                <td class=''>{$qty_invoiced}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=hms_order&_spAction=editInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $classCst = '';
        $classVat = '';
        if($invoiceRec['cst_value'] == 0) {
            $classCst = "cstValue";
        }

        if($invoiceRec['vat_value'] == 0) {
            $classVat = "vatValue";
        }

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'invoice_date', $invoiceRec['invoice_date'])}
            {$formObj->getDateRow('Due Date', 'invoice_due_date', $invoiceRec['invoice_due_date'])}
            {$formObj->getTARow('Terms', 'invoice_terms', $invoiceRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'notes', $invoiceRec['notes'])}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
	        {$formObj->getYesNoRRow('Add CST', 'cst', $invoiceRec['cst'])}
	        <div class='cst_value {$classCst}'>
	            {$formObj->getTBRow('', 'cst_value', $invoiceRec['cst_value'])}
            </div>
            {$formObj->getTBRow('Add Frieght', 'frieght', $invoiceRec['frieght'])}
            {$formObj->getTBRow('Add P & F', 'p_f', $invoiceRec['p_f'])}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>

            <table class='thinlist room-order-table'>
                <thead>
                    <th class='click-all-top'>
                        <a href='#' class='check-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                        </a>
                        <a href='#' class='uncheck-all'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                        </a>
                    </th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th class=''>Qty (Current Invoice)</th>
                    <th>Qty (Balance)</th>
                    <th>Qty (Invoiced)</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='qty_balance' value='{$qty_balance}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];
        $stillToBill   = '';
        $base_value = '';
        $ref_value  = '';

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];
        $vlUrl    = "index.php?module=core_valuelist&_spAction=showValuesInModal&showHTML=0&key_text=";

        $expNotes = array();

        $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD MMM YYYY');
        $subSqlForPercentSum = "
        SELECT SUM(invHist.amount) AS amount_paid
        FROM invoice_receipt_history invHist
        WHERE invHist.invoice_id =  {$row['invoice_id']}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);

        $balance_Amount = $row['invoice_amount'] - $rowSql['amount_paid'];
        $balance_Amount = number_format($balance_Amount, 2);
        $invoiced_Paid_Amount = number_format($rowSql['amount_paid'], 2);

        $order = "<a href='index.php?_topRm=finance&module=hms_order&order_id={$row['order_id']}&_action=edit'>{$row['order_id']}</a>";

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Code', 'invoice_code', $row['invoice_code'])}
        {$formObj->getTBRow('Order Id', 'order_id', $order)}
        {$formObj->getTBRow('Bill To', 'bill_to', $row['bill_type'])}
        {$formObj->getTBRow('Billed To', 'billed_to', $row['billed_to'])}
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invoice_date)}
        {$formObj->getDDRowBySQL('Status', 'status', '', $row['status'])}
        {$formObj->getTBRow('Amount', 'invoice_amount', $row['invoice_amount'])}
        {$formObj->getTBRow('Amount Paid', 'invoice_amount', $invoiced_Paid_Amount)}
        {$formObj->getTBRow('Balance', 'invoice_amount', $balance_Amount)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $count = 1;

        $text = "
        {$this->getPatientVisitPortal($row)}
        {$this->getReceiptPortal($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getPatientVisitPortal($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $count = 1;

        $sqlOi = "
        SELECT DISTINCT pv.patient_visit_id
              ,pv.visit_code
              ,pv.check_up_date
              ,p.first_name
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (pv.patient_information_id = p.patient_information_id)
        LEFT JOIN (`order` o) ON (pv.patient_visit_id = o.patient_visit_id)
        LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
        WHERE i.invoice_id = {$row['invoice_id']}
        ";
        $resultOi = $db->sql_query($sqlOi);
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            $check_up_date = $fn->getCPDate($rowOi['check_up_date'],"d-m-Y");
            $visitUrl = "/admin/index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowOi['patient_visit_id']}";

            $rows .=  "
            <tr>
                <td>{$count}</td>
                <td><a href='{$visitUrl}'><u>{$rowOi['visit_code']}</u></a></td>
                <td>{$check_up_date}</td>
                <td>{$rowOi['first_name']}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <div style='font-weight:bold;'>Patient Visits Linked</div>
        <table class='thinlist room-order-table' border='1'>
            <thead>
                <th>S.No</th>
                <th>Visit Code</th>
                <th>Checkup Date</th>
                <th>Patient Name</th>
            </thead>
            <tbody>
                {$rows}
            <t/body>            
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptPortal($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $count = 1;

        $sqlOi = "
        SELECT DISTINCT irh.invoice_receipt_history_id
              ,irh.amount
              ,r.receipt_code
              ,r.remarks
              ,r.date
              ,r.mode_of_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
        WHERE irh.invoice_id = {$row['invoice_id']}
        ";
        $resultOi = $db->sql_query($sqlOi);
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            $receipt_date = $fn->getCPDate($rowOi['date'],"d-m-Y");

            $rows .=  "
            <tr>
                <td>{$count}</td>
                <td>{$rowOi['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowOi['amount']}</td>
                <td>{$rowOi['mode_of_payment']}</td>
                <td>{$rowOi['remarks']}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <div style='font-weight:bold;margin-top:15px;'>Receipts Linked</div>
        <table class='thinlist room-order-table' border='1'>
            <thead>
                <th>S.No</th>
                <th>Receipt Code</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Mode of Payment</th>
                <th>Remarks</th>
            </thead>
            <tbody>
                {$rows}
            <t/body>            
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $bill_type  = $fn->getReqParam('bill_type');
        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $sqlBillType = "SELECT DISTINCT bill_type FROM `order` WHERE bill_type != '' ORDER BY bill_type ASC";
        $sqlStatus = "SELECT DISTINCT status FROM invoice WHERE status != '' ORDER BY status ASC";
        
        $text = "
        <td>
            <select name='bill_type' class='float_right m5'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $bill_type)}
            </select>
        </td>
        <td>
            <select name='status' class='float_right m5'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td class='dateRange'>
            From Date:
            <input type='text' allowEdit='1' name='start_date' class='fld_date'
            id='fld_start_date' value='{$start_date}' />
            To Date:
            <input type='text' allowEdit='1' name='end_date' class='fld_date'
            id='fld_end_date' value='{$end_date}' />
        </td>
        ";

        return $text;
    }
}