<?
class CP_Admin_Modules_Hms_Order_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $subSqlForPercentSum = "
            SELECT o.*
                  ,(SELECT SUM(invHist.amount) AS prev_sum
                    FROM invoice_receipt_history invHist
                    LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                    LEFT JOIN `invoice` i ON (i.order_id = {$row['order_id']})
                    WHERE invHist.related_invoice_id =  i.invoice_id
                    AND r.receipt_status != 'Cancelled'
                    AND i.status != 'Cancelled'
                    ) as Amount_Paid
                 ,(SELECT SUM(inv.invoice_amount)
                    FROM invoice inv
                    WHERE inv.order_id = o.order_id AND
                    inv.status != 'Cancelled'
                      ) as total_invoice_amount
            FROM `order`o
            WHERE o.order_id = {$row['order_id']}
            {$appendSql}
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);

            if($rowSql['total_invoice_amount'] != ''){
                $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                $balance_Amount = number_format($balance_Amount, 2);
                $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                $total_invoice_amount = number_format($total_invoice_amount, 2);
            }else{
                $total_invoice_amount = $rowSql['total_invoice_amount'];
                $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                $balance_Amount = number_format($balance_Amount, 2);
                $total_invoice_amount = number_format($total_invoice_amount, 2);
            }

            $order_date = $fn->getCPDate($row['order_date'], 'd-m-Y');

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell('')}
            {$listObj->getGoToDetailText($rowCounter, $row['order_code'])}
            {$listObj->getListDataCell($order_date)}
            {$listObj->getListDataCell($row['patient_name'])}
            {$listObj->getListDataCell($row['nric'])}
            {$listObj->getListDataCell($row['billtype'])}
            {$listObj->getListDataCell($row['order_status'])}
            {$listObj->getListDataCell($total_invoice_amount)}
            {$listObj->getListDataCell($invoiced_Paid_Amount)}
            {$listObj->getListDataCell($balance_Amount)}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Image', '')}
        {$listObj->getListHeaderCell('Bill No', 'o.order_code')}
        {$listObj->getListHeaderCell('Date', 'o.order_date')}
        {$listObj->getListHeaderCell('Patient Name', '')}
        {$listObj->getListHeaderCell('NRIC', '')}
        {$listObj->getListHeaderCell('Bill Type', 'billtype')}
        {$listObj->getListHeaderCell('Status', 'o.order_status')}
        {$listObj->getListHeaderCell('Total Amount', '')}
        {$listObj->getListHeaderCell('Amount Paid', '')}
        {$listObj->getListHeaderCell('Balance', '')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    /*function getUpdateCode(){
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT order_id
        FROM `order`
        ";
        $result = $db->sql_query($SQL);
        $count = 1000;

        while ($row = $db->sql_fetchrow($result)) {
            $SQlUpdate="
            UPDATE `order` set order_code = {$count}
            WHERE order_id = {$row['order_id']}
            ";
            $resultUpdate = $db->sql_query($SQlUpdate);

            $count++;
        }
    }*/

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getDateRow('Order Date', 'order_date')}
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        $subSqlForPercentSum = "
        SELECT o.*
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                LEFT JOIN `invoice` i ON (i.order_id = {$row['order_id']})
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
             ,(SELECT SUM(inv.invoice_amount)
                FROM invoice inv
                WHERE inv.order_id = o.order_id AND
                inv.status != 'Cancelled'
                  ) as total_invoice_amount
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
        {$appendSql}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);

        if($rowSql['total_invoice_amount'] != ''){
            $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
            $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $rowSql['total_invoice_amount'];
            $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $actionButtons = '';

        $SQLInvoice = "
        SELECT i.invoice_id
        FROM invoice i
        WHERE i.order_id = {$row['order_id']}
        AND i.status != 'Cancelled'
        ";
        $resultInvoice = $db->sql_query($SQLInvoice);
        $numRowsInvoice = $db->sql_numrows($resultInvoice);

        if($numRowsInvoice == 0 && $row['order_status'] != 'Cancelled'){
            $formActionInvoice = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id={$row['order_id']}&showHTML=0";

            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$formActionInvoice}' id='generateInvoice'>CREATE DETAIL INVOICE</a>
            </div>
            ";


            $formActionInvoice = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id={$row['order_id']}&showHTML=0";

            $actionButtons .="
            <div class='float_right button mb5'>
                <a id='generateFullInvoice' order_id = {$row['order_id']}>CREATE INVOICE</a>
            </div>
            ";

        }


        if($numRowsInvoice > 0){
            $formActionReceipt = "index.php?module=hms_order&_spAction=generateReceiptForm&order_id={$row['order_id']}&patient_information_id={$row['patient_information_id']}&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
        }

        $Patient_visit_link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";

        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$Patient_visit_link}'>Goto Patient Visit</a>
        </div>
        ";

        $print ="
        <div class='floatbox actionBtnsDetail'>
            <div class='orderRightpanelButtons floatbox'>
                {$actionButtons}
            </div>
        </div>
        ";

        $text = "
        {$print}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Order Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Bill No</th>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>NRIC</th>
                                <th>Status</th>
                                <th class='txtRight'>Amount Paid</th>
                                <th class='txtRight'>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td>{$row['order_code']}</td>
                                <td>{$row['order_date']}</td>
                                <td>{$row['patient_name']}</td>
                                <td>{$row['nric']}</td>
                                <td>{$row['order_status']}</td>
                                <td class='txtRight'>{$invoiced_Paid_Amount}</td>
                                <td class='txtRight'>{$balance_Amount}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Customer Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div class='orderEdit'>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Phone</th>
                                <th>Office Address</th>
                                <th>Street Address</th>
                                <th>District / Town</th>
                                <th>State / Zip</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$row['company_name']}</td>
                                <td>{$row['cust_phone']}</td>
                                <td>{$row['cust_address1']}</td>
                                <td>{$row['cust_address2']}</td>
                                <td>{$row['cust_address_city']}</td>
                                <td>{$row['cust_address_state']}</td>
                                <td>{$row['cust_address_country_code']}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $order_date1                    = $fn->getReqParam('order_date_1');
        $order_date2                    = $fn->getReqParam('order_date_2');
        $order_status                   = $fn->getReqParam('order_status');
        $shipment_status                = $fn->getReqParam('shipment_status');
        $shipping_address_country_code  = $fn->getReqParam('shipping_address_country_code');

        $billType   = $fn->getReqParam('bill_type');
        $sqlBillType    = $fn->getValueListSQL('billType');

        $text = "
        <td>
            <select name='bill_type'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $billType)}
           </select>
        </td>
        <td>
            {$formObj->getDateRangeRow('Order Date:', 'order_date', $order_date1, $order_date2)}
        </td>

        <!--<td class='fieldValue'>
            <select name='shipping_address_country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $fn->getGeoCountrySQL(), $shipping_address_country_code)}
            </select>
        </td>-->

        ";

        /*
        <td class='fieldValue'>
            <select name='order_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.tradingin.order.statusArr'], $order_status)}
            </select>
        </td>*/

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $links ='';

        $links .= "<div id='orderInvoicePortal'>{$this->getInvoicePortalDisplay($row['order_id'])}</div>";

        $links .= "<div id='orderReceiptPortal'>{$this->getReceiptPortalDisplay($row['order_id'])}</div>";

        $summaryTableOrder = $this->getSummaryInOrder($row);

        $text = "
        {$summaryTableOrder}
        {$links}
        ";

        return $text;
    }

    /**
    **/

    function getSummaryInOrder ($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT o.*
              ,(SELECT SUM(round((oi.unit_price * oi.qty),2))
               FROM order_item oi
               WHERE oi.order_id = {$row['order_id']}
               ) AS order_amount
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
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
        WHERE o.order_id = {$row['order_id']}
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        $order_items_Details = '';

        $Lab = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        $Sub_Total = 0;
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                    $rowOrderItem['record_type'] = 'Consultation Fees';
                }


                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount'] = $rowOrderItem['QTY_AMOUNT'];
                }

                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['record_type']}</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        if($rowOrderItem['record_type'] != 'Consultation Fees'){
                            $Lab .= "<li>{$rowList['item_title']}</li>";
                        }
                    }
                }

                $Lab .="</ol></td>
                                <td class='txtRight'>{$rowOrderItem['Amount']}</td>
                            </tr>";

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }
        }

        $order_items_Details .="{$Lab}";
        $total_amount = number_format($Sub_Total - $row['discount'], 2);
        $Sub_Total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);

        $rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Bill Summary</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tr>
                            <th>Description</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$order_items_Details}
                        <tr>
                            <th>Sub Total</th>
                            <th class='txtRight'>{$Sub_Total}</th>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <th class='txtRight'>{$discount}</th>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <th class='txtRight'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
     */
    function getPrintInvoiceRecord() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND i.site_id = {$cpSiteIdSession}";
        }
        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_po_code
                ,o.shipping_address1
                ,o.shipping_address_area
                ,o.shipping_address_city
                ,o.shipping_address_country_code
                ,o.shipping_address_po_code
                ,o.shipping_phone
                ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                ,o.order_id
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.status
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 2) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 2) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE i.invoice_id = '{$invoice_code}'
        {$appendSql}
        ORDER BY ini.invoice_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        if ($company['status'] == 'Cancelled') {
            /* Watermark code start for Cancelled */
            $ImageW = 130; //WaterMark Size
            $ImageH = 150;

            $myPageWidth = $pdf->getPageWidth();
            $myPageHeight = $pdf->getPageHeight();
            $myX = ( $myPageWidth / 2 ) - 60;  //210 WaterMark Positioning
            $myY = ( $myPageHeight / 2 ) - 95; //297

            $pdf->SetAlpha(0.40); //opacity of bg image

            $bg_image = $cpCfg['cp.localPath']."images/cancelled.jpg";
            //$bg_image = $pdf->Image('images/logo_bg.jpg');
            //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->Image($bg_image, $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
            $pdf->SetAlpha(1);
            /* Watermark code end for Cancelled */
        }

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $invoice_code = substr($company['invoice_code'], 2);

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="62%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.strtoupper($company['patient_name']).'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.strtoupper($company['shipping_address1']).'</span><br/>
                            <span>'.strtoupper($company['shipping_address_city']).', '.strtoupper($company['shipping_address_country_code']).' - '.$company['shipping_address_po_code'].'.</span>
                        </td>
                        <td width="38%" style="line-height:20px;"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$invoice_date.'</span><br/>
                            <span>Invoice Code : '.$company['invoice_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';


        $tbl3 ='<table border="1" width="100%" cellpadding="4" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="70%">DESCRIPTION</th>
                            <th width="20%" style="text-align:right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        $discount = '';
        $Total_Amount = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$company['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        if($numRowsOrderItem > 0){
            $count = 1;
            $Sub_Total = '';
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$company['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                        $rowOrderItem['record_type'] = 'Consultation Fees';
                }

                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount']      = $rowOrderItem['QTY_AMOUNT'];
                }

                $tbl3 = $tbl3.'<tr>
                                    <td width="10%">'.$count.'</td>
                                    <td width="70%">'.$rowOrderItem['record_type'].':
                                    <ol>
                               ';


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        $tbl3 = $tbl3.'<li>'.$rowList['item_title'].'</li>';
                    }
                }

                $tbl3 = $tbl3.'</ol></td>
                                    <td width="20%" style="text-align:right;">'.$rowOrderItem['Amount'].'</td>
                                </tr>';

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }

            $Total_Amount = $Sub_Total - $company['discount'];
            $Sub_Total    = number_format($Sub_Total, 2);
            $discount     = number_format($company['discount'], 2);
            $Total_Amount = number_format($Total_Amount, 2);

            $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="text-align:right;">SUB TOTAL</td>
                                <td style="text-align:right;">'.$Sub_Total.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;">DISCOUNT</td>
                                <td style="text-align:right;">'.$discount.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;">TOTAL AMOUNT</td>
                                <td style="text-align:right;">'.$Total_Amount.'</td>
                            </tr>
            ';
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     */
    function getInvoicePortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Invoice(s)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$this->getInvoicePortalDisplayDetail($order_id)}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getInvoicePortalDisplayDetail($order_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $sqlAppend = "";

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        ORDER BY i.invoice_id
        ";

        $result   = $db->sql_query($SQL);
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = '';
        $add_registration_fee = '';
        $invoice_hist_amount  = '';

        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $gstvalue = '';
            $gsttaxvalue = '';
            $pfvalue = '';
            $frieghtValue = '';
            $total = 0;
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);


            $urlPrint  = "index.php?_topRm=finance&module=hms_order&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_id']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] != 'Cancelled'){
                $total += $rowInvoice['invoice_amount'];
            }

            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";
                $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><img src='{$cancel_image}' class='icon'></a>";
            }

            $highlight = '';
            if ($rowInvoice['status'] == 'Cancelled') {
                $highlight = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');

            if($total > 0){
                $total = $total - $rowInvoice['discount'];
            }

            $totalvalueRounded = number_format($total,2);

            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";

            $rows .= "
            <tr>
                <td>{$rowInvoice['invoice_code']}</td>
                <td>{$invoice_date}</td>
                <td>{$rowInvoice['status']}</td>
                <td align='right'>{$totalvalueRounded}</td>
                <td><a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a></td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
                ";
            }


        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Invoice Date</th>
        <th>Status</th>
        <th class='txtRight'>Invoice Amount</th>
        <th>Print Invoice</th>
        <th>Cancel Invoice</th>
        </tr>
        ";

        $invoice_count = $fn->getRecordCount('invoice', "order_id = '{$order_id}' AND status != 'Cancelled'");

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
            {$rowsPvt}
        </table>
        <input type='hidden' id='fld_invoice_count' value='{$invoice_count}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptPortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = "";
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $order_id);

        $SQL = "
        SELECT r.receipt_id
              ,r.receipt_status
              ,r.receipt_code
              ,r.date
              ,r.mode_of_payment
              ,r.amount
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$order_id}
              {$sqlAppend}
        GROUP BY r.receipt_id
        UNION
        SELECT r.receipt_id
              ,r.receipt_status
              ,r.receipt_code
              ,r.date
              ,r.mode_of_payment
              ,irh.amount
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON ( r.receipt_id = irh.receipt_id )
        LEFT JOIN (invoice i) ON ( i.invoice_id = irh.related_invoice_id )
        WHERE r.receipt_status != 'Cancelled'
        AND irh.related_invoice_id != irh.invoice_id
        AND r.order_id = {$order_id}
        AND i.invoice_id IN(
            SELECT invoice_id
            FROM invoice
            WHERE order_id = {$order_id}
            AND i.status !='Cancelled'
        )
        {$sqlAppend}
        GROUP BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=hms_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";
            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $highlight = '';
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' order_id =
                '{$order_id}' receipt_code='{$rowReceipt['receipt_code']}'><img src='{$cancel_image}' class='icon'></a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $highlight = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td><a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a></td>
                <td class='{$highlight}'>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Code</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Print Receipt</th>
            <th>Cancel Receipt</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$order_id}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Receipt(s)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <form id='orderItemPrint' class='' method='post'
                    action='{$formAction}'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                        </table>
                        <input type='hidden' name='order_id' value='{$order_id}' />
                        <input type='hidden' name='receipt_id' value='{$receiptRec['receipt_id']}' />
                    </form>
                </div>
            </div>
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getPrintReceipt1() {
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
        $pdf->SetFont('Arial','',11);
        /*
        This fucntions requires
        1.total invoice amount for thie receipt
        2.Amount already paid for this invoice
        3. Amount Paid now
        4. Balance to be calculated.
        */

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id = $fn->getReqParam('order_id');

        //$receiptRec     = $fn->getRecordRowByID('receipt', 'receipt_code', $receipt_code);

        /*$SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_code = {$receipt_code}
        ";
        $result = $db->sql_query($SQL);*/

        $SQL = "
        SELECT c.company_name
              ,c.address_flat
              ,c.address_street
              ,c.address_town
              ,c.address_state
              ,c.address_country
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_town
              ,c.billing_address_state
              ,c.billing_address_country
              ,c.fax
              ,c.phone
              ,o.shipping_address1
              ,o.shipping_address_area
              ,o.shipping_address_city
              ,o.shipping_address_country_code
              ,o.shipping_address_po_code
              ,o.shipping_phone
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,o.order_id
              ,i.creation_date
              ,i.invoice_id AS invoice_id_main
              ,i.invoice_code
              ,i.invoice_amount
              ,r.receipt_id
              ,r.amount AS receipt_amount
              ,r.receipt_code
              ,r.mode_of_payment
              ,r.remarks
              ,r.creation_date AS receipt_date
        FROM receipt r
        LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        WHERE r.receipt_code = '{$receipt_code}'
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
            $pdf->Output();
            return;
        }

        $previous_paid_amount = '';
        $total_amount = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt


        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {

            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/HMS Logo.png',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->SetXY(10,25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['receipt_date'], 'd-m-Y');

                /* Company address */
                //Address to be got from settings
                /*$pdf->SetXY(130,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(130,5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(130,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(130,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 30);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->SetXY(130,35);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);
                $pdf->Ln(5);
                /*$pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);*/

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 40);
                $pdf->Cell(21, 20, "RECEIPT", 0, 0, 'C');
                $pdf->Ln(20);

                /* Company Details*/
                $billingAddressFlat = '';
                $billingAddressStreet = '';
                $billingAddressTown = '';
                $billingAddressState = '';
                $billingAddressCountry = '';

                if ($row['billing_address_flat'] != ''
                 || $row['billing_address_street'] != ''
                 || $row['billing_address_town'] != ''
                 || $row['billing_address_state'] != ''
                 || $row['billing_address_country'] != '')
                {
                    $billingAddressFlat     = $row['billing_address_flat'];
                    $billingAddressStreet   = $row['billing_address_street'];
                    $billingAddressTown     = $row['billing_address_town'];
                    $billingAddressState    = $row['billing_address_state'];
                    $billingAddressCountry  = $row['billing_address_country'];
                } else {
                    $billingAddressFlat     = $row['shipping_address1'];
                    $billingAddressStreet   = $row['shipping_address_area'];
                    $billingAddressTown     = $row['shipping_address_city'];
                    $billingAddressState    = $row['shipping_address_country_code'];
                    $billingAddressCountry  = $row['shipping_address_po_code'];
                }

                /* Address of the Company */
                $pdf->SetXY(10, 50);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 63, 80, 30, 'D');
                $pdf->SetXY(10, 58);
                $pdf->Cell(50, 20, 'Patient Name: '.$row['patient_name']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(50, 20, 'Address:');
                $pdf->SetXY(10, 70);
                $pdf->Cell(50, 20, $billingAddressFlat);
                $pdf->SetXY(10, 75);
                $pdf->Cell(50, 20, $billingAddressTown.','.$billingAddressState . ' - ' . $billingAddressCountry);
                $pdf->Ln(20);

                /* Recepit code and date */
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetXY(135, 50);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(135);
                $date = $fn->getCPDate($row['receipt_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date       : ");
                $pdf->SetXY(165, 55);
                $pdf->Cell(50, 20, $date);
                $pdf->Ln(45);

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(135,8,"Description",1,0, 'L', 1);
                $pdf->Cell(55,8,"Amount",1,0, 'R', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $count++;
            $lineItemNumber++;

           /*This sql used to find the previous amount paid for the invoice */
            $sqlPreviousPayment = "
            SELECT SUM(irhist.amount) AS total_amount_paid
            FROM invoice_receipt_history irhist
            LEFT JOIN receipt r ON (irhist.receipt_id = r.receipt_id)
            WHERE irhist.invoice_id = {$row['invoice_id_main']}
              AND irhist.receipt_id != {$row['receipt_id']}
              AND r.receipt_status != 'Cancelled'
            ";
            $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
            $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
            $previous_paid_amount += $rowPreviousPayment['total_amount_paid'];

            $sqlInvoiceAmount = "
            SELECT i.invoice_amount
                   ,i.discount
            FROM invoice i
            WHERE i.invoice_id = {$row['invoice_id_main']}
            ";
            $resultInvAmount = $db->sql_query($sqlInvoiceAmount);
            $rowInvoiceAmount= $db->sql_fetchrow($resultInvAmount);

            $total_amount += $rowInvoiceAmount['invoice_amount'] - $rowInvoiceAmount['discount'];

            $invoice_code = $row['invoice_code'];
            $mode_of_payment = $row['mode_of_payment'];
            $remarks = $row['remarks'];
            $receipt_amount = $row['receipt_amount'];
        }

            $balance_due          = $total_amount - $previous_paid_amount - $receipt_amount;

            /* Total amount to be paid */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $label = 'Invoice Amount (Invoice Code : ' . $invoice_code . ')';
            $pdf->Cell(135, 8, $label, 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format(round($total_amount), 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total amount paid earlier */
            $pdf->Cell(135, 8,'Amount already Paid ', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format(round($previous_paid_amount), 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total amount paid */
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(135, 8,'Amount Received Now', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format(round($receipt_amount), 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total balance amount to be paid */
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(135, 8,'Balance Amount to be Paid', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format(round($balance_due), 2), 1, 0, 'R');
            $pdf->Ln(15);

            /* Cheque Details */
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(20, 8, 'Payment Method');
            $pdf->Ln(5);

            $pdf->SetFont('Arial','',8);
            $pdf->Cell(130, 8, $mode_of_payment);
            $pdf->Ln(10);

            /* Notes */
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(150, 8, 'Notes:');
            $pdf->Ln(4);

            $pdf->SetFont('Arial','',8);
            $pdf->Cell(150, 8, $remarks);
            $pdf->Ln();

            /*$pdf->SetFillColor(255,255,255);
            $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(80, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(37, 8, $row['part_number'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(19, 8, number_format(round($row['unit_price']),2), 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format(round($row['amount']),2), 1, 0, 'R', 1);
            $pdf->Ln();*/

            /*if($row['vat'] == 1 && $row['cst'] == 0){
                $printTaxName = $cpCfg['printTaxName'] ;
                $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
                $gstvalue = round($row['sub_total']) * $gsttaxvalue / 100;
                $totalvalue = $gstvalue + round($row['sub_total']);
            } else if($row['cst'] == 1 && $row['vat'] == 0){
                $printTaxName = $cpCfg['printCstText'] ;
                $gsttaxvalue = $cpCfg['printCstinInvoice'] ;
                $gstvalue = round($row['sub_total']) * $gsttaxvalue / 100;
                $totalvalue = $gstvalue + round($row['sub_total']) ;
            } */

            /*$pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "SUB TOTAL {$currency}", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

            $printTaxName = $cpCfg['printTaxName'] ;

            $pdf->Cell(166, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format(round($gstvalue), 2), 1, 0, 'R', 1);
            $pdf->Ln();

            $totalvalueRounded = round($totalvalue);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($totalvalueRounded, 2), 1, 0, 'R', 1);
            $pdf->Ln(20);

            $pdf->Cell(30,15,"(Note : The above receipt is paid for the invoice (INV-1003, INV-1004))",0,0, 'L', 1);
            $pdf->Ln(10);*/

            /* Creation of media record of the invoice */
            //$file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
            //$outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

            //$outputFileName = $outputPath . '/' . $file_name;
            //$pdf->Output($outputFileName , "F");
            $pdf->Cell(195,8, "(This is computer generated document, and does not require a signature)", 0, 0, 'L', 1);
            $pdf->Output();

    }

    /**
     *
     */
    function getPrintReceipt() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id = $fn->getReqParam('order_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlCheck = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlCheck = " AND r.site_id = {$cpSiteIdSession}";
        }
        $SQLCheck = "
        SELECT r.order_id
        FROM receipt r
        WHERE r.receipt_code = '{$receipt_code}'
        {$appendSqlCheck}
        ";
        $resultCheck = $db->sql_query($SQLCheck);
        $rowCheck    = $db->sql_fetchrow($resultCheck);

        if($rowCheck['order_id'] == $order_id){
            $SQL = "
            SELECT ini.*
                  ,c.company_name
                  ,o.cust_address1
                  ,o.cust_address2
                  ,o.cust_address_po_code
                  ,o.shipping_address1
                  ,o.shipping_address_area
                  ,o.shipping_address_city
                  ,o.shipping_address_country_code
                  ,o.shipping_address_po_code
                  ,o.shipping_phone
                  ,o.patient_visit_id
                  ,o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                  ,o.order_id
                  ,i.discount
                  ,i.creation_date
                  ,i.invoice_id AS invoice_id_main
                  ,i.invoice_code
                  ,i.invoice_amount
                  ,r.receipt_id
                  ,r.amount AS receipt_amount
                  ,r.receipt_code
                  ,r.mode_of_payment
                  ,r.remarks
                  ,r.creation_date AS receipt_date
                  ,r.receipt_status
            FROM receipt r
            LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN invoice_item ini ON (i.invoice_id = ini.invoice_id)
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN company c ON (c.company_id = o.company_id)
            WHERE r.receipt_code = '{$receipt_code}'
            AND r.order_id = {$order_id}
            {$appendSqlCheck}
            ";
        }else{
            $SQL = "
            SELECT ini.*
                  ,c.company_name
                  ,o.cust_address1
                  ,o.cust_address2
                  ,o.cust_address_po_code
                  ,o.shipping_address1
                  ,o.shipping_address_area
                  ,o.shipping_address_city
                  ,o.shipping_address_country_code
                  ,o.shipping_address_po_code
                  ,o.shipping_phone
                  ,o.patient_visit_id
                  ,o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                  ,o.order_id
                  ,i.discount
                  ,i.creation_date
                  ,i.invoice_id AS invoice_id_main
                  ,i.invoice_code
                  ,i.invoice_amount
                  ,r.receipt_id
                  ,r.amount AS receipt_amount
                  ,r.receipt_code
                  ,r.mode_of_payment
                  ,r.remarks
                  ,r.creation_date AS receipt_date
                  ,irh.amount AS receipt_amount
                  ,r.receipt_status
            FROM receipt r
            LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN invoice i ON (i.invoice_id = irh.related_invoice_id)
            LEFT JOIN invoice_item ini ON (i.invoice_id = ini.invoice_id)
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN company c ON (c.company_id = o.company_id)
            WHERE r.receipt_code = '{$receipt_code}'
            AND r.receipt_status != 'Cancelled'
            AND irh.invoice_id != irh.related_invoice_id
            {$appendSqlCheck}
            ";
        }

        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        if ($company['receipt_status'] == 'Cancelled') {
            /* Watermark code start for Cancelled */
            $ImageW = 130; //WaterMark Size
            $ImageH = 150;

            $myPageWidth = $pdf->getPageWidth();
            $myPageHeight = $pdf->getPageHeight();
            $myX = ( $myPageWidth / 2 ) - 60;  //210 WaterMark Positioning
            $myY = ( $myPageHeight / 2 ) - 95; //297

            $pdf->SetAlpha(0.40); //opacity of bg image

            $bg_image = $cpCfg['cp.localPath']."images/cancelled.jpg";
            //$bg_image = $pdf->Image('images/logo_bg.jpg');
            //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->Image($bg_image, $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
            $pdf->SetAlpha(1);
            /* Watermark code end for Cancelled */
        }

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $receipt_date = $fn->getCPDate($company['receipt_date'], 'd-m-Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">RECEIPT</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="70%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.strtoupper($company['patient_name']).'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.strtoupper($company['shipping_address1']).'</span><br/>
                            <span>'.strtoupper($company['shipping_address_city']).', '.strtoupper($company['shipping_address_country_code']).' - '.$company['shipping_address_po_code'].'.</span>
                        </td>
                        <td width="30%" style="line-height:20px;"><br/>
                            <span>DATE : '.$receipt_date.'</span><br/>
                            <span>Code : '.$company['receipt_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';


        $tbl3 ='<table border="1" width="100%" cellpadding="4" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="70%">DESCRIPTION</th>
                            <th width="20%" style="text-align:right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        $discount = '';
        $Total_Amount = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$company['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        if($numRowsOrderItem > 0){
            $count = 1;
            $Sub_Total = '';
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$company['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                        $rowOrderItem['record_type'] = 'Consultation Fees';
                }

                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount']      = $rowOrderItem['QTY_AMOUNT'];
                }

                $tbl3 = $tbl3.'<tr>
                                    <td width="10%">'.$count.'</td>
                                    <td width="70%">'.$rowOrderItem['record_type'].':
                                    <ol>
                               ';


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        $tbl3 = $tbl3.'<li>'.$rowList['item_title'].'</li>';
                    }
                }

                $tbl3 = $tbl3.'</ol></td>
                                    <td width="20%" style="text-align:right;">'.$rowOrderItem['Amount'].'</td>
                                </tr>';

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }

            $SQLDues = "
            SELECT i.invoice_code
                  ,i.invoice_amount
                  ,i.discount
                  ,irh.amount
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON ( r.receipt_id = irh.receipt_id )
            LEFT JOIN (invoice i) ON ( i.invoice_id = irh.related_invoice_id )
            WHERE r.receipt_status != 'Cancelled'
            AND irh.related_invoice_id != irh.invoice_id
            AND r.receipt_id = {$company['receipt_id']}
            AND r.order_id = {$company['order_id']}
            ";
            $resultDues  = $db->sql_query($SQLDues);
            $numRowsDues = $db->sql_numrows($resultDues);
            $invoice_amount = 0;
            $invoice_due_amount = 0;
            if($numRowsDues > 0){
                $checkboxInvoice = '';
                $Due_items_Details = '';
                $tbl3 = $tbl3.'<tr>
                                 <td colspan="2">Other Invoice(s) Due:
                                 <ol>
                ';
                while ($rowDues = $db->sql_fetchrow($resultDues)) {
                    $invoice_amount += $rowDues['amount'];
                    $invoice_due_amount += $rowDues['invoice_amount'];
                    $tbl3 = $tbl3.'
                        <li>'.$rowDues['invoice_code'].'</li>
                    ';
                }

                $invoice_amount = number_format($invoice_amount, 2);
                $tbl3 = $tbl3.'</ol>
                            </td>
                               <td style="text-align:right;">
                                  '.$invoice_amount.'
                               </td>
                            </tr>';
            }

            $Total_Amount  = $Sub_Total - $company['discount'] + $invoice_amount;
            $Total_Amount_balance  = $Sub_Total - $company['discount'] + $invoice_due_amount;
            $balanceAmount = $Total_Amount_balance - $company['receipt_amount'];
            $Sub_Total     = number_format($Sub_Total + $invoice_amount, 2);
            $discount      = number_format($company['discount'], 2);
            $Total_Amount  = number_format($Total_Amount, 2);
            $ReceiptAmount = number_format($company['receipt_amount'], 2);
            $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="text-align:right;">SUB TOTAL</td>
                                <td style="text-align:right;">'.$Sub_Total.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;">DISCOUNT</td>
                                <td style="text-align:right;">'.$discount.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;">TOTAL AMOUNT</td>
                                <td style="text-align:right;">'.$Total_Amount.'</td>
                            </tr>
            ';

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = " AND i.site_id = {$cpSiteIdSession}";
            }

            $SQLPrevSum = "
            SELECT i.*
                ,(
                SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND r.receipt_id < '{$company['receipt_id']}'
                ) as prev_inv_amount_group
            FROM invoice i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE i.order_id = {$company['order_id']}
                AND i.status != 'Cancelled'                
                {$appendSql}
            ";
            $resultPrevSum  = $db->sql_query($SQLPrevSum);
            $numRowsPrevSum = $db->sql_numrows($resultPrevSum);
            $rowPrevSum     = $db->sql_fetchrow($resultPrevSum);
            $previous_paid_amount = 0;
            if($rowPrevSum['prev_inv_amount_group'] != ''){
                $previous_paid_amount = $rowPrevSum['prev_inv_amount_group'];
                $previous_paid_amount_formatted = number_format($previous_paid_amount, 2);

                $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="text-align:right;">AMOUNT PAID PREVIOUS</td>
                                <td style="text-align:right;">'.$previous_paid_amount_formatted.'</td>
                            </tr>
                ';
            }

            $balanceAmount = number_format($balanceAmount - $previous_paid_amount, 2);

            $tbl3 = $tbl3.'<tr bgColor="#BCFDFD">
                                <td colspan="2" style="text-align:right;">AMOUNT PAID NOW</td>
                                <td style="text-align:right;">'.$ReceiptAmount.'</td>
                            </tr>
            ';

            /*<tr>
                                <td colspan="2" style="text-align:right;">BALANCE AMOUNT</td>
                                <td style="text-align:right;">'.$balanceAmount.'</td>
                            </tr>*/
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

   /**
     *
     */
    function getPrintInvoiceRecordForPurchaseOrder() {
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
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','',11);

        $invoice_code         = $fn->getReqParam('invoice_code');
        $purchase_order_id    = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT ini.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,p.part_number
              ,po.delivery_terms
              ,po.company_id_supplier
              ,po.notes
              ,c.company_name
              ,c.address_flat
              ,c.address_street
              ,c.address_town
              ,c.address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.address_country)
                AS address_country
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_town
              ,c.billing_address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.billing_address_country)
                AS billing_address_country
              ,c.fax
              ,c.phone
              ,i.invoice_date
              ,q.delivery_date
              ,q.delivery_location
              ,ini.unit_price
              ,i.invoice_code
              ,i.invoice_terms
              ,i.invoice_due_date
              ,i.cst
              ,i.vat
              ,i.frieght
              ,i.p_f
              ,q.quote_code
              ,q.currency
              ,ini.qty * ini.unit_price AS amount
              ,(SELECT SUM(init.qty * init.unit_price) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN product p ON (p.product_id = ini.record_id)
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN purchase_order po  ON (po.purchase_order_id = i.purchase_order_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = po.company_id_supplier)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY pg.sort_order ASC, p.title
        ";
        $result = $db->sql_query($SQL);


        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
            $pdf->Output();
            return;
        }

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        $printTaxName = '';
        $gsttaxvalue = '';
        $gstvalue = '';
        $totalvalue = '';

        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, 'Authorized Distributor of:');
                $pdf->SetXY(10,25);
                $pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate   = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
                $invoiceDueDate = $fn->getCPDate($row['invoice_due_date'], 'd-m-Y');
                $deliveryDate   = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
                $currency = $row['currency'];

                $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
                $gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
                $totalvalue = $gstvalue + $row['sub_total'];

                /* Company address */
                //Address to be got from settings
                $pdf->SetXY(130,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(130,5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(130,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(130,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->SetXY(130,30);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->SetXY(130,35);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(80, 40);
                $pdf->Cell(50, 20, "INVOICE", 0, 0, 'C');
                $pdf->SetFont('Courier','B',11);
                $pdf->SetX(130);
                $pdf->Cell(31, 20, "DATE : " . $creationDate, 0, 0, 'L');
                $pdf->Ln(20);

                /* Company Details*/
                $billingAddressFlat = '';
                $billingAddressStreet = '';
                $billingAddressTown = '';
                $billingAddressState = '';
                $billingAddressCountry = '';

                if ($row['billing_address_flat'] != ''
                 || $row['billing_address_street'] != ''
                 || $row['billing_address_town'] != ''
                 || $row['billing_address_state'] != ''
                 || $row['billing_address_country'] != '')
                {
                    $billingAddressFlat     = $row['billing_address_flat'];
                    $billingAddressStreet   = $row['billing_address_street'];
                    $billingAddressTown     = $row['billing_address_town'];
                    $billingAddressState    = $row['billing_address_state'];
                    $billingAddressCountry  = $row['billing_address_country'];
                } else {
                    $billingAddressFlat     = $row['address_flat'];
                    $billingAddressStreet   = $row['address_street'];
                    $billingAddressTown     = $row['address_town'];
                    $billingAddressState    = $row['address_state'];
                    $billingAddressCountry  = $row['address_country'];
                }


                /* Company Details*/
                $date = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"INVOICE TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"DELIVERY TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95, 8, $cpCfg['cp.companyName'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, $cpCfg['cp.companyName'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf1'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf1'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf2'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf2'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf3'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf3'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf4'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf4'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf7'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf7'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf6'], 'LRB', 0, 'L', 1);
                $pdf->Cell(95, 5, $cpCfg['cp.addressPdf6'], 'LRB', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(10);

                /* Invoice Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"INVOICE NO :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(47.5, 8, $row['invoice_code'], 1, 0, 'L', 1);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"DUE DATE :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(47.5, 8, $invoiceDueDate, 1, 0, 'L', 1);
                $pdf->Ln(20);

                $terms = $row['invoice_terms'];
                $bank = "HDFC BANK LTD\nNO.9, MOSQUE STREET\nPALLAVARAM, CHENNAI-600043\nCURRENT A/C:50200000741296";

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"TERMS",1,0, 'L', 1);
                $pdf->Cell(95,8,"BANK DETAILS",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetXY(10,144);
                $pdf->drawTextBox($terms, 95, 32, 'L', 'C', 1);
                $pdf->SetXY(105,144);
                $pdf->drawTextBox($bank, 95, 32, 'L', 'C', 'BLR');
                $pdf->Ln(28);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(65,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(37,8,"PART NUMBER",1,0, 'C', 1);
                $pdf->Cell(13,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(13,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(26,8,"UP",1,0, 'C', 1);
                $pdf->Cell(26,8,"AMOUNT(" . $row['currency'] . ")",1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $company_name   = $row['company_name'];
            $delivery_terms = $row['delivery_terms'];
            $notes          = $row['notes'];


            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(65, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(37, 8, $row['part_number'], 1, 0, 'L', 1);
            $pdf->Cell(13, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(13, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($row['unit_price']),2), 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($row['amount']),2), 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
            $notes = $row['notes'];
            $frieght = $row['frieght'];
            $pf = $row['p_f'];

            if($row['vat'] == 1 && $row['cst'] == 0){
                $printTaxName = $cpCfg['printTaxName'] ;
                $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
                $gstvalue = round($row['sub_total']) * $gsttaxvalue / 100;
                $totalvalue = $gstvalue + round($row['sub_total']);
            } else if($row['cst'] == 1 && $row['vat'] == 0){
                $printTaxName = $cpCfg['printCstText'] ;
                $gsttaxvalue = $cpCfg['printCstinInvoice'] ;
                $gstvalue = round($row['sub_total']) * $gsttaxvalue / 100;
                $totalvalue = $gstvalue + round($row['sub_total']) ;
            }
        }
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(164, 8, "SUB TOTAL", 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($sub_total),2), 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFillColor(255,255,255);

            $pdf->Cell(164, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($gstvalue), 2), 1, 0, 'R', 1);
            $pdf->Ln();

            $totalvalueRounded = round($totalvalue);
            $totalFrieght = $sub_total * $frieght / 100;

            if($frieght != '' ){
                $totalvalueRounded = $totalvalueRounded + $totalFrieght;
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(164, 8, "ADD FRIEGHT : {$frieght}%", 1, 0, 'R', 1);
                $pdf->Cell(26, 8, number_format($totalFrieght, 2), 1, 0, 'R', 1);
                $pdf->Ln();
            }

            if($pf != '' ){
                $totalvalueRounded = $totalvalueRounded + $pf;
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(164, 8, "ADD P&F", 1, 0, 'R', 1);
                $pdf->Cell(26, 8, number_format($pf, 2), 1, 0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(164, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format($totalvalueRounded, 2), 1, 0, 'R', 1);
            $pdf->Ln(20);

            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "Client :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($company_name, 180, 55, 'L', 'T', 0);
            $pdf->Ln();
            $pdf->Ln(5);

            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "Delivery Terms :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($delivery_terms, 170, 32, 'L', 'T', 0);
            $pdf->Ln();
            $pdf->Ln(5);

            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(195,8, "NOTE :", 0, 0, 'L', 1);
            $pdf->Ln(12);
            $pdf->SetFillColor(255,255,255);
            $pdf->drawTextBox($notes, 170, 32, 'L', 'T', 0);
            $pdf->Ln();
            $pdf->Ln(5);

            /* Creation of media record of the invoice */
            $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

            $outputFileName = $outputPath . '/' . $file_name;
            //$pdf->Output($outputFileName , "F");
            $pdf->Output();

    }

    /**
     *
     */
    function getPrintBill() {
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
        $pdf->SetFont('Arial','',11);

        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT ini.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
              ,c.address_flat
              ,c.address_street
              ,c.address_town
              ,c.address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.address_country)
                AS address_country
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_town
              ,c.billing_address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.billing_address_country)
                AS billing_address_country
              ,c.fax
              ,c.phone
              ,i.invoice_date
              ,q.delivery_date
              ,q.delivery_location
              ,ini.unit_price
              ,i.invoice_code
              ,i.invoice_terms
              ,i.invoice_due_date
              ,i.notes
              ,i.discount
              ,q.quote_code
              ,q.currency
              ,ini.qty * ini.unit_price AS amount
              ,(SELECT SUM(init.qty * init.unit_price) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN product p ON (p.product_id = ini.record_id)
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
            $pdf->Output();
            return;
        }

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        if($session_order_id < 10){
            $orderId = '0000' . $session_order_id;
        }
        else if($session_order_id < 99){
            $orderId = '000' . $session_order_id;
        }
        else if($session_order_id < 999){
            $orderId = '00' . $session_order_id;
        }
        else if($session_order_id < 9999){
            $orderId = '0' . $session_order_id;
        }
        else{
            $orderId = $session_order_id;
        }

        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printWebAddress']);

                $creationDate   = $fn->getCPDate($row['invoice_date'], 'd-m-Y');

                /* Company address */
                //Address to be got from settings
                $pdf->SetFont('Courier','B',11);
                $pdf->SetXY(130,0);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(130,5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(130,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(130,20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5'  ]);
                $pdf->Ln(5);
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(5);
                $pdf->SetXY(130,30);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(80, 45);
                $pdf->Cell(50, 20, "BILL", 0, 0, 'C');
                $pdf->SetFont('Courier','B',11);
                $pdf->SetX(130);
                $pdf->Cell(31, 20, "DATE : " . $creationDate, 0, 0, 'L');
                $pdf->Ln(20);

                /* Invoice Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(30,8,"BILL NO :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(65, 8, $row['invoice_code'], 1, 0, 'L', 1);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(30,8,"ORD NO :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(65, 8, $orderId, 1, 0, 'L', 1);
                $pdf->Ln(12);

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(22,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(90,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(15,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(15,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(24,8,"UP",1,0, 'C', 1);
                $pdf->Cell(25,8,"AMOUNT" ,1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(22, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(90, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(15, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(15, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(24, 8, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $row['amount'], 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
            $discount = $row['discount'];
            $total = $row['sub_total'] - $row['discount'];
        }
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "SUB TOTAL", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

            $printTaxName = $cpCfg['printTaxName'] ;

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "Discount", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $discount, 1, 0, 'R', 1);
            $pdf->Ln();

            //$totalvalueRounded = round($totalvalue);
            //$totalvalueRounded = $totalvalue;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($total, 2), 1, 0, 'R', 1);
            $pdf->Ln(20);

            /* Creation of media record of the invoice */
            $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

            $outputFileName = $outputPath . '/' . $file_name;
            //$pdf->Output($outputFileName , "F");
            $pdf->Output();

    }
}