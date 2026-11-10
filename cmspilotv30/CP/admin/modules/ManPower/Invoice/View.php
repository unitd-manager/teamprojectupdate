<?
class CP_Admin_Modules_ManPower_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

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
            if ($cpCfg['m.manPower.invoice.hasMultiCurrency'] == 1){
                $base_value = $listObj->getListDataCell(number_format($row['invoice_amount_base']),'right');
            }

            $ref_value  = '';
            if ($cpCfg['m.manPower.invoice.showRefValue'] == 1){
                $ref_value = $listObj->getListDataCell(number_format($row['invoice_amount_ref']),'right');
            }

            $branch = '';
            if ($cpCfg['m.manPower.invoice.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $currency = '';
            if ($cpCfg['m.manPower.invoice.hasMultiCurrency'] == 1){
                $currency = $row['inv_currency'] . '&nbsp;';
            }

            $days_ago = date('Y-m-d', strtotime('-45 days', strtotime(date("Y-m-d"))));
            if ($row['status'] =='Due' ||  $row['status'] == 'Partial Payment'){
                if ($row['invoice_date'] < $days_ago){
                     $hightlightDueTasks = $listObj->getListRowHeader($row, $count, 'projectList2');
                }
                else{
                    $hightlightDueTasks = $listObj->getListRowHeader($row, $count);
                }
            }
            else {
                $hightlightDueTasks = $listObj->getListRowHeader($row, $count);
            }

            $urlInvoicePrint  = "index.php?_topRm=finance&module=manPower_order&_spAction=printInvoiceRecord&invoice_code={$row['invoice_code']}&invoice_type=normal&showHTML=0";
            $printInvoiceRecord = "<a target ='_blank' href='{$urlInvoicePrint}'>Print PDF</a>";

            $urlOrder = "index.php?_topRm=finance&module=manPower_order&_action=edit&record_id={$row['order_id']}";
            $orderId = "<a href='{$urlOrder}'><u>{$row['order_id']}</u></a>";

            $invoice_date = $fn->getCPDate($row['invoice_date'], 'm-d-Y');

            $rows .="
		    {$hightlightDueTasks}
		    {$listObj->getListDataCell($row['invoice_code'], '', 60)}
            {$listObj->getListDataCell($invoice_date, '', 60)}
		    {$listObj->getListDataCell($row['invoice_type'], 'left', '', 100)}
            {$listObj->getListDataCell($row['company_name'], 'left', '', 150)}
		    {$listObj->getListDataCell('$'.number_format($row['invoice_amount'],2),'right', '', 60)}
		    {$listObj->getListDataCell($row['status'],'left','', 60)}
            {$base_value}
		    {$ref_value}
		    {$listObj->getListDateCell($row['invoice_due_date'], 'left', '', 75)}
		    {$listObj->getListDataCell($age,'left','', 50)}
            {$branch}
            {$listObj->getListDataCell($orderId, 'center')}
            {$listObj->getListDataCell($printInvoiceRecord)}
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$count++;
		}

        $rowSum = $this->getSummaryRow();

        $branch = '';
        if ($cpCfg['m.manPower.invoice.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'b.title');
        }

        $base_value = '';
        if ($cpCfg['m.manPower.invoice.hasMultiCurrency'] == 1){
            $base_value = $listObj->getListHeaderCell($cpCfg['m.project.baseCurrency'], 'i.invoice_amount_base', 'headerRight');
            $base_value_sum = "
            ";
        }

        $ref_value  = '';
        if ($cpCfg['m.manPower.invoice.showRefValue'] == 1){
            $ref_value = $listObj->getListHeaderCell($cpCfg['m.project.refCurrency'], 'i.invoice_amount_ref', 'headerRight');
            $ref_value_sum = "
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'i.invoice_code')}
        {$listObj->getListHeaderCell('Date', '')}
        {$listObj->getListHeaderCell('Type', 'i.invoice_type')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$base_value}
        {$ref_value}
        {$listObj->getListHeaderCell('Due Date', 'i.invoice_due_date')}
        {$listObj->getListHeaderCell('Age', 'age')}
        {$branch}
        {$listObj->getListHeaderCell('Order ID', 'order_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Print', '')}
        {$listObj->getListHeaderEnd()}
        {$rows}
   	    <tr class='even'>
   	    	<td colspan='7'></td>
   	    	<td style='text-align:right;font-weight:bold;padding:2px;'>$ {$rowSum['sum_invoice_amount']}</td>
   	    	<td colspan='6'></td>
	    </tr>
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

        $fnMod = includeCPClass('ModuleFns', 'manPower_invoice');

        $SQL    = $fnMod->getInvoiceValueTotal();
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
        //$expInvNo  = array('isEditable' => $cpCfg['m.project.invoice.CodeEditable']);
        $expNum    = array('autoFormat' => 1);

        $invoiceToArray = array(
            "Agent"
           ,"Client"
        );

        if ($row['project_id'] > 0) {
            $modInvoice = getCPModuleObj('project_invoice');
            $still_to_bill= $row['project_value']-$modInvoice->model->getInvoiceAmount($row['project_id']);
            $stillToBill = $formObj->getTBRow("Still to Bill", "still_to_bill", number_format($still_to_bill), $expNoEdit);
        }

        $projUrl = "index.php?_topRm=project&module=manPower_project&record_id={$row['project_id']}&_action=edit";
        $projUrl = "<a href='{$projUrl}'>{$row['project_title']}</a>";

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];

        $contact = "<a href='index.php?_topRm={$tv['topRm']}&module=project_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";
        $company = "<a href='index.php?_topRm=opportunity&module=manPower_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";
        $agent = "<a href='index.php?_topRm=admin&module=manPower_agent&agent_id={$row['agent_id']}&_action=detail'>{$row['agent_name']}</a>";

        $vlUrl    = "index.php?module=core_valuelist&_spAction=showValuesInModal&showHTML=0&key_text=";

        $expNotes = array();
        $expTerms = array();

        if ($formObj->mode == 'edit'){
            $notesUrl = "{$vlUrl}invoiceNotes";
            $expNotes = array('notesRight' => "<input type='button' value='Set' class='w50' link='{$notesUrl}' id='showInvoiceNotes' />");

            $termsUrl = "{$vlUrl}invoiceTerms";
            $expTerms = array('notesRight' => "<input type='button' value='Set' class='w50' link='{$termsUrl}' id='showInvoiceTerms' />");
        }

        if ($cpCfg['m.manPower.invoice.hasMultiCurrency'] == 1){
            $base_value = $formObj->getTBRow("Base Invoice Amount ({$cpCfg['m.project.baseCurrency']})", 'invoice_amount_base', $row['invoice_amount_base'], $expNum);
        }

        if ($cpCfg['m.manPower.invoice.showRefValue'] == 1){
            $ref_value = $formObj->getTBRow("Reference Amount ({$cpCfg['m.project.refCurrency']})", 'invoice_amount_ref', $row['invoice_amount_ref'], $expNum);
        }

        /*if($row['invoice_to'] == ''){
            $invoice_to = 'Agent';
        } else {
            $invoice_to = $row['invoice_to'];
        }*/

        //{$formObj->getDDRowByArr('Invoice to', 'invoice_to', $invoiceToArray, $invoice_to)}

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlType, $row['invoice_type'], $expVl)}
        {$formObj->getTBRow('Client Company', 'company_id', $company, $expNoEdit)}
        {$formObj->getTBRow('Client Contact', 'contact_id', $contact, $expNoEdit)}
        {$formObj->getTBRow('Agent Contact', 'agent_id', $agent, $expNoEdit)}
        {$formObj->getTBRow('Project Name', 'project_title', $projUrl, $expNoEdit)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
		";

        /*if ($cpCfg['m.manPower.invoice.currencyDD'] == 1){
            $currency = $formObj->getDDRowBySQL('Currency', 'inv_currency', $sqlCurrency, $row['inv_currency'], $expVl);
        } else {
            $currency = $formObj->getTBRow('Currency', 'inv_currency', $row['inv_currency']);
        }*/

        $fieldset2 = "
        {$formObj->getTBRow('Project Value', 'project_value', $row['project_currency'] . ' ' . number_format($row['project_value']), $expNoEdit)}
        {$base_value}
        {$ref_value}
		{$stillToBill}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $row['invoice_amount'], $expNum)}
		";

       /* {$formObj->getYesNoRRow('Invoice Sent Out', 'invoice_sent_out', $row['invoice_sent_out'])}
        {$formObj->getDateRow('Invoice Paid Date', 'invoice_paid_date', $row['invoice_paid_date'])}*/

        $fieldset3 = "
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invDate)}
        {$formObj->getDateRow('Invoice Due Date', 'invoice_due_date', $row['invoice_due_date'])}
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

		$printURL = "index.php?module=manPower_invoice&_spAction=invoiceNoItemsPrintToFpdf&id={$row['invoice_id']}&showHTML=0";
        $receiptLink = "index.php?_topRm=finance&module=manPower_receipt&_spAction=generateReceiptFormFromInvoice&showHTML=0&invoice_id={$row['invoice_id']}";

        $actionButtons ="
        <div class='button mb5'>
            <a href='{$printURL}' target='_blank' id='printInvoice'>Print Invoice</a>
        </div>

        <!--<div class='button mb5'>
            <a href='{$receiptLink}' id='generateReceipt'>Generate Receipt</a>
        </div>-->
        ";

        $record_id = $fn->getIssetParam($row, 'invoice_id');
        /*{$displayLinkData->getLinkPortalMain('manPower_invoice', 'manPower_invoiceItem', 'Invoice Items', $row)}
        {$this->getReceiptPortalDisplay($row)}*/

        $text = "
        {$actionButtons}
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
     function getGenerateInvoiceForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        $order_id = $fn->getReqParam('order_id');

        $date        = date('Y-m-d');
        $due_date    = date('Y-m-d', strtotime("+14 days"));
        //$expDate     = array('dateFormat' => 'm-dd-yy');

        $qty_balance = '';

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateInvoiceFormSubmit&showHTML=0";

        $expNoEdit = array('isEditable' => 0);

        $orderRec = $fn->getRecordRowById('order', 'order_id', $order_id);

        $client_Name = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $ref_Name    = $fn->getRecordRowByID('company', 'company_id', $orderRec['referral_id']);
        $candi_Name  = $fn->getRecordRowByID('candidate', 'candidate_id', $orderRec['candidate_id']);

        $agent_name_candidate = '';
        if($candi_Name['agent_id'] != ''){
            $agent_Name  = $fn->getRecordRowByID('agent', 'agent_id', $candi_Name['agent_id']);
            $agent_name_candidate = ', '.$agent_Name['first_name'].''.$agent_Name['last_name'];
        }

        if($orderRec['referral_id'] != '' && $orderRec['apply_commission'] == 1){
            $invoicetype = array('Client'
                                ,'Candidate'
                                ,'Referral');
            $referral_fieldset = "
                {$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage_value', $orderRec['commission_percentage'])}
                {$formObj->getTBRow('Amount', 'commission_amount', '')}
                {$formObj->getTARow('Terms', 'referral_invoice_terms', $orderRec['invoice_terms'])}
                {$formObj->getTARow('Notes', 'referral_invoice_notes', $orderRec['notes'])}
            ";
            $referral_display = "
                {$formObj->getFieldSetWrapped('Referral ('.$ref_Name['company_name'].')', $referral_fieldset)}
            ";
        }else{
            $invoicetype = array('Client'
                                ,'Candidate');
            $referral_display = "";
        }

        //{$formObj->getTBRow('FUTA', 'FUTA', '')}
        //{$formObj->getTBRow('SUTA', 'SUTA', '')}
        $state_formula = '';
        if($orderRec['work_state'] == 'Illinois'){
            $state_formula = "
            {$formObj->getTBRow('', 'State_formula', '(State =  Gross * '.$cpCfg['StateCalculationValue'].')',$expNoEdit)}
            ";
        }

        if($orderRec['position_type'] == 'Full Time'){
            $payStub_fieldset = "
                {$formObj->getTBRow('Fed', 'fed', '')}
                <div class = 'formula_text_invoice'>
                    {$formObj->getTBRow('', 'soc_formula', '(Soc. Sec. = Gross * '.$cpCfg['SSCalculationValue'].')',$expNoEdit)}
                </div>
                {$formObj->getTBRow('Soc. Sec.', 'soc', '')}
                <div class = 'formula_text_invoice'>
                    {$formObj->getTBRow('', 'med_formula', '(Med =  Gross * '.$cpCfg['MedCalculationValue'].')',$expNoEdit)}
                </div>
                {$formObj->getTBRow('Med', 'med', '')}
                <div class = 'formula_text_invoice'>
                    {$state_formula}
                </div>
                {$formObj->getTBRow('State', 'state', '')}
                {$formObj->getTBRow('Deductions', 'deductions', '')}
                {$formObj->getTBRow('Net Amount', 'net', '',$expNoEdit)}
            ";
        }else{
            $payStub_fieldset ="";
        }

        $cst = '';
        if($orderRec['vat'] != 1){
            $cst ="
            {$formObj->getTBRow('Add CST(%)', 'cst_value')}
            ";
        }

        $date_condition     = date('Y-m-d');
        $due_date_condition = date('Y-m-d', strtotime("+14 days"));

        $sqlInvoice = "
        SELECT * FROM invoice
        WHERE ((start_date >= '{$date_condition}' AND start_date <= '{$due_date_condition}')
          OR (end_date >= '{$date_condition}' AND end_date <= '{$due_date_condition}'))
          AND order_id = {$order_id}
          AND invoice_type = 'Client'
          AND status != 'Cancelled'
          ORDER BY end_date DESC
        ";
        $resultInvoice      = $db->sql_query($sqlInvoice);
        $rowDateClientRow   = $db->sql_fetchrow($resultInvoice);

        $client_start_date  = date("Y-m-d", strtotime($rowDateClientRow['start_date']));
        $client_end_date    = date("Y-m-d", strtotime($rowDateClientRow['end_date']));

        $numRows = $db->sql_numrows($resultInvoice);
        if($numRows > 0){
            $test ="<div class ='alertDate chequeNoDisplay'>Client, candidate invoice already created for this date range</div>";
        }
        else{
            $test = "";
        }

        $sqlInvoice1 = "
        SELECT * FROM invoice
        WHERE ((start_date >= '{$date_condition}' AND start_date <= '{$due_date_condition}')
          OR (end_date >= '{$date_condition}' AND end_date <= '{$due_date_condition}'))
          AND order_id = {$order_id}
          AND invoice_type = 'Candidate'
          AND status != 'Cancelled'
          ORDER BY end_date DESC
        ";
        $resultInvoice1 = $db->sql_query($sqlInvoice1);
        $rowDateRow     = $db->sql_fetchrow($resultInvoice1);

        $candidate_start_date  = date("Y-m-d", strtotime($rowDateRow['start_date']));
        $candidate_end_date    = date("Y-m-d", strtotime($rowDateRow['end_date']));

        $numRows1 = $db->sql_numrows($resultInvoice1);
        if($numRows1 > 0){
            $test ="<div class ='alertDate chequeNoDisplay'>Client, candidate invoice already created for this date range</div>";
        }
        else{
            $test = "";
        }

        $dateFormatNotify ="<div class='dateFormatNotify'>( YYYY-MM-DD )</div>";

        $fieldset2 ="
            {$formObj->getTBRow('Hourly Rate Client', 'hourly_Rate_client', $orderRec['client_hourly_rate'])}
            {$formObj->getTBRow('Amount for Client', 'invoice_Amount', '')}
            {$formObj->getTARow('Terms', 'client_invoice_terms', $orderRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'client_invoice_notes', $orderRec['notes'])}
        ";

        $fieldset3 ="
            {$formObj->getTBRow('Hourly Rate Candidate', 'hourly_Rate_candidate', $orderRec['candidate_hourly_rate'])}
            {$formObj->getTBRow('Amount for Candidate', 'invoice_Amount_Candidate', '')}
            {$payStub_fieldset}
            {$formObj->getTARow('Terms', 'candidate_invoice_terms', $orderRec['invoice_terms'])}
            {$formObj->getTARow('Notes', 'candidate_invoice_notes', $orderRec['notes'])}
        ";

        $fieldset4 ="<div class ='invoiceTypeCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'client_invoice', $invoicetype ,$invoicetype)}</div>";

        $fieldset = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getFieldSetWrapped('Please Check The Related Check Box for generating invoice', $fieldset4)}
            {$formObj->getTBRow('Chargeable Hours', 'hrs_Client', '')}
            <div class='button updateTotal'>
                <a href='#'>Update Total</a>
            </div>
            {$test}
            {$dateFormatNotify}
            {$formObj->getDateRow('Start Date', 'invoice_start_date', $date)}
            {$formObj->getDateRow('End Date', 'invoice_end_date', $due_date)}
            <div class='clientDisplay'>
                {$formObj->getFieldSetWrapped('Client ('.$client_Name['company_name'].')', $fieldset2)}
            </div>
            <div class='candidateDisplay'>
                {$formObj->getFieldSetWrapped('Candidate ('.$candi_Name['first_name'].' '.$candi_Name['last_name'].$agent_name_candidate.')', $fieldset3)}
            </div>
            <div class='referralDisplay'>
                {$referral_display}
            </div>
            {$formObj->getTBRow('Issued By', 'staff_id', $_SESSION['userFullName'], $expNoEdit)}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='order_type' value='{$orderRec['position_type']}' />
            <input type='hidden' id='count_client' name='count_client' value='{$numRows}' />
            <input type='hidden' id='count_candidate' name='count_candidate' value='{$numRows1}' />
            <input type='hidden' id='start_date_candidate' name='date_range' value='{$candidate_start_date}' />
            <input type='hidden' id='end_date_candidate' name='date_range_end' value='{$candidate_end_date}' />
            <input type='hidden' id='start_date_client' name='date_range_client' value='{$client_start_date}' />
            <input type='hidden' id='end_date_client' name='date_range_end_client' value='{$client_end_date}' />
        </form>
        ";

        $text = "
            {$fieldset}
        ";

        return $text;
    }


    /**
     *
     */
     function getGenerateInvoiceFormDetail() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        $order_id   = $fn->getReqParam('order_id');
        $invoice_id = $fn->getReqParam('invoice_id');
        $expNoEdit = array('isEditable' => 0);

        $netAmount  = 0;
        $formAction = '';
        $invoice_amount = 0;
        $orderRec   = $fn->getRecordRowById('order', 'order_id', $order_id);
        $invoiceRec = $fn->getRecordRowById('invoice', 'invoice_id', $invoice_id);
        $candi_Name = $fn->getRecordRowByID('candidate', 'candidate_id', $orderRec['candidate_id']);

        $invoice_amount = number_format($invoiceRec['invoice_amount'],2);
        $netAmount  = $invoiceRec['invoice_amount'] - $invoiceRec['fed'] - $invoiceRec['soc'] - $invoiceRec['med'] - $invoiceRec['state'] - $invoiceRec['deductions'];
        $netAmount  = number_format($netAmount,2);

        if($orderRec['position_type'] == 'Full Time'){
            $payStub_fieldset = "
                {$formObj->getTBRow('Fed', 'fed', '$'.$invoiceRec['fed'], $expNoEdit)}
                {$formObj->getTBRow('Soc. Sec.', 'soc', '$'.$invoiceRec['soc'], $expNoEdit)}
                {$formObj->getTBRow('Med', 'med', '$'.$invoiceRec['med'], $expNoEdit)}
                {$formObj->getTBRow('State', 'state', '$'.$invoiceRec['state'], $expNoEdit)}
                {$formObj->getTBRow('Deductions', 'deductions', '$'.$invoiceRec['deductions'], $expNoEdit)}
                {$formObj->getTBRow('Net Amount', 'net', '$'.$netAmount, $expNoEdit)}
            ";
        }else{
            $payStub_fieldset ="";
        }

        $agent_name_candidate = '';
        if($candi_Name['agent_id'] != ''){
            $agent_Name  = $fn->getRecordRowByID('agent', 'agent_id', $candi_Name['agent_id']);
            $agent_name_candidate = ', '.$agent_Name['first_name'].''.$agent_Name['last_name'];
        }

        $fieldset3 ="
            {$formObj->getTBRow('Hourly Rate Candidate', 'hourly_Rate_candidate', $invoiceRec['candidate_hourly_rate'] ,$expNoEdit)}
            {$formObj->getTBRow('Amount for Candidate', 'invoice_Amount_Candidate', '$'.$invoice_amount ,$expNoEdit)}
            {$payStub_fieldset}
            {$formObj->getTARow('Terms', 'candidate_invoice_terms', $invoiceRec['invoice_terms'] ,$expNoEdit)}
            {$formObj->getTARow('Notes', 'candidate_invoice_notes', $invoiceRec['notes'] ,$expNoEdit)}
        ";

        $fieldset = "
        <form id='invoiceDetailForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            <div class='candidateDisplay'>
                {$formObj->getFieldSetWrapped('Candidate ('.$candi_Name['first_name'].' '.$candi_Name['last_name'].$agent_name_candidate.')', $fieldset3)}
            </div>
        </form>
        ";

        $text = "
            {$fieldset}
        ";

        return $text;
    }


    /**
     *
     */
    function getGenerateEmpTaxFormOld(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        $suta_amount = 0;
        $futa_amount = 0;
        $UCS_fed     = 0;
        $UCS_Tax     = 0;
        $UCS_Cost    = 0;
        $invoice_amount = 0;

        $date        = date('m-d-Y');
        $due_date    = date('m-d-Y', strtotime("+14 days"));

        $expNoEdit = array('isEditable' => 0);

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        $invoice_start_date = $fn->getReqParam('invoice_start_date');
        $invoice_end_date   = $fn->getReqParam('invoice_end_date');
        $invoiceRow = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $orderRow   = $fn->getRecordRowByID('order', 'order_id', $invoiceRow['order_id']);

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateEmpTaxFormSubmit&showHTML=0";

        $SQLAmountCheck = "
        SELECT SUM(invoice_amount) as total_invoice_amount 
        FROM invoice
        WHERE invoice_type = 'Candidate'
        AND status != 'Cancelled'
        AND order_id = {$order_id}
        AND start_date <= '{$invoice_start_date}'
        AND end_date <= '{$invoice_end_date}'
        ";
        $resultAmountCheck = $db->sql_query($SQLAmountCheck);
        $rowAmountCheck    = $db->sql_fetchrow($resultAmountCheck);

        if($rowAmountCheck['total_invoice_amount'] < 7000){
            $futa_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['FUTACalculationValue']),2);
        }
        else{
            $futa_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
            if($futa_amount < 7000){
                $futa_amount = 7000 - $futa_amount;
                $futa_amount = number_format(($futa_amount * $cpCfg['FUTACalculationValue']),2);
            }
            else{
                $futa_amount = 0;
            }
        }

        $SUTA_Formula = '';
        if($orderRow['work_state'] == 'Illinois'){

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTAI_llinois_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 12950){
               $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTAI_llinois_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 12950){
                    $suta_amount = 12950 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTAI_llinois_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }elseif ($orderRow['work_state'] == 'Michigan') {

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTA_Michigan_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 13000){
                $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTA_Michigan_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 13000){
                    $suta_amount = 13000 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTA_Michigan_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }elseif ($orderRow['work_state'] == 'Texas') {

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTA_Texas_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 9000){
                $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTA_Texas_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 9000){
                    $suta_amount = 9000 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTA_Texas_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }

        $UCS_fed  = $invoiceRow['fed'] + $invoiceRow['soc'] * 2 + $invoiceRow['med'] * 2;
        $UCS_Tax  = $UCS_fed + $invoiceRow['state'] + $futa_amount + $suta_amount;
        $UCS_Cost = $invoiceRow['soc'] + $invoiceRow['med'] + $futa_amount + $suta_amount;

        $UCS_fed  = number_format($UCS_fed,2);
        $UCS_Tax  = number_format($UCS_Tax,2);;
        $UCS_Cost = number_format($UCS_Cost,2);;
        $invoice_amount = number_format($invoiceRow['invoice_amount'],2);

        $date     = date("m-d-Y", strtotime($invoice_start_date));
        $due_date = date("m-d-Y", strtotime($invoice_end_date));


        $fieldset = "
        <form id='portalEmpTaxForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Gross', 'tax_invoice_amount', '$'.$invoice_amount, $expNoEdit)}
            {$formObj->getTBRow('Start Date', 'emp_start_date', $date, $expNoEdit)}
            {$formObj->getTBRow('End Date', 'emp_end_date', $due_date, $expNoEdit)}
            {$formObj->getTBRow('Fed', 'fed', '$'.$invoiceRow['fed'], $expNoEdit)}
            {$formObj->getTBRow('Soc', 'soc', '$'.$invoiceRow['soc'], $expNoEdit)}
            {$formObj->getTBRow('Med', 'med', '$'.$invoiceRow['med'], $expNoEdit)}
            {$formObj->getTBRow('State', 'state', '$'.$invoiceRow['state'], $expNoEdit)}
            <div class = 'formula_text'>
                {$formObj->getTBRow(' ', 'FUTA_formula', '(FUTA = Gross * '.$cpCfg['FUTACalculationValue'].')',$expNoEdit)}
            </div>
            {$formObj->getTBRow('FUTA', 'FUTA', $futa_amount)}
            <div class = 'formula_text'>
                {$SUTA_Formula}
            </div>
            {$formObj->getTBRow('SUTA', 'SUTA', $suta_amount)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_fed_formula', '(UCS fed = Fed + SS * 2 + Med * 2)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('UCS fed', 'ucs_fed', '$'.$UCS_fed, $expNoEdit)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_tax_formula', '(UCS Tax = UCS fed tax + state + FUTA + SUTA)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('UCS Tax', 'ucs_tax', '$'.$UCS_Tax, $expNoEdit)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_cost_formula', '(UCS Cost = Soc + Med + FUTA + SUTA)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('UCS Cost', 'ucs_cost', '$'.$UCS_Cost, $expNoEdit)}
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_start_date' value='{$invoice_start_date}' />
            <input type='hidden' name='invoice_end_date' value='{$invoice_end_date}' />
            <input type='hidden' name='tax_no_of_hours' value='{$invoiceRow['no_of_hours']}' />
            <input type='hidden' id='tax_invoiceamount' name='tax_invoiceamount' value='{$invoiceRow['invoice_amount']}' />
            <input type='hidden' id='Fed1'   value='{$invoiceRow['fed']}'   />
            <input type='hidden' id='soc1'   value='{$invoiceRow['soc']}'   />
            <input type='hidden' id='med1'   value='{$invoiceRow['med']}'   />
            <input type='hidden' id='state1' value='{$invoiceRow['state']}' />
            <input type='hidden' id='UCS_fed1' value='{$UCS_fed}' />
        </form>
        ";

        $text = "
            {$fieldset}
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateEmpTaxForm(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        $suta_amount = 0;
        $futa_amount = 0;
        $UCS_fed     = 0;
        $UCS_Tax     = 0;
        $UCS_Cost    = 0;
        $invoice_amount = 0;

        $date        = date('m-d-Y');
        $due_date    = date('m-d-Y', strtotime("+14 days"));

        $expNoEdit = array('isEditable' => 0);

        $invoice_id = $fn->getReqParam('invoice_id');
        $order_id   = $fn->getReqParam('order_id');
        $invoice_start_date = $fn->getReqParam('invoice_start_date');
        $invoice_end_date   = $fn->getReqParam('invoice_end_date');
        $invoiceRow = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $orderRow   = $fn->getRecordRowByID('order', 'order_id', $invoiceRow['order_id']);

        $formAction = "index.php?_topRm=finance&module=manPower_order&_spAction=generateEmpTaxFormSubmit&showHTML=0";

        $SQLAmountCheck = "
        SELECT SUM(i.invoice_amount) as total_invoice_amount 
        FROM `invoice` i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.invoice_type = 'Candidate'
        AND i.status != 'Cancelled'
        AND i.start_date <= '{$invoice_start_date}'
        AND i.end_date <= '{$invoice_end_date}'
        AND o.candidate_id = {$orderRow['candidate_id']}
        ";
        $resultAmountCheck = $db->sql_query($SQLAmountCheck);
        $rowAmountCheck    = $db->sql_fetchrow($resultAmountCheck);

        if($rowAmountCheck['total_invoice_amount'] < 7000){
            $futa_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['FUTACalculationValue']),2);
        }
        else{
            $futa_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
            if($futa_amount < 7000){
                $futa_amount = 7000 - $futa_amount;
                $futa_amount = number_format(($futa_amount * $cpCfg['FUTACalculationValue']),2);
            }
            else{
                $futa_amount = 0;
            }
        }

        $SUTA_Formula = '';
        if($orderRow['work_state'] == 'Illinois'){

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTAI_llinois_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 12950){
               $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTAI_llinois_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 12950){
                    $suta_amount = 12950 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTAI_llinois_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }elseif ($orderRow['work_state'] == 'Michigan') {

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTA_Michigan_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 13000){
                $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTA_Michigan_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 13000){
                    $suta_amount = 13000 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTA_Michigan_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }elseif ($orderRow['work_state'] == 'Texas') {

            $SUTA_Formula = "
            {$formObj->getTBRow(' ', 'SUTA_formula', '(SUTA = Gross * '.$cpCfg['SUTA_Texas_Calculation_Value'].')',$expNoEdit)}
            ";

            if($rowAmountCheck['total_invoice_amount'] < 9000){
                $suta_amount = number_format(($invoiceRow['invoice_amount'] * $cpCfg['SUTA_Texas_Calculation_Value']),2);
            }else{
                $suta_amount = $rowAmountCheck['total_invoice_amount'] - $invoiceRow['invoice_amount'];
                if($suta_amount < 9000){
                    $suta_amount = 9000 - $suta_amount;
                    $suta_amount = number_format(($suta_amount * $cpCfg['SUTA_Texas_Calculation_Value']),2);
                }
                else{
                    $suta_amount = 0;
                }
            }
        }

        $UCS_fed  = $invoiceRow['fed'] + $invoiceRow['soc'] * 2 + $invoiceRow['med'] * 2;
        $UCS_Tax  = $UCS_fed + $invoiceRow['state'] + $futa_amount + $suta_amount;
        $UCS_Cost = $invoiceRow['soc'] + $invoiceRow['med'] + $futa_amount + $suta_amount;

        $UCS_fed  = number_format($UCS_fed,2);
        $UCS_Tax  = number_format($UCS_Tax,2);;
        $UCS_Cost = number_format($UCS_Cost,2);;
        $invoice_amount = number_format($invoiceRow['invoice_amount'],2);

        $date     = date("m-d-Y", strtotime($invoice_start_date));
        $due_date = date("m-d-Y", strtotime($invoice_end_date));


        $fieldset = "
        <form id='portalEmpTaxForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Gross', 'tax_invoice_amount', '$'.$invoice_amount, $expNoEdit)}
            {$formObj->getTBRow('Start Date', 'emp_start_date', $date, $expNoEdit)}
            {$formObj->getTBRow('End Date', 'emp_end_date', $due_date, $expNoEdit)}
            {$formObj->getTBRow('Fed', 'fed', '$'.$invoiceRow['fed'], $expNoEdit)}
            {$formObj->getTBRow('Soc', 'soc', '$'.$invoiceRow['soc'], $expNoEdit)}
            {$formObj->getTBRow('Med', 'med', '$'.$invoiceRow['med'], $expNoEdit)}
            {$formObj->getTBRow('State', 'state', '$'.$invoiceRow['state'], $expNoEdit)}
            <div class = 'formula_text'>
                {$formObj->getTBRow(' ', 'FUTA_formula', '(FUTA = Gross * '.$cpCfg['FUTACalculationValue'].')',$expNoEdit)}
            </div>
            {$formObj->getTBRow('FUTA', 'FUTA', $futa_amount)}
            <div class = 'formula_text'>
                {$SUTA_Formula}
            </div>
            {$formObj->getTBRow('SUTA', 'SUTA', $suta_amount)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_fed_formula', '(Company fed = Fed + SS * 2 + Med * 2)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('Company fed', 'ucs_fed', '$'.$UCS_fed, $expNoEdit)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_tax_formula', '(Company Tax = Company fed tax + state + FUTA + SUTA)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('Company Tax', 'ucs_tax', '$'.$UCS_Tax, $expNoEdit)}
            <div class = 'formula_text_italic'>
                {$formObj->getTBRow(' ', 'ucs_cost_formula', '(UCS Cost = Soc + Med + FUTA + SUTA)',$expNoEdit)}
            </div>
            {$formObj->getTBRow('Company Cost', 'ucs_cost', '$'.$UCS_Cost, $expNoEdit)}
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='invoice_start_date' value='{$invoice_start_date}' />
            <input type='hidden' name='invoice_end_date' value='{$invoice_end_date}' />
            <input type='hidden' name='tax_no_of_hours' value='{$invoiceRow['no_of_hours']}' />
            <input type='hidden' id='tax_invoiceamount' name='tax_invoiceamount' value='{$invoiceRow['invoice_amount']}' />
            <input type='hidden' id='Fed1'   value='{$invoiceRow['fed']}'   />
            <input type='hidden' id='soc1'   value='{$invoiceRow['soc']}'   />
            <input type='hidden' id='med1'   value='{$invoiceRow['med']}'   />
            <input type='hidden' id='state1' value='{$invoiceRow['state']}' />
            <input type='hidden' id='UCS_fed1' value='{$UCS_fed}' />
        </form>
        ";

        $text = "
            {$fieldset}
        ";

        return $text;
    }


    /**
     *
     */
    function getGenerateEmpTaxFormDetail(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        $suta_amount = 0;
        $futa_amount = 0;
        $UCS_fed     = 0;
        $UCS_Tax     = 0;
        $UCS_Cost    = 0;

        $order_id           = $fn->getReqParam('order_id');
        $invoice_id         = $fn->getReqParam('invoice_id');
        $invoice_start_date = $fn->getReqParam('invoice_start_date');
        $invoice_end_date   = $fn->getReqParam('invoice_end_date');

        $expNoEdit = array('isEditable' => 0);

        $invoiceRow     = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $orderRow       = $fn->getRecordRowByID('order', 'order_id', $invoiceRow['order_id']);
        $sourceInvRow   = $fn->getRecordRowByID('invoice', 'invoice_id', $invoiceRow['source_invoice_id']);

        $formAction = "";

        $futa_amount    = number_format($invoiceRow['FUTA'],2);
        $suta_amount    = number_format($invoiceRow['SUTA'],2);
        $invoice_amount = number_format($sourceInvRow['invoice_amount'],2);

        $start_date  = $fn->getCPDate($invoiceRow['start_date'],'m-d-Y');
        $end_date    = $fn->getCPDate($invoiceRow['end_date'],'m-d-Y');

        $fieldset = "
        <form id='portalEmpTaxForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            <div class='employer_Formula'>
                {$formObj->getTBRow('Gross', 'tax_invoice_amount', '$'.$invoice_amount, $expNoEdit)}
            </div>
            {$formObj->getTBRow('Start Date', 'emp_start_date', $start_date, $expNoEdit)}
            {$formObj->getTBRow('End Date', 'emp_end_date', $end_date, $expNoEdit)}
            {$formObj->getTBRow('Fed', 'fed', '$'.$invoiceRow['fed'], $expNoEdit)}
            {$formObj->getTBRow('Soc', 'soc', '$'.$invoiceRow['soc'], $expNoEdit)}
            {$formObj->getTBRow('Med', 'med', '$'.$invoiceRow['med'], $expNoEdit)}
            <div class='employer_Formula'>
                {$formObj->getTBRow('State', 'state', '$'.$invoiceRow['state'], $expNoEdit)}
            </div>
            {$formObj->getTBRow('FUTA', 'FUTA', '$'.$futa_amount, $expNoEdit)}
            {$formObj->getTBRow('SUTA', 'SUTA', '$'.$suta_amount, $expNoEdit)}
            {$formObj->getTBRow('Company fed', 'ucs_fed', '$'.$invoiceRow['ucs_fed'], $expNoEdit)}
            {$formObj->getTBRow('Company Tax', 'ucs_tax', '$'.$invoiceRow['ucs_tax'], $expNoEdit)}
            {$formObj->getTBRow('Company Cost', 'ucs_cost', '$'.$invoiceRow['ucs_cost'], $expNoEdit)}
            <input type='hidden' name='invoice_id' value='{$invoice_id}' />
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='tax_no_of_hours' value='{$invoiceRow['no_of_hours']}' />
            <input type='hidden' id='tax_invoiceamount' name='tax_invoiceamount' value='{$invoiceRow['invoice_amount']}' />
        </form>
        ";

        $text = "
            {$fieldset}
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
        $invoice_type = $fn->getReqParam('invoice_type');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN (`order` o) ON (o.company_id = a.company_id)
        JOIN (invoice i) ON (o.order_id = i.order_id)
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

        $invoicetype = array('Client'
                            ,'Candidate'
                            ,'Referral'
                            ,'Employer Tax');

        $branch = '';
        if ($cpCfg['m.manPower.invoice.hasMultiBranches'] == 1){
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

        /*<td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>*/
        $text = "
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
            <select name='invoice_type'>
                <option value=''>Invoice Type</option>
                {$cpUtil->getDropDown1($invoicetype, $invoice_type)}
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
        WHERE r.invoice_id = {$row['invoice_id']}
              {$sqlAppend}
        ORDER BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        $urlPrint = '';

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            //$urlPrint = "index.php?_topRm=finance&module=pms_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=manPower_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $editURL = "index.php?_topRm=finance&module=manPower_receipt&_spAction=editReceiptFormFromInvoice&receipt_id={$rowReceipt['receipt_id']}&invoice_id={$row['invoice_id']}&showHTML=0";
            $editRow = "<td><a href='{$editURL}' class='editReceipt'>Edit</a></td>";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' receipt_code={$rowReceipt['receipt_code']}>Cancel Receipt</a>";
            }

            $printURL = "index.php?module=manPower_receipt&_spAction=generateReceiptForMedia&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$rowReceipt['created_by']}</td>
                <td>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$printURL}' target='_blank'>Print Receipt</a></td>
                <td>{$cancelReceiptLink}</td>
                {$editRow}
            </tr>
            ";

            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=3 class='txtRight'>Total : $total</td>
                <td colspan=5></td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Code</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Created By</th>
            <th>Status</th>
            <th>Print</th>
            <th>Cancel</th>
            <th>Edit</th>
        </tr>
        ";

        $text = "
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper manPower_invoice__manPower_receiptLink'>
            <div class='header'>
            <div class='floatbox'>
                <div class='float_left'>Receipt(s)</div>
            </div>
            </div>
                <table class='thinlist'>
                    {$header}
                    {$rows}
                    {$total}
                </table>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }
}