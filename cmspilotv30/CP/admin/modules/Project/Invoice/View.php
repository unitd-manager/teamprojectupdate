<?
class CP_Admin_Modules_Project_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
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

            $base_value = '';
            if ($cpCfg['m.project.hasMultiCurrency'] == 1){
                $base_value = $listObj->getListDataCell(number_format($row['invoice_amount_base']),'right');
            }
            
            $ref_value  = '';
            if ($cpCfg['m.project.invoice.showRefValue'] == 1){
                $ref_value = $listObj->getListDataCell(number_format($row['invoice_amount_ref']),'right');
            }
        
            $branch = '';
            if ($cpCfg['m.project.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $currency = '';
            if ($cpCfg['m.project.hasMultiCurrency'] == 1){
                $currency = $row['inv_currency'] . '&nbsp;';
            }

            $reminderMail = "index.php?_spAction=sendReminderEmail&module={$tv['module']}&invoice_id={$row['invoice_id']}&showHTML=0";
            $editText = "
            <a class='reminderMail ' dialogTitle=\"Invoice - {$row['invoice_code']}\" href='javascript:void(0);' link='{$reminderMail}' title='Send Task Mail'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/mail.png' border='0'>
            </a>
            ";


            $rows .="
		    {$listObj->getListRowHeader($row, $count)}
		    {$listObj->getGoToDetailText($count, $row['invoice_code'], '', 60)}
		    {$listObj->getListDataCell($row['invoice_type'], 'left', '', 100)}
		    {$listObj->getListDataCell($row['project_title'], 'left', '', 300)}
		    {$listObj->getListDataCell($row['company_name'], 'left', '', 225)}
		    {$listObj->getListDataCell($currency . number_format($row['invoice_amount']),'right', '', 60)}
		    {$base_value}
		    {$ref_value}
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
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'b.title');
        }

        $base_value = '';
        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            $base_value = $listObj->getListHeaderCell($cpCfg['m.project.baseCurrency'], 'i.invoice_amount_base', 'headerRight');
            $base_value_sum = "            
            ";
        }
        
        $ref_value  = '';
        if ($cpCfg['m.project.invoice.showRefValue'] == 1){
            $ref_value = $listObj->getListHeaderCell($cpCfg['m.project.refCurrency'], 'i.invoice_amount_ref', 'headerRight');
            $ref_value_sum = "
            ";
            $rowSumRef = $this->getSummaryRowForRefValue();
            $trInvoiceSum="
            <tr class='even'>
                <td colspan='7'></td>
                <td style='text-align:right;font-weight:bold;padding:2px;'>{$rowSum['sum_invoice_amount']}</td>
                <td colspan='1'></td>
                <td style='text-align:right;font-weight:bold;padding:2px;'>{$rowSumRef['sum_invoice_ref_amount']}</td>
                <td colspan='6'></td>
            </tr>
            ";
        }
        else{   
            $trInvoiceSum="
            <tr class='even'>
                <td colspan='5'></td>
                <td style='text-align:right;font-weight:bold;padding:2px;'>{$rowSum['sum_invoice_amount']}
                </td>
                <td colspan='10'></td>
            </tr>
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice', 'i.invoice_sequence')}
        {$listObj->getListHeaderCell('Invoice Type', 'i.invoice_type')}
        {$listObj->getListHeaderCell('Project Name', 'project_title')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$base_value}
        {$ref_value}
        {$listObj->getListHeaderCell('Due Date', 'i.invoice_due_date')}
        {$listObj->getListHeaderCell('Age', 'age')}
        {$branch}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('Reminder Email')}
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

        $fnMod = includeCPClass('ModuleFns', 'project_invoice');

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

        $fnMod = includeCPClass('ModuleFns', 'project_invoice');

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
        $stillToBill   = '';
        $base_value = '';
        $ref_value  = '';

        $sqlType   = $fn->getValueListSQL('invoiceType');
        $sqlStatus = $fn->getValueListSQL('invoiceStatus');
        $sqlTerms  = $fn->getValueListSQL('invoiceTerms');
        $sqlCurrency = $fn->getValueListSQL('invoiceCurrency');
        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);
        $expInvNo  = array('isEditable' => $cpCfg['m.project.invoice.CodeEditable']);
        $expNum    = array('autoFormat' => 1);

        if ($row['project_id'] > 0) {
            $modInvoice = getCPModuleObj('project_invoice');
            $still_to_bill= $row['project_value']-$modInvoice->model->getInvoiceAmount($row['project_id']);
            $stillToBill = $formObj->getTBRow("Still to Bill", "still_to_bill", number_format($still_to_bill), $expNoEdit);
        }

        $projUrl = "index.php?_topRm={$tv['topRm']}&module=project_project&record_id={$row['project_id']}&_action=detail";
        $projUrl = "<a href='{$projUrl}'>{$row['project_title']}</a>";

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];

        $contact = "<a href='index.php?_topRm={$tv['topRm']}&module=project_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";
        $company = "<a href='index.php?_topRm={$tv['topRm']}&module=project_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";

        $vlUrl    = "index.php?module=core_valuelist&_spAction=showValuesInModal&showHTML=0&key_text=";

        $expNotes = array();
        $expTerms = array();

        if ($formObj->mode == 'edit'){
            $notesUrl = "{$vlUrl}invoiceNotes";
            $expNotes = array('notesRight' => "<input type='button' value='Set' class='w50' link='{$notesUrl}' id='showInvoiceNotes' />");

            $termsUrl = "{$vlUrl}invoiceTerms";
            $expTerms = array('notesRight' => "<input type='button' value='Set' class='w50' link='{$termsUrl}' id='showInvoiceTerms' />");
        }

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            $base_value = $formObj->getTBRow("Base Invoice Amount ({$cpCfg['m.project.baseCurrency']})", 'invoice_amount_base', $row['invoice_amount_base'], $expNum);
        }

        if ($cpCfg['m.project.invoice.showRefValue'] == 1){
            $ref_value = $formObj->getTBRow("Reference Amount ({$cpCfg['m.project.refCurrency']})", 'invoice_amount_ref', $row['invoice_amount_ref'], $expNum);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expInvNo)}
        {$formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlType, $row['invoice_type'], $expVl)}
        {$formObj->getTBRow('Client Contact', 'contact_id', $contact, $expNoEdit)}
        {$formObj->getTBRow('Client Company', 'company_id', $company, $expNoEdit)}
        {$formObj->getTBRow('Project Name', 'project_title', $projUrl, $expNoEdit)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
		";

        if ($cpCfg['m.project.invoice.currencyDD'] == 1){
            $currency = $formObj->getDDRowBySQL('Currency', 'inv_currency', $sqlCurrency, $row['inv_currency'], $expVl);
        } else {
            $currency = $formObj->getTBRow('Currency', 'inv_currency', $row['inv_currency']);
        }

        $fieldset2 = "
        {$currency}
        {$formObj->getTBRow('Project Value', 'project_value', $row['project_currency'] . ' ' . number_format($row['project_value']), $expNoEdit)}
        {$base_value}
        {$ref_value}
		{$stillToBill}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $row['invoice_amount'], $expNum)}
		";

        $fieldset3 = "
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invDate)}
        {$formObj->getYesNoRRow('Invoice Sent Out', 'invoice_sent_out', $row['invoice_sent_out'])}
        {$formObj->getDateRow('Invoice Due Date', 'invoice_due_date', $row['invoice_due_date'])}
        {$formObj->getDateRow('Invoice Paid Date', 'invoice_paid_date', $row['invoice_paid_date'])}
		";

        $fieldset4 = "
        {$formObj->getTARow('Project Description', 'project_description', $row['project_description'], $expNoEdit)}
        {$formObj->getTARow('Notes', 'notes', $row['notes'], $expNotes)}
        {$formObj->getTARow('Invoice Terms', 'invoice_terms', $row['invoice_terms'], $expTerms)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Amount', $fieldset2)}
        {$formObj->getFieldSetWrapped('Date', $fieldset3)}
        {$formObj->getFieldSetWrapped('Other Values', $fieldset4)}
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
        if ($cpCfg['m.project.invoice.showInvoiceItemInPortal']  == 1) {
            $invoiceItem = $displayLinkData->getLinkPortalMain('project_invoice', 'project_invoiceItem', 'Invoice Items', $row);       
        }

		$urlPrintSubscriptionPdf = "index.php?module=project_invoice&_spAction=printSubscriptionPdf&invoice_id={$row['invoice_id']}&showHTML=0";

		$printSubscriptionButton = "
        <div class='floatbox  btnbackground'>
            <div class='button mb5'>
                <a href='{$urlPrintSubscriptionPdf}' id='printSubscription' target='_blank'>Print Invoice</a>
            </div> 
        </div>        
		";

        $record_id = $fn->getIssetParam($row, 'invoice_id');
        $text = "
        {$comment->getView(array(
             'roomName' => 'project_invoice'
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

            if ($cpCfg['m.project.hasQuotingModule'] == 1){
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
        JOIN (project b) ON (a.company_id = b.company_id)
        JOIN (invoice c) ON (b.project_id = c.project_id)
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('invoiceStatus');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(start_date, '%b %Y') AS monthYear
        FROM project
        WHERE DATE_FORMAT( start_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
         ";

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $branch_id = $fn->getReqParam('branch_id');
            $fnModBranch = includeCPClass('ModuleFns', 'project_branch');
            $sqlBranch = $fnModBranch->getBranchSQL();
            $branch = "
            <td>
                <select name='branch_id'>
                    <option value=''>Branch</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $branch_id)}
                </select>
            </td>
            ";
        }

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>
        {$branch}
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        <td>
            <select name='yearMonth'>
                <option value=''>Start Month</option>
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
}