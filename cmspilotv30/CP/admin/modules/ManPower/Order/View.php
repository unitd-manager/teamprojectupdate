<?
class CP_Admin_Modules_ManPower_Order_View extends CP_Common_Lib_ModuleViewAbstract
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
            $creation_date = $fn->getCPDate($row['creation_date'], 'm-d-Y');
            $currency = strtoupper($row['currency']);
            $no_of_hours = '';

            if($row['position_type'] == 'Hourly'){
                $sqlClient = "SELECT SUM(no_of_hours) AS no_of_hours
                              FROM `invoice`
                              WHERE invoice_type = 'Client'
                              AND order_id = {$row['order_id']}
                              AND status != 'Cancelled'
                ";
                $resultClient = $db->sql_query($sqlClient);
                $rowClient    = $db->sql_fetchrow($resultClient);

                $no_of_hours  = $rowClient['no_of_hours'];
            }
            $order_amount = $row['order_amount'];

            //{$listObj->getListDataCell($currency.'&nbsp;'.number_format(round($order_amount), 2))}

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['order_id'])}
            {$listObj->getListDataCell($row['client_company_name'])}
            {$listObj->getListDataCell($row['candidate_name'])}
            {$listObj->getListDataCell($creation_date)}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getListDataCell($row['position_type'])}
            {$listObj->getListDataCell($no_of_hours,'right')}
            {$listObj->getListDataCell('$'.number_format($order_amount, 2),'right')}
            {$listObj->getListDataCell($row['order_status'])}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Id', 'o.order_id')}
        {$listObj->getListHeaderCell('Company Name', 'client_company_name')}
        {$listObj->getListHeaderCell('Candidate Name', 'client_company_name')}
        {$listObj->getListHeaderCell('Order Date', 'o.creation_date')}
        {$listObj->getListHeaderCell('Position', 'o.position')}
        {$listObj->getListHeaderCell('Position Type', 'o.position_type')}
        {$listObj->getListHeaderCell('Hours', '','headerCenter')}
        {$listObj->getListHeaderCell('Amount', '','headerRight')}
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['shipping_address_country']);

        $creation_date = $dateUtil->formatDate($row['creation_date'], 'MM-DD-YYYY');

        $currency = strtoupper($row['currency']);

        $order_amount = $row['order_amount'];

        /*$sqlAmount = "
        SELECT SUM(invoice_amount)As order_amount
        FROM invoice
        WHERE invoice_type = 'Client'
        AND order_id = {$row['order_id']}
        AND status != 'Cancelled'
        ";
        $resultAmont = $db->sql_query($sqlAmount);
        $rowAmount  = $db->sql_fetchrow($resultAmont);*/

        $discount = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $discount = $formObj->getTBRow('Discount', 'discount', $row['discount']);
        }

        if ($row['apply_commission'] == 1){
             $apply_commission_stat = 'Yes';
        }
        else{
             $apply_commission_stat = 'No';
        }

        $referral_Display = '';
        if($row['apply_commission'] == 1){
            $referral_Display ="
            {$formObj->getTBRow('Apply commission', 'apply_commission', $apply_commission_stat, $expNoEdit)}
            {$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage', $row['commission_percentage'])}
            ";
        }

        $position = "
            <div class='positionTitle'>
             {$formObj->getTBRow('Position', 'position', $row['position'], $expNoEdit)}
            </div>
            ";

        $expProjectCode = array('isEditable' => 0);

        $project_ref = '';
        if ($row['project_id'] > 0) {

            $projectRow = $fn->getRecordRowByID('project', 'project_id', $row['project_id']);
            
            $projectLink   = "index.php?_topRm=project&module=manPower_project&project_id={$row['project_id']}&_action=detail";
            $linkToProject = "<a href='{$projectLink}' target='_blank'><u>{$projectRow['project_code']}</u></a>";
            $project_ref   = $formObj->getTBRow('Project Ref#', 'project_ref', $linkToProject, $expProjectCode);

        }

        //{$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        //$quote = "<a href='index.php?_topRm=order&module=tradingin_quote&record_id={$row['quote_id']}&_action=edit'>{$row['quote_code']}</a>";
        //{$formObj->getTBRow('Quote Code', 'quote_id', $quote, $expNoEdit)}

        //{$formObj->getYesNoRRow('Apply commission', 'apply_commission', $row['apply_commission'])}
        //{$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage', $row['commission_percentage'])}
        $fielset1 = "
        {$formObj->getTBRow('Order Id', 'order_id', $row['order_id'], $expNoEdit)}
        {$project_ref}
        {$position}
        {$formObj->getTBRow('Candidate Name', 'candidate_name', $row['candidate_name'], $expNoEdit)}
        {$formObj->getTBRow('Position Type', 'position_type', $row['position_type'], $expNoEdit)}
        {$formObj->getTBRow('Status', 'order_status',$row['order_status'], $expNoEdit)}
        {$formObj->getTBRow('Work State', 'work_state', $row['work_state'], $expNoEdit)}
        {$formObj->getTBRow('Order Date', 'creation_date', $creation_date, $expNoEdit)}
        {$formObj->getTBRow('Amount', 'amount', '$'. number_format($order_amount, 2), $expNoEdit)}
        {$discount}
        {$formObj->getTBRow('Client Hourly Rate', 'client_hourly_rate', $row['client_hourly_rate'], $expNoEdit)}
        {$formObj->getTBRow('Candidate Hourly Rate', 'candidate_hourly_rate', $row['candidate_hourly_rate'], $expNoEdit)}
        {$referral_Display}
        {$formObj->getTARow('Terms', 'invoice_terms', $row['invoice_terms'])}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
        ";

        $fielset2 = "
        {$formObj->getTBRow('Company Name', 'client_company_name', $row['client_company_name'], $expNoEdit)}
        {$formObj->getTBRow('Website', 'company_website', $row['company_website'], $expNoEdit)}
        {$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'company_phone', $row['company_phone'], $expNoEdit)}
        {$formObj->getTBRow('Office Address', 'company_address_flat', $row['company_address_flat'], $expNoEdit)}
        {$formObj->getTBRow('State', 'company_address_street', $row['company_address_street'], $expNoEdit)}
        {$formObj->getTBRow('District / Town', 'company_address_town', $row['company_address_town'], $expNoEdit)}
        {$formObj->getTBRow('Zip Code', 'postal_code', $row['postal_code'], $expNoEdit)}
        ";
        //{$formObj->getTBRow('Country', 'company_country_name', $row['company_country_name'], $expNoEdit)}


        $fielset3 = "
        {$formObj->getTBRow('Company Name', 'shipping_first_name', $row['shipping_first_name'])}
        {$formObj->getTBRow('Address 1', 'shipping_address1', $row['shipping_address1'])}
        {$formObj->getTBRow('Address 2', 'shipping_address2', $row['shipping_address2'])}
        {$formObj->getTBRow('District/ Town', 'shipping_address_city', $row['shipping_address_city'])}
        {$formObj->getTBRow('State/ Zip', 'shipping_address_state', $row['shipping_address_state'])}
        {$formObj->getDDRowBySQL('Country', 'shipping_address_country', $sqlCountry, $row['shipping_address_country'], $expCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Delivery Address', $fielset3)}
        {$formObj->getFieldSetWrapped('Customer Details', $fielset2)}
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

        $order_date1 	 				= $fn->getReqParam('order_date_1');
        $order_date2	 				= $fn->getReqParam('order_date_2');
        $order_status    				= $fn->getReqParam('order_status');
        $shipment_status 				= $fn->getReqParam('shipment_status');
        $shipping_address_country_code  = $fn->getReqParam('shipping_address_country_code');
        $position                       = $fn->getReqParam('position');
        $position_type                  = $fn->getReqParam('position_type');
        $sqlPosition        = $fn->getValueListSQL('opportunityPosition','value');
        $sqlPosition_type   = $fn->getValueListSQL('opportunityPositionType');

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
            {$formObj->getDateRangeRow('Order Date:', 'order_date', $order_date1, $order_date2)}
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
        <td>
            <select name='position'>
                <option value=''>Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>
        <td>
            <select name='position_type'>
                <option value=''>Position Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition_type, $position_type)}
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
        $Referrallinks = "";

        $links ='';
        if ($cpCfg['m.ecommerce.order.showAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'manPower_order', 'attachment', $row);
        }

        $printTextButton ='';


        //$formActionReceipt = "index.php?module=manPower_order&_spAction=generateReceiptForm&order_id={$row['order_id']}&showHTML=0";

        /*$actionButtons .="
        <div class='float_right button mb5'>
            <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
        </div>
        ";*/

        $formActionInvoice = "index.php?module=manPower_order&_spAction=generateInvoiceForm&order_id={$row['order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$formActionInvoice}' id='generateInvoice'>CREATE INVOICE</a>
        </div>
        ";

        if($row['record_type'] == 'POS') {
            $urlPrint  = "index.php?_topRm=pos&module=tradingsg_pos&_spAction=printBill&printOnly=1&orderNo={$row['order_id']}&showHTML=0";
            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$urlPrint}' target='_blank'>PRINT INVOICE</a>
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

        $invoicePortalClient = $this->getInvoicePortalDisplay($row);

        $ReceiptPortalClient = $this->getReceiptPortalDisplay($row);

        $links .= $this->getInvoicePortalDisplayCandidate($row);

        $links .= $this->getReceiptPortalDisplayCandidate($row);

        $Referralinvoice = $this->getReferralInvoicePortalDisplay($row);

        $referralReceipt = $this->getReceiptPortalDisplayReferral($row);

        $formActionReceiptClient = "index.php?module=manPower_order&_spAction=generateReceiptFormClient&order_id={$row['order_id']}&showHTML=0";

        $receiptButtonClient ="
        <div class='float_right button'>
            <a href='{$formActionReceiptClient}' id='generateReceiptClient' style='color: #174a6f'>CREATE CLIENT RECEIPT</a>
        </div>
        ";

        $formActionReceiptCandidate ="index.php?module=manPower_order&_spAction=generateReceiptFormCandidate&order_id={$row['order_id']}&showHTML=0";

        $receiptButtonCandidate ="
        <div class='float_right button'>
            <a href='{$formActionReceiptCandidate}' id='generateReceiptCandidate' style='color: #174a6f'>CREATE CANDIDATE RECEIPT</a>
        </div>
        ";

        $formActionReceiptReferral = "index.php?module=manPower_order&_spAction=generateReceiptFormReferral&order_id={$row['order_id']}&showHTML=0";

        $receiptButtonReferral ="
        <div class='float_right button'>
            <a href='{$formActionReceiptReferral}' id='generateReceiptReferral' style='color: #174a6f'>CREATE REFERRAL RECEIPT</a>
        </div>
        ";

        $summaryTableOrder = '' ;
        if($row['record_type'] != 'POS') {
            $summaryTableOrder = $this->getSummaryInOrder($row);
        }

        $referralPortal ='';
        if($row['referral_id'] !='' && $row['apply_commission'] == 1){
            $referralPortal ="
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='orderRighpanelClient'>Referral Commissions</div>
                    {$receiptButtonReferral}
                </div>
            </div>
            {$Referralinvoice}
            <div id='receiptReferral'>{$referralReceipt}</div>
           ";
        }

        $orderItem = '';

        $text = "
        {$print}
        {$summaryTableOrder}
        <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='orderRighpanelClient'>Client Payments</div>
                    {$receiptButtonClient}
                </div>
        </div>
        {$invoicePortalClient}
        {$ReceiptPortalClient}
        <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='orderRighpanelClient'>Candidate Payments</div>
                    {$receiptButtonCandidate}
                </div>
        </div>
        {$links}
        {$referralPortal}
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
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                AND i.invoice_type = 'Client'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                AND r.receipt_type = 'Client'
                )AS receipt_amount
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        //$overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

            $currency_symbol = '$';

            $rows = "
            <div class='linkPortalWrapper'>
                <div class='header' expanded='1'>
                    <div class='floatbox'>
                        <div class='float_left'>SUMMARY</div>
                    </div>
                </div>
                <table class='oderSummaryList'>
                    <tr>
                        <td class='titleSummary'>TOTAL ORDER AMOUNT</td>
                        <td class='summaryAmount'>{$currency_symbol}{$invoiceAmt}</td>
                    <tr>
                        <td class='titleSummary'>TOTAL INVOICE RAISED</td>
                        <td class='summaryAmount'>{$currency_symbol}{$invoiceAmt}</td>
                    <tr>
                    <tr>
                        <td class='titleSummary'>AMOUNT PAID</td>
                        <td class='summaryAmount'>{$currency_symbol}{$receiptAmt}</td>
                    <tr>
                    <tr>
                        <td class='titleSummary'>OUTSTANDING INVOICE</td>
                        <td class='summaryAmount'>{$currency_symbol}{$outstandingInvoiceAmt}</td>
                    <tr>
                </table>
            </div>
            ";
                    /*<tr>
                        <td class='titleSummary'>OVERALL BALANCE</td>
                        <td class='summaryAmount'>{$overallBalanceAmt}</td>
                    <tr>*/

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
     */
    function getPrintInvoiceRecordFpdf() {
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
        $invoice_type = $fn->getReqParam('invoice_type');
        if($invoice_type == 'normal'){
            $invoiceHeading = '';
        }
        else if($invoice_type == 'transporter'){
            $invoiceHeading = 'TRANSPORTER - ';
        }
        else if($invoice_type == 'proforma'){
            $invoiceHeading = 'PROFORMA - ';
        }
        else if($invoice_type == 'extra'){
            $invoiceHeading = 'EXTRA - ';
        }


        $SQL = "
        SELECT ini.*
              ,ini.item_title AS product_title
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
              ,i.invoice_terms
              ,i.invoice_due_date
              ,i.notes
              ,i.cst
              ,i.vat
              ,i.cst_value
              ,i.vat_value
              ,i.frieght
              ,i.p_f
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
              ,ini.qty * ini.unit_price AS amount
              ,(SELECT SUM(init.qty * init.unit_price) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE i.invoice_code = '{$invoice_code}'
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



        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        //syed:multi text code to set width of each column and alignment
        $pdf->SetWidths(array(10, 65, 37, 13, 13, 26, 26));
        $pdf->SetAligns(array('L', 'L', 'L', 'R', 'L', 'R', 'R'));

        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printWebAddress']);

                $creationDate   = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
                $invoiceDueDate = $fn->getCPDate($row['invoice_due_date'], 'd-m-Y');
                $deliveryDate   = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
				$currency = $row['currency'];

				$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
				$gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
				//$totalvalue = $gstvalue + $row['sub_total'];
				$totalvalue += $row['sub_total'];

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
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);
                $pdf->Ln(5);
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(5);
                $pdf->SetXY(130,30);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(80, 45);
                $pdf->Cell(50, 20, $invoiceHeading . "INVOICE", 0, 0, 'C');
                $pdf->SetFont('Courier','B',11);
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
						$deliveryAddressFlat 	= $row['shipping_address1'];
						$deliveryAddressStreet 	= $row['shipping_address2'];
						$deliveryAddressTown 	= $row['shipping_address_city'];
						$deliveryAddressState 	= $row['shipping_address_state'];
						$deliveryAddressCountry = $row['shipping_address_country'];
						$deliveryCompanyName 	= $row['shipping_first_name'];
				} else {
					//Delivery Address Fields in client
					$deliveryAddressFlat 	= $row['address_flat'];
					$deliveryAddressStreet 	= $row['address_street'];
					$deliveryAddressTown 	= $row['address_town'];
					$deliveryAddressState 	= $row['address_state'];
					$deliveryAddressCountry = $row['address_country'];
					$deliveryCompanyName 	= $row['company_name'];
				}

                /* Company Details*/

                $date = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"INVOICE TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"DELIVERY TO",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Courier','B',10.5);

                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(95, 8, $row['company_name'],'LR', 0, 'L', 1);
            	$pdf->Cell(95, 8, $deliveryCompanyName , 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',10);
            	$pdf->Cell(95, 5, $row['billing_address_flat'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressFlat, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',10);
	            $pdf->Cell(95, 5, $row['billing_address_street'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressStreet, 'LR', 0, 'L', 1);
                $pdf->Ln();
	        	$pdf->Cell(95, 5, $row['billing_address_town'], 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, $deliveryAddressTown, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',10);
	            $pdf->Cell(95, 5, $row['billing_address_country'] .' - '. $row['billing_address_state'], 'LR', 0, 'L', 1);
                $pdf->SetFont('Courier','B',10);
	            $pdf->Cell(95, 5, $deliveryAddressCountry .' - '. $deliveryAddressState, 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(95, 8, 'TIN NO:' . $row['tin_no'], 'LR', 0, 'L', 1);
                $pdf->Cell(95, 8, 'TIN NO:' .$row['tin_no'], 'LR', 0, 'L', 1);
                $pdf->Ln(6);
                $pdf->Cell(95, 8, 'CST NO:' . $row['cst_no'], 'BLR', 0, 'L', 1);
                $pdf->Cell(95, 8, 'CST NO:' .$row['cst_no'], 'BLR', 0, 'L', 1);

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
                $pdf->Ln(12);

				$terms = $row['invoice_terms'];
				$bank = $cpCfg['cp.bankDetails'];

	            $pdf->SetFont('Courier','B',11);
	            $pdf->SetFillColor(254,203,156);
	            $pdf->Cell(95,8,"TERMS",1,0, 'L', 1);
	            $pdf->Cell(95,8,"BANK DETAILS",1,0, 'L', 1);
	            $pdf->SetFillColor(255,255,255);
                $pdf->SetXY(10,132);
	            $pdf->drawTextBox($terms, 95, 32, 'L', 'C', 1);
                $pdf->SetXY(105,132);
	            $pdf->drawTextBox($bank, 95, 32, 'L', 'C', 'BLR');
	            $pdf->Ln(20);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(65,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(37,8,"REF Code",1,0, 'C', 1);
                $pdf->Cell(13,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(13,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(26,8,"UP",1,0, 'C', 1);
                $pdf->Cell(26,8,"AMOUNT(" . $row['currency'] . ")",1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
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
            $pdf->Row(array($lineItemNumber, $row['product_title'] , $row['code'], $row['qty'], $row['unit'], number_format($row['unit_price'],2) , number_format($row['amount'],2) ));


            //$pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
            $notes = $row['notes'];
            $frieght = $row['frieght'];
            $pf = $row['p_f'];
            $vat = $row['vat'];
            $cst = $row['cst'];
            $vat_value = $row['vat_value'];
            $cst_value = $row['cst_value'];

        }
            /*$pdf->SetFillColor(255,255,255);
            $pdf->Cell(164, 8, "SUB TOTAL", 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($sub_total),2), 1, 0, 'R', 1);
            $pdf->Ln();*/

            $totalvalueRounded = round($totalvalue);
			$totalFrieght = $sub_total * $frieght / 100;

			if($frieght > 0 ){
				$totalvalueRounded = $totalvalueRounded + round($totalFrieght);
	            $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(164, 8, "ADD FRIEGHT : {$frieght}%", 1, 0, 'R', 1);
	            $pdf->Cell(26, 8, number_format(round($totalFrieght), 2), 1, 0, 'R', 1);
				$pdf->Ln();
			}

			if($pf > 0 ){
                $totalpf = $sub_total * $pf / 100;
				$totalvalueRounded = $totalvalueRounded + round($totalpf);
	            $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(164, 8, "ADD P&F: {$pf}%", 1, 0, 'R', 1);
	            $pdf->Cell(26, 8, number_format(round($totalpf), 2), 1, 0, 'R', 1);
				$pdf->Ln();
			}

            $totalvalue = $totalvalue +  $totalpf + $totalFrieght;

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(164, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(26, 8, number_format(round($totalvalue), 2), 1, 0, 'R', 1);
			$pdf->Ln(10);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(190, 8, $cpCfg['cp.invoiceVatInclusive'], 0, 0, 'L');
            $pdf->Ln(10);

            $pdf->SetFont('Courier','B',11);
            $pdf->Cell(150, 8, 'NOTE: ');
            $pdf->Ln(5);
            $pdf->drawTextBox($notes, 180, 55, 'L', 'T', 0);
            $pdf->Ln(15);

            $pdf->Cell(195,8, "(This is computer generated document, and does not require a signature)", 0, 0, 'L', 1);

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
                <div class='invoiceTitle'>
                    Invoice(s)
                </div>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuterClient'>
                        {$this->getInvoicePortalDisplayDetail($row['order_id'])}
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
    function getReferralInvoicePortalDisplay($row){
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
                <div class='invoiceTitle'>
                    Invoice(s)
                </div>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuterReferral'>
                        {$this->getReferralInvoicePortalDisplayDetail($row['order_id'])}
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
    function getInvoicePortalDisplayCandidate($row){
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
                <div class='invoiceTitle'>
                    Invoice(s)
                </div>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuterCandidate'>
                        {$this->getInvoicePortalDisplayDetailCandidate($row['order_id'])}
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
    function getInvoicePortalDisplayDetail($order_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $links = "";
        $sqlAppend = "";

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

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
          AND i.invoice_type = 'Client'
        ORDER BY i.start_date
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

            $urlPrint  = "index.php?_topRm=finance&module=manPower_order&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_code']}&invoice_type=normal&showHTML=0";

            /*$expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";*/

            if($rowInvoice['status'] != 'Cancelled'){
                $total += $rowInvoice['invoice_amount'];
            }

            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}' order_id = {$order_id} type='Client'>Cancel Invoice</a>";
            }


            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'm-d-Y');
            $invoice_month = $fn->getCPDate($rowInvoice['invoice_date'], 'F');
            $totalvalueRounded = number_format($total,2);

            if($rowInvoice['status'] == 'Cancelled'){
                $rowStatus = "<b>{$rowInvoice['status']}</b>";
            } else {
                $rowStatus = $rowInvoice['status'];
            }

            $start_date   = date("m-d-Y", strtotime($rowInvoice['start_date']));
            $end_date     = date("m-d-Y", strtotime($rowInvoice['end_date']));

            $currency_symbol = '$';

            $rows .= "
            <tr>
                <td>{$cpCfg['clientInvoicePrefix']} - {$rowInvoice['invoice_code']}</td>
                <td>{$rowStatus}</td>
                <td>{$invoice_date}</td>
                <td>{$invoice_month}</td>
                <td>{$start_date}</td>
                <td>{$end_date}</td>
                <td align='right'>{$currency_symbol}{$totalvalueRounded}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Status</th>
        <th>Invoice Date</th>
        <th>Month</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Amount</th>
        <th>Print</th>
        <th>Cancel</th>
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
     */
    function getReferralInvoicePortalDisplayDetail($order_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $links = "";
        $sqlAppend = "";

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

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
          AND i.invoice_type = 'Referral'
        ORDER BY i.start_date
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
            $total = '';
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            $urlPrint  = "index.php?_topRm=finance&module=manPower_order&_spAction=printInvoiceRecordReferral&invoice_code={$rowInvoice['invoice_code']}&invoice_type=normal&showHTML=0";

            /*$expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";*/

            if($rowInvoice['status'] != 'Cancelled'){
                $total += $rowInvoice['invoice_amount'];
            }

            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}' order_id = {$order_id} type='Referral'>Cancel Invoice</a>";
            }


            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'm-d-Y');
            $invoice_month = $fn->getCPDate($rowInvoice['invoice_date'], 'F');
            $totalvalueRounded = number_format($total,2);

            if($rowInvoice['status'] == 'Cancelled'){
                $rowStatus = "<b>{$rowInvoice['status']}</b>";
            } else {
                $rowStatus = $rowInvoice['status'];
            }

            $start_date   = date("m-d-Y", strtotime($rowInvoice['start_date']));
            $end_date     = date("m-d-Y", strtotime($rowInvoice['end_date']));
            $currency_symbol = '$';

            $rows .= "
            <tr>
                <td>{$cpCfg['referralInvoicePrefix']} - {$rowInvoice['invoice_code']}</td>
                <td>{$rowStatus}</td>
                <td>{$invoice_date}</td>
                <td>{$invoice_month}</td>
                <td>{$start_date}</td>
                <td>{$end_date}</td>
                <td align='right'>{$currency_symbol}{$totalvalueRounded}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Status</th>
        <th>Invoice Date</th>
        <th>Month</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Amount</th>
        <th>Print</th>
        <th>Cancel</th>
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
    function getInvoicePortalDisplayDetailCandidate($order_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows      = "";
        $links     = "";
        $emp_rows  = "";
        $sqlAppend = "";

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQLtype ="
            SELECT position_type
            FROM `order`
            WHERE order_id = {$order_id}";
        $resultType   = $db->sql_query($SQLtype);
        $rowType = $db->sql_fetchrow($resultType);

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
          AND i.invoice_type = 'Candidate'
        ORDER BY i.start_date
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

            $urlPrint  = "index.php?_topRm=finance&module=manPower_order&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_code']}&invoice_type=normal&showHTML=0";
            $urlPrintPayStub  = "index.php?_topRm=finance&module=manPower_order&_spAction=printPayStub&invoice_code={$rowInvoice['invoice_code']}&invoice_type=normal&showHTML=0";

            /*$expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";*/

                if($rowInvoice['status'] != 'Cancelled'){
                    $total += $rowInvoice['invoice_amount'];
                }

                $cancelInvoiceLink   = '';
                $print_pay_stub_link = '';
                $invoice_detail_view = '';
                if ($rowInvoice['status'] != 'Cancelled'){
                    $print_pay_stub_link = "<a href='{$urlPrintPayStub}' target='_blank'>Print</a>";
                    $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}' order_id = '{$order_id}' type='Candidate'>Cancel Invoice</a>";
                    
                    $invoice_detail_view_link = "index.php?module=manPower_order&_spAction=generateInvoiceFormDetail&invoice_id={$rowInvoice['invoice_id']}&order_id={$order_id}&showHTML=0";
                    $invoice_detail_view = "<a href='#' class ='invoice_detail_link' invoice_id='{$rowInvoice['invoice_id']}' order_id='{$order_id}'  target='_blank'>Detail</a>";
                }


                $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'm-d-Y');
                $invoice_month = $fn->getCPDate($rowInvoice['start_date'], 'F');
                $totalvalueRounded = number_format($total,2);

                if($rowInvoice['status'] == 'Cancelled'){
                    $rowStatus = "<b>{$rowInvoice['status']}</b>";
                } else {
                    $rowStatus = $rowInvoice['status'];
                }


                $pay_Stub_td = '';
                $emp_tax_td  = '';

                if($rowType['position_type'] == 'Full Time'){
                    $pay_Stub_td= "
                        <td>{$print_pay_stub_link}</td>
                    ";

                    $formActionEmpTaxInvoice = "index.php?module=manPower_order&_spAction=generateEmpTaxForm&invoice_id={$rowInvoice['invoice_id']}&order_id={$order_id}&invoice_start_date={$rowInvoice['start_date']}&invoice_end_date={$rowInvoice['end_date']}&showHTML=0";
                    $print_emp_tax_link      = "<a href='{$formActionEmpTaxInvoice}' class ='emp_tax_invoice' order_id = '{$order_id}'  target='_blank'>Create</a>";

                    if($rowInvoice['status'] != 'Cancelled'){
                        $emp_tax_td = "
                            <td>{$print_emp_tax_link}</td>
                        ";
                    }
                }


                $start_date   = date("m-d-Y", strtotime($rowInvoice['start_date']));
                $end_date     = date("m-d-Y", strtotime($rowInvoice['end_date']));
                $currency_symbol = '$';

                if($rowType['position_type'] == 'Full Time'){
                        $SQL_emp_tax = "
                        SELECT i.*
                        FROM invoice i
                        WHERE i.order_id = {$order_id}
                          AND i.invoice_type = 'Employer Tax'
                          AND source_invoice_id = {$rowInvoice['invoice_id']}
                        ORDER BY i.invoice_id
                        ";
                        $result_emp_tax  = $db->sql_query($SQL_emp_tax);
                        $numRows_emp_tax = $db->sql_numrows($result_emp_tax);

                        if($numRows_emp_tax > 0){
                            $emp_header = "";
                            $emp_rows   = "";
                            while ($row_emp_tax = $db->sql_fetchrow($result_emp_tax)){

                                if($row_emp_tax['status'] == 'Cancelled'){
                                    $row_emp_Status = "<b>{$row_emp_tax['status']}</b>";
                                } else {
                                    $row_emp_Status = $row_emp_tax['status'];
                                }

                                $emp_invoice_date  = $fn->getCPDate($row_emp_tax['invoice_date'], 'm-d-Y');
                                $emp_invoice_month = $fn->getCPDate($row_emp_tax['invoice_date'], 'F');
                                $emp_start_date    = date("m-d-Y", strtotime($row_emp_tax['start_date']));
                                $emp_end_date      = date("m-d-Y", strtotime($row_emp_tax['end_date']));

                                $cancelempTaxInvoiceLink = '';
                                $emp_tax_detail_view     = '';
                                $makepaymentLink         = '';
                                $receipt_detail_view     = '';
                                if($row_emp_tax['status'] != 'Cancelled'){
                                    $cancelempTaxInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$row_emp_tax['invoice_code']}' invoice_id='{$row_emp_tax['invoice_id']}' order_id = '{$order_id}' type='Employer Tax'>Cancel Tax</a>";
                                    if ($row_emp_tax['status'] != 'Paid'){
                                        $EmpTaxReceiptLink = "index.php?module=manPower_order&_spAction=generateReceiptFormEmployerTax&order_id={$order_id}&invoice_id={$row_emp_tax['invoice_id']}&showHTML=0";
                                        $makepaymentLink = "<a href='{$EmpTaxReceiptLink}' order_id = '{$order_id}'  class='makePayment'>Make payment</a>";
                                    }

                                    $receiptSql = "
                                    SELECT receipt_status
                                    FROM receipt
                                    WHERE tax_invoice_id = {$row_emp_tax['invoice_id']}
                                    AND receipt_status != 'Cancelled'
                                    ";
                                    $resultReceipt = $db->sql_query($receiptSql);
                                    $numRows       = $db->sql_numrows($resultReceipt);
                                    $rowReceipt    = $db->sql_fetchrow($resultReceipt);

                                    if($numRows > 0){
                                        $receipt_detail_view = "<a href='#' class ='receipt_detail_link' invoice_id='{$row_emp_tax['invoice_id']}' order_id='{$order_id}'  target='_blank'>View Receipt</a>";
                                    }
                                }

                                $emp_tax_detail_view = "<a href='#' class ='emp_tax_detail_link' invoice_id='{$row_emp_tax['invoice_id']}' order_id='{$order_id}'  target='_blank'>Detail</a>";
                                $tax_invoice_amount  = number_format($row_emp_tax['invoice_amount'],2);
                                
                                $emp_rows .= "
                                <tr class = 'emp_tax_invoice_tr_td showemptaxrow'>
                                    <td>{$cpCfg['employerTaxInvoicePrefix']} - {$row_emp_tax['invoice_code']}</td>
                                    <td>{$row_emp_Status}</td>
                                    <td>{$emp_invoice_date}</td>
                                    <td>{$emp_invoice_month}</td>
                                    <td>{$emp_start_date}</td>
                                    <td>{$emp_end_date}</td>
                                    <td align='right'>{$currency_symbol}{$tax_invoice_amount}</td>
                                    <td>{$makepaymentLink}</td>
                                    <td>{$receipt_detail_view}</td>
                                    <td></td>
                                    <td>{$emp_tax_detail_view}</td>
                                    <td>{$cancelempTaxInvoiceLink}</td>
                                </tr>
                                ";

                            }

                            $emp_header ="
                            <tr class = 'emp_tax_invoice_tr_td showemptaxheader' style='background-color:#EAEAE8;'>
                                <th>Invoice Code</th>
                                <th>Status</th>
                                <th>Invoice Date</th>
                                <th>Month</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Amount</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Cancel</th>
                            </tr>
                            ";

                            $SQL_empltax = "
                            SELECT i.status
                            FROM invoice i
                            WHERE i.order_id = {$order_id}
                              AND i.invoice_type = 'Employer Tax'
                              AND source_invoice_id = {$rowInvoice['invoice_id']}
                              AND i.status = 'Cancelled'
                            ORDER BY i.invoice_id
                            ";
                            $result_empltax  = $db->sql_query($SQL_empltax);
                            $numRows_empltax = $db->sql_numrows($result_empltax);


                            $SQL_empltax_check = "
                            SELECT i.status
                            FROM invoice i
                            WHERE i.order_id = {$order_id}
                              AND i.invoice_type = 'Employer Tax'
                              AND source_invoice_id = {$rowInvoice['invoice_id']}
                              AND i.status != 'Cancelled'
                            ORDER BY i.invoice_id
                            ";
                            $result_empltax_check  = $db->sql_query($SQL_empltax_check);
                            $numRows_empltax_check = $db->sql_numrows($result_empltax_check);

                            if($rowInvoice['status'] != 'Cancelled'){

                                if($numRows_empltax_check == 0 && $numRows_empltax >0){
                                   $emp_tax_td = "
                                        <td><a href='#' class='employerTaxShow' order_id = '{$order_id}'>View</a> / {$print_emp_tax_link}</td>
                                    "; 
                                }else{
                                    $emp_tax_td = "
                                        <td><a href='#' class='employerTaxShow'>View</a></td>
                                    ";
                                }
                            }

                        }else{
                            $emp_header = "";
                            $emp_rows   = "";
                        }
                    }
                //<td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                $rows .= "
                <tbody class='slideTaxRow'>
                    <tr>
                        <td>{$cpCfg['candidateInvoicePrefix']} - {$rowInvoice['invoice_code']}</td>
                        <td>{$rowStatus}</td>
                        <td class='invoice_Date'>{$invoice_date}</td>
                        <td>{$invoice_month}</td>
                        <td>{$start_date}</td>
                        <td>{$end_date}</td>
                        <td align='right'>{$currency_symbol}{$totalvalueRounded}</td>
                        {$pay_Stub_td}
                        {$emp_tax_td}
                        <td>{$invoice_detail_view}</td>
                        <td><a href='{$urlPrint}' target='_blank'>Print Invoice</a></td>
                        <td>{$cancelInvoiceLink}</td>
                    </tr>
                    {$emp_rows}
                </tbody>
                ";

            }
        $pay_stub_header ='';
        $emp_tax_header  = '';
        if($rowType['position_type'] == 'Full Time'){
            $pay_stub_header ="
                <th>Pay Stub</th>
            ";

            $emp_tax_header ="
                <th>Emp Tax</th>
            ";
        }

        //<th>Print</th>

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class ='invoiceCodeth'>Invoice Code</th>
            <th class ='statusth'>Status</th>
            <th class = 'invoice_Date'>Invoice Date</th>
            <th class ='monthth'>Month</th>
            <th class ='startdateth'>Start Date</th>
            <th class ='enddateth'>End Date</th>
            <th class ='amountth'>Amount</th>
            {$pay_stub_header}
            {$emp_tax_header}
            <th class ='detailth'>Detail</th>
            <th>Print</th>
            <th class ='cancelth'>Cancel</th>
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
        AND r.receipt_type = 'Client'
              {$sqlAppend}
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $receipt_amount = 0;
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint      = "index.php?_topRm=finance&module=manPower_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$row['order_id']}&showHTML=0";
            $receipt_date  = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');
            $receipt_month = $fn->getCPDate($rowReceipt['date'], 'F');

            $cancelReceiptLink = '';
            $editReceipt   = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' type='Client' order_id ='{$row['order_id']}' receipt_code='{$rowReceipt['receipt_code']}'>Cancel Receipt</a>";
                $editReceiptLink   = "index.php?_topRm=finance&module=manPower_order&_spAction=editReceiptFormCandidate&showHTML=0&order_id={$row['order_id']}&receipt_id={$rowReceipt['receipt_id']}";
                $editReceipt ="<a href='{$editReceiptLink}' class='editReceiptCandidate'>Edit</a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "<b>Cancelled</b>";
            }

            $receipt_amount = number_format($rowReceipt['amount'],2);

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$receipt_month}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
                <td>{$editReceipt}</td>
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
        <th>Month</th>
        <th>Mode of Payment</th>
        <th>Receipt Amount</th>
        <th>Print</th>
        <th>Cancel</th>
        <th>Edit</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <div class='invoiceTitle'>
            Receipt(s)
        </div>
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
    function getReceiptPortalDisplayCandidate($row){
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
        AND r.receipt_type = 'Candidate'
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

            $urlPrint = "index.php?_topRm=finance&module=manPower_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$row['order_id']}&showHTML=0";
            $urlPrintPayStub  = "index.php?_topRm=finance&module=manPower_order&_spAction=printPayStubReceipt&receipt_id={$rowReceipt['receipt_id']}&order_id={$row['order_id']}&showHTML=0";

            $receipt_date  = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');
            $receipt_month = $fn->getCPDate($rowReceipt['date'], 'F');

            $cancelReceiptLink = '';
            $editReceipt   = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' type='Candidate' order_id ='{$row['order_id']}' receipt_code='{$rowReceipt['receipt_code']}'>Cancel Receipt</a>";
                $editReceiptLink   = "index.php?_topRm=finance&module=manPower_order&_spAction=editReceiptFormCandidate&showHTML=0&order_id={$row['order_id']}&receipt_id={$rowReceipt['receipt_id']}";
                $editReceipt ="<a href='{$editReceiptLink}' class='editReceiptCandidate'>Edit</a>";
            }

            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "<b>Cancelled</b>";
            }

            $pay_Stub_td = '';
            if($row['position_type'] == 'Full Time'){
                $print_pay_stub_link = "<a href='{$urlPrintPayStub}' target='_blank'>Print</a>";
                $pay_Stub_td= "
                    <td>{$print_pay_stub_link}</td>
                ";
            }

            $receipt_amount = number_format($rowReceipt['amount'],2);

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$receipt_month}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Receipt</a></td>
                {$pay_Stub_td}
                <td>{$cancelReceiptLink}</td>
                <td>{$editReceipt}</td>
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

        $pay_stub_header ='';
        if($row['position_type'] == 'Full Time'){
            $pay_stub_header ="
                <th>Pay Stub</th>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Receipt Code</th>
        <th>Receipt Date</th>
        <th>Month</th>
        <th>Mode of Payment</th>
        <th>Receipt Amount</th>
        <th>Print</th>
        {$pay_stub_header}
        <th>Cancel</th>
        <th>Edit</th>
        </tr>
        ";

        $portalHeading = 'Receipt(s)';
        if($row['subcontr_name'] != ''){
            $portalHeading = 'Payment(s)';
        }

        //$formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$row['order_id']}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <div class='invoiceTitle'>
            {$portalHeading}
        </div>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <form id='orderItemPrint' class='' method='post'>
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
    function getReceiptPortalDisplayReferral($row){
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
        AND r.receipt_type = 'Referral'
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

            $urlPrint = "index.php?_topRm=finance&module=manPower_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$row['order_id']}&showHTML=0";
            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');
            $receipt_month = $fn->getCPDate($rowReceipt['date'], 'F');
 
            $cancelReceiptLink = '';
            $editReceipt = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' type='Referral' order_id ='{$row['order_id']}' receipt_code='{$rowReceipt['receipt_code']}'>Cancel Receipt</a>";
                $editReceiptLink   = "index.php?_topRm=finance&module=manPower_order&_spAction=editReceiptFormCandidate&showHTML=0&order_id={$row['order_id']}&receipt_id={$rowReceipt['receipt_id']}";
                $editReceipt ="<a href='{$editReceiptLink}' class='editReceiptCandidate'>Edit</a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "<b>Cancelled</b>";
            }

            $receipt_amount = number_format($rowReceipt['amount'],2);

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$receipt_month}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
                <td>{$editReceipt}</td>
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
        <th>Month</th>
        <th>Mode of Payment</th>
        <th>Receipt Amount</th>
        <th>Print</th>
        <th>Cancel</th>
        <th>Edit</th>
        </tr>
        ";

        $text = "
        <div class='invoiceTitle'>
            Receipt(s)
        </div>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <form id='orderItemPrint' class='' method='post'>
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
              ,c.fax
              ,c.phone
              ,i.creation_date
              ,i.invoice_id AS invoice_id_main
              ,i.invoice_code
              ,i.invoice_amount
              ,o.currency
              ,q.quote_code
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
                $pdf->Image('images/logo-print.png',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                //$pdf->Cell(50, 50, 'Authorized Distributor of:');
                //$pdf->SetXY(10,25);
                //$pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['receipt_date'], 'd-m-Y');
                //$deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');
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
                /*$pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
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
                $pdf->Ln(5);*/
                /*$pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);*/
                $pdf->Ln(5);

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

				$billingAddressFlat     = $row['address_flat'];
				$billingAddressStreet   = $row['address_street'];
				$billingAddressTown     = $row['address_town'];
				$billingAddressState    = $row['address_state'];
				$billingAddressCountry  = $row['address_country'];


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
                $pdf->Cell(11, 20, 'Date : '. $date);
                //$pdf->Cell(50, 20, );
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
            $pdf->Cell(195,8, "(This is computer generated document, and does not require a signature)", 0, 0, 'L', 1);
            $pdf->Output();

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