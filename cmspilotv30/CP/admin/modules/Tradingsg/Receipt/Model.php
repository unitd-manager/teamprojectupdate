<?
class CP_Admin_Modules_Tradingsg_Receipt_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $amount          = $fn->getPostParam('amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $order_id        = $fn->getReqParam('order_id');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($invoiceCodes);

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']         = $amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = date("Y-m-d H:i:s");
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;

        foreach($invoiceCodes AS $invoice_code){
            $SQLInvoice = "
            SELECT *
            FROM `invoice`
            WHERE invoice_code = '{$invoice_code}'
            AND status != 'Cancelled'
            ";
            $resultInvoice  = $db->sql_query($SQLInvoice);
            $invoiceRec     = $db->sql_fetchrow($resultInvoice);
            $invoice_amount = $invoiceRec['invoice_amount'];
            $invoice_id     = $invoiceRec['invoice_id'];

            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id = '{$invoice_id}'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $invoice_amount = $invoice_amount - $rowPaid['prev_sum'];

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
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        //$this->getGenerateReceiptForMedia($receipt_id, $invoiceCodes);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';

        $amount          = $fn->getPostParam('amount');
        $invoiceCodesArr = $fn->getPostParam('invoiceCode', array());

        $validate->resetErrorArray();
        if(count($invoiceCodesArr) == 0){
            $validate->validateData('amount' , 'Please choose the invoice(s) to be paid');
        }
        //==================================================================
        $invoiceCodes = join(",", $invoiceCodesArr);
        $sessionExplode = explode(',', $invoiceCodes);

        $counter = 1;
        $count = count($sessionExplode);

        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        if ($invoiceCodes != ''){
            $SQL = "
                SELECT SUM(invoice_amount) as invoice_sum
                FROM invoice
                WHERE invoice_code IN ({$invoice_code})
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN receipt r ON (r.receipt_id = irh.receipt_id)
            WHERE invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_code IN ({$invoice_code})
                )
            AND r.receipt_status != 'Cancelled'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than the balance amount';
            }
        }

        $validate->validateData('invoiceCode' , 'Please check invoice code');
        $validate->validateData('amount' , 'Please enter the amount');
        $validate->validateData('mode_of_payment' , 'Please select mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getGenerateReceiptForMedia($receipt_id, $invoiceCodes) {
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
        LEFT JOIN (invoice_receipt_history irh) ON (it.invoice_id = irh.invoice_id)
        WHERE irh.receipt_id = {$receipt_id}
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
        $pdf->Cell(20, 20, 'RECEIPT');
        $pdf->Ln(10);

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $receiptRec     = $fn->getRecordRowByID('receipt', 'receipt_id', $receipt_id);
            $invoiceRec     = $fn->getRecordRowByID('invoice', 'invoice_id', $row['invoice_id']);
            $orderItemRec   = $fn->getRecordRowByID('order_item', 'invoice_id', $row['invoice_id']);
            $productRec     = $fn->getRecordRowByID('product', 'product_id', $orderItemRec['record_id']);
            $orderRec       = $fn->getRecordRowByID('order', 'order_id', $invoiceRec['order_id']);
            $quoteRec       = $fn->getRecordRowByID('quote', 'quote_id', $orderRec['quote_id']);
            $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);

            $quote_currency = $quoteRec['currency'];
            $amount_paid    = $receiptRec['amount'];

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

                $receipt_code = $receiptRec['receipt_code'];
                /* Receipt code*/
                $code = 'Receipt No : '. $receipt_code;
                $pdf->SetXY(145, 44);
                $pdf->Cell(50, 20, $code);
                $pdf->Ln(14);

                /* Company Name */
                $pdf->SetX(10);
                $companyName = 'Name: ' . $companyRec['company_name'];
                $pdf->Cell(100, 8, $companyName, 1, 0, 'L', 1);

                /* Invoice Date*/
                $date = $fn->getCPDate($receiptRec['date'], 'dS F Y');
                $receiptDate = 'Receipt Date : '. $date;
                $pdf->SetXY(145, 52);
                $pdf->Cell(50, 20, $receiptDate);
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

        /* Total amount owe */
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

        /* Current Payment Amount */
        $amount_paid = number_format($amount_paid, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Current Payment Amount', 'B', 0, 'R');
        $pdf->Cell(25, 8, $amount_paid, 'LRB', 0, 'R');
        $pdf->Ln(7);

        $total_amount_paid = $this->getTotalAmountPaidForInvoice($invoiceCodes);
        $previous_amount = number_format($total_amount_paid, 2);
        /* Previously Paid Amount */
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Total Paid Amount', 'B', 0, 'R');
        $pdf->Cell(25, 8, $previous_amount, 'LRB', 0, 'R');
        $pdf->Ln(7);

        $total_amount_invoice = $this->getTotalAmountDueForInvoice($invoiceCodes);
        $balance_amount = number_format($total_amount_invoice - $total_amount_paid, 2);

        /* Balance Amount */
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(15, 8, '', 'LB');
        $pdf->Cell(20, 8, '', 'B');
        $pdf->Cell(80, 8, '', 'B');
        $pdf->Cell(10, 8, '', 'B');
        $pdf->Cell(18, 8, '', 'B');
        $pdf->Cell(30, 8, 'Balance to be Paid', 'B', 0, 'R');
        $pdf->Cell(25, 8, $balance_amount, 'LRB', 0, 'R');
        $pdf->Ln(17);

        $invoices_paid = $this->getInvoicesPaid($invoiceCodes);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 20, 'Invoice Codes: ' . $invoices_paid);

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'tradingsg_receipt';
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
    function getTotalAmountPaidForInvoice($invoiceCodes) {
        $db = Zend_Registry::get('db');

        $invoiceCodes = join(',', $invoiceCodes);
        $sql = "
        SELECT SUM(irh.amount) AS total_paid_amount
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code IN ({$invoiceCodes})
        ";
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        return $row['total_paid_amount'];
    }

    /**
     *
     */
    function getTotalAmountDueForInvoice($invoiceCodes) {
        $db = Zend_Registry::get('db');

        $invoiceCodes = join(',', $invoiceCodes);
        $sql = "
        SELECT SUM(invoice_amount) AS total_due_amount
        FROM invoice
        WHERE invoice_code IN ({$invoiceCodes})
        ";
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        return $row['total_due_amount'];
    }

    /**
     *
     */
    function getInvoicesPaid($invoiceCodes) {
        $db = Zend_Registry::get('db');

        $invoiceCodes = join(',', $invoiceCodes);
        $sql = "
        SELECT invoice_code
        FROM invoice
        WHERE invoice_code IN ({$invoiceCodes})
        ";
        $result     = $db->sql_query($sql);

        $rows   = '';
        $append = '';
        $count  = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if ($count > 1) {
                $append = ', ';
            }

            $rows .= $append . $row['invoice_code'];
            $count++;
        }

        return $rows;
    }
}