<?
class CPL_Admin_Modules_EnggCrm_Invoice_View extends CP_Admin_Modules_EnggCrm_Invoice_View
{

    var $jssKeys = array('jQueryHtmlTextEditor');
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        //**** SET STATUS UPDATE ***//
        $today = date("Y-m-d");
        $SQL1 = "
        UPDATE invoice
        SET status = 'Late'
        WHERE invoice_due_date < '{$today}'
          AND LOWER(status) != 'paid'
          AND LOWER(status) != 'cancelled'
          AND LOWER(status) != 'partial payment'
        ";
        $result1 = $db->sql_query($SQL1);
        //********************************************************//
        $count   = 0;
        $rows    = '';
        $total_invoice_amount_summary = 0;

        foreach ($dataArray as $row){
    		if (strtolower($row['status']) == 'late') {
    		    $age = $row['age'];
    		} else {
    		    $age = '';
    		}

            $branch = '';
            if ($cpCfg['m.enggCrm.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $reminderMail = "index.php?_spAction=sendReminderEmail&module={$tv['module']}&invoice_id={$row['invoice_id']}&showHTML=0";
            $editText = "
            <a class='reminderMail ' dialogTitle=\"Invoice - {$row['invoice_code']}\" href='javascript:void(0);' link='{$reminderMail}' title='Send Task Mail'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/mail.png' border='0'>
            </a>
            ";
            $editText = '';

            // if ($row['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
            // } else {
                $total = $row['invoice_amount'];
            // }

               
            $urlPrintLinkPdf  = "index.php?module=enggCrm_order&_spAction=Printinvoice&invoice_id={$row['invoice_id']}&order_id={$row['order_id']}&showHTML=0";
    
                
                
            $quoteActions ="
            <div class='float_box clearfix'>
                
                <div class='printLink float_left'>
                    <a href='{$urlPrintLinkPdf}' target='_blank' class='btn btn-info button ml10' title='Print Invoice'>print pdf</a>
                </div>
            
            </div>
            ";   

            $totalvalueRounded = number_format((float)$total, 3, '.', '');
            $invoice_code = "<a target='_blank' href='index.php?_topRm=finance&module=enggCrm_order&order_id=" . $row['order_id'] . "&_action=edit'>PT/" . substr($row['invoice_code'], 2) . "/" . $row['invoice_code_user'] . "</a>";

            $rows .="
		    {$listObj->getListRowHeader($row, $count)}
		    {$listObj->getListDataCell($invoice_code, '', 60)}
		    {$listObj->getListDataCell($row['project_reference'], 'left', '', 300)}
		    {$listObj->getListDataCell($row['company_name'], 'left', '', 225)}
		    {$listObj->getListDataCell($row['po_number'], 'left', '', 50)}
            {$listObj->getListDateCell($row['invoice_date'], 'left', '', 75)}
		    {$listObj->getListDataCell($totalvalueRounded,'right', '', 60)}
		    {$listObj->getListDateCell($row['invoice_due_date'], 'left', '', 75)}
		    {$listObj->getListDataCell($age,'left','', 50)}
            {$branch}
		    {$listObj->getListDataCell($row['status'],'left','', 60)}
            {$listObj->getListDataCell($row['invoice_type'])}
            {$listObj->getListDataCell($quoteActions)} 
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$count++;
            $total_invoice_amount_summary += $total;
		}

        $total_invoice_amount_summary_formatted = number_format((float)$total_invoice_amount_summary, 3, '.', '');
        $trInvoiceSum="
        <tr class='even'>
            <td colspan='6'></td>
            <td style='text-align:right;font-weight:bold;padding:2px;'>{$total_invoice_amount_summary_formatted}</td>
            <td colspan='4'></td>
        </tr>
        ";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice', 'i.invoice_code')}
        {$listObj->getListHeaderCell('Project Ref', 'project_title')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('PO No.', 'i.po_number')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$listObj->getListHeaderCell('Due Date', 'i.invoice_due_date')}
        {$listObj->getListHeaderCell('Age', 'age')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('Type', 'i.invoice_type')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$trInvoiceSum}
        {$listObj->getListFooter()}
       	";

        return $text;
    }

    /**
     *
     */
    function getSummaryRow() {
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_invoice');

        $SQL    = $fnMod->getInvoiceValueTotal();
        $SQL   .= $searchVar->getSearchVar($tv['module'], 0);
        $result = $db->sql_query($SQL);
        $rowSum = $db->sql_fetchrow($result);

        return $rowSum;
    }

    /**
     *
     */
    function getSummaryRowForRefValue() {
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_invoice');

        $SQL    = $fnMod->getInvoiceValueTotalRef();
        $SQL   .= $searchVar->getSearchVar($tv['module'], 0);
        $result = $db->sql_query($SQL);
        $rowSum = $db->sql_fetchrow($result);

        return $rowSum;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $sqlProject = "
        SELECT a.project_id
              ,CONCAT_WS(' ', a.project_code, a.title)
              ,b.company_name
        FROM project a
            ,company b
        WHERE a.company_id = b.company_id
        ORDER BY b.company_name
                ,a.project_code
        ";

        $fielset1 = "
        {$formObj->getDDRowBySQL('Project Name', 'project_id', $sqlProject, '', array('sqlType' => 'hasSeperator'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $sqlStatus = $fn->getValueListSQL('invoiceStatus');
        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);
        $expInvNo  = array('isEditable' => $cpCfg['m.enggCrm.invoice.CodeEditable']);
        $expNum    = array('autoFormat' => 1);

        $orderRec   = $fn->getRecordRowById('order', 'order_id', $row['order_id']);

        $projUrl = "index.php?_topRm=project&module=enggCrm_project&record_id={$orderRec['project_id']}&_action=detail";
        $projUrl = "<a href='{$projUrl}'>{$row['project_title']}</a>";

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];

        $order = "<a href='index.php?_topRm=finance&module=enggCrm_order&order_id={$row['order_id']}&_action=edit'>{$row['order_id']}</a>";
        $contact = "<a href='index.php?_topRm=project&module=enggCrm_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";
        $company = "<a href='index.php?_topRm=project&module=enggCrm_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";

        if ($row['gst_percentage'] > 0) {
            $total = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
        } else {
            $total = $row['invoice_amount'];
        }

        $totalvalueRounded = number_format((float)$total, 3, '.', '');

        $invoice_code = 'PT/' . substr($row['invoice_code'], 2) . '/' . $row['invoice_code_user'];
        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $invoice_code, $expInvNo)}
        {$formObj->getTBRow('Order', 'order_id', $order, $expNoEdit)}
        {$formObj->getTBRow('Client Contact', 'contact_id', $contact, $expNoEdit)}
        {$formObj->getTBRow('Client Company', 'company_id', $company, $expNoEdit)}
        {$formObj->getTBRow('Project Name', 'project_title', $projUrl, $expNoEdit)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $totalvalueRounded, $expNum)}
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invDate)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlProj = "
        SELECT p.project_id
              ,p.title
        FROM project p
        JOIN (invoice i) ON (p.project_id = i.project_id)
        ORDER BY p.title
        ";

        $sqlComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN (project b) ON (a.company_id = b.company_id)
        JOIN (invoice c) ON (b.project_id = c.project_id)
        ORDER BY company_name
        ";

        $expVl     = array('sqlType' => 'OneField');
        $sqlStatus = $fn->getValueListSQL('invoiceStatus');
        $sqlType   = $fn->getValueListSQL('invoiceType');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
        {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj)}
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlComp)}
        {$formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlType, '', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
        {$formObj->getDateRangeRow('Invoice Date', 'invoice_date')}
        {$formObj->getDateRangeRow('Due Date', 'due_date')}
        {$formObj->getDateRangeRow('Paid Date', 'paid_date')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        {$formObj->getTARow('Description', 'description')}
        {$formObj->getTARow('Notes', 'notes')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        $invoiceItem = '';
        if ($cpCfg['m.enggCrm.invoice.showInvoiceItemInPortal']  == 1) {
            $invoiceItem = $displayLinkData->getLinkPortalMain('enggCrm_invoice', 'enggCrm_invoiceItem', 'Invoice Items', $row);
        }

        $orderRec = $fn->getRecordRowById('order', 'order_id', $row['order_id']);
        if($orderRec['record_type'] == 'Manpower Supply'){
            $PrintFunctionName = 'PrintinvoiceManpowerNormal';
            if($row['invoice_type'] == 'LOT'){
                $PrintFunctionName = "PrintinvoiceManpowerLot";
            }
            
            $urlPrintInvoicePdf  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_code={$row['invoice_code']}&printOnly=1&orderNo={$row['order_id']}&showHTML=0";

        } else {
		    $urlPrintInvoicePdf = "index.php?_topRm=order&module=enggCrm_order&_spAction=Printinvoice&invoice_code={$row['invoice_code']}&printOnly=1&orderNo={$row['order_id']}&showHTML=0";
        }

		$printSubscriptionButton = "
        <div class='floatbox  btnbackground'>
            <div class='button mb5'>
                <a href='{$urlPrintInvoicePdf}' id='printSubscription' target='_blank'>Print Invoice</a>
            </div>
        </div>
		";

        $record_id = $fn->getIssetParam($row, 'invoice_id');
        $text = "
        {$comment->getView(array(
             'roomName' => 'enggCrm_invoice'
            ,'recordId' => $record_id
        ))}
        {$invoiceItem}
		{$printSubscriptionButton}
        ";

        return $text;

    }

    /**
     *
     */
    function getPriceRangeDisplay($id) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $SQL     = "
        SELECT *
        FROM price_range
        WHERE city_id = '{$id}'
        ORDER BY sort_order
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowCounter = 0;
		$rows = '';

        while ($row = $db->sql_fetchrow($result)) {

            $fieldValue = $ln->getLangFieldValue($row, "title", 1);

            $rowClass = $fn->getRowClass ($rowCounter % 2, "list1", "list3");

			$icons = '';

            if ($tv['action'] != "detail") {
                $icons = "
				<td class='{$rowClass}' width='20'>
                	<a href=\"javascript:PriceRange.editPriceRange('{$row['price_range_id']}' ) \">
                    <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit.png' width='15' border='0'></a>
				</td>

                <td class='{$rowClass}' width='20'>
                	<a href=\"javascript:PriceRange.deletePriceRange('{$row['price_range_id']}' ) \">
                    <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/delete.png' width='15' border='0'></a>
				</td>
				";
            }

            $rows  .= "
            <tr>
				<td class='{$rowClass}' align='left'>{$fieldValue}</td>
                <td class='{$rowClass}' align='left'>{$row['range_start']}</td>
                <td class='{$rowClass}' align='left'>{$row['range_end']}</td>
                <td class='{$rowClass}' align='left'>{$row['sort_order']}</td>
                {$icons}

			</tr>
			";

            $rowCounter++;
        }

        if ($numRows == 0) {
            $header = "<tr><td colspan='3' class='media' height='50'></td></tr>";
        } else {
            $header = "
             <tr>
                <td><b>display</b></td>
                <td><b>start</b></td>
                <td><b>end</b></td>
                <td><b>sort</b></td>
             </tr>
             ";
        }

		$addBtn = '';

        if ($tv['action'] != "detail") {
            $addBtn = "
            <a href=\"javascript:PriceRange.addPriceRange('$id')\">Add</a>
            ";
        }

        $text = "
        <table class='picture'>
			{$header}
			{$rows}
			<tr>
            	<td class='header' colspan='10'>
            	{$addBtn}
			</td>
			</tr>
		</table>
		<br>
		";

        return $text;
    }

    /**
     *
     */
    function getReportsMenu() {
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        if ($tv['action'] == "detail") {
            $record_id      = $fn->getReqParam('record_id');
            $printReportUrl = "index.php?_spAction=printReport&record_id={$record_id}&showHTML=0&roomName={$tv['module']}&report=";

            if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1){
                $text = "
    			<ul class='printOptions'>
                	<li><a href='{$printReportUrl}invoice'>Invoice (HK$)</a>
                    <li><a href='{$printReportUrl}invoiceOther'>Invoice (Other$)</a>
                    <li><a href='{$printReportUrl}invoiceNoCategory'>Invoice No Category (HK$)</a>
                    <li><a href='{$printReportUrl}invoiceNoItems'>Invoice (No Line Items)</a>
                    <li><a href='{$printReportUrl}invoiceWOLogo'>Invoice w/o Logo (HK$)</a>
                    <li><a href='{$printReportUrl}invoiceOtherWOLogo'>Invoice w/o Logo (Other$)</a>
                    <li><a href='{$printReportUrl}invoiceNoItemsWOLogo'>Invoice w/o Logo (No Line Items)</a>
                	<li><a href='{$printReportUrl}invoiceWOQuote'>Invoice w/o Quote</a>
                    <li><a href='{$printReportUrl}invoiceWOQuoteWOLogo'>Invoice w/o Quote w/o Logo</a>
    			</ul>
    			";
    		} else {
                $text = "
    			<ul class='printOptions'>
                	<li><a href='{$printReportUrl}invoiceWOQuote'>Invoice (HK$)</a>
                    <li><a href='{$printReportUrl}invoiceWOQuoteWOLogo'>Invoice without Logo (HK$)</a>
    			</ul>
    			";
    		}
		} else {

            $searchQueryString = $pager->removeQueryString(array("_spAction"));

            $printChartUrl = "{$searchQueryString}&_spAction=charts&chartName=";
            $text = "
            <h2>Reports:</h2>
            <ul class='printOptions'>
                <li><a href='{$printChartUrl}barChartInvoice'>Total Invoices Raised vs. Paid by Month</a>
                <li><a href='{$printChartUrl}barChartInvoice'>Last Invoices Report - Sorted by Client</a>
                <li><a href='{$printChartUrl}barChartInvoice'>Last Invoices Report - Sorted by Age</a>
                <li><a href='{$printChartUrl}barChartInvoice'>All Outstanding Invoices Report - Sorted by Client</a>
                <li><a href='{$printChartUrl}barChartInvoice'>All Outstanding Invoices Report - Sorted by Age</a>
			</ul>
            ";
        }
        return $text;
    }

    /**
     *
     */
    function getCharts() {
        $pager = Zend_Registry::get('pager');
        $fn = Zend_Registry::get('fn');
        $dh = Zend_Registry::get('dh');

        $chartName = $fn->getReqParam('chartName');
        $searchQueryString = $pager->removeQueryString(array("_spAction"));

        $text  = "";
        if ($chartName == "barChartInvoice") {
            $text .= $dh->getReportHeader();
            $text .= "<IMG SRC='{$searchQueryString}&_spAction=barChartInvoice&_sortOrder=i.invoice_date&showHTML=0&hasDB=1'>";

            $text .= "{$searchQueryString}&_spAction=barChartInvoice&_sortOrder=i.invoice_date&showHTML=0&hasDB=1";

            $text .= $dh->getListFooterReport();
        }
        return $text;
    }

    /**
     *
     */
    function getBarChartInvoice($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        require_once("include/ChartDirector/lib/phpchartdir.php");

        $numRows        = $db->sql_numrows($result);

        $monthsArr = array();

        $data0     = array();
        $data1     = array();
        $data2     = array();
        $labels    = array();
        $invoice_date      = "";
        $invoice_paid_date = "";
        $rangeEndMonthTemp = 0;

        $rowCounter = 1;


        //*** for invoice_date values
        // $SQL = "SELECT a.*, CONCAT_WS('', YEAR(a.invoice_date), '-', DATE_FORMAT(a.invoice_date, '%m')) AS start_month,
        $SQL = "
        SELECT a.invoice_amount
              ,DATE_FORMAT(a.invoice_date, '%y-%b') AS start_month,
               SUM(invoice_amount) as invoice_amount
        FROM invoice a
        WHERE a.invoice_date BETWEEN '2008-01-01' AND '2008-12-01'
        GROUP BY start_month
        ORDER BY a.invoice_date
        ";
        $result      = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            # The data for the bar chart
            $allData[$row['start_month']]['data0'] = $row['invoice_amount'];
        }

        foreach ($allData as $month => $dataArrTemp) {
            $data0[] = $dataArrTemp['data0'];
            $data1[] = $dataArrTemp['data1'];
            $labels[]= $month;
        }

        # Create a XYChart object of size 400 x 240 pixels
        $c = new XYChart(1000, 650);

        # Add a title to the chart using 10 pt Arial font
        $c->addTitle(" Total Sales by Month", "", 10);

        # Set the plot area at (50, 25) and of size 320 x 180. Use two alternative background # colors (0xffffc0 and 0xffffe0)
        $c->setPlotArea(50, 25, 800, 500, 0xffffc0, 0xffffe0);

        # Add a legend box at (55, 18) using horizontal layout. Use 8 pt Arial font, with
        # transparent background
        $legendObj = $c->addLegend(55, 18, false, "", 8);
        $legendObj->setBackground(Transparent);

        # Add a title to the y-axis
        $c->yAxis->setTitle("Throughput (MBytes Per Hour)");

        # Reserve 20 pixels at the top of the y-axis for the legend box
        $c->yAxis->setTopMargin(20);

        # Set the x axis labels
        $c->xAxis->setLabels($labels);

        # Add a multi-bar layer with 3 data sets and 3 pixels 3D depth
        $layer = $c->addBarLayer2(Side, 3);
        $layer->addDataSet($data0, 0xff8080, "Third party #1");
        $layer->addDataSet($data1, 0x80ff80, "In House #2");

        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(PNG));
    }

    /**
     *
     */
    function getPrintList($result) {
        return $this->getList($result);
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $project_id   = $fn->getReqParam('project_id');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');
        $status       = $fn->getReqParam('status');
        $yearMonth    = $fn->getReqParam('yearMonth');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN (`order` b) ON (a.company_id = b.company_id)
        JOIN (invoice c) ON (b.order_id = c.order_id)
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('invoiceStatus');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(invoice_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(invoice_date, '%b %Y') AS monthYear
        FROM invoice
        ORDER BY yearMonthStart DESC
         ";

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );


        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        <td>
            <select name='yearMonth'>
                <option value=''>Invoice Month</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonth)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getSendReminderEmail() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=sendReminderEmailSubmit&module={$tv['module']}&showHTML=0";

        $invoice_id   = $fn->getReqParam('invoice_id');

        $text = "
        <form id='reminderEmail' class='yform columnar' method='post' action='{$formAction}'>
            <p>Would you like to send reminder mail for invoice? </p>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateInvoiceForm() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $order_id    = $fn->getReqParam('order_id');
        $record_type = $fn->getReqParam('record_type');
        $addSubArr = array(
             "Add"
            ,"Minus"
        );


        $title       = "<textarea value='' id='title' class='text invoiceItemTitleFull' name='title[]'></textarea>";
        $description = "<textarea value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='unit_price' class='text invoiceItemUnitPrice' name='unit_price[]'>";
        $total_cost  = "<td><input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'></td>";
        $remarks     = "<textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";
        $addSub      = "{$formObj->getDDRowByArr('', 'add_minus[]', $addSubArr, 'Add')}";

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
              ,SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
              ,SUM(admin_charges) AS admin_charges
              ,SUM(transport_charges) AS transport_charges 
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);
        $rowOi['total_order_amt'] = $rowOi['total_order_amt'] + $rowOi['totalOTAmount'] + $rowOi['totalPHAmount'] + $rowOi['admin_charges'] + $rowOi['transport_charges'];

        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
        );
        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = number_format(($rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated']), 3);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        $quoteRec = $fn->getRecordRowById('quote', 'quote_id', $orderRec['quote_id']);
        $projectRec = $fn->getRecordRowById('project', 'project_id', $orderRec['project_id']);
        $rows = '';
        if (isset($projectRec['category'], $quoteRec['drawing_nos'])
            && $projectRec['category'] == 'Maintenance'
            && $quoteRec['drawing_nos'] != 1) {
            $sqlQi = "
            SELECT *
            FROM quote_items
            WHERE quote_id = {$orderRec['quote_id']}
            ";
            $resultQi  = $db->sql_query($sqlQi);
            $numRowsQi = $db->sql_numrows($resultQi);
            while ($rowQi = $db->sql_fetchrow($resultQi)) {
                $title       = "<textarea value='{$rowQi['title']}' id='title' class='text invoiceItemTitleFull' name='title[]'>{$rowQi['title']}</textarea>";
                $description = "<textarea value='{$rowQi['description']}' id='description' class='text invoiceItemDescription' name='description[]'>{$rowQi['description']}</textarea>";
                $quantity    = "<input type='text' value='{$rowQi['quantity']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
                $unit        = "<input type='text' value='{$rowQi['unit']}' id='unit' class='text invoiceItemUnit' name='unit[]'>";
                $amount      = "<input type='text' value='{$rowQi['unit_price']}' id='unit_price' class='text invoiceItemUnitPrice' name='unit_price[]'>";
                $total_cost  = "<td><input type='text' value='{$rowQi['amount']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>";
                $clear       = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";
                $rows .= "
                <tr>
                    <td width='50%'>{$title}</td>
                    <td width='50%'>{$description}</td>
                    <td align='center'>{$unit}</td>
                    <td>{$quantity}</td>
                    <td align='center'>{$amount}</td>
                    {$total_cost}
                    {$clear}
                </tr>
                ";
            }
        } else {
            $rows = "
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <td width='20%'>{$addSub}</td>
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <td width='20%'>{$addSub}</td>
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <td width='20%'>{$addSub}</td>
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <td width='20%'>{$addSub}</td>
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <td width='20%'>{$addSub}</td>
                {$clear}
            </tr>
            ";
        }

        $newRow = "
        <div class='float_right'>
            <strong class='totalInvoiceAmountLabel'>Net Amount : 
                <span class='totalInvoiceAmount'>0.000</span>
            </strong>
        </div>
        <a class='addRow btn btn-primary mb10'>Add Invoice Item</a>
        <a class='addMoreDetailsInvoiceRow btn btn-success ml10 mb10'>(+) Add More Details</a>
        ";

        $invoice_type  = '';
        $sqlType       = $fn->getValueListSQL('invoiceTypes');
        $expType       = array('sqlType' => 'OneField');
        $invoice_type  = $formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlType, '', $expType);
        $invoice_type  = "<tr>{$invoice_type}</tr>";
        $invoice_terms = $fn->getSettingsValueByKey("invoiceTermsAndCondition");
      $sqlEmployee = "
      SELECT employee_id, employee_name FROM employee
      ORDER BY employee_name
      ";

        $header ="
        <tr>
            {$newRow}
            {$formObj->getTBRow('Discount', 'discount')}
            <div class='row hideMoreInvoiceDetails'>
                <div class='col-md-12'>
                    <div class='linkPortalWrapper col-md-12 noPadding'>
                        <div class='header col-md-12 noPadding' expanded='1'>
                            <div class='floatbox'></div>
                        </div>
                        <div class='linkPortalDataWrapper col-md-12 noPadding'>
                            <div class='col-md-12'>
                                {$formObj->getTBRow('Quote Code', 'quote_code', '')}
                                {$formObj->getTBRow('PO Number', 'po_number', '')}
                                {$formObj->getTBRow('Project Location', 'project_location', '')}
                                {$formObj->getTBRow('Project Reference', 'project_reference', '')}
                                {$formObj->getTBRow('Subject', 'subject', '')}
                                {$formObj->getTBRow('Invoice Terms', 'invoice_terms', $invoice_terms)}
                                {$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', '')}                
                                {$formObj->getDDRowBySQL('Signature Name', 'employee_id',  $sqlEmployee)}
                                <label>Terms & Conditions:</label>
                                {$formObj->getHTMLEditor('Terms & Conditions', 'payment_terms', $cpCfg['cp.bankDetailsinvoice'])}

                            </div>
                         </div>    
                    </div>
                </div>
            </div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Qty</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtRight'>Total Cost</th>
            <th class='txtCenter'>Add/Sub</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $expVl   = array('sqlType' => 'OneField');
        $sqlInvoiceType = $fn->getValueListSQL('invoiceTypes');

        /*$orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_order_amount_due == 0) {
            return "Invoice for the total amount generated already. Cancel the generated invoices and then create invoice";
        }*/

        $text = "
        <form id='generateInvoiceForm' class='yform columnar generateInvoiceForm' method='post' action='{$formAction}'>
            <div class='table-responsive'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
              <table class='table table-striped table-bordered thinlist'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        <style>
        .dialog-form {
                max-width: 600px;
                max-height: 530px;
                width: 90vw;
                height: 90vh;
            }
  
            @media (max-width: 600px) {
            #generateInvoiceForm {
                overflow-x: scroll;
                width:400px;
            }
                .dialog-form {
                width: 90vw; /* 90% of the viewport width */
                height: 90vh; /* 90% of the viewport height */
                max-width: none; /* Remove max-width for smaller screens */
                max-height: none; /* Remove max-height for smaller screens */
            }
            
            }
        </style>

        ";

        return $text;
    }


     /**
     *
     */
    function getGenerateInvoiceForm1() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $order_id    = $fn->getReqParam('order_id');
        $record_type = $fn->getReqParam('record_type');

        $title       = "<textarea value='' id='title' class='text invoiceItemTitleFull' name='title[]'></textarea>";
        $description = "<textarea value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='unit_price' class='text invoiceItemUnitPrice' name='unit_price[]'>";
        $total_cost  = "<td><input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'></td>";
        $remarks     = "<textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
              ,SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
              ,SUM(admin_charges) AS admin_charges
              ,SUM(transport_charges) AS transport_charges 
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);
        $rowOi['total_order_amt'] = $rowOi['total_order_amt'] + $rowOi['totalOTAmount'] + $rowOi['totalPHAmount'] + $rowOi['admin_charges'] + $rowOi['transport_charges'];


        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = number_format(($rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated']), 3);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        $quoteRec = $fn->getRecordRowById('quote', 'quote_id', $orderRec['quote_id']);
        $projectRec = $fn->getRecordRowById('project', 'project_id', $orderRec['project_id']);
        $rows = '';
        if (isset($projectRec['category'], $quoteRec['drawing_nos'])
            && $projectRec['category'] == 'Maintenance'
            && $quoteRec['drawing_nos'] != 1) {
            $sqlQi = "
            SELECT *
            FROM quote_items
            WHERE quote_id = {$orderRec['quote_id']}
            ";
            $resultQi  = $db->sql_query($sqlQi);
            $numRowsQi = $db->sql_numrows($resultQi);
            while ($rowQi = $db->sql_fetchrow($resultQi)) {
                $title       = "<textarea value='{$rowQi['title']}' id='title' class='text invoiceItemTitleFull' name='title[]'>{$rowQi['title']}</textarea>";
                $description = "<textarea value='{$rowQi['description']}' id='description' class='text invoiceItemDescription' name='description[]'>{$rowQi['description']}</textarea>";
                $quantity    = "<input type='text' value='{$rowQi['quantity']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
                $unit        = "<input type='text' value='{$rowQi['unit']}' id='unit' class='text invoiceItemUnit' name='unit[]'>";
                $amount      = "<input type='text' value='{$rowQi['unit_price']}' id='unit_price' class='text invoiceItemUnitPrice' name='unit_price[]'>";
                $total_cost  = "<td><input type='text' value='{$rowQi['amount']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>";
                $clear       = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";
                $rows .= "
                <tr>
                    <td width='50%'>{$title}</td>
                    <td width='50%'>{$description}</td>
                    <td align='center'>{$unit}</td>
                    <td>{$quantity}</td>
                    <td align='center'>{$amount}</td>
                    {$total_cost}
                    {$clear}
                </tr>
                ";
            }
        } else {
            $rows = "
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td width='50%'>{$title}</td>
                <td width='50%'>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            ";
        }

        $newRow = "
        <div class='float_right'>
            <strong class='totalInvoiceAmountLabel'>Net Amount : 
                <span class='totalInvoiceAmount'>0.000</span>
            </strong>
        </div>
        <a class='addRow btn btn-primary mb10'>Add Invoice Item</a>
        <a class='addMoreDetailsInvoiceRow1 btn btn-success ml10 mb10'>(+) Add More Details</a>
        ";

        $invoice_type  = '';
        $sqlType       = $fn->getValueListSQL('invoiceTypes');
        $expType       = array('sqlType' => 'OneField');
        $invoice_type  = $formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlType, '', $expType);
        $invoice_type  = "<tr>{$invoice_type}</tr>";
        $invoice_terms = $fn->getSettingsValueByKey("invoiceTermsAndCondition");
        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );

        $header ="
        <tr>
            {$newRow}
            {$formObj->getTBRow('Discount', 'discount')}
            <div class='row hideMoreInvoiceDetails1'>
                <div class='col-md-12'>
                    <div class='linkPortalWrapper col-md-12 noPadding'>
                        <div class='header col-md-12 noPadding' expanded='1'>
                            <div class='floatbox'></div>
                        </div>
                        <div class='linkPortalDataWrapper col-md-12 noPadding'>
                            <div class='col-md-12'>
                                {$formObj->getTBRow('Quote Code', 'quote_code', '')}
                                {$formObj->getTBRow('PO Number', 'po_number', '')}
                                {$formObj->getTBRow('Subject', 'subject', '')}
                                {$formObj->getTBRow('Project Location', 'project_location', '')}
                                {$formObj->getTBRow('Project Reference', 'project_reference', '')}
                                {$formObj->getTBRow('Invoice Terms', 'invoice_terms', $invoice_terms)}
                                {$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', '')}                
                                {$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, '')}  
                                <label>Terms & Conditions:</label>
                                {$formObj->getHTMLEditor('Terms & Conditions', 'payment_terms', $cpCfg['cp.bankDetailsinvoice'])}
                            </div>
                         </div>    
                    </div>
                </div>
            </div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Qty</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtRight'>Total Cost</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $expVl   = array('sqlType' => 'OneField');
        $sqlInvoiceType = $fn->getValueListSQL('invoiceTypes');

        /*$orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_order_amount_due == 0) {
            return "Invoice for the total amount generated already. Cancel the generated invoices and then create invoice";
        }*/

        $text = "
        <form id='generateInvoiceForm' class='yform columnar generateInvoiceForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            <table class='thinlist'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddInvoiceItemRecordDetail() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $record_type = $fn->getReqParam('record_type');

        if($record_type == ''){

        }

        $description      = "<textarea type='text' value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $title            = "<textarea type='text' value='' id='title' class='text invoiceItemTitle' name='title[]'></textarea>";
        $quantity         = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit             = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount           = "<input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'>";
        $total_cost       = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $balanceQuantity  = "<td><label class='text invoiceItemBalanceQuantity'></label></td>";
        $remarks          = "<textarea type='text' value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear            = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            {$balanceQuantity}
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddInvoiceItemRecordManpower() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $description      = "<textarea type='text' value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $title            = "<textarea type='text' value='' id='title' class='text invoiceItemTitle' name='title[]'></textarea>";
        $quantity         = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $amount           = "<input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'>";
        $total_cost       = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $clear            = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddInvoiceItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $addSubArr = array(
             "Add"
            ,"Minus"
        );

        $title       = "<textarea value='' id='title' class='text invoiceItemTitleFull' name='title[]'></textarea>";
        $description = "<textarea value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='unit_price' class='text invoiceItemUnitPrice' name='unit_price[]'>";
        $total_cost  = "<td><input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'></td>";
        $remarks     = "<textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>";
        $addSub      = "{$formObj->getDDRowByArr('', 'add_minus[]', $addSubArr, 'Add')}";

        $rows = "
        <tr>
            <td width='50%'>{$title}</td>
            <td width='50%'>{$description}</td>
            <td align='center'>{$unit}</td>
            <td>{$quantity}</td>
            <td align='center'>{$amount}</td>
            {$total_cost}
            <td width='20%'>{$addSub}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getGenerateDetailInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');
        $record_type = $fn->getReqParam('record_type');

        $sqlOi = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $numRowsOi = $db->sql_numrows($resultOi);

        $rowCounter = 0;
        $rows = '';

        while ($rowOi = $db->sql_fetchrow($resultOi)) {

            if ($rowOi['qty'] > 0) {
                $total_cost = $rowOi['qty'] * $rowOi['unit_price'];
            } else {
                $total_cost = 0;
            }

            $total_cost = round($total_cost, 3);

            if($record_type == 'Manpower Supply'){
                $rows = "<tbody id='changingResultRows'>{$this->getGenerateDetailInvoiceOrderItem($order_id)}</tbody>";

            }else{

                $sqlQty = "
                SELECT SUM(it.qty) AS qty_invoiced
                FROM invoice_item it
                JOIN invoice i ON (i.invoice_id = it.invoice_id)
                WHERE i.order_id = {$order_id}
                 AND it.record_id = {$rowOi['record_id']}
                 AND i.status != 'Cancelled'
                ";
                $resultQty = $db->sql_query($sqlQty);
                $rowQty = $db->sql_fetchrow($resultQty);

                $selling_price = $rowOi['unit_price'] * $rowOi['qty'];
                $qty_balance = $rowOi['qty'] - $rowQty['qty_invoiced'];

                if($qty_balance != ''){
                    $rowOi['qty'] = $qty_balance;
                }

                $hiddenRecordIdInput = "<input type='hidden' value='{$rowOi['record_id']}'  name='orderItem_record_id[]'>";

                $rows .= "
                <tr orderRowItem[] = {$rowOi['order_item_id']}>
                    <!--<td>
                        <textarea value='' id='title' class='text invoiceItemTitle' name='title[]'>{$rowOi['item_title']}</textarea>
                    </td>-->
                    <td><textarea value='' id='description' class='text invoiceItemDescription' name='description[]'>{$rowOi['description']}</textarea></td>
                        {$hiddenRecordIdInput}
                    <td><input type='text' value='{$rowOi['unit']}' id='unit' class='text invoiceItemUnit' name='unit[]'></td>
                    <td><input type='text' value='{$rowOi['qty']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'></td>
                    <td><label class='text invoiceItemBalanceQuantity'>{$qty_balance}</label></td>
                    <td><input type='text' value='{$rowOi['unit_price']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>
                    <td class='txtRight text totalCost' name='totalCost[]'>{$total_cost}</td>
                    <!--<td><textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'>{$rowOi['remarks']}</textarea></td>-->
                    <td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>
                    <input type='hidden' name='qty_balance' value='{$qty_balance}' />
                </tr>
                ";
            }
        }

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
              ,SUM(employee_ot_hours * ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours * ph_hourly_rate) AS totalPHAmount
              ,SUM(admin_charges) AS admin_charges
              ,SUM(transport_charges) AS transport_charges 
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);
        $rowOi['total_order_amt'] = $rowOi['total_order_amt'] + $rowOi['totalOTAmount'] + $rowOi['totalPHAmount'] + $rowOi['admin_charges'] + $rowOi['transport_charges'];

        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = number_format(($rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated']), 3);

        if($record_type == 'Manpower Supply') {
            $newRow = "
            <div class='float_right'><strong>Net Amount : {$total_order_amount_due}</strong></div>
            ";
        } else {
            $newRow = "
            <div class='float_right'><strong>Net Amount : {$total_order_amount_due}</strong></div>
            <a href='#' class='addRow button mb10' record_type='{$record_type}'>Add Invoice Item</a>
            ";
        }

        $qty_text_label = '';
        if($record_type == 'Manpower Supply') {
            $header ="
            <tr>{$newRow}</tr>
            <tr style='background-color:#EAEAE8;'>
                <th>Description</th>
                <!--<th class='txtCenter'>Rate</th>
                <th class='txtCenter'>Hours</th>-->
                <th class='txtRight'>Amount</th>
                <th></th>
            </tr>
            ";

            $qty_text_label = "Hours";
            $qty_text_input ="<input name='qty_text' value='LOT'/>";
            $rate_text_input = "<input name='rate_text' value='1'/>";
        }
        else{
            $header ="
            <tr>{$newRow}</tr>
            <tr style='background-color:#EAEAE8;'>
                <!--<th>Title</th>-->
                <th>Description</th>
                <th class='txtCenter'>UoM</th>
                <th class='txtCenter'>Quantity</th>
                <th class='txtCenter'>Balance Qty</th>
                <th class='txtCenter'>Amount</th>
                <th class='txtRight'>Total Cost</th>
                <!--<th>Remarks</th>-->
                <th></th>
            </tr>
            ";

            $qty_text_label = "Qty";
            $rate_text_input = "<input name='rate_text' value='As Attached'/>";
            $qty_text_input ="<input name='qty_text' value='1 LOT'/>";
        }

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_order_amount_due == 0) {
            return "Invoice for the total amount generated already. Cancel the generated invoices and then create invoice";
        }

        $invoice_type = '';
        $display_in_pdf = '';
        $display_in_pdf_date = '';
        $sqlType   = $fn->getValueListSQL('invoiceTypes');
        $expType = array('sqlType' => 'OneField');
        $invoice_type   = $formObj->getDDRowBySQL('Invoice Type :', 'invoice_type', $sqlType, 'Normal', $expType);

        if($record_type == 'Manpower Supply'){
            $display_in_pdf_date = "<td>
                                        <div class='invoice_creation_date invoice_creation_date_disable'>
                                            {$formObj->getDateRow('Start Date (d/m/Y):', 'invoice_start_date', '')}
                                        </div>
                                    </td>
                                    <td>
                                        <div class='invoice_creation_date invoice_creation_date_disable'>
                                            {$formObj->getDateRow('End Date (d/m/Y):', 'invoice_end_date', '')}
                                        </div>
                                    </td>
            ";
        }

        $invoice_type = "<td><div class='invoice_creation_type'>{$invoice_type}</div></td>";
        if($record_type == 'Manpower Supply') {
            $invoice_type = "";
        }
        
        $display_in_pdf = "<div class='invoice_creation_text invoice_creation_text_disable'>
                                <label>Rate - Text : </label>
                                {$rate_text_input}
                                <label>{$qty_text_label} - Text : </label>
                                {$qty_text_input}
                           </div>
                           ";

        $display_in_pdf_description = "<div class='invoice_creation_description invoice_creation_description_disable'>
                                            {$formObj->getTARow('Description :', 'notes')}
                                        </div>
                                        ";


        $text = "
        <form id='generateDetailInvoiceForm' class='generateDetailInvoiceForm' method='post' action='{$formAction}'>
            <div>NOTE: You can enter a maximum of {$total_order_amount_due} for the Order</div>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            <div class='floatbox'>
            <table>
                <tr>
                    {$invoice_type}
                    {$display_in_pdf_date}
                </tr>
            </table>
            </div>
            {$display_in_pdf}
            <table class='thinlist room-invoice-table'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' id='fld_order_id' name='order_id' value='{$order_id}' />
            <input type='hidden' name='record_type' value='{$record_type}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateDetailInvoiceOrderItem($order_id = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == ''){
            $order_id   = $fn->getReqParam('order_id');
        }

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $sqlOi = "
        SELECT oi.* 
             ,(SELECT oi1.item_title 
               FROM order_item oi1
               WHERE oi1.order_id = {$order_id}
               ORDER BY oi1.order_item_id ASC
               LIMIT 1) AS 'firstMonth'
             ,(SELECT oi1.item_title
               FROM order_item oi1
               WHERE oi1.order_id = {$order_id}
               ORDER BY oi1.order_item_id DESC
               LIMIT 1) as 'lastMonth'
        FROM order_item oi
        WHERE order_id = {$order_id}
        ORDER BY order_item_id ASC
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $numRowsOi = $db->sql_numrows($resultOi);

        $rowCounter = 0;
        $rows = '';
        $extraHiddenFields = '';
        if($start_date != '' & $end_date !=''){
            $SQLTimesheet = "
            SELECT  SUM(et.employee_hours) AS unit_price
                    ,et.hourly_rate AS qty
                    ,et.ot_hourly_rate
                    ,et.admin_charges
                    ,et.transport_charges
                    ,et.employee_id
                    ,SUM(et.employee_ot_hours) AS employee_ot_hours
                    ,et.ph_hourly_rate 
                    ,SUM(et.employee_ph_hours) AS employee_ph_hours
                    ,DATE_FORMAT(et.date, '%M') AS item_title
                    ,DATE_FORMAT(et.date, '%m') AS month
                    ,DATE_FORMAT(MIN(et.date),'%d/%c/%Y') FIRST_DATE
                    ,DATE_FORMAT(MAX(et.date),'%d/%c/%Y') LAST_DATE
                    ,DATE_FORMAT(et.date, '%b') AS month_three
                    ,DATE_FORMAT(et.date, '%Y') AS year
                    ,DATE_FORMAT(et.date, '%Y-%m') AS year_Months
            FROM employee_timesheet_finance et
            WHERE et.order_id = {$order_id}
            AND et.date BETWEEN '{$start_date}' AND '{$end_date}'
            GROUP BY DATE_FORMAT(et.date, '%Y-%m'), et.employee_id
            ";

            $resultOi = $db->sql_query($SQLTimesheet);
            $count = 0;
        }

        $overAllCost   = 0;
        $employee_name = "";
        $description   = "";
        $title         = "";
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            if($start_date != '' & $end_date !=''){
                $start_date_formatted = $fn->getCPDate($start_date, 'd/n/Y');
                $end_date_formatted   = $fn->getCPDate($end_date, 'd/n/Y');

                $SQLEmployee = "
                SELECT employee_name
                FROM employee
                WHERE employee_id = {$rowOi['employee_id']}
                ";
                $resultEmployee = $db->sql_query($SQLEmployee);
                $rowEmployee    = $db->sql_fetchrow($resultEmployee);

                $employee_name .= $rowEmployee['employee_name'].', ';            
            } else {
                $description = $rowOi['description'];
            }

            if ($rowOi['qty'] > 0) {
                $total_cost = $rowOi['qty'] * $rowOi['unit_price'];
            } else {
                $total_cost = 0;
            }

            $overAllCost += $total_cost + ($rowOi['ot_hourly_rate'] * $rowOi['employee_ot_hours']) + ($rowOi['ph_hourly_rate'] * $rowOi['employee_ph_hours']) + $rowOi['admin_charges'] + $rowOi['transport_charges'];

            if($start_date != '' & $end_date !='') {
                $start_date_month = $fn->getCPDate($start_date, 'F');
                $end_date_month   = $fn->getCPDate($end_date, 'F');
                if($start_date_month == $end_date_month) {
                    $title = $start_date_month;
                } else {
                    $title = $start_date_month. ' - ' .$end_date_month;
                }
            } else {
                if($rowOi['firstMonth'] == $rowOi['lastMonth']) {
                    $title = $rowOi['firstMonth'];
                } else {
                    $title = $rowOi['firstMonth']. ' - ' .$rowOi['lastMonth'];
                }
            }
        }
        
        $employee_name = rtrim($employee_name, ", ");
        $extraHiddenFields = "
        <input type='hidden' value='{$title}' class='text invoiceItemTitle' name='title[]'>
        <input type='hidden' value='{$overAllCost}'  name='total_cost_input[]'>
        ";

        $overAllCost = round($overAllCost, 3);
        if($start_date != '' & $end_date !=''){
            $start_date_formatted = $fn->getCPDate($start_date, 'd/n/Y');
            $end_date_formatted   = $fn->getCPDate($end_date, 'd/n/Y');
            $description = "Progress Claim for Subcontract Work done for the period from {$start_date_formatted} - {$end_date_formatted}";
        }

        if($start_date != "" && $end_date != "") {
            $SQLINV = "
            SELECT start_date
                   ,end_date
            FROM invoice
            WHERE order_id = {$order_id}
            AND ('{$start_date}' BETWEEN start_date AND end_date
            OR '{$end_date}' BETWEEN start_date AND end_date)
            AND status != 'Cancelled'
            ";
            $resultINV  = $db->sql_query($SQLINV);
            $numRowsINV = $db->sql_numrows($resultINV);
            $rowINV     = $db->sql_fetchrow($resultINV);

            if($numRowsINV > 0){
                 $rows = "
                    <tr>
                        <th colspan='4'>Invoice already created for the dates between {$start_date_formatted} and {$end_date_formatted}</th>
                    </tr>
                ";
            } else {
                $rows = "
                    <tr>
                        <td>
                            <textarea value='' id='description' class='text invoiceItemDescription' name='description[]'>{$description}</textarea>
                            {$extraHiddenFields}
                        </td>
                        <!--<td><input type='text' value='{$rowOi['qty']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'></td>
                        <td><input type='text' value='{$rowOi['unit_price']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>-->
                        <td class='txtRight text totalCost' name='totalCost[]'>{$overAllCost}</td>
                        <td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>
                    </tr>
                ";

            }
        } else {
            $rows = "
                <tr>
                    <td>
                        <textarea value='' id='description' class='text invoiceItemDescription' name='description[]'>{$description}</textarea>
                        {$extraHiddenFields}
                    </td>
                    <!--<td><input type='text' value='{$rowOi['qty']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'></td>
                    <td><input type='text' value='{$rowOi['unit_price']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>-->
                    <td class='txtRight text totalCost' name='totalCost[]'>{$overAllCost}</td>
                    <td class='text'><a class='clearInvoiceItem'><u>Clear</u></a></td>
                </tr>
            ";

        }
        
        
        return $rows;

    }

    /**
     *
     */
    function getEditInvoiceForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        $row        = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);

        $rows = '';
        $sqlIi = "
        SELECT invoice_item_id, item_title, description,unit,qty,unit_price,amount,add_minus
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        $numRowsIi = $db->sql_numrows($resultIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            $invoice_item_id = $rowIi['invoice_item_id'];
            $totalCost  = $rowIi['qty'] * $rowIi['unit_price'];
            //$totalCost  = number_format($totalCost, 2);
            $addSubArr = array(
                 "Add"
                ,"Minus"
            );

            $rows .= "
            <tr>
                <td><textarea id='title' name='title_{$invoice_item_id}'>{$rowIi['item_title']}</textarea></td>
                <td><textarea id='description' name='description_{$invoice_item_id}'>{$rowIi['description']}</textarea></td>
                <td><input type='text' value='{$rowIi['unit']}' class='text invoiceUnit' name='unit_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['qty']}' id='qty' class='text invoiceQuantity' name='qty_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['unit_price']}' id='unit_price' class='text invoiceUnitPrice' name='unitprice_{$invoice_item_id}'></td>
                <td><input type='text' value='{$totalCost}' id='amount' class='text invoiceAmount' name='amount_{$invoice_item_id}'></td>
                <td>{$formObj->getDDRowByArr('', 'addminus_'.$invoice_item_id, $addSubArr, $rowIi['add_minus'])}</td>

                <td class='text'><a class='clearInvoiceItemEdit' data-invoice-item-id='{$invoice_item_id}'><u>Remove</u></a></td>
            </tr>
            ";
        }

        $formAction = "index.php?module=enggCrm_invoice&_spAction=editInvoiceFormSubmit&showHTML=0";

        $invoice_terms = "";
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {

            /*$invoice_terms ="
            <div class='float_box clearfix'>
                <label class='classy-editor-label'>Invoice Terms</label>
                <textarea
                class='classy-editor'
                name='invoice_terms'
                id='fld_invoice_terms'
                >{$row['invoice_terms']}</textarea>
            </div>
            ";*/

            $invoice_terms = $formObj->getTARow('Invoice Terms', 'invoice_terms', $row['invoice_terms']);
        }

        $location = "";
        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $location = $formObj->getTBRow('Location', 'invoice_title', $row['title']);
        }

        /*$text = "
        <link rel='stylesheet' href='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/css/jquery.classyedit.css?v=20130110'>
        <script src='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/js/jquery.classyedit.js?v=20130110'></script>
        <script type='text/javascript'>
            $('.classy-editor').ClassyEdit();
        </script>
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Invoice Title', 'invoice_title', $row['title'])}
            {$invoice_terms}
            {$location}
            {$formObj->getTBRow('Ref No', 'reference_no', $row['reference_no'])}
            {$formObj->getTBRow('COB Ref No', 'CBF_Ref_No', $row['CBF_Ref_No'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th width='60%'>Title</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";*/
        $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');
      $sqlEmployee = "
      SELECT employee_id, employee_name FROM employee
      ORDER BY employee_name
      ";

        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );
        $text = "
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Subject', 'subject', $row['subject'])}
            {$formObj->getTBRow('PO Number', 'po_number', $row['po_number'])}
            {$formObj->getTBRow('Discount', 'discount', $row['discount'])}
            {$formObj->getTBRow('Invoice Terms', 'invoice_terms', $row['invoice_terms'])}
            {$formObj->getTBRow('Project Reference', 'project_reference', $row['project_reference'])}
            {$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', $row['apply_digital_signature'])}                
            {$formObj->getDDRowBySQL('Signature Name', 'employee_id',  $sqlEmployee, $row['employee_id'])}
            <label>Terms & Conditions:</label>
            {$formObj->getHTMLEditor('Terms & Conditions', 'payment_terms', $row['payment_terms'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th>Title</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                    <th>Add/Sub</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <a class='addInvoiceItem btn btn-primary mb10'>Add Invoice Item</a>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }


     /**
     *
     */
    function getEditInvoiceForm2() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        $row        = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);

        $rows = '';
        $sqlIi = "
        SELECT invoice_item_id, item_title, description,unit,qty,unit_price,amount
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        $numRowsIi = $db->sql_numrows($resultIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            $invoice_item_id = $rowIi['invoice_item_id'];
            $totalCost  = $rowIi['qty'] * $rowIi['unit_price'];
            $totalCost  = number_format($totalCost, 2);
            $rows .= "
            <tr>
                <td><textarea id='title' name='title_{$invoice_item_id}'>{$rowIi['item_title']}</textarea></td>
                <td><textarea id='description' name='description_{$invoice_item_id}'>{$rowIi['description']}</textarea></td>
                <td><input type='text' value='{$rowIi['unit']}' class='text invoiceUnit' name='unit_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['qty']}' id='qty' class='text invoiceQuantity' name='qty_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['unit_price']}' id='unit_price' class='text invoiceUnitPrice' name='unitprice_{$invoice_item_id}'></td>
                <td><input type='text' value='{$totalCost}' id='amount' class='text invoiceAmount' name='amount_{$invoice_item_id}'></td>
                <td class='text'><a class='clearInvoiceItemEdit' data-invoice-item-id='{$invoice_item_id}'><u>Remove</u></a></td>
            </tr>
            ";
        }

        $formAction = "index.php?module=enggCrm_invoice&_spAction=editInvoiceFormSubmit&showHTML=0";

        $invoice_terms = "";
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {

            /*$invoice_terms ="
            <div class='float_box clearfix'>
                <label class='classy-editor-label'>Invoice Terms</label>
                <textarea
                class='classy-editor'
                name='invoice_terms'
                id='fld_invoice_terms'
                >{$row['invoice_terms']}</textarea>
            </div>
            ";*/

            $invoice_terms = $formObj->getTARow('Invoice Terms', 'invoice_terms', $row['invoice_terms']);
        }

        $location = "";
        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $location = $formObj->getTBRow('Location', 'invoice_title', $row['title']);
        }

        /*$text = "
        <link rel='stylesheet' href='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/css/jquery.classyedit.css?v=20130110'>
        <script src='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/js/jquery.classyedit.js?v=20130110'></script>
        <script type='text/javascript'>
            $('.classy-editor').ClassyEdit();
        </script>
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Invoice Title', 'invoice_title', $row['title'])}
            {$invoice_terms}
            {$location}
            {$formObj->getTBRow('Ref No', 'reference_no', $row['reference_no'])}
            {$formObj->getTBRow('COB Ref No', 'CBF_Ref_No', $row['CBF_Ref_No'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th width='60%'>Title</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";*/
        $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');

        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );
        $text = "
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Subject', 'subject', $row['subject'])}
            {$formObj->getTBRow('PO Number', 'po_number', $row['po_number'])}
            {$formObj->getTBRow('Discount', 'discount', $row['discount'])}
            {$formObj->getTBRow('Invoice Terms', 'invoice_terms', $row['invoice_terms'])}
            {$formObj->getTBRow('Project Reference', 'project_reference', $row['project_reference'])}
            {$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', $row['apply_digital_signature'])}                
            {$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, $row['signature_name'])}
            <label>Terms & Conditions:</label>
            {$formObj->getHTMLEditor('Terms & Conditions', 'payment_terms', $row['payment_terms'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th>Tle</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }


     /**
     *
     */
    function getEditInvoiceForm1() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        $row        = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);

        $rows = '';
        $sqlIi = "
        SELECT invoice_item_id, item_title, description,unit,qty,unit_price,amount
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        $numRowsIi = $db->sql_numrows($resultIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            $invoice_item_id = $rowIi['invoice_item_id'];
            $totalCost  = $rowIi['qty'] * $rowIi['unit_price'];
            $totalCost  = number_format($totalCost, 2);
            $rows .= "
            <tr>
                <td><textarea id='title' name='title_{$invoice_item_id}'>{$rowIi['item_title']}</textarea></td>
                <td><textarea id='description' name='description_{$invoice_item_id}'>{$rowIi['description']}</textarea></td>
                <td><input type='text' value='{$rowIi['unit']}' class='text invoiceUnit' name='unit_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['qty']}' id='qty' class='text invoiceQuantity' name='qty_{$invoice_item_id}'></td>
                <td><input type='text' value='{$rowIi['unit_price']}' id='unit_price' class='text invoiceUnitPrice' name='unitprice_{$invoice_item_id}'></td>
                <td><input type='text' value='{$totalCost}' id='amount' class='text invoiceAmount' name='amount_{$invoice_item_id}'></td>
                <td class='text'><a class='clearInvoiceItemEdit' data-invoice-item-id='{$invoice_item_id}'><u>Remove</u></a></td>
            </tr>
            ";
        }

        $formAction = "index.php?module=enggCrm_invoice&_spAction=editInvoiceFormSubmit&showHTML=0";

        $invoice_terms = "";
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {

            /*$invoice_terms ="
            <div class='float_box clearfix'>
                <label class='classy-editor-label'>Invoice Terms</label>
                <textarea
                class='classy-editor'
                name='invoice_terms'
                id='fld_invoice_terms'
                >{$row['invoice_terms']}</textarea>
            </div>
            ";*/

            $invoice_terms = $formObj->getTARow('Invoice Terms', 'invoice_terms', $row['invoice_terms']);
        }

        $location = "";
        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $location = $formObj->getTBRow('Location', 'invoice_title', $row['title']);
        }

        /*$text = "
        <link rel='stylesheet' href='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/css/jquery.classyedit.css?v=20130110'>
        <script src='/cmspilotv30/library/jss/jquery/jQueryHtmlTextEditor/js/jquery.classyedit.js?v=20130110'></script>
        <script type='text/javascript'>
            $('.classy-editor').ClassyEdit();
        </script>
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Invoice Title', 'invoice_title', $row['title'])}
            {$invoice_terms}
            {$location}
            {$formObj->getTBRow('Ref No', 'reference_no', $row['reference_no'])}
            {$formObj->getTBRow('COB Ref No', 'CBF_Ref_No', $row['CBF_Ref_No'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th width='60%'>Title</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";*/
        $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');

        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );
        $text = "
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Subject', 'subject', $row['subject'])}
            {$formObj->getTBRow('PO Number', 'po_number', $row['po_number'])}
            {$formObj->getTBRow('Discount', 'discount', $row['discount'])}
            {$formObj->getTBRow('Invoice Terms', 'invoice_terms', $row['invoice_terms'])}
            {$formObj->getTBRow('Project Reference', 'project_reference', $row['project_reference'])}
            {$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', $row['apply_digital_signature'])}                
            {$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, $row['signature_name'])}
            <label>Terms & Conditions:</label>
            {$formObj->getHTMLEditor('Terms & Conditions', 'payment_terms', $row['payment_terms'])}
            <table class='thinlist'>
                <tr style='background-color:#EAEAE8;'>
                    <th>Title</th>
                    <th>Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
                {$rows}
            </table>
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getGenerateCreditNoteForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

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
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment' || i.status = 'Late')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry, all invoices have been paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);

            $rows .= "
            <tr>
                <td>{$invoice_code}</td>
                <td>{$row['invoice_amount']}</td>
                <td><textarea value='' id='title' class='text creditNoteTitle' name='title[]'></textarea></td>
                <td><textarea value='' id='description' class='text creditNoteDescription' name='description[]'></textarea></td>
                <td><input type='text' id='amount' class='text creditNoteAmount' name='amount[]'></td>
                <input type='hidden' name='invoice_id[]' value='{$row['invoice_id']}'
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateCreditNoteFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $signArray = array("Jassim", "Ibrahim", "Wassim");
        $text = "
        <form id='portalForm' class='yform columnar creditNoteForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            <table class='thinlist room-order-table'>
                <thead>
                    <th width='5%'>Invoice Code</th>
                    <th width='5%'>Amount</th>
                    <th width='40%'>Title</th>
                    <th width='40%'>Description</th>
                    <th width='10%'>Credit Amount</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            {$formObj->getDateRow('Date', 'date', date('Y-m-d'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            {$formObj->getYesNoRRow('Digital Signature', 'apply_digital_signature', '')}
            {$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, '')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }

    function getEditCreditNoteForm() {
         error_log('[InvoiceView] getEditCreditNoteForm CALLED');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $creditNoteId = $fn->getReqParam('credit_note_id');
        $orderId = $fn->getReqParam('order_id');

        $creditNote = $fn->getRecordRowByID('credit_note', 'credit_note_id', $creditNoteId);
        $rows = '';
        $sql = "
          SELECT SQL_NO_CACHE icnh.invoice_credit_note_history_id
              ,icnh.invoice_id
              ,icnh.item_title
              ,icnh.description
              ,icnh.amount
              ,i.invoice_amount
              ,i.invoice_code
        FROM invoice_credit_note_history icnh
        LEFT JOIN invoice i ON (i.invoice_id = icnh.invoice_id)
        WHERE icnh.credit_note_id = {$creditNoteId}
        ORDER BY icnh.invoice_credit_note_history_id
        ";
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $historyId = $row['invoice_credit_note_history_id'];
          $rows .= "
<tr>
    <td width='15%'>
        {$row['invoice_code']}
        <input type='hidden' name='history_id[]' value='{$historyId}' />
        <input type='hidden' name='invoice_id[]' value='{$row['invoice_id']}' />
    </td>

    <td width='15%'>{$row['invoice_amount']}</td>

    <td width='15%'>
        <textarea
            name='title[]'
            style='width:100%;'
        >{$row['item_title']}</textarea>
    </td>

    <td width='35%'>
        <textarea
            name='description[]'
            style='width:100%;'
        >{$row['description']}</textarea>
    </td>

    <td width='20%'>
        <input
            type='text'
            value='{$row['amount']}'
            class='text creditNoteAmount'
            name='amount[]'
            style='width:100%;'
        >
    </td>
</tr>
            ";
        }

        $formAction = "index.php?module=enggCrm_invoice&_spAction=editCreditNoteFormSubmit&showHTML=0";
        $expEdit = array('isEditable' => 0);
        $signArray = array("Jassim", "Ibrahim", "Wassim");
        $text = "
        <form id='editCreditNotePortalForm' class='yform columnar creditNoteForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', 'error_box', '', $expEdit)}</div>
            <table class='thinlist room-order-table' style='width:100%; table-layout:fixed;'>
                <thead>
                    <tr>
                        <th width='15%'>Invoice Code</th>
                        <th width='15%'>Amount</th>
                        <th width='15%'>Title</th>
                        <th width='35%'>Description</th>
                        <th width='20%'>Credit Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
            {$formObj->getDateRow('Date', 'date', $creditNote['date'])}
            {$formObj->getTextAreaRow('Note', 'remarks', $creditNote['remarks'])}
            {$formObj->getYesNoRRow('Digital Signature', 'apply_digital_signature', $creditNote['apply_digital_signature'])}
            {$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, $creditNote['signature_name'])}
            <input type='hidden' name='credit_note_id' value='{$creditNoteId}' />
            <input type='hidden' name='order_id' value='{$orderId}' />
        </form>
        ";

        return $text;
    }
}