<?
class CP_Admin_Modules_ManPower_CandidateLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        
        $rows       = '';
        $rowCounter = 0;
        $company = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            <td><div align='left'><a href='#' class='candidateDetails' candidate_id='{$row['candidate_id']}'>{$row['first_name']}</a></div></td>
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['travel_document_no'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['candidate_id'])}
            ";
            $rowCounter++ ;
        }


        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'c.first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Travel Document No', 'c.travel_document_no')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $candidateLink = '';

        $sqlProStatus = $fn->getValueListSQL('processStatus','valuelist_id');
        $sqlPercent   = $fn->getValueListSQL('percent');
        $sqlCandidate = $fn->getDDSql('manPower_candidate');
        $sqlAgent     = $fn->getDDSql('manPower_agent');

        /*
        $sqlCandidate   = "
        SELECT distinct c.candidate_id 
        ,c.first_name
        FROM candidate c
        JOIN (opportunity_candidate oc) ON ( oc.candidate_id = c.candidate_id )
        WHERE (oc.process_status = 'Rejected In Interview (Staff)'
        OR oc.process_status = '')
        ";
        */

        //$site_id = $_SESSION['cp_site_id'];
 
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('candidate', 'candidate_id', $id);

        $candidateLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_opportunity', 'manPower_candidateLink', 'fld_candidate_id')}'>Choose</a>";
        $expCandidate  = array('notesRight' => $candidateLink);
        $expNoEdit  = array('isEditable' => 0);
        //$expAgent   = array('hideFirstOption' => true);

        $text = "
        <form id='portalForm' class='yform columnar candidateLink' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>
                {$formObj->getDDRowBySQL('Candidate Name', 'candidate_id', $sqlCandidate, '', $expCandidate)}
                {$formObj->getTBRow('Passport NO', 'passport_no', '', $expNoEdit)}
                {$formObj->getDDRowBySQL('Process Status', 'process_status', $sqlProStatus, '', $exp)}
                {$formObj->getDDRowBySQL('Percent', 'percent_win', $sqlPercent, '', $exp)}
                {$formObj->getTBRow('Agent Code', 'agent_name', '', $expNoEdit)}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='srcRoom' value='{$tv['srcRoom']}' />
        </form>
        ";

        return $text;
    }

    /**
     */
    function getAddCandidate(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $candidateLink = '';
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $sqlProStatus   = $fn->getValueListSQL('processStatus','valuelist_id');
        $sqlPercent     = $fn->getValueListSQL('percent');
        //$sqlCandidate   = $fn->getDDSql('manPower_candidate');
        $sqlAgent       = $fn->getDDSql('manPower_agent');

        $formAction = "index.php?_topRm=finance&module=manPower_opportunity&_spAction=addCandidateFormSubmit&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $sqlCandidate = "SELECT  candidate_id
                                ,CONCAT_WS(' ', first_name, last_name ) AS candidate_name
                        FROM candidate
                        WHERE (edit_locked IS NULL OR edit_locked = 0)";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('candidate', 'candidate_id', $id);

        $candidateLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_opportunity', 'manPower_candidateLink', 'fld_candidate_id')}'>Choose</a>";
        $expCandidate  = array('notesRight' => $candidateLink);
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar candidateLink' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>
                {$formObj->getDDRowBySQL('Candidate Name', 'candidate_id', $sqlCandidate, '', $expCandidate)}
                {$formObj->getTBRow('Passport NO', 'passport_no', '', $expNoEdit)}
                {$formObj->getDDRowBySQL('Process Status', 'process_status', $sqlProStatus, 'Interview Schedule Process', $exp)}
                {$formObj->getDDRowBySQL('Percent', 'percent_win', $sqlPercent, '', $exp)}
                {$formObj->getTBRow('Subcontractor Code', 'agent_name', '', $expNoEdit)}
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}'>
            </fieldset>
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getEditCandidate(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $sqlProStatus = $fn->getValueListSQL('processStatus','sort_order');
        $sqlPercent   = $fn->getValueListSQL('percent');
        //$sqlCandidate = $fn->getDDSql('manPower_candidate');
        $sqlAgent     = $fn->getDDSql('manPower_agent');

        $sqlCandidate = "SELECT  candidate_id
                                ,CONCAT_WS(' ', first_name, last_name ) AS candidate_name
                        FROM candidate
                        WHERE (edit_locked IS NULL OR edit_locked = 0)";

        $formAction = "index.php?_topRm=finance&module=manPower_opportunity&_spAction=editCandidateFormSubmit&showHTML=0";
        //$formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);
        $expAgent   = array('hideFirstOption' => true);

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('opportunity_candidate', 'opportunity_candidate_id', $id);

        $candidateLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_opportunity', 'manPower_candidateLink', 'fld_candidate_id')}'>Choose</a>";
        $expCandidate  = array('notesRight' => $candidateLink);

        $passport_no = '';
        $agent_name  = '';
        if ($row['candidate_id']) {
            $candidateRec = $fn->getRecordByCondition('candidate', "candidate_id = {$row['candidate_id']}");
            $passport_no = $candidateRec['travel_document_no'];

            $sql = "
            SELECT a.agent_code
            FROM agent a
            LEFT JOIN (candidate c) ON (a.agent_id = c.agent_id)
            WHERE c.candidate_id = {$row['candidate_id']}
            ";
            $result   = $db->sql_query($sql);
            $rowAgent = $db->sql_fetchrow($result);

            $agent_name = $rowAgent['agent_code'];
        }

        $text = "
        <form id='portalForm' class='yform columnar candidateLink' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>
                {$formObj->getDDRowBySQL('Name', 'candidate_id', $sqlCandidate, $row['candidate_id'], $expCandidate)}
                {$formObj->getTBRow('Passport NO', 'passport_no', $passport_no, $expNoEdit)}
                {$formObj->getDDRowBySQL('Process Status', 'process_status', $sqlProStatus, $row['process_status'], $expVl)}
                {$formObj->getDDRowBySQL('Percent', 'percent_win', $sqlPercent, $row['percent_win'], $expVl)}
                {$formObj->getTBRow('Agent Code', 'agent_name', $agent_name, $expNoEdit)}
                {$formObj->getCreationModificationText($row)}
                <input type='hidden' name='opportunity_candidate_id' value='{$id}' />
                <input type='hidden' id='edit_opportunity_id' name='opportunity_id' value='{$row['opportunity_id']}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $sqlProStatus = $fn->getValueListSQL('processStatus','sort_order');
        $sqlPercent   = $fn->getValueListSQL('percent');
        $sqlCandidate = $fn->getDDSql('manPower_candidate');
        $sqlAgent     = $fn->getDDSql('manPower_agent');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);
        $expAgent   = array('hideFirstOption' => true);

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('opportunity_candidate', 'opportunity_candidate_id', $id);
        
        $candidateLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_opportunity', 'manPower_candidateLink', 'fld_candidate_id')}'>Choose</a>";
        $expCandidate  = array('notesRight' => $candidateLink);

        $passport_no = '';
        $agent_name  = '';
        if ($row['candidate_id']) {
            $candidateRec = $fn->getRecordByCondition('candidate', "candidate_id = {$row['candidate_id']}");
            $passport_no = $candidateRec['travel_document_no'];          

            $sql = "
            SELECT a.agent_code
            FROM agent a
            LEFT JOIN (candidate c) ON (a.agent_id = c.agent_id)
            WHERE c.candidate_id = {$row['candidate_id']}
            ";
            $result   = $db->sql_query($sql);
            $rowAgent = $db->sql_fetchrow($result);
            
            $agent_name = $rowAgent['agent_code'];
        }
        
        $text = "
        <form id='portalForm' class='yform columnar candidateLink' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expNoEdit)}</div>
                {$formObj->getDDRowBySQL('Name', 'candidate_id', $sqlCandidate, $row['candidate_id'], $expCandidate)}
                {$formObj->getTBRow('Passport NO', 'passport_no', $passport_no, $expNoEdit)}
                {$formObj->getDDRowBySQL('Process Status', 'process_status', $sqlProStatus, $row['process_status'], $expVl)}
                {$formObj->getDDRowBySQL('Percent', 'percent_win', $sqlPercent, $row['percent_win'], $expVl)}
                {$formObj->getTBRow('Agent Code', 'agent_name', $agent_name, $expNoEdit)}
            </fieldset>
            <input type='hidden' name='opportunity_candidate_id' value='{$id}' />
            <input type='hidden' name='srcRoom' value='{$tv['srcRoom']}' />
            <input type='hidden' name='opportunity_id' value='{$row['opportunity_id']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $agent_id       = $fn->getReqParam('agent_id');
        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');
        $position       = $fn->getReqParam('position');
        $status         = $fn->getReqParam('status');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        $SQLStatus      = $fn->getValueListSQL('companyStatus');
        $sqlPosition    = $fn->getValueListSQL('opportunityPosition','value');

        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );


        $text = "
        <td>
            <select name='position'>
                <option value=''>Select Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>
        <td>
            <select name='status' >
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


    /**
     *
     */
    function getQuickSearch1() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $class_id       = $fn->getReqParam('class_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $language       = $fn->getReqParam('language');
        $interestText   = "";
        $languageText   = '';

        $sqlInterest = $fn->getDDSql('common_interest');
        $sqlClass    = $fn->getDDSql('pms_class');

        $interestText = "
        <td>
            <select name='interest_id' >
                <option value=''>Interest Group</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
            </select>
        </td>
        ";

        if ($cpCfg['m.common.contact.showLangPrefernce'] == 1) {
            $languageText = "
            <td>
                <select name='language' >
                    <option value=''>Language Preference</option>
                    {$cpUtil->getDropDown1($cpCfg['cp.availableLanguages'], $language, true)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        {$interestText}
        {$languageText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='class_id' >
                <option value=''>Class</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlClass, $class_id)}
            </select>
        </td>
        ";


        return $text;
    }
}
