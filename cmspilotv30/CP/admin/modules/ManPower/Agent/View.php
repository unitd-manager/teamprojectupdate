<?
class CP_Admin_Modules_ManPower_Agent_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $company = "<a href='index.php?_topRm=project&module=project_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['agent_code'])}
            {$listObj->getGoToDetailText($count, strtoupper($row['first_name']))}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['c_phone'])}
            {$listObj->getListDataCell($row['phone_direct'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['agent_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subcontractor Code', 'a.agent_code')}
        {$listObj->getListHeaderCell('Company Name', 'a.first_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Website', 'a.website')}
        {$listObj->getListHeaderCell('Category', 'category')}
        {$listObj->getListHeaderCell('Phone (Main)', 'b.phone')}
        {$listObj->getListHeaderCell('Phone (Direct)', 'a.phone_direct')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
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

        $fielset = "
        {$formObj->getTBRow('Company Name', 'first_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $expNoEdit = array('isEditable' => 0);

        $chineseName    = '';
        $chinesePos     = '';
        $chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $sqlCategory    = $fn->getValueListSQL('agentCategory');
        $sqlTitle       = $fn->getValueListSQL('salutation');
        $sqlComp        = $fn->getDDSql('manPower_company');

        if ($cpCfg['m.manPower.agent.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.manPower.hasMultipleCompanyAddress'] == 1){
                $sqlCombo = "
                SELECT company_address_id
                      ,CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
                FROM  company_address a
                WHERE company_id = '{$row['company_id']}'
                ORDER BY company_address_id
                ";
                $compAddressDD = "
                {$formObj->getDDRowBySQL('Company Address', 'company_address_id', $sqlCombo, $row['company_address_id'])}
                ";
            }
        }

        if ($cpCfg['m.manPower.agent.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            $fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.project.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.project.staffFieldLabel'], $fieldset);
        }

        if ($cpCfg['m.manPower.agent.showPersonalAddress'] == 1){
            $fieldset = "
            {$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
            {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
            {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
            {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            ";

            $personalAdd = $formObj->getFieldSetWrapped('Personal Address', $fieldset);
        }

        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Subcontractor Code', 'agent_code', $row['agent_code'],$expNoEdit)}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        <!--{$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}-->
        {$formObj->getTBRow('Company Name *', 'first_name', $row['first_name'])}
        {$chineseName}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Username / Email', 'email', $row['email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getDateRow('Contract Date', 'contract_date', $row['contract_date'])}
        {$formObj->getTBRow('NRIC No', 'nric_no', $row['nric_no'])}
        {$formObj->getTBRow('Passport No', 'passport_no', $row['passport_no'])}
        ";

        if ($tv['action'] == 'detail'){
            if($cpCfg['m.manPower.hasMultipleCompanyAddress'] == 1){
                $companyAddress = "
                {$formObj->getTBRow('Flat / Building', 'comp_mul_address_flat', $row['comp_mul_address_flat'])}
                {$formObj->getTBRow('Street Address', 'comp_mul_address_street', $row['comp_mul_address_street'])}
                {$formObj->getTBRow('District/ Town', 'comp_mul_address_town', $row['comp_mul_address_town'])}
                {$formObj->getTBRow('State/ Zip', 'comp_mul_address_state', $row['comp_mul_address_state'])}
                {$formObj->getTBRow('Country', 'comp_mul_address_country', $row['comp_mul_address_country'])}
                ";
            } else {
                $companyAddress = "
                {$formObj->getTBRow('Main Phone', 'c_phone', $row['c_phone'])}
                {$formObj->getTBRow('Main Fax', 'c_fax', $row['c_fax'])}
                {$formObj->getTBRow('Flat/Apartment/House', 'c_address_flat', $row['c_address_flat'])}
                {$formObj->getTBRow('Street Address', 'c_address_street', $row['c_address_street'])}
                {$formObj->getTBRow('Town/ Suburb', 'c_address_town', $row['c_address_town'])}
                {$formObj->getTBRow('State', 'c_address_state', $row['c_address_state'])}
                {$formObj->getTBRow('Country', 'c_address_country', $row['c_address_country'])}
                ";
            }
        }

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_agent', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);


        $fielset2 = "
        ";

        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];
        $sqlStatus  = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['company_country_name']);

		$fielset3 ="
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        <!--{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}-->
        {$compAddressDD}
        {$companyAddress}
        {$chinesePos}
        <!--{$formObj->getTBRow('Department', 'department', $row['department'])}-->
        {$chineseDept}
        {$formObj->getTBRow('Address 1', 'company_address_flat', $row['company_address_flat'])}
        {$formObj->getTBRow('Address 2', 'company_address_street', $row['company_address_street'])}
        {$formObj->getTBRow('City', 'company_address_town', $row['company_address_town'])}
        {$formObj->getDDRowByArr('State', 'company_address_state', $cpCfg['m.manPower.project.stateListArr'], $row['company_address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_country_code', $row['address_country_code'])}
        {$formObj->getDDRowBySQL('Country', 'company_address_country', $sqlCountry, $row['company_address_country'], $expCountry)}
		";

        $fielset4 = "
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $subscribed)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Subcontractor Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fielset3)}
        {$staffDetail}
        {$personalAdd}
        {$formObj->getFieldSetWrapped('Other Details', $fielset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }


    /**
     *
     */
    function getSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textPublished  = "";

        $sqlCompany = $fn->getDDSql('project_company');
        $sqlInterest = $fn->getDDSql('common_interest');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email' )}
        ";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany)}
        {$formObj->getTBRow('Position', 'position')}
        ";

        $fielset3 = "
        {$formObj->getYesNoDropDownRow('Subscribed', 'subscribe')}
        {$formObj->getDDRowBySQL('Interst Group', 'interest_id', $sqlInterest)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Agent Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $agent_id       = $fn->getReqParam('agent_id');

        $rows = "";
        //$candidate_country_id = $fn->getReqParam('candidate_country_id');

        if( $cpCfg['m.manPower.agent.showInterest'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("manPower_agent", "common_interestLink", "Interests Linked", $row);
        }

        if( $cpCfg['m.manPower.agent.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("manPower_agent", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'agent_id');

		$agentDocumentLink ='';
		$links ='';
        $contractButton = '';

        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){
            if ($_SESSION['userGroupType'] == 'Super Administrator') {
				$agentDocumentLink = "
		        <div class='header' expanded='1'>
		            Agent Document Link
		        </div>
		        <div class='agentDocs'>
		            {$this->getAgentDocument($row['agent_id'])}
		        </div>
				";
            }

        if ($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator') {
			$urlAgentContract = "index.php?module=manPower_agent&_spAction=printAgentContract&agent_id={$row['agent_id']}&showHTML=0";
			$contractButton = "
	        <div class='floatbox  btnbackground'>
	            <div class='button mb5'>
	                <a href='{$urlAgentContract}' id='agentContract'>Agent Contract</a>
	            </div>
	        </div>
			";
        }

            $links ="
            {$displayLinkData->getLinkPortalMain('manPower_agent', 'manPower_contactLink', 'Contacts Linked', $row)}
            {$media->getRightPanelMediaDisplay('Picture', 'manPower_agent', 'picture', $row)}
            <div class='header' expanded='1'>
                Candidate Pass Linked
            </div>
            <div class='canPass'>
                {$this->getCandidatePass($row['agent_id'])}
            </div>
    		{$agentDocumentLink}
            <div class='header' expanded='1'>
                Candidate Country Linked
            </div>
            <div class='canCountry'>
                {$this->getCandidateCountry($row['agent_id'])}
            </div>
            ";

        }
		//$attachementLink ='';

        //if ($_SESSION['userGroupType'] == 'Super Administrator') {
    		/*$attachementLink = "
            {$media->getRightPanelMediaDisplay('Attachments', 'manPower_agent', 'attachment', $row)}
    		";*/
        //}
		//{$attachementLink}


        $text = "
        {$displayLinkData->getLinkPortalMain('manPower_agent', 'manPower_contactLink', 'Contacts Linked', $row)}
        {$rows}
        {$links}
        {$comment->getView(array(
             'roomName' => 'manPower_agent'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
        ))}
        <!--
        -->
        ";

        return $text;
    }

    /**
     *
     */

    function getAgentDocument($agent_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $text= '';

        $SQL = "
        SELECT d.documents_id
              ,d.title
        FROM documents d
        WHERE d.module_name = 'Agent'
        ";
        $result = $db->sql_query($SQL);

        $formAction = "index.php?_topRm=admin&module=manPower_agent&_spAction=agentDocumentSubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $agentDocumentRec = $fn->getRecordByCondition('agent_documents',
                                                      "agent_id = '{$agent_id}' AND documents_id = {$row['documents_id']}");
            $checked = $agentDocumentRec['agent_documents_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='documentChk'>
                <input type='checkbox' name='documents' value='1' {$checked}
                 agent_id='{$agent_id}' documents_id='{$row['documents_id']}' class='agentDocument_{$row['documents_id']}'>
                <label name='documents'>{$row['title']}</label>
            </div>
            <input type='hidden' name='agent_id' value='{$agent_id}' />
            ";
        }

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

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');
        $category       = $fn->getReqParam('category');
        $status         = $fn->getReqParam('status');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";

        $sqlCompany     = $fn->getDDSql('project_company');
        $SQLCategory    = $fn->getValueListSQL('agentCategory');
        $sqlInterest    = $fn->getDDSql('common_interest');

        $companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";

        if ($cpCfg['m.manPower.agent.showCategory'] == 1) {
            $categoryText = "
            <td>
                <select name='category'>
                    <option value=''>Category</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $SQLCategory, $category)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        if ($cpCfg['m.manPower.agent.showInterest'] == 1) {
            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
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
        {$companyText}
        {$categoryText}
        {$interestText}
        <td>
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
    function getCandidateCountry($agent_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $text= '';

        $SQL = "
        SELECT cc.candidate_country_id
              ,cc.title
        FROM candidate_country cc
        ";
        $result = $db->sql_query($SQL);


        $formAction = "index.php?_topRm=admin&module=manPower_agent&_spAction=candidateCountrySubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $agentCountryRec = $fn->getRecordByCondition('agent_country',
                                                      "agent_id = '{$agent_id}' AND candidate_country_id = {$row['candidate_country_id']}");
            $checked = $agentCountryRec['agent_country_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='countryChk'>
                <input type='checkbox' name='country' value='1' {$checked}
                 agent_id='{$agent_id}' candidate_country_id='{$row['candidate_country_id']}' class='agentCountry_{$row['candidate_country_id']}'>
                <label name='country'>{$row['title']}</label>
            </div>
            <input type='hidden' name='agent_id' value='{$agent_id}' />
            ";
        }

        return $text;
    }

    function getCandidatePass($agent_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $text= '';

        $SQL = "
        SELECT cp.candidate_pass_id
              ,cp.title
        FROM candidate_pass cp
        ";
        $result = $db->sql_query($SQL);


        $formAction = "index.php?_topRm=admin&module=manPower_agent&_spAction=candidatePassSubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $agentPassRec = $fn->getRecordByCondition('agent_pass',
                                                      "agent_id = '{$agent_id}' AND candidate_pass_id = {$row['candidate_pass_id']}");
            $checked = $agentPassRec['agent_pass_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='candidatePassChk'>
                <input type='checkbox' name='candidatePass' value='1' {$checked}
                 agent_id='{$agent_id}' candidate_pass_id='{$row['candidate_pass_id']}' class='agentPass_{$row['candidate_pass_id']}'>
                <label name='candidatePass'>{$row['title']}</label>
            </div>
            <input type='hidden' name='agent_id' value='{$agent_id}' />
            ";
        }

        return $text;
    }

}