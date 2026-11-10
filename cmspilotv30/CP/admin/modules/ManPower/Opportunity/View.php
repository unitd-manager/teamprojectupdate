<?
class CP_Admin_Modules_ManPower_Opportunity_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('noty-2.0.3');
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $db = Zend_Registry::get('db');

        if ($_SESSION['userGroupType'] == "Agent") {
            return $this->getListForAgent($dataArray);
        } else {
            //**** DELETE EMPTY RECORDS ***//
            //$this->model->getDeleteEmptyOpportunityRecords();

            $text = '';
            $rows = '';
            $rowCounter = 0;

            foreach ($dataArray as $row){
                $editText = "
                <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                    <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
                </a>
                ";

                $site = '';
                if ($_SESSION['userGroupID'] == 1){
                    $site = $listObj->getListDataCell($row['site_title']);
                }

                $currency = '';
                if ($cpCfg['m.manPower.hasMultiCurrency'] == 1){
                    $currency = $row['currency'] . '&nbsp;';
                }

                //{$listObj->getListDataCell($currency . number_format($row['estimated_value']), "right")}

                $rows .= "
                {$listObj->getListRowHeader($row, $rowCounter)}
                {$listObj->getGoToDetailText($rowCounter, $row['opportunity_code'])}
                {$listObj->getGoToDetailText($rowCounter, $row['title'])}
                {$listObj->getListDataCell($row['position'])}
                {$listObj->getListDataCell($row['company_name'])}
                {$listObj->getListDataCell($row['contact_name'])}
                {$listObj->getListDataCell($row['no_of_position'])}
                {$listObj->getListDataCell('$'.number_format($row['estimated_value']), "right")}
                {$listObj->getListDataCell($row['status'])}
                {$listObj->getListDataCell($row['modified_by'].' '.$row['modification_date'])}
                {$listObj->getListRowEnd($row['opportunity_id'])}
                ";
                $rowCounter++;
            }

            $site = '';
            if ($_SESSION['userGroupID'] == 1 ){
                $site = $listObj->getListHeaderCell('Site', 'site_title');
            }



            $text = "
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Code', 'o.opportunity_id', 'w50')}
            {$listObj->getListHeaderCell('(Project) Name', 'o.title')}
            {$listObj->getListHeaderCell('Position', 'position')}
            {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
            {$listObj->getListHeaderCell('Contact', 'contact_name')}
            {$listObj->getListHeaderCell('No. of Position', 'o.no_of_position')}
            {$listObj->getListHeaderCell('Est Value', 'o.estimated_value', 'headerRight')}
            {$listObj->getListHeaderCell('Status', 'o.status')}
            {$listObj->getListHeaderCell('Updated By', '' )}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$this->getListFooter()}
            ";

            return $text;
        }
    }

    /**
     *
     */
    function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');

        $text = "";

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';

        $modOpp = getCPModuleObj('project_opportunity');
        $SQLSum  = $this->model->getOpportunityEstValueSQL();
        //$SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total = $row[0];

        $text = "
        </tbody>
        <tfoot>
            <!--
            <tr class='header'  background='{$cpCfg['cp.masterImagesPathAlias']}body/header_bg.jpg'>
               <td class='header' colspan='8'></td>
               <td class='header' style='text-align:right'>{$total}</td>
               <td class='header' colspan='10'></td>
            </tr>-->
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
       /* if ($cpCfg['m.manPower.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch);
        } */

        //{$formObj->getTBRow('Opportunity Title', 'title')}
        //if ($_SESSION['userGroupType'] == 'User') {
            $sqlCompany = "
            SELECT company_id
                  ,company_name AS title
            FROM company
            WHERE company_type != 'Referral'
            ORDER BY company_name
            ";
        //} else {
        //    $sqlCompany = $fn->getDDSql('manPower_company', array());
        //}

        $fielset1 = "
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlCompany)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $msgTop   = '';
        //to get related opportunities which come from same call registry record.
        if($row['call_registry_id'] != '' || $row['history_opportunity_id'] != ''){
            $whereCondition = '';
            if ($row['call_registry_id'] != '') {
                $whereCondition .= "call_registry_id = {$row['call_registry_id']}";
            } else if ($row['history_opportunity_id'] != '') {
                $whereCondition .= "opportunity_id = {$row['history_opportunity_id']}";
            }

            $SQLOpp = "
            SELECT opportunity_id
                  ,opportunity_code
            FROM opportunity
            WHERE {$whereCondition}
            AND opportunity_id != {$row['opportunity_id']}
            ";
            $resultOpp  = $db->sql_query($SQLOpp);
            $opportunity_codes = '';
            while ($rowOpp = $db->sql_fetchrow($resultOpp)) {
                $url = "index.php?_topRm=opportunity&module=manPower_opportunity&_action=edit&opportunity_id={$rowOpp['opportunity_id']}";
                $opportunity_codes = $opportunity_codes . "<u><a href='{$url}'>{$rowOpp['opportunity_code']}</a></u>" . '  ';
            }
        }

        if ($row['project_id'] != ''){
            $formObj->mode = 'detail';
            $msgTop = "
            <div class='p5'>
                <h3>This opportunity is already converted to project and no further editing allowed</h3>
            <div>
            ";
            $tv['action'] = 'detail';

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                $('.actionBtns #actBtn_save').parent().remove();
                $('.actionBtns #actBtn_apply').parent().remove();
                $('.actionBtns #actBtn_convertOppToProject').parent().remove();
            "));

        }
        $quote_ref = '';

        if ($cpCfg['m.manPower.oppurtunity.showQuoteRef'] == 1) {
            $quote_ref = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        $sqlComboContact = '';
        if ($row['company_id'] != '') {
            $sqlComboContact = $fn->getDDSql('manPower_candidate', array('condn' => "company_id = {$row['company_id']}"));
            $sqlComboContact = "
            SELECT c.contact_id
                 , CONCAT_WS(' ', c.first_name, c.last_name) as name
            FROM contact c
            WHERE c.company_id = {$row['company_id']}
            ORDER BY contact_priority ASC
            ";
        }

        $sqlStatusOld = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'opportunityStatus'
          AND value != 'Win'
        ORDER BY sort_order
        ";

        $appendStatusSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStatusSql = "AND site_id = {$_SESSION['cp_site_id']}";
        }

        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'opportunityStatus'
        {$appendStatusSql}
        ORDER BY sort_order
        ";

        $expVl = array('sqlType' => 'OneField');


        if (strtolower($row['status']) == 'win'
           || strtolower($row['status']) == 'confirmed'
           || $row['project_id'] > 0
           ){
            $projectTxt = '';

            if ($row['project_id'] > 0) {
                $projectLink = "index.php?_topRm={$tv['topRm']}&module=manPower_project&project_id={$row['project_id']}&_action=detail";
                $linkToProj = "<a href='{$projectLink}'>{$row['project_code']}</a>";
                $projectTxt = $formObj->getTBRow('Project Code', 'project_code', $linkToProj, array('isEditable' => 0));
            }

            $expStatus = array('sqlType' => 'OneField');
            $status  = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
            $status .= $projectTxt;

        } else {
            $convertBtn = '';

            $SQL2 = "
            SELECT count(*) AS count
            FROM quote
            WHERE opportunity_id = {$row['opportunity_id']}
              AND status = 'Agreed'
            ";
            $result2 = $db->sql_query($SQL2);
            $row2 = $db->sql_fetchrow($result2);

            if ($row2['count'] > 0) {
                $convertBtn = "
                <button type='button' id='convertToProject'>Convert to Project</button>
                ";
            }
            if($formObj->mode == 'edit'){
                $expStatus = array('notes' => $convertBtn, 'sqlType' => 'OneField');
            } else {
                $expStatus = array('sqlType' => 'OneField');
            }
            $status = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
        }

        $notes = '';

        $expNoEdit  = array('isEditable' => 0);

       /* if ($_SESSION['userGroupType'] == 'User') {
            $sqlCompany = "
            SELECT company_id
                  ,company_name AS title
            FROM company
            WHERE company_type !='Referral'
            ORDER BY company_name
            ";
        } else {*/
            $sqlCompany = "
            SELECT company_id
                  ,company_name AS title
            FROM company
            WHERE company_type !='Referral'
            ORDER BY company_name
            ";
        //}
        //'condn' => "category = 'client'"

        $sqlType            = $fn->getValueListSQL('clientType');
        $sqlDiff            = $fn->getValueListSQL('projectDifficulty');
        $sqlCat             = $fn->getValueListSQL('projectCategory');
        $sqlChance          = $fn->getValueListSQL('opportunityChance');
        $sqlPM              = $fn->getValueListSQL('projectManager');
        $sqlPosition        = $fn->getValueListSQL('opportunityPosition','value');
        $sqlPosition_type   = $fn->getValueListSQL('opportunityPositionType');
        $sqlPassType        = $fn->getValueListSQL('opportunityPassType');
        $sqlIndustry        = $fn->getValueListSQL('callRegistryIndustry','value');

        //$staff_name = $_SESSION['staff_id'];
        $staff_name = $row['staff_id'];
        $staffRec = $fn->getRecordRowByID('staff', 'staff_id', $staff_name);

        $expComp = array();

        $company = "<a href='index.php?_topRm={$tv['topRm']}&module=manPower_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";
        $contact = "<a href='index.php?_topRm={$tv['topRm']}&module=manPower_candidate&contact_id={$row['candidate_id']}&_action=detail'>{$row['contact_name']}</a>";

        if ($tv['action'] == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_opportunity', 'manPower_companyLink', 'fld_company_id')}'>Choose</a>";
            $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['company_name']);
        } else {
            $expComp  = array('detailValue' => $company);
        }

        $sqlCandidateCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['opportunity_country_name']);

        $expCont = array('detailValue' => $contact);
        $enqDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['enquiry_date'];
        $expNum  = array('autoFormat' => 1);
        $expCost = array('autoFormat' => 1, 'isEditable' => 0);

        $call_registry_code = "
        <a href='index.php?_topRm=marketing&module=manPower_callRegistry&call_registry_id={$row['call_registry_id']}&_action=detail' target='_blank'>{$row['call_registry_code']}</a>";

        $otherOppMsg = "";
        $callRegistryTr  = '';
        $callRegistryRelatedTr = '';
        if($row['call_registry_id'] != '' || $row['history_opportunity_id'] != ''){
            /* Finding empty opportunity records for a call registry record */
            $whereCondition = '';
            if ($row['call_registry_id'] != '') {
                $whereCondition .= "call_registry_id = {$row['call_registry_id']}";
            } else if ($row['history_opportunity_id'] != '') {
                $whereCondition .= "opportunity_id = {$row['history_opportunity_id']}";
            }

            $SQLOppEmpty = "
            SELECT opportunity_id
                  ,opportunity_code
            FROM opportunity
            WHERE {$whereCondition}
            AND opportunity_id != {$row['opportunity_id']}
            AND salary IS NULL
            ";
            $resultOppEmpty = $db->sql_query($SQLOppEmpty);
            $numRowsOppEmpty = $db->sql_numrows($resultOppEmpty);

            /* Asking USER to input data for empty data of opportunity */
            if ($numRowsOppEmpty) {
                $opportunity_codes_empty = '';
                while ($rowOpp = $db->sql_fetchrow($resultOppEmpty)) {
                    $url = "index.php?_topRm=opportunity&module=manPower_opportunity&_action=edit&opportunity_id={$rowOpp['opportunity_id']}";
                    $opportunity_codes_empty = $opportunity_codes_empty . "<u><a href='{$url}'>{$rowOpp['opportunity_code']}</a></u>" . '  ';
                }

                $otherOppMsg = "
                <div class='opportunityAlertBorder'>
                    <div class='opportunityAlertText'>Please populate opportunity data for other opportunities</div>
                    <div>{$opportunity_codes_empty}</div>
                </div>
                ";
            }

            //$company = $formObj->getTBRow('Company Name', 'company_name', $row['company_name'], $expNoEdit);
        $company = $formObj->getDDRowBySQL('Client Name', 'company_id', $sqlCompany, $row['company_id'], $expComp);
        if ($row['call_registry_id'] != '') {
            $callRegistryTr = $formObj->getTBRow('Call Registry Code', 'call_registry_id', $call_registry_code, $expNoEdit);
        }
        $callRegistryRelatedTr = $formObj->getTBRow('Related Opportunity', 'related_opportunity_code', $opportunity_codes, $expNoEdit);
        } else {
            $company = $formObj->getDDRowBySQL('Client Name', 'company_id', $sqlCompany, $row['company_id'], $expComp);
        }

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&_spAction=addNewValuelistForm&valuelist_name=opportunityPosition&opportunity_id={$row['opportunity_id']}&showHTML=0";
        $expPosition = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='opportunityPosition'>Add</a>");

        $formAddIndustry = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&_spAction=addNewValuelistForm&valuelist_name=callRegistryIndustry&opportunity_id={$row['opportunity_id']}&showHTML=0";
        $expIndustry     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddIndustry}' class='mr20 addNewValue' valuelist_name='callRegistryIndustry'>Add</a>");

        $formAddCategory = "index.php?_topRm={$tv['topRm']}&module=manPower_opportunity&_spAction=addNewValuelistForm&valuelist_name=projectCategory&opportunity_id={$row['opportunity_id']}&showHTML=0";
        $expCategory     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddCategory}' class='mr20 addNewValue' valuelist_name='projectCategory'>Add</a>");

        $position = "
        <div class='positionTitle'>
         {$formObj->getDDRowBySQL('Position *', 'position', $sqlPosition, $row['position'], $expPosition)}
        </div>
        ";

        /*
        $staff_name = $_SESSION['userFullName'];
        $sqlStaff = $fn->getDDSql('manPower_staff', array('condn' => $staff_name));
        {$formObj->getTBRow('Staff', 'staff_id', $row['staff_name'], $expNoEdit)}
        */
        //{$formObj->getTBRow('Period Of Years', 'period_of_year', $row['period_of_year'])}

        $fieldset1  = "
        {$otherOppMsg}
        {$msgTop}
        {$formObj->getTBRow('Code', 'opportunity_code', $row['opportunity_code'], $expNoEdit)}
        {$position}
        {$formObj->getDDRowBySQL('Position Type *', 'position_type', $sqlPosition_type, $row['position_type'], $expVl)}
        {$formObj->getTBRow('Number of Position*', 'no_of_position', $row['no_of_position'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$status}
        {$formObj->getTBRow('Work State', 'work_state', $row['work_state'])}
        {$company}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlComboContact, $row['contact_id'], $expCont)}
        {$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expIndustry)}
        {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $expCategory)}
        {$formObj->getTBRow('Staff', 'staff_id', $staffRec['first_name'] . ' ' . $staffRec['last_name'], $expNoEdit)}
        {$callRegistryRelatedTr}
        {$callRegistryTr}
        ";
        //{$formObj->getDDRowBySQL('Chance', 'chance', $sqlChance, $row['chance'], $expVl)}

        $fieldset2 = "
        {$formObj->getTBRow('Salary', 'salary', $row['salary'])}
        {$formObj->getTBRow('Working Hours', 'working_hours', $row['working_hours'])}
        {$formObj->getTBRow('Leave/Month', 'leave_year', $row['leave_year'])}
        {$formObj->getTBRow('Required Experience', 'required_experience', $row['required_experience'])}
        {$formObj->getDDRowBySQL('Pass Type', 'pass_type',$sqlPassType, $row['pass_type'], $expVl)}
        {$formObj->getDDRowBySQL('Candidate Country', 'candidate_country',$sqlCandidateCountry, $row['candidate_country'], $expCountry)}
        ";

        /*$fieldset2 = "
        {$formObj->getDDRowBySQL('Difficulty', 'difficulty', $sqlDiff, $row['difficulty'], $expVl)}
        {$formObj->getDDRowBySQL('Opportunity Category', 'category', $sqlCat, $row['category'], $expVl)}
        {$formObj->getTBRow('Enquiry Date', 'enquiry_date', $row['enquiry_date'], $expNoEdit)}
        {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getYesNoRRow('Follow up Required', 'follow_up_needed', $row['follow_up_needed'])}
        {$formObj->getDateRow('Estimated Start Date', 'estimated_start_date', $row['estimated_start_date'])}
        ";*/

        $currency = '';
        $base_value = '';
        if ($cpCfg['m.manPower.hasMultiCurrency'] == 1){
            $sqlCurrency = $fn->getValueListSQL('currency');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], $expVl);
            $base_value = $formObj->getTBRow("Estimated Value ({$cpCfg['m.manPower.baseCurrency']})", 'estimated_value_base', $row['estimated_value_base'], $expNum);
        }

        $fieldset3 = "
        {$formObj->getTBRow('Client Hourly Rate', 'client_hourly_rate', $row['client_hourly_rate'])}
        {$formObj->getTBRow('Candidate Hourly Rate', 'candidate_hourly_rate', $row['candidate_hourly_rate'])}
        {$currency}
        {$formObj->getTBRow('Estimated Value', 'estimated_value', $row['estimated_value'], $expNum)}
        ";

        $fieldset4 = "
        {$formObj->getHTMLEditor('Description', 'description', $row['description'], '0')}
        ";

        if ($_SESSION['userGroupType'] == "Agent") {

            $fieldset1  = "
            {$formObj->getTBRow('Code', 'opportunity_code', $row['opportunity_code'], $expNoEdit)}
            {$formObj->getTBRow('Required job Position', 'position', $row['position'], $expNoEdit)}
            {$formObj->getTBRow('Title', 'title', $row['title'], $expNoEdit)}
            ";

            $text = "
            {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
            {$formObj->getFieldSetWrapped('Opportunity Details', $fieldset2)}
            ";
        } else {
            $text = "
            {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
            {$formObj->getFieldSetWrapped('Opportunity Details', $fieldset2)}
            {$formObj->getFieldSetWrapped('Costs', $fieldset3)}
            {$formObj->getFieldSetWrapped('Description', $fieldset4)}
            {$formObj->getCreationModificationText($row)}
            <input type='hidden' id='hasQuotingModule' value='{$cpCfg['m.manPower.hasQuotingModule']}' />
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getSearch($result) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $row = Zend_Registry::get('row');

        $sqlComboClientCompany  = $fn->getDDSql('project_company');
        $sqlComboStaffName = $fn->getDDSql('manPower_staff', array('condn' => "status = 'Current'"));

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlCat    = $fn->getValueListSQL('projectCategory');
        $sqlChance = $fn->getValueListSQL('opportunityChance');
        $sqlStatus = $fn->getValueListSQL('opportunityStatus');
        $expVl     = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComboClientCompany)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlComboStaffName)}
        {$formObj->getDDRowBySQL('Opportunity Category', 'category', $sqlCat, '', $expVl)}
        {$formObj->getDateRangeRow('Enquiry Date', 'enquiry_date')}
        {$formObj->getDateRangeRow('Follow up Date', 'follow_up_date')}
        {$formObj->getDateRangeRow('Estimated Start Date', 'estimated_start_date')}
        {$formObj->getDDRowBySQL('Chance', 'chance', $sqlChance, $row['chance'], $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Opportunity Details', $fieldset)}
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
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $quotingModule = '';
        $links = '';
        if ($row['project_id'] != ''){
            $formObj->mode = 'detail';
            $tv['action'] = 'detail';
        }

        if ($cpCfg['m.manPower.hasQuotingModule'] == 1 && ($tv['action'] == "edit" || $tv['action'] == "detail")) {
            $quote = getCPModuleObj('project_quote', true);

            if ($tv['action'] == '') {
                CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    $('.actBtns').html('');
                "));
            }

            $quotingModule = "
            <div id='quotesOuter'>{$quote->view->getQuotesPortal($row['opportunity_id'], 'opp')}</div>
            <input type='hidden' id='opportunity_id' value='{$row['opportunity_id']}' />
            ";
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array('cpm.project.quote.init()'));
        }

        $record_id = $fn->getIssetParam($row, 'opportunity_id');

        //{$media->getRightPanelMediaDisplay('Job Description(Optional)', 'manPower_opportunity', 'attachment1', $row)}
        $agentMail = '';
        if ($cpCfg['m.manPower.opportunity.hasAgentMail'] == 1) {
            $urlAgentMail = "index.php?_topRm=opportunity&module=manPower_opportunity&_spAction=sendMailToAgentForm&opportunity_id={$row['opportunity_id']}&showHTML=0";
            $agentMail = "
            <div class='floatbox  btnbackground'>
                <div class='button mb5'>
                    <a href='{$urlAgentMail}' id='sendAgentMail'>Send Mail To Agent</a>
                </div>
            </div>
            ";
        }


        if ($_SESSION['userGroupType'] == 'Super Administrator') {
            $comments = "
            {$comment->getView(array(
                 'roomName' => 'manPower_opportunity'
                ,'recordId' => $record_id
                ,'contactModule' => 'manPower_staff'
                ,'allowEdit' => false
                ,'allowDelete' => false
            ))}
            ";
        } else {
            $comments = "
            {$comment->getView(array(
                 'roomName' => 'manPower_opportunity'
                ,'recordId' => $record_id
                ,'contactModule' => 'manPower_staff'
                ,'addReviewLbl' => ''
                ,'allowResendLink' => true
            ))}
            ";
        }

        $urlResendLink = "";
        $resendLink = "
        <div class='floatbox  btnbackground'>
            <div class='resendMail mb5 line'>
                <a href='{$urlResendLink}' id='resendLink'>Resend Agent Mail</a>
            </div>
        </div>
        ";
        //$expAtt = array('hasNew' => 0);
        //$arr['hasNew']  = true;

        //{$displayLinkData->getLinkPortalMain('manPower_opportunity', 'manPower_candidateLink', 'Candidate Linked', $row)}

        if ($_SESSION['userGroupType'] == "Agent") {
            $text = "
            {$this->getCandidateByAgentId($row)}
            ";
        } else {
            $text = "
            {$agentMail}
            {$media->getRightPanelMediaDisplay('Attachments', 'manPower_opportunity', 'attachment', $row)}
            <div id='candidateLinkPortal'>{$this->getOpportunityCandidateDisplay($row['opportunity_id'])}</div>
            {$displayLinkData->getLinkPortalMain('manPower_opportunity', 'manPower_staffLink', 'Staff Linked', $row)}
            {$displayLinkData->getLinkPortalMain('manPower_opportunity', 'manPower_taskLink', 'Tasks', $row)}
            {$displayLinkData->getLinkPortalMain('manPower_opportunity', 'manPower_expenseLink', 'Expense Linked', $row)}
            {$quotingModule}
            {$comments}
            ";
        }

        return $text;
    }

    /**
     */
    function getOpportunityCandidateDisplay($opportunity_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }

        $Candidates = $this->getCandidatMembers($opportunity_id);

        $recCount = $fn->getRecordCount('opportunity_candidate', "opportunity_id = '{$opportunity_id}'");

        //<th>Resume</th>
        //<th>Response Status</th>
        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Candidate name</th>
            <th>Process Status</th>
            <th>Percent</th>
            <th>Subcontractor Code</th>
            <th></th>
            <th></th>
            <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount ==0){
            $header ="<thead></thead>";
        }

        $formActionAddCandidate = "index.php?module=manPower_opportunity&_spAction=addCandidate&opportunity_id={$opportunity_id}&showHTML=0";
        $add = "<div class='actBtns'>
                    <a id='addCandidateNew' href='{$formActionAddCandidate}' opportunity_id={$opportunity_id}>Add Candidate</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper manPower_opportunity__manPower_candidateLink'>
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
                        <tbody id='candidateDisplayPortal'>
                            {$Candidates}
                        </tbody>
                    </table>
                    <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }

    function getCandidatMembers($opportunity_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }

        $rows  = "";

        $SQL="
        SELECT oc.opportunity_candidate_id
              ,oc.candidate_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS candidate_name
              ,oc.process_status
              ,oc.response_status
              ,oc.percent_win
              ,a.agent_id
              ,a.agent_code
        FROM opportunity_candidate oc
        LEFT JOIN candidate c ON (c.candidate_id = oc.candidate_id)
        LEFT JOIN agent a ON (a.agent_id = c.agent_id)
        WHERE oc.opportunity_id = '{$opportunity_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($rowCandidate = $db->sql_fetchrow($result)) {

            $Resume                  = "<a href='index.php?_topRm=opportunity&module=manPower_opportunity&_spAction=printCandidateResumeAsPdf&candidate_id={$rowCandidate['candidate_id']}' target='_blank'>Resume</a>";
            $detailLink              = "<a href='index.php?_topRm=opportunity&module=manPower_opportunityCandidate&_action=edit&opportunity_candidate_id={$rowCandidate['opportunity_candidate_id']}' target='_blank'>Go to Detail</a>";
            $candidate_name          = "<a href='index.php?_topRm=opportunity&module=manPower_candidate&_action=detail&record_id={$rowCandidate['candidate_id']}'>{$rowCandidate['candidate_name']}</a>";
            //$formDeleteCandidate     = "index.php?module=manPower_opportunity&_spAction=deleteCandidateRecord&opportunity_id={$opportunity_id}&opportunity_candidate_id={$rowCandidate['opportunity_candidate_id']}&showHTML=0";
            $formActionEditCandidate = "index.php?module=manPower_opportunity&_spAction=editCandidate&id={$rowCandidate['opportunity_candidate_id']}&opportunity_id={$opportunity_id}&showHTML=0";
            //$convertToProject = "<a href='index.php?module=manPower_opportunity&_spAction=convertOppToProject&candidate_id={$rowCandidate['candidate_id']}&opportunity_id={$opportunity_id}' target='_blank'>Convert to Project</a>";
            //<td>{$Resume}</td>
            //<td>{$rowCandidate['response_status']}</td>

            $sqlProjectLink="
            SELECT oc.opportunity_id
                   ,p.project_id
                   ,p.candidate_id
            FROM project p
            LEFT JOIN opportunity_candidate oc ON (oc.opportunity_id=p.opportunity_id)
            WHERE p.opportunity_id={$opportunity_id}
            AND p.candidate_id = {$rowCandidate['candidate_id']}
            AND oc.opportunity_candidate_id = {$rowCandidate['opportunity_candidate_id']}
            ";
            $resultProjectLink   = $db->sql_query($sqlProjectLink);
            $resultProject       = $db->sql_query($sqlProjectLink);
            $ProjectLink         = $db->sql_numrows($resultProjectLink);
            $rowProject          = $db->sql_fetchrow($resultProject);

            if($ProjectLink >0){
                if($rowProject['candidate_id']==$rowCandidate['candidate_id']){
                    $convertToProject = "<a href='index.php?_topRm=project&module=manPower_project&project_id={$rowProject['project_id']}&_action=edit'>Go to Project</a>";
                    $deleteIcon ="";
                    $editIcon   ="";
                }
            }else{
                $convertToProject = "<a class='convertToProjectClass' candidate_id={$rowCandidate['candidate_id']} opportunity_id={$opportunity_id} href='#'>Convert to Project</a>";
                $deleteIcon ="
                    <div class='float_right'>
                        <a class='deleteCandidateRecord' href='#'  opportunity_id='{$opportunity_id}' opportunity_candidate_id='{$rowCandidate['opportunity_candidate_id']}'>
                            <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                        </a>
                    </div>
                    ";
                $editIcon ="
                    <div class='float_left'>
                        <a class='editCandidate' href='{$formActionEditCandidate}' opportunity_id={$opportunity_id}>
                            <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                        </a>
                    </div>
                    ";
            }

            $agent_code  = "<a href='index.php?_topRm=opportunity&module=manPower_agent&_action=detail&record_id={$rowCandidate['agent_id']}'>{$rowCandidate['agent_code']}</a>";

            $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$candidate_name}</td>
                    <td>{$rowCandidate['process_status']}</td>
                    <td>{$rowCandidate['percent_win']}</td>
                    <td>{$agent_code}</td>
                    <td>{$convertToProject}</td>
                    <td>{$detailLink}</td>
                    <td class='portalActBtns'>
                        {$editIcon}
                        {$deleteIcon}
                    </td>
                </tr>
            ";
            $count++;
        }

        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noCandidate'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

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
        $row = $fn->getRecordRowByID('opportunity', 'opportunity_id', $id);

        $sqlChance = $fn->getValueListSQL('opportunityChance');
        $sqlStatus = $fn->getValueListSQL('opportunityStatus');

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Chance', 'chance', $sqlChance, $row['chance'], $exp)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
            </fieldset>
            <input type='hidden' name='opportunity_id' value='{$id}' />
        </form>
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

        $chance             = $fn->getReqParam('chance');
        $company_id         = $fn->getReqParam('company_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $position             = $fn->getReqParam('position');

        $sqlPosition = $fn->getValueListSQL('opportunityPosition');

        //$today_reminder     = $fn->getReqParam('today_reminder');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN opportunity b ON (a.company_id = b.company_id)
        ORDER BY company_name
        ";

        $appendStaffSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStaffSql = " AND site_id = '{$_SESSION['cp_site_id']}'";
        }

        $SQLStf = $fn->getDDSql('manPower_staff',
                                array('condn' => "status = 'Current'
                                      AND staff_login_type = 'Staff'
                                      {$appendStaffSql}"
                               ));


        $SQLStatus = $fn->getValueListSQL('opportunityStatus');
        $SQLChance = $fn->getValueListSQL('opportunityChance');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(enquiry_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(enquiry_date, '%b %Y') AS monthYear
        FROM opportunity
        WHERE DATE_FORMAT(enquiry_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
        ";

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        /*$spArrayReminder = array(
            "Follow Up For Today"
           ,"Show All"
        );*/

        $staff = '';
        $company = '';
        if ($_SESSION['userGroupType'] == 'Super Administrator') {
            $company = "
            <td>
                <select name='company_id'>
                    <option value=''>Client Name</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
                </select>
            </td>
            ";

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


        $text = '';
        if ($_SESSION['userGroupType'] != "Agent") {
            $text = "
            {$company}
            {$staff}
            {$position}
            <td>
                <select name='status'>
                    <option value=''>Status</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $tv['status'])}
                </select>
            </td>
            ";
            /*<td>
                <select name='today_reminder'>
                    <option value=''>Please Select</option>
                    {$cpUtil->getDropDown1($spArrayReminder, $today_reminder)}
               </select>
            </td>*/
        }

        return $text;
    }

    /**
     *
     */
    function getSendMailToAgentForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $opportunity_id = $fn->getReqParam('opportunity_id');

        $SQL = "
        SELECT opp.*
        FROM opportunity opp
        WHERE opp.opportunity_id = {$opportunity_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        /*
        $phpScript = realpath('.') . '/util/send_message.php';
        $params  = 'host=' . $_SERVER['HTTP_HOST'] .'/admin';
        print $phpScript . '/' . $params;
        */

        $emailDraft = $cpCfg['cp.emailtoagentdraft'];

        $formAction = "index.php?_topRm=opportuntiy&module=manPower_opportunity&_spAction=sendMailToAgentFormSubmit&showHTML=0";

        /*
        $message = $cpCfg['cp.emailtoagentdraft'];
        $message = str_replace("[opp_code]", $row['opportunity_code'], $message );
        $message = str_replace("[position]", $row['position'], $message );
        $message = str_replace("[salary]", $row['salary'], $message );
        $message = str_replace("[working_hours]", $row['working_hours'], $message );
        $message = str_replace("[leave_year]", $row['leave_year'], $message );
        $message = str_replace("[required_experience]", $row['required_experience'], $message );
        $message = str_replace("[pass_type]", $row['pass_type'], $message );
        $message = str_replace("[candidate_country]", $row['candidate_country'], $message );
        $message = str_replace("[description]", $row['description'], $message );
        $message = str_replace("[user]", 'Agent Name', $message );
        $message = nl2br($message);
        */

        $text = "
        <form id='portalForm' class='yform columnar agentMailForm' method='post' action='{$formAction}'>
            {$formObj->getTextAreaRow('Remarks to Agent', "remarks")}
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getShowCandidateDetails() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $candidate_id = $fn->getReqParam('candidate_id');
        $row = $fn->getRecordRowById('candidate', 'candidate_id', $candidate_id);
        $rowAgent = $fn->getRecordRowById('agent', 'agent_id', $row['agent_id']);
        $education_country1 = $row['education_country1'];
        $country_name_edu = $fn->getRecordByCondition('geo_country', "country_code = '{$education_country1}'");

        $expEdit = array('isEditable' => 0);
        $period_of_study_from1  = $fn->getCPDate($row['period_of_study_from1'], 'd F Y'); //23 January 2013 format
        $period_of_study_to1    = $fn->getCPDate($row['period_of_study_to1'], 'd F Y'); //23 January 2013 format

        $employment_period_from1 = $fn->getCPDate($row['employment_period_from1'], 'd F Y'); //23 January 2013 format
        $employment_period_to1   = $fn->getCPDate($row['employment_period_to1'], 'd F Y'); //23 January 2013 format

        $text = "
        <form class='yform columnar' method='post' action=''>
            <div class='linkPortalWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                        <div class='float_left'>Personal Details </div>
                        <div class='toggle'> </div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper ml10'>
                        {$formObj->getTBRow('Name', '', $row['first_name'], $expEdit)}
                        {$formObj->getTBRow('Travel Document No', '', $row['travel_document_no'], $expEdit)}
                        {$formObj->getTBRow('Agency Code', '', $rowAgent['agent_code'], $expEdit)}
                    </div>
                </div>
            </div>

            <div class='linkPortalWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                        <div class='float_left'>Education Details </div>
                        <div class='toggle'> </div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper ml10'>
                        {$formObj->getTBRow('University Country', '', $country_name_edu['name'], $expEdit)}
                        {$formObj->getTBRow('Name of degree', '', $row['degree_name1'], $expEdit)}
                        {$formObj->getTBRow('Main Campus or Affiliating College Attended', '', $row['college_name1'], $expEdit)}
                        {$formObj->getTBRow('Qualification', '', $row['education_qualification1'], $expEdit)}
                        {$formObj->getTBRow('Faculty', '', $row['education_faculty1'], $expEdit)}
                        {$formObj->getTBRow('Specialisation', '', $row['education_specialisation1'], $expEdit)}
                        {$formObj->getTBRow('If None of the Above, please specify', '', $row['education_none_of_the_above1'], $expEdit)}
                        {$formObj->getTBRow('Mode of Study', '', $row['mode_of_study1'], $expEdit)}

                        <div class='subcolumns'>
                            <div class='c50l'>
                                <div class='subcl'>
                                    <div class='dateFrom'>
                                        {$formObj->getTBRow('Period of Study From', '', $period_of_study_from1, $expEdit)}
                                    </div>
                                </div>
                            </div>
                            <div class='c50r'>
                                <div class='subcl'>
                                    <div class='dateTo'>
                                        {$formObj->getTBRow('Period of Study To', '', $period_of_study_to1, $expEdit)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='linkPortalWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                        <div class='float_left'>Employment Details </div>
                        <div class='toggle'> </div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper ml10'>
                        {$formObj->getTBRow('Name of Company', '', $row['employment_company_name1'], $expEdit)}
                        {$formObj->getTBRow('Occupation', '', $row['employment_occupation1'], $expEdit)}
                        {$formObj->getTBRow('Country', '', $row['employment_country1'], $expEdit)}

                        <div class='subcolumns'>
                            <div class='c50l'>
                                <div class='subcl'>
                                    <div class='dateFrom'>
                                        {$formObj->getTBRow('Period From', '', $employment_period_from1, $expEdit)}
                                    </div>
                                </div>
                            </div>
                            <div class='c50r'>
                                <div class='subcl'>
                                    <div class='dateTo'>
                                        {$formObj->getTBRow('Period To', '', $employment_period_to1, $expEdit)}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {$formObj->getTBRow('Fixed Monthly  Salary', '', $row['employment_salary1'], $expEdit)}
                        {$formObj->getTARow('Job Duties & Responsibilities', '', $row['job_duties_responsibilities1'], $expEdit)}
                    </div>
                </div>
            </div>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getCandidateByAgentId($row) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $sqlCandidate = "
        SELECT c.first_name
              ,oc.process_status
        FROM candidate c
        LEFT JOIN (opportunity_candidate oc) ON (c.candidate_id = oc.candidate_id)
        LEFT JOIN (agent a)     ON (c.agent_id = a.agent_id)
        LEFT JOIN (staff s)     ON (a.agent_id = s.agent_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
          AND oc.opportunity_id = {$row['opportunity_id']}
        ORDER BY c.first_name ASC
        ";
        $resultCandidate  = $db->sql_query($sqlCandidate);
        while ($rowCandidate = $db->sql_fetchrow($resultCandidate)) {
            $rows .= "
            <tr>
                <td>{$rowCandidate['first_name']}</td>
                <td>{$rowCandidate['process_status']}</td>
            </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Candidate Linked</div>
                </div>
            </div>

            <table>
                <thead>
                    <th>Candidate Name</th>
                    <th>Status</th>
                </thead>

                <tbody>
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
    function getListForAgent($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['opportunity_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getListRowEnd($row['opportunity_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'o.opportunity_id')}
        {$listObj->getListHeaderCell('(Project) Name', 'o.title')}
        {$listObj->getListHeaderCell('Position', 'position')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$this->getListFooter()}
        ";

        return $text;
    }
}