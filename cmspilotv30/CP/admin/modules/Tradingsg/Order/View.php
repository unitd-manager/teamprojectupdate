<?
class CP_Admin_Modules_Tradingsg_Order_View extends CP_Common_Lib_ModuleViewAbstract
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
            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            $currency = strtoupper($row['currency']);
            $order_amount = $row['order_amount'];

            if($cpCfg['m.tradingsg.order.addGstAmountToOrderTotal']){
                $gsttaxperc   = $cpCfg['amtForGSTCalc'] ;
                $order_amount = $row['order_amount'] + ($row['order_amount'] * $gsttaxperc/100);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['order_id'])}
            {$listObj->getListDataCell($row['companyName'])}
            {$listObj->getListDataCell($creation_date)}
            {$listObj->getListDataCell($currency.'&nbsp;'.number_format(round($order_amount), 2))}
            {$listObj->getListDataCell($row['order_status'])}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Id', 'o.order_id')}
        {$listObj->getListHeaderCell('Company Name', 'c.companyName')}
        {$listObj->getListHeaderCell('Order Date', 'o.creation_date')}
        {$listObj->getListHeaderCell('Amount', '')}
        {$listObj->getListHeaderCell('Status', 'o.order_status')}
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

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['shipping_address_country']);

        $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD MM YYYY');

        $currency = strtoupper($row['currency']);

        $order_amount = $row['order_amount'];

        if($cpCfg['m.tradingsg.order.addGstAmountToOrderTotal']){
            $gsttaxperc   = $cpCfg['amtForGSTCalc'] ;
            $order_amount = $row['order_amount'] + ($row['order_amount'] * $gsttaxperc/100);
        }

        $discount = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $discount = $formObj->getTBRow('Discount', 'discount', $row['discount']);
        }

        $quote = "<a href='index.php?_topRm=order&module=tradingsg_quote&record_id={$row['quote_id']}&_action=edit'>{$row['quote_code']}</a>";

        $fielset1 = "
        {$formObj->getTBRow('Order Id', 'order_id', $row['order_id'], $expNoEdit)}
        {$formObj->getTBRow('Quote Code', 'quote_id', $quote, $expNoEdit)}
        {$formObj->getDateRow('Order Date', 'creation_date', $creation_date)}
        {$formObj->getTBRow('Amount', 'amount', $currency.'&nbsp;'. number_format(round($order_amount), 2), $expNoEdit)}
        {$discount}
        {$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        {$formObj->getTARow('Terms', 'invoice_terms', $row['invoice_terms'])}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
        ";

        //{$formObj->getTBRow('Country', 'company_country_name', $row['company_country_name'], $expNoEdit)}

        $fielset2 = "
        {$formObj->getTBRow('Company Name', 'shipping_first_name', $row['shipping_first_name'])}
        {$formObj->getTBRow('Address 1', 'shipping_address1', $row['shipping_address1'])}
        {$formObj->getTBRow('Address 2', 'shipping_address2', $row['shipping_address2'])}
        {$formObj->getTBRow('District/ Town', 'shipping_address_city', $row['shipping_address_city'])}
        {$formObj->getTBRow('State/ Zip', 'shipping_address_state', $row['shipping_address_state'])}
        {$formObj->getDDRowBySQL('Country', 'shipping_address_country', $sqlCountry, $row['shipping_address_country'], $expCountry)}
        ";

        $fielset3 = "
        {$formObj->getTBRow('Company Name', 'companyName', $row['companyName'], $expNoEdit)}
        {$formObj->getTBRow('Website', 'company_website', $row['company_website'], $expNoEdit)}
        {$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'company_phone', $row['company_phone'], $expNoEdit)}
        {$formObj->getTBRow('Office Address', 'company_address_flat', $row['company_address_flat'], $expNoEdit)}
        {$formObj->getTBRow('Street Address', 'company_address_street', $row['company_address_street'], $expNoEdit)}
        {$formObj->getTBRow('District / Town', 'company_address_town', $row['company_address_town'], $expNoEdit)}
        {$formObj->getTBRow('State / Zip', 'company_address_state', $row['company_address_state'], $expNoEdit)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Delivery Address', $fielset2)}
        {$formObj->getFieldSetWrapped('Customer Details', $fielset3)}
        {$formObj->getCreationModificationText($row)}
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

        $creation_date1 = $fn->getReqParam('creation_date_1');
        $creation_date2 = $fn->getReqParam('creation_date_2');
        $order_status   = $fn->getReqParam('order_status');
        $shipment_status   = $fn->getReqParam('shipment_status');
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

        $shipmentStatus = "";
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

        $text = "
        {$dirText}
        {$orgText}
        <td>
            {$formObj->getDateRangeRow('Creation Date:', 'creation_date', $creation_date1, $creation_date2)}
        </td>

        <!--<td class='fieldValue'>
            <select name='shipping_address_country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $fn->getGeoCountrySQL(), $shipping_address_country_code)}
            </select>
        </td>-->
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

        $links ='';
        if ($cpCfg['m.ecommerce.order.showAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'tradingsg_order', 'attachment', $row);
        }

        $printTextButton ='';


        if ($cpCfg['m.tradingsg.order.showReceiptButton']){
            $formActionReceipt = "index.php?module=tradingsg_order&_spAction=generateReceiptForm&order_id={$row['order_id']}&showHTML=0";

            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
        }

        if ($cpCfg['m.tradingsg.order.showInvoiceButton']){
            $formActionInvoice = "index.php?module=tradingsg_order&_spAction=generateInvoiceForm&order_id={$row['order_id']}&showHTML=0";
            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$formActionInvoice}' id='generateInvoice'>CREATE INVOICE</a>
            </div>
            ";
        }

        $print ="
        <div class='floatbox actionBtnsDetail'>
	        <div class='orderbtnbackground floatbox'>
            {$actionButtons}
	        </div>
        </div>
        ";

        if ($cpCfg['m.tradingsg.order.showInvoicePortalDisplay']){
            //$links .= $displayLinkData->getLinkPortalMain('tradingsg_order', 'tradingsg_invoiceLink', 'Invoices Linked', $row);
            $links .= $this->getInvoicePortalDisplay($row);
        }

        if ($cpCfg['m.tradingsg.order.showReceiptPortalDisplay']){
            //$links .= $displayLinkData->getLinkPortalMain('tradingsg_order', 'tradingsg_receiptLink', 'Receipt Linked', $row);
            $links .= $this->getReceiptPortalDisplay($row);
        }

            $summaryTableOrder = $this->getSummaryInOrder($row);

        $orderItem = '';
        if ($cpCfg['m.tradingsg.order.showOrderItemDisplay']){
            $orderItem = $displayLinkData->getLinkPortalMain('tradingsg_order', 'ecommerce_orderItemLink', 'Order Items', $row);
        }

        $text = "
        {$print}
        {$summaryTableOrder}
        {$orderItem}
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
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        if($cpCfg['m.tradingsg.order.addGstAmountToOrderTotal']){
            $gsttaxperc   = $cpCfg['amtForGSTCalc'] ;
            $orderAmt = $row['order_amount'] + ($row['order_amount'] * $gsttaxperc/100);
            $overallBalanceAmt     = number_format($orderAmt - $row['receipt_amount'], 2);
        }

        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);

            $rows = "
            <table class='summaryAmountDetails'>
                <tr class= 'summaryTitle'>
                    <th>SUMMARY</th>
                    <th></th>
                </tr>
                <tr>
                    <td class='totalOrderAmountLabel'>TOTAL ORDER AMOUNT</td>
                    <td class='totalOrderAmountValue'>{$orderAmt}</td>
                <tr>
                    <td class='totalOrderAmountLabel'>TOTAL INVOICE RAISED</td>
                    <td class='totalInvoiceAmountValue'>{$invoiceAmt}</td>
                <tr>
                <tr>
                    <td class='totalOrderAmountLabel'>AMOUNT PAID</td>
                    <td class='totalReciptAmountValue'>{$receiptAmt}</td>
                <tr>
                <tr>
                    <td class='totalOrderAmountLabel'>OUTSTANDING INVOICE</td>
                    <td class='totalOutstandingInvoiceAmtValue'>{$outstandingInvoiceAmt}</td>
                <tr>
                <tr>
                    <td class='totalOrderAmountLabel'>OVERALL BALANCE</td>
                    <td class='totalOverallAmountValue'>{$overallBalanceAmt}</td>
                <tr>
            </table>
            ";

        $text = "
        {$rows}
        ";

        return $text;

    }
    function getPrintInvoiceRecordOld() {
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


        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
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
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(80, 35);
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
                $pdf->Cell(95, 8, $row['company_name'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, $row['company_name'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $billingAddressFlat, 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $row['address_flat'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $billingAddressStreet, 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $row['address_street'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $billingAddressTown, 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $row['address_town'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 5, $billingAddressCountry . ' - ' . $billingAddressState, 'LR', 0, 'L', 1);
                $pdf->Cell(95, 5, $row['address_country'] . ' - ' . $row['address_state'], 'LR', 0, 'L', 1);
                $pdf->Ln();

                $terms = $row['invoice_terms'];

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
                $pdf->Ln(12);

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(22,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(90,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(15,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(15,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(24,8,"UP",1,0, 'C', 1);
                $pdf->Cell(25,8,"AMOUNT(" .$row['currency'] . ")" ,1,0, 'C', 1);
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
            $notes = $row['notes'];
        }
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, "SUB TOTAL {$currency}", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

            $printTaxName = $cpCfg['printTaxName'] ;

            $pdf->SetFillColor(255,255,255);

            $pdf->Cell(166, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($gstvalue, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            //$totalvalueRounded = round($totalvalue);
            $totalvalueRounded = $totalvalue;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(166, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($totalvalueRounded, 2), 1, 0, 'R', 1);
            $pdf->Ln(20);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(150, 8, 'TERMS: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($terms, 180, 55, 'L', 'T', 0);
            $pdf->Ln(4);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(150, 8, 'NOTE: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($notes, 180, 55, 'L', 'T', 0);
            $pdf->Ln(4);

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
    function getPrintInvoiceRecord() {
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

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('Invoice');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

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

        // ---------------------------------------------------------QUOTE QUERY START

        // set font
        $pdf->SetFont('Courier','B',10);
        // add a page
        $pdf->AddPage();
        //$pdf->ln(5);


        $invoiceHeading = '';

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoice_type = $fn->getReqParam('invoice_type');
        //$invoiceRec = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
       $SQL = "
        SELECT ini.*
              ,o.*
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
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY pg.sort_order ASC, p.title
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $row = $db->sql_fetchrow($result);

        //$pdf->ln(10);

                /* Company Details*/
                $companyName = '';
                $billingAddressFlat = '';
                $billingAddressStreet = '';
                $billingAddressTown = '';
                $billingAddressState = '';
                $billingAddressCountry = '';

                if ($row['cust_first_name'] != ''
                 || $row['cust_address1'] != ''
                 || $row['cust_address2'] != ''
                 || $row['cust_address_city'] != ''
                 || $row['cust_address_state'] != ''
                 || $row['cust_address_country_code'] != '')
                {
                    $companyName            = $row['cust_first_name'];
                    $billingAddressFlat     = $row['cust_address1'];
                    $billingAddressStreet   = $row['cust_address2'];
                    $billingAddressTown     = $row['cust_address_city'];
                    $billingAddressState    = $row['cust_address_state'];
                    $billingAddressCountry  = $row['cust_address_country_code'];
                } else {
                    $companyName            = $row['shipping_first_name'];
                    $billingAddressFlat     = $row['shipping_address1'];
                    $billingAddressStreet   = $row['shipping_address2'];
                    $billingAddressTown     = $row['shipping_address_city'];
                    $billingAddressState    = $row['shipping_address_state'];
                    $billingAddressCountry  = $row['shipping_address_country'];
                }

        $creationDate   = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
        $address = '<table border="1" width="100%" cellpadding="3">
                      <tr>
                            <td width="12%" height="20">Bill To:</td>
                            <td width="33%" style="border-bottom:1pt solid black;"> '.$companyName.'</td>
                            <td width="25%"></td>
                            <td width="30%">TAX INVOICE</td>
                     </tr>
                     <tr>
                            <td width="45%" height="20" style="border-bottom:1pt solid black;">'.$billingAddressFlat.'</td>
                            <td width="25%"></td>
                            <td width="30%">No. <font style="color:red;">'.$row['invoice_code'].'</font></td>
                     </tr>
                     <tr>
                            <td width="45%" height="20" style="border-bottom:1pt solid black;">'.$billingAddressStreet.'</td>
                            <td width="25%"></td>
                            <td width="8%">Terms:</td>
                            <td width="22%" style="border-bottom:1pt solid black;"></td>
                     </tr>
                     <tr>
                            <td width="45%" height="20" style="border-bottom:1pt solid black;">'.$billingAddressTown.'</td>
                            <td width="25%"></td>
                            <td width="8%">Date:</td>
                            <td width="22%" style="border-bottom:1pt solid black;">'.$creationDate.'</td>
                     </tr>
                     <tr>
                            <td height="20" style="border-bottom:1pt solid black;">'.$billingAddressCountry . ' - ' . $billingAddressState.'</td>
                     </tr>
                </table>';


        $orderItem ='<table border="1" cellpadding="5" width="100%">';

        $orderItem = $orderItem.'
                    <thead>
                    <tr bgcolor="#FDCA9C">
                        <th width="10%" height="30" align="center">QTY</th>
                        <th align="center" width="40%">DESCRIPTION</th>
                        <th width="10%" align="center">UOM</th>
                        <th width="20%"align="center">UNIT PRICE</th>
                        <th width="20%" align="center">AMOUNT ('.$row['currency'].') </th>
                    </tr>
                    </thead>';

        while ($rowz = $db->sql_fetchrow($result2)) {
            $orderItem = $orderItem.'<tr nobr="true">
                                        <td width="10%" height="30" align="center">'.$rowz['qty'].'</td>
                                        <td align="left" width="40%">'.$rowz['product_title'].'</td>
                                        <td width="10%" align="center">'.$rowz['unit'].'</td>
                                        <td width="20%" align="right">'.$rowz['unit_price'].'</td>
                                        <td width="20%"  align="right">'.$rowz['amount'].'</td>
                                    </tr>';
        }
        $sub_total = $row['sub_total'];
        $notes = $row['notes'];
        $printTaxName = $cpCfg['printTaxName'] ;
        $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
        $gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
        $totalvalue = $gstvalue + $row['sub_total'];

        $orderItem = $orderItem.'<tr>
                                      <td colspan="4" align="right">SUB TOTAL '.$row['currency'].'</td>
                                      <td align="right">'.$row['sub_total'].'</td>
                                  </tr>
                                  <tr>
                                      <td colspan="4" align="right">ADD: '.$printTaxName.' '.$gsttaxvalue.'</td>
                                      <td align="right">'.number_format($gstvalue, 2).'</td>
                                  </tr>
                                  <tr>
                                      <td colspan="4" align="right">TOTAL</td>
                                      <td align="right">'.number_format($totalvalue, 2).'</td>
                                  </tr>';

        $orderItem = $orderItem.'</table>';

        $notesItem = '<table border="0" width="100%">
                        <tr>
                            <td>'.$cpCfg['cp.addInformationPdf'].' "'.$cpCfg['cp.companyName'].'".</td>
                        </tr>
                      </table>';
        $signBox  = '<table border="0" width="100%">
                        <tr>
                            <td width="38%" height="80" style="border-bottom:1pt solid black;"></td>
                            <td width="20%"></td>
                            <td width="42%" style="border-bottom:1pt solid black;" align="top">for '.$cpCfg['cp.companyName'].'</td>
                        </tr>
                        <tr>
                            <td width="38%">Company Stamp & Signature</td>
                            <td width="20%"></td>
                            <td width="42%"></td>
                        </tr>
                    </table>';
        //<td width="42%">for '.$cpCfg['cp.companyName'].'</td>

        $pdf->writeHTML($address, true, false, false, false, '');
        $pdf->writeHTML($orderItem, true, false, false, false, '');
        $pdf->writeHTML($notesItem, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($signBox, true, false, false, false, '');
        $pdf->Output('example_003.pdf', 'I');

    }

    /**
     *
     */
    function getPrintInvoiceRecordFPDF() {
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


        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                //$pdf->Image('images/logo-print.gif',10,5,45);
                //$pdf->SetFont('Courier','BU',11);
                //$pdf->SetXY(50, 8);
                //$pdf->Cell(10, 5, "TRADELINK EAST ASIA (S) PTE LTD", 0, 0, 'C');
                $creationDate   = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
                $invoiceDueDate = $fn->getCPDate($row['invoice_due_date'], 'd-m-Y');
                $deliveryDate   = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
				$currency = $row['currency'];

				$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
				$gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
				$totalvalue = $gstvalue + $row['sub_total'];

                /* Company address */
                //Address to be got from settings
                /* ---Header--- */
                $pdf->SetFont('Courier','B',18);
                $pdf->SetXY(80, 0);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName'], 0, 0, 'C');
                $pdf->Ln(5);
                $pdf->SetXY(60, 06);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(25, 12);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                //$pdf->Ln(5);
                $pdf->SetXY(115, 12);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                //$pdf->Ln(5);
                $pdf->SetXY(52, 18);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->SetXY(96, 18);
                $pdf->Cell(50, 20, '/');
                //$pdf->Ln(5);
                $pdf->SetXY(100, 18);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);
                $pdf->Ln(20);

                /* Header */
                /*$pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(80, 35);
                $pdf->Cell(50, 20, "INVOICE", 0, 0, 'C');
                $pdf->SetFont('Courier','B',11);
                $pdf->SetX(130);
                $pdf->Cell(31, 20, "DATE : " . $creationDate, 0, 0, 'L');
                $pdf->Ln(20);*/

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
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(95,8,"Bill To:", 0, 0, 'L', 1);
                //$pdf->Cell(95,8,"DELIVERY TO", 0, 0, 'L', 1);
                //$pdf->Ln();
                //$pdf->SetFillColor(255,255,255);
                $pdf->SetXY(30, 37);
                $pdf->Cell(70, 6, $row['company_name'], 'B', 0, 'L', 1);
                $pdf->Cell(68, 6, 'TAX INVOICE', 0, 0, 'R', 1);
                //$pdf->Cell(95, 8, $row['company_name'], 0, 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetXY(10, 44);
	            $pdf->Cell(90, 6, $billingAddressFlat, 'B', 0, 'L', 1);
                $pdf->Cell(50, 6, 'No.', 0, 0, 'R', 1);
                $pdf->Cell(50, 6, $row['invoice_code'], 0, 0, 'L', 1);
	            //$pdf->Cell(95, 5, $row['address_flat'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetXY(10, 51);
	            $pdf->Cell(90, 6, $billingAddressStreet, 'B', 0, 'L', 1);
                $pdf->SetXY(100, 53);
                $pdf->Cell(57, 6, 'Terms:', 0, 0, 'R', 1);
                $pdf->SetXY(156, 51);
                $pdf->Cell(44, 6, '', 'B', 0, 'L', 1);
	            //$pdf->Cell(95, 5, $row['address_street'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetXY(10, 58);
	            $pdf->Cell(90, 6, $billingAddressTown, 'B', 0, 'L', 1);
                $pdf->SetXY(100, 62);
                $pdf->Cell(57, 6, 'Date:', 0, 0, 'R', 1);
                $pdf->SetXY(156, 60);
                $pdf->Cell(44, 6, $creationDate, 'B', 0, 'C', 1);
	            //$pdf->Cell(95, 5, $row['address_town'], 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetXY(10, 65);
	            $pdf->Cell(90, 6, $billingAddressCountry . ' - ' . $billingAddressState, 'B', 0, 'L', 1);
	            //$pdf->Cell(95, 5, $row['address_country'] . ' - ' . $row['address_state'], 'LR', 0, 'L', 1);
                $pdf->Ln(10);

                $terms = $row['invoice_terms'];

                /* Invoice Details*/
                /*$pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"INVOICE NO :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(47.5, 8, $row['invoice_code'], 1, 0, 'L', 1);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(47.5,8,"DUE DATE :",1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(47.5, 8, $invoiceDueDate, 1, 0, 'L', 1);
                $pdf->Ln(12);*/

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                //$pdf->Cell(22,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(18,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(90,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(18,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(30,8,"UP",1,0, 'C', 1);
                $pdf->Cell(35,8,"AMOUNT(" .$row['currency'] . ")" ,1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            //$pdf->Cell(22, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(18, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(90, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(18, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(30, 8, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Cell(35, 8, $row['amount'], 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
            $notes = $row['notes'];
        }
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(156, 8, "SUB TOTAL {$currency}", 1, 0, 'R', 1);
            $pdf->Cell(35, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

	        $printTaxName = $cpCfg['printTaxName'] ;

            $pdf->SetFillColor(255,255,255);

            $pdf->Cell(156, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(35, 8, number_format($gstvalue, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            //$totalvalueRounded = round($totalvalue);
            $totalvalueRounded = $totalvalue;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(156, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(35, 8, number_format($totalvalueRounded, 2), 1, 0, 'R', 1);
			$pdf->Ln(10);
            $pdf->SetFont('Courier','B',8);
            $pdf->drawTextBox($cpCfg['cp.addInformationPdf'], 110, 55, 'L', 'T', 0);
            $pdf->SetFont('Courier','B',9);
            $pdf->SetXY(133, 116);
            $pdf->Cell(31, 20, "for " . $cpCfg['cp.companyName'], 0, 0, 'L');
            //$pdf->Cell(120, 10, $cpCfg['cp.addInformationPdf'], 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(75, 24, '', 'B', 0, 'L', 1);
            $pdf->Ln();
            $pdf->Cell(75, 4, 'Company Stamp & Signature');
            $pdf->SetXY(132, 160);
            $pdf->Cell(68, 0, '', 'B', 0, 'R', 1);
            /*$pdf->SetFont('Courier','B',11);
            $pdf->Cell(150, 8, 'TERMS: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($terms, 180, 55, 'L', 'T', 0);
            $pdf->Ln(4);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(150, 8, 'NOTE: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($notes, 180, 55, 'L', 'T', 0);
            $pdf->Ln(4);*/

	        /* Creation of media record of the invoice */
	        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
	        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

	        $outputFileName = $outputPath . '/' . $file_name;
	        //$pdf->Output($outputFileName , "F");
			$pdf->Output();

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
        ";

        return $text;
    }

    /**
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
        $leftJoin  = "";
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
        {$leftJoin}
        WHERE i.order_id = {$row['order_id']}
          AND i.invoice_type = 'Client'
        ORDER BY i.invoice_id
        ";

        $result   = $db->sql_query($SQL);
        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = '';
        $add_registration_fee = '';
        $invoice_hist_amount  = '';

        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';

            $urlPrint = "index.php?_topRm=finance&module=tradingsg_order&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_code']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingsg_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] == 'Paid'){
                $selectedValuePaid =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Due'){
                $selectedValueDue =  "selected='selected'";
            }
            if($rowInvoice['status'] == 'Cancelled'){
                $selectedValueCancel =  "selected='selected'";
            }

            if($rowInvoice['status'] != 'Cancelled' && $invoice_code != $rowInvoice['invoice_code']){
                $total += $rowInvoice['invoice_amount'];
            }
            if($invoice_code == '' || $invoice_code != $rowInvoice['invoice_code']){

                /* Half way done. Need to do submit functioanlity. Move $editRow = ''; from below to this comment line */
                $editRow = '<td></td>';
                if ($rowInvoice['status'] == 'Due'
                 || $rowInvoice['status'] == ''
                 || $rowInvoice['status'] == 'Partial Payment'
                ) {
                    $editURL = "index.php?_topRm=finance&module=tradingsg_order&_spAction=editInvoiceForm&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$row['order_id']}";
                    $editRow = "<td><a href='{$editURL}' id='editInvoice'>Edit</a></td>";
                }

                $cancelInvoiceLink = '';
                if ($rowInvoice['status'] != 'Cancelled') {
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_status='{$rowInvoice['status']}' invoice_code='{$rowInvoice['invoice_code']}'>Cancel Invoice</a>";
                }

                $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');
                $rows .= "
                <tr>
                    <td>{$rowInvoice['invoice_code']}</td>
                    <td>{$rowInvoice['status']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$rowInvoice['invoice_amount']}</td>
                    <td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                    <!--<td><a href='{$mediaLink}'>Print Invoice</a></td>-->
                    <td>{$cancelInvoiceLink}</td>
                    {$editRow}
                </tr>
                ";
            }

            $invoice_code = $rowInvoice['invoice_code'];
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Status</th>
        <th>Invoice Date</th>
        <th>Amount</th>
        <th>Print</th>
        <th>Cancel</th>
        <th>Edit</th>
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

            $urlPrint = "index.php?_topRm=finance&module=tradingsg_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$row['order_id']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code='{$rowReceipt['receipt_code']}'>Cancel Receipt</a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "Cancelled";
            }

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
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
        <th>Receipt Amount</th>
        <th>Print</th>
        <th>Cancel</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <h2 class='mt20'>Receipt(s)</h2>
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
              ,i.creation_date
              ,q.delivery_date
              ,q.delivery_location
              ,i.invoice_id AS invoice_id_main
              ,i.invoice_code
              ,i.invoice_amount
              ,q.quote_code
              ,q.currency
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
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE r.receipt_code = '{$receipt_code}'
          AND i.order_id = {$order_id}
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
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['receipt_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
				$currency = $row['currency'];

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
					$billingAddressFlat     = $row['address_flat'];
					$billingAddressStreet   = $row['address_street'];
					$billingAddressTown     = $row['address_town'];
					$billingAddressState    = $row['address_state'];
					$billingAddressCountry  = $row['address_country'];
				}

                /* Address of the Company */
                $pdf->SetXY(10, 50);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 63, 72, 30, 'D');
                $pdf->SetXY(10, 56);
                //$pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['company_name']);
                $pdf->SetXY(10, 61);
                $pdf->Cell(50, 20, $billingAddressFlat);
                $pdf->SetXY(10, 66);
                $pdf->Cell(50, 20, $billingAddressStreet);
                $pdf->SetXY(10, 71);
                $pdf->Cell(50, 20, $billingAddressTown);
                $pdf->SetXY(10, 76);
                $pdf->Cell(50, 20, $billingAddressState . ' ' . $billingAddressCountry);
                $pdf->SetXY(10, 81);
                $pdf->Ln(20);

                /* Recepit code and date */
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetXY(135, 50);
                $pdf->Cell(50, 20, $code );
                $pdf->Ln(5);

                $pdf->SetX(135);
                $date = $fn->getCPDate($row['receipt_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->Cell(50, 20, $date);
                $pdf->Ln(45);

                /* List of order items header */
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(135,8,"Description",1,0, 'L', 1);
                $pdf->Cell(55,8,"Amount(" . $row['currency'] . ")",1,0, 'R', 1);
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
            FROM invoice i
            WHERE i.invoice_id = {$row['invoice_id_main']}
            ";
            $resultInvAmount = $db->sql_query($sqlInvoiceAmount);
            $rowInvoiceAmount= $db->sql_fetchrow($resultInvAmount);

            $total_amount += $rowInvoiceAmount['invoice_amount'];

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
            $pdf->Cell(55, 8, number_format($total_amount, 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total amount paid earlier */
            $pdf->Cell(135, 8,'Amount already Paid ', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($previous_paid_amount, 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total amount paid */
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(135, 8,'Amount Received Now', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($receipt_amount, 2), 1, 0, 'R');
            $pdf->Ln();

            /* Total balance amount to be paid */
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(135, 8,'Balance Amount to be Paid', 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($balance_due, 2), 1, 0, 'R');
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
			$pdf->Output();

    }

}