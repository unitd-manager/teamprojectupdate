<?
class CP_Admin_Modules_ManPower_Project_View extends CP_Common_Lib_ModuleViewAbstract
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
        $db = Zend_Registry::get('db');
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

            $no_of_hours = '';
            $SQLHours = "
            SELECT order_id
                   FROM `project` p
            LEFT JOIN `order` o ON (o.project_id = p.project_id)
            WHERE p.project_id = {$row['project_id']}
            ";
            $resultHours = $db->sql_query($SQLHours);
            $rowHours    = $db->sql_fetchrow($resultHours);

            if($rowHours['order_id'] != ''){
                $sqlClient = "SELECT SUM(no_of_hours) AS no_of_hours
                              FROM `invoice`
                              WHERE invoice_type = 'Candidate'
                              AND order_id = {$rowHours['order_id']}
                              AND status != 'Cancelled'
                ";
                $resultClient = $db->sql_query($sqlClient);
                $rowClient    = $db->sql_fetchrow($resultClient);

                $no_of_hours = $rowClient['no_of_hours'];
            }

            $branch = '';
            if ($cpCfg['m.manPower.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $currency = '';
            if ($cpCfg['m.manPower.hasMultiCurrency'] == 1){
                $currency = $row['currency'];
            }

            

            //{$listObj->getListDataCell($currency . number_format($cpCfg['m.manPower.project.valueField']), 'right', '', 70)}

            $rows .= "
            {$listRowHeader}
            {$listObj->getGoToDetailText($rowCounter, $row['project_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['candidate_name'])}
            {$listObj->getListDateCell($row['start_date'], 'left', '', 80)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($no_of_hours, 'right', '', 65)}
            {$branch}
            {$listObj->getListDataCell($row['modified_by'].' '.$row['modification_date'])}
            {$listObj->getListRowEnd($row['project_id'])}
            ";

            $rowCounter++;
        }

        $branch = '';
        if ($cpCfg['m.manPower.project.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'branch_name');
        }

        $stage = '';
        if ($cpCfg['m.manPower.project.showStage'] == 1){
            $stage = $listObj->getListHeaderCell('Stage', 'p.statge');
        }

        //{$listObj->getListHeaderCell('Value($)', "p.{$cpCfg['m.manPower.project.valueField']}", 'headerRight')}

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.project_code', 'w50')}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Position', 'position')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Candidate Name', '')}
        {$listObj->getListHeaderCell('Start Date', 'p.start_date')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
        {$listObj->getListHeaderCell('Man Hours', 'used_hours', 'headerlistManHours')}
        {$branch}
        {$listObj->getListHeaderCell('Updated By', '' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    /*function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');

        $searchVar->sqlSearchVar = array();

        $fld_suffix = '';
        if ($cpCfg['m.manPower.project.hasMultiCurrency'] == 1){
            $fld_suffix = '_base';
        }

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';
        $SQLSum  = $this->model->getProjectValueSumSQL('project_value');

        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $SQLSum .= "
        AND LOWER(p.status) != 'lost'
        AND LOWER(p.status) != 'cancelled'
        ";
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total1 = $row[0];

        $searchVar->sqlSearchVar = array();
        $SQLSum  = $this->model->getProjectValueSumSQL('still_to_bill');

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
    }*/

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

        $branch = '';
        if ($cpCfg['m.manPower.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch);
        }

        $fielset1 = "
        {$formObj->getTBRow('Project Title', 'title')}
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
        $paymentTerms = '';

        $showSensitiveDetails = $fn->getSessionParam('showSensitiveDetails');

        if ($cpCfg['m.manPower.hasQuotingModule'] == 1) {
            if ($row['opportunity_id'] > 0) {
                $totalQuotes = $fn->getRecordCount('quote', "opportunity_id = {$row['opportunity_id']}");
            }
        }

        $expOppCode = array('isEditable' => 0);

        if ($row['opportunity_id'] > 0) {
            $oppLink   = "index.php?_topRm=opportunity&module=manPower_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail";
            //index.php?_topRm=opportunity&module=manPower_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail
            $linkToOpp = "<a href='{$oppLink}' target='_blank'><u>{$row['opportunity_code']}</u></a>";
            $quoteRef  = $formObj->getTBRow('Opportunity Ref#', 'quote_ref', $linkToOpp, $expOppCode);

        } else {
            $quoteRef = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        if ($cpCfg['m.manPower.project.showInvoiceRef'] == 1) {
            $invoiceRef = "
            {$formObj->getTBRow('Deposit Inv Ref#', 'deposit_inv_ref', $row['deposit_inv_ref'])}
            {$formObj->getTBRow('Invoice Ref#', 'invoice', $row['invoice'])}
            ";
        }

        if ($cpCfg['m.manPower.project.showPaymentTerms'] == 1 && $cpCfg['m.manPower.hasQuotingModule'] == 0) {
            $paymentTerms = $formObj->getTBRow('Payment Terms', 'payment_terms', $row['payment_terms']);
        }

        //--------------------------------------------------------------------------//
        $sqlComp = $fn->getDDSql('manPower_company', array('condn' => "category = 'client'"));

        $append = ($row['company_id'] > 0) ? "AND company_id = {$row['company_id']}" : '';
        $sqlCont = $fn->getDDSql('manPower_candidate', array('condn' => "CONCAT_WS('', first_name, last_name) != '' {$append}"));
        $sqlPM = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        //--------------------------------------------------------------------------//
        $expCode = array('isEditable' => $cpCfg['m.manPower.project.codeEditable']);
        $expVl   = array('sqlType' => 'OneField');
        $expNotEditable = array('isEditable' => 0);
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
        $staffRec = $fn->getRecordRowByID('staff', 'staff_id', $row['staff_id']);

        $sqlType            = $fn->getValueListSQL('clientType');
        $sqlDiff            = $fn->getValueListSQL('projectDifficulty');
        $sqlCat             = $fn->getValueListSQL('projectCategory');
        $sqlPerc            = $fn->getValueListSQL('percentCompleted');
        $sqlStatus          = $fn->getValueListSQL('projectStatus');
        $sqlPosition_type   = $fn->getValueListSQL('opportunityPositionType');

        $contact  = "<a href='index.php?_topRm=manPower&module=manPower_candidate&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
        $company  = "<a href='index.php?_topRm=manPower&module=project_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

        $compLink = '';
        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('project_project', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        //$expComp  = array('notesRight' => $compLink, 'detailValue' => $company);
        $expComp  = array('isEditable' => 0);

        $contLink = '';
        if ($formObj->mode == 'edit'){
            $contLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('project_project', 'project_contactLink', 'fld_contact_id')}'>Choose</a>";
        }
        $expCont  = array('notesRight' => $contLink, 'detailValue' => $contact);

        $expPM = array('detailValue' => $row['project_manager_name']);

        $branch = '';
        if ($cpCfg['m.manPower.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => $row['branch_name']);
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, $row['branch_id'], $expBranch);
        }

        $position = "
            <div class='positionTitle'>
             {$formObj->getTBRow('Position', 'position', $row['position'], $expNotEditable)}
            </div>
            ";

        $stage = '';
        if ($cpCfg['m.manPower.project.showStage'] == 1){
            $sqlStage = $fn->getValueListSQL('projectStage');
            $stage = "
            {$formObj->getDDRowBySQL('Stage', 'stage', $sqlStage, $row['stage'], $expVl)}
            ";
        }

        $currency = '';
        if ($cpCfg['m.manPower.hasMultiCurrency'] == 1){
            $sqlCurrency = $fn->getValueListSQL('currency');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], $expVl);
        }
        //--------------------------------------------------------------------------//
        /*$fieldset1 = "
        {$formObj->getTBRow('Code', 'project_code', $row['project_code'], $expNotEditable)}
        {$position}
        {$formObj->getTBRow('Position Type', 'position_type', $row['position_type'], $expNotEditable)}
        {$formObj->getTBRow('Project Name', 'title', $row['title'])}
        {$quoteRef}
        {$formObj->getTBRow('Client Company', 'company_name', $row['company_name'], $expNotEditable)}
        {$formObj->getTBRow('Contact', 'contact_id', $contactRec['name'], $expNotEditable)}
        {$formObj->getTBRow('Staff', 'staff_id', $staffRec['first_name'] . ' ' . $staffRec['last_name'], $expNotEditable)}
        {$branch}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$stage}
        {$formObj->getTBRow('Client Hourly Rate', 'client_hourly_rate', $row['client_hourly_rate'])}
        {$formObj->getTBRow('Candidate Hourly Rate', 'candidate_hourly_rate', $row['candidate_hourly_rate'])}
        ";*/
        $contact_name = $contactRec['first_name'].' '.$contactRec['last_name'];

        if($row['position_type'] == 'Full Time'){
            $work_state = "
            {$formObj->getDDRowByArr('Work State *', 'work_state', $cpCfg['m.manPower.project.stateListArr'], $row['work_state'])}
            ";
        }else{
            $work_state = "
            {$formObj->getDDRowByArr('Work State', 'work_state', $cpCfg['m.manPower.project.stateListArr'], $row['work_state'])}
            ";
        }

        $fieldset1 = "
        {$formObj->getTBRow('Code', 'project_code', $row['project_code'], $expNotEditable)}
        {$quoteRef}
        {$position}
        {$formObj->getDDRowBySQL('Position Type', 'position_type', $sqlPosition_type, $row['position_type'], $expVl)}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$work_state}
        {$formObj->getTBRow('Client Company', 'company_name', $row['company_name'], $expNotEditable)}
        {$formObj->getTBRow('Contact', 'contact_id', $contact_name, $expNotEditable)}
        {$formObj->getDDRowBySQL('Project Category', 'category', $sqlCat, $row['category'], $expVl)}
        {$formObj->getTBRow('Staff', 'staff_id', $staffRec['first_name'] . ' ' . $staffRec['last_name'], $expNotEditable)}
        {$branch}
        ";

        /*$fieldset2 = "
        {$formObj->getDDRowBySQL('Difficulty', 'difficulty', $sqlDiff, $row['difficulty'], $expVl)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getDateRow('Estimated Finish Date', 'estimated_finish_date', $row['estimated_finish_date'])}
        {$formObj->getDateRow('Actual Finish Date', 'actual_finish_date', $row['actual_finish_date'])}
        {$formObj->getDDRowBySQL('Percentage Completed', 'per_completed', $sqlPerc, $row['per_completed'], $expVl)}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getDDRowBySQL('Project Category', 'category', $sqlCat, $row['category'], $expVl)}
        ";*/

        $sqlCompany = "
            SELECT company_id
                   ,company_name AS title
            FROM company
            WHERE company_type = 'Referral'
            ";

        $expComp  = array();

        if($row['apply_commission'] == 0){
            $commission_display="
            <div class = 'chequeNoDisplay'>
                {$formObj->getDDRowBySQL('Company Name', 'referral_id', $sqlCompany, $row['referral_id'],$expComp)}
                {$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage', $row['commission_percentage'])}
            </div>
            ";
        }else{
            $commission_display="
            <div>
                {$formObj->getDDRowBySQL('Company Name', 'referral_id', $sqlCompany, $row['referral_id'],$expComp)}
                {$formObj->getTBRow('Commission Percentage (%)', 'commission_percentage', $row['commission_percentage'])}
            </div>
            ";
        }

        $fieldset2 = "
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('Actual Finish Date', 'estimated_finish_date', $row['estimated_finish_date'])}
        {$formObj->getDDRowBySQL('Percentage Completed', 'per_completed', $sqlPerc, $row['per_completed'], $expVl)}
        ";
        //{$formObj->getYesNoRRow('Apply commission', 'apply_commission', $row['apply_commission'])}
        $fieldset3 = "
        {$formObj->getTBRow('Client Hourly Rate', 'client_hourly_rate', $row['client_hourly_rate'])}
        {$formObj->getTBRow('Candidate Hourly Rate', 'candidate_hourly_rate', $row['candidate_hourly_rate'])}
        {$formObj->getYesNoRRow('Apply commission', 'apply_commission', $row['apply_commission'])}
        {$commission_display}
        {$currency}
        <div id='projectValues'>
            {$this->getProjectValuesTable($row)}
        </div>
        ";

        $fieldset4 = "
        {$formObj->getHTMLEditor('Description', 'description', $row['description'], '0')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Costs', $fieldset3)}
        {$formObj->getFieldSetWrapped('Project Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Description', $fieldset4)}
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
    function getCommissionPercent() {
       $fn  = Zend_Registry::get('fn');

       $company_id = $fn->getReqParam('company_id');
       $recCommission = $fn->getRecordRowByID('company', 'company_id', $company_id);

       $commission_percent = $recCommission['commission_percentage'];

       return $commission_percent;
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
            $SQL = $sqlMaster->getSQL('project');
            $SQL .= $searchVar->getSearchVar('project');
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
        }

        if ($cpCfg['m.manPower.hasQuotingModule'] == 1) {
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

        if ($cpCfg['m.manPower.project.hasMultiCurrency'] == 1){
            $sqlStatus = $fn->getValueListSQL('currency');
            $expVl = array('sqlType' => 'OneField');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlStatus, $row['currency'], $expVl);
            $base_value = $formObj->getTBRow("Base Project Value ({$cpCfg['m.manPower.baseCurrency']})", 'project_value_base', $row['project_value_base'], $expNum);
        }

        if ($cpCfg['m.manPower.project.showRefValue'] == 1){
            $ref_value = $formObj->getTBRow("Reference Value ({$cpCfg['m.manPower.refCurrency']})", 'project_value_ref', $row['project_value_ref'], $expNum);
        }

        /*{$currency}
        {$project_value}
        {$base_value}
        {$ref_value}
        {$formObj->getTBRow('Budgeted in house$', 'budget_inhouse', $row['budget_inhouse'], $expNoEdit)}
        {$formObj->getTBRow('Used in house$', 'used_inhouse', $row['used_inhouse'], $expNoEdit)}
        {$formObj->getTBRow('Budgeted 3rd Parties$', 'budget_third_party', $row['budget_third_party'], $expNoEdit)}
        {$formObj->getTBRow('Used 3rd Parties$', 'used_third_party', $row['used_third_party'], $expNoEdit)}
        {$project_commission}
        {$targetLeft}
        {$formObj->getTBRow('Budgeted Left$', 'budgeted_left', $budgeted_left, $expNoEdit)}
        {$formObj->getTBRow('Actual Left$', 'actual_left', $actual_left, $expNoEdit)}
        */

        /*$text = "
        {$project_value}
        {$formObj->getTBRow('Still to Bill$', 'still_to_bill', number_format($row['still_to_bill']), $expNoEdit)}
        ";*/

        $text = "
        {$project_value}
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

        $sqlStaff = $fn->getDDSql('manPower_staff', array('condn' => "status = 'Current'"));
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
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $pager = Zend_Registry::get('pager');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $text = "";
        $attachment = '';
        $goToOrder  = '';

        $rightLinkInv = "<a id='raiseInvoice' class='actionButtons' href=\"javascript:Invoice.raiseInvoice('project')\">Raise Invoice</a>";

    	$generateFinanceLink = "<a href='#' id='generateOrderRecord' project_id='{$row['project_id']}' client_hourly_rate='{$row['client_hourly_rate']}' candidate_hourly_rate='{$row['candidate_hourly_rate']}'>Generate Finance Records</a>";

        $invoice ="
        <div class='float_left button mb10'>
            {$generateFinanceLink}
        </div>
        ";

        $SQLOrderLink = "
        SELECT order_id
               FROM `project` p
        LEFT JOIN `order` o ON (o.project_id = p.project_id)
        WHERE p.project_id = {$row['project_id']}
        ";
        $resultOrderLink = $db->sql_query($SQLOrderLink);
        $rowOrderLink    = $db->sql_fetchrow($resultOrderLink);

        if($rowOrderLink['order_id'] != ''){
            $orderLink = "<a href='index.php?_topRm=finance&module=manPower_order&order_id={$rowOrderLink['order_id']}&_action=edit'>Go To Finance</a>";
            $goToOrder ="
            <div class='float_left button mb10'>
                {$orderLink}
            </div>
            ";

            $generateFinanceLink = "<a href='#' id='generateOrderRecord' project_id='{$row['project_id']}' client_hourly_rate='{$row['client_hourly_rate']}' candidate_hourly_rate='{$row['candidate_hourly_rate']}'>Update Finance Records</a>";
            $invoice ="
            <div class='float_left button mb10'>
                {$generateFinanceLink}
            </div>
            ";
        }

        $quotesPortal = '';
        if ($cpCfg['m.manPower.hasQuotingModule'] == 1 && ($tv['action'] == "edit" || $tv['action'] == "detail")) {
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
        if ($cpCfg['m.manPower.project.showCostingTable'] == 1){
            $exportUrl = "index.php?_topRm=manPower&module=project_costing&showHTML=0&_spAction=exportCosting&project_id={$row['project_id']}";
            $rightLink = "<a class='actionButtons' href='{$exportUrl}'>Export to Excel</a>&nbsp;";
            $costing = $displayLinkData->getLinkPortalMain('manPower_project', 'project_costingLink', 'Costing Table', $row, '', $rightLink);
        }

        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){
            $attachment ="
            {$media->getRightPanelMediaDisplay('Card Collection Form', 'manPower_project', 'attachment1', $row)}
            {$media->getRightPanelMediaDisplay('No Due Letter', 'manPower_project', 'attachment2', $row)}
            {$media->getRightPanelMediaDisplay('Resignation Letter', 'manPower_project', 'attachment3', $row)}
            {$media->getRightPanelMediaDisplay('Cancel Letter', 'manPower_project', 'attachment4', $row)}
            {$media->getRightPanelMediaDisplay('Employer Authorisation', 'manPower_project', 'attachment5', $row)}
            ";
        }

        $Exhibit_PO_Doc ="
            <div class='floatbox actionBtnsDetail'>
                <div class='orderbtnbackground floatbox'>
                    {$invoice}
                    <div class='float_left button mb10'>
                        <a href='/admin/lib/template/ExhibitPODOC.doc' id='exhibitPoDoc'>Exhibit PO Doc</a>
                    </div>
                    {$goToOrder}
                </div>
            </div>
            ";

        $record_id = $fn->getIssetParam($row, 'project_id');

        /*{$displayLinkData->getLinkPortalMain('manPower_project', 'project_scheduleLink', 'Project Schedule', $row)}
        {$displayLinkData->getLinkPortalMain('manPower_project', 'project_thirdPartyCostLink', 'Additional Third Party Costs', $row)}
        */
        //{$displayLinkData->getLinkPortalMain('manPower_project', 'manPower_taskLink', 'Tasks', $row)}
        //{$displayLinkData->getLinkPortalMain('manPower_project', 'manPower_candidateLink', 'Candidate Linked', $row)}

        $text = "
        {$Exhibit_PO_Doc}
        {$this->getProjectCandidateDisplay($row['project_id'])}
        {$media->getRightPanelMediaDisplay('Attachment', 'manPower_project', 'attachment', $row)}
        {$attachment}
        {$displayLinkData->getLinkPortalMain('manPower_project', 'core_staffLink', 'Staff Linked', $row)}
        {$displayLinkData->getLinkPortalMain('manPower_project', 'manPower_taskLink', 'Tasks', $row)}
        {$displayLinkData->getLinkPortalMain('manPower_project', 'manPower_invoiceLink', 'Invoices', $row, '', $rightLinkInv)}
        {$displayLinkData->getLinkPortalMain('manPower_project', 'manPower_expenseLink', 'Expense Linked', $row)}
        {$costing}
        {$quotesPortal}
        {$comment->getView(array(
             'roomName' => 'manPower_project'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getProjectCandidateDisplay($project_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows ='';

        $recCount = $fn->getRecordCount('project', "project_id = '{$project_id}'");

        $SQL = "
            SELECT p.project_id
                  ,p.candidate_id
                  ,CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
                  ,c.agent_id
            FROM project p
            LEFT JOIN candidate c ON (c.candidate_id = p.candidate_id)
            WHERE p.project_id = '{$project_id}'
            ";

        $result   = $db->sql_query($SQL);
        $count = 1;

        while ($rowCandidate = $db->sql_fetchrow($result)) {
            $candidate_name = "<a href='index.php?_topRm=opportunity&module=manPower_candidate&_action=detail&record_id={$rowCandidate['candidate_id']}'>{$rowCandidate['candidate_name']}</a>";

            $agent_name   = '';
            $agent_header = '';
            $agentTd      = '';

            if ($rowCandidate['agent_id']!=''){
                $SQLAgent = "
                SELECT CONCAT_WS(' ',first_name ,last_name) AS agent_name
                       ,agent_id
                FROM agent WHERE agent_id = {$rowCandidate['agent_id']}
                ";
                $resultAgent  = $db->sql_query($SQLAgent);
                $rowAgent     = $db->sql_fetchrow($resultAgent);

                $agent_name   = "<a href='index.php?_topRm=opportunity&module=manPower_agent&_action=detail&record_id={$rowAgent['agent_id']}'>{$rowAgent['agent_name']}</a>";
                $agent_header = "<th>subcontractor Name</th>";
                $agentTd      = "<td>{$agent_name}</td>";
            }
                $rows .= "
                    <tr>
                        <td>{$count}</td>
                        <td>{$candidate_name}</td>
                        {$agentTd}
                    </tr>
                    ";

                $count++;
        }

        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Candidate name</th>
            {$agent_header}
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
            $rows .= "
                <tr>
                    <td class='noCandidate'>No Records Linked</td>
                </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper manPower_project__manPower_candidateLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Candidate Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='candidatelist'>
                        {$header}
                        <tbody>
                            {$rows}
                        </tbody>
                    </table>
                    <input type='hidden' name='project_id' value='{$project_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     *
     */
        /*function getExhibitPO() {
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

        $batch_id  = $fn->getReqParam('id');
        $template = 'Attendance1.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Attendance_' . $batch_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT b.*
              ,c.title AS course_title
              ,c.course_code
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS student_name
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,cont.phone
              ,cont.registration_no
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN course_contact cc ON (cc.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (cc.contact_id = cont.contact_id)
        LEFT JOIN teacher t ON (b.teacher_id = t.teacher_id)
        WHERE b.batch_id = {$batch_id}
        ORDER BY cont.registration_no
        ";
        $result = $db->sql_query($SQL);

        $serialNo    = 1;
        $arr         = array();
        $blkMain     = array();
        $blkStd      = array();
        $blkPhone    = array();
        $blkRegNo    = array();
        $blkSerialNo = array();

        while ($row = $db->sql_fetchrow($result)) {
            $arr1 = array('student_name' => $row['student_name']);
            $blkStd[] = $arr1;

            $arr2 = array('registration_no' => $row['registration_no']);
            $blkRegNo[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('phone' => $row['phone']);
            $blkPhone[] = $arr4;

            $arr['course_code']  = $row['course_code'];
            $arr['teacher_name'] = $row['teacher_name'];
            $arr['start_date']   = $row['start_date'];
            $arr['end_date']     = $row['end_date'];
            $arr['course_title'] = $row['course_title'];
            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkStd', $blkStd);
        $TBS->MergeBlock('blkRegNo', $blkRegNo);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('blkPhone', $blkPhone);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }*/

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
        $position             = $fn->getReqParam('position');

        $sqlPosition = $fn->getValueListSQL('opportunityPosition');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN project b ON (a.company_id = b.company_id)
        ORDER BY company_name
        ";

        $appendStaffSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStaffSql = " AND site_id = '{$_SESSION['cp_site_id']}'";
        }

        $SQLPM      = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        $SQLStf     = $fn->getDDSql('manPower_staff', array('condn' => "status = 'Current' AND staff_login_type = 'Staff' {$appendStaffSql}"));
        $SQLStatus  = $fn->getValueListSQL('projectStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
           ,"Overrun"
        );

        $branch = '';
        if ($cpCfg['m.manPower.project.hasMultiBranches'] == 1){
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

        $company = '';
        $pm = '';
        $staff = '';

        if ($_SESSION['userGroupType'] == 'Super Administrator') {
            $company = "
            <td>
                <select name='company_id'>
                    <option value=''>Client Name</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
                </select>
            </td>
            ";

            /*$pm = "
            <td>
                <select name='project_manager_id'>
                    <option value=''>Project manager</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLPM, $project_manager_id)}
                </select>
            </td>
            ";*/

            $staff = "
            <td>
                <select name='staff_id'>
                    <option value=''>Staff Name</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
                </select>
            </td>
            ";
        }
        $position = "
        <td>
            <select name='position'>
                <option value=''>Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>
        ";

        $text = "
        {$company}
        {$pm}
        {$staff}
        {$branch}
        {$position}

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
    function getGenerateOrderRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id  = $fn->getReqParam('id');

        $fa = array();
        $fa['project_id'] = $project_id;
        $fa['order_date'] = date('Y-m-d');
        $fa['creation_date'] = date('Y-m-d');

        $projectRow = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $candRow    = $fn->getRecordRowByID('candidate', 'candidate_id', $projectRow['candidate_id']);
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $projectRow['company_id']);

        if($candRow['agent_id'] > 0){
            $agentRow   = $fn->getRecordRowByID('agent', 'agent_id', $candRow['agent_id']);
        }

        $fa['company_id']                = $projectRow['company_id'];
        $fa['position']                  = $projectRow['position'];
        $fa['position_type']             = $projectRow['position_type'];
        $fa['order_status']              = 'New';
        $fa['candidate_id']              = $projectRow['candidate_id'];
        $fa['candidate_name']            = $candRow['first_name'] .' ' . $candRow['last_name'];
        $fa['client_hourly_rate']        = $projectRow['client_hourly_rate'];
        $fa['candidate_hourly_rate']     = $projectRow['candidate_hourly_rate'];
        $fa['currency']                  = $projectRow['currency'];
        $fa['work_state']                = $projectRow['work_state'];
        $fa['company_name']              = $companyRow['company_name'];
        $fa['cust_address1']             = $companyRow['address_flat'];
        $fa['cust_address2']             = $companyRow['address_street'];
        $fa['cust_address_city']         = $companyRow['address_town'];
        $fa['cust_address_state']        = $companyRow['address_state'];
        $fa['cust_address_po_code']      = $companyRow['address_po_code'];
        $fa['address_country']           = $companyRow['address_country'];
        $fa['cust_address_country_code'] = $companyRow['address_country_code'];
        $fa['cust_phone']                = $companyRow['phone'];
        $fa['cust_fax']                  = $companyRow['fax'];
        $fa['created_by']                = $fn->getSessionParam('userName');

        if($projectRow['apply_commission'] == 1){
            $fa['apply_commission']         = $projectRow['apply_commission'];
            $fa['referral_id']              = $projectRow['referral_id'];
            $fa['commission_percentage']    = $projectRow['commission_percentage'];
        }
        else{
            $fa['apply_commission']         = 0;
            $fa['referral_id']              = '';
            $fa['commission_percentage']    = '';
        }

        if($candRow['agent_id'] > 0){
            $fa['subcontr_name']       = $agentRow['first_name'];
            $fa['subcontr_address1']   = $agentRow['company_address_flat'];
            $fa['subcontr_address2']   = $agentRow['company_address_street'];
            $fa['subcontr_city']       = $agentRow['company_address_town'];
            $fa['subcontr_state']      = $agentRow['company_address_state'];
            $fa['subcontr_pincode']    = $agentRow['address_country_code'];
            $fa['subcontr_country']    = $agentRow['company_address_country'];
        }

        $orderRec = $fn->getRecordByCondition('order', "project_id = '{$project_id}'");

        //check if the order record already exist or not
        if(is_array($orderRec)){
            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

    }

}