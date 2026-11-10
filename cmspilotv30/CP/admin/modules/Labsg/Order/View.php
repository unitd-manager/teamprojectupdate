<?
class CP_Admin_Modules_Labsg_Order_View extends CP_Common_Lib_ModuleViewAbstract
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

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $subSqlForPercentSum = "
            SELECT o.*
                  ,(SELECT SUM(invHist.amount) AS prev_sum
                    FROM invoice_receipt_history invHist
                    LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                    LEFT JOIN `invoice` i ON (i.order_id = {$row['order_id']})
                    WHERE invHist.invoice_id =  i.invoice_id
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
            if($row['bill_type'] == 'Company'){
                $name = $row['company_name'];
            } else {
                $name = $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'];
            }
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['order_code'])}
            {$listObj->getListDataCell($row['bill_type'])}
            {$listObj->getListDataCell($name)}
            {$listObj->getListDataCell($order_date)}
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
        {$listObj->getListHeaderCell('Order Code', 'o.order_code')}
        {$listObj->getListHeaderCell('Bill Type', 'o.bill_type')}
        {$listObj->getListHeaderCell('Company / Individual Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Order Date', 'o.order_date')}
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
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['shipping_address_country_code']);

        $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY');

        $currency = strtoupper($row['currency']);

        $subSqlForPercentSum = "
        SELECT o.*
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                LEFT JOIN `invoice` i ON (i.order_id = {$row['order_id']})
                WHERE invHist.invoice_id =  i.invoice_id
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

        //{$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        $fielset1 = "
        {$formObj->getTBRow('Order Id', 'order_id', $row['order_id'], $expNoEdit)}
        {$formObj->getTBRow('Order Date', 'creation_date', $creation_date, $expNoEdit)}
        {$formObj->getTBRow('Status', 'order_status', $row['order_status'], $expNoEdit)}
        ";

        $fielset2 = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'], $expNoEdit)}
        {$formObj->getTBRow('Office Address', 'cust_address1', $row['cust_address1'], $expNoEdit)}
        {$formObj->getTBRow('Street Address', 'cust_address2', $row['cust_address2'], $expNoEdit)}
        {$formObj->getTBRow('District / Town', 'cust_address_city', $row['cust_address_city'], $expNoEdit)}
        {$formObj->getTBRow('State / Zip', 'cust_address_state', $row['cust_address_state'], $expNoEdit)}
        {$formObj->getTBRow('Country', 'cust_address_country_code', $row['cust_address_country_code'], $expNoEdit)}
        ";

        $fielset3 = "
        {$formObj->getTBRow('Company Name', 'shipping_first_name', $row['shipping_first_name'])}
        {$formObj->getTBRow('Address 1', 'shipping_address1', $row['shipping_address1'])}
        {$formObj->getTBRow('Address 2', 'shipping_address2', $row['shipping_address2'])}
        {$formObj->getTBRow('District/ Town', 'shipping_address_city', $row['shipping_address_city'])}
        {$formObj->getTBRow('State/ Zip', 'shipping_address_state', $row['shipping_address_state'])}
        {$formObj->getDDRowBySQL('Country', 'shipping_address_country_code', $sqlCountry, $row['shipping_address_country_code'], $expCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Customer Details', $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";
        //{$formObj->getFieldSetWrapped('Delivery Address', $fielset3)}
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Order Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div class='orderEdit'>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <thead>
                            <tr>
                                <th>Order Id</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th class='txtRight'>Amount Paid</th>
                                <th class='txtRight'>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$row['order_id']}</td>
                                <td>{$creation_date}</td>
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
                                <th>Address 1</th>
                                <th>Address 2</th>
                                <th>District / Town</th>
                                <th>Postal Code</th>
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
                                <td>{$row['country_name']}</td>
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

        $bill_type                     = $fn->getReqParam('bill_type');
        $order_date1 	 			   = $fn->getReqParam('order_date_1');
        $order_date2	 			   = $fn->getReqParam('order_date_2');
        $order_status    			   = $fn->getReqParam('order_status');
        $shipment_status 			   = $fn->getReqParam('shipment_status');
        $shipping_address_country_code = $fn->getReqParam('shipping_address_country_code');

        $dirText = "";

        if ($cpCfg['cp.hasDirectoryMg'] == 1){
            $business_id = $fn->getReqParam('business_id');
            $business_contact_id = $fn->getReqParam('business_contact_id');

            $SQLBusiness = "
            SELECT b.business_id
                    ,b.business_name
            FROM business b
            ORDER BY b.business_name
            ";

            $SQLBusinessContact = "
            SELECT bc.business_contact_id
                    ,CONCAT_WS(' ', bc.first_name, bc.last_name) AS contact_name
            FROM business_contact bc
            ORDER BY contact_name
            ";

            $dirText = "
            <td class='fieldValue'>
                <select name='business_id'>
                    <option value=''>Business</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLBusiness, $business_id)}
                </select>
            </td>

            <td class='fieldValue'>
                <select name='business_contact_id'>
                    <option value=''>Contact</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLBusinessContact, $business_contact_id)}
                </select>
            </td>
            ";
        }

        $orgText = "";
        /*
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
	        $organization_id = $fn->getReqParam('organization_id');

	        $SQLOrg = "
	        SELECT o.organization_id
	              ,o.name
	        FROM organization o
	        ORDER BY o.name
	        ";

                $orgText = "
	        <td class='fieldValue'>
	            <select name='organization_id'>
	                <option value=''>Organization</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $SQLOrg, $organization_id)}
	            </select>
	        </td>
	        ";
        }
        */

        $shipmentStatus = "";
        /*
        if ($cpCfg['m.ecommerce.order.showShipmentStatus']) {
            $shipmentStatus = "
            <td class='fieldValue'>
                <select name='shipment_status'>
                    <option value=''>Shipment Status</option>
                    {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.shipmentStatusArr'], $shipment_status)}
                </select>
            </td>
            ";
        }
*/
        $sqlBillType = "SELECT DISTINCT bill_type FROM `order` WHERE bill_type != ''";
        $text = "
        {$dirText}
        {$orgText}
        <td>
            {$formObj->getDateRangeRow('Order Date:', 'order_date', $order_date1, $order_date2)}
        </td>

        <!--<td class='fieldValue'>
            <select name='shipping_address_country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $fn->getGeoCountrySQL(), $shipping_address_country_code)}
            </select>
        </td>-->
        <td>
            <select name='bill_type' class='float_right m5'>
                <option value=''>Bill Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBillType, $bill_type)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='order_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $order_status)}
            </select>
        </td>
        {$shipmentStatus}
        ";


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

        $printText = "";
        $actionButtons = "";
        $summaryAction = "";
        $captainCopy = "";

        $summaryTableOrder = $this->getSummaryInOrder($row);

        //{$this->getOrderItemPortalDisplay($row)}
        $text = "
        {$this->getPatientVisitPortalDisplay($row['order_id'])}
        {$summaryTableOrder}
        {$this->getOrderItemPortalDisplay($row)}
        {$this->getInvoicePortalDisplay($row)}
        {$this->getReceiptPortalDisplay($row)}
        ";

        return $text;
    }

    /**
    **/

    function getSummaryInOrder($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $text = '';

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
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
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
        SELECT oi.record_type
              ,SUM(oi.unit_price) AS Amount
              ,SUM(oi.unit_price*oi.qty) AS QTY_AMOUNT
              ,oi.patient_name
              ,oi.patient_information_id
              ,pv.visit_code
              ,pv.patient_visit_id
        FROM order_item oi
        LEFT JOIN (patient_visit pv) ON (oi.patient_visit_id = pv.patient_visit_id)
        WHERE oi.order_id = {$row['order_id']}
        AND oi.record_type != ''
        GROUP BY oi.patient_information_id
        ORDER BY oi.record_type ASC
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
                AND patient_information_id = {$rowOrderItem['patient_information_id']}
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                $patientVisitLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$rowOrderItem['patient_visit_id']}";
                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['patient_name']} [Visit code : <a href='{$patientVisitLink}'><u>{$rowOrderItem['visit_code']}</u></a> ]</b></td>
                            <td><b>{$rowOrderItem['record_type']}:</b>
                            <ol>
                        ";

                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        $Lab .= "<li>{$rowList['item_title']}</li>";
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

        $sub_total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);
        $total_amount = number_format($Sub_Total - $row['discount'], 2);

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
                            <th>Name</th>
                            <th>Description</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$order_items_Details}
                        <tr>
                            <th class='txtRight' colspan='2'>Sub Total</th>
                            <th class='txtRight'>{$sub_total}</th>
                        </tr>
                        <tr>
                            <th class='txtRight' colspan='2'>Discount</th>
                            <th class='txtRight'>{$discount}</th>
                        </tr>
                        <tr>
                            <th class='txtRight' colspan='2'>Total Amount</th>
                            <th class='txtRight'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        if($numRowsOrderItem > 0){
        $text = "
        {$rows}
        ";
        }

        return $text;

    }

    /**
     *
     */
    function getPrintInvoiceRecord1() {
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

        $SQL = "
        SELECT ini.*
                ,o.company_name
                ,o.first_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_state
                ,o.cust_address_po_code
                ,o.bill_type
                ,gc.name AS shipping_address_country_code
                ,o.shipping_address1
                ,o.shipping_address_area
                ,gco.name AS shipping_address_country
                ,o.shipping_address_po_code
                ,o.shipping_phone
                ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
                ,o.order_id
                ,o.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.status
                ,i.invoice_id
                ,ROUND((ini.qty * ini.unit_price), 2) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 2) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = o.patient_visit_id)
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country_code = gc.country_code)
        LEFT JOIN geo_country gco ON (o.shipping_address_country_code = gco.country_code)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE i.invoice_id = '{$invoice_code}'
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
                <td align="center" style="font-weight:bold;">INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if ($company['bill_type'] == 'Individual') {
            $bill_to_name = strtoupper($company['first_name']);
            $address1 = strtoupper($company['shipping_address1']);
            if($company['shipping_address_area']) {
                $address2 = '
                <span>'.strtoupper($company['shipping_address_area']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country']);
            $addressPostal  = $company['shipping_address_po_code'];
        } else {
            $bill_to_name = strtoupper($company['company_name']);
            $address1 = strtoupper($company['cust_address1']);
            if($company['cust_address2']) {
                $address2 = '
                <span>'.strtoupper($company['cust_address2']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country_code']);
            $addressPostal  = $company['cust_address_state'];
        }

        $invoice_codeVal = substr($company['invoice_code'], 2);

        if($company['bill_type'] == 'Company'){
            $company['patient_name'] = $company['company_name'];
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="62%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.$bill_to_name.'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.$address1.', </span><br/>'. $address2 .'
                            <span>'.$addressCountry.' - '.$addressPostal.'.</span>
                        </td>
                        <td width="38%" style="line-height:20px;"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$invoice_date.'</span><br/>
                            <span>Invoice Code : '.$company['invoice_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='
        <table border="1" width="100%" cellpadding="4" style="font-size:15px;">
            <thead>
                <tr>
                    <th width="8%">S.NO</th>
                    <th width="77%">DESCRIPTION</th>
                    <th width="15%" style="text-align:right;">AMOUNT</th>
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
        FROM invoice_item
        WHERE invoice_id = '{$invoice_code}'
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
                SELECT ii.item_title
                      ,ii.unit_price
                      ,ii.order_item_id
                      ,oi.patient_name
                      ,oi.patient_visit_id
                      ,oi.patient_information_id
                      ,oi.nric
                FROM invoice_item ii
                LEFT JOIN (order_item oi) ON (ii.order_item_id = oi.order_item_id)
                WHERE ii.invoice_id = {$company['invoice_id']}
                AND ii.record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList  = $db->sql_query($SQLOrderItemList);
                $resultList2 = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);
                $numRowsCounter = 1;
                $numRowsCounter1 = 1;

                $tbl3 = $tbl3.'<tr>
                                    <td width="8%">'.$count.'</td>
                                    <td width="77%">'.$rowOrderItem['record_type'].':<br/>
                               ';

                if($numRowsList > 0){
                    $patient_name = '';
                    $count = 1;
                    while($rowList = $db->sql_fetchrow($resultList)){
                        if ($numRowsCounter == $numRowsList && $patient_name != '' && $rowList['patient_name'] == $patient_name) {
                            $tbl3 = $tbl3 . $count . '. ' . $rowList['item_title'];
                        } else if ($patient_name != '' && $rowList['patient_name'] == $patient_name) {
                            $tbl3 = $tbl3 . $count . '. ' . $rowList['item_title'] .'<br/>';                            
                        } else {
                            $SQLTreatmentVisit = "
                            SELECT creation_date
                            FROM treatment_visit tv
                            WHERE tv.patient_visit_id = '{$rowList['patient_visit_id']}'
                            ";
                            $resultTv   = $db->sql_query($SQLTreatmentVisit);
                            $rowTv = $db->sql_fetchrow($resultTv);
                            $treatment_date = $fn->getCPDate($rowTv['creation_date'], 'd-m-Y');

                            $count = 1;
                            $tbl3 = $tbl3 .'<br/>' . $rowList['patient_name'] . ' - ' . $rowList['nric'] . ' ['.$treatment_date.']'.'<br/>' . 
                            $count . '. ' . $rowList['item_title'] .'<br/>';
                            $patient_name = $rowList['patient_name'];
                            $count++;
                        }
                        $numRowsCounter++;
                    }
                }

                $tbl3 = $tbl3.'</td>
                        <td width="15%" style="text-align:right;">';
                if($numRowsList > 0){
                    $patient_name = '';
                    $count = 1;
                    while($rowList1 = $db->sql_fetchrow($resultList2)){
                        if ($numRowsCounter1 == $numRowsList && $patient_name != '' && $rowList['patient_name'] == $patient_name) {
                            $tbl3 = $tbl3 . $count . '. ' . $rowList1['unit_price'];
                        } else if ($patient_name != '' && $rowList['patient_name'] == $patient_name) {
                            $tbl3 = $tbl3 . $count . '. ' . $rowList1['unit_price'] .'<br/>';                            
                        } else {
                            $count = 1;
                            $patient_name_length = strlen($rowList1['patient_name']);
                            if ($patient_name == ''){
                                if ($patient_name_length > 28) {
                                    $tbl3 = $tbl3 .'<br/><br/><br/><br/><br/>' . $rowList1['unit_price'];
                                } else {
                                    $tbl3 = $tbl3 .'<br/><br/><br/><br/>' . $rowList1['unit_price'];
                                }
                            } else if ($patient_name == $rowList1['patient_name']) {
                                $tbl3 = $tbl3 .'<br/>' . $rowList1['unit_price'];
                            } else {
                                if ($patient_name_length > 28) {
                                    $tbl3 = $tbl3 .'<br/><br/><br/><br/>' . $rowList1['unit_price'];
                                } else {
                                    $tbl3 = $tbl3 .'<br/><br/><br/>' . $rowList1['unit_price'];                                    
                                }
                            }
                            $patient_name = $rowList1['patient_name'];
                            $count++;
                        }
                        $numRowsCounter1++;
                    }
                }

                $tbl3 = $tbl3.'</td></tr>';

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }

            $Total_Amount = $Sub_Total - $company['discount'];
            $Sub_Total    = number_format($Sub_Total, 2);
            $discount     = number_format($company['discount'], 2);
            $Total_Amount = number_format($Total_Amount, 2);

            $tbl3 = $tbl3.'
            <tr>
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

        $tbl4 ='
        <table width="100%" cellpadding="0" style="font-size:15px;">
            <tr>
                <td>If you wish to make payment by Cheque, please issue the cheque to MEDIWAY PTE LTD</td>
            </tr>
        <table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintInvoiceRecord() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

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

        $SQL = "
        SELECT ini.*
                ,o.company_name
                ,o.first_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_state
                ,o.cust_address_po_code
                ,o.bill_type
                ,gc.name AS shipping_address_country_code
                ,o.shipping_address1
                ,o.shipping_address_area
                ,gco.name AS shipping_address_country
                ,o.shipping_address_po_code
                ,o.shipping_phone
                ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
                ,o.order_id
                ,o.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.status
                ,i.invoice_id
                ,ROUND((ini.qty * ini.unit_price), 2) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 2) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = o.patient_visit_id)
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country_code = gc.country_code)
        LEFT JOIN geo_country gco ON (o.shipping_address_country_code = gco.country_code)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE i.invoice_id = '{$invoice_code}'
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
                <td align="center" style="font-weight:bold;">INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if ($company['bill_type'] == 'Individual') {
            $bill_to_name = strtoupper($company['first_name']);
            $address1 = strtoupper($company['shipping_address1']);
            if($company['shipping_address_area']) {
                $address2 = '
                <span>'.strtoupper($company['shipping_address_area']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country']);
            $addressPostal  = $company['shipping_address_po_code'];
        } else {
            $bill_to_name = strtoupper($company['company_name']);
            $address1 = strtoupper($company['cust_address1']);
            if($company['cust_address2']) {
                $address2 = '
                <span>'.strtoupper($company['cust_address2']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country_code']);
            $addressPostal  = $company['cust_address_state'];
        }

        $invoice_codeVal = substr($company['invoice_code'], 2);

        if($company['bill_type'] == 'Company'){
            $company['patient_name'] = $company['company_name'];
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="62%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.$bill_to_name.'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.$address1.', </span><br/>'. $address2 .'
                            <span>'.$addressCountry.' - '.$addressPostal.'.</span>
                        </td>
                        <td width="38%" style="line-height:20px;"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$invoice_date.'</span><br/>
                            <span>Invoice Code : '.$company['invoice_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='
        <table border="0" width="100%" cellpadding="4" style="font-size:15px; border:1px solid #000;">
            <thead>
                <tr>
                    <th style="border:1px solid #000;" width="8%">S.NO</th>
                    <th style="border:1px solid #000;" width="77%">DESCRIPTION</th>
                    <th width="15%" style="text-align:right;border:1px solid #000;">AMOUNT</th>
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
        FROM invoice_item
        WHERE invoice_id = '{$invoice_code}'
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
                SELECT ii.item_title
                      ,ii.unit_price
                      ,ii.order_item_id
                      ,oi.patient_name
                      ,oi.patient_visit_id
                      ,oi.patient_information_id
                      ,oi.nric
                FROM invoice_item ii
                LEFT JOIN (order_item oi) ON (ii.order_item_id = oi.order_item_id)
                WHERE ii.invoice_id = {$company['invoice_id']}
                AND ii.record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList  = $db->sql_query($SQLOrderItemList);
                $resultList2 = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);
                $numRowsCounter = 1;
                $numRowsCounter1 = 1;

                $tbl3 = $tbl3.'<tr>
                                    <td width="8%">'.$count.'</td>
                                    <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$rowOrderItem['record_type'].':</td>
                                    <td width="15%"></td>
                                </tr>
                               ';

                if($numRowsList > 0){
                    $patient_name = '';
                    $count = 1;
                    while($rowList = $db->sql_fetchrow($resultList)){
                        if ($numRowsCounter == $numRowsList && $patient_name != '' && $rowList['patient_name'] == $patient_name) {
                           $tbl3 = $tbl3.'<tr>
                                            <td width="8%"></td>
                                            <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$count . '. ' . $rowList['item_title'].'</td>
                                            <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                          </tr>';
                        } else if ($patient_name != '' && $rowList['patient_name'] == $patient_name) {
                           $tbl3 = $tbl3.'<tr>
                                            <td width="8%"></td>
                                            <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'. $count . '. ' . $rowList['item_title'].'</td>
                                            <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                          </tr>';                          
                        } else {
                            $SQLTreatmentVisit = "
                            SELECT creation_date
                            FROM treatment_visit tv
                            WHERE tv.patient_visit_id = '{$rowList['patient_visit_id']}'
                            ";
                            $resultTv   = $db->sql_query($SQLTreatmentVisit);
                            $rowTv = $db->sql_fetchrow($resultTv);
                            $treatment_date = $dateUtil->formatDate($rowTv['creation_date'], 'DD-MM-YYYY');

                            $count = 1;
                            $tbl3 = $tbl3.'<tr>
                                            <td width="8%"></td>
                                            <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$rowList['patient_name'] . ' - ' . $rowList['nric'] . ' ['.$treatment_date.']'.'</td>
                                            <td width="15%"></td>
                                          </tr>'; 
                            $tbl3 = $tbl3.'<tr>
                                            <td width="8%"></td>
                                            <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$count . '. ' . $rowList['item_title'].'</td>
                                            <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                          </tr>';
                            $patient_name = $rowList['patient_name'];
                            $count++;
                        }

                        $numRowsCounter++;
                    }
                }

                $tbl3 = $tbl3.'';

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }

            $Total_Amount = $Sub_Total - $company['discount'];
            $Sub_Total    = number_format($Sub_Total, 2);
            $discount     = number_format($company['discount'], 2);
            $Total_Amount = number_format($Total_Amount, 2);

            $tbl3 = $tbl3.'
            <tr>
                <td colspan="2" style="text-align:right;border:1px solid #000;">SUB TOTAL</td>
                <td style="text-align:right;border:1px solid #000;">'.$Sub_Total.'</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:right;border:1px solid #000;">DISCOUNT</td>
                <td style="text-align:right;border:1px solid #000;">'.$discount.'</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:right;border:1px solid #000;">TOTAL AMOUNT</td>
                <td style="text-align:right;border:1px solid #000;">'.$Total_Amount.'</td>
            </tr>
            ';
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $tbl4 ='
        <table width="100%" cellpadding="0" style="font-size:15px;">
            <tr>
                <td>If you wish to make payment by Cheque, please issue the cheque to MEDIWAY PTE LTD</td>
            </tr>
        <table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     */
    function getInvoicePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $formActionReceipt = "index.php?module=labsg_order&_spAction=generateReceiptForm&order_id={$row['order_id']}&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
        $receiptBtn = "
        <button href='{$formActionReceipt}' id='generateReceipt'
        class='button mt5 ml5 mb20'>Generate Receipt</button>
        ";

        $text = "
        <tr class=''>
        <td>
            <div id='' class='invoiceDisplay'>
                <h2>Invoice(s)</h2>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getInvoicePortalDisplayDetail($row)}
                    </div>
                </form>
            </div>
        </td>
        </tr>
        {$receiptBtn}
        ";

        return $text;
    }

    /**
     */
    function getSalesReturnDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $text = "
        <tr class=''>
        <td>
            <div id='' class='invoiceDisplay'>
                <h2>Sales Return(s)</h2>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getSalesReturnDisplayDetail($row)}
                    </div>
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getInvoicePortalDisplayDetail($row){
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
        WHERE i.order_id = {$row['order_id']}
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
        $printLbl = "Print";

        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $gstvalue = '';
            $gsttaxvalue = '';
            $pfvalue = '';
            $frieghtValue = '';
            $total = '';
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);

            $urlPrint  = "index.php?_topRm=finance&module=labsg_order&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_id']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $total += $rowInvoice['invoice_amount'];

            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";
                $cancelInvoiceForm = "index.php?module=labsg_invoice&_spAction=cancelInvoiceForm&invoice_id={$rowInvoice['invoice_id']}&&showHTML=0";
                $cancelInvoiceLink = "<a href='{$cancelInvoiceForm}' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><img src='{$cancel_image}' class='icon'></a>";
            }

            $highlight = '';
            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";
            $printInv = "<a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a>";
            if ($rowInvoice['status'] == 'Cancelled'){
                $highlight = 'highlightCell';
                $printInv = $rowInvoice['cancelling_notes'];
                $printLbl = "Remarks";
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');

            if($total > 0){
                $total = $total - $rowInvoice['discount'];
            }
            $total = number_format($total, 2);

            $rows .= "
            <tr>
                <td>{$rowInvoice['invoice_code']}</td>
                <td class='{$highlight}'>{$rowInvoice['status']}</td>
                <td>{$invoice_date}</td>
                <td align='right'>{$total}</td>
                <td>{$printInv}</td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
            }


        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Status</th>
        <th>Invoice Date</th>
        <th class='txtRight'>Amount</th>
        <th>{$printLbl}</th>
        <th>Cancel</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
            {$rowsPvt}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getSalesReturnDisplayDetail($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT srh.*
              ,i.invoice_code
              ,(SELECT SUM(srhh.price * srhh.qty_return) FROM sales_return_history srhh
                WHERE srhh.invoice_id = i.invoice_id
                AND srhh.order_id = {$row['order_id']}
                AND srhh.date = srh.date
                AND srhh.status IS NULL
                ) AS sales_return_amount
        FROM sales_return_history srh
        LEFT JOIN (invoice i) ON (i.invoice_id = srh.invoice_id)
        WHERE srh.order_id = {$row['order_id']}
          AND srh.status IS NULL
        ORDER BY i.invoice_id
        ";
        $result   = $db->sql_query($SQL);

        $invoice_code = '';
        $datechk = '';
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $total = '';

            $urlPrint  = "index.php?_topRm=finance&module=tradingin_order&_spAction=printSalesReturn&invoice_code={$rowInvoice['invoice_code']}&date={$rowInvoice['date']}&sales_return_history_id={$rowInvoice['sales_return_history_id']}&showHTML=0";

            $date = $fn->getCPDate($rowInvoice['date'], 'd-m-Y');
            //$total += $rowInvoice['price'] * $rowInvoice['qty_return'];
            $total += $rowInvoice['sales_return_amount'];
            $totalvalueRounded = number_format(round($total),2);

            if($invoice_code != $rowInvoice['invoice_code'] || $datechk != $rowInvoice['date']){
                $srStatus = '';
                if($rowInvoice['status'] == 'Cancelled'){
                    $srStatus = '(' .$rowInvoice['status']. ')';
                }
                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']} {$srStatus}</td>
                    <td>{$date}</td>
                    <td align='right'>$totalvalueRounded</td>
                    <td><a href='{$urlPrint}' target='_blank'>Print Sales Return</a></td>
                </tr>
                ";
                $invoice_code = $rowInvoice['invoice_code'];
                $datechk = $rowInvoice['date'];
            }
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Sales Return Date</th>
        <th>Amount</th>
        <th>Print</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
     function getGenerateSalesReturnForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        unset($_SESSION['selectedOrderItemIds']);

        $rows = '';

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id = $fn->getReqParam('order_id');
        $date     = $fn->getCurrentDate();
        $qty_balance = '';

        $sqlInvoiceItem = "
        SELECT ii.*
              ,p.carton_no
              ,o.record_type
        FROM invoice_item ii
        LEFT JOIN (product p) ON (p.product_id = ii.record_id)
        LEFT JOIN (`invoice` i) ON (i.invoice_id = ii.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        WHERE ii.invoice_id = {$invoice_id}
        ";
        $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);
        while ($rowII = $db->sql_fetchrow($resultInvoiceItem)) {
            $sqlQty = "
            SELECT SUM(srh.qty_return) AS qty_return
            FROM sales_return_history srh
            WHERE srh.invoice_id = {$invoice_id}
             AND srh.invoice_item_id = {$rowII['invoice_item_id']}
             AND srh.status IS NULL
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            if($rowII['record_type'] == 'POS'){
                $discount_value_for_one_qty = '';
                $discountValue = 0;
                $discountPrice = 0;

                if($rowII['discount_type'] == '%'){
                    $discount_value_for_one_qty  =  $rowII['unit_price'] * ($rowII['discount_percentage']/100);
                    $discountValue = $discount_value_for_one_qty;
                    $discountPrice = $rowII['unit_price'] - $discountValue;
                }
                else if($rowII['discount_type']  == 'Value'){
                    $discount_value_for_one_qty  =  $rowII['discount_percentage'];
                    $discountValue = $discount_value_for_one_qty;
                    $discountPrice = $rowII['unit_price'] - $discountValue;
                }
                $product_Price = $discountPrice;
            }
            else{
                $product_Price = $rowII['unit_price'];
            }

            $inputRow = '';
            $qtyRow = '';
            $qty_balance = $rowII['qty'] - $rowQty['qty_return'];
            if ($rowQty['qty_return'] != $rowII['qty']) {
                $pfx = $rowII['invoice_item_id'] . '_' ;
                $inputRow = "<input class='invoiceItemId' type='checkbox' name='invoiceItemId[]' value='{$rowII['invoice_item_id']}'>";
                $qtyRow = "<input type='text' value='{$qty_balance}' id='fld_qty' class='text w50' name='{$pfx}qty_return'>";
            }

            $rows .= "
            <tr invoiceRowItem[] = {$rowII['invoice_item_id']}>
                <td>
                    {$inputRow}
                </td>
                <td>{$rowII['item_title']}</td>
                <td>{$rowII['carton_no']}</td>
                <td class='sellingPrice txtRight'>{$product_Price}</td>
                <td class=''>{$rowII['qty']}</td>
                <td class=''>{$qtyRow}</td>
                <td class=''>{$rowQty['qty_return']}</td>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=tradingin_order&_spAction=generateSalesReturnFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Amount', 'invoice_amount', '', $expNoEdit)}
            {$formObj->getDateRow('Date', 'sales_return_date', $date)}
            {$formObj->getTARow('Notes', 'notes')}
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            <div class='button updateSalesReturnTotal'>
                <a href='#'>Update Total</a>
            </div>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>
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
                    <th>Carton No.</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th class=''>Qty (Sales Return)</th>
                    <th>Qty Returned</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>

            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintSalesReturn() {
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
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        //$pdf = new MYPDF();
        $pdf = new PDF_MC_Table();
        $pdf->AddPage();
        $pdf->SetFont('Arial','',11);

        $invoiceHeading = '';

        $invoice_code = $fn->getReqParam('invoice_code');
        $date = $fn->getReqParam('date');
        $sales_return_history_id = $fn->getReqParam('sales_return_history_id');

        $SQLInvoice = "
        SELECT *
        FROM `invoice`
        WHERE invoice_code = '{$invoice_code}'
        ";
        $resultInvoice = $db->sql_query($SQLInvoice);
        $invoiceRec = $db->sql_fetchrow($resultInvoice);

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((ini.cost_price * ini.discount_percentage )/100)* sr.qty_return,2)) as discount_sum
        FROM sales_return_history sr
        LEFT JOIN invoice_item ini ON (ini.invoice_item_id = sr.invoice_item_id)
        WHERE sr.invoice_id = {$invoiceRec['invoice_id']}
            AND ini.discount_type = '%'
            AND sr.status IS NULL
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((ini.cost_price * ini.discount_percentage)/100)* sr.qty_return,2))
            FROM sales_return_history sr
            LEFT JOIN invoice_item ini ON (ini.invoice_item_id = sr.invoice_item_id)
            WHERE sr.invoice_id = {$invoiceRec['invoice_id']}
                AND ini.discount_type = '%'
                AND sr.status IS NULL
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(ini.discount_percentage  * sr.qty_return,2)) as discount_sum
        FROM sales_return_history sr
        LEFT JOIN invoice_item ini ON (ini.invoice_item_id = sr.invoice_item_id)
        WHERE sr.invoice_id = {$invoiceRec['invoice_id']}
            AND ini.discount_type = 'Value'
            AND sr.status IS NULL
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(ini.discount_percentage  * sr.qty_return,2))
            FROM sales_return_history sr
            LEFT JOIN invoice_item ini ON (ini.invoice_item_id = sr.invoice_item_id)
            WHERE sr.invoice_id = {$invoiceRec['invoice_id']}
                AND ini.discount_type = 'Value'
                AND sr.status IS NULL
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL = "
        SELECT sr.*
              ,ini.item_title AS product_title
              ,ini.discount_percentage
              ,ini.discount_type
              ,ini.vat
              ,ini.cost_price
              ,sr.qty_return AS qty
              ,p.title AS product_title1
              ,p.unit
              ,CONCAT_WS('::', p.carton_no, p.batch_no, p.model) code
              ,p.item_code
              ,p.part_number
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
              ,c.tin_no
              ,c.cst_no
              ,i.invoice_date
              ,q.delivery_date
              ,q.delivery_location
              ,ini.unit_price
              ,i.invoice_code
              ,i.invoice_code_vat
              ,i.invoice_code_vat_quote
              ,i.invoice_terms
              ,i.invoice_due_date
              ,i.notes
              ,i.cst
              ,i.cst_value
              ,i.vat_value
              ,i.vat AS invoice_vat
              ,i.frieght
              ,i.p_f
              ,o.record_type
              ,o.order_id
              ,o.shipping_address1
              ,o.shipping_first_name
              ,o.shipping_address2
              ,o.shipping_address_city
              ,o.shipping_address_state
               ,(SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = o.shipping_address_country)
                 AS shipping_address_country
              ,q.quote_code
              ,q.currency
              ,sr.qty_return * sr.price AS amount
              ,(ini.unit_price * sr.qty_return) AS Price_POS
              ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)) as discount_percentage_amount_sum
              ,(SELECT SUM(((inih.cost_price * inih.vat )/100)* inih.qty)
                FROM invoice_item inih
                WHERE inih.invoice_id = ini.invoice_id) AS vat_amount_sum
              ,(SELECT SUM(srh.qty_return * srh.price)
                FROM sales_return_history srh
                WHERE srh.invoice_id = sr.invoice_id
                  AND srh.date = sr.date
                  AND srh.status IS NULL) AS selling_price_sum
              ,(SELECT SUM(srh.qty_return * init.cost_price) FROM sales_return_history srh
                LEFT JOIN invoice_item init ON (init.invoice_item_id = srh  .invoice_item_id)
                WHERE srh.invoice_id = sr.invoice_id
                  AND srh.date = sr.date
                  AND srh.status IS NULL) AS sub_total
        FROM sales_return_history sr
        LEFT JOIN invoice_item ini ON (ini.invoice_item_id = sr.invoice_item_id)
        LEFT JOIN product p ON (p.product_id = ini.record_id)
        LEFT JOIN invoice i ON (i.invoice_id = sr.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = sr.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE i.invoice_code = '{$invoice_code}'
        AND sr.date = '{$date}'
        ORDER BY ini.invoice_item_id, pg.sort_order ASC, p.title
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
        $totalpf = '';
        $record_type = '';
        $discountValueTotal = 0;
        $total_discount_value_sum = 0;
        $total_vat_sum = 0;

        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        //syed:multi text code to set width of each column and alignment
        $pdf->SetWidths(array(10, 40, 40, 10, 10, 22, 18, 15, 25));
        $pdf->SetAligns(array('L', 'L', 'L', 'R', 'L', 'R', 'R', 'R', 'R'));

        while ($row = $db->sql_fetchrow($result)) {
            $discount_value_for_one_qty = 0;
            $discountValue =0;

            if($row['record_type'] == 'POS'){
                $pdf->SetWidths(array(10, 45, 50, 10, 10, 22, 18, 25));
                $pdf->SetAligns(array('L', 'L', 'L', 'R', 'L', 'R', 'R', 'R', 'R'));
            }

            if($row['record_type'] == 'POS'){
                $amount = $row['Price_POS'];
            }else{
                $amount = $row['amount'];
            }

            if($row['discount_percentage'] > 0){
                if($row['discount_type'] == '%'){
                    $discount_value_for_one_qty  =  $row['cost_price'] * ($row['discount_percentage']/100);
                    $discountValue = $discount_value_for_one_qty * $row['qty'];
                }
                else if($row['discount_type']  == 'Value'){
                    $discount_value_for_one_qty  =  $row['discount_percentage'];
                    $discountValue = $discount_value_for_one_qty * $row['qty'];
                }
                $discountValueTotal += $discountValue;

            }
            $total_discount_value_sum += $discountValue;
            $vat_for_one_qty = 0;
            $vatAmount =0;

            if($row['vat'] > 0){
                //$vat_for_one_qty  =  $row['cost_price'] * $row['vat']/100;
                $vat_for_one_qty  =  ($row['cost_price'] - $discount_value_for_one_qty) * $row['vat']/100;
                $vatAmount = $vat_for_one_qty;
            }

            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printWebAddress']);

                $creationDate   = $fn->getCPDate($row['date'], 'd-m-Y');
                $invoiceDueDate = $fn->getCPDate($row['date'], 'd-m-Y');
                $currency = $row['currency'];

                $totalvalue = $row['sub_total'];

                /* Company address */
                //Address to be got from settings
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
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);
                $pdf->Ln(5);
                /*$pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(5);*/
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',10);
                $pdf->SetXY(80, 45);
                $pdf->Cell(50, 20, $invoiceHeading . "Sales Return", 0, 0, 'C');
                $pdf->SetFont('Courier','B',9);
                $pdf->SetX(130);
                $pdf->Cell(31, 20, "DATE : " . $creationDate, 0, 0, 'L');
                $pdf->Ln(15);

                /* Company Details*/

                if ($row['shipping_address1'] != ''
                    || $row['shipping_address2'] != ''
                    || $row['shipping_address_city'] != ''
                    || $row['shipping_address_state'] != ''
                    || $row['shipping_address_country'] != '') {
                        //Delivery Address Fields in Order
                        $deliveryAddressFlat    = $row['shipping_address1'];
                        $deliveryAddressStreet  = $row['shipping_address2'];
                        $deliveryAddressTown    = $row['shipping_address_city'];
                        $deliveryAddressState   = $row['shipping_address_state'];
                        $deliveryAddressCountry = $row['shipping_address_country'];
                        $deliveryCompanyName    = $row['shipping_first_name'];
                } else {
                    //Delivery Address Fields in client
                    $deliveryAddressFlat    = $row['address_flat'];
                    $deliveryAddressStreet  = $row['address_street'];
                    $deliveryAddressTown    = $row['address_town'];
                    $deliveryAddressState   = $row['address_state'];
                    $deliveryAddressCountry = $row['address_country'];
                    $deliveryCompanyName    = $row['company_name'];
                }

                /* Company Details*/

                $date = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"INVOICE TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"DELIVERY TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);

                $pdf->Cell(95, 8, $row['company_name'],'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, $deliveryCompanyName , 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $row['billing_address_flat'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $deliveryAddressFlat, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $row['billing_address_street'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $deliveryAddressStreet, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $row['billing_address_town'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $deliveryAddressTown, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $row['billing_address_country'] .' - '. $row['billing_address_state'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $deliveryAddressCountry .' - '. $deliveryAddressState, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 8, 'TIN NO:' . $row['tin_no'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, 'TIN NO:' .$row['tin_no'], 'LR', 0, 'L', 1);
                $pdf->Ln(6);
                $pdf->Cell(95, 8, 'CST NO:' . $row['cst_no'], 'BLR', 0, 'L', 1);
                $pdf->Cell(95, 8, 'CST NO:' .$row['cst_no'], 'BLR', 0, 'L', 1);

                $pdf->Ln(10);

               if($row['record_type'] != 'POS'){

                   if($row['invoice_vat'] == 1){
                        $invoiceCode = 'INVQ -' . $row['invoice_code_vat_quote'];
                    } else {
                        $invoiceCode = $row['invoice_code'];
                    }
                }
                else{
                    $invoiceCode = 'INVT -' .$row['invoice_code_vat'];
                }


                /* Invoice Details*/
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"INVOICE NO :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(47.5, 8, $invoiceCode, 1, 0, 'L', 1);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"Sales Return Date :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(47.5, 8, $invoiceDueDate, 1, 0, 'L', 1);
                $pdf->Ln(12);

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);

                if($row['record_type'] != 'POS'){
                    $pdf->Cell(40,8,"ITEM NAME",1,0, 'C', 1);
                    $pdf->Cell(40,8,"ITEM CODE",1,0, 'C', 1);
                }
                else{
                    $pdf->Cell(45,8,"ITEM NAME",1,0, 'C', 1);
                    $pdf->Cell(50,8,"ITEM CODE",1,0, 'C', 1);
                }

                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(22,8,"UNIT PRICE",1,0, 'C', 1);
                $pdf->Cell(18,8,"DISCOUNT",1,0, 'C', 1);

                if ($row['record_type'] != 'POS'){

                    $pdf->Cell(15,8,"VAT",1,0, 'C', 1);
                    $pdf->Cell(25,8,"AMOUNT(" . $row['currency'] . ")",1,0, 'C', 1);
                    $pdf->Ln();
                }
                else{

                    $pdf->Cell(25,8,"AMOUNT",1,0, 'C', 1);
                    $pdf->Ln();
                }
            }

            //$total_discount_value_sum += $discount_value_for_one_qty;
            $total_vat_sum += $vatAmount;

            //===================================MAIN TABLE============================= //
            $discount_value_for_one_qty = number_format($discount_value_for_one_qty, 2);

            $pdf->SetFillColor(255,255,255);
            /*
            $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(65, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(37, 8, $row['part_number'], 1, 0, 'L', 1);
            $pdf->Cell(13, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(13, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format($row['unit_price'],2), 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($row['amount']),2), 1, 0, 'R', 1);
            */

            if ($row['record_type'] != 'POS'){
                $pdf->Row(array($lineItemNumber, $row['product_title'] , $row['code'], $row['qty'], $row['unit'], number_format($row['cost_price'],2) , '- ' . $discount_value_for_one_qty, number_format($vatAmount, 2), number_format($amount,2) ));
            }
            else{
                $pdf->Row(array($lineItemNumber, $row['product_title'] , $row['code'], $row['qty'], $row['unit'], number_format($row['cost_price'],2) , '- ' . $discount_value_for_one_qty, number_format($amount,2) ));
            }

            //$pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
            $notes = $row['notes'];
            $vat_value = $row['vat_value'];
            //$discount = $row['discount_percentage_amount_sum'];
            $discount  = $total_discount_value_sum;
            $record_type = $row['record_type'];

            $vat_amount_sum = $row['selling_price_sum'] - ($sub_total - $discount);
        }

            $totalvalueRounded = $totalvalue;

            $subtotalvalue = $totalvalue;
            if ($record_type != 'POS'){
                $totalvalue = $totalvalue + $vat_amount_sum - $discount;
            }
            else{
                $totalvalue = $totalvalue - $discount;
            }
            $total_vat_sum = number_format(round($total_vat_sum),2);
            $vat_amount_sum = number_format(round($vat_amount_sum),2);
            $discount = number_format(round($discount),2);

            $pdf->Cell(165,8,"SUB TOTAL",1,0, 'R', 1);
            $pdf->Cell(25,8,number_format(round($subtotalvalue), 2),1,0, 'R', 1);
            $pdf->Ln();

            $pdf->Cell(165,8,"TOTAL DISCOUNT",1,0, 'R', 1);
            $pdf->Cell(25,8,'- ' . $discount,1,0, 'R', 1);
            $pdf->Ln();

            if($record_type != 'POS'){
                $pdf->Cell(165,8,"TOTAL VAT",1,0, 'R', 1);
                //$pdf->Cell(25,8,$vat_amount_sum,1,0, 'R', 1);
                $pdf->Cell(25,8,$total_vat_sum,1,0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(165, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format(round($totalvalue), 2), 1, 0, 'R', 1);
            $pdf->Ln(10);

            $pdf->Cell(190, 8, $cpCfg['cp.invoiceVatInclusive'], 0, 0, 'L');
            $pdf->Ln(10);

            $pdf->Cell(150, 8, 'NOTE: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($notes, 180, 55, 'L', 'T', 0);
            $pdf->Ln(15);

            $pdf->Cell(195,8, "(This is computer generated document, and does not require a signature)", 0, 0, 'L', 1);

            $pdf->Output();
    }

    /**
     *
     */
    function getReceiptPortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);

        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$row['order_id']}
              {$sqlAppend}
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=labsg_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$row['order_id']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";

                $cancelReceiptForm = "index.php?module=labsg_receipt&_spAction=cancelReceiptForm&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";
                $cancelReceiptLink = "<a href='{$cancelReceiptForm}' class='cancelReceipt' order_id =
                '{$row['order_id']}' receipt_code='{$rowReceipt['receipt_code']}'><img src='{$cancel_image}' class='icon'></a>";
            }

            $highlight = '';
            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";
            $print_receipt = "<a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a>";
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $highlight = 'highlightCell';
                $print_receipt = "";
            }

            $view_image = $cpCfg['cp.localPath']."images/icon-view.png";
            $viewUrl ='index.php?module=labsg_receipt&_spAction=receiptDetails&showHTML=0&receipt_id=' . $rowReceipt['receipt_id'];
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td class='{$highlight}'>{$rowReceipt['receipt_status']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$print_receipt}</td>
                <td>{$cancelReceiptLink}</td>
                <td><a class='showDetailPortalForm jqui-dialog' href='{$viewUrl}'><img src='{$view_image}' class='icon'></a></td>
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
            <th>Status</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Amount</th>
            <th>Print</th>
            <th>Cancel</th>
            <th>Details</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <div class='receiptDisplay'><h2>Receipt(s)</h2></div>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <form id='orderItemPrint' class='' method='post'
                action='{$formAction}'>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                </table>
                <input type='hidden' name='order_id' value='{$row['order_id']}' />
                <input type='hidden' name='receipt_id' value='{$receiptRec['receipt_id']}' />
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
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

        $previous_paid_amount = 0;
        $total_amount = 0;

        $SQL = "
        SELECT o.company_name
              ,o.first_name
              ,o.cust_address1
              ,o.cust_address2
              ,o.cust_address_state
              ,o.cust_address_po_code
              ,o.bill_type
              ,gc.name AS shipping_address_country_code
              ,o.shipping_address1
              ,o.shipping_address_area
              ,gco.name AS shipping_address_country
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
              ,r.receipt_status
              ,r.date
        FROM receipt r
        LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country_code = gc.country_code)
        LEFT JOIN geo_country gco ON (o.shipping_address_country_code = gco.country_code)
        WHERE r.receipt_code = '{$receipt_code}'
          AND i.order_id = {$order_id}
        ";
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
        $receipt_date = $fn->getCPDate($company['date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;">RECEIPT</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if ($company['bill_type'] == 'Individual') {
            $bill_to_name = strtoupper($company['first_name']);
            $address1 = strtoupper($company['shipping_address1']);
            if($company['shipping_address_area']) {
                $address2 = '
                <span>'.strtoupper($company['shipping_address_area']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country']);
            $addressPostal  = $company['shipping_address_po_code'];
        } else {
            $bill_to_name = strtoupper($company['company_name']);
            $address1 = strtoupper($company['cust_address1']);
            if($company['cust_address2']) {
                $address2 = '
                <span>'.strtoupper($company['cust_address2']).',</span><br/>
                ';
            }
            $addressCountry = strtoupper($company['shipping_address_country_code']);
            $addressPostal  = $company['cust_address_state'];
        }

        $tbl2 ='
        <table border="0" width="100%" cellpadding="0" style="font-size:15px;">
            <tr>
                <td width="62%" style="line-height:20px;"><br/>
                    <span><b>NAME :</b> '.$bill_to_name.'</span><br/><br/>
                    <span><b>ADDRESS :</b><br/></span>
                    <span>'.$address1.', </span><br/>'. $address2 .'
                    <span>'.$addressCountry.' - '.$addressPostal.'.</span>
                </td>
                <td width="38%" style="line-height:20px;"><br/>
                    <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$receipt_date.'</span><br/>
                    <span>Receipt Code : '.$company['receipt_code'].'</span>
                </td>
            </tr>
        </table>
        ';

       /*This sql used to find the previous amount paid for the invoice */
        //print $row['invoice_id_main'] . ' - receipt id';
        //print $row['receipt_id'] . ' - receipt id';
        $sqlPreviousPayment = "
        SELECT SUM(irhist.amount) AS total_amount_paid
        FROM invoice_receipt_history irhist
        LEFT JOIN receipt r ON (irhist.receipt_id = r.receipt_id)
        WHERE irhist.invoice_id = {$company['invoice_id_main']}
          AND irhist.receipt_id != {$company['receipt_id']}
          AND r.receipt_status != 'Cancelled'
        ";
        $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
        $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
        $previous_paid_amount += $rowPreviousPayment['total_amount_paid'];

        $sqlInv = "
        SELECT i.invoice_code
              ,i.invoice_amount
              ,i.discount
        FROM invoice i
        LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
        WHERE irh.receipt_id = {$company['receipt_id']}
        ";
        $resultInv = $db->sql_query($sqlInv);
        $numRowsInv = $db->sql_numrows($resultInv);
        $count = 1;
        $inv_code_display = '';
        $invoice_amount_display = 0;
        $discount_amount_display = 0;
        while ($rowInv = $db->sql_fetchrow($resultInv)) {
            if ($count == $numRowsInv) {
                $inv_code_display .= $rowInv['invoice_code'];
            } else {
                $inv_code_display .= $rowInv['invoice_code'] . ', ';
            }
            $invoice_amount_display += $rowInv['invoice_amount'];
            $discount_amount_display += $rowInv['discount'];
            $count++;
        }

        $amount_payable = $invoice_amount_display - $discount_amount_display;
        $receipt_amount = $company['receipt_amount'];
        $balance_due = $amount_payable - $previous_paid_amount - $receipt_amount;

        $SQLOrderItemList = "
        SELECT ii.item_title
              ,ii.unit_price
              ,ii.order_item_id
              ,oi.patient_name
              ,oi.nric
        FROM invoice_item ii
        LEFT JOIN (order_item oi) ON (ii.order_item_id = oi.order_item_id)
        WHERE ii.invoice_id = {$company['invoice_id_main']}
        ";
        $resultList = $db->sql_query($SQLOrderItemList);
        $numRowsList = $db->sql_numrows($resultList);
        $numRowsCounter = 1;
        if($numRowsList > 0){
            $patient_name = '';
            $count = 1;
            $invoiceItemRow = '';
            while($rowList = $db->sql_fetchrow($resultList)){
                if ($numRowsCounter == $numRowsList && $patient_name != '' && $rowList['patient_name'] == $patient_name) {
                    $invoiceItemRow = $invoiceItemRow . $count . '. ' . $rowList['item_title'];
                } else if ($patient_name != '' && $rowList['patient_name'] == $patient_name) {
                    $invoiceItemRow = $invoiceItemRow . $count . '. ' . $rowList['item_title'] .'<br/>';                            
                } else {
                    $count = 1;
                    $invoiceItemRow = $invoiceItemRow .'<br/>' . $rowList['patient_name'] . ' - ' . $rowList['nric'] . '<br/>' . 
                    $count . '. ' . $rowList['item_title'] .'<br/>';
                    $patient_name = $rowList['patient_name'];
                    $count++;
                }
                $numRowsCounter++;
            }
        }

        $tbl3 ='
        <table border="1" width="100%" cellpadding="4" style="font-size:15px;">
            <tr>
                <td width="85%">DESCRIPTION</td>
                <td width="15%" style="text-align:right;">AMOUNT</td>
            </tr>
            <tr>
                <td width="85%">Invoice Amount for:<br/>(Invoice Code:' . $inv_code_display .')<br/>
                '. $invoiceItemRow . '
                </td>
                <td width="15%" style="text-align:right;">'. number_format($amount_payable, 2) .'</td>
            </tr>
            <tr>
                <td width="85%">Amount Already Paid</td>
                <td width="15%" style="text-align:right;">'. number_format($previous_paid_amount, 2) .'</td>
            </tr>
            <tr>
                <td width="85%">Amount Received Now</td>
                <td width="15%" style="text-align:right;">'. number_format($receipt_amount, 2) .'</td>
            </tr>
            <tr>
                <td width="85%">Balance Amount to be Paid</td>
                <td width="15%" style="text-align:right;">'. number_format($balance_due, 2) .'</td>
            </tr>
        </table>
        ';

        $tbl4 ='
        <table border="0" width="100%" cellpadding="4" style="font-size:15px;">
            <tr>
                <td>Payment Method</td>
            </tr>
            <tr>
                <td>'. $company['mode_of_payment'] .'<br/></td>
            </tr>
            <tr>
                <td>Notes</td>
            </tr>
            <tr>
                <td>'. $company['remarks'] .'</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = $company['receipt_code'] . '-Receipt.pdf';
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

        $invoice_code 		  = $fn->getReqParam('invoice_code');
        $purchase_order_id 	  = $fn->getReqParam('purchase_order_id');

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
			$company_name 	= $row['company_name'];
			$delivery_terms = $row['delivery_terms'];
			$notes 			= $row['notes'];


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

    /**
     */
    function getPatientVisitPortalDisplay($order_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows       = "";
        $text       = '';
        $printPO    = '';
        $orderRow   = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $site_id    = $fn->getSessionParam('cp_site_id');

        $formAction = "index.php?_topRm=main&module=labsg_order&_spAction=patientInvoiceSubmit&showHTML=0";

        $numRows = 0;
        if($orderRow['company_id'] != ''){

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND pv.site_id = {$site_id}";
            }

            $SQL = "
            SELECT pv.*
                  ,pi.name
                  ,pi.registration_no
            FROM patient_visit pv
            LEFT JOIN patient_information pi ON pi.patient_information_id = pv.patient_information_id
            WHERE pi.company_id = {$orderRow['company_id']}
              AND (pv.order_id IS NULL OR pv.order_id = $order_id)
              AND pi.bill_type = 'Company'
              AND pv.status != 'Cancelled'
               {$appendSql}
            ";
            $result   = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            while ($row = $db->sql_fetchrow($result)) {
                $SQLTV = "
                SELECT tv.treatment_id
                      ,t.title
                      ,tv.patient_visit_id
                      ,tv.fees
                FROM treatment_visit tv
                LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
                WHERE tv.patient_visit_id = '{$row['patient_visit_id']}'
                GROUP BY tv.treatment_id
                ";
                $resultTv = $db->sql_query($SQLTV);
                $pvTreatment = '';
                $treatmentAmount = 0;
                while ($rowTv = $db->sql_fetchrow($resultTv)) {
                    $pvTreatment .=$rowTv['title'] . ', ';

                    $treatmentAmount += $rowTv['fees'];
                }
                $pv_treatment = rtrim($pvTreatment,', ');


                $pvRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $row['patient_visit_id'] );
                if($row['order_id'] == $order_id) {
                    $checkbox = "checked = 'checked'";
                    $disabledText = "disabled='disabled'";
                } else {
                    $checkbox = '';
                    $disabledText = '';
                }

                $inputRow = "<input class='checkEmployee' {$checkbox} type='checkbox' name='patientVisitId[]'
                             value='{$row['patient_visit_id']}' {$disabledText}>";
                $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

                $PvLink = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";
                $visitCode = "<a href='{$PvLink}' class=''>{$row['visit_code']}</a>";
                $treatmentAmount =number_format($treatmentAmount, 2);
                
                $rows .= "
                <tr>
                    <td>
                        {$inputRow}
                    </td>
                    <td>{$visitCode}</td>
                    <td>{$check_up_date}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['registration_no']}</td>
                    <td>{$pv_treatment}</td>
                    <td align='right'>{$treatmentAmount}</td>
                </tr>
                ";
            }
        }

        if($numRows > 0) {
        $text = "
        <form id='portalForm_PatientInfo' class='yform columnar' method='post' action='{$formAction}'>
            <div class='highlight p5'>
                <strong>(Note: Please check the Employee Names to create invoice)</strong>
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
                    <th>Visit Code</th>
                    <th>Date</th>
                    <th>Patient Name</th>
                    <th>Passport / ID</th>
                    <th>Treatment</th>
                    <th class='txtRight'>Amount</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='receipt' value='' />
        </form>
            <div class='mt10 mb10'>
                <button id='createInvoice' order_id='{$order_id}' class='button mt5 ml5 mb20'>Create Invoice</button>
                <!--<button id='createInvoiceReceipt' order_id='{$order_id}' receipt='1' class='button mt5 ml5 mb20'>Create Invoice & Receipt</button>-->
            </div>
        ";
        }
                //<input class='button createInvoice' type='submit' order_id='{$order_id}' value='Create Invoice' name='portalForm' />
                //<input class='button' type='submit' value='Create Order Items' name='portalForm' />

        return $text;
    }

    /**
    **/

    function getOrderItemPortalDisplay($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $text = '';

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
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
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
               ,CONCAT_WS(' ', first_name, middle_name, last_name ) AS patient_name
               ,patient_information_id
               ,invoice_id
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        AND invoice_id = ''
        GROUP BY patient_information_id
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

            $Sub_Total = 0;
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                if($rowOrderItem['invoice_id'] != '' && $rowOrderItem['invoice_id'] != 0) {
                    $checkbox = "checked = 'checked'";
                    $disabledText = "disabled='disabled'";
                } else {
                    $checkbox = '';
                    $disabledText = '';
                }

                $tdCheckBox = "
                <td>
                    <input type='checkbox' {$checkbox} {$disabledText} name='patientId[]' value='{$rowOrderItem['patient_information_id']}'>
                </td>
                ";

                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                AND patient_information_id = {$rowOrderItem['patient_information_id']}
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                $Lab .= "<tr>
                            {$tdCheckBox}
                            <td><b>{$rowOrderItem['patient_name']}</b></td>
                            <td><b>{$rowOrderItem['record_type']}:</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        $Lab .= "<li>{$rowList['item_title']}</li>";
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

        $sub_total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);
        $total_amount = number_format($Sub_Total - $row['discount'], 2);

        $total = "
        <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
            <td class='txtRight fontBigAndBold' colspan=4>Total : $total_amount</td>
        </tr>
        ";

        $formAction = "index.php?module=labsg_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $recordCountOverall = $fn->getRecordCount('order_item', "order_id = {$row['order_id']}", array('includeSiteId' => false));
        $recordCountInvGenerated = $fn->getRecordCount('order_item', "order_id = {$row['order_id']} AND invoice_id != ''", array('includeSiteId' => false));
        if ($recordCountOverall == $recordCountInvGenerated){
            $formAction = '';
            $invoiceBtn = "<button  disabled='disabled' class='button mt5 ml5 mb20 receiptButtonClass'>Generate Invoice</button>";
            $invoiceBtn = "<br>";
        } else {
            $invoiceBtn = "<button href='' id='generateInvoice' class='button mt5 ml5 mb20 receiptButtonClass'>Generate Invoice</button>";
        }

        /*$rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Order Item Summary</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div id='labsg_company#labsg_orderLink' class=''>
                        <form id='orderItemList' class='cpJqForm' method='post' action='{$formAction}'>
                        <table class='thinlist'>
                            <tr>
                                <th>Check/Uncheck</th>
                                <th>Name</th>
                                <th>Treatment(s)</th>
                                <th class='txtRight'>Fees</th>
                            </tr>
                            {$order_items_Details}
                            {$total}
                        </table>
                        <input type='hidden' name='order_id' value='{$row['order_id']}' />
                        <input type='hidden' name='callbackAfterSuccess' value='cpm.labsg.order.cbAfterGenerateInvoice' />
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {$invoiceBtn}
        ";
        */

        $SQLInvoice = "
        SELECT i.*
        FROM invoice i
        WHERE i.order_id = {$row['order_id']}
        AND i.status = 'Cancelled'
        ";
        $resultInvoice = $db->sql_query($SQLInvoice);
        $numRowsInvoice = $db->sql_numrows($resultInvoice);

        $SQLOI = "
        SELECT  record_type
               ,invoice_id
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        AND invoice_id = ''
        ";
        $resultOI = $db->sql_query($SQLOI);
        $numRowsOI = $db->sql_numrows($resultOI);

        if($numRowsInvoice > 0){
            if($numRowsOI > 0) {
            $text = "
            {$rows}
            ";
            }
        }

        return $text;
    }

    /**
     *
     */
    function getPrintYearWiseInvoiceRecord() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $site_id    = $fn->getReqParam('site_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

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

        $appendSql = "";
        if($month != "") {
            $appendSql .= "AND DATE_FORMAT(i.invoice_date, '%m') = '{$month}'";
        }
        
        if($year != "") {
            $appendSql .= "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql .= "AND i.site_id = '{$site_id}'";
        }

        if($start_date != "" && $end_date != "") {
            $appendSql .= "AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $SQLInvoiceMain = "
        SELECT i.invoice_id
        FROM invoice i
        WHERE i.status != 'Cancelled'
        {$appendSql}
        ";
        $resultInvoiceMain = $db->sql_query($SQLInvoiceMain);
        while($rowInvoiceMain = $db->sql_fetchrow($resultInvoiceMain)) {
            /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
            $pdf->AddPage();
            $invoice_code = $rowInvoiceMain['invoice_id'];

            $SQL = "
            SELECT ini.*
                    ,o.company_name
                    ,o.first_name
                    ,o.cust_address1
                    ,o.cust_address2
                    ,o.cust_address_state
                    ,o.cust_address_po_code
                    ,o.bill_type
                    ,gc.name AS shipping_address_country_code
                    ,o.shipping_address1
                    ,o.shipping_address_area
                    ,gco.name AS shipping_address_country
                    ,o.shipping_address_po_code
                    ,o.shipping_phone
                    ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS patient_name
                    ,o.order_id
                    ,o.company_id
                    ,i.invoice_date
                    ,ini.unit_price
                    ,i.invoice_code
                    ,i.invoice_terms
                    ,i.invoice_due_date
                    ,i.notes
                    ,i.discount
                    ,i.status
                    ,i.invoice_id
                    ,ROUND((ini.qty * ini.unit_price), 2) AS amount
                  ,(SELECT ROUND(SUM(init.qty * init.unit_price), 2) FROM invoice_item init
                   WHERE init.invoice_id = ini.invoice_id) AS sub_total
            FROM invoice_item ini
            LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN patient_visit pv ON (pv.patient_visit_id = o.patient_visit_id)
            LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN company c ON (c.company_id = p.company_id)
            LEFT JOIN geo_country gc ON (o.cust_address_country_code = gc.country_code)
            LEFT JOIN geo_country gco ON (o.shipping_address_country_code = gco.country_code)
            LEFT JOIN contact co ON (co.contact_id = o.contact_id)
            WHERE i.invoice_id = '{$invoice_code}'
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
                    <td align="center" style="font-weight:bold;">INVOICE</td>
                </tr>
            </table>
            ';

            $address2 = '';
            if ($company['bill_type'] == 'Individual') {
                $bill_to_name = strtoupper($company['first_name']);
                $address1 = strtoupper($company['shipping_address1']);
                if($company['shipping_address_area']) {
                    $address2 = '
                    <span>'.strtoupper($company['shipping_address_area']).',</span><br/>
                    ';
                }
                $addressCountry = strtoupper($company['shipping_address_country']);
                $addressPostal  = $company['shipping_address_po_code'];
            } else {
                $bill_to_name = strtoupper($company['company_name']);
                $address1 = strtoupper($company['cust_address1']);
                if($company['cust_address2']) {
                    $address2 = '
                    <span>'.strtoupper($company['cust_address2']).',</span><br/>
                    ';
                }
                $addressCountry = strtoupper($company['shipping_address_country_code']);
                $addressPostal  = $company['cust_address_state'];
            }

            $invoice_codeVal = substr($company['invoice_code'], 2);

            if($company['bill_type'] == 'Company'){
                $company['patient_name'] = $company['company_name'];
            }

            $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                        <tr>
                            <td width="62%" style="line-height:20px;"><br/>
                                <span><b>NAME :</b> '.$bill_to_name.'</span><br/><br/>
                                <span><b>ADDRESS :</b><br/></span>
                                <span>'.$address1.', </span><br/>'. $address2 .'
                                <span>'.$addressCountry.' - '.$addressPostal.'.</span>
                            </td>
                            <td width="38%" style="line-height:20px;"><br/>
                                <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$invoice_date.'</span><br/>
                                <span>Invoice Code : '.$company['invoice_code'].'</span>
                            </td>
                        </tr>
                    </table>
                    ';

            $tbl3 ='
            <table border="0" width="100%" cellpadding="4" style="font-size:15px; border:1px solid #000;">
                <thead>
                    <tr>
                        <th style="border:1px solid #000;" width="8%">S.NO</th>
                        <th style="border:1px solid #000;" width="77%">DESCRIPTION</th>
                        <th width="15%" style="text-align:right;border:1px solid #000;">AMOUNT</th>
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
            FROM invoice_item
            WHERE invoice_id = '{$invoice_code}'
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
                    SELECT ii.item_title
                          ,ii.unit_price
                          ,ii.order_item_id
                          ,oi.patient_name
                          ,oi.patient_visit_id
                          ,oi.patient_information_id
                          ,oi.nric
                    FROM invoice_item ii
                    LEFT JOIN (order_item oi) ON (ii.order_item_id = oi.order_item_id)
                    WHERE ii.invoice_id = {$company['invoice_id']}
                    AND ii.record_type = '{$rowOrderItem['record_type']}'
                    ";
                    $resultList  = $db->sql_query($SQLOrderItemList);
                    $resultList2 = $db->sql_query($SQLOrderItemList);
                    $numRowsList = $db->sql_numrows($resultList);
                    $numRowsCounter = 1;
                    $numRowsCounter1 = 1;

                    $tbl3 = $tbl3.'<tr>
                                        <td width="8%">'.$count.'</td>
                                        <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$rowOrderItem['record_type'].':</td>
                                        <td width="15%"></td>
                                    </tr>
                                   ';

                    if($numRowsList > 0){
                        $patient_name = '';
                        $count = 1;
                        while($rowList = $db->sql_fetchrow($resultList)){
                            if ($numRowsCounter == $numRowsList && $patient_name != '' && $rowList['patient_name'] == $patient_name) {
                               $tbl3 = $tbl3.'<tr>
                                                <td width="8%"></td>
                                                <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$count . '. ' . $rowList['item_title'].'</td>
                                                <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                              </tr>';
                            } else if ($patient_name != '' && $rowList['patient_name'] == $patient_name) {
                               $tbl3 = $tbl3.'<tr>
                                                <td width="8%"></td>
                                                <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'. $count . '. ' . $rowList['item_title'].'</td>
                                                <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                              </tr>';                          
                            } else {
                                $SQLTreatmentVisit = "
                                SELECT creation_date
                                FROM treatment_visit tv
                                WHERE tv.patient_visit_id = '{$rowList['patient_visit_id']}'
                                ";
                                $resultTv   = $db->sql_query($SQLTreatmentVisit);
                                $rowTv = $db->sql_fetchrow($resultTv);
                                $treatment_date = $dateUtil->formatDate($rowTv['creation_date'], 'DD-MM-YYYY');

                                $count = 1;
                                $tbl3 = $tbl3.'<tr>
                                                <td width="8%"></td>
                                                <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$rowList['patient_name'] . ' - ' . $rowList['nric'] . ' ['.$treatment_date.']'.'</td>
                                                <td width="15%"></td>
                                              </tr>'; 
                                $tbl3 = $tbl3.'<tr>
                                                <td width="8%"></td>
                                                <td style="border-left:1px solid #000;border-right:1px solid #000;" width="77%">'.$count . '. ' . $rowList['item_title'].'</td>
                                                <td width="15%" align="Right">'.number_format($rowList['unit_price'], 2).'</td>
                                              </tr>';
                                $patient_name = $rowList['patient_name'];
                                $count++;
                            }

                            $numRowsCounter++;
                        }
                    }

                    $tbl3 = $tbl3.'';

                    $Sub_Total += $rowOrderItem['Amount'];

                    $count++;
                }

                $Total_Amount = $Sub_Total - $company['discount'];
                $Sub_Total    = number_format($Sub_Total, 2);
                $discount     = number_format($company['discount'], 2);
                $Total_Amount = number_format($Total_Amount, 2);

                $tbl3 = $tbl3.'
                <tr>
                    <td colspan="2" style="text-align:right;border:1px solid #000;">SUB TOTAL</td>
                    <td style="text-align:right;border:1px solid #000;">'.$Sub_Total.'</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;border:1px solid #000;">DISCOUNT</td>
                    <td style="text-align:right;border:1px solid #000;">'.$discount.'</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;border:1px solid #000;">TOTAL AMOUNT</td>
                    <td style="text-align:right;border:1px solid #000;">'.$Total_Amount.'</td>
                </tr>
                ';
            }

            $tbl3 = $tbl3.'</tbody></table>';

            $tbl4 ='
            <table width="100%" cellpadding="0" style="font-size:15px;">
                <tr>
                    <td>If you wish to make payment by Cheque, please issue the cheque to MEDIWAY PTE LTD</td>
                </tr>
            <table>
            ';

            $pdf->writeHTML($tbl1, true, false, false, false, '');
            $pdf->writeHTML($tbl2, true, false, false, false, '');
            $pdf->ln(4);
            $pdf->writeHTML($tbl3, true, false, false, false, '');
            $pdf->writeHTML($tbl4, true, false, false, false, '');           
        }

        $download_title = 'Group-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }
}