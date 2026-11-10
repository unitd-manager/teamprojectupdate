<?
class CP_Admin_Modules_EnggCrm_Opportunity_View extends CP_Common_Lib_ModuleViewAbstract
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

            $currency = '';
            if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
                $currency = $row['currency'] . '&nbsp;';
            }

            $enquiry_date = $dateUtil->formatDate($row['enquiry_date'], 'DD MMM YYYY');

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['opportunity_code'])}
            {$listObj->getGoToDetailText($rowCounter, $enquiry_date)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['ref_contact_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            <!-- {$listObj->getListDataCell($currency . number_format($row['estimated_value']), "right")} -->
            {$listObj->getListDateCell($row['follow_up_date'])}
            <!-- {$listObj->getListDataCell($editText)} -->
            {$listObj->getListRowEnd($row['opportunity_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'o.opportunity_code')}
        {$listObj->getListHeaderCell('Enquiry Date', 'o.enquiry_date')}
        {$listObj->getListHeaderCell('Title', 'o.title')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Referrer', 'ref_contact_name')}
        {$listObj->getListHeaderCell('Category', 'o.category')}
        {$listObj->getListHeaderCell('Status', 'o.status')}
        <!-- {$listObj->getListHeaderCell('Est Value', 'o.estimated_value', 'headerRight')} -->
        {$listObj->getListHeaderCell('Follow up Date', 'o.follow_up_date')}
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

        $modOpp = getCPModuleObj('enggCrm_opportunity');
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
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

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

        $expVl   = array('sqlType' => 'OneField');
        $sqlCat  = $fn->getValueListSQL('projectCategory');

        $fieldset1 = "
        {$formObj->getTBRow('Title *', 'title')}
        {$formObj->getDDRowBySQL('Company Name *', 'company_id', $sqlCompany, '', $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact, '', $expCont)}
        {$formObj->getDDRowBySQL('Category *', 'category', $sqlCat,'', $expVl)}
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

        if ($cpCfg['m.enggCrm.oppurtunity.showQuoteRef'] == 1) {
            $quote_ref = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        $sqlComboContact = '';
        if ($row['company_id'] != "") {
            $sqlComboContact = $fn->getDDSql('enggCrm_contact', array('condn' => "company_id = {$row['company_id']}"));
        }

        $sqlComboCompany = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Client'
        ORDER BY company_name
        ";

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
                $projectLink = "index.php?_topRm={$tv['topRm']}&module=enggCrm_project&project_id={$row['project_id']}&_action=detail";
                $linkToProj = "<a href='{$projectLink}'>{$row['project_code']}</a>";
                $projectTxt = $formObj->getTBRow('Project Code', 'project_code', $linkToProj, array('isEditable' => 0));
            }

            $expStatus = array('sqlType' => 'OneField');
            $status  = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
            $status .= $projectTxt;

        } else {

            $SQL2 = "
            SELECT count(*) AS count
            FROM quote
            WHERE opportunity_id = {$row['opportunity_id']}
            ";
            $result2 = $db->sql_query($SQL2);
            $row2 = $db->sql_fetchrow($result2);

            $expStatus = array('sqlType' => 'OneField');
            $status = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
        }

        $notes = '';

        $expNoEdit  = array('isEditable' => 0);
        $expOppCode = array('isEditable' => $cpCfg['m.enggCrm.oppurtunity.codeEditable']);

        $sqlCompany = $fn->getDDSql('enggCrm_company', array('condn' => "category = 'client'"));

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlCat    = $fn->getValueListSQL('projectCategory');
        $sqlChance = $fn->getValueListSQL('opportunityChance');


        $expComp = array();
        $expCont = array();

        $companyLink = "<a href='index.php?_topRm={$tv['topRm']}&module=enggCrm_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";
        $contactLink = "<a href='index.php?_topRm={$tv['topRm']}&module=enggCrm_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";

        if ($tv['action'] == 'edit'){
            $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
            $newCompUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Company'
            w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.opportunity.afterNewCompany'>New</a>";
            $expComp  = array(
                 'notesRight'  => $newCompUrl
                ,'detailValue' => $row['company_name']
                ,'autoSgstModule' => 'enggCrm_company'
                ,'autoSgstSrchFld' => 'company_name'
                ,'autoSgstActualFld' => 'company_id'
                ,'autoSgstActualFldVal' => $row['company_id']
                ,'autoSgstCallBack' => 'cpm.enggCrm.opportunity.loadContactsByCompany'
            );

            $newContactUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0';
            $newContactUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Contact'
            w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.opportunity.afterNewContact'>New</a>";

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
        {$formObj->getTBRow('Title *', 'title', $row['title'])}
        {$quote_ref}
        {$formObj->getDDRowBySQL('Company Name *', 'company_id', $sqlComboCompany, $row['company_id'], $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlComboContact, $row['contact_id'], $expCont)}
        {$formObj->getDateRow('Enquiry Date', 'enquiry_date', $enqDate)}
        {$formObj->getDDRowBySQL('Category *', 'category', $sqlCat, $row['category'], $expVl)}
        {$status}
        {$formObj->getTBRow('Opportunity Value', 'estimated_value', $row['estimated_value'], $expNoEdit)}
        ";

        $expReferrer = array(
             'autoSgstModule' => 'enggCrm_contact'
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


        $currency = '';
        if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
            $sqlStatus = $fn->getValueListSQL('currency');
            $currency = $formObj->getDDRowBySQL('Currency', 'currency', $sqlStatus, $row['currency'], $expVl);
        }

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "status = 'Current' AND staff_type='Project Manager'"));
        $expPM = array('detailValue' => $row['project_manager_name']);

        $fieldset4 = "
        {$currency}
        {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'], $expPM)}
        {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getTARow('Notes', 'description', $row['description'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Source', $fieldset2)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        <input type='hidden' id='hasQuotingModule' value='{$cpCfg['m.enggCrm.hasQuotingModule']}' />
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

        $sqlComboClientCompany  = $fn->getDDSql('enggCrm_company');
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


        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComboClientCompany)}
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlComboStaffName)}
        {$formObj->getDDRowBySQL('Opportunity Category', 'category', $sqlCat, '', $expVl)}
        {$formObj->getDateRangeRow('Enquiry Date', 'enquiry_date')}
        {$formObj->getDateRangeRow('Follow up Date', 'follow_up_date')}
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
        $db = Zend_Registry::get('db');

        $record_id = $fn->getIssetParam($row, 'opportunity_id');

        $sqlForQuoteConvertProj ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$row['opportunity_id']}
        AND quote_status = 'Confirmed'
        ";

        $resultForQuoteItems  = $db->sql_query($sqlForQuoteConvertProj);
        $rowQuoteStatus       = $db->sql_fetchrow($resultForQuoteItems);
        $numRowsForQuote      = $db->sql_numrows($resultForQuoteItems);

        if($numRowsForQuote > 0) {
            $statusConfirmed = 'Yes';
        } else {
            $statusConfirmed = 'Not Confirmed';
        }

       // After clicking the Converted Project button it will be disabled

        $sqlForProj ="
        SELECT p.*
        FROM project p
        WHERE p.opportunity_id = {$row['opportunity_id']}

        ";

        $resultForProject   = $db->sql_query($sqlForProj);
        $rowForProject      = $db->sql_fetchrow($resultForProject);
        $numRowsForProject  = $db->sql_numrows($resultForProject);

        $quoteStatus='';
        $addQuoteBtn = '';
        $projectRecBtn = '';
        $urlprojectRecord = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$rowForProject['project_id']}";

        // THIS CONDITIONS IS USED FOR: ONCE THE QUOTE STATUS IS CONFIRMED. THE CONVERT BUTTON WILL BE APPEARED //
        if($rowQuoteStatus['quote_status'] == 'Confirmed' && $numRowsForProject == 0) {
            $quoteStatus="
            <div class='button float_left'>
                <a href='#' class='convertOppToProject'statusConfirmed='{$statusConfirmed}'>Convert Opp To Project</a>
            </div>
            ";
        }

        // THIS CONDITIONS IS USED FOR: ONCE IT IS CONVERTED TO PROJECT, ADD QUOTE BUTTON WOULD BE HIDE AFTER THAT GO TO PROJECT BUTTON WILL BE SHOWING //
        if($rowForProject['quote_id'] != 0){
            $projectRecBtn="
            <div class='float_left button mb5'>
                <a href='{$urlprojectRecord}' title='Project Record' target='_blank'>Go to Project</a>
            </div>
            ";
        }

        if($numRowsForProject == 0){

            $addQuoteBtn ="
            <div class='float_left button mb5'>
                <a href='#' id='addQuote' opportunity_id='{$row['opportunity_id']}' category='{$row['category']}'>Add Quote</a>
            </div>
            ";
        }

        $text = "
        {$addQuoteBtn}
        {$quoteStatus}
        {$projectRecBtn}
        <div class='mt40'>
        <div id='addLineItemPortalView'>{$this->getAddQuoteFormListView($row['opportunity_id'], $row['category'])}</div>
        </div>
        {$media->getRightPanelMediaDisplay('Attachments', 'enggCrm_opportunity', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('enggCrm_opportunity', 'core_staffLink', $cpCfg['m.enggCrm.staffFieldLabel'], $row)}
        {$comment->getView(array(
             'roomName' => 'enggCrm_opportunity'
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
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN opportunity b ON (a.company_id = b.company_id)
        WHERE a.category = 'Client'
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('opportunityStatus');
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


        $text = "
        <td>
            <select name='company_id' class='w100'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
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

    /**
     *
     */
    function getAddQuoteFormListView($opportunity_id = '', $category = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }

        if($category == ''){
            $category = $fn->getReqParam('category');
        }

        $SQL = "
        SELECT q.*
        FROM `quote` q
        WHERE q.opportunity_id = {$opportunity_id}
        ORDER BY quote_code DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $sqlForQuoteItems ="
            SELECT SUM(quantity * amount) AS quote_amount
                   ,quote_items_id
                   ,SUM(quantity) AS quote_qty
            FROM quote_items
            WHERE quote_id = {$row['quote_id']}
            ";
            $resultForQuoteItems  = $db->sql_query($sqlForQuoteItems);
            $rowQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);
            $numRowsForQuoteItems = $db->sql_numrows($resultForQuoteItems);

            $addLineItemView = '';
            if($rowQuoteItems['quote_amount'] > 0 || $rowQuoteItems['quote_qty']) {
                $addLineItemView = "
                <div class='float_right'>
                    <a href='#' class='quoteLayoutShow'>Hide</a>
                </div>
                ";
            }

            $quoteActions = '';
            $editForQuote = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=editForQuote&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";

            if($category == 'Manpower Supply'){
                $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printLinkForManpowerPdf&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
            }
            else{
                $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printLinkForPdf&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
            }

            $formActionGroupForQuoteLineItem = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=addLineItemForQuoteForm&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";

            if ($row['project_id'] == 0) {
                $quoteActions ="
                <div class='float_box clearfix'>
                    <div class='float_left'>
                        <a class='editForQuote' opportunity_id={$opportunity_id} quote_id = {$row['quote_id']}  href='{$editForQuote}'>Edit</a>
                    </div>
                    <!--<div class='float_left'>
                        <a href='#' class='deleteAddQuote' quote_id='{$row['quote_id']}'>Delete</a>
                    </div>-->
                    <div class='printLink'>
                        <a href='{$urlPrintLinkPdf}' target='_blank'>Print Pdf</a>
                    </div>
                    <!--<div class='float_right'>
                        <a href='{$formActionGroupForQuoteLineItem}' opportunity_id={$opportunity_id} quote_id = {$row['quote_id']} class='addLineItem'>Add Line Item</a>
                    </div>-->
                </div>

                <div class='float_box clearfix'>
                    <div class='float_left duplicateQuote'>
                        <a href='#' class='duplicateQuote' quote_id='{$row['quote_id']}' quote_items_id='{$rowQuoteItems['quote_items_id']}' opportunity_id='{$row['opportunity_id']}'>Duplicate</a>
                    </div>
                    <div class='float_left'>
                        <a href='#' opportunity_id={$opportunity_id} quote_id = {$row['quote_id']} class='addMultipleLineItem'>Add Line Item</a>
                    </div>
                </div>
                ";
            } else {
                $quoteActions ="
                <div class='float_left printLink'>
                    <a href='{$urlPrintLinkPdf}' target='_blank'>Print Pdf</a>
                </div>
                ";
            }

            $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

            $confirmedQuoteStatus = '';
            if($row['quote_status'] == 'Confirmed') {
                $confirmedQuoteStatus = 'confirmedQuote';
            }

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
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
                    <td class='txtRight'>{$quote_amount}</td>
                    <td>{$updation_details}</td>
                    <td class='viewRowWidth'>{$addLineItemView}</td>
                    <td width='30%'>{$quoteActions}</td>
                </tr>
                {$this->getAddLineItemForQuoteListView($opportunity_id, $row['quote_id'], $category)}
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
     *
    */
    function getAddLineItemForQuoteForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $opportunity_id     = $fn->getReqParam('opportunity_id');
        $quote_id           = $fn->getReqParam('quote_id');
        $quote_items_id     = $fn->getReqParam('quote_items_id');

        $formAction = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=addLineItemForQuoteFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addLineItem' method='post' action='{$formAction}'>
            {$formObj->getTARow('Description', 'description')}
            {$formObj->getTBRow('Quantity', 'quantity')}
            {$formObj->getTBRow('Amount', 'amount')}
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
            <input type='hidden' name='quote_id' value='{$quote_id}' />
            <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *Line Item Edit
     */
    function getEditLineItem() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $quote_items_id  = $fn->getReqParam('quote_items_id');
        $opportunity_id  = $fn->getReqParam('opportunity_id');
        $category        = $fn->getReqParam('category');

        $rowQuoteItem = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);

        $exp = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?module=enggCrm_opportunity&_spAction=editLineItemSubmit&lnkRoom={$tv['lnkRoom']}&quote_items_id={$quote_items_id}&opportunity_id={$opportunity_id}&showHTML=0";

        if($category == 'Manpower Supply'){

            $text = "
            <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
                <fieldset>
                    {$formObj->getTBRow('Title', 'title',$rowQuoteItem['title'] )}
                    {$formObj->getTARow('Description', 'description',$rowQuoteItem['description'] )}
                    {$formObj->getTBRow('Hourly Rate', 'quantity',$rowQuoteItem['quantity'])}
                    {$formObj->getTARow('Remarks', 'remarks',$rowQuoteItem['remarks'] )}
                    <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                    <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                </fieldset>
            </form>
            ";

        }else{

            $text = "
            <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
                <fieldset>
                    {$formObj->getTBRow('Title', 'title',$rowQuoteItem['title'] )}
                    {$formObj->getTARow('Description', 'description',$rowQuoteItem['description'] )}
                    {$formObj->getTBRow('Unit', 'unit',$rowQuoteItem['unit'])}
                    {$formObj->getTBRow('Quantity', 'quantity',$rowQuoteItem['quantity'])}
                    {$formObj->getTBRow('Amount', 'amount',$rowQuoteItem['amount'])}
                    {$formObj->getTARow('Remarks', 'remarks',$rowQuoteItem['remarks'] )}
                    <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                    <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                </fieldset>
            </form>
            ";
        }

        return $text;
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
        $opportunity_id   = $fn->getReqParam('opportunity_id');
        $quote_status     = $fn->getReqParam('quote_status');

        $rowQuote         = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $quoteItemsRec    = $fn->getRecordRowByID('quote_items', 'quote_id', $rowQuote['quote_id']);

        $formActionEditForQuote = "index.php?module=enggCrm_opportunity&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&quote_id={$rowQuote['quote_id']}&opportunity_id={$opportunity_id}&showHTML=0";

        $expNoEdit  = array('isEditable' => 0);

        $spArrayQuoteStatus = array('New' ,'Cancelled' ,'Confirmed','Hold');

        $text = "
        <form id='editForQuote' class='yform columnar' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title',$rowQuote['title'])}
                {$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'])}
                {$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'])}
                {$formObj->getTextAreaRow('Terms & Condition', 'condition',$rowQuote['condition'])}
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                <input type='hidden' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddLineItemForQuoteListView($opportunity_id, $quote_id, $category) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT qt.*
        FROM `quote_items` qt
        WHERE qt.opportunity_id = {$opportunity_id}
        AND qt.quote_id = {$quote_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';

            $editText = '';
            $editForLineItem = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=editLineItem&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&quote_items_id={$row['quote_items_id']}&category={$category}&showHTML=0";

            $SQlForQuoteItems = "
            SELECT q.*
            FROM `quote` q
            WHERE q.opportunity_id = {$opportunity_id}
            ";

            $resultForQuoteItems     = $db->sql_query($SQlForQuoteItems);
            $rowForQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);

            $SQlForQuoteItemsConfiredProj ="
            SELECT *
            FROM project
            WHERE quote_id = {$row['quote_id']}
            ";

            $resultForQuoteItemsConfiredProj    = $db->sql_query($SQlForQuoteItemsConfiredProj);
            $rowForQuoteItemsConfirmedProj     = $db->sql_fetchrow($resultForQuoteItemsConfiredProj);

            if ($rowForQuoteItemsConfirmedProj['quote_id'] == 0 && $rowForQuoteItems['project_id'] == 0) {
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
            }

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
                    <td class=''>{$row['quantity']}</td>
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
                    <td class=''>{$row['quantity']}</td>
                    <td class='amountRow'>{$row['amount']}</td>
                    <td class='amountRow'>{$total_amount}</td>
                    <td colspan='2'>{$updation_details}</td>
                    <td>{$editText} {$deleteLineItem}</td>
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
                    <th class='quoteRowBackground txtRight'>Unit Price</th>
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <th colspan='2' class='quoteRowBackground'>Updated By</th>
                    <th class='quoteRowBackground'>Action</th>
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
    function getPrintLinkForPdf() {
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
        //============================================================================= //
        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.remarks
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
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
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $quote_date = $fn->getCPDate($company['quote_date'], 'd-m-Y');

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

        $quo_date = $fn->getCPDate($company['quote_date'], 'ym/');
        $quote_code = $quo_date . substr($company['quote_code'], 2);
        $address_street = "";
        if ($company['billing_address_street']) {
            $address_street = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }
        $tbl2 ='<table border="0" width="100%" cellpadding="">
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">TO: </td>
                        <td width= "40%" colspan="2" align="right" style="font-size:12px; font-weight:bold;">'.$cpCfg['cp.addressPdfRegNo'].'</td>
                    </tr>
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                        <td width="26%" style="font-size:12px; font-weight:bold;" align="right"><b>QUOTATION NO : </b></td>
                        <td width="14%" style="font-size:12px; font-weight:bold;" align="right">'.$company['quote_code'].'</td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_flat']).'</td>
                        <td style="font-size:12px; font-weight:bold;" align="right"><b>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                        <td style="font-size:12px; font-weight:bold;" align="right">'.$quote_date.'</td>
                    </tr>
                    '. $address_street .'
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="font-size:12px; font-weight:bold;">ATTN:&nbsp;' .strtoupper($company['salutation']). ' '.strtoupper($company['first_name']).'</td>
                        <td colspan="2"></td>
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

        $tbl3 ='<table border="1" cellpadding="2" width="100%">
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
                                <td width="5%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="35%" style="font-size:12px;">'.nl2br($row['description']).'</td>
                                <td width="8%" align="center" style="font-size:12px;">'.$row['unit'].'</td>
                                <td width="10%" align="center" style="font-size:12px;">'.$row['quantity'].'</td>
                                <td width="13%" align="center" style="font-size:12px;">'.$row['amount'].'</td>
                                <td width="14%" align="right" style="font-size:12px;">'.$subtotal_amount_formatted.'</td>
                                <td width="15%" style="font-size:12px;">'.$row['remarks'].'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'];
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
                          <td align="right" style="font-size:12px; font-weight:bold;">'.$gstvalue.'</td>
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
                <td border="0" align="left" width="100%" style="font-size:12px; text-decoration:underline; font-weight:bold">TERMS & CONDITIONS:</td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table><br>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td align="left" style="font-size:12px;" width="70%">Yours faithfully</td>
                <td align="right" style="font-size:12px;" width="30%">Confirmed & accepted by</td>
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

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLinkForManpowerPdf() {
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
        //============================================================================= //
        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.remarks
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
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
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $quote_date = $fn->getCPDate($company['quote_date'], 'd-m-Y');

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

        $quo_date = $fn->getCPDate($company['quote_date'], 'ym/');
        $quote_code = $quo_date . substr($company['quote_code'], 2);
        $address_street = "";
        if ($company['billing_address_street']) {
            $address_street = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }
        $tbl2 ='<table border="0" width="100%" cellpadding="">
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">TO: </td>
                        <td width= "40%" colspan="2" align="right" style="font-size:12px; font-weight:bold;">'.$cpCfg['cp.addressPdfRegNo'].'</td>
                    </tr>
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                        <td width="26%" style="font-size:12px; font-weight:bold;" align="right"><b>QUOTATION NO : </b></td>
                        <td width="14%" style="font-size:12px; font-weight:bold;" align="right">'.$company['quote_code'].'</td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_flat']).'</td>
                        <td style="font-size:12px; font-weight:bold;" align="right"><b>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                        <td style="font-size:12px; font-weight:bold;" align="right">'.$quote_date.'</td>
                    </tr>
                    '. $address_street .'
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="font-size:12px; font-weight:bold;">ATTN:&nbsp;' .strtoupper($company['salutation']). ' '.strtoupper($company['first_name']).'</td>
                        <td colspan="2"></td>
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

        $tbl3 ='<table border="1" cellpadding="2" width="100%">
                    <thead>
                        <tr bgcolor="#DDE4FF">
                            <th height="20" style="font-size:12px; font-weight:bold;">DETAILS OF QUOTATION</th>
                        </tr>
                        <tr>
                            <th width="5%" align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                            <th width="35%" align="center" style="font-size:12px; font-weight:bold;">DESCRIPTION</th>
                            <th width="25%" align="center" style="font-size:12px; font-weight:bold;">HOURLY RATE</th>
                            <th width="35%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
            $tbl3 = $tbl3.'<tr>
                                <td width="5%"></td>
                                <td width="35%" style="font-size:12px; font-weight:bold;"><u>'.strtoupper($row['quote_item_title']).'</u></td>
                                <td width="25%"></td>
                                <td width="35%"></td>
                            </tr>
                    ';
            }


            $tbl3 = $tbl3.'<tr>
                                <td width="5%" align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="35%" style="font-size:12px;">'.nl2br($row['description']).'</td>
                                <td width="25%" align="center" style="font-size:12px;">$'.$row['quantity'].'/hr/pax</td>
                                <td width="35%" style="font-size:12px;">'.$row['remarks'].'</td>
                            </tr>
                        </table>
                    ';

            $count++;
        }

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td border="0" align="left" width="100%" style="font-size:12px; text-decoration:underline; font-weight:bold">TERMS & CONDITIONS:</td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table><br>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td align="left" style="font-size:12px;" width="70%">Yours faithfully</td>
                <td align="right" style="font-size:12px;" width="30%">Confirmed & accepted by</td>
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

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn = Zend_Registry::get('fn');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $quote_id       = $fn->getReqParam('quote_id');

        $title       = "<input type='text' value='' id='title' class='text lineItemTitle' name='title[]'>";
        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
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

        $formAction = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=addMultipleLineItemSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
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
        $remarks     = "<textarea value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
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

}