<?
class CP_Admin_Modules_Hms_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT i.*
              ,o.bill_type
              ,IF(o.bill_type = 'Company', o.company_name, o.first_name) AS billed_to
              ,c.company_id
              ,c.company_name
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'i';

        $invoice_id   = $fn->getReqParam('invoice_id');
        $bill_type    = $fn->getReqParam('bill_type');
        $status       = $fn->getReqParam('status');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');

        if ($invoice_id != "") {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$invoice_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = '{$tv['record_id']}'";
        } else {

            if ($start_date != '' && $end_date != '') {
                $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
            }

            if ($bill_type != '') {
                $searchVar->sqlSearchVar[] = "o.bill_type = '{$bill_type}'";
            }

            if ($status != '') {
                $searchVar->sqlSearchVar[] = "i.status = '{$status}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.invoice_code   LIKE '%{$tv['keyword']}%'
                 OR i.invoice_amount LIKE '%{$tv['keyword']}%'
                 OR o.company_name LIKE '%{$tv['keyword']}%'
                 OR c.company_name LIKE '%{$tv['keyword']}%'
                 OR o.first_name LIKE '%{$tv['keyword']}%'
                 OR pi.name LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "i.invoice_id DESC";
        }
    }

    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getGenerateInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $date            = $fn->getCurrentDate();
        $due_date        = date('Y-m-d', strtotime("+14 days"));
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $total_count     = $fn->getReqParam('total_count');
        $order_id        = $fn->getReqParam('order_id');
        $discount        = $fn->getPostParam('discount');
        $receipt         = $fn->getReqParam('receipt');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $due_amount      = $fn->getReqParam('due_receipt_amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');

        for ($i= 1; $i <= $total_count; $i++) {
            $list_fees         = $fn->getPostParam("list_fees_"."$i");
            $list_orderItemId  = $fn->getPostParam("list_orderItemId_"."$i");

            if($list_fees != '' && $list_orderItemId != ''){
                $fa1['unit_price']          = $list_fees;
                $fa1['modification_date']   = date('Y-m-d-H-i-s');

                $whereCondition = "WHERE order_item_id = {$list_orderItemId}";
                $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'order_item', $whereCondition);
                $resultSQL =$db->sql_query($updateSQL);
            }
        }

        if($discount != ''){
            $fa2['discount'] = $discount;
            $fa2['modification_date']   = date('Y-m-d-H-i-s');

            $whereCondition = "WHERE order_id = {$order_id}";
            $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa2, 'order', $whereCondition);
            $resultSQL =$db->sql_query($updateSQL);
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLOrderItem = "
        SELECT  record_type
        FROM order_item
        WHERE order_id = {$order_id}
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
                WHERE order_id = {$order_id}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                    $List = '';
                    $List_Amount = '';
                    while($rowList    = $db->sql_fetchrow($resultList)){
                       $List_Amount = $rowList['unit_price'];

                       if($rowOrderItem['record_type'] == 'Inventory'){
                            $List_Amount = $rowList['qty_total'];
                       }

                        $Total_Amount += $List_Amount;
                    }
            }
        }

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $invoice_code    = '';
        $invoice_id_main = '';
        if($Total_Amount > 0){
            //To update invoice code
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' {$appendSql}";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

            $fa = array();
            $fa['invoice_code']     = 'INV - ' . $invoice_code;
            $fa['invoice_amount']   = $Total_Amount;
            $fa['invoice_date']     = $date;
            $fa['invoice_due_date'] = $due_date;

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }

            $fa['order_id']         = $order_id;
            $fa['status']           = 'Due';
            $fa['discount']         = $discount;
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');

            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
            $resultSQL          = $db->sql_query($insertInvoiceSQL);
            $invoice_id         = $db->sql_nextid();

            $SQLOrderItems = "
            SELECT *
            FROM order_item
            WHERE order_id = {$order_id}
            ";
            $resultOrderItems = $db->sql_query($SQLOrderItems);
            $recCount = 0;
            while ($rowOrderItems = $db->sql_fetchrow($resultOrderItems)){

                if ($invoice_id > 0){
                    $fa = array();
                    $fa['invoice_id']          = $invoice_id;
                    $fa['record_id']           = $rowOrderItems['record_id'];
                    $fa['record_type']         = $rowOrderItems['record_type'];
                    $fa['qty']                 = $rowOrderItems['qty'];
                    $fa['unit_price']          = $rowOrderItems['unit_price'];
                    $fa['item_title']          = $rowOrderItems['item_title'];
                    $fa['description']         = $rowOrderItems['description'];
                    $fa['order_item_id']       = $rowOrderItems['order_item_id'];

                    $invoice_item_id = $fn->addRecord($fa, 'invoice_item');
                    $recCount++;
                }
            }

            $invoice_id_main = $invoice_id;

            $SQLUpdate = "
            UPDATE patient_visit SET status = 'Bill Due'
            , order_id = {$order_id}
            WHERE patient_visit_id = {$orderRec['patient_visit_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        //RECEIPT and HISTORY CREATION CODES
        if($receipt == 1){
            $invoice_code_arr = '';
            if($invoice_code != ''){
                $invoice_code_arr   = 'INV - ' . $invoice_code;
            }

            array_unshift($invoiceCodes, $invoice_code_arr);
            $count = count($invoiceCodes);
            
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND site_id = {$cpSiteIdSession}";
            }
            //To update receipt codes
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' {$appendSql}";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

            if($due_amount > 0){
                $Total_Amount = $due_amount;
            }else{
                $Total_Amount = $Total_Amount - $discount;
            }


            $fa = array();
            $fa['amount']          = $Total_Amount;
            $fa['order_id']        = $order_id;
            $fa['receipt_code']    = 'RCPT - ' . $receipt_code;
            $fa['mode_of_payment'] = $mode_of_payment;

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }
            
            $fa['source_order_id'] = $order_id;
            $fa['date']            = date("Y-m-d H:i:s");
            $fa['receipt_status']  = 'Paid';
            $fa['creation_date']   = date("Y-m-d H:i:s");
            $fa['created_by']      = $fn->getSessionParam('userName');

            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            $receipt_amount     = $Total_Amount;
            $invoice_status_due = '';
            $count = 0;

            foreach($invoiceCodes AS $invoice_code){
                $appendSqlInvoice = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlInvoice = " AND site_id = {$cpSiteIdSession}";
                }

                if($invoice_code != ''){
                    $SQLInvoice = "
                    SELECT *
                    FROM `invoice`
                    WHERE invoice_code = '{$invoice_code}'
                    {$appendSqlInvoice}
                    ";
                    $resultInvoice  = $db->sql_query($SQLInvoice);
                    $invoiceRec     = $db->sql_fetchrow($resultInvoice);
                    $invoice_amount = $invoiceRec['invoice_amount'] - $invoiceRec['discount'];
                    $invoice_id     = $invoiceRec['invoice_id'];

                    if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                        continue;
                    }

                    //CHANGED BY SYED 8-3-2014 , TO EXCLUDE CANCEL RECEIPTS
                    $appendSqlPaid1 = '';
                    $appendSqlPaid2 = '';
                    if ($cpCfg['cp.hasMultiUniqueSites']) {
                        $appendSqlPaid1 = " AND site_id = {$cpSiteIdSession}";
                        $appendSqlPaid2 = " AND rec.site_id = {$cpSiteIdSession}";
                    }

                    $SQLPaid = "
                    SELECT SUM(invHist.amount) AS prev_sum
                          ,(
                            SELECT irh.amount
                            FROM receipt r
                            LEFT JOIN (invoice_receipt_history irh) ON ( r.receipt_id = irh.receipt_id )
                            LEFT JOIN (invoice i) ON ( i.invoice_id = irh.related_invoice_id )
                            WHERE r.receipt_status != 'Cancelled'
                            AND irh.related_invoice_id != irh.invoice_id
                            AND i.invoice_id = {$invoice_id}
                            {$appendSqlPaid1}
                            GROUP BY r.receipt_id
                          ) as prev_inv_amount
                    FROM invoice_receipt_history invHist
                    LEFT JOIN (receipt rec) ON (invHist.receipt_id = rec.receipt_id)
                    WHERE invHist.invoice_id =  '{$invoice_id}' and rec.receipt_status = 'Paid'
                    {$appendSqlPaid2}
                    ";
                    $resultPaid = $db->sql_query($SQLPaid);
                    $rowPaid    = $db->sql_fetchrow($resultPaid);

                    $invoice_amount = $invoice_amount - $rowPaid['prev_sum'] - $rowPaid['prev_inv_amount'];

                    $faInv = array();
                    $recpInvAmount = 0;
                    if ($invoice_amount <= $receipt_amount){
                        $recpInvAmount = $invoice_amount;
                        $faInv['status'] = 'Paid';
                    } else if ($invoice_amount > $receipt_amount){
                        $recpInvAmount = $receipt_amount;
                        $faInv['status'] = 'Partial Payment';
                    }

                    $receipt_amount = $receipt_amount - $recpInvAmount;

                    $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

                    //Inserting receipt id in to history table ( one invoice can have multiple receipts)
                    if($invoice_id_main == ''){
                        $invoice_id_main = $invoice_id;
                    }

                    $fa = array();
                    $fa['receipt_id']         = $receipt_id;
                    $fa['invoice_id']         = $invoice_id_main;
                    $fa['amount']             = $recpInvAmount;
                    $fa['related_invoice_id'] = $invoice_id;
                    $fa['creation_date']      = date("Y-m-d H:i:s");
                    $histId = $fn->addRecord($fa, 'invoice_receipt_history');
                }
            }


            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $appendSqlForPercentSum = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlForPercentSum = " AND o.site_id = {$cpSiteIdSession}";
            }

            $subSqlForPercentSum = "
            SELECT o.*
                  ,(SELECT  SUM(oi.unit_price) AS Amount
                    FROM order_item oi
                    WHERE oi.order_id = o.order_id
                    AND oi.record_type != ''
                    )AS order_amount
                  ,(SELECT SUM(inv.invoice_amount)
                    FROM invoice inv
                    WHERE inv.order_id = o.order_id AND inv.status = 'Paid'
                      ) as total_invoice_amount
            FROM `order`o
            WHERE o.order_id = {$order_id}
            {$appendSqlForPercentSum}
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);

            $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
            $order_amount = $rowSql['order_amount'] - $rowSql['discount'];

            //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
            if($order_amount == $total_invoice_amount){
                $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
                $resultUpdate = $db->sql_query($SQLUpdate);

                $SQLPVUpdate = "UPDATE patient_visit SET status = 'Closed' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
                $resultPVUpdate = $db->sql_query($SQLPVUpdate);
            } else {
                $SQLPVUpdate = "UPDATE patient_visit SET status = 'Partial Receipt' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
                $resultPVUpdate = $db->sql_query($SQLPVUpdate);
            }

            $SQLUpdate = "
            UPDATE patient_visit SET status = 'Closed'
            , order_id = {$order_id}
            WHERE patient_visit_id = {$orderRec['patient_visit_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateInvoiceForMedia($invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT it.*
        FROM invoice_item it
        WHERE it.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $count = 0;
        $total_invoice_sum = 0;
        $item_no = 1;

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}

        //=================================== FIRST TABLE ============================= //
        /* Logo of the institution */
        $pdf->Image('images/logo-print.gif', 10, 5, 45);

        /* Institute company address */
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(140,1);
        $pdf->Cell(20, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
        $pdf->SetXY(140,5);
        $pdf->Cell(24, 20, $cpCfg['printTelephoneAndFax']);
        $pdf->SetXY(140,10);
        $pdf->Cell(28, 20, $cpCfg['printEmailAddress']);
        $pdf->SetXY(140,15);
        $pdf->Cell(20, 20, $cpCfg['printWebAddress']);
        $pdf->SetXY(140,20);
        $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
        $pdf->Ln(15);

        $pdf->SetX(100);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 20, 'INVOICE');
        $pdf->Ln(10);

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $invoiceRec     = $fn->getRecordRowByID('invoice', 'invoice_id', $row['invoice_id']);
            $orderItemRec   = $fn->getRecordRowByID('order_item', 'invoice_id', $row['invoice_id']);
            $productRec     = $fn->getRecordRowByID('product', 'product_id', $orderItemRec['record_id']);
            $orderRec       = $fn->getRecordRowByID('order', 'order_id', $invoiceRec['order_id']);
            $quoteRec       = $fn->getRecordRowByID('quote', 'quote_id', $orderRec['quote_id']);
            $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);

            $quote_currency = $quoteRec['currency'];

            if ($count == 0) {
                //=================================== SECOND TABLE ============================= //
                /* Hard coded text of Master and Owner */
                if ($cpCfg['hasMasterAndOwnerTextInInvoicePdf']) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetXY(10, 50);
                    $pdf->SetFillColor(255,255,255);
                    $toText = $cpCfg['printMasterAndOwnerText'];
                    $pdf->Cell(100, 8, $toText, 1, 0, 'L', 1);
                }

                $invoice_code = $invoiceRec['invoice_code'];
                /* Invoice code*/
                $code = 'Invoice No : '. $invoice_code;
                $pdf->SetXY(145, 44);
                $pdf->Cell(50, 20, $code);
                $pdf->Ln(14);

                /* Company Name */
                $pdf->SetX(10);
                $companyName = 'Name: ' . $companyRec['company_name'];
                $pdf->Cell(100, 8, $companyName, 1, 0, 'L', 1);

                /* Invoice Date*/
                $date = $fn->getCPDate($invoiceRec['invoice_date'], 'dS F Y');
                $invoiceDate = 'Invoice Date : '. $date;
                $pdf->SetXY(145, 52);
                $pdf->Cell(50, 20, $invoiceDate);
                $pdf->Ln(14);

                if ($cpCfg['hasCompanyAddressInInvoicePdf']) {
                    $pdf->SetX(10);
                    $pdf->SetFont('Arial', 'B', 10);
                    $companyAddressText = 'Address of the company comes here';
                    $pdf->Cell(70, 8, $companyAddressText, 1, 0, 'L', 1);
                    $pdf->Ln(5);
                }
                $pdf->Ln(10);

                //=================================== THIRD TABLE ============================= //
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(15, 8, "Item No",1,0, 'C', 1);
                $pdf->Cell(20, 8, "Item Code",1,0, 'C', 1);
                $pdf->Cell(80, 8, "Name of the Item",1,0, 'C', 1);
                $pdf->Cell(10, 8, "Qty",1,0, 'C', 1);
                $pdf->Cell(18, 8, "Unit Size",1,0, 'C', 1);
                $pdf->Cell(30, 8, "Unit Price",1,0, 'C', 1);
                $pdf->Cell(25, 8, "Amount",1,0, 'C', 1);
                $pdf->Ln();
            }

            $item_amount_price = $row['qty'] * $row['unit_price'];
            $item_amount_formatted = number_format($item_amount_price, 2);
            $total_invoice_sum += $item_amount_price;

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(15, 8, $item_no, 1, 0, 'C', 1);
            $pdf->Cell(20, 8, $productRec['item_code'], 1, 0, 'L', 1);
            $pdf->Cell(80, 8, $row['item_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(18, 8, $productRec['unit'], 1, 0, 'R', 1);
            $pdf->Cell(30, 8, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $item_amount_formatted, 1, 0, 'R', 1);
            $pdf->Ln(7);

            $count++;
            $item_no++;
        }

        /* SubTotal */
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Sub Total ' . $quote_currency, 'B', 0, 'R');
        $pdf->Cell(25, 8, number_format($total_invoice_sum, 2), 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* GST */
        $gst_amount = 0;
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'ADD: ' . $cpCfg['printTaxName'] . ' ' . $cpCfg['printTaxValue'], 'B', 0, 'R');
        $pdf->Cell(25, 8, $gst_amount, 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* Total */
        $total_amount = number_format($total_invoice_sum + $gst_amount, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Total', 'B', 0, 'R');
        $pdf->Cell(25, 8, $total_amount, 'LRB', 0, 'R');
        $pdf->Ln(7);

        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'tradingin_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateInvoiceFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate->resetErrorArray();

        $receipt            = $fn->getReqParam('receipt');
        $due_receipt_amount = $fn->getReqParam('due_receipt_amount');
        $due_invoice_amount = $fn->getReqParam('overall_Total_invoice_hidden');

        /*if($due_invoice_amount == 0){
            $validate->errorArray['error_box2']['name'] = "error_box2";
            $validate->errorArray['error_box2']['msg']  = "Please Enter invoice amount";
        }*/
        
        if($receipt == 1){
            $validate->validateData('mode_of_payment', 'Please select the mode of payment');
            if($due_receipt_amount <= 0){
                $validate->errorArray['error_box1']['name'] = "error_box1";
                $validate->errorArray['error_box1']['msg']  = "Please Enter the amount to be paid";
            }

            $order_id  = $fn->getReqParam('order_id');
            $discount  = $fn->getPostParam('discount');

            $SQLOrderItem = "
            SELECT  record_type
            FROM order_item
            WHERE order_id = {$order_id}
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
                    WHERE order_id = {$order_id}
                    AND record_type = '{$rowOrderItem['record_type']}'
                    ";
                    $resultList = $db->sql_query($SQLOrderItemList);
                    $numRowsList = $db->sql_numrows($resultList);

                        $List = '';
                        $List_Amount = '';
                        while($rowList    = $db->sql_fetchrow($resultList)){
                           $List_Amount = $rowList['unit_price'];
                           
                           if($rowOrderItem['record_type'] == 'Inventory'){
                                $List_Amount = $rowList['qty_total'];
                           }

                            $Total_Amount += $List_Amount;
                        }
                }
            }

            if($Total_Amount < $discount){
                $validate->errorArray['error_box2']['name'] = "error_box2";
                $validate->errorArray['error_box2']['msg']  = "Please enter the discount amount less or equal to sub total amount";
            }

        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceItemIds     = $fn->getPostParam('invoiceItemId', array());
        $invoice_amount     = $fn->getPostParam('invoice_amount');
        $invoice_date       = $fn->getPostParam('invoice_date');
        $invoice_due_date   = $fn->getPostParam('invoice_due_date');
        $invoice_terms      = $fn->getPostParam('invoice_terms');
        $notes              = $fn->getPostParam('notes');
        $order_id           = $fn->getReqParam('order_id');
        $invoice_id         = $fn->getReqParam('invoice_id');
        $qty_arr            = $fn->getReqParam('qty', array());
        $qty_balance        = $fn->getReqParam('qty_balance');
        $cst        		= $fn->getReqParam('cst');
        $vat        		= $fn->getReqParam('vat');
        $cst_value        	= $fn->getReqParam('cst_value');
        $vat_value        	= $fn->getReqParam('vat_value');
        $frieght        	= $fn->getReqParam('frieght');
        $pf        			= $fn->getReqParam('p_f');


        $fa = array();
        $fa['invoice_amount']   = $invoice_amount;
        $fa['invoice_date']     = $invoice_date;
        $fa['invoice_due_date'] = $invoice_due_date;
        $fa['invoice_terms']    = $invoice_terms;
        $fa['notes']            = $notes;
        $fa['order_id']         = $order_id;
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['modification_date']= date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        $fa['cst']     			= $cst;
        $fa['vat']     			= $vat;
        $fa['cst_value']     	= $cst_value;
        $fa['vat_value']     	= $vat_value;
        $fa['frieght']     		= $frieght;
        $fa['p_f']     			= $pf;

        $totalvalue = '';

        $whereCondition = "WHERE invoice_id = {$invoice_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        $count = count($invoiceItemIds);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $invoice_item_id = $invoiceItemIds[$i];
            $qty = $qty_arr[$i];

            $fa = array();
            $fa['qty']          = $qty;

            $whereCondition = "WHERE invoice_item_id = {$invoice_item_id}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice_item", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);

            $recCount++;
        }

        $sql ="
        SELECT SUM(it.qty * it.unit_price) As amount
        FROM invoice_item it
        WHERE it.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $sub_total = $row['amount'];

        /*
        if($cst == 0 && $vat == 1){
			$gsttaxvalue = $vat_value ;
			$gstvalue = ($sub_total * $gsttaxvalue) / 100;
			$amount = $gstvalue + $sub_total;
		} else if($cst == 1 && $vat == 0){
			$gsttaxvalue = $cst_value ;
			$gstvalue = ($sub_total * $gsttaxvalue) / 100;
			$amount = $gstvalue + $sub_total;
        } else {
			$amount = $row['amount'];
        }
        */

        $totalFrieght = 0;
        if($frieght != ''){
            $totalFrieght = ($sub_total * $frieght) / 100;
        }

        $totalpf = 0;
        if($pf != ''){
            $totalpf = ($sub_total * $pf) / 100;
        }

        if($vat == 1 && $cst == 0){
            $gsttaxvalue = $vat_value;
            $gstvalue = ($sub_total + $totalpf + $totalFrieght) * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + round($sub_total);
        } else if($cst == 1 && $vat == 0){
            $gsttaxvalue = $cst_value;
            $gstvalue = ($sub_total + $totalpf + $totalFrieght) * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + round($sub_total) ;
        }
        else{
            $totalvalue = $sub_total ;
        }

        $totalvalue = $totalvalue + round($totalFrieght) + round($totalpf);
        $totalvalue = round($totalvalue);
        $fa2 = array();
        $fa2['invoice_amount']  = $totalvalue;

        $whereCondition = "
        WHERE invoice_id = {$invoice_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'invoice', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
     function getGenerateFullInvoice() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $order_id  = $fn->getReqParam('order_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $SQLOrderItem = "
        SELECT  record_type
        FROM order_item
        WHERE order_id = {$order_id}
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
                WHERE order_id = {$order_id}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                    $List = '';
                    $List_Amount = '';
                    while($rowList    = $db->sql_fetchrow($resultList)){
                       $List_Amount = $rowList['unit_price'];

                       if($rowOrderItem['record_type'] == 'Inventory'){
                            $List_Amount = $rowList['qty_total'];
                       }

                        $Total_Amount += $List_Amount;
                    }
            }
        }

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $fa = array();
        $fa['invoice_code']     = 'INV - ' . $invoice_code;
        $fa['invoice_amount']   = $Total_Amount;
        $fa['invoice_date']     = date("Y-m-d");
        $fa['invoice_due_date'] = $due_date;
        $fa['order_id']         = $order_id;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }
        $fa['discount']         = $orderRec['discount'];
        $fa['status']           = 'Due';
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        $SQLOrderItems = "
        SELECT *
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOrderItems = $db->sql_query($SQLOrderItems);
        $recCount = 0;
        while ($rowOrderItems = $db->sql_fetchrow($resultOrderItems)){

            if ($invoice_id > 0){
                $fa = array();
                $fa['invoice_id']          = $invoice_id;
                $fa['record_id']           = $rowOrderItems['record_id'];
                $fa['record_type']         = $rowOrderItems['record_type'];
                $fa['qty']                 = $rowOrderItems['qty'];
                $fa['unit_price']          = $rowOrderItems['unit_price'];
                $fa['item_title']          = $rowOrderItems['item_title'];
                $fa['description']         = $rowOrderItems['description'];
                $fa['order_item_id']       = $rowOrderItems['order_item_id'];

                $invoice_item_id = $fn->addRecord($fa, 'invoice_item');
                $recCount++;
            }
        }

        $SQLUpdate = "
        UPDATE patient_visit SET status = 'Invoiced' WHERE patient_visit_id = {$orderRec['patient_visit_id']}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);
    }

}