<?
class CP_Admin_Modules_Tradingin_Quote_View extends CP_Common_Lib_ModuleViewAbstract
{
    //========================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $_SESSION['selectedQuoteProductIds'] = '';
        $_SESSION['sortBySupplier'] = '';

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $SQLTotal = "
            SELECT SUM(round(
            (qp.selling_price * qp.qty),2)) as total_selling_price
            FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $total_selling_price = number_format($rowTotal['total_selling_price'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['quote_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($total_selling_price)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['quote_date'])}
            {$listObj->getListDateCell($row['delivery_date'])}
            {$listObj->getListDataCell($row['priority'])}
            {$listObj->getListDataCell($row['modified_by'] . ' ' . $row['modification_date'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Quote Code', 'q.quote_code')}
        {$listObj->getListHeaderCell('Title', 'q.title')}
        {$listObj->getListHeaderCell('Client Name', 'company_name')}
        {$listObj->getListHeaderCell('Contact Person', 'contact_name')}
        {$listObj->getListHeaderCell('Total Selling Price', 'amount')}
        {$listObj->getListHeaderCell('Status', 'q.status')}
        {$listObj->getListHeaderCell('Quote Date', 'q.quote_date')}
        {$listObj->getListHeaderCell('Delivery Date', 'q.delivery_date')}
        {$listObj->getListHeaderCell('Priority', 'q.priority')}
        {$listObj->getListHeaderCell('Updated By', 'q.modified_by')}
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
        $fn = Zend_Registry::get('fn');

        $sqlQuoteType = $fn->getValuelistSql('quoteType');
        $expVl      = array('sqlType' => 'OneField');
        //{$formObj->getDDRowBySQL('Quote Type', 'quote_type', $sqlQuoteType, '', $expVl)}
        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'quote_id');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $formObj->mode = $tv['action'];
        $modContact = getCPModuleObj('trading_contact');

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $fnsModQuote = includeCPClass('ModuleFns', 'trading_quote');

        $sqlCurrency = $fn->getValueListSQL('currency');
        $sqlPriority = $fn->getValuelistSql('quotePriority');
        $sqlDeliveryLocation = $fn->getValuelistSql('deliveryLocation');
        $expVl      = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);
        $validatedClient = '';

        if ($row['staff_id'] == '') {
            $staff_name = $_SESSION['staff_id'];
        } else {
            $staff_name = $row['staff_id'];
        }

        $expStaff   = array('detailValue' => $row['staff_name'], 'isEditable' => 0);

        $sqlContact = '';
        if($row['company_id'] != '') {
            $sqlContact = "
            SELECT contact_id
                  ,CONCAT_WS(' ', first_name, last_name ) AS contact_name
            FROM contact
            WHERE company_id = {$row['company_id']}
            ";
        }
        $expContact = array('detailValue' => $row['contact_name']);

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Client'
        ORDER BY company_name
        ";
        $expComp = array('detailValue' => $row['company_name']);

		$validatedClient =	"{$formObj->getTBRow('Title', 'title', $row['title'])}
							 {$formObj->getDDRowBySQL('Client Name*', 'company_id', $sqlCompany, $row['company_id'], $expComp)}
							 {$formObj->getDDRowBySQL('Contact Person', 'contact_id', $sqlContact, $row['contact_id'], $expContact)}
			        		 {$formObj->getDDRowByArr('Quote Status', 'status', $cpCfg['m.trading.product.quoteProductStatusArr'], $row['status'])}
			        		 {$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}
			        		 {$formObj->getDDRowBySQL('Delivery Location', 'delivery_location', $sqlDeliveryLocation, $row['delivery_location'], $expVl)}
			        		 {$formObj->getDateRow('Delivery Date', 'delivery_date', $row['delivery_date'])}
			        		 {$formObj->getDateRow('Quote Date', 'quote_date', $row['quote_date'])}
			        		 {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
			        		 {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], $expVl)}
							";

        //$summaryDisplay = $this->getSummaryDisplayGeneralTrading($row);
        $summaryDisplay = '';

        $enquiryCode = '';
        if($row['enquiry_id'] != '') {
            $enquiryRecord = $fn->getRecordRowByID('enquiry', 'enquiry_id', $row['enquiry_id']);
            $enquiryCode = "{$formObj->getTBRow('Enquiry Code', 'enquiry_code', $enquiryRecord['enquiry_code'], $expNoEdit)}";
        }

        $fieldset1 = "
		{$formObj->getTBRow('Quotation Code', 'quote_code', $row['quote_code'], $expNoEdit)}
		{$enquiryCode}
		{$validatedClient}
   		{$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'])}
   		{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}
   		{$formObj->getTARow('Notes ', 'note', $row['note'])}
		{$formObj->getDDRowBySQL('Staff', 'staff_id', $sqlSalesManager, $staff_name, $expStaff)}
		{$formObj->getTBRow('Quote Type', 'quote_type', $row['quote_type'], $expNoEdit)}
		";

        $text = "
        <div class='summary'>
		    <div class='c50l'>
		    <div class='subcl'>
	        <div class='linkPortalWrapper'>
	            <div class='header'>
	                <div class='floatbox'>
	                    <div class='float_left' style='font-size:125%;'>Quote Header</div>
	                    <div class='toggle'> </div>
	                </div>
	            </div>
	            <div>
	                <div class='linkPortalDataWrapper'>
	                    {$formObj->getFieldSetWrapped('', $fieldset1)}
	                </div>
	            </div>
	        </div>
        	{$formObj->getCreationModificationText($row)}
	        </div>
	        </div>
	        <div class='c50r'>
    	        <div class='subcr'>
    	            {$summaryDisplay}
    	        </div>
	        </div>
        </div>

        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');

        if($cpCfg['m.tradingsg.quote.printQuoteGeneral']){
            $printText ="";
            $urlQuote = "index.php?module=tradingin_quote&_spAction=printQuoteExcelGeneral&id={$row['quote_id']}&showHTML=0";
        }
        else{
            $printText ="";
            $urlQuote = "index.php?module=tradingin_quote&_spAction=printQuoteExcel&id={$row['quote_id']}&showHTML=0";
        }
        $urlRaisePo = "index.php?module=tradingin_quote&_spAction=raisePurchaseOrder&id={$row['quote_id']}&showHTML=0";

        if ($cpCfg['m.tradingsg.quote.printQuoteGeneralTrading'] == 1) {
            $urlQuoteGeneral = "index.php?module=tradingin_quote&_spAction=printQuoteGeneralTrading&id={$row['quote_id']}&showHTML=0";
        } else {
            $urlQuoteGeneral = "index.php?module=tradingin_quote&_spAction=printQuoteExcelBasic&id={$row['quote_id']}&showHTML=0";
        }

        $formActionCategory = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateMarkupByCategoryForm&quote_id={$row['quote_id']}&showHTML=0";
        $formActionGroup = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateMarkupByGroupForm&quote_id={$row['quote_id']}&showHTML=0";
        $formActionDiscGroup = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateDiscountByGroupForm&quote_id={$row['quote_id']}&showHTML=0";
        $formActionUpdateDiscount = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateDiscountForm&quote_id={$row['quote_id']}&showHTML=0";
        $bulkGenerateUrl  = "index.php?module=tradingin_quote&_spAction=generateBulkProduct&id={$row['quote_id']}&showHTML=0";

        $updateDiscountByGroup = '';
        if ($cpCfg['m.tradingsg.quote.updateDiscountByGroup'] == 1) {
        	$updateDiscountByGroup = "
            <div class='button mb5'>
	            <a href='{$formActionDiscGroup}' id='updateDiscountByGroupForm'>Update Discount by Department</a>
            </div>
            ";
        }


        if ($cpCfg['m.tradingsg.quote.showExportExcellC2']) {
            $ExportToExcellC2 ="
            <div class='button mb5'>
                <a href='{$urlQuote}' id='print'>Export to Excel C2</a>
            </div>
            ";
        }

        if ($cpCfg['m.tradingsg.quote.printQuoteGeneralTrading'] == 1) {
            $ExportToExcellC1 ="
            <div class='button mb5'>
                <a href='{$urlQuoteGeneral}' id='print'>Export to Excel</a>
            </div>
            ";
        } else {
            $ExportToExcellC1 ="
            <div class='button mb5'>
                <a href='{$urlQuoteGeneral}' id='print'>Export to Excel C1</a>
            </div>
            ";
        }

        $deleteProductChecked ="
        <div class='button mb5'>
            <a href='#' id='deleteProductChecked' quote_id='{$row['quote_id']}'>Delete Checked Products</a>
        </div>
        ";

        $urlExportAsPdf = "index.php?module=tradingin_quote&_spAction=printExportAsPdf&id={$row['quote_id']}&showHTML=0";

        if ($cpCfg['countryForCurrency'] == 'India'){
            $exportAsPdf ="
            <div class='button mb5'>
                <a href='{$urlExportAsPdf}' target='blank' id='exportasPdf'>Export as PDF</a>
            </div>
            ";
        }

        $sqlQuoteProduct = "
        SELECT qp.quote_product_id, qp.quote_id
        FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resulQp = $db->sql_query($sqlQuoteProduct);
        $rowQp = $db->sql_fetchrow($resulQp);

        $generalQuotation = '';
        if ($rowQp['quote_id'] == '') {
            $generalQuotation ="
            <div class='button mb5'>
                <a href='#' id='raiseGeneralQuotation' quote_id='{$row['quote_id']}'>Raise General Quotation</a>
            </div>
            ";
        }

        if ($row['quote_type'] == 'General Quotation') {
            $printText .="
            <div class='floatbox  btnbackground'>
    	        <div class='actionBtnsDetail actionbtnwidth '>
    	            {$generalQuotation}
    	            <div class='button mb5'>
    	                <a href='#' id='convertClientRequirement' quote_id='{$row['quote_id']}'>Convert to Client Requirement</a>
    	            </div>
    	        </div>
    	        <div class='actionBtnsDetail actionbtnwidth'>
    	            {$ExportToExcellC1}
    	            {$deleteProductChecked}
			        {$exportAsPdf}
    	        </div>
            </div>
            ";
        } else {
            $raisePo = '';
            if ($_SESSION['userGroupType'] != "User") {
                $raisePo = "
	            <div class='button mb5'>
	                <a href='#' id='raisePo' quote_id='{$row['quote_id']}'>Raise PO</a>
	            </div>
                ";
            }

            $printText .="
            <div class='floatbox  btnbackground'>
    	        <div class='actionBtnsDetail actionbtnwidth '>
                    {$raisePo}
    	            <div class='button mb5'>
    	                <a href='#' id='raiseInvoice' quote_id='{$row['quote_id']}'>Raise Invoice</a>
    	            </div>
    	        </div>
    	        <div class='actionBtnsDetail actionbtnwidth'>
                    <div class='button mb5'>
    	                <a href='{$formActionGroup}' id='updateMarkupByGroup'>Update Markup by Group</a>
    	            </div>
    	            <div class='button mb5'>
    	                <a href='{$formActionUpdateDiscount}' id='updateDiscountForm'>Update Discount</a>
    	            </div>
    	            {$updateDiscountByGroup}
    	        </div>
    	        <div class='actionBtnsDetail actionbtnwidth'>
    	            {$ExportToExcellC1}
	                {$deleteProductChecked}
			        {$exportAsPdf}
    	        </div>
            </div>
            ";
        }


	            /*<div class='button mb5'>
	                <a href='{$formActionCategory}' id='updateMarkupByCategory'>Update Markup by Category</a>
	            </div>
	            <div class='button mb5'>
	                <a href='#' id='deleteProducts' quote_id='{$row['quote_id']}'>Delete Products Linked</a>
	            </div>

	            */

        $SQL = "
        SELECT count(*) AS total
        FROM quote_product
        WHERE quote_id = {$row['quote_id']}
        ";
        $result = $db->sql_query($SQL);
        $rowCount = $db->sql_fetchrow($result);

        $SQLTotalCp = "
        SELECT SUM(round(qp.cost_price * qp.qty,2)) AS total_cost_price_sum
        FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resultTotalCp = $db->sql_query($SQLTotalCp);
        $rowTotalCp = $db->sql_fetchrow($resultTotalCp);

        $SQLMarkUp = "
        SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2)) AS mark_up_amount_sum
        FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resultMarkUp = $db->sql_query($SQLMarkUp);
        $rowMarkUp = $db->sql_fetchrow($resultMarkUp);

        $SQLDiscount = "
        SELECT SUM(round(((qp.cost_price * qp.discount_percentage)/100) * qp.qty,2)) AS discount_percentage_amount_sum
        FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resultDiscount = $db->sql_query($SQLDiscount);
        $rowDiscount = $db->sql_fetchrow($resultDiscount);

        $SQLTotalSp = "
        SELECT SUM(format((qp.selling_price * qp.qty),2)) AS total_selling_price_sum
        FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resultTotalSp = $db->sql_query($SQLTotalSp);
        $rowTotalSp = $db->sql_fetchrow($resultTotalSp);

        $orderRec = $fn->getRecordByCondition('order', "quote_id = '{$row['quote_id']}'");

        $urlInvoice = "index.php?_topRm=finance&module=tradingin_order&_action=edit&record_id={$orderRec['order_id']}";
        $invoiceLink = '';
        if ($orderRec['order_id'] != '') {
            $invoiceLink ="<a href='{$urlInvoice}'>Go To Invoice</a>";
        }

        $bulkAdd = "<a href='{$bulkGenerateUrl}' id='bulkAddProduct'>Generate Bulk Items</a>";

        $poLink = '';
        if ($_SESSION['userGroupType'] != "User") {
            $poLink = $displayLinkData->getLinkPortalMain('tradingin_quote', 'tradingsg_purchaseOrderLink', 'Purchase Order Linked', $row);
        }

        $text = "
		{$comment->getView(array(
		     'roomName' => 'tradingin_quote'
		    ,'recordId' => $row['quote_id']
		    ,'allowEdit' => false
		    ,'allowDelete' => false
		    ,'addReviewLbl' => 'Add Activity'
		    ,'heading' => 'Activities'
		))}
        <div class='subcolumns summary'>
            <div class='c50l'>
            <div class='subcl'>
                {$media->getRightPanelMediaDisplay("Attachments", "tradingin_quote", "attachment", $row)}
            </div>
            </div>
            <div class='c50r'>
            <div class='subcr'>
                {$displayLinkData->getLinkPortalMain('tradingin_quote', 'tradingsg_expenseLink', 'Expense Linked', $row)}
            </div>
            </div>
        </div>

        {$printText}
        {$displayLinkData->getLinkPortalMain('tradingin_quote', 'tradingsg_productLink',
                                             'Products Linked - No. of items (' . $rowCount['total'] .')
                                             ' . $invoiceLink .' ' . $bulkAdd .'
                                              ', $row)}
        {$poLink}
        ";

          /*- Total Cost Price (' . $rowTotalCp['total_cost_price_sum'] .')
          - Total Mark Up (' . $rowMarkUp['mark_up_amount_sum'] .')
          - Total Discount (' . $rowDiscount['discount_percentage_amount_sum'] .')
          - Total Selling Price (' . $rowTotalSp['total_selling_price_sum'] .')*/
        return $text;
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getRaiseSOList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');

        $quote_id = $fn->getReqParam('quote_id');

        $SQL = "
        SELECT qi.quote_items_id
              ,CONCAT_WS('-', q.quote_code, qi.line_no) AS line_no
              ,p.product_code
              ,p.product_id
              ,p.title AS product_name
              ,p.unit
              ,IF(qi.record_type = 'product', qr.buy_currency, po.buy_currency) AS buy_currency
              ,qi.quantity
              ,qi.buy_unit_price
              ,qi.sell_unit_price
              ,qi.valid_until
              ,qi.status
              ,q.quote_id
              ,q.sell_currency
        FROM quote_items qi
        JOIN quote q   ON (q.quote_id = qi.quote_id)
        JOIN product p ON (p.product_id = qi.product_id)
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        LEFT JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        LEFT JOIN purchase_order_items poi ON (poi.purchase_order_items_id = qi.purchase_order_items_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = poi.purchase_order_id)
        WHERE qi.quote_id = {$quote_id}
        ORDER BY qi.line_no
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity w65' type='text' value='{$row['quantity']}'
                   name='quantity__{$row['quote_items_id']}' />
            ";
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['quote_items_id']}'
                   name='quote_items_ids[]' checked='checked' />
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['quote_items_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
                   );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['quantity'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['buy_unit_price'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['sell_unit_price'])}
            {$listObj->getListDataCell($row['valid_until'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($quantityText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['quote_items_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseSOCancel' />
            <input type='button' value='Raise SO' id='btnRaiseSO' />
            </div>
        </form>
        ";

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Line #')}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Buy Currency')}
            {$listObj->getListHeaderCell('Buy Unit Price')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Sell Unit Price')}
            {$listObj->getListHeaderCell('Valid Until')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Sales Order Quantity')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status     	= $fn->getReqParam('status');
        $priority   	= $fn->getReqParam('priority');
        $company_id 	= $fn->getReqParam('company_id');
        $quoteDate1     = $fn->getReqParam('quoteDate1');
        $quoteDate2     = $fn->getReqParam('quoteDate2');
        $deliveryDate1  = $fn->getReqParam('deliveryDate1');
        $deliveryDate2  = $fn->getReqParam('deliveryDate2');
        $yearEnd = date('Y') + 10;

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

       //$sqlCompany = $fn->getDDSql('tradingin_company');

        $sqlCompany = "
        SELECT DISTINCT company_id, company_name FROM company
        WHERE category = 'Client'
        ORDER BY company_name
        ";

		if($quoteDate1 == ''){
			$quoteDate1 = 'From';
		}

		if($quoteDate2 == ''){
			$quoteDate2 = 'To';
		}

        $text = "
        <td>
            <select name='priority'>
                <option value=''>Priority</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.product.quoteProductPriorityArr'], $priority)}
            </select>
        </td>
        <td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>

        <td class='dateRange'>
            Quote Date:
            <input type='text' allowEdit='1' name='quoteDate1' class='fld_date'
                   id='fld_quoteDate1' value='{$quoteDate1 }' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='quoteDate2' class='fld_date'
                   id='fld_quoteDate2' value='{$quoteDate2}' yearEnd='{$yearEnd}' />
        </td>

        <td class='dateRange'>
            Delivery Date:
            <input type='text' allowEdit='1' name='deliveryDate1' class='fld_date'
                   id='fld_deliveryDate1' value='{$deliveryDate1 }' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='deliveryDate2' class='fld_date'
                   id='fld_deliveryDate2' value='{$deliveryDate2}' yearEnd='{$yearEnd}' />
        </td>

        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.product.quoteProductStatusArr'], $status)}
            </select>
        </td>
        <!--<td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>-->
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryDisplayGeneralTrading($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $row['company_id']);

        //TO CHECK IF THE SUM OF MARK UP TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND qp.mark_up_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND qp.mark_up_type = '%'
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }

        //TO CHECK IF THE SUM OF MARK UP TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.mark_up * qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND qp.mark_up_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.mark_up * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND qp.mark_up_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForDiscPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.discount_percentage)/100)* qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND qp.discount_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForDiscPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlDiscForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.discount_percentage)/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND qp.discount_type = '%'
            ";
        }
        else{
            $subSqlDiscForPercentSum = 0;
        }

        $subSqlDiscForValueSum ="
        SELECT SUM(round(qp.discount_percentage * qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND qp.discount_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlDiscForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlDiscForValueSum ="
            SELECT SUM(round(qp.discount_percentage * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND qp.discount_type = 'Value'
            ";
        }
        else{
            $subSqlDiscForValueSum = 0;
        }



        //discount value need not be subracted from selling price as below.
        $SQLQuoteProd = "
        SELECT (SUM(qp.selling_price * qp.qty)) AS total_selling_price
              ,(SUM(qp.cost_price * qty)) AS total_cost_price
              ,(count(qp.qty)) AS total_count
              ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)
               )
               as total_mark_up
              ,(SELECT
              ($subSqlDiscForPercentSum)
               +
              ($subSqlDiscForValueSum)
               )
               as total_discount
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
        ";
        $resultQuoteProd = $db->sql_query($SQLQuoteProd);
        $rowQuoteProd    = $db->sql_fetchrow($resultQuoteProd);

        $total_selling_price = number_format($rowQuoteProd['total_selling_price'], 2);
        $total_cost_price    = number_format($rowQuoteProd['total_cost_price'], 2);

        /*
        $total_discount_percentage = 0;
        if ($rowQuoteProd['total_count']){
            $total_discount_percentage = $rowQuoteProd['total_discount_percentage'] / $rowQuoteProd['total_count'];
        }
        */
        //$total_discount_value = ($total_selling_price * $total_discount_percentage)/100;
        $total_discount_value = $rowQuoteProd['total_discount'];

        $SQLExpense = "
        SELECT (SUM(amount)) AS total_amount
        FROM expense
        WHERE quote_id = {$row['quote_id']}
        ";
        $resultExpense = $db->sql_query($SQLExpense);
        $rowExpense    = $db->sql_fetchrow($resultExpense);


        /* Total of Items before Customer Type Discount */
        /*
        $total_profit =   $rowQuoteProd['total_selling_price']
                        - $rowQuoteProd['total_cost_price'];
        $total_profit_format = number_format($total_profit, 2);
        */

        $total_discount_format = 0;

        $total_discount_format = number_format($total_discount_value, 2);

        //$total_profit = $total_profit - $total_discount_format;
        $total_profit = $rowQuoteProd['total_mark_up'];
        $total_profit_format = number_format($total_profit, 2);
	    $printTaxName = $cpCfg['printTaxName'] ;

        $text = "
        <div class='linkPortalWrapper'>
        <div class='header'>
            <div class='floatbox'>
                <div class='float_left'>Summary</div>
                <div class='toggle'> </div>
            </div>
        </div>

        <div>
            <div class='linkPortalDataWrapper'>
                <table class='thinlist mb10'>
                    <thead>
                        <th>Line Items</th>
                        <th class='txtRight'>Amount</th>
                    </thead>

                    <tr>
                        <td>Status</td>
                        <td class='txtRight'>{$row['status']}</td>
                    </tr>

                    <tr>
                        <td>Total Selling Price</td>
                        <td class='txtRight'>{$total_selling_price}</td>
                    </tr>

                    <tr>
                        <td>Total Cost Price</td>
                        <td class='txtRight'>{$total_cost_price}</td>
                    </tr>

                    <tr>
                        <td>Other Expense</td>
                        <td class='txtRight'>{$rowExpense['total_amount']}</td>
                    </tr>

                    <tr>
                        <td>Total Discount</td>
                        <td class='txtRight'>{$total_discount_format}</td>
                    </tr>

                    <tr>
                        <td>Total Profit</td>
                        <td class='txtRight'>{$total_profit_format}</td>
                    </tr>

                    <tr>
                        <td>Prepared By</td>
                        <td class='txtRight'></td>
                    </tr>
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
    function getQuoteSummaryByProductGroup($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $companyRec = $fn->getRecordRowById('company', 'company_id', $row['company_id']);

        $SQLQuoteProd = "
        SELECT qp.*
              ,pg.title AS product_group_title
              ,(SELECT SUM(qph.qty * qph.selling_price)
                FROM quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = {$row['quote_id']}
                    AND pgp.product_group_id = pg.product_group_id) AS sub_total
              ,(SELECT SUM(round(((qph.cost_price * qph.discount_percentage)/100) * qph.qty,2))
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS discount_total
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE qp.quote_id = {$row['quote_id']}
        GROUP BY pg.title
        ORDER BY pg.sort_order ASC, qp.quote_product_id
        ";
        $resultQuoteProd = $db->sql_query($SQLQuoteProd);

        while ($rowQuoteProd = $db->sql_fetchrow($resultQuoteProd)) {
            $rows .= "
            <tr>
                <td>{$rowQuoteProd['product_group_title']}</td>
                <td class='txtRight'>{$rowQuoteProd['sub_total']}</td>
            </tr>
            <tr>
                <td>{$rowQuoteProd['product_group_title']} DISCOUNT ({$rowQuoteProd['discount_percentage']}%)</td>
                <td class='txtRight'>{$rowQuoteProd['discount_total']}</td>
            </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper'>
        <div class='header'>
            <div class='floatbox'>
                <div class='float_left'>Summary by Department</div>
                <div class='toggle'> </div>
            </div>
        </div>

        <div>
            <div class='linkPortalDataWrapper'>
                <table class='thinlist mb10'>
                    <thead>
                        <th>Line Items</th>
                        <th class='txtRight'>Amount</th>
                    </thead>
                    {$rows}
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
     function getUpdateMarkupByGroupForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $quote_id  = $fn->getReqParam('quote_id');

        $formAction = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateMarkupByGroupFormSubmit&showHTML=0";

		$sqlProductGroup = "
		SELECT product_group_id
			  ,title
		FROM product_group
		";
        $sqlCategory = '';

        $text = "
        <form id='portalForm' class='yform columnar updateMarkupForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Product Department', 'product_group_id', $sqlProductGroup)}
            {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
            {$formObj->getTBRow('Mark Up(%)', 'profit_percent')}
            <input type='hidden' name='quote_id' value='{$quote_id}' />
        </form>
        ";
        return $text;

    }
    /**
     *
     */
     function getUpdateDiscountByGroupForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $quote_id  = $fn->getReqParam('quote_id');

        $formAction = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateDiscountByGroupFormSubmit&showHTML=0";

		$sqlProductGroup = "
		SELECT product_group_id
			  ,title
		FROM product_group
		";
        $sqlCategory = '';

        $text = "
        <form id='portalForm' class='yform columnar updateMarkupForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Product Department', 'product_group_id', $sqlProductGroup)}
            {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
            {$formObj->getTBRow('Discount %', 'discount_percentage')}
            <input type='hidden' name='quote_id' value='{$quote_id}' />
        </form>
        ";
        return $text;

    }
    /**
     *
     */

     function getUpdateDiscountForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $quote_id  = $fn->getReqParam('quote_id');

        $formAction = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateDiscountFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar updateMarkupForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Discount %', 'discount_percentage')}
            <input type='hidden' name='quote_id' value='{$quote_id}' />
        </form>
        ";
        return $text;

    }
    /**
     *
     */
     function getUpdateMarkupByCategoryForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $quote_id  = $fn->getReqParam('quote_id');

        $formAction = "index.php?_topRm=order&module=tradingin_quote&_spAction=updateMarkupByCategoryFormSubmit&showHTML=0";

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $text = "
        <form id='portalForm' class='yform columnar updateMarkupForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
            {$formObj->getTBRow('Mark Up', 'profit_percent')}
            <input type='hidden' name='quote_id' value='{$quote_id}' />
        </form>
        ";
        return $text;

    }

    /**
     *
     */
    function getAddNotePo() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('id');

        $formAction = "index.php?_topRm=order&module=tradingin_quote&_spAction=addNoteFormSubmit&showHTML=0";
        $purchaseOrderRec = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $text = "
        <form id='portalForm' class='yform columnar addNoteForm' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'notes', $purchaseOrderRec['notes'])}
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getPrintQuoteExcelGeneral() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $quote_id  = $fn->getReqParam('id');
        $template = 'Quote-Export.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Quote-Product_' . $quote_id . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.unit
              ,q.quote_code
              ,q.quote_date
              ,c.company_name
              ,c.customer_type
              ,(SELECT SUM(qph.qty * qph.selling_price) FROM  quote_product qph
               WHERE qph.quote_id = qp.quote_id) AS sub_total
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $blkProduct     = array();
        $blkQty         = array();
        $blkUom         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();

        while ($row = $db->sql_fetchrow($result)) {
            //repoeating rows of product values
            $arr1 = array('product_title' => $row['product_title']);
            $blkProduct[] = $arr1;

            $arr2 = array('qty' => $row['qty']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $row['serial_no']);
            $blkSerialNo[] = $arr3;

            $arr4 = array('unit' => $row['unit']);
            $blkUom[] = $arr4;

            $arr5 = array('selling_price' => $row['selling_price']);
            $blkPrice[] = $arr5;

            $arr6 = array('amount' => number_format($row['selling_price'] * $row['qty'], 2));
            $blkAmount[] = $arr6;

            //Header Part and TOtal/subtotal
            $arr['quote_code']   = $row['quote_code'];
            $arr['company_name'] = $row['company_name'];
            $arr['quote_date'] = $row['quote_date'];
            $arr['sub_total'] = $row['sub_total'];
            $discount =  ($row['sub_total'] * $row['discount_percentage'])/100;
            $arr['discount'] =  number_format($discount, 2);
            $arr['discount_per'] = $row['discount_percentage'];
            $arr['total'] =  number_format($arr['sub_total'] - $arr['discount'], 2);
            //$arr['usd_total'] =  number_format($arr['total'] / $cpCfg['convertToUSD'], 2);

            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('blkUom', $blkUom);
        $TBS->MergeBlock('blkPrice', $blkPrice);
        $TBS->MergeBlock('blkAmount', $blkAmount);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);

    }
    /**
     *
     */
    function getPrintQuoteExcel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $quote_id  = $fn->getReqParam('id');
        $template = 'Quote-Export.xlsx';
        $template = 'quote_excel_export.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();

        /*
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        */
        $today =  date('d/m/Y');
        $discount_amount_total = '';
        $mark_up_amount_total  = '';

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.item_code
              ,p.unit
              ,pg.title as product_group_title
              ,pg.product_group_id
              ,q.quote_code
              ,q.quote_date
              ,q.company_id
              ,c.company_name
              ,c.customer_type
              ,(SELECT SUM(qph.qty * qph.cost_price)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS sub_total
              ,(SELECT SUM(qph.cost_price) * (qph.discount_percentage/100)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS discount_total
              ,(SELECT SUM(qph.cost_price) * (qph.mark_up/100)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS mark_up_total
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE q.quote_id = {$quote_id}
        AND qp.product_id > 0
        ORDER BY pg.sort_order ASC, qp.quote_product_id
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $headerArray    = array();
        $blkProduct     = array();
        $blkQty         = array();
        $blkUom         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();
        //remove the cfg value below
        //$cpCfg['convertToUSD'] = 1.21;
        global $TeamList;
        $TeamList    = array();
        $product_group_title = '';
        // 16 is the starting row number in the excell sheet used for formatting.
        $start_row = 16;
        $count = -1;

        $headerArray[] = array('itemNoHeader'=>'ITEM NO'
        ,'itemCodeHeader'=>'ITEM CODE'
        ,'itemNameHeader'=>'NAME OF THE ITEMS'
        ,'qtyHeader'=>'QTY'
        ,'unitSizeHeader'=>'UOM'
        ,'unitPriceHeader'=>'UP'
        ,'totalCostPriceHeader'=>'TOTAL BUYING PRICE'
        ,'discount'=>'DISCOUNT (%)'
        ,'serviceCost'=>'SERVICE COST (%)'
        ,'sellingCost'=>'SELLING PRICE'
        ,'amtHeader'=>'TOTAL SALE AMOUNT'
        );

        while ($row = $db->sql_fetchrow($result)) {
            //Header Part and TOtal/subtotal
            $arr['quote_code']   = $row['quote_code'];
            $arr['company_name'] = $row['company_name'];
            $arr['quote_date']   = $fn->getCPDate($row['quote_date'], 'd-m-Y');
            //$arr['start_row']    = 16;

            $blkMain[] = $arr;

            $discountRec = $fn->getRecordByCondition('discount',
                                                    "company_id = '{$row['company_id']}'
                                                    AND product_group_id = '{$row['product_group_id']}'
                                                    AND category_id IS NULL
                                                    ");
            //========================================
            if($product_group_title != $row['product_group_title']
            ){
                $discount_amount_total = '';
                $mark_up_amount_total  = '';
                $count++;
                $discount  =  ($row['sub_total'] * $row['discount_percentage'])/100;
                $mark_up   =   ($row['sub_total'] * $row['mark_up'])/100;
                $sub_total = $discount + $mark_up + $row['sub_total'];
                //$total    = number_format($row['sub_total'] - $discount, 2);
                $TeamList[$count] = array(
                    'product_group_title' => $row['product_group_title'],
                    'sub_total'           => number_format($sub_total, 2),
                    'discount'            => number_format($discount,2),
                    'mark_up_total'       => number_format($row['mark_up_total'], 2),
                    'discount_percent'    => number_format($discountRec['discount_percent']),
                    'mark_up'             => number_format($discountRec['margin'])  ,
                    'mark_up_total'       => number_format($row['mark_up_total'], 2),
                    'discount_total'      => number_format($row['discount_total'], 2),
                    'total'               => number_format($sub_total, 2),
                    'usd_value'           => $cpCfg['convertToUSD'],
                    'usd_total'           => number_format(
                    ($sub_total/$cpCfg['convertToUSD']), 2),
                    'empty_space'         => '',
                    'start_row'         => $start_row
                    );
                $start_row = $start_row + 10;
            }
            $start_row++;
            $discount_amount =  ($row['cost_price'] * $row['discount_percentage'])/100;
            $mark_up_amount  =  ($row['cost_price'] * $row['mark_up'])/100 ;
            $selling_price = number_format($discount_amount,2)
            + number_format($mark_up_amount,2)
            + $row['cost_price'] ;

            $discount_amount_total += $discount_amount_total ;
            $TeamList[$count]['matches'][] = array(
              'product_title'=>$row['product_title']
             ,'qty'=>$row['qty']
             ,'product_code'=>$row['item_code']
             ,'serial_no'=>$serialNo
             ,'unit'=>$row['unit']
             ,'mark_up_percent'=> $row['mark_up']
             ,'discount_percentage'=> $row['discount_percentage']
             ,'cost_price_total'    => number_format($row['cost_price'] * $row['qty'] , 2)
             ,'discount_amount'=>number_format($discount_amount * $row['qty'], 2)
             ,'mark_up_amount'=>number_format($mark_up_amount * $row['qty'], 2)
             ,'cost_price'=>$row['cost_price']
             ,'selling_price'=>number_format($selling_price, 2)
             ,'amount'=>number_format($selling_price * $row['qty'], 2)
            );

            if($product_group_title != $row['product_group_title']
               && $product_group_title != ''
            ){
            }
            $product_group_title = $row['product_group_title'];

            //$date = $fn->getCPDate($row['quote_date'], 'd-m-Y');
            $date = $dateUtil->formatDate($row['quote_date'], 'DD MM YYYY');
            $file_name = $row['company_name'] . '_' . $row['quote_code'] . '_' . $date;
            $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

            $serialNo++;
        }

            //print_r ($TeamList);
            //return;
        $TBS->MergeBlock('blkH', $headerArray);
        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('mb', $TeamList);
        $TBS->MergeBlock('mb','array','TeamList');
        $TBS->MergeBlock('sb','array','TeamList[%p1%][matches]');
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintQuoteExcelBasic() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $quote_id  = $fn->getReqParam('id');
        $template = 'Quote-Export-Basic.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();

        /*
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        */
        $today =  date('d/m/Y');

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.item_code
              ,p.unit
              ,pg.title as product_group_title
              ,pg.product_group_id
              ,q.quote_code
              ,q.quote_date
              ,q.company_id
              ,c.company_name
              ,c.customer_type
              ,(SELECT SUM(qph.qty * qph.cost_price)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS sub_total
              ,(SELECT SUM(qph.cost_price) * (qph.discount_percentage/100)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS discount_total
              ,(SELECT SUM(qph.cost_price) * (qph.mark_up/100)
                FROM  quote_product qph
                JOIN product p ON (p.product_id = qph.product_id)
                JOIN product_group pgp ON (p.product_group_id = pgp.product_group_id)
                WHERE qph.quote_id = qp.quote_id
                    AND pgp.product_group_id = pg.product_group_id) AS mark_up_total
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE q.quote_id = {$quote_id}
        ORDER BY pg.sort_order ASC
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $headerArray    = array();
        $blkProduct     = array();
        $blkQty         = array();
        $blkUom         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();
        //remove the cfg value below
        //$cpCfg['convertToUSD'] = 1.21;
        global $TeamList;
        $TeamList    = array();
        $product_group_title = '';
        // 16 is the starting row number in the excell sheet used for formatting.
        $start_row = 16;
        $count = -1;

        $headerArray[] = array('itemNoHeader'=>'ITEM NO'
        ,'itemCodeHeader'=>'ITEM CODE'
        ,'itemNameHeader'=>'NAME OF THE ITEMS'
        ,'qtyHeader'=>'QTY'
        ,'unitSizeHeader'=>'UOM'
        ,'unitPriceHeader'=>'UNIT PRICE'
        ,'totalCostPriceHeader'=>'TOTAL BUYING PRICE'
        ,'discount'=>'DISCOUNT (%)'
        ,'serviceCost'=>'SERVICE COST (%)'
        ,'sellingCost'=>'SELLING PRICE'
        ,'amtHeader'=>'TOTAL SALE AMOUNT'
        );

        while ($row = $db->sql_fetchrow($result)) {
            //Header Part and TOtal/subtotal
            $arr['quote_code']   = $row['quote_code'];
            $arr['company_name'] = $row['company_name'];
            $arr['quote_date']   = $fn->getCPDate($row['quote_date'], 'd-m-Y');
            //$arr['start_row']    = 16;

            $blkMain[] = $arr;

            //========================================
            if($product_group_title != $row['product_group_title']
            ){
                $discount_amount_total = '';
                $mark_up_amount_total  = '';
                $count++;
                $discount  =  ($row['sub_total'] * $row['discount_percentage'])/100;
                $mark_up   =   ($row['sub_total'] * $row['mark_up'])/100;
                $sub_total = $discount + $mark_up + $row['sub_total'];
                //$total    = number_format($row['sub_total'] - $discount, 2);
                $TeamList[$count] = array(
                    'product_group_title' => $row['product_group_title'],
                    'sub_total'           => number_format($sub_total, 2),
                    'discount'            => number_format($discount,2),
                    'mark_up_total'       => number_format($row['mark_up_total'], 2),
                    'discount_percent'    => $row['discount_percentage']  ,
                    'mark_up'             => $row['mark_up']  ,
                    'mark_up_total'       => number_format($row['mark_up_total'], 2),
                    'discount_total'      => number_format($row['discount_total'], 2),
                    'total'               => number_format($sub_total, 2),
                    'usd_value'           => $cpCfg['convertToUSD'],
                    'usd_total'           => number_format(
                    ($sub_total/$cpCfg['convertToUSD']), 2),
                    'empty_space'         => '',
                    'start_row'         => $start_row
                    );
                $start_row = $start_row + 8;
            }
            $start_row++;
            $discount_amount =  ($row['cost_price'] * $row['discount_percentage'])/100;
            $mark_up_amount  =  ($row['cost_price'] * $row['mark_up'])/100 ;
            $selling_price = number_format($discount_amount,2)
            + number_format($mark_up_amount,2)
            + $row['cost_price'] ;

            $discount_amount_total += $discount_amount_total ;
            $TeamList[$count]['matches'][] = array(
              'product_title'=>$row['product_title']
             ,'qty'=>$row['qty']
             ,'product_code'=>$row['item_code']
             ,'serial_no'=>$serialNo
             ,'unit'=>$row['unit']
             ,'cost_price_total'    => number_format($row['cost_price'] * $row['qty'] , 2)
             ,'discount_amount'=>number_format($discount_amount * $row['qty'], 2)
             ,'mark_up_amount'=>number_format($mark_up_amount * $row['qty'], 2)
             ,'cost_price'=>$row['cost_price']
             ,'selling_price'=>number_format($selling_price, 2)
             ,'amount'=>number_format($selling_price * $row['qty'], 2)
            );

            if($product_group_title != $row['product_group_title']
               && $product_group_title != ''
            ){
            }
            $product_group_title = $row['product_group_title'];

            //$date = $fn->getCPDate($row['quote_date'], 'd-m-Y');
            $date = $dateUtil->formatDate($row['quote_date'], 'DD MM YYYY');
            $file_name = $row['company_name'] . '_' . $row['quote_code'] . '_' . $date;
            $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

            $serialNo++;
        }

            //print_r ($TeamList);
            //return;
        $TBS->MergeBlock('blkH', $headerArray);
        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('mb', $TeamList);
        $TBS->MergeBlock('mb','array','TeamList');
        $TBS->MergeBlock('sb','array','TeamList[%p1%][matches]');
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintQuoteGeneralTrading() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $quote_id  = $fn->getReqParam('id');
        $template = 'Quote-General-Trading.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Quote-Product_' . $quote_id . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
		$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = '%'
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.discount_percentage  * qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.discount_percentage  * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,p.part_number
              ,q.quote_code
              ,q.quote_date
              ,q.payment_terms
              ,c.company_name
              ,c.address_flat
              ,c.address_street
              ,c.address_town
              ,c.address_state
              ,c.address_country
              ,c.customer_type
              ,(SELECT SUM(qph.qty * qph.selling_price) FROM  quote_product qph
               WHERE qph.quote_id = qp.quote_id) AS total
              ,(SELECT
               ($subSqlForPercentSum)
                +
               ($subSqlForValueSum)
               )
               as discount_percentage_amount_sum
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $blkProduct     = array();
        $blkQty         = array();
        $blkUom         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();
        $selling_price = '';

        while ($row = $db->sql_fetchrow($result)) {
            //repeating rows of product values
            $arr1 = array('product_title' => $row['product_title']);
            $blkProduct[] = $arr1;

            $arr2 = array('qty' => $row['qty']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('unit' => $row['unit']);
            $blkUom[] = $arr4;

            if($row['mark_up_type'] == '%'){
                $selling_price = $row['cost_price'] + ($row['cost_price'] * ($row['mark_up']/100));
            }
            else if($row['mark_up_type'] == 'Value'){
                $selling_price = $row['cost_price']  + $row['mark_up'];
            } else {
                $selling_price = $row['cost_price'];
            }

            $arr5 = array('selling_price' => number_format($selling_price,2));
            $blkPrice[] = $arr5;

            $arr6 = array('amount' => number_format($selling_price * $row['qty'], 2));
            $blkAmount[] = $arr6;

            $arr7 = array('item_code' => $row['item_code']);
            $blkItemCode[] = $arr7;

            $arr8 = array('part_number' => $row['part_number']);
            $blkPartNumber[] = $arr8;

            $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

            //Header Part and Total/subtotal
            $arr['quote_code']   = $row['quote_code'];
            $arr['company_name'] = $row['company_name'];
            $arr['address_flat'] = $row['address_flat'];
            $arr['address_street'] = $row['address_street'];
            $arr['address_town'] = $row['address_town'];
            $arr['address_state'] = $row['address_state'];
            $arr['address_country'] = $row['address_country'];
            $arr['quote_date'] = $quote_date;
            $arr['sub_total'] = number_format($row['total'] + $row['discount_percentage_amount_sum'], 2);
            $arr['payment_terms'] = $row['payment_terms'];
            $arr['discount'] = $row['discount_percentage_amount_sum'];

            //$tax =  ($row['sub_total'] * $gsttaxvalue)/100;
            //$arr['tax'] =  number_format($tax, 2);
            $arr['total'] =  number_format($row['total'], 2);
            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('blkItemCode', $blkItemCode);
        $TBS->MergeBlock('blkUom', $blkUom);
        $TBS->MergeBlock('blkPrice', $blkPrice);
        $TBS->MergeBlock('blkAmount', $blkAmount);
        $TBS->MergeBlock('blkPartNumber', $blkPartNumber);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }
    /**
     *
     */
    function getPrintPOExcel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $purchase_order_id  = $fn->getReqParam('id');
        $template = 'Po-Product.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Po-Product_' . $purchase_order_id . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
        WHERE pop.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $blkProduct     = array();
        $blkQty         = array();
        $blkUom         = array();
        $blkPrice       = array();
        $blkSerialNo    = array();
        $blkAmount    = array();

        while ($row = $db->sql_fetchrow($result)) {
            $arr1 = array('product_title' => $row['product_title']);
            $blkProduct[] = $arr1;

            $arr2 = array('qty' => $row['qty']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('unit' => $row['unit']);
            $blkUom[] = $arr4;

            $arr['company_name'] = $row['company_name'];

            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('blkUom', $blkUom);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);

    }
    /**
     *
     */
    function getPrintPurchaseOrder() {
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

        $purchase_order_id = $fn->getReqParam('id');

        $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
              ,c.fax
              ,c.phone
              ,pop.creation_date
              ,po.po_code
              ,q.quote_code
              ,q.delivery_date
              ,q.delivery_location
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE pop.purchase_order_id = {$purchase_order_id}
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
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, 'Authorized Distributor of:');
                $pdf->SetXY(10,25);
                $pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['creation_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

                /* Company address */
                //Address to be got from settings
                /*
                $pdf->Cell(50, 20, '25 LORONG 39 GEYLANG SINGAPORE 387875');
                $pdf->Ln(5);
                $pdf->SetXY(140,5);

                $pdf->Cell(50, 20, 'TEL: +65 674 74 126 FAX: +65 674 84 322');
                $pdf->Ln(5);
                $pdf->SetXY(140,10);

                $pdf->Cell(50, 20, 'EMAIL: enquiry@novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140, 15);

                $pdf->Cell(50, 20, 'WEBSITE: www.novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140,20);
                $pdf->Cell( 50, 20, 'GST REG. NO: 201203469M');
                */
                $pdf->SetXY(140,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->SetFont('Courier','',8);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(140,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(140, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "PURCHASE ORDER", 0, 0, 'C');
                $pdf->Ln(20);


                /* Company Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(100,8,"VENDOR",1,0, 'L', 1);
                $pdf->Cell(45,8,"TEL",1,0, 'L', 1);
                $pdf->Cell(45,8,"FAX",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(100, 8, $row['company_name'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['phone'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['fax'], 1, 0, 'L', 1);
                $pdf->Ln(20);

			    $quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

                /* Purchase Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(70,8,"QUOTE CODE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO DATE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO CODE",1,0, 'L', 1);
                $pdf->Cell(60,8,"DELIVERY DATE",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            //$pdf->Cell(70, 8, $formatedQC[3], 1, 0, 'L', 1);
	            $pdf->Cell(70, 8, $quoteCode, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $creationDate, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $row['po_code'], 1, 0, 'L', 1);
	            $pdf->Cell(60, 8, $deliveryDate, 1, 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(190,8,"LOCATION: {$row['delivery_location']}",1,0, 'L', 1);
                //$pdf->Cell(30,8,"TIME: AM(TBC)",1,0, 'L', 1);
                //$pdf->Cell(33,8,"DELIVERY DATE : ",'TBL',0, 'L', 1);
				//$pdf->Cell(27,8, $deliveryDate, 'TBR', 0, 'L', 1);
                $pdf->Ln(10);

				$pdf->Cell(30,15,"(Note : Please mention the exact Item Code for all the products.)",0,0, 'L', 1);
                $pdf->Ln(10);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25,8,"ITEM CODE",1,0, 'C', 1);
                $pdf->Cell(145,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(25, 8, $row['item_code'], 1, 0, 'L', 1);
            //$pdf->Cell(145, 8, substr($row['product_title'], 0, 61), 1, 0, 'L', 1);
            $pdf->Cell(145, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
        }

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
    function getPrintPurchaseOrderWithPrice() {
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

        $purchase_order_id = $fn->getReqParam('id');

        $SQL = "
        SELECT pop.*
              ,p.title AS product_title
              ,p.unit
              ,p.item_code
              ,c.company_name
              ,c.fax
              ,c.phone
              ,pop.creation_date
              ,q.delivery_date
              ,q.delivery_location
              ,pop.price
              ,po.po_code
              ,q.quote_code
              ,q.currency
              ,pop.qty * pop.price AS amount
              ,(SELECT SUM(popp.qty * popp.price) FROM po_product popp
               WHERE popp.purchase_order_id = pop.purchase_order_id) AS sub_total
        FROM po_product pop
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN company c ON (c.company_id = pop.supplier_id)
        LEFT JOIN quote q ON (q.quote_id = pop.quote_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE pop.purchase_order_id = {$purchase_order_id}
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
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, 'Authorized Distributor of:');
                $pdf->SetXY(10,25);
                $pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);
                $creationDate = $fn->getCPDate($row['creation_date'], 'd-m-Y');
                $deliveryDate = $fn->getCPDate($row['delivery_date'], 'd-m-Y');

				$printTaxName = $cpCfg['printTaxName'] ;
				$gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
				$gstvalue = $row['sub_total'] * $gsttaxvalue / 100;
				$totalvalue = $gstvalue + $row['sub_total'];

                /* Company address */
                //Address to be got from settings
                /*
                $pdf->Cell(50, 20, '25 LORONG 39 GEYLANG SINGAPORE 387875');
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->Cell(50, 20, 'TEL: +65 674 74 126 FAX: +65 674 84 322');
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, 'EMAIL: enquiry@novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140, 15);
                $pdf->Cell(50, 20, 'WEBSITE: www.novo-ship-supplies.com.sg');
                $pdf->Ln(5);
                $pdf->SetXY(140,20);
                $pdf->Cell( 50, 20, 'GST REG. NO: 201203469M');
                */
                $pdf->SetXY(140,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(140,5);
                $pdf->SetFont('Courier','',8);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(140,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(140,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(140, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(140,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf5']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "PURCHASE ORDER WITH PRICE", 0, 0, 'C');
                $pdf->Ln(20);


                /* Company Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(100,8,"VENDOR",1,0, 'L', 1);
                $pdf->Cell(45,8,"TEL",1,0, 'L', 1);
                $pdf->Cell(45,8,"FAX",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            $pdf->Cell(100, 8, $row['company_name'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['phone'], 1, 0, 'L', 1);
	            $pdf->Cell(45, 8, $row['fax'], 1, 0, 'L', 1);
                $pdf->Ln(20);

				$currency = $row['currency'];
				$quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

                /* Purchase Details*/
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(70,8,"QUOTE CODE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO DATE",1,0, 'L', 1);
                $pdf->Cell(30,8,"PO CODE",1,0, 'L', 1);
                $pdf->Cell(60,8,"DELIVERY DATE",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
	            //$pdf->Cell(70, 8, $formatedQC[3], 1, 0, 'L', 1);
	            $pdf->Cell(70, 8, $quoteCode, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $creationDate, 1, 0, 'L', 1);
	            $pdf->Cell(30, 8, $row['po_code'], 1, 0, 'L', 1);
	            $pdf->Cell(60, 8, $deliveryDate, 1, 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(190,8,"LOCATION: {$row['delivery_location']}",1,0, 'L', 1);
                //$pdf->Cell(30,8,"TIME: AM(TBC)",1,0, 'L', 1);
                //$pdf->Cell(33,8,"DELIVERY DATE : ",'TBL',0, 'L', 1);
				//$pdf->Cell(27,8, $deliveryDate, 'TBR', 0, 'L', 1);
                $pdf->Ln(10);


				$pdf->Cell(30,15,"(Note : Please mention the exact Item Code for all the products.)",0,0, 'L', 1);
                $pdf->Ln(10);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(17,8,"ITEM NO",1,0, 'C', 1);
                $pdf->Cell(24,8,"ITEM CODE",1,0, 'C', 1);
                $pdf->Cell(81,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(23,8,"UP",1,0, 'C', 1);
                $pdf->Cell(25,8,"AMOUNT(" . $row['currency'] . ")",1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(17, 8, $lineItemNumber, 1, 0, 'C', 1);
            $pdf->Cell(24, 8, $row['item_code'], 1, 0, 'L', 1);
            //$pdf->Cell(91, 8, substr($row['product_title'], 0, 61), 1, 0, 'L', 1);
            $pdf->Cell(81, 8, $row['product_title'], 1, 0, 'L', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(23, 8, $row['price'], 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $row['amount'], 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;
            $sub_total = $row['sub_total'];
        }
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(165, 8, "SUB TOTAL {$currency}", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, $sub_total, 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(165, 8, "ADD: {$printTaxName} {$gsttaxvalue}%", 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($gstvalue, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(165, 8, 'TOTAL', 1, 0, 'R', 1);
            $pdf->Cell(25, 8, number_format($totalvalue, 2), 1, 0, 'R', 1);
			$pdf->Ln(20);

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
    function getPreviousOrderForClient() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $quote_product_id   = $fn->getReqParam('quote_product_id');
        $quoteProductRec    = $fn->getRecordRowByID('quote_product', 'quote_product_id', $quote_product_id);
        $quoteRec           = $fn->getRecordRowByID('quote', 'quote_id', $quoteProductRec['quote_id']);

        $rows = '';
        $product_title ='';

        $sqlClient = "
        SELECT c.company_name
              ,q.quote_code
              ,q.status
              ,q.link_stock
              ,qp.cost_price
              ,DATE_FORMAT(q.quote_date, '%d-%m-%Y') AS quote_date
              ,qp.qty
              ,qp.selling_price
              ,qp.discount_percentage
              ,qp.discount_type
              ,CONCAT_WS('::', p.title, p.model, p.carton_no, p.batch_no, p.unit) as product_title
        FROM quote_product qp
        LEFT JOIN (quote q) ON (qp.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (q.company_id = c.company_id)
        LEFT JOIN (product p) ON (qp.product_id = p.product_id)
        WHERE qp.product_id = {$quoteProductRec['product_id']}
          AND qp.quote_id != {$quoteProductRec['quote_id']}
          AND q.company_id = {$quoteRec['company_id']}
        ORDER BY q.quote_id DESC
        LIMIT 0, 10
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        if ($numRows == 0) {
            $supplierRows =  "<h4>Sorry, no previous product records for this client</h4>" . "<br><br>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $discount_value_for_one_qty  = 0;

                if($row['discount_percentage'] > 0){
                    if($row['discount_type'] == '%'){
                        $discount_value_for_one_qty  =  $row['cost_price'] * ($row['discount_percentage']/100);
                    }
                    else if($row['discount_type']  == 'Value'){
                        $discount_value_for_one_qty  =  $row['discount_percentage'];
                    }
                }

                $bgcolor = '';
                if($row['link_stock'] == 0){
                    $bgcolor ="bgcolor='#FFCCCC'";
                }

                $rows .= "
                <tr $bgcolor>
                    <td>{$row['quote_date']}</td>
                    <td>{$row['quote_code']}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$discount_value_for_one_qty}</td>
                    <td>{$row['cost_price']}</td>
                    <td>{$row['status']}</td>
                </tr>
                ";
                $product_title = $row['product_title'];
            }

            $supplierRows = "
            <h4>Sales History for $product_title </h4>
            <table class='thinlist'>
                <thead>
                    <th>Date</th>
                    <th>Quote Code</th>
                    <th>Client Name</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Cost Price</th>
                    <th>Status</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }

        $text ="
        {$supplierRows}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuoteProductLink($quote_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if($quote_id == ''){
            $quote_id   = $fn->getReqParam('quote_id');
        } else {
            $quote_id = $quote_id;
        }

        $rows = '';
        $SQL = "
        SELECT count(*) AS total
        FROM quote_product
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);
        $rowCount = $db->sql_fetchrow($result);
        $orderRec = $fn->getRecordByCondition('order', "quote_id = '{$quote_id}'");
        $bulkGenerateUrl  = "index.php?module=tradingin_quote&_spAction=generateBulkProduct&id={$quote_id}&showHTML=0";
        $addUrl  = "index.php?module=tradingin_quote&_spAction=addProduct&id={$quote_id}&showHTML=0";

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = '%'
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.discount_percentage  * qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.discount_percentage  * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL = "
        SELECT qp.quote_product_id
              ,p.title AS product_title
              ,p.part_number
              ,c.title As category_title
              ,qp.qty
              ,qp.pieces
              ,p.unit
              ,IF(
              (SELECT qphist.selling_price
                FROM quote_product qphist
                LEFT JOIN quote qt ON ( qt.quote_id = qphist.quote_id )
                WHERE qt.company_id = q.company_id
                AND qphist.product_id = qp.product_id
                AND qphist.product_id > 0
                AND qphist.quote_product_id != qp.quote_product_id
                ORDER BY qphist.quote_id
                LIMIT 0 , 1
                )
                ,CONCAT_WS(''
                ,'<a href=index.php?_topRm=order&module=tradingin_quote&_spAction=previousOrderForClient&showHTML=0&quote_product_id=', qp.quote_product_id, ' class=productViewHistory', '> View</a>', '::'
                ,(SELECT qphist.selling_price
                FROM quote_product qphist
                LEFT JOIN quote qt ON ( qt.quote_id = qphist.quote_id )
                WHERE qt.company_id = q.company_id
                AND qphist.product_id = qp.product_id
                AND qphist.quote_product_id != qp.quote_product_id
                ORDER BY qphist.quote_id DESC
                LIMIT 0 , 1))
                , '')
                as view_history
              ,qp.cost_price
              ,qp.discount_type
              ,qp.discount_percentage as discount_percentage_amount

              ,CONCAT_WS(' ', '-',
              (SELECT CASE WHEN qp.discount_type = '%' then
                  (SELECT round(((qop.cost_price * qop.discount_percentage )/100),2)
                    FROM quote_product qop
                    WHERE qop.quote_product_id = qp.quote_product_id
                      AND qop.discount_type = '%'
                   )
                    WHEN qp.discount_type = 'Value' then
                   (SELECT round(qop.discount_percentage,2)
                    FROM quote_product qop
                    WHERE qop.quote_product_id = qp.quote_product_id
                      AND qop.discount_type = 'Value'
                   )
                    ELSE 0
               END))
               as discount_percentage_total

              ,qp.vat
              ,round((((qp.cost_price -(SELECT CASE WHEN qp.discount_type = '%' then
                  (SELECT round(((qop.cost_price * qop.discount_percentage )/100),2)
                    FROM quote_product qop
                    WHERE qop.quote_product_id = qp.quote_product_id
                      AND qop.discount_type = '%'
                   )
                    WHEN qp.discount_type = 'Value' then
                   (SELECT round(qop.discount_percentage,2)
                    FROM quote_product qop
                    WHERE qop.quote_product_id = qp.quote_product_id
                      AND qop.discount_type = 'Value'
                   )
                    ELSE 0
               END))* qp.vat )/100), 2)
                   as total_vat

              ,round((qp.selling_price) ,2) as sub_total
              ,round((qp.selling_price  * qp.qty) ,2) as total_selling_price

              ,qp.quote_product_id AS qo_po_id
              ,(SELECT SUM(round(qp.cost_price * qp.qty,2))
               FROM quote_product qp WHERE qp.quote_id = {$quote_id})
               AS total_cost_price_sum

               ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)
               )
               as discount_percentage_total_sum

              ,(SELECT SUM(round(
              (qp.selling_price * qp.qty),2))

              FROM quote_product qp WHERE qp.quote_id = {$quote_id}) as total_selling_price_sum
        FROM quote_product qp
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN category c ON (c.category_id = p.category_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        ,(SELECT @row := 0) r
        WHERE qp.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        $total_vat_sum = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr recid='{$row['quote_product_id']}' class='portal-row2 row-tradingin_quote__tradingsg_productLink'>
                <td width='5'>{$count}</td>
                <td class='product-title w200'>
                    <input type='text' value='{$row['product_title']}' name='product_title'>
                </td>
                <td class='part-number w50'>{$row['part_number']}</td>
                <td class='category-title w50'>{$row['category_title']}</td>
                <td class='qty w50 al-right'>
                    <input type='text' value='{$row['qty']}' name='qty'>
                </td>
                <td class='pieces w50 al-right'>
                    <input type='text' value='{$row['pieces']}' name='pieces'>
                </td>
                <td class='unit w50'>{$row['unit']}</td>
                <td class='view-history w50'>
                    {$row['view_history']}
                </td>
                <td class='cost-price w100 al-right'>
                    <input type='text' value='{$row['cost_price']}' name='cost_price'>
                </td>
                <td class='discount-type w50'>
                    <select name='discount_type'>
                        <option value=''>Please Select</option>
                        <option value='%' selected='selected'>%</option>
                        <option value='Value'>Value</option>
                    </select>
                </td>
                <td class='discount-percentage-amount w50 al-right'>
                    <input type='text' value='{$row['discount_percentage_amount']}' name='discount_percentage_amount'>
                </td>
                <td class='discount-percentage-total al-right w50 discountSum'>{$row['discount_percentage_total']}</td>
                <td class='vat al-right w50'>{$row['vat']}</td>
                <td class='total-vat al-right w50'>{$row['total_vat']}</td>
                <td class='sub-total al-right w50'>{$row['sub_total']}</td>
                <td class='total-selling-price al-right w50 totalSp'>{$row['total_selling_price']}</td>
                <td class='qo-po-id'>
                    <input type='checkbox' value='1' name='qo_po_id'>
                </td>
            </tr>
            ";
            $count++;
            $total_selling_price_sum = $row['total_selling_price_sum'];
            $discount_percentage_total_sum = $row['discount_percentage_total_sum'];
            $total_vat_sum += $row['total_vat'];
        }
                /*<td class='portalActBtns'>
                    <div style='float:right'>
                        <a srcroomid='{$quote_id}' link='/admin/index.php?_spAction=deletePortalRecordByID&srcRoom=tradingin_quote&lnkRoom=tradingsg_productLink&id={$row['quote_product_id']}&showHTML=0' class='deletePortalRecord' href='javascript:void(0);'>
                        <img border='0' title='Delete Record' src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                        </a>
                    </div>
                </td>*/

        $urlInvoice = "index.php?_topRm=finance&module=tradingin_order&_action=edit&record_id={$orderRec['order_id']}";
        $invoiceLink = '';
        if ($orderRec['order_id'] != '') {
            $invoiceLink ="
            <div class='button float_right'>
                <a href='{$urlInvoice}'>Go To Order</a>
            </div>
            ";
        }

        $bulkAdd = "
        <div class='button float_right'>
            <a href='{$bulkGenerateUrl}' id='bulkAddProduct' quote_id={$quote_id}>Generate Bulk Items</a>
        </div>
        ";
        $add = "
        <div class='button float_right'>
            <a href='#' id='addProduct' class='ml0' quote_id={$quote_id}>Add</a>
        </div>
        ";

        $text="
        <div class='header' expanded='1'>
            <div class='floatbox'>
                <div class='float_left'>
                    Products Linked - No. of items ({$rowCount['total']})
                </div>
                    {$add}
                    {$bulkAdd}
                    {$invoiceLink}
            </div>
        </div>
        <div class='linkPortalWrapper'>
        <table keyfld='quote_product_id' id='quoteProductLink'>
            <thead>
                <tr>
                    <th width='5'>#</th>
                    <th class='name-of-the-item w200'>Name of the Item</th>
                    <th class='item-ref w50'>Item Ref</th>
                    <th class='category w50'>Category</th>
                    <th class='wtgms w50 al-right'>Wt(gms)</th>
                    <th class='pieces w50'>Pieces</th>
                    <th class='uom w50'>UOM</th>
                    <th class='history w50'>History</th>
                    <th class='cp w100 al-right'>CP</th>
                    <th class='discount-type w50'>Discount Type</th>
                    <th class='discount w50 al-right'>Discount</th>
                    <th class='discount-amt al-right w50 discountSum'>Discount Amt</th>
                    <th class='vat- al-right w50'>VAT %</th>
                    <th class='vat-amt al-right w50'>VAT Amt</th>
                    <th class='sub-total al-right w50'>Final Unit Price</th>
                    <th class='total-selling-price al-right w50 totalSp'>Total Selling Price</th>
                    <th class='delete '>Delete</th>
                </tr>
            </thead>
            <tbody>
                <tr class='summary-row'>
                    <th colspan='11'></th>
                    <th class='al-right w50 discountSum'>{$discount_percentage_total_sum}</th>
                    <th></th>
                    <th class='al-right w50 vatSum'>{$total_vat_sum}</th>
                    <th></th>
                    <th class='al-right w50 totalSp'>{$total_selling_price_sum}</th>
                    <th></th>
                </tr>
                {$rows}
            </tbody>
        </table>
        </div>
        ";

        return $text;
    }
}