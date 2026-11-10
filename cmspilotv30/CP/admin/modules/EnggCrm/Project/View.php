<?
class CP_Admin_Modules_EnggCrm_Project_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function Project() {
    }

    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $currency = '';
            if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
                $currency = $row['currency'];
            }

            $stage = '';
            if ($cpCfg['m.enggCrm.project.showStage'] == 1){
                $stage = $listObj->getListDataCell($row['stage']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['project_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDateCell($row['start_date'], 'left', '', 80)}
            {$listObj->getListDateCell($row['estimated_finish_date'], 'left', '', 110)}
            {$listObj->getListDataCell($row['status'])}
            {$stage}
            {$listObj->getListDataCell($editText)}
            {$listObj->getListRowEnd($row['project_id'])}
            ";

            $rowCounter++;
        }

        $stage = '';
        if ($cpCfg['m.enggCrm.project.showStage'] == 1){
            $stage = $listObj->getListHeaderCell('Stage', 'p.statge');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.project_code', 'w50')}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Category', 'p.category')}
        {$listObj->getListHeaderCell('Start Date', 'p.start_date')}
        {$listObj->getListHeaderCell('Est. Finish Date', 'p.estimated_finish_date')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
        {$stage}
        {$listObj->getListHeaderCell('Edit')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$this->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');

        $searchVar->sqlSearchVar = array();

        $fld_suffix = '';
        if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
            $fld_suffix = '_base';
        }

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';
        $modProject = getCPModuleObj('enggCrm_project');
        $SQLSum  = $modProject->model->getProjectValueSumSQL('project_value');

        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $SQLSum .= "
        AND LOWER(p.status) != 'lost'
        AND LOWER(p.status) != 'cancelled'
        ";
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total1 = $row[0];

        $searchVar->sqlSearchVar = array();
        $modProject = getCPModuleObj('enggCrm_project');
        $SQLSum  = $modProject->model->getProjectValueSumSQL('still_to_bill');

        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $SQLSum .= "
        AND LOWER(p.status) != 'lost'
        AND LOWER(p.status) != 'cancelled'
        ";

        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total2 = $row[0];

        $text = "
            </tbody>
            <tfoot>
                <tr class='header' >
                    <td colspan='13'></td>
                    <!--<td class='txtRight'>{$total1}</td>
                    <td class='txtRight'>{$total2}</td>
                    <td colspan='8'></td>-->
                </tr>
                <input type='hidden' name='boxChecked' value='0' />
                <input type='hidden' name='task' value='' />
            </form>
            </tfoot>
        </table>
        ";

        return $text;
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
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Client'
        ORDER BY company_name
        ";

        $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
        $newCompUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Company'
        w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.opportunity.afterNewCompany'>New</a>";
        $expComp  = array(
             'notesRight'  => $newCompUrl
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => ''
            ,'autoSgstCallBack' => 'cpm.enggCrm.opportunity.loadContactsByCompany'
        );

        $sqlContact = '';

        $newContactUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0';
        $newContactUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Contact'
        w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.opportunity.afterNewContact'>New</a>";

        $expCont  = array(
             'notesRight'  => $newContactUrl
        );

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, '', $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact, '', $expCont)}
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

        $totalQuotes  = 0;
        $quoteRef     = '';
        $invoiceRef   = '';
        $paymentTerms = '';

        $showSensitiveDetails = $fn->getSessionParam('showSensitiveDetails');

        if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1) {
            if ($row['opportunity_id'] > 0) {
                $totalQuotes = $fn->getRecordCount('quote', "opportunity_id = {$row['opportunity_id']}");
            }
        }

        $expCode = array('isEditable' => 0);

        if ($row['opportunity_id'] > 0) {
            $oppLink   = "index.php?_topRm=project&module=enggCrm_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail";
            $linkToOpp = "<a href='{$oppLink}'><u>{$row['opportunity_code']}</u></a>";
            $quoteRef  = $formObj->getTBRow('Opportunity Ref#', 'quote_ref', $linkToOpp, $expCode);
        } else {
            $quoteRef = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        if ($cpCfg['m.enggCrm.project.showInvoiceRef'] == 1) {
            $invoiceRef = "
            {$formObj->getTBRow('Deposit Inv Ref#', 'deposit_inv_ref', $row['deposit_inv_ref'])}
            {$formObj->getTBRow('Invoice Ref#', 'invoice', $row['invoice'])}
            ";
        }

        if ($cpCfg['m.enggCrm.project.showPaymentTerms'] == 1 && $cpCfg['m.enggCrm.hasQuotingModule'] == 0) {
            $paymentTerms = $formObj->getTBRow('Payment Terms', 'payment_terms', $row['payment_terms']);
        }

        //--------------------------------------------------------------------------//
        $sqlComp = $fn->getDDSql('enggCrm_company', array('condn' => "category = 'client'"));
        $append = ($row['company_id'] > 0) ? "AND company_id = {$row['company_id']}" : '';
        $sqlCont = $fn->getDDSql('enggCrm_contact', array('condn' => "CONCAT_WS('', first_name, last_name) != '' {$append}"));
        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        //--------------------------------------------------------------------------//
        $expCode = array('isEditable' => $cpCfg['m.enggCrm.project.codeEditable']);
        $expVl   = array('sqlType' => 'OneField');

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlPerc   = $fn->getValueListSQL('percentCompleted');
        $sqlStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat     = $fn->getValueListSQL('projectCategory');

        $contact  = "<a href='index.php?_topRm=project&module=enggCrm_contact&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
        $company  = "<a href='index.php?_topRm=project&module=enggCrm_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

        $compLink = '';
        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('enggCrm_project', 'enggCrm_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $company);

        $contLink = '';
        if ($formObj->mode == 'edit'){
            $contLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('enggCrm_project', 'enggCrm_contactLink', 'fld_contact_id')}'>Choose</a>";
        }
        $expCont  = array('notesRight' => $contLink, 'detailValue' => $contact);

        $expPM = array('detailValue' => $row['project_manager_name']);


        $stage = '';
        if ($cpCfg['m.enggCrm.project.showStage'] == 1){
            $sqlStage = $fn->getValueListSQL('projectStage');
            $stage = "
            {$formObj->getDDRowBySQL('Stage', 'stage', $sqlStage, $row['stage'], $expVl)}
            ";
        }

        //--------------------------------------------------------------------------//
        $fieldset1 = "
        {$formObj->getTBRow('Code', 'project_code', $row['project_code'], $expCode)}
        {$formObj->getTBRow('Project Name', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category *', 'category', $sqlCat, $row['category'], $expVl)}
        {$quoteRef}
        {$invoiceRef}
        {$paymentTerms}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$stage}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Client type', 'client_type', $sqlType, $row['client_type'], $expVl)}
        {$formObj->getDDRowBySQL('Difficulty', 'difficulty', $sqlDiff, $row['difficulty'], $expVl)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getDateRow('Start Date *', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('Estimated Finish Date *', 'estimated_finish_date', $row['estimated_finish_date'])}
        {$formObj->getDateRow('Actual Finish Date', 'actual_finish_date', $row['actual_finish_date'])}
        {$formObj->getDDRowBySQL('Percentage Completed', 'per_completed', $sqlPerc, $row['per_completed'], $expVl)}
        ";

        $fieldset3 = "
        <div id='projectValues'>
            {$this->getProjectValuesTable($row)}
        </div>
        ";

        $fieldset4 = "
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Project Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row) {
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getProjectValuesTable($row = "") {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $searchVar = Zend_Registry::get('searchVar');

        $totalQuotes = 0;
        $expNoEdit = array('isEditable' => 0, 'autoFormat' => 1);
        $expNum    = array('autoFormat' => 1);

        if ($row == "") {
            $SQL = $sqlMaster->getSQL('enggCrm_project');
            $SQL .= $searchVar->getSearchVar('enggCrm_project');
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
        }

        if ($totalQuotes > 0) {
            $project_value = $formObj->getTBRow('Project Value$', 'project_value', $row['project_value'], $expNoEdit);
        } else {
            $project_value = $formObj->getTBRow('Project Value$', 'project_value', $row['project_value'], $expNum);
        }

        if ($fn->getSessionParam('showSensitiveDetails') == 1){
            $project_commission = $formObj->getTBRow($cpCfg['m.enggCrm.project.commissionLabel'], 'project_commission', $row['project_commission'], $expNum);
        }

        $text = "
        {$project_value}
        {$formObj->getTBRow('Still to Bill$', 'still_to_bill', number_format($row['still_to_bill']), $expNoEdit)}
        ";
        return $text;
    }

    /**
     *
     */
    function getSearch($result) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $row = Zend_Registry::get('row');

        $sqlComp    = $fn->getDDSql('enggCrm_company');
        $sqlType    = $fn->getValueListSQL('clientType');
        $sqlCat     = $fn->getValueListSQL('projectCategory');
        $sqlStatus  = $fn->getValueListSQL('projectStatus');

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));
        $expVl   = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
        {$formObj->getTBRow('Project Name', 'title')}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComp)}
        {$formObj->getDDRowBySQL('Client type', 'client_type', $sqlType, $row['client_type'], $expVl)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff)}
        {$formObj->getDDRowBySQL('Project Category', 'category', $sqlCat, '', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
        {$formObj->getDateRangeRow('Start Date (range)', 'start_date')}
        {$formObj->getDateRangeRow('Est. Finish Date (range)', 'end_date')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        {$formObj->getTARow('Description', 'description')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Project Details', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $pager = Zend_Registry::get('pager');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $db = Zend_Registry::get('db');

        $text = "";

        $rightLinkInv = "<a id='raiseInvoice' class='actionButtons' href=\"javascript:Invoice.raiseInvoice('project')\">Raise Invoice</a>";

        $costing = '';
        if ($cpCfg['m.enggCrm.project.showCostingTable'] == 1){
            $exportUrl = "index.php?_topRm=project&module=enggCrm_costing&showHTML=0&_spAction=exportCosting&project_id={$row['project_id']}";
            $rightLink = "<a class='actionButtons' href='{$exportUrl}'>Export to Excel</a>&nbsp;";
            $costing = $displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_costingLink', 'Costing Table', $row, '', $rightLink);
        }

        $record_id = $fn->getIssetParam($row, 'project_id');

        $gotoProjectBtn = '';
        $generateFinanceRecordLbl = "Generate Finance Record";
        $orderRows = $fn->getRecordCount('order', "project_id = {$row['project_id']}");
        if ($orderRows > 0) {
            $orderRec = $fn->getRecordRowByID('order', 'project_id', $row['project_id']);
            $urlOrderRecord = "index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$orderRec['order_id']}";
            $urlUpdateOrderRecord = "index.php?_topRm=finance&module=enggCrm_order&_action=updateProjectDetailsInOrder&order_id={$orderRec['order_id']}";

            $gotoProjectBtn = "
            <div class='button mb5'>
                <a href='{$urlOrderRecord}' title='Order Record' target='_blank'>Goto Finance</a>
            </div>

            <div class='button mb10'>
                <a href='{$urlUpdateOrderRecord}'>Update Finance</a>
            </div>
            ";

            $generateFinanceRecordLbl = "Update Finance Record";
        }

        $addQuoteBtn = '';
        $quoteRows = $fn->getRecordCount('quote', "project_id = {$row['project_id']}");
        if ($quoteRows == 0) {
            $addQuoteBtn = "
            <div class='button mb10'>
                <a href='#' id='addQuoteProject' project_id='{$row['project_id']}'>Add Quote</a>
            </div>
            ";
        }

        $quoteConfirmCount = $fn->getRecordCount('quote', "project_id = {$row['project_id']} AND quote_status = 'Confirmed'");

        if($row['category'] == 'Manpower Supply'){
            $generateOrderRecordClass = 'generateManpowerOrderRecords';
        }else{
            $generateOrderRecordClass = 'generateOrderRecords';
        }

        $orderBtn = '';
        if ($quoteConfirmCount){
            $orderBtn = "
            <div class='button mb5 mr10'>
                <a href='#' class='{$generateOrderRecordClass}' quote_id='{$row['quote_id']}' project_id='{$row['project_id']}'>{$generateFinanceRecordLbl}</a>
            </div>
            ";
        }

        $text = "
        <div class='float_box'>
            {$addQuoteBtn}
            {$orderBtn}
            {$gotoProjectBtn}
        </div>

        {$this->getAddQuoteFormListView($row['opportunity_id'], $row['project_id'])}
        {$this->getProjectMaterialPortal($row['project_id'])}
        {$this->getPurchaseOrderPortal($row['project_id'])}
        {$media->getRightPanelMediaDisplay('Attachment', 'enggCrm_project', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'core_staffLink', $cpCfg['m.enggCrm.staffFieldLabel'], $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_employeeLink', 'Add Employee', $row)}
        {$this->getEmploymentTimeSheetView($row['project_id'])}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_scheduleLink', 'Project Schedule', $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_invoiceLink', 'Invoices', $row, '', $rightLinkInv)}
        {$costing}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_thirdPartyCostLink', 'Additional Third Party Costs', $row)}
        {$comment->getView(array(
             'roomName' => 'enggCrm_project'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditFromList() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('project', 'project_id', $id);

        $sqlPercent = $fn->getValueListSQL('percentCompleted');
        $sqlStatus  = $fn->getValueListSQL('projectStatus');

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Estimated Finish Date', 'estimated_finish_date', $row['estimated_finish_date'])}
                {$formObj->getDDRowBySQL('Percentage Completed', 'per_completed', $sqlPercent, $row['per_completed'], $exp)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
            </fieldset>
            <input type='hidden' name='project_id' value='{$id}' />
        </form>
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

        $searchQueryString = $pager->removeQueryString(array('_spAction'));
        $module = $fn->getReqParam('module');

        if ($module == "") {
            $searchQueryString .= '&module=' . $tv['module'];
        }

        if ($tv['action'] == 'detail') {
            $tableBody = "
            <div class='reportsBlock'>
                <a href='{$searchQueryString}&_spAction=printReport&reportName=scheduleGanttChart'>Project Schedules</a>
            </div>
            <div class='reportsBlock'>
                <a href='{$searchQueryString}&_spAction=printReport&reportName=barchart3rdParty'>Bar Chart - 3rd Party </a>
            </div>
            <div class='reportsBlock'>
                <a href='{$searchQueryString}&_spAction=printReport&reportName=barchartInHouse'>Bar Chart - In House </a>
            </div>
            <div class='reportsBlock'>
                <a href='{$searchQueryString}&_spAction=printReport&reportName=icon'>Fuel Gauge Chart</a>
            </div>
            ";
        } else {

            $qstr = $fn->getQueryStringForJasper();
            $printReportUrl = "index.php?_spAction=printReport&showHTML=0&{$qstr}&roomName={$tv['module']}&report=";

            $taskReport = '';
            if ($cpCfg['m.enggCrm.project.showTaskSummaryReport'] == 1){
                $printTaskSummaryUrl = "{$searchQueryString}&_spAction=taskSummaryByProject&showHTML=0&hasDB=1";
                $taskReport = "
                <li><a href='{$printTaskSummaryUrl}'>Task Summary By Projects</a></li>
                ";
            }

            $tableBody = "
            <ul class='printOptions'>
                <li><a href='{$printReportUrl}projectSummaryList'>Projects Summary List</a></li>
                {$taskReport}
                <!--
                <li><a href='{$printReportUrl}gantt'>Projects Summary Chart</a></li>
                <li><a href='{$printReportUrl}barChartSales'>Sales by Month</a></li>
                <li><a href='{$printReportUrl}gantt'>WIP Schedule</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Category</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Company Size</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Industry</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Source</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Client Type</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Difficulty Level</a></li>
                <li><a href='{$printReportUrl}pie'>Sales Breakdown by Project Manager</a></li>
                -->
            </ul>
            ";
        }

        $text = "
        <table width='100%' height='100%' bgcolor='#ffffff'>
            <tr>
                <td style='height:25px;'><h2>Reports:</h2></td>
            </tr>
            <tr>
                <td valign='top'>{$tableBody}</td>
            </tr>
        </table>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $company_id         = $fn->getReqParam('company_id');
        $project_month      = $fn->getReqParam('project_month');
        $status             = $fn->getReqParam('status');
        $category           = $fn->getReqParam('category');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');

        $appendSqlCompany = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlCompany = " AND a.site_id = {$cpSiteIdSession}";
        }
        $sqlCompany = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN project b ON (a.company_id = b.company_id)
        WHERE a.category = 'Client'
              {$appendSqlCompany}
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat    = $fn->getValueListSQL('projectCategory');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
           ,"Overrun"
        );


        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(start_date, '%b %Y') AS monthYear
        FROM project
        WHERE DATE_FORMAT( start_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
         ";

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCat, $category)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $tv['status'])}
            </select>
        </td>

        <td>
            <select name='yearMonthStart'>
                <option value=''>Start Month</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonthStart)}
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
    function getAddQuoteFormListView($opportunity_id, $project_id, $category = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT q.*
        FROM `quote` q
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        WHERE p.project_id = {$project_id}
        ORDER BY q.quote_code DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
                $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

                $sqlQuoteItems ="
                SELECT SUM(quantity * amount)As quote_amount
                      ,SUM(quantity) AS quote_qty
                       ,quote_items_id
                FROM quote_items qi
                WHERE qi.quote_id = {$row['quote_id']}
                ";

                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $rowQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);

                $addLineItemView = '';
                if($rowQuoteItems['quote_amount'] > 0 || $rowQuoteItems['quote_qty']) {
                    $addLineItemView ="
                    <div class='float_right'>
                        <a href='#' class='quoteLayoutShow'>Hide</a>
                    </div>
                    ";
                }

                $quoteActions = '';
                $editForQuote = "index.php?_topRm=project&module=enggCrm_project&_spAction=editForQuote&project_id={$project_id}&quote_id={$row['quote_id']}&showHTML=0";

                if($category == 'Manpower Supply'){
                    $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printLinkForManpowerPdf&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
                }
                else{
                    $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printLinkForPdf&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
                }

                $formActionGroupForQuoteLineItem = "index.php?_topRm=project&module=enggCrm_project&_spAction=addLineItemForQuoteForm&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&project_id={$project_id}&showHTML=0";

                $quoteActions ="
                <div class='float_box clearfix'>
                    <div class='float_left'>
                        <a class='editForQuote' href='{$editForQuote}'>Edit</a>
                    </div>
                    <!--<div class='float_left'>
                        <a href='#' class='deleteAddQuote' quote_id='{$row['quote_id']}'>Delete</a>
                    </div>-->
                    <div class='printLink'>
                        <a href='{$urlPrintLinkPdf}' target='_blank'>Print Pdf</a>
                    </div>
                    <!--<div class='float_right'>
                        <a href='{$formActionGroupForQuoteLineItem}' class='addLineItem'>Add Line Item</a>
                    </div>-->
                </div>

                <div class='float_box clearfix'>
                    <!--<div class='duplicateQuote'>
                        <a href='#' class='duplicateQuote'  quote_id='{$row['quote_id']}' project_id='{$row['project_id']}' quote_items_id='{$rowQuoteItems['quote_items_id']}'>Duplicate</a>
                    </div>-->
                    <div class=''>
                        <a href='#' project_id={$row['project_id']} quote_id = {$row['quote_id']} class='addMultipleLineItem'>Add Line Item</a>
                    </div>
                </div>
                ";

                $updation_details = '';
                if ($row['modified_by']) {
                    $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
                } else {
                    $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
                }

                $confirmedQuoteStatus = '';
                if($row['quote_status'] == 'Confirmed') {
                    $confirmedQuoteStatus = 'confirmedQuote';
                }

                $quote_amount = number_format($rowQuoteItems['quote_amount'], 2);

                if($category == 'Manpower Supply'){
                    $quote_amount = number_format($rowQuoteItems['quote_qty'], 2);
                }

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>{$row['quote_code']}</td>
                        <td>{$quote_date}</td>
                        <td>{$row['quote_status']}</td>
                        <td>{$quote_amount}</td>
                        <td>{$updation_details}</td>
                        <td class='viewRowWidth'>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForQuoteListView($opportunity_id,$project_id,$row['quote_id'], $category)}
                </tbody>
                ";

            }

            $text = '';

            if ($numRows > 0)  {

                if($category == 'Manpower Supply'){
                    $ChangeHead = "<th class='txtRight'>Hourly Rate</th>";
                }else{
                    $ChangeHead = "<th class='txtRight'>Amount</th>";
                }

            $text = "
            <div id='quotesPortal' class='linkPortalWrapper'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='7' align='left'>Quotations</th>
                        </tr>
                        <tr>
                            <th>Quote Code</th>
                            <th>Quote Date</th>
                            <th>Quote Status</th>
                            {$ChangeHead}
                            <th>Updated By</th>
                            <th></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";

            return $text;

        }
    }

    /**
     * Quote Portal Edit
     */
    function getEditForQuote() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $quote_id         = $fn->getReqParam('quote_id');
        $project_id       = $fn->getReqParam('project_id');
        $quote_status     = $fn->getReqParam('quote_status');
        $opportunity_id   = $fn->getReqParam('opportunity_id');

        $rowQuote         = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $quoteItemsRec    = $fn->getRecordRowByID('quote_items', 'quote_id', $rowQuote['quote_id']);

        $formActionEditForQuote = "index.php?module=enggCrm_project&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&quote_id={$rowQuote['quote_id']}&project_id={$project_id}&showHTML=0";

        $expNoEdit  = array('isEditable' => 0);

        $status = "";
        if ($rowQuote['opportunity_id'] == ''){
            $spArrayQuoteStatus = array('New' ,'Cancelled' ,'Confirmed','Hold');
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'])}";
        }

        $text = "
        <form id='editForQuote' class='yform columnar' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title',$rowQuote['title'])}
                {$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'])}
                {$status}
                {$formObj->getTextAreaRow('Terms & Condition', 'condition',$rowQuote['condition'])}
                <input type='hidden' name='project_id' value='{$project_id}' />
                <input type='hidden' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintLinkForPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

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

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.remarks
              ,p.project_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_country
              ,c.billing_address_po_code
              ,c.company_id
              ,co.salutation
              ,co.first_name
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }
        $tbl2 ='<table border="0" width="100%" cellpadding="">
                    <tr>
                        <td width="6%" style="font-size:12px; font-weight:bold;">TO: </td>
                        <td width="54%"></td>
                        <td width="26%" align="right" style="font-size:12px; font-weight:bold;"><b>QUOTATION NO : </b></td>
                        <td width="14%" align="right" style="font-size:12px; font-weight:bold;">'.$company['quote_code'].'</td>
                    </tr>
                    <tr>
                        <td width="54%" style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                        <td width="6%"></td>
                        <td width="26%" align="right" style="font-size:12px; font-weight:bold;">DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </td>
                        <td width="14%" align="right" style="font-size:12px; font-weight:bold;">'.$quote_date.'</td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_flat']).'</td>
                        <td colspan="3"></td>
                    </tr>
                    ' .  $rowStreet .'
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td width="54%" style="font-size:12px; font-weight:bold;">ATTN:&nbsp;' .strtoupper($company['salutation']). ' '.strtoupper($company['first_name']).'</td>
                        <td colspan="3"></td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="line-height:10px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" style="font-size:12px; line-height:20px;">Dear Sir<br/>
                            Thank you for your consideration and opportunity to render our services, we are pleased to quote you our best price for the site
                        '.$company['title'].' as follows:</td>
                    </tr>
                </table>';


        $tbl3 ='<table border="1" cellpadding="2"  width="100%">
                    <thead>
                        <tr bgcolor="#DDE4FF">
                            <th height="20" style="font-size:12px; font-weight:bold;">DETAILS OF QUOTATION</th>
                        </tr>
                        <tr>
                            <th width="5%" align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                            <th width="35%" align="center" style="font-size:12px; font-weight:bold;">DESCRIPTION</th>
                            <th width="8%" align="center" style="font-size:12px; font-weight:bold;">SIZE</th>
                            <th width="10%" align="center" style="font-size:12px; font-weight:bold;">QTY</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">UNIT PRICE (S$)</th>
                            <th width="14%" align="center" style="font-size:12px; font-weight:bold;">TOTAL AMT (S$)</th>
                            <th width="15%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
            $tbl3 = $tbl3.'<tr>
                                <td width="5%"></td>
                                <td width="35%" style="font-size:12px; font-weight:bold;"><u>'.strtoupper($row['quote_item_title']).'</u></td>
                                <td width="8%"></td>
                                <td width="10%"></td>
                                <td width="13%"></td>
                                <td width="14%"></td>
                                <td width="15%"></td>
                            </tr>
                    ';
            }

            $subtotal_amount = round($row['quantity'] * $row['amount'], 2);
            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            $tbl3 = $tbl3.'<tr>
                                <td width="5%" style="font-size:12px;">'.$count.'</td>
                                <td width="35%" style="font-size:12px;">'.nl2br($row['description']).'</td>
                                <td width="8%" align="center" style="font-size:12px;">'.$row['unit'].'</td>
                                <td width="10%" align="center" style="font-size:12px;">'.$row['quantity'].'</td>
                                <td width="13%" align="center" style="font-size:12px;">'.$row['amount'].'</td>
                                <td width="14%" align="right" style="font-size:12px;">'.$subtotal_amount_formatted.'</td>
                                <td width="15%" style="font-size:12px;">'.$row['remarks'].'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
            $gstvalue = $subtotalValue * $gsttaxvalue / 100;
            /* Taking two decimal values for gst amount */
            $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gstvalue);
                $fraction = substr($fraction, 0, 2);
                $gstvalue = $integer . "." . $fraction;
            }

            $totalvalue = $gstvalue + $subtotalValue;
            $count++;
        }

        $amount_in_words = $fn->getConvertNumber($totalvalue);
        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="5" style="font-size:12px; font-weight:bold;">SUB TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                          <td></td>
                      </tr>
                      <tr>
                          <td colspan="5" align="right" style="font-size:12px; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                          <td></td>
                       </tr>
                       <tr>
                          <td colspan="5" align="right" style="font-size:12px; font-weight:bold;">TOTAL AMOUNT</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                          <td></td>
                       </tr>
                    </table>
                    <table border="0" width="100%">
                        <tr>
                            <td style="line-height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="font-size:12px;">TOTAL = <b>'.strtoupper($amount_in_words).'</b></td>
                        </tr>
                    </table>';

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td align="left" width="100%" style="font-size:12px; text-decoration:underline; font-weight:bold">TERMS & CONDITIONS:</td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td border="0" align="left" style="font-size:12px;" width="70%">Yours faithfully</td>
                <td border="0" align="left" style="font-size:12px;" width="30%">Confirmed & accepted by</td>
            </tr>
            <tr>
                <td style="font-size:12px; font-weight:bold;">'.$cpCfg['cp.companyName'].'</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%">
            <tr>
                <td width="40%" style="border-bottom:2px solid black"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:2px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:12px; font-weight:bold;">'.$cpCfg['cp.companyDirectorName'].'</td>
                <td></td>
                <td style="font-size:12px; font-weight:bold;">Name / Company Stamp</td>
            </tr>
            <tr>
                <td width="40%" style="font-size:12px;">'.$cpCfg['cp.companyRepresentativePosition'].'</td>
                <td></td>
                <td style="font-size:12px;">Date:</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     * Add Line Item Edit
     */
    function getEditLineItem() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $quote_items_id  = $fn->getReqParam('quote_items_id');
        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $project_id      = $fn->getReqParam('project_id');

        $rowQuoteItem = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);

        $exp = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?module=enggCrm_project&_spAction=editLineItemSubmit&lnkRoom={$tv['lnkRoom']}&quote_items_id={$quote_items_id}&opportunity_id={$opportunity_id}&showHTML=0";

        $text = "
        <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title',$rowQuoteItem['title'] )}
                {$formObj->getTARow('Description', 'description',$rowQuoteItem['description'] )}
                {$formObj->getTBRow('UoM', 'unit',$rowQuoteItem['unit'])}
                {$formObj->getTBRow('Quantity', 'quantity',$rowQuoteItem['quantity'])}
                {$formObj->getTBRow('Amount', 'amount',$rowQuoteItem['amount'])}
                {$formObj->getTARow('Remarks', 'remarks',$rowQuoteItem['remarks'] )}
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                <input type='hidden' name='project_id' value='{$project_id}' />
            </fieldset>
        </form>
        ";
        return $text;
    }

    /**
     * Employee Add Line Item Edit
     */
    function getEditEmploymentViewItem() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $employee_id             = $fn->getReqParam('employee_id');
        $project_id              = $fn->getReqParam('project_id');
        $employee_timesheet_id   = $fn->getReqParam('employee_timesheet_id');

        $rowEmployeeItem = $fn->getRecordByCondition('employee_timesheet', "employee_timesheet_id = '{$employee_timesheet_id}'");

        $exp = array('sqlType' => 'OneField');

        $formActionEditEmployeeItem = "index.php?module=enggCrm_project&_spAction=editEmployeeItemSubmit&lnkRoom={$tv['lnkRoom']}&employee_id={$employee_id}&employee_timesheet_id={$employee_timesheet_id}&showHTML=0";

        $text = "
        <form id='editForEmployeeItemView' class='yform columnar' method='post' action='{$formActionEditEmployeeItem}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'date',$rowEmployeeItem['date'])}
                {$formObj->getTBRow('Hours', 'employee_hours',$rowEmployeeItem['employee_hours'])}
                {$formObj->getTARow('Description', 'description',$rowEmployeeItem['description'] )}
                <input type='hidden' name='employee_id' value='{$employee_id}' />
                <input type='hidden' name='employee_timesheet_id' value='{$employee_timesheet_id}' />
                <input type='hidden' name='project_id' value='{$project_id}' />
            </fieldset>
        </form>
        ";
        return $text;
    }
    /**
     *
     */
    function getAddLineItemForQuoteListView($opportunity_id, $project_id, $quote_id, $category) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        //$appendSql = '';
        /*if($opportunity_id != ''){
            $appendSql = "WHERE  qt.opportunity_id = {$opportunity_id} AND qt.quote_id = {$quote_id}";
        }else {
            $appendSql = "
            LEFT JOIN quote q ON (qt.quote_id = q.quote_id)
            WHERE q.project_id = {$project_id}
            AND qt.quote_id = {$quote_id}
            ";
        }*/

        $SQL = "
        SELECT qt.*
        FROM `quote_items` qt
        LEFT JOIN quote q ON (qt.quote_id = q.quote_id)
        WHERE q.project_id = {$project_id}
        AND qt.quote_id = {$quote_id}
        ORDER BY qt.quote_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';

            $editForLineItem = "index.php?_topRm=project&module=enggCrm_project&_spAction=editLineItem&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&quote_items_id={$row['quote_items_id']}&showHTML=0";

            $editText = "
            <div class='float_left'>
                <a class='editForLineItem' href='{$editForLineItem}'>Edit</a>
            </div>
            ";

            $deleteLineItem = "
            <div class='float_right'>
                <a href='#' class='deleteLineItem' quote_items_id='{$row['quote_items_id']}' quote_id= '{$row['quote_id']}'>Delete</a></td>
            </div>
            ";

            $addclass = '';
            if ($row['project_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            $total_amount = number_format($row['quantity'] * $row['amount'], 2);

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            if($category == 'Manpower Supply'){
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                    <td class='descriptionWrap'>{$row['description']}</td>
                    <td class=''>{$row['amount']}</td>
                    <td class='amountRow'>{$row['remarks']}</td>
                    <td colspan='2'>{$updation_details}</td>
                    <td colspan='2'>{$editText} {$deleteLineItem}</td>
                </tr>
                ";
            }

            else{
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                    <!--<td class='quoteRowBorder'></td>
                    <td class='quoteRowBordersecond'></td>-->
                    <td class='descriptionWrap'>{$row['description']}</td>
                    <td>{$row['quantity']}</td>
                    <td class='amountRow'>{$row['amount']}</td>
                    <td class='amountRow'>{$total_amount}</td>
                    <td>{$updation_details}</td>
                    <td colspan='2'>{$editText} {$deleteLineItem}</td>
                </tr>
                ";
            }
        }

        $text = '';

        if ($numRows > 0)  {

            if($category == 'Manpower Supply'){
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th class='quoteRowBackground'>Description</th>
                    <th class='quoteRowBackground'>Hourly Rate</th>
                    <th class='quoteRowBackground txtRight'>Remarks</th>
                    <th colspan='2' class='quoteRowBackground'>Updated By</th>
                    <th colspan='2' class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
                ";
            }

            else{
                $text = "
                    <tr class = 'quoteLayoutHide showAddLineRow'>
                        <!--<th class='quoteRowBorder'></th>
                        <th class='quoteRowBordersecond'></th>-->
                        <th class='quoteRowBackground'>Description</th>
                        <th class='quoteRowBackground'>Quantity</th>
                        <th class='quoteRowBackground txtRight'>($) Unit Price</th>
                        <th class='quoteRowBackground txtRight'>Amount</th>
                        <th class='quoteRowBackground'>Updated By</th>
                        <th colspan='2' class='quoteRowBackground'>Action</th>
                    </tr>
                    {$rows}
                ";
            }

            return $text;
        }
    }

    /**
     *
     */
     function getAddLineItemForQuoteForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $project_id      = $fn->getReqParam('project_id');
        $quote_id        = $fn->getReqParam('quote_id');
        $quote_items_id  = $fn->getReqParam('quote_items_id');

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addLineItemForQuoteFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addLineItem' method='post' action='{$formAction}'>
            {$formObj->getTARow('Description', 'description')}
            {$formObj->getTBRow('Quantity', 'quantity')}
            {$formObj->getTBRow('Amount', 'amount')}
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='quote_id' value='{$quote_id}' />
            <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
        </form>
        ";
        return $text;

    }


    /**
     *
     */
    function getEmploymentTimeSheetPopupView($project_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT employee_id
        FROM `project_employee`
        WHERE project_id = {$project_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $text = "";

        if($numRows > 0){
            $text = "
            <div class='button mb5'>
                <a href='#' class='addTimesheetForProjectEmployee' project_id='{$project_id}'>New Timesheet</a>
            </div>
            {$this->getEmploymentTimeSheetNewAllView($project_id)}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getAddHoursProjectEmployee() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $project_id = $fn->getReqParam('project_id');

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;

        $currentMonth = date("m");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
            'hideFirstOption' => true
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         1 => 'January'
                        ,2 => 'February'
                        ,3 => 'March'
                        ,4 => 'April'
                        ,5 => 'May'
                        ,6 => 'June'
                        ,7 => 'July'
                        ,8 => 'August'
                        ,9 => 'September'
                        ,10 => 'October'
                        ,11 => 'November'
                        ,12 => 'December'
                      );

        $SQLCheckMonth = "
        SELECT month
              ,DATE_FORMAT(date, '%M') AS Month
        FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        GROUP BY month,project_id
        ";
        $resultCheckMonth    = $db->sql_query($SQLCheckMonth);
        $dataArrayCheckMonth = $dbUtil->getResultsetAsArrayForForm($resultCheckMonth);

        $monthResultArray = array_diff_key($monthArray, $dataArrayCheckMonth);

        $yearRow  = "{$formObj->getDropDownByArray('Year', 'project_Time_year', $yearArray, $currentYear, $exp)}";
        $MonthRow = "{$formObj->getDropDownByArray('Month', 'project_Time_Month', $monthArray, $currentMonth, $expmonth)}";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&showHTML=0";
        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class= 'float_box yearDivHoursEmployeeForm'>
                <label>Year: </label>
                {$yearRow}

                <label class='monthlabelfilter'>Month: </label>
                {$MonthRow}
                <div class='float_right validationDivforAdd'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            </div>
            <div class= 'timesheetTableProj'>
                {$this->getAddDaysRowHeadTimesheet($project_id, $currentMonth, $currentYear)}
            </div>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddDaysRowHeadTimesheet1($project_id= '', $currentMonth= '', $currentYear= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($currentMonth == ''){
            $currentMonth = $fn->getReqParam('selected_month');
        }

        if($currentYear == ''){
            $currentYear = $fn->getReqParam('selected_year');
        }


        $text = "";
        $rows = "";

        $SQL = "
        SELECT pe.employee_id
             , e.employee_name
        FROM `project_employee` pe
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        WHERE pe.project_id = {$project_id}
        ";

        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            $dayCount = 1;
            $totalHoursSheet = 0;
            for ($j= 0; $j < $count2; $j++) {

                $timeSheetDate =  $currentYear.'-'.$currentMonth.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $dayContRow .= "<td class='timesheetDaysTd'><input type='text' value='{$rowTimesheetDays['employee_hours']}' id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}'  class='text timeSheetDaysInput txtRight' name='TimesheetDaysProject{$dayCount}[]'></td>";
                $dayCount++;

                $totalHoursSheet += $rowTimesheetDays['employee_hours'];
            }

            $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

            $SQLTimesheet = "
            SELECT hourly_rate
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            $totalHoursSheet = number_format($totalHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        <td class='timesheetDaysTdRate'><input type='text' value='{$rowTimesheet['hourly_rate']}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]'></td>
                        {$dayContRow}
                        <td class='timesheetDaysTd'><input type='text' value='{$totalHoursSheet}' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1></td>
                    ";

            $rows .= "
            <tr>
                <td>{$count}</td>
                <td><div class = 'employee_name_timesheet'>
                        {$row['employee_name']}
                    </div>
                </td>
                {$daysRow}
            </tr>";
            $count++;

        }


        $dayContHeader = "";
        $dayNameRow = "";
        $dayHeaderCount = 1;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        for ($j= 0; $j < $count2; $j++) {
            $dateTimesheet =  $currentYear.'-'.$currentMonth.'-'.$dayHeaderCount;
            $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
            $dayNameDate = strtoupper($dayNameDate);
            $dayNameRow .= "<th class='timesheetDaysTd txtCenter'>{$dayNameDate}</th>";

            $dayContHeader .= "<th class='txtCenter'>{$dayHeaderCount}</th>";
            $dayHeaderCount++;
        }

        $header = "
        <tr style='background-color:#EAEAE8;'>
            <th rowspan='2'>S.No</th>
            <th rowspan='2'>Employee Name</th>
            <th rowspan='2' class='txtRight'>Rate / HR</th>
            {$dayNameRow}
            <th rowspan='2' class='txtRight'>Total HRS</th>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            {$dayContHeader}
        </tr>
        ";

        $text = "
        <div class= 'float_box timesheetTableProjRel'>
            <table class='thinlist'>
                {$header}
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddDaysRowHeadTimesheet($project_id= '', $currentMonth= '', $currentYear= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($currentMonth == ''){
            $currentMonth = $fn->getReqParam('selected_month');
        }

        if($currentYear == ''){
            $currentYear = $fn->getReqParam('selected_year');
        }


        $text = "";
        $rows = "";
        $header = "";

        $SQL = "
        SELECT pe.employee_id
             , e.employee_name
        FROM `project_employee` pe
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.employee_name ASC
        ";

        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $rowSplitCount = 1;
            for ($j= 0; $j < $count2; $j++) {
            $dayContHeader = "";
            $dayNameRow = "";
                $timeSheetDate =  $currentYear.'-'.$currentMonth.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }
                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayContRow .= "<th class='timesheetDaysTd txtCenter'>{$dayNameDate}<br/>{$dayCount}&nbsp;<input type='text' value='' id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysInput txtRight' name='TimesheetDaysProject{$dayCount}[]'></th>";
                $dayCount++;

                $totalHoursSheet += $rowTimesheetDays['employee_hours'];
            }

            $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

            $SQLTimesheet = "
            SELECT hourly_rate
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            $totalHoursSheet = number_format($totalHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                    ";

            $dayContHeader = "";
            $dayNameRow = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $currentYear.'-'.$currentMonth.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hrlyRate = '';
            $SQLQuote = "
            SELECT qi.quantity
            FROM quote q
            LEFT JOIN quote_items qi ON (qi.quote_id = q.quote_id)
            WHERE q.project_id = {$project_id}
            AND (q.quote_status = 'Confirmed' OR q.quote_status = 'Order Raised')
            ";
            $resultQuote = $db->sql_query($SQLQuote);
            $QuoteRec    = $db->sql_fetchrow($resultQuote);

            if ($QuoteRec['quantity'] != ''){
                $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
                if($projRec['category'] == 'Manpower Supply'){
                    $hrlyRate = $QuoteRec['quantity'];
                }
            }

            $rows .= "
                <table class='thinlist timesheetTableProjReltab'>
                    <thead>
                        <tr>
                            <th>S.No: {$count}</th>
                            <th colspan='5'>
                                <div class = 'float_left'>Employee Name:
                                    <div class = 'employee_name_timesheet float_right'>
                                        {$row['employee_name']}
                                    </div>
                                </div>
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='4'>Rate / HR:
                                <input type='text' value='{$hrlyRate}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]'>
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='6'>Total HRS:
                                <input type='text' value='' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            {$daysRow}
                        </tr>
                    </tbody>
                </table>
            <br/>
            ";

            $count++;

        }

        $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

        $SQLProjectTimeSheet ="
        SELECT * FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
        ";
        $resultProjectTimeSheet  = $db->sql_query($SQLProjectTimeSheet);
        $numRowsProjectTimeSheet = $db->sql_numrows($resultProjectTimeSheet);

        if($numRowsProjectTimeSheet > 0){
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                <p class='ValidationForTimesheetRecord'> Record already created for this month. </p>
            </div>
            ";
        }else{
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                {$rows}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getEditHoursProjectEmployee() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $month = ltrim($month,"0");

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;
        $currentMonth = date("m");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
              'hideFirstOption' => true
             ,'disabled'  => true
        );

        $expmonth = array(
            'hideFirstOption' => true
            ,'useKey' => true
            ,'disabled'  => true
        );


        $monthArray = array(
                         1 => 'January'
                        ,2 => 'February'
                        ,3 => 'March'
                        ,4 => 'April'
                        ,5 => 'May'
                        ,6 => 'June'
                        ,7 => 'July'
                        ,8 => 'August'
                        ,9 => 'September'
                        ,10 => 'October'
                        ,11 => 'November'
                        ,12 => 'December'
                      );

        $yearRow  = "{$formObj->getDropDownByArray('Year', 'project_Time_year', $yearArray, $year, $exp)}";
        $MonthRow = "{$formObj->getDropDownByArray('Month', 'project_Time_Month', $monthArray, $month, $expmonth)}";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&showHTML=0";

        $SQLInvoiceCheck = "
        SELECT i.start_date
              ,i.end_date
        FROM `invoice` i
        LEFT JOIN `order` o ON(o.project_id = {$project_id})
        WHERE i.status != 'Cancelled'
        AND i.order_id = o.order_id
        AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
        AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
        $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
        $msg = '';
        if($numRowsInvoiceCheck > 0){
            $msg = "<div class='msgforInvoiceCreated'><font>Please cancel the related invoice to edit the below records.</font></div>";
        }

        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class= 'float_box yearDivHoursEmployeeForm'>
                <label>Year: </label>
                {$yearRow}

                <label class='monthlabelfilter'>Month: </label>
                {$MonthRow}
                <div class='float_right validationDivforEdit'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                {$msg}
            </div>
            <div class= 'timesheetTableProj'>
                {$this->getEditDaysRowHeadTimesheet($project_id, $month, $year)}
            </div>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_Time_year' value='{$year}' />
            <input type='hidden' name='project_Time_Month' value='{$month}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditDaysRowHeadTimesheet($project_id= '', $month= '', $year= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($year == ''){
            $year = $fn->getReqParam('year');
        }

        if($month == ''){
            $month = $fn->getReqParam('month');
        }

        $text = "";
        $rows = "";
        $header = "";

        $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

        $SQL = "
        SELECT e.employee_name
              ,e.employee_id
        FROM project_employee et
        LEFT JOIN employee e ON(e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        GROUP BY et.employee_id
        ORDER BY e.employee_name ASC
        ";

        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $rowSplitCount = 1;
            for ($j= 0; $j < $count2; $j++) {
            $dayContHeader = "";
            $dayNameRow = "";
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $SQLInvoice = "
                SELECT i.start_date
                      ,i.end_date
                FROM `invoice` i
                LEFT JOIN `order` o ON(o.project_id = {$project_id})
                WHERE i.status != 'Cancelled'
                AND i.order_id = o.order_id
                AND '{$timeSheetDate}' BETWEEN i.start_date AND i.end_date
                ";
                $resultInvoice   = $db->sql_query($SQLInvoice);
                $numRowsInvoice  = $db->sql_numrows($resultInvoice);

                $disabledInput = '';
                if($numRowsInvoice > 0){
                    $disabledInput = "disabled=1";
                }

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }
                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayContRow .= "<th class='timesheetDaysTd txtCenter'>{$dayNameDate}<br/>{$dayCount}&nbsp;<input type='text' value='{$rowTimesheetDays['employee_hours']}' id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysInput txtRight' name='TimesheetDaysProject{$dayCount}[]' {$disabledInput}></th>";
                $dayCount++;

                $totalHoursSheet += $rowTimesheetDays['employee_hours'];
            }

            $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

            $SQLTimesheet = "
            SELECT hourly_rate
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            $totalHoursSheet = number_format($totalHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                    ";

            $dayContHeader = "";
            $dayNameRow = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hiddenHrlyRate = '';
            $SQLInvoiceCheck = "
            SELECT i.start_date
                  ,i.end_date
            FROM `invoice` i
            LEFT JOIN `order` o ON(o.project_id = {$project_id})
            WHERE i.status != 'Cancelled'
            AND i.order_id = o.order_id
            AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
            AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
            $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
            $disabledInputHrly = '';
            if($numRowsInvoiceCheck > 0){
                $disabledInputHrly = "disabled=1";
                $hiddenHrlyRate = "<input type='hidden' value='{$rowTimesheet['hourly_rate']}' name='TimesheetRatePerHr[]'>";
            }


            $rows .= "
                <table class='thinlist timesheetTableProjReltab'>
                    <thead>
                        <tr>
                            <th>S.No: {$count}</th>
                            <th colspan='5'>
                                <div class = 'float_left'>Employee Name:
                                    <div class = 'employee_name_timesheet float_right'>
                                        {$row['employee_name']}
                                    </div>
                                </div>
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='4'>Rate / HR:
                                <input type='text' value='{$rowTimesheet['hourly_rate']}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]' {$disabledInputHrly}>
                                {$hiddenHrlyRate}
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='6'>Total HRS:
                                <input type='text' value='{$totalHoursSheet}' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            {$daysRow}
                        </tr>
                    </tbody>
                </table>
            <br/>
            ";

            $count++;

        }

        $text = "
        <div class= 'float_box timesheetTableProjRel'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmploymentTimeSheetNewAllView($project_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT  SUM(et.employee_hours) AS totalHours
                ,SUM(et.employee_hours*et.hourly_rate) AS totalAmount
                ,DATE_FORMAT(et.date, '%M') AS Month
                ,DATE_FORMAT(et.date, '%m') AS month_req
                ,DATE_FORMAT(et.date, '%Y') AS year_req
                ,DATE_FORMAT(et.date, '%Y-%m') AS year_Months
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        GROUP BY DATE_FORMAT(et.date, '%Y-%m')
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
                $editLink = "<a project_id={$project_id} year={$row['year_req']} month={$row['month_req']} class='editTimesheetForProjectEmployee' href='#'>Edit</a>";

                $SQL2 = "
                SELECT e.employee_name
                      ,e.employee_id
                FROM employee_timesheet et
                LEFT JOIN employee e ON(e.employee_id = et.employee_id)
                WHERE et.project_id = {$project_id}
                AND DATE_FORMAT(date, '%Y-%m') = '{$row['year_Months']}'
                GROUP BY et.employee_id
                ORDER BY e.employee_name ASC
                ";

                $result2 = $db->sql_query($SQL2);
                $rows2 = '';
                while ($row2 = $db->sql_fetchrow($result2)) {
                    $rows2 .= "{$this->getTimeSheetByEmployee($project_id, $row2['employee_id'], $row['year_Months'])}";
                }

                $addEmployeeLineItemView = '';
                if($row['totalHours'] > 0 ) {
                    $addEmployeeLineItemView ="
                    <div class='float_right'>
                        <a href='#' class='employeeListShow'>View Staff</a>
                    </div>
                    ";
                }

                $rows .= "
                <tbody class='employeeMonthRow'>
                    <tr class='addEmployeeRow'>
                        <td>{$count}</td>
                        <td>{$row['Month']}</td>
                        <td>{$row['totalHours']}</td>
                        <td>{$row['totalAmount']}</td>
                        <td>{$editLink}</td>
                        <td>{$addEmployeeLineItemView}</td>
                    </tr>

                    <tr class='employeeListHide'>
                        <td></td>
                        <td colspan='5'>
                            <table class='thinlist'>
                                <tr class='employeeTrTh'>
                                    <th>Name</th>
                                    <th>Total Hours</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                                {$rows2}
                            </table>
                        </td>
                    </tr>

                </tbody>
                ";
                $count++;
        }

            $text = '';

            $urlOverAllPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printOverAllEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

            $overAllTimeSheetPdf = "
            <div class='float_right printTimeSheetPdf'>
                <a href='{$urlOverAllPrintEmployeePdf}' target='_blank'>Over All Print Timesheet</a>
             </div>
             ";

            if ($numRows > 0)  {
            $text = "
            <div id='employeePortal' class='linkPortalWrapper'>
                <table class='list'>
                    <thead>
                    <tr>
                        <th colspan='6' align='left'>Employee Time Sheet</th>
                    </tr>
                    <tr>
                        <th>S.No</th>
                        <th>Month</th>
                        <th>Total Hours</th>
                        <th>Amount</th>
                        <th>Action</th>
                        <th></th>
                    </tr>
                    </thead>
                    {$rows}
                </table>
            </div>
            ";

            return $text;
        }
    }

    /**
     *
     */
    function getTimeSheetByEmployee($project_id, $employee_id, $year_Months) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');


        $SQL = "
        SELECT e.employee_id
              ,SUM(employee_hours)As employee_total_hrs
              ,e.employee_name
              ,e.employee_work_type
              ,et.hourly_rate AS add_hourly_rate
        FROM employee_timesheet et
        LEFT JOIN (employee e) ON (e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$year_Months}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        $urlPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

        $addEmployeeLineItemView = '';
        if($row['employee_total_hrs'] > 0 ) {
            $addEmployeeLineItemView ="
            <div class='float_right'>
                <a href='#' class='timesheetLayoutShow'>View Hours</a>
            </div>
            ";
        }

        $amount = ($row['employee_total_hrs'] * $row['add_hourly_rate']);
        $amount = number_format($amount ,2);

        $rows = "
        <tr class='addEmployeeRow2 employeeListHide'>
            <td>{$row['employee_name']}</td>
            <td>{$row['employee_total_hrs']}</td>
            <td>{$amount}</td>
            <td class='viewRowWidth'>{$addEmployeeLineItemView}</td>
        </tr>
        <tr class='timesheetLayoutHide timeSheetLayout'>
            <td colspan='3'>
            </td>
            <td colspan='3'>
                <div class = 'timeSheetTableScroll'>
                    <table class='thinlist'>
                        {$this->getEmployeeAddTimeHoursNewListView($project_id,$row['employee_id'], $year_Months)}
                    </table>
                </div>
            </td>
        </tr>
        ";

        $text = '';

        if ($numRows > 0)  {
            $text = "
            {$rows}
            ";

           return $text;
        }
    }

     /**
     *
     */
    function getEmployeeAddTimeHoursNewListView($project_id, $employee_id, $year_Months) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT et.*
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year_Months}'
        ORDER BY et.date ASC
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

            while ($row = $db->sql_fetchrow($result)) {
                $employee_date   = $fn->getCPDate($row['date'], 'd-m-Y');

                $editEmployeeView = "index.php?_topRm=project&module=enggCrm_project&_spAction=editEmploymentViewItem&project_id={$project_id}&employee_id={$row['employee_id']}&employee_timesheet_id={$row['employee_timesheet_id']}&showHTML=0";

                $editEmployeeView = "
                <div class='float_left'>
                    <a class='editForEmployeeItemView' href='{$editEmployeeView}'>Edit</a>
                </div>
                ";

                $deleteEmployeeView = "
                <div class='float_right'>
                    <a href='#' class='deleteForEmployeeItemView' project_id='{$row['project_id']}' employee_id= '{$row['employee_id']}' employee_timesheet_id={$row['employee_timesheet_id']}>Delete</a></td>
                </div>
                ";

                $rows .= "
                    <tr class = 'employeeItemBackgroundSecond'>
                        <td>{$employee_date}</td>
                        <td>{$row['employee_hours']}</td>
                    </tr>
                ";
            }

            $text = '';

            if ($numRows > 0)  {
            $text = "
            <tr class='employeeTrTh'>
                <th>Date</th>
                <th>Hours</th>
            </tr>
            {$rows}
            ";

            return $text;

        }
    }
    /**
     *
     */
    function getEmploymentTimeSheetView($project_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');


        $SQL = "
        SELECT emp.*
               ,project_employee_id
        FROM employee emp
        LEFT JOIN (project_employee pe) ON (pe.employee_id = emp.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY emp.employee_name ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $addHours = '';
        $employeeActions = '';
        while ($row = $db->sql_fetchrow($result)) {
                $addHoursUrl = "index.php?_topRm=project&module=enggCrm_project&_spAction=addHoursFromEmployee&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";
                $urlPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

                $employeeActions ="
                <div class='float_left'>
                    <a href='#' class='deleteEmployeePortal' employee_id='{$row['employee_id']}' project_id='{$project_id}'>Delete</a>
                </div>
                <div class='float_left'>
                    <a href='{$addHoursUrl}' project_id = {$project_id} class='addToHours'>Add Hours</a>
                 </div>
                <div class='float_left printTimeSheetPdf'>
                    <a href='{$urlPrintEmployeePdf}' target='_blank'>Print Timesheet</a>
                 </div>
                ";

                $sqlForEmployeeItems ="
                SELECT SUM(employee_hours)As employee_total_hrs
                       ,employee_id
                FROM employee_timesheet
                WHERE employee_id = {$row['employee_id']}
                  AND project_id = {$project_id}
                ";
                $resultForEmployeeItems  = $db->sql_query($sqlForEmployeeItems);
                $rowEmployeeItems        = $db->sql_fetchrow($resultForEmployeeItems);

                $addEmployeeLineItemView = '';
                if($rowEmployeeItems['employee_total_hrs'] > 0 ) {
                    $addEmployeeLineItemView ="
                    <div class='float_right'>
                        <a href='#' class='employeeLayoutShow'>View</a>
                    </div>
                    ";
                }

                $amount = 'Full Time staff';
                if ($row['employee_work_type'] == 'Part time') {
                    $amount = ($rowEmployeeItems['employee_total_hrs'] * $row['add_hourly_rate']);
                }

                $rows .= "
                <tbody class='employeeDetailRow'>
                    <tr class='addEmployeeRow'>
                        <td>{$row['employee_name']}</td>
                        <td>{$rowEmployeeItems['employee_total_hrs']}</td>
                        <td>{$amount}</td>
                        <td class='viewRowWidth'>{$addEmployeeLineItemView}</td>
                        <td colspan='2'>{$employeeActions}</td>
                    </tr>
                    {$this->getEmployeeAddTimeHoursListView($project_id,$row['employee_id'])}
                </tbody>
                ";
            }

            $text = '';

            $urlOverAllPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printOverAllEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

            $overAllTimeSheetPdf = "
            <div class='float_right printTimeSheetPdf'>
                <a href='{$urlOverAllPrintEmployeePdf}' target='_blank'>Over All Print Timesheet</a>
             </div>
             ";

            if ($numRows > 0)  {
            $text = "
            <div id='employeePortal' class='linkPortalWrapper'>
                <table class='list'>
                    <thead>
                    <tr>
                        <th colspan='6' align='left'>Employee Time Sheet</th>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <th>Total Hours</th>
                        <th>Amount</th>
                        <th colspan='2'>{$overAllTimeSheetPdf}</th>
                    </tr>
                    </thead>
                    {$rows}
                </table>
            </div>
            ";

            return $text;
        }
    }

    /**
     *
     */
    function getAddHoursFromEmployee() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $employee_id = $fn->getReqParam('employee_id');
        $project_id  = $fn->getReqParam('project_id');

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addHoursFromEmployeeSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'date', date('Y-m-d'))}
                {$formObj->getTBRow('Hours', 'employee_hours')}
                {$formObj->getTARow('Description', 'description')}
            </fieldset>
            <input type='hidden' name='employee_id' value='{$employee_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmployeeAddTimeHoursListView($project_id, $employee_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT et.*
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        ORDER BY et.date ASC
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

            while ($row = $db->sql_fetchrow($result)) {
                $employee_date   = $fn->getCPDate($row['date'], 'd-m-Y');

                $editEmployeeView = "index.php?_topRm=project&module=enggCrm_project&_spAction=editEmploymentViewItem&project_id={$project_id}&employee_id={$row['employee_id']}&employee_timesheet_id={$row['employee_timesheet_id']}&showHTML=0";

                $editEmployeeView = "
                <div class='float_left'>
                    <a class='editForEmployeeItemView' href='{$editEmployeeView}'>Edit</a>
                </div>
                ";

                $deleteEmployeeView = "
                <div class='float_right'>
                    <a href='#' class='deleteForEmployeeItemView' project_id='{$row['project_id']}' employee_id= '{$row['employee_id']}' employee_timesheet_id={$row['employee_timesheet_id']}>Delete</a></td>
                </div>
                ";

                $rows .= "
                    <tr class = 'employeeLayoutHide employeeItemBackground showAddEmployeeLineRow'>
                        <td class='employeeRowBorder'></td>
                        <td>{$employee_date}</td>
                        <td>{$row['employee_hours']}</td>
                        <td class='descriptionWrap'>{$row['description']}</td>
                        <td>{$editEmployeeView} {$deleteEmployeeView}</td>
                    </tr>
                ";
            }

            $text = '';

            if ($numRows > 0)  {
            $text = "
            <tr class = 'employeeLayoutHide showAddEmployeeLineRow'>
                <th class='employeeRowBorder'></th>
                <th>Date</th>
                <th>Hours</th>
                <th class='employeeRowBackground'>Description</th>
                <th class='employeeRowBackground'></th>
            </tr>
            {$rows}
            ";

            return $text;

        }
    }


    /**
     *
     */
    function getPrintOverAllEmployeeTimesheetForPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Boxx Engg');
        $pdf->SetSubject('Print Employee');
        $pdf->SetTitle('Print Employee');

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
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $project_id = $fn->getReqParam('project_id');

        $SQL = "
        SELECT emp.*
              ,p.project_code
        FROM employee emp
        LEFT JOIN (project_employee pe) ON (emp.employee_id = pe.employee_id)
        LEFT JOIN (project p) ON (pe.project_id = p.project_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY emp.employee_name ASC
        ";
        $result  = $db->sql_query($SQL);

        $pdf->SetFont('arialblack','',10);
        $pdf->ln(5);

        $current_date = date('d-m-Y');

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center"><font style="font-size:25px; font-weight:bold">OVERALL EMPLOYEE TIMESHEET</font>
                </td>
            </tr>
            <tr>
                <td width="30%"></td>
                <td width="40%" align="center"></td>
                <td width="30%" align="right"><b>Date:</b>'.$current_date .'</td>
            </tr>
        </table>
        ';

        $tbl2 ='<table border="1" cellpadding="4" width="100%">
                    <thead>
                        <tr bgcolor="#DDE4FF">
                            <th align="left" style="font-weight:bold;">Employee Name</th>
                            <th align="center" style="font-weight:bold;">Total Hours</th>
                            <th align="right" style="font-weight:bold;"> Amount</th>
                        </tr>
                    </thead>';

        $totalRate = '';

        while ($row = $db->sql_fetchrow($result)) {

            $sqlForEmployeeView ="
            SELECT SUM(employee_hours) AS employee_total_hrs
            FROM employee_timesheet
            WHERE employee_id = {$row['employee_id']}
              AND project_id = {$project_id}
            ";
            $resultForEmployeeView  = $db->sql_query($sqlForEmployeeView);
            $rowEmployeeView        = $db->sql_fetchrow($resultForEmployeeView);

            $totalRate = 'Full Time staff';
            if ($row['employee_work_type'] == 'Part time') {
                $totalRate = ($rowEmployeeView['employee_total_hrs'] * $row['add_hourly_rate']);
                $totalRate = number_format($totalRate,2);
            }

            $tbl2 = $tbl2.'<tr>
                                <td>'.$row['employee_name'].'</td>
                                <td align="center">'.$rowEmployeeView['employee_total_hrs'].'</td>
                                <td align="right">'. $totalRate .'</td>
                            </tr>';
        }

        $tbl2 = $tbl2.'</table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(8);
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $row['project_code'] . '-Overall-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintEmployeeTimesheetForPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Boxx Engg');
        $pdf->SetSubject('Print Employee');
        $pdf->SetTitle('Print Employee');

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
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');

        $SQL = "
        SELECT et.*
             ,emp.employee_name
             ,p.project_code
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        WHERE et.employee_id = {$employee_id}
          AND et.project_id = {$project_id}
        AND et.employee_hours != ''
        ";
        $result             = $db->sql_query($SQL);
        $resultEmployee     = $db->sql_query($SQL);
        $quoteEmployee      = $db->sql_fetchrow($resultEmployee);

        $current_date = date('d-m-Y');

        $pdf->SetFont('arialblack','',10);
        $pdf->ln(5);
        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center"><font style="font-size:25px; font-weight:bold">EMPLOYEE TIMESHEET</font>
                </td>
            </tr>
            <tr>
                <td width="30%"></td>
                <td width="40%" align="center"></td>
                <td width="30%" align="right"><b>Date:</b>'.$current_date .'</td>
            </tr>
        </table>
        ';

        $tbl2 ='<table border="1" cellpadding="4" width="100%">
                    <thead>
                        <tr bgcolor="#DDE4FF">
                            <th height="30" colspan="3" style="font-weight:bold;font-size:14pt;">Employee Name : '.$quoteEmployee['employee_name'].'</th>
                        </tr>
                        <tr bgcolor="#DDE4FF">
                            <th height="30" colspan="3" style="font-weight:bold;font-size:14pt;">Project Code : '.$quoteEmployee['project_code'].'</th>
                        </tr>
                        <tr bgcolor="#DDE4FF">
                            <th width="20%" align="left" style="font-weight:bold;">DATE</th>
                            <th width="20%" align="center" style="font-weight:bold;">HOURS</th>
                            <th width="60%" align="center" style="font-weight:bold;">DESCRIPTION</th>
                        </tr>
                    </thead>';

        $totalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $employee_date   = $fn->getCPDate($row['date'], 'd-m-Y');

            $tbl2 = $tbl2.'<tr>
                                <td width="20%">'.$employee_date.'</td>
                                <td width="20%" align="right">'.$row['employee_hours'].'</td>
                                <td width="60%">'.$row['description'].'</td>
                            </tr>';

            $totalValue += $row['employee_hours'];
        }

        $totalValue = number_format($totalValue, 2);

        $tbl2 = $tbl2.'<tr>
                           <td align="right" style="font-weight:bold;">Total Hours</td>
                           <td align="right" style="font-weight:bold;">'. $totalValue .'</td>
                           <td align="right" style="font-weight:bold;"></td>
                      </tr>
                      </table>';


        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(8);
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $quoteEmployee['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn = Zend_Registry::get('fn');

        $project_id = $fn->getReqParam('project_id');
        $quote_id   = $fn->getReqParam('quote_id');

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $title       = "<input type='text' value='' id='title' class='text lineItemTitle' name='title[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearLineItem'><u>Clear</u></a></td>";

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
        <a href='#' class='addRow button mb10'>Add Line Item</a>
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

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleLineItemSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='quote_id' value='{$quote_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddLineItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $title       = "<input type='text' value='' id='title' class='text lineItemTitle' name='title[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearLineItem'><u>Clear</u></a></td>";

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
    function getProjectMaterialPortal($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT pm.*
        FROM project_materials pm
        WHERE pm.project_id = {$project_id}
        ORDER BY pm.title ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $cancelLink = '';
            if ($row['status'] != 'Cancelled') {
                $cancelLink = "<a href='#' class='cancelMaterial' project_materials_id={$row['project_materials_id']}><u>Cancel</u></a>";
            }

            $add_class = '';
            if ($row['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td>{$row['quantity']}</td>
                <td class='txtRight'>{$row['amount']}</td>
                <td>{$row['description']}</td>
                <td class='{$add_class}'>{$row['status']}</td>
                <td>{$updation_details}</td>
                <td>{$cancelLink}</td>
            </tr>
            ";
        }

        $urlPrintmaterialLinkPdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printmaterialLinkForPdf&project_id={$project_id}&showHTML=0";

        $text = "
        <div class='button mb5'>
            <a href='#' class='addMultipleMaterials' project_id='{$project_id}'>Add materials used</a>
        </div>
        <div class='button mb5'>
            <a href='{$urlPrintmaterialLinkPdf}' target='_blank' class='printLink' project_id='{$project_id}'>Print Pdf</a>
        </div>
        <div id='materialsPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='7' align='left'>Materials used</th>
                    </tr>
                    <tr>
                        <th>Title</th>
                        <th>Quantity</th>
                        <th class='txtRight'>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Updated By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class='materialsDetailRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getprintmaterialLinkForPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Boxx Engg');
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

        $project_id = $fn->getReqParam('project_id');

        $SQL = "
        SELECT p.*
              ,pm.title
              ,pm.quantity
              ,pm.description
              ,pm.amount
              ,c.company_name
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_country
              ,c.billing_address_po_code
              ,c.company_id
              ,co.salutation
              ,co.first_name
              ,co.last_name
        FROM project_materials pm
        LEFT JOIN (project p) ON (pm.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        WHERE p.project_id = {$project_id}
          AND pm.status != 'Cancelled'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today      = date("d-m-Y");

        $sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center"><font style="font-size:16px; font-weight:bold">MATERIALS</font></td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }
        $tbl2 = '
        <table border="0" width="100%" cellpadding="">
                    <tr>
                        <td width="65%" style="font-size:12px; font-weight:bold;">TO: </td>
                        <td width="23%" align="right" style="font-size:12px; font-weight:bold;">DATE &nbsp;: </td>
                        <td width="12%" align="right" style="font-size:12px; font-weight:bold;">'.  $today .'</td>
                    </tr>
                    <tr>
                        <td style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_flat']).'</td>
                        <td colspan="2"></td>
                    </tr>
                    ' .  $rowStreet .'
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="font-size:12px; font-weight:bold;">ATTN:&nbsp;'.strtoupper($company['salutation']).' '.strtoupper($company['first_name']).'</td>
                        <td colspan="2"></td>
                    </tr>
        </table>
        ';

        $tbl3 = '<table border="1" cellpadding="2"  width="100%">
                    <thead>
                        <tr>
                            <th width="5%" align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                            <th width="15%" align="center" style="font-size:12px; font-weight:bold;">DATE</th>
                            <th width="30%" align="center" style="font-size:12px; font-weight:bold;">DESCRIPTION</th>
                            <th width="10%" align="center" style="font-size:12px; font-weight:bold;">QTY</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">UNIT PRICE (S$)</th>
                            <th width="14%" align="center" style="font-size:12px; font-weight:bold;"> TOTAL AMT (S$)</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $subtotal_amount = $row['quantity'] * $row['amount'];
            $material_date = $fn->getCPDate($company['creation_date'], 'd-m-Y');

            $tbl3 = $tbl3.'<tr>
                                <td width="5%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="15%" style="font-size:12px;">'.$material_date.'</td>
                                <td width="30%" style="font-size:12px;">'.$row['title'].'</td>
                                <td width="10%" align="center" style="font-size:12px;">'.$row['quantity'].'</td>
                                <td width="13%" align="center" style="font-size:12px;">'.$row['amount'].'</td>
                                <td width="14%" align="right" style="font-size:12px;">'.number_format($subtotal_amount, 2).'</td>
                                <td width="13%" style="font-size:12px;">'.$row['description'].'</td>
                            </tr>
                    ';
            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'];
            $gstvalue = $subtotalValue * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + $subtotalValue;
            $count++;
        }

        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="5" style="font-size:12px; font-weight:bold;">SUB TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                          <td></td>
                      </tr>
                      <tr>
                          <td colspan="5" align="right" style="font-size:12px; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                          <td></td>
                       </tr>
                       <tr>
                          <td colspan="5" align="right" style="font-size:12px; font-weight:bold;">TOTAL AMOUNT</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                          <td></td>
                       </tr>
                    </table>';

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td style="line-height:20px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="font-size:12px;" width="70%">Requested by :</td>
                <td style="font-size:12px;" width="30%">Approved by :</td>
            </tr>
        </table>
        ';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td width="70%"></td>
                <td width="30%" style="border-bottom:2px solid black"></td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size:12px; font-weight:bold;">Authorised signature</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Materials.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getAddMultipleMaterials() {
        $fn = Zend_Registry::get('fn');

        $project_id = $fn->getReqParam('project_id');

        $title       = "<input type='text' value='' id='title' class='text materialTitle' name='title[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text materialQuantity' name='quantity[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text materialAmount' name='amount[]'>";
        $remarks     = "<input type='text' value='' id='description' class='text materialDescription' name='description[]'>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        ";

        $header ="
        <tr><a href='#' class='addMaterialRow button mb10'>Add Material</a></tr>
        <tr style='background-color:#EAEAE8;text-align:left;'>
            <th>Description</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Remarks</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleMaterialsSubmit&showHTML=0";

        $text = "
        <form id='addMultipleMaterialsForm' class='addMultipleMaterialsForm' method='post' action='{$formAction}'>
            <table class='thinlist' id='materialsTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddMaterialRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title       = "<input type='text' value='' id='title' class='text materialTitle' name='title[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text materialQuantity' name='quantity[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text materialAmount' name='amount[]'>";
        $remarks     = "<input type='text' value='' id='description' class='text materialDescription' name='description[]'>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getPurchaseOrderPortal($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $sql = "
        SELECT DISTINCT po.company_id_supplier
              ,c.company_name
              ,po.purchase_order_id
        FROM purchase_order po
        LEFT JOIN (company c) ON (po.company_id_supplier = c.company_id)
        WHERE po.project_id = {$project_id}
        ORDER BY c.company_name ASC
        ";
        $result  = $db->sql_query($sql);
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForPo = "index.php?_topRm=project&module=enggCrm_project&_spAction=editForPo&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
            $urlPrintpurchaseorder = "index.php?_topRm=project&module=enggCrm_project&_spAction=Printpurchaseorder&project_id={$project_id}&supplier_id={$row['company_id_supplier']}&showHTML=0";

            $rows .= "
            <tr class='header'>
                <td colspan='5'>{$row['company_name']}</td>
                <td colspan='2'>
                    <div class='float_left'>
                        <u><a class='editForPo' style='color:#fff;' href='{$editForPo}'>Edit PO</a></u>
                    </div>
                    <div class='float_right'>
                        <u><a target='_blank' style='color: #fff;' href='{$urlPrintpurchaseorder}'>Print pdf</a></u>
                    </div>
                </td>
            </tr>
            {$this->getMaterialsofPurchaseOrderForSupplier($row['purchase_order_id'], $row['company_id_supplier'])}
            ";
        }

        $text = "
        <div class='button mb5'>
            <a href='#' class='addMultiplePurchaseOrder' project_id='{$project_id}'>Add Purchase Order</a>
        </div>
        <div id='quotesPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='7' align='left'>Materials Purchased</th>
                    </tr>
                </thead>
                <tbody class='poItemsRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getPrintpurchaseorder() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
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

        $project_id = $fn->getReqParam('project_id');
        $supplier_id = $fn->getReqParam('supplier_id');

        $SQL = "
        SELECT DISTINCT pop.po_product_id
              ,pop.item_title
              ,pop.quantity
              ,pop.description
              ,pop.amount
              ,pop.unit
              ,po.company_id_supplier
              ,po.delivery_terms
              ,c.company_name
              ,c.category
              ,c.address_flat
              ,c.address_street
              ,c.address_country
              ,c.address_po_code
              ,c.company_id
              ,p.project_code
              ,p.title
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (pop.purchase_order_id = po.purchase_order_id)
        LEFT JOIN (project p) ON (po.project_id = p.project_id)
        LEFT JOIN (company c) ON (po.company_id_supplier = c.company_id)
        WHERE po.project_id = {$project_id}
          AND po.company_id_supplier = {$supplier_id}
          AND pop.status = 'Confirmed'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold">PURCHASE ORDER</td>
            </tr>
        </table>
        ';

        $po_current_date = date('d-m-Y');
        $po_date = date('ym/');
        $po_code = $po_date . substr($company['project_code'], 2) . '-' . $supplier_id;
        $rowStreet = '';
        if ($company['address_street']) {
            $rowStreet = '
            <tr>
                <td colspan="3" style="font-size:12px;">'.strtoupper($company['address_street']).'</td>
            </tr>
            ';
        }
        $tbl2 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td width="63%" style="font-size:12px; font-weight:bold;">TO: </td>
                <td width="19%" align="right" style="font-size:12px; font-weight:bold;"><b>PO No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                <td width="18%" align="right" style="font-size:12px; font-weight:bold;">PO-'.$po_code.'</td>
            </tr>
            <tr>
                <td width="63%" style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                <td width="19%" align="right" style="font-size:12px; font-weight:bold;"><b>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                <td width="18%" align="right" style="font-size:12px; font-weight:bold;">'.$po_current_date.'</td>
            </tr>
            <tr>
                <td colspan="3" style="font-size:12px;">'.strtoupper($company['address_flat']).'</td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td colspan="3" style="font-size:12px;">'.strtoupper($company['address_country']).' - '.$company['address_po_code'].'</td>
            </tr>
        </table>
        <table>
            <tr>
                <td style="line-height:20px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="font-size:12px; font-weight:bold;">'.strtoupper($company['title']).'</td>
            </tr>
        </table>';

        $tbl3 ='
        <table border="1" cellpadding="2"  width="100%">
            <thead>
                <tr>
                    <th width="5%" align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                    <th width="34%" align="center" style="font-size:12px; font-weight:bold;">Description</th>
                    <th width="11%" align="center" style="font-size:12px; font-weight:bold;">QTY</th>
                    <th width="15%" align="center" style="font-size:12px; font-weight:bold;">UNIT PRICE (S$)</th>
                    <th width="16%" align="center" style="font-size:12px; font-weight:bold;"> TOTAL AMT (S$)</th>
                    <th width="19%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                </tr>
            </thead>
        ';

        $subtotalValue = '';
        $count = 1;
        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {

            $companyRec = $fn->getRecordRowByID('company', 'company_id', $supplier_id);
            $subtotal_amount = $row['quantity'] * $row['amount'];

            $tbl3 = $tbl3.'<tr>
                                <td width="5%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="34%" style="font-size:12px;">'.$row['item_title'].'</td>
                                <td width="11%" align="center" style="font-size:12px;">'.$row['quantity'].'</td>
                                <td width="15%" align="center" style="font-size:12px;">'.$row['amount'].'</td>
                                <td width="16%" align="right" style="font-size:12px;">'.number_format($subtotal_amount, 2).'</td>
                                <td width="19%" style="font-size:12px;">'.$row['description'].'</td>
                            </tr>
                    ';
            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
            $gstvalue = $subtotalValue * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + $subtotalValue;
            $count++;

        }
        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="4" style="font-size:12px; font-weight:bold;">SUB TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                          <td></td>
                      </tr>
                      <tr>
                          <td colspan="4" align="right" style="font-size:12px; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                          <td></td>
                       </tr>
                       <tr>
                          <td colspan="4" align="right" style="font-size:12px; font-weight:bold;">TOTAL AMOUNT</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                          <td></td>
                       </tr>
                    </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td style="height: 15px;"></td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px; font-weight:bold">Delivery Instructions :</td>
            </tr>
            <tr>
                <td style="font-size:12px;">'.$company['delivery_terms'].'</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%">
            <tr>
                <td colspan="3" style="height: 40px;"></td>
            </tr>
            <tr>
                <td width="30%" style="font-size:12px;">Requested by :</td>
                <td width="40%"></td>
                <td style="font-size:12px;" width="30%">Authorised Signature</td>
            </tr>
            <tr>
                <td colspan="3" style="height: 30px;"></td>
            </tr>
            <tr>
                <td width="30%" style="border-bottom:2px solid black"></td>
                <td></td>
                <td width="30%" style="border-bottom:2px solid black"></td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $po_code . '-PO.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getAddMultiplePurchaseOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id = $fn->getReqParam('project_id');

        $sqlSupplier = "
        SELECT company_id, company_name
        FROM company
        WHERE category = 'Supplier';
        ";

        $supplier    = "
        <select name='supplier_id[]' class='poSupplier'>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";
        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearPo'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        $newRow = "<a href='#' class='addSinglePoRow button mb10'>Add Purchase Order</a>";

        $header = "
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Supplier</th>
            <th>Title</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Amount</th>
            <th>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultiplePurchaseOrderSubmit&showHTML=0";

        $text = "
        <form id='addMultiplePurchaseOrderForm' class='addMultiplePurchaseOrderForm' method='post' action='{$formAction}'>
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSinglePurchaseOrderRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sqlSupplier = "
        SELECT company_id, company_name
        FROM company
        WHERE category = 'Supplier';
        ";

        $supplier    = "
        <select name='supplier_id[] class='poSupplier'>
            <option value=''>Select Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";
        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a href='#' class='clearPo'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$supplier}</td>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getMaterialsofPurchaseOrderForSupplier($purchase_order_id, $supplier_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT po.*
        FROM po_product po
        WHERE po.purchase_order_id = {$purchase_order_id}
        ORDER BY po.item_title ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowsPo = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $companyRec = $fn->getRecordRowByID('company', 'company_id', $supplier_id);

            $cancelLink = '';
            if ($row['status'] != 'Cancelled') {
                $cancelLink = "<a href='#' class='cancelPoItem' po_product_id={$row['po_product_id']}><u>Cancel</u></a>";
            }

            $add_class = '';
            if ($row['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $rowsPo .= "
            <tr>
                <td class='{$add_class}'>{$row['item_title']}</td>
                <td class='{$add_class}'>{$row['quantity']}</td>
                <td class='{$add_class}'>{$row['unit']}</td>
                <td class='txtRight {$add_class}'>{$row['amount']}</td>
                <td class='{$add_class}'>{$row['description']}</td>
                <td>{$updation_details}</td>
                <td>{$cancelLink}</td>
            </tr>
            ";
        }

        $rowsPoPrint = "
        <tr>
            <th>Title</th>
            <th>Quantity</th>
            <th>UoM</th>
            <th class='txtRight'>Amount</th>
            <th>Remarks</th>
            <th>Updated By</th>
            <th></th>
        </tr>
        {$rowsPo}
        ";

        return $rowsPoPrint;
    }

    /**
     * Purchase Order Edit
     */
    function getEditForPo() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $rowPo = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $formActionEditForPo = "index.php?module=enggCrm_project&_spAction=editForPoSubmit&lnkRoom={$tv['lnkRoom']}&purchase_order_id={$purchase_order_id}&showHTML=0";

        $shipping_address_fields = "";
        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $shipping_address_fields = "
                {$formObj->getTBRow('Address 1', 'shipping_address_flat', $rowPo['shipping_address_flat'])}
                {$formObj->getTBRow('Address 2', 'shipping_address_street', $rowPo['shipping_address_street'])}
                {$formObj->getTBRow('Country', 'shipping_address_country', $rowPo['shipping_address_country'])}
                {$formObj->getTBRow('Postal Code', 'shipping_address_po_code', $rowPo['shipping_address_po_code'])}
            ";
        }

        $text = "
        <form id='editForPoForm' class='yform columnar' method='post' action='{$formActionEditForPo}'>
            <fieldset>
                {$shipping_address_fields}
                {$formObj->getTBRow('Supplier Ref', 'supplier_reference_no', $rowPo['supplier_reference_no'])}
                {$formObj->getTBRow('Our Ref', 'our_reference_no', $rowPo['our_reference_no'])}
                {$formObj->getDateRow('PO Date', 'po_date', $rowPo['po_date'])}
                {$formObj->getTBRow('Shipping method', 'shipping_method', $rowPo['shipping_method'])}
                {$formObj->getTextAreaRow('Payment Terms', 'payment_terms', $rowPo['payment_terms'])}
                {$formObj->getDateRow('Required by Date', 'delivery_date', $rowPo['delivery_date'])}
                {$formObj->getTextAreaRow('Delivery Terms', 'delivery_terms',$rowPo['delivery_terms'])}
                <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }
}