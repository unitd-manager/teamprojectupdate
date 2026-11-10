<?
class CP_Admin_Modules_EnterpriseIms_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
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

            $rows .="
		    {$listObj->getListRowHeader($row, $count)}
		    {$listObj->getGoToDetailText($count, $row['invoice_code'])}
		    {$listObj->getListDataCell($row['contact_name'])}
		    {$listObj->getListDataCell($row['parent_name'])}
		    {$listObj->getListDataCell($row['title'])}
		    {$listObj->getListDateCell($row['invoice_date'])}
		    {$listObj->getListDataCell($row['status'])}
		    {$listObj->getListDataCell($row['invoice_amount'] ,'right')}
		    {$listObj->getListDataCell($row['receipt_code'])}
		    {$listObj->getListDataCell($row['order_id'], 'center')}
		    {$listObj->getListRowEnd($row['invoice_id'])}
			";

        	$count++;
		}

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice Code', 'invoice_code')}
        {$listObj->getListHeaderCell('Contact Name', 'co.contact_name')}
        {$listObj->getListHeaderCell('Parent Name', 'parent_name')}
        {$listObj->getListHeaderCell('Company Name', 'c.title')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('Amount', 'i.invoice_amount', 'headerRight')}
        {$listObj->getListHeaderCell('Receipt Code', 'receipt_code')}
        {$listObj->getListHeaderCell('Order Id', 'i.order_id', 'headerCenter')}
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];
        $stillToBill   = '';
        $base_value = '';
        $ref_value  = '';

        $sqlStatus = $fn->getValueListSQL('invoiceStatus');
        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);
        $expInvNo  = array('isEditable' => $cpCfg['m.enterpriseIms.invoice.codeEditable']);
        $expNum    = array('autoFormat' => 1);

        $invDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['invoice_date'];
        $vlUrl    = "index.php?module=core_valuelist&_spAction=showValuesInModal&showHTML=0&key_text=";

        $expNotes = array();

        $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD MMM YYYY');

        $fieldset1 = "
        {$formObj->getTBRow('Client Contact', 'contact_id', $row['contact_name'], $expNoEdit)}
        {$formObj->getTBRow('Client Company', 'company_id', $row['title'], $expNoEdit)}
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expInvNo)}
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $invoice_date)}
        {$formObj->getTBRow('Invoice Amount', 'invoice_amount', $row['invoice_amount'], $expNum)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
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

        $sqlComp = "
        SELECT DISTINCT a.company_id
              ,a.title
        FROM company a
        JOIN (project b) ON (a.company_id = b.company_id)
        JOIN (invoice c) ON (b.project_id = c.project_id)
        ORDER BY title
        ";

        $expVl     = array('sqlType' => 'OneField');
        $sqlStatus = $fn->getValueListSQL('invoiceStatus');
        $sqlType   = $fn->getValueListSQL('invoiceType');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
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
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'invoice_id');
        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'enterpriseIms_invoice', 'attachment', $row)}
        ";
        
        return $text;
        
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
        $formObj = Zend_Registry::get('formObj');

        $company_id   = $fn->getReqParam('company_id');
        $title = $fn->getReqParam('title');
        $status       = $fn->getReqParam('status');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.title 
        FROM company a
        ORDER BY title
        ";

        $SQLStatus = $fn->getValueListSQL('invoiceStatus');


        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $date1 = $fn->getReqParam('date1');
        $date2 = $fn->getReqParam('date2');
        $yearEnd = date('Y') + 10;

        $text = "
        <td class='dateRange'>
            Invoice Date:
            <input type='text' allowEdit='1' name='date1' class='fld_date' 
                   id='fld_date1' value='{$date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='date2' class='fld_date' 
                   id='fld_date2' value='{$date2}' yearEnd='{$yearEnd}' />
        </td>
        <td>
            <select name='company_id'>
                <option value=''>Company Name</option>
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
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";
        
        return $text;
    }
}