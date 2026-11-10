<?
class CP_Admin_Modules_Project_Opportunity_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";
            
            $branch = '';
            if ($cpCfg['m.project.hasMultiBranches'] == 1){
                $branch = $listObj->getListDataCell($row['branch_name']);
            }

            $currency = '';
            if ($cpCfg['m.project.hasMultiCurrency'] == 1){
                $currency = $row['currency'] . '&nbsp;';
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['enquiry_date'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['ref_contact_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['chance'])}
            <!-- {$listObj->getListDataCell($currency . number_format($row['estimated_value']), "right")} -->
            {$listObj->getListDateCell($row['follow_up_date'])}
            {$branch}
            <!-- {$listObj->getListDataCell($editText)} -->
            {$listObj->getListRowEnd($row['opportunity_id'])}
            ";
            $rowCounter++;
        }

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $branch = $listObj->getListHeaderCell('Branch', 'branch_name');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Enquiry Date', 'o.enquiry_date')}
        {$listObj->getListHeaderCell('Opportunity', 'o.title')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Referrer', 'ref_contact_name')}
        {$listObj->getListHeaderCell('Category', 'o.category')}
        {$listObj->getListHeaderCell('Status', 'o.status')}
        {$listObj->getListHeaderCell('Rating', 'o.chance')}
        <!-- {$listObj->getListHeaderCell('Est Value', 'o.estimated_value', 'headerRight')} -->
        {$listObj->getListHeaderCell('Follow up Date', 'o.follow_up_date')}
        {$branch}
        <!-- {$listObj->getListHeaderCell('Edit')} -->
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
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

        $text = "";

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';

        $modOpp = getCPModuleObj('project_opportunity');
        $SQLSum  = $modOpp->model->getOpportunityEstValueSQL();
        $SQLSum .= $searchVar->getSearchVar($tv['module'], 0);
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total = $row[0];

        $text = "
        </tbody>
        <tfoot>
            <tr class='header'  background='{$cpCfg['cp.masterImagesPathAlias']}body/header_bg.jpg'>
               <td class='header' colspan='8'></td>
               <td class='header' style='text-align:right'>{$total}</td>
               <td class='header' colspan='10'></td>
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

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Opportunity Title', 'title')}
        {$branch}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('New Opportunity', $fieldset1)}
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
        
        if ($cpCfg['m.project.oppurtunity.showQuoteRef'] == 1) {
            $quote_ref = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        $sqlComboContact = '';
        if ($row['company_id'] != "") {
            $sqlComboContact = $fn->getDDSql('project_contact', array('condn' => "company_id = {$row['company_id']}"));
        }

        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'opportunityStatus'
          AND value != 'Won'
        ORDER BY sort_order
        ";

        $expVl = array('sqlType' => 'OneField');

        if (strtolower($row['status']) == 'won' 
           || strtolower($row['status']) == 'confirmed'
           || $row['project_id'] > 0
           ){
            $projectTxt = '';

            if ($row['project_id'] > 0) {
                $projectLink = "index.php?_topRm={$tv['topRm']}&module=project_project&project_id={$row['project_id']}&_action=detail";
                $linkToProj = "<a href='{$projectLink}'>{$row['project_code']}</a>";
                $projectTxt = $formObj->getTBRow('Project Code', 'project_code', $linkToProj, array('isEditable' => 0));
            }
            
            $expStatus = array('sqlType' => 'OneField');
            $status  = $formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expStatus);
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
            $status = $formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expStatus);
        }

        $notes = '';

        $expNoEdit  = array('isEditable' => 0);
        $expOppCode = array('isEditable' => $cpCfg['m.project.oppurtunity.codeEditable']);

        $sqlCompany = $fn->getDDSql('project_company', array('condn' => "category = 'client'"));

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlCat    = $fn->getValueListSQL('projectCategory');
        $sqlChance = $fn->getValueListSQL('opportunityChance');

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $expBranch = array('detailValue' => $row['branch_name']);
            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch, $row['branch_id'], $expBranch);
        }
        
        $expComp = array();
        $expCont = array();

        $companyLink = "<a href='index.php?_topRm={$tv['topRm']}&module=project_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";
        $contactLink = "<a href='index.php?_topRm={$tv['topRm']}&module=project_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";
        
        if ($tv['action'] == 'edit'){
            $newCompUrl = 'index.php?_spAction=new&lnkRoom=project_companyLink&showHTML=0';
            $newCompUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Company' 
            w=800 href='' link='{$newCompUrl}' callback='cpm.project.opportunity.afterNewCompany'>New</a>";
            $expComp  = array(
                 'notesRight'  => $newCompUrl
                ,'detailValue' => $row['company_name']
                ,'autoSgstModule' => 'project_company'
                ,'autoSgstSrchFld' => 'company_name'
                ,'autoSgstActualFld' => 'company_id'
                ,'autoSgstActualFldVal' => $row['company_id']
                ,'autoSgstCallBack' => 'cpm.project.opportunity.loadContactsByCompany'
            );

            $newContactUrl = 'index.php?_spAction=new&lnkRoom=project_contactLink&showHTML=0';
            $newContactUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Contact' 
            w=800 href='' link='{$newContactUrl}' callback='cpm.project.opportunity.afterNewContact'>New</a>";

            $expCont  = array(
                 'notesRight'  => $newContactUrl
                ,'detailValue' => $row['contact_name']
            );


        } else {
            if ($row['company_name'] != ''){
                $expComp['detailValue'] = $companyLink;
            }
        }
        
        if ($row['contact_name'] != ''){
            $expCont['detailValue'] = $contactLink;
        }
        
        $enqDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['enquiry_date'];
        $expNum  = array('autoFormat' => 1);
        $expCost = array('autoFormat' => 1, 'isEditable' => 0);

        $fieldset1  = "
        {$msgTop}
        {$formObj->getTBRow('Code', 'opportunity_code', $row['opportunity_code'], $expOppCode)}
        {$formObj->getTBRow('Opportunity Title', 'title', $row['title'])}
        {$quote_ref}
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'], $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlComboContact, $row['contact_id'], $expCont)}
        {$formObj->getDateRow('Enquiry Date', 'enquiry_date', $enqDate)}
        {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
        {$branch}
        {$formObj->getDDRowBySQL('Opportunity Category', 'category', $sqlCat, $row['category'], $expVl)}
        {$status}
        ";

        $expReferrer = array(
             'autoSgstModule' => 'project_contact'
            ,'autoSgstActualFld' => 'referrer_contact_id'
            ,'autoSgstActualFldVal' => $row['referrer_contact_id']
        );

        $fieldset2  = "
        {$formObj->getDDRowByVL('Channel', 'source_channel', 'opportunitySourceChannel', $row['source_channel'], $expVl)}
        {$formObj->getTBRow('Referrer', 'ref_contact_name', $row['ref_contact_name'], $expReferrer)}
        ";

        $optionArr = array(
             1 => 'Very Low'
            ,2 => 'Low'
            ,3 => 'Normal'
            ,4 => 'High'
            ,5 => 'Very High'
        );
        
        $expRating1['hoverTipDefault'] = ($row['rating_1'] > 0) ? $optionArr[$row['rating_1']] : 'Click to Rate';
        $expRating2['hoverTipDefault'] = ($row['rating_2'] > 0) ? $optionArr[$row['rating_2']] : 'Click to Rate';
        $expRating3['hoverTipDefault'] = ($row['rating_3'] > 0) ? $optionArr[$row['rating_3']] : 'Click to Rate';
        $expRating4['hoverTipDefault'] = ($row['rating_4'] > 0) ? $optionArr[$row['rating_4']] : 'Click to Rate';
        
        $expRating1['optionArr'] = $optionArr;
        $expRating2['optionArr'] = $optionArr;
        $expRating3['optionArr'] = $optionArr;
        $expRating4['optionArr'] = $optionArr;
        
        $fieldset3 = "
        {$formObj->getStarRatingRow('Value to You',   'rating_1', $row['rating_1'], false, $expRating1)}
        {$formObj->getStarRatingRow('Value to Them',  'rating_2', $row['rating_2'], false, $expRating2)}
        {$formObj->getStarRatingRow('Competitiveness','rating_3', $row['rating_3'], false, $expRating3)}
        {$formObj->getStarRatingRow('Relationship',   'rating_4', $row['rating_4'], false, $expRating4)}
        ";

        $currency = '';
        $base_value = '';
        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            $sqlStatus = $fn->getValueListSQL('currency');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlStatus, $row['currency'], $expVl);
            $base_value = $formObj->getTBRow("Estimated Value ({$cpCfg['m.project.baseCurrency']})", 'estimated_value_base', $row['estimated_value_base'], $expNum);
        }

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "status = 'Current' AND staff_type='Project Manager'"));
        $expPM = array('detailValue' => $row['project_manager_name']);

        $fieldset4 = "
        {$currency}
        {$formObj->getTBRow('Estimated Value', 'estimated_value', $row['estimated_value'], $expNum)}
        {$base_value}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getTARow('Notes', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Source', $fieldset2)}
        {$formObj->getFieldSetWrapped('Rating', $fieldset3)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        <input type='hidden' id='hasQuotingModule' value='{$cpCfg['m.project.hasQuotingModule']}' />
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlComboClientCompany  = $fn->getDDSql('project_company');
        $sqlComboStaffName = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

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

        $branch = '';
        if ($cpCfg['m.project.hasMultiBranches'] == 1){
            $modBranch = getCPModuleObj('project_branch');
            $sqlBranch = $modBranch->model->getBranchSQL();

            $branch = $formObj->getDDRowBySQL('Branch', 'branch_id', $sqlBranch);
        }

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComboClientCompany)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlComboStaffName)}
        {$formObj->getDDRowBySQL('Opportunity Category', 'category', $sqlCat, '', $expVl)}
        {$formObj->getDateRangeRow('Enquiry Date', 'enquiry_date')}
        {$formObj->getDateRangeRow('Follow up Date', 'follow_up_date')}
        {$formObj->getDDRowBySQL('Rating', 'chance', $sqlChance, '', $expVl)}
        {$branch}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
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
        if ($row['project_id'] != ''){
            $formObj->mode = 'detail';
            $tv['action'] = 'detail';
        }
        
        if ($cpCfg['m.project.hasQuotingModule'] == 1 && ($tv['action'] == "edit" || $tv['action'] == "detail")) {
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

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'project_opportunity', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('project_opportunity', 'core_staffLink', $cpCfg['m.project.staffFieldLabel'], $row)}
        {$displayLinkData->getLinkPortalMain('project_opportunity', 'project_taskLink', 'Tasks', $row)}
        {$quotingModule}
        {$comment->getView(array(
             'roomName' => 'project_opportunity'
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
        $category           = $fn->getReqParam('category');
        $company_id         = $fn->getReqParam('company_id');
        $project_manager_id = $fn->getReqParam('project_manager_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name 
        FROM company a
        JOIN opportunity b ON (a.company_id = b.company_id)
        WHERE a.category = 'Client' 
        ORDER BY company_name
        ";

        $SQLPM = $fn->getDDSql('core_staff', array('condn' => "status = 'Current' AND staff_type='Project Manager'"));
        $SQLStf = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $SQLStatus = $fn->getValueListSQL('opportunityStatus');
        $SQLChance = $fn->getValueListSQL('opportunityChance');
        $sqlCat    = $fn->getValueListSQL('projectCategory');

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

        $text = "
        <td>
            <select name='company_id' class='w100'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>

        <td>
            <select name='project_manager_id' class='w100'>
                <option value=''>Project Manager</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLPM, $project_manager_id)}
            </select>
        </td>
        <!--
        <td>
            <select name='staff_id' class='w100'>
                <option value=''>{$cpCfg['m.project.staffFieldLabel']}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStf, $tv['staff_id'])}
            </select>
        </td>
        -->
        {$branch}
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
            <select name='chance'>
                <option value=''>Rating</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLChance, $chance)}
            </select>
        </td>
        <td>
            <select name='yearMonthStart'>
                <option value=''>Month</option>
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