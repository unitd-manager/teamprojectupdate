<?
class CP_Admin_Modules_EnggCrm_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
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
        ";
        $result1 = $db->sql_query($SQL1);
        //********************************************************//

        $count   = 0;
        $rows    = '';

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

            $rows .="
		    {$listObj->getListRowHeader($row, $count)}
		    {$listObj->getGoToDetailText($count, $row['invoice_code'], '', 60)}
		    {$listObj->getListDataCell($row['project_title'], 'left', '', 300)}
		    {$listObj->getListDataCell($row['company_name'], 'left', '', 225)}
		    {$listObj->getListDataCell(number_format($row['invoice_amount'], 2),'right', '', 60)}
		    {$listObj->getListDateCell($row['invoice_due_date'], 'left', '', 75)}
		    {$listObj->getListDataCell($age,'left','', 50)}
            {$branch}
		    {$listObj->getListDataCell($row['status'],'left','', 60)}
            {$listObj->getListDataCell($editText, 'center', '', 60)}
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$count++;
		}

        $rowSum    = $this->getSummaryRow();

        $branch = '';
        if ($cpCfg['m.enggCrm.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'b.title');
        }

        $trInvoiceSum="
        <tr class='even'>
            <td colspan='6'></td>
            <td style='text-align:right;font-weight:bold;padding:2px;'>{$rowSum['sum_invoice_amount']}</td>
            <td colspan='4'></td>
        </tr>
        ";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice', 'i.invoice_code')}
        {$listObj->getListHeaderCell('Project Name', 'project_title')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$listObj->getListHeaderCell('Due Date', 'i.invoice_due_date')}
        {$listObj->getListHeaderCell('Age', 'age')}
        {$branch}
        {$listObj->getListHeaderCell('Status', 'i.status')}
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

        $projUrl = "index.php?_topRm=project&module=enggCrm_project&record_id={$row['project_id']}&_action=detail";
        $projUrl = "<a href='{$projUrl}'>{$row['project_title']}</a>";

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];

        $contact = "<a href='index.php?_topRm=project&module=enggCrm_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";
        $company = "<a href='index.php?_topRm=project&module=enggCrm_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expInvNo)}
        {$formObj->getTBRow('Client Contact', 'contact_id', $contact, $expNoEdit)}
        {$formObj->getTBRow('Client Company', 'company_id', $company, $expNoEdit)}
        {$formObj->getTBRow('Project Name', 'project_title', $projUrl, $expNoEdit)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $row['invoice_amount'], $expNum)}
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

		$urlPrintInvoicePdf = "index.php?_topRm=order&module=enggCrm_order&_spAction=Printinvoice&invoice_code={$row['invoice_code']}&printOnly=1&orderNo={$row['order_id']}&showHTML=0";

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
                <option value=''>Month</option>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

        $title       = "<textarea value='' id='title' class='text invoiceItemTitle' name='title[]'></textarea>";
        $description = "<textarea value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearInvoiceItem'><u>Clear</u></a></td>";

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);

        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = number_format(($rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated']), 2);

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        $newRow = "
        <div class='float_right'><strong>Net Amount : {$total_order_amount_due}</strong></div>
        <a href='#' class='addRow button mb10'>Add Invoice Item</a>
        ";

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Amount</th>
            <th class='txtRight'>Total Cost</th>
            <th>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_order_amount_due == 0) {
            return "Invoice for the total amount generated already. Cancel the generated invoices and then create invoice";
        }

        $text = "
        <form id='generateInvoiceForm' class='generateInvoiceForm' method='post' action='{$formAction}'>
            <div>NOTE: You can enter a maximum of {$total_order_amount_due} for the Order</div>
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
    function getAddInvoiceItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $description = "<textarea type='text' value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
        $title       = "<textarea type='text' value='' id='title' class='text invoiceItemTitle' name='title[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text invoiceItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text invoiceItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text invoiceItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearInvoiceItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
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
    function getGenerateDetailInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

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
                $total_cost = $rowOi['unit_price'];
            }

            $total_cost = round($total_cost, 2);

            $rows .= "
            <tr>
                <td><textarea value='' id='title' class='text invoiceItemTitle' name='title[]'>{$rowOi['item_title']}</textarea></td>
                <td><textarea value='' id='description' class='text invoiceItemDescription' name='description[]'>{$rowOi['description']}</textarea></td>
                <td><input type='text' value='{$rowOi['unit']}' id='unit' class='text invoiceItemUnit' name='unit[]'></td>
                <td><input type='text' value='{$rowOi['qty']}' id='quantity' class='text invoiceItemQuantity' name='quantity[]'></td>
                <td><input type='text' value='{$rowOi['unit_price']}' id='amount' class='text invoiceItemAmount' name='amount[]'></td>
                <td class='txtRight text totalCost' name='totalCost[]'>{$total_cost}</td>
                <td><textarea value='' id='remarks' class='text invoiceItemRemarks' name='remarks[]'>{$rowOi['remarks']}</textarea></td>
                <td class='text'><a href='#' class='clearInvoiceItem'><u>Clear</u></a></td>
            </tr>
            ";
        }

        /* Total Order Amount */
        $sqlOi = "
        SELECT SUM(qty * unit_price) AS total_order_amt
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);

        /* Total Invoice Amount generated earlier */
        $sqlInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amt_generated
        FROM invoice
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($sqlInv);
        $rowInv     = $db->sql_fetchrow($resultInv);

        $total_order_amount_due = number_format(($rowOi['total_order_amt'] - $rowInv['total_invoice_amt_generated']), 2);

        $newRow = "
        <div class='float_right'><strong>Net Amount : {$total_order_amount_due}</strong></div>
        <a href='#' class='addRow button mb10'>Add Invoice Item</a>
        ";

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Amount</th>
            <th class='txtRight'>Total Cost</th>
            <th>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=enggCrm_invoice&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);
        if ($orderRec['project_type'] != 'Maintenance' && $total_order_amount_due == 0) {
            return "Invoice for the total amount generated already. Cancel the generated invoices and then create invoice";
        }

        $text = "
        <form id='generateDetailInvoiceForm' class='generateDetailInvoiceForm' method='post' action='{$formAction}'>
            <div>NOTE: You can enter a maximum of {$total_order_amount_due} for the Order</div>
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
        SELECT invoice_item_id, item_title, description
        FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultIi  = $db->sql_query($sqlIi);
        $numRowsIi = $db->sql_numrows($resultIi);
        while ($rowIi = $db->sql_fetchrow($resultIi)) {
            $invoice_item_id = $rowIi['invoice_item_id'];
            $rows .= "
            <tr>
                <td><textarea id='title' name='title_{$invoice_item_id}'>{$rowIi['item_title']}</textarea></td>
                <td>{$rowIi['description']}</td>
                <td class='text'><a href='#' class='clearInvoiceItem'><u>Clear</u></a></td>
            </tr>
            ";
        }

        $formAction = "index.php?module=enggCrm_invoice&_spAction=editInvoiceFormSubmit&showHTML=0";

        $invoice_terms = "";
        if ($cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm']) {
            $invoice_terms = $formObj->getTARow('Invoice Terms', 'invoice_terms', $row['invoice_terms']);
        }

        $location = "";
        if ($cpCfg['m.enggCrm.invoice.showLocationInEditForm']) {
            $location = $formObj->getTBRow('Location', 'invoice_title', $row['title']);
        }

        $text = "
        <form id='editInvoicePortalForm' class='yform columnar invoiceEditForm' method='post' action='{$formAction}'>
            {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
            {$formObj->getTBRow('Invoice Title', 'invoice_title', $row['title'])}
            {$invoice_terms}
            {$location}
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
        ";

        return $text;
    }
}