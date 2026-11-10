<?
class CP_Admin_Modules_Project_Project_View extends CP_Common_Lib_ModuleViewAbstract
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
            $percentage_used = $row['percentage_used'];

            if ($percentage_used >= 100) {
                $listRowHeader = $listObj->getListRowHeader($row, $rowCounter, 'projectList2');
            } else if ($percentage_used > 80) {
                $listRowHeader = $listObj->getListRowHeader($row, $rowCounter, 'projectList1');
            } else {
                $listRowHeader = $listObj->getListRowHeader($row, $rowCounter);
            }

            $branch = '';
            if ($cpCfg['m.project.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $currency = '';
            if ($cpCfg['m.project.hasMultiCurrency'] == 1){
                $currency = $row['currency'];
            }

            $stage = '';
            if ($cpCfg['m.project.project.showStage'] == 1){
                $stage = $listObj->getListDataCell($row['stage']);
            }

            $rows .= "
            {$listRowHeader}
            {$listObj->getGoToDetailText($rowCounter, $row['project_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['per_completed'], 'center')}
            {$listObj->getListDataCell(number_format($row['still_to_bill']), 'right', '', 60)}
            {$listObj->getListDataCell($row['used_hours'], 'right', '', 65)}
            {$branch}
            {$stage}
            {$listObj->getListDataCell($editText)}
            {$listObj->getListRowEnd($row['project_id'])}
            ";

            $rowCounter++;
        }

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'branch_name');
        }

        $stage = '';
        if ($cpCfg['m.project.project.showStage'] == 1){
            $stage = $listObj->getListHeaderCell('Stage', 'p.statge');
        }

        $countText = "
        <div class='row moduleWidgetsDisplayRow'> 
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-primary'> 
                    <div class='widget-bg-icon'> 
                        <i class='fa fa-bookmark-o'></i> 
                    </div> 
                    <div class='widget-details'> 
                        <span class='block h4 mt0 mb5'>    </span> 
                        <span>     </span> 
                    </div> 
                </div> 
            </div>
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-info'>
                    <div class='widget-bg-icon'> 
                        <i class='fa fa-tag'></i> 
                    </div> 
                    <div class='widget-details'>
                        <span class='block h4 mt0 mb5'>   </span> 
                        <span>    </span> 
                    </div> 
                </div> 
            </div> 
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-cyan'> 
                    <div class='widget-bg-icon'> 
                        <i class='fa fa-lemon-o'></i> 
                    </div> 
                    <div class='widget-details'> 
                        <span class='block h4 mt0 mb5'>   </span> 
                        <span>   </span> 
                    </div> 
                </div> 
            </div> 
        </div>
        ";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.project_code', 'w50')}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
        {$listObj->getListHeaderCell('%', 'p.per_completed', 'w20 headerCenter')}
        {$listObj->getListHeaderCell('Still to bill', 'still_to_bill desc', 'headerRight')}
        {$listObj->getListHeaderCell('Man Hours', 'used_hours', 'headerCenter')}
        {$branch}
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
        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            $fld_suffix = '_base';
        }

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';
        $modProject = getCPModuleObj('project_project');
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
        $modProject = getCPModuleObj('project_project');
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
                    <td colspan='11'></td>
                    <td class='txtRight'>{$total1}</td>
                    <td class='txtRight'>{$total2}</td>
                    <td colspan='8'></td>
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

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch);
        }

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title')}
        {$branch}
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
		$productList  = '';
        $paymentTerms = '';

        $showSensitiveDetails = $fn->getSessionParam('showSensitiveDetails');

        $sqlProduct = $fn->getDDSql('webBasic_product');


        if ($cpCfg['m.project.hasQuotingModule'] == 1) {
            if ($row['opportunity_id'] > 0) {
                $totalQuotes = $fn->getRecordCount('quote', "opportunity_id = {$row['opportunity_id']}");
            }
        }

        $expCode = array('isEditable' => 0);

        if ($row['opportunity_id'] > 0) {
            $oppLink   = "index.php?_topRm={$tv['topRm']}&module=project_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail";
            $linkToOpp = "<a href='{$oppLink}'>{$row['opportunity_code']}</a>";
            $quoteRef  = $formObj->getTBRow('Opportunity Ref#', 'quote_ref', $linkToOpp, $expCode);

        } else {
            $quoteRef = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        if ($cpCfg['m.project.project.showInvoiceRef'] == 1) {
            $invoiceRef = "
            {$formObj->getTBRow('Deposit Inv Ref#', 'deposit_inv_ref', $row['deposit_inv_ref'])}
            {$formObj->getTBRow('Invoice Ref#', 'invoice', $row['invoice'])}
            ";
        }

        if ($cpCfg['m.project.project.showPaymentTerms'] == 1 && $cpCfg['m.project.hasQuotingModule'] == 0) {
            $paymentTerms = $formObj->getTBRow('Payment Terms', 'payment_terms', $row['payment_terms']);
        }

        //--------------------------------------------------------------------------//
        $sqlComp = $fn->getDDSql('project_company', array('condn' => "category = 'client'"));

        $append = ($row['company_id'] > 0) ? "AND company_id = {$row['company_id']}" : '';
        $sqlCont = $fn->getDDSql('project_contact', array('condn' => "CONCAT_WS('', first_name, last_name) != '' {$append}"));
        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        //--------------------------------------------------------------------------//
        $expCode = array('isEditable' => $cpCfg['m.project.project.codeEditable']);
        $expVl   = array('sqlType' => 'OneField');

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlPerc   = $fn->getValueListSQL('percentCompleted');
        $sqlStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat     = $fn->getValueListSQL('projectCategory');

        $contact  = "<a href='index.php?_topRm=project&module=project_contact&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
        $company  = "<a href='index.php?_topRm=project&module=project_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

        $compLink = '';
        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('project_project', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $company);

        $contLink = '';
        if ($formObj->mode == 'edit'){
            $contLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('project_project', 'project_contactLink', 'fld_contact_id')}'>Choose</a>";
        }
        $expCont  = array('notesRight' => $contLink, 'detailValue' => $contact);

        $expPM = array('detailValue' => $row['project_manager_name']);

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => $row['branch_name']);
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, $row['branch_id'], $expBranch);
        }

        $stage = '';
        if ($cpCfg['m.project.project.showStage'] == 1){
            $sqlStage = $fn->getValueListSQL('projectStage');
            $stage = "
            {$formObj->getDDRowBySQL('Stage', 'stage', $sqlStage, $row['stage'], $expVl)}
            ";
        }

        $secretarial = ''; // For Nahvibiz CRM
        if ($cpCfg['m.project.project.showSecretarialDetails'] == 1) {
            $secretarialDetails = "
            {$formObj->getDateRow('Date of Incorporation', 'date_of_incorporation', $row['date_of_incorporation'])}
            {$formObj->getDateRow('Last AGM Date', 'last_agm_date', $row['last_agm_date'])}
            {$formObj->getDateRow('Last AR Date', 'last_ar_date', $row['last_ar_date'])}
            {$formObj->getDateRow('Date of Accounts Laid', 'date_of_accounts_laid', $row['date_of_accounts_laid'])}
            {$formObj->getDateRow('Next AGM Due Date', 'next_agm_due_date', $row['next_agm_due_date'])}
            ";

            $secretarial = $formObj->getFieldSetWrapped('Secretarial', $secretarialDetails);
        } 

        $tax = '';  // For Nahvibiz CRM
        if ($cpCfg['m.project.project.showTaxDetails'] == 1) {
            $taxDetails = "
            {$formObj->getDateRow('Form C Filed Date', 'form_c_filed_date', $row['form_c_filed_date'])}
            {$formObj->getDateRow('Form CS', 'form_cs', $row['form_cs'])}
            {$formObj->getDateRow('ECI', 'eci', $row['eci'])}
            ";

            $tax = $formObj->getFieldSetWrapped('Tax', $taxDetails);
        }
                
        //--------------------------------------------------------------------------//
        $fieldset1 = "
        {$formObj->getTBRow('Code', 'project_code', $row['project_code'], $expCode)}
        {$formObj->getTBRow('Project Name', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $expVl)}
        {$productList}
        {$quoteRef}
        {$invoiceRef}
        {$paymentTerms}        
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$branch}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$stage}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Client type', 'client_type', $sqlType, $row['client_type'], $expVl)}
        {$formObj->getDDRowBySQL('Difficulty', 'difficulty', $sqlDiff, $row['difficulty'], $expVl)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('Estimated Finish Date', 'estimated_finish_date', $row['estimated_finish_date'])}
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
        {$secretarial}
        {$formObj->getFieldSetWrapped('Values', $fieldset3)}
        {$tax}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $searchVar = Zend_Registry::get('searchVar');

        $project_value = '';
        $project_commission = '';
        $targetLeft = '';
        $currency   = '';
        $base_value = '';
        $ref_value  = '';

        $totalQuotes = 0;
        $expNoEdit = array('isEditable' => 0, 'autoFormat' => 1);
        $expNum    = array('autoFormat' => 1);

        if ($row == "") {
            $SQL = $sqlMaster->getSQL('project_project');
            $SQL .= $searchVar->getSearchVar('project_project');
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
        }

        if ($cpCfg['m.project.hasQuotingModule'] == 1) {
            if ($row['opportunity_id'] > 0) {
                $totalQuotes = $fn->getRecordCount('quote', "opportunity_id = {$row['opportunity_id']}");
            }
        }

        if ($totalQuotes > 0) {
            $project_value = $formObj->getTBRow('Project Value$', 'project_value', $row['project_value'], $expNoEdit);
        } else {
            $project_value = $formObj->getTBRow('Project Value$', 'project_value', $row['project_value'], $expNum);
        }

        if ($fn->getSessionParam('showSensitiveDetails') == 1){
            $project_commission = $formObj->getTBRow($cpCfg['m.project.project.commissionLabel'], 'project_commission', $row['project_commission'], $expNum);
        }

        if ($dbUtil->getColumnExists('project', 'target_left')) {
            $targetLeft = $formObj->getTBRow('Target $ Left', 'target_left', $row['target_left'], $expNum);
        }

        $budgeted_left = $row['project_value'] - $row['budget_third_party'] - $row['used_inhouse'];
        $actual_left   = $row['project_value'] - $row['used_inhouse'] - $row['used_third_party'];

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            $sqlStatus = $fn->getValueListSQL('currency');
            $expVl = array('sqlType' => 'OneField');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlStatus, $row['currency'], $expVl);
            $base_value = $formObj->getTBRow("Base Project Value ({$cpCfg['m.project.baseCurrency']})", 'project_value_base', $row['project_value_base'], $expNum);
        }

        if ($cpCfg['m.project.project.showRefValue'] == 1){
            $ref_value = $formObj->getTBRow("Reference Value ({$cpCfg['m.project.refCurrency']})", 'project_value_ref', $row['project_value_ref'], $expNum);
        }

        $text = "
        {$currency}
        {$project_value}
        {$base_value}
        {$ref_value}
        {$formObj->getTBRow('Still to Bill$', 'still_to_bill', number_format($row['still_to_bill']), $expNoEdit)}
        {$formObj->getTBRow('Budgeted in house$', 'budget_inhouse', $row['budget_inhouse'], $expNoEdit)}
        {$formObj->getTBRow('Used in house$', 'used_inhouse', $row['used_inhouse'], $expNoEdit)}
        {$formObj->getTBRow('Budgeted 3rd Parties$', 'budget_third_party', $row['budget_third_party'], $expNoEdit)}
        {$formObj->getTBRow('Used 3rd Parties$', 'used_third_party', $row['used_third_party'], $expNoEdit)}
        {$project_commission}
        {$targetLeft}
        {$formObj->getTBRow('Budgeted Left$', 'budgeted_left', $budgeted_left, $expNoEdit)}
        {$formObj->getTBRow('Actual Left$', 'actual_left', $actual_left, $expNoEdit)}
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

        $sqlComp    = $fn->getDDSql('project_company');
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

        $text = "";

        $rightLinkInv = "<a id='raiseInvoice' class='actionButtons' href=\"javascript:Invoice.raiseInvoice('project')\">Raise Invoice</a>";

        $quotesPortal = '';
        if ($cpCfg['m.project.hasQuotingModule'] == 1 && ($tv['action'] == "edit" || $tv['action'] == "detail")) {
            $quote = getCPModuleObj('project_quote', true);

            $quotesPortal = "
            <div id='quotesOuter'>
                {$quote->getQuotesPortal($row['project_id'], 'proj')}
            </div>
            <input type='hidden' id='project_id' value='{$row['project_id']}' />
            <input type='hidden' id='section_name' value='{$tv['module']}' />
            ";

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array('cpm.project.quote.init()'));
        }

        $costing = '';
        if ($cpCfg['m.project.project.showCostingTable'] == 1){
            $exportUrl = "index.php?_topRm=project&module=project_costing&showHTML=0&_spAction=exportCosting&project_id={$row['project_id']}";
            $rightLink = "<a class='actionButtons' href='{$exportUrl}'>Export to Excel</a>&nbsp;";
            $costing = $displayLinkData->getLinkPortalMain('project_project', 'project_costingLink', 'Costing Table', $row, '', $rightLink);
        }

        $record_id = $fn->getIssetParam($row, 'project_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachment', 'project_project', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('project_project', 'core_staffLink', $cpCfg['m.project.staffFieldLabel'], $row)}
        {$displayLinkData->getLinkPortalMain('project_project', 'project_taskLink', 'Tasks', $row)}
        {$displayLinkData->getLinkPortalMain('project_project', 'project_scheduleLink', 'Project Schedule', $row)}
        {$displayLinkData->getLinkPortalMain('project_project', 'project_invoiceLink', 'Invoices', $row, '', $rightLinkInv)}
        {$costing}
        {$quotesPortal}
        {$displayLinkData->getLinkPortalMain('project_project', 'project_thirdPartyCostLink', 'Additional Third Party Costs', $row)}
        {$comment->getView(array(
             'roomName' => 'project_project'
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
            if ($cpCfg['m.project.project.showTaskSummaryReport'] == 1){
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
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $status             = $fn->getReqParam('status');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN project b ON (a.company_id = b.company_id)
        WHERE a.category = 'Client'
        ORDER BY company_name
        ";

        $SQLPM      = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        $SQLStf     = $fn->getDDSql('core_staff', array('condn' => "status = 'Current' AND team = 'In-house'"));
        $SQLStatus  = $fn->getValueListSQL('projectStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
           ,"Overrun"
        );

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $branch_id = $fn->getReqParam('branch_id');
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = "
            <td>
                <select name='branch_id'>
                    <option value=''>Branch</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $branch_id)}
                </select>
            </td>
            ";
        }

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
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>

        <td>
            <select name='project_manager_id'>
                <option value=''>Project manager</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLPM, $project_manager_id)}
            </select>
        </td>

        <td>
            <select name='staff_id'>
                <option value=''>{$cpCfg['m.project.staffFieldLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
            </select>
        </td>

        {$branch}

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
}