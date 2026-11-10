<?
class CP_Admin_Modules_ManPower_Candidate_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $appendSql = "";
        if ($_SESSION['userGroupType'] == 'Agent') {
            $rowAgent  = $fn->getRecordByCondition('agent', "email = '{$_SESSION['email']}'");
            $appendSql = "AND agent_id = {$rowAgent['agent_id']}";
        } else {
            $appendSql = "AND staff_id = {$_SESSION['staff_id']}";
        }

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $company = "<a href='index.php?_topRm=project&module=project_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";

            $sqlPositionCandidate ="
            SELECT position_title
            FROM position_candidate
            WHERE candidate_id={$row['candidate_id']}
            ";
            $resultPosition = $db->sql_query($sqlPositionCandidate);
            $position_list  = '';
            while($rowPost = $db->sql_fetchrow($resultPosition)){
                $position = $rowPost['position_title'];
                $position_list .= $position.", ";
            }
            $position_list = trim($position_list, ', ');
            $positionTitle ="{$position_list}";

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, strtoupper($row['first_name']))}
            {$listObj->getGoToDetailText($count, strtoupper($row['last_name']))}
            {$listObj->getListDataCell("<a href='mailto:{$row['email_address']}'>{$row['email_address']}</a>")}
            {$listObj->getListDataCell($row['agent_name'])}
            {$listObj->getListDataCell($row['candidate_mobile_no'])}
            {$listObj->getListDataCell($positionTitle)}
            {$listObj->getListDataCell($fn->getYesNo($row['edit_locked']), 'center')}
            {$listObj->getListRowEnd($row['candidate_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Email', 'c.email_address')}
        {$listObj->getListHeaderCell('Agent Name', 'agent_name')}
        {$listObj->getListHeaderCell('Mobile', 'c.candidate_mobile_no')}
        {$listObj->getListHeaderCell('Postion', '')}
        {$listObj->getListHeaderCell('Locked', 'c.edit_locked')}
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
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
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
        $candidate_id   = $fn->getReqParam('candidate_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=manPower_candidate&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='candidate_id' value='{$candidate_id}' />
        </form>
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
        $dateUtil = Zend_Registry::get('dateUtil');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $chineseName    = '';
        $chinesePos     = '';
        $chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $agentDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $sqlTitle         	       	 = $fn->getValueListSQL('salutation');
        $sqlComp          	       	 = $fn->getDDSql('manPower_company');
        $sqlSex        	  	       	 = $fn->getValueListSQL('candidateSex');
        $sqlMarialStatus  	       	 = $fn->getValueListSQL('candidateMarialStatus');
        $sqlRace          	       	 = $fn->getValueListSQL('candidateRace');
        $sqlReligion      	       	 = $fn->getValueListSQL('candidateReligion','value');
        $sqlCandidateModeOfStudy   	 = $fn->getValueListSQL('candidateModeOfStudy');
		$sqlCandidateQualification 	 = $fn->getValueListSQL('candidateQualification');
		$sqlCandidatePeriodOfWorking = $fn->getValueListSQL('candidatePeriodOfWorking','sort_order');
		$sqlTravelDocumentType 		 = $fn->getValueListSQL('candidateTravelDocumentType');
		$sqlFaculty 		         = $fn->getValueListSQL('candidateFaculty', 'sort_order');
		$sqlSpecialisation 		     = $fn->getValueListSQL('candidateSpecialisation', 'sort_order');
        $sqlPosition                 = $fn->getValueListSQL('opportunityPosition','value');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);


        if ($cpCfg['m.manPower.contact.showChineseFields'] == 1){
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

        $appendComboSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendComboSql = "WHERE a.site_id = '{$_SESSION['cp_site_id']}'";
        }

        if ($cpCfg['m.manPower.contact.showDetail'] == 1){
            $sqlCombo = "
            SELECT agent_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
            FROM agent a
            {$appendComboSql}
            ORDER BY agent_name";

            $exp = array('isEditable' => 0, 'detailValue' => $row['agent_name']);
            $expedit = array('detailValue' => $row['agent_name']);
            if ($_SESSION['userGroupType'] == 'Agent') {
                $fieldset = "
                {$formObj->getDDRowBySQL("{$cpCfg['m.manPower.agentFieldLabel']}", "agent_id", $sqlCombo, $row['agent_id'], $exp)}
                ";
            } else {
                $fieldset = "
                {$formObj->getDDRowBySQL("{$cpCfg['m.manPower.agentFieldLabel']}", "agent_id", $sqlCombo, $row['agent_id'], $expedit)}
                ";
            }

            $agentDetail = $fieldset;

        }

        if ($cpCfg['m.manPower.contact.showPersonalAddress'] == 1){
            $fieldset = "
			    {$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}
			    {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
			    {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
			    {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
			    {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            ";

            //$personalAdd = $formObj->getFieldSetWrapped('Personal Address', $fieldset);
        }

		$locked = '';
        if ($_SESSION['userGroupType'] != 'Agent') {
			$locked = $formObj->getYesNoRRow('Locked', 'edit_locked', $row['edit_locked']);
    	}

        $expVl = array('sqlType' => 'OneField');
        $expPeriod = array('fld_period' => 'W200');
        $expNoEdit = array('isEditable' => 0);
        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=manPower_candidate&_spAction=addNewValuelistForm&valuelist_name=opportunityPosition&candidate_id={$row['candidate_id']}&showHTML=0";
        $expPosition = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='opportunityPosition'>Add</a>");

        $position = "
        <div class='positionTitle'>
         {$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}
        </div>
        ";

        $date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'DD MM YYYY');

        /*$addressCandidate = "
            {$formObj->getTBRow('Address', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
            {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
            {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        ";*/

        $addressCandidate = "
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('City', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $fielset1 = "
		{$locked}
        {$formObj->getTBRow('Candidate Code', 'candidate_code', $row['candidate_code'], $expNoEdit)}
        {$agentDetail}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('First Name *', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name *', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Mobile Number', 'candidate_mobile_no', $row['candidate_mobile_no'])}
        {$formObj->getTBRow('E-mail Address', 'email_address', $row['email_address'])}
        {$formObj->getDDRowBySQL('Sex', 'sex', $sqlSex, $row['sex'], $expVl)}
        {$formObj->getTBRow('SSN', 'ssn', $row['ssn'])}
        {$formObj->getTBRow('No of withholding', 'no_of_withholding', $row['no_of_withholding'])}
        {$formObj->getDateRow('Date Of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1940, 'yearEnd' => 2040))}
        {$formObj->getDDRowBySQL('Marital Status', 'martial_status', $sqlMarialStatus, $row['martial_status'], $expVl)}
        {$formObj->getTBRow('Home Phone Number', 'home_no', $row['home_no'])}
        {$formObj->getTBRow('Father / Mother / Wife Mobile Number', 'father_mother_no', $row['father_mother_no'])}
        {$addressCandidate}
        ";

        $fielsetOtherDetails = "
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlCountry, $row['nationality'], $expCountry)}
        {$formObj->getDDRowBySQL('Country of  Birth', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        {$formObj->getDDRowBySQL('Country of  Origin', 'address_country1', $sqlCountry, $row['address_country1'], $expCountry)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVl)}
        {$formObj->getDDRowBySQL('Religion', 'religion', $sqlReligion, $row['religion'], $expVl)}
        {$formObj->getDateRow('Contract Date', 'contract_date', $row['contract_date'])}
        {$chineseName}
        ";

        $fielset4 = "
        {$formObj->getDDRowBySQL('Travel Document Type', 'travel_document_type', $sqlTravelDocumentType, $row['travel_document_type'], $expVl)}
        {$formObj->getTBRow('Travel Document No', 'travel_document_no', $row['travel_document_no'])}
        {$formObj->getDateRow('Date Of Expiry', 'date_of_expiry', $row['date_of_expiry'], array('yearStart' => 1940, 'yearEnd' => 2040))}
        ";

		$specialisation1 = '';
		$specialisation2 = '';
		$specialisation3 = '';
		$specialisation4 = '';
		$specialisation5 = '';
		if($row['education_specialisation1'] == 'NONE OF THE ABOVE'){
			$specialisation1 = 'specify';
		}

		if($row['education_specialisation2'] == 'NONE OF THE ABOVE'){
			$specialisation2 = 'specify';
		}

		if($row['education_specialisation3'] == 'NONE OF THE ABOVE'){
			$specialisation3 = 'specify';
		}

		if($row['education_specialisation4'] == 'NONE OF THE ABOVE'){
			$specialisation4 = 'specify';
		}

		if($row['education_specialisation5'] == 'NONE OF THE ABOVE'){
			$specialisation5 = 'specify';
		}
        $fielset5 = "
        <div class='linkPortalWrapper'>
            <div class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Education Details </div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getDDRowBySQL('University Country', 'education_country1', $sqlCountry, $row['education_country1'])}
                    {$formObj->getTBRow('Name', 'degree_name1', $row['degree_name1'])}
                    {$formObj->getTBRow('Main Campus or Affiliating College Attended', 'college_name1', $row['college_name1'])}
                    {$formObj->getDDRowBySQL('Qualification', 'education_qualification1', $sqlCandidateQualification, $row['education_qualification1'], $expVl)}
                    {$formObj->getDDRowBySQL('Faculty', 'education_faculty1', $sqlFaculty, $row['education_faculty1'], $expVl)}
                    {$formObj->getDDRowBySQL('Specialisation', 'education_specialisation1', $sqlSpecialisation, $row['education_specialisation1'], $expVl)}
					<div class = 'specialisation {$specialisation1}'>
	                    {$formObj->getTBRow('If None of the Above, please specify', 'education_none_of_the_above1', $row['education_none_of_the_above1'])}
				    </div>
                    {$formObj->getDDRowBySQL('Mode of Study', 'mode_of_study1', $sqlCandidateModeOfStudy, $row['mode_of_study1'], $expVl)}
					<div class='subcolumns'>
						<div class='dateFrom'>
		                  {$formObj->getDateRow('Period of Study From', 'period_of_study_from1', $row['period_of_study_from1'], array('yearStart' => 1940, 'yearEnd' => 2040))}
						</div>
						<div class='dateTo'>
	                      {$formObj->getDateRow('', 'period_of_study_to1', $row['period_of_study_to1'], array('yearStart' => 1940, 'yearEnd' => 2040))}
						</div>
   				    </div>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Education Details 2</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getDDRowBySQL('University Country', 'education_country2', $sqlCountry, $row['education_country2'])}
                    {$formObj->getTBRow('Name', 'degree_name2', $row['degree_name2'])}
                    {$formObj->getTBRow('Main Campus or Affiliating College Attended', 'college_name2', $row['college_name2'])}
        	        {$formObj->getDDRowBySQL('Qualification', 'education_qualification2', $sqlCandidateQualification, $row['education_qualification2'], $expVl)}
                    {$formObj->getTBRow('Faculty', 'education_faculty2', $row['education_faculty2'])}
                    {$formObj->getDDRowBySQL('Specialisation', 'education_specialisation2', $sqlSpecialisation, $row['education_specialisation2'], $expVl)}
					<div class = 'specialisation {$specialisation2}'>
	                    {$formObj->getTBRow('If None of the Above, please specify', 'education_none_of_the_above2', $row['education_none_of_the_above2'])}
				    </div>
                    {$formObj->getDDRowBySQL('Mode of Study', 'mode_of_study2', $sqlCandidateModeOfStudy, $row['mode_of_study2'], $expVl)}
					<div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period of Study From', 'period_of_study_from2', $row['period_of_study_from2'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'period_of_study_to2', $row['period_of_study_to2'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
   				    </div>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Education Details 3</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getDDRowBySQL('University Country', 'education_country3', $sqlCountry, $row['education_country3'])}
                    {$formObj->getTBRow('Name', 'degree_name3', $row['degree_name3'])}
                    {$formObj->getTBRow('Main Campus or Affiliating College Attended', 'college_name3', $row['college_name3'])}
        	        {$formObj->getDDRowBySQL('Qualification', 'education_qualification3', $sqlCandidateQualification, $row['education_qualification3'], $expVl)}
                    {$formObj->getTBRow('Faculty', 'education_faculty3', $row['education_faculty3'])}
                    {$formObj->getDDRowBySQL('Specialisation', 'education_specialisation3', $sqlSpecialisation, $row['education_specialisation3'], $expVl)}
					<div class = 'specialisation {$specialisation3}'>
	                    {$formObj->getTBRow('If None of the Above, please specify', 'education_none_of_the_above3', $row['education_none_of_the_above3'])}
				    </div>
                    {$formObj->getDDRowBySQL('Mode of Study', 'mode_of_study3', $sqlCandidateModeOfStudy, $row['mode_of_study3'], $expVl)}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period of Study From', 'period_of_study_from3', $row['period_of_study_from3'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'period_of_study_to3', $row['period_of_study_to3'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Education Details 4</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getDDRowBySQL('University Country', 'education_country4', $sqlCountry, $row['education_country4'])}
                    {$formObj->getTBRow('Name', 'degree_name4', $row['degree_name4'])}
                    {$formObj->getTBRow('Main Campus or Affiliating College Attended', 'college_name4', $row['college_name4'])}
        	        {$formObj->getDDRowBySQL('Qualification', 'education_qualification4', $sqlCandidateQualification, $row['education_qualification4'], $expVl)}
                    {$formObj->getTBRow('Faculty', 'education_faculty4', $row['education_faculty4'])}
                    {$formObj->getDDRowBySQL('Specialisation', 'education_specialisation4', $sqlSpecialisation, $row['education_specialisation4'], $expVl)}
					<div class = 'specialisation {$specialisation4}'>
	                    {$formObj->getTBRow('If None of the Above, please specify', 'education_none_of_the_above4', $row['education_none_of_the_above4'])}
				    </div>
                    {$formObj->getDDRowBySQL('Mode of Study', 'mode_of_study4', $sqlCandidateModeOfStudy, $row['mode_of_study4'], $expVl)}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period of Study From', 'period_of_study_from4', $row['period_of_study_from4'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'period_of_study_to4', $row['period_of_study_to4'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Education Details 5</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getDDRowBySQL('University Country', 'education_country5', $sqlCountry, $row['education_country5'])}
                    {$formObj->getTBRow('Name', 'degree_name5', $row['degree_name5'])}
                    {$formObj->getTBRow('Main Campus or Affiliating College Attended', 'college_name5', $row['college_name5'])}
        	        {$formObj->getDDRowBySQL('Qualification', 'education_qualification5', $sqlCandidateQualification, $row['education_qualification5'], $expVl)}
                    {$formObj->getTBRow('Faculty', 'education_faculty5', $row['education_faculty5'])}
                    {$formObj->getDDRowBySQL('Specialisation', 'education_specialisation5', $sqlSpecialisation, $row['education_specialisation5'], $expVl)}
					<div class = 'specialisation {$specialisation5}'>
	                    {$formObj->getTBRow('If None of the Above, please specify', 'education_none_of_the_above5', $row['education_none_of_the_above5'])}
				    </div>
                    {$formObj->getDDRowBySQL('Mode of Study', 'mode_of_study5', $sqlCandidateModeOfStudy, $row['mode_of_study5'], $expVl)}
					<div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period of Study From', 'period_of_study_from5', $row['period_of_study_from5'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'period_of_study_to5', $row['period_of_study_to5'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
   				    </div>
                </div>
            </div>
        </div>
        ";

        $fielset6 = "
		<div class='subcolumns'>
		  	<div class='c50l'>
			  	<div class='subcl'>
					<div class = 'texboxPeriod'>
		        	  {$formObj->getTBRow('Total Period of Working Experience (Years Month)', 'period_of_working_year', $row['period_of_working_year'])}
	    	    	  {$formObj->getTBRow('Total Period of Relevant Experience (Years Month)', 'period_of_relevant_year', $row['period_of_relevant_year'])}
				    </div>
				</div>
			</div>
		  	<div class='c50r'>
			  	<div class='subcr'>
					<div class = 'dropdownPeriod'>
					   <div class = 'workingMonth'>
			        	{$formObj->getDDRowBySQL('Month', 'period_of_working_month', $sqlCandidatePeriodOfWorking, $row['period_of_working_month'], $expVl)}
						</div>
						<div class = 'relevantMonth'>
			        	{$formObj->getDDRowBySQL('', 'period_of_relevant_month', $sqlCandidatePeriodOfWorking, $row['period_of_relevant_month'], $expVl)}
						</div>
					</div>
				</div>
			</div>
		</div>
        ";

        $fielset7 = "
        <div class='linkPortalWrapper'>
            <div class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Employment Details </div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getTBRow('Name of Company', 'employment_company_name1', $row['employment_company_name1'])}
                    {$formObj->getTBRow('Occupation', 'employment_occupation1', $row['employment_occupation1'])}
                    {$formObj->getDDRowBySQL('Country', 'employment_country1', $sqlCountry, $row['employment_country1'])}
					<div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period From', 'employment_period_from1', $row['employment_period_from1'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'employment_period_to1', $row['employment_period_to1'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
   				    </div>
                    {$formObj->getTBRow('Fixed Monthly  Salary', 'employment_salary1', $row['employment_salary1'])}
                    {$formObj->getTARow('JOB DUTIES & RESPONSIBILITIES', 'job_duties_responsibilities1', $row['job_duties_responsibilities1'])}
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Employment Details 2</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getTBRow('Name of Company', 'employment_company_name2', $row['employment_company_name2'])}
                    {$formObj->getTBRow('Occupation', 'employment_occupation2', $row['employment_occupation2'])}
                    {$formObj->getDDRowBySQL('Country', 'employment_country2', $sqlCountry, $row['employment_country2'])}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period From', 'employment_period_from2', $row['employment_period_from2'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'employment_period_to2', $row['employment_period_to2'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                    {$formObj->getTBRow('Fixed Monthly  Salary *', 'employment_salary2', $row['employment_salary2'])}
                    {$formObj->getTARow('JOB DUTIES & RESPONSIBILITIES', 'job_duties_responsibilities2', $row['job_duties_responsibilities2'])}
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Employment Details 3</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getTBRow('Name of Company', 'employment_company_name3', $row['employment_company_name3'])}
                    {$formObj->getTBRow('Occupation', 'employment_occupation3', $row['employment_occupation3'])}
                    {$formObj->getDDRowBySQL('Country', 'employment_country3', $sqlCountry, $row['employment_country3'])}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period From', 'employment_period_from3', $row['employment_period_from3'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'employment_period_to3', $row['employment_period_to3'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                    {$formObj->getTBRow('Fixed Monthly  Salary', 'employment_salary3', $row['employment_salary3'])}
                    {$formObj->getTARow('JOB DUTIES & RESPONSIBILITIES', 'job_duties_responsibilities3', $row['job_duties_responsibilities3'])}
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Employment Details 4</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getTBRow('Name of Company', 'employment_company_name4', $row['employment_company_name4'])}
                    {$formObj->getTBRow('Occupation', 'employment_occupation4', $row['employment_occupation4'])}
                    {$formObj->getDDRowBySQL('Country', 'employment_country4', $sqlCountry, $row['employment_country4'])}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period From', 'employment_period_from4', $row['employment_period_from4'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'employment_period_to4', $row['employment_period_to4'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                    {$formObj->getTBRow('Fixed Monthly  Salary', 'employment_salary4', $row['employment_salary4'])}
                    {$formObj->getTARow('JOB DUTIES & RESPONSIBILITIES', 'job_duties_responsibilities4', $row['job_duties_responsibilities4'])}
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div class='header' expanded=0>
                <div class='floatbox'>
                    <div class='float_left'>Employment Details 5</div>
                    <div class='toggle'> </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$formObj->getTBRow('Name of Company', 'employment_company_name5', $row['employment_company_name5'])}
                    {$formObj->getTBRow('Occupation', 'employment_occupation5', $row['employment_occupation5'])}
                    {$formObj->getDDRowBySQL('Country', 'employment_country5', $sqlCountry, $row['employment_country5'])}
                    <div class='subcolumns'>
                        <div class='dateFrom'>
                          {$formObj->getDateRow('Period From', 'employment_period_from5', $row['employment_period_from5'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                        <div class='dateTo'>
                          {$formObj->getDateRow('', 'employment_period_to5', $row['employment_period_to5'], array('yearStart' => 1940, 'yearEnd' => 2040))}
                        </div>
                    </div>
                    {$formObj->getTBRow('Fixed Monthly  Salary', 'employment_salary5', $row['employment_salary5'])}
                    {$formObj->getTARow('JOB DUTIES & RESPONSIBILITIES', 'job_duties_responsibilities5', $row['job_duties_responsibilities5'])}
                </div>
            </div>
        </div>
        ";

        $class = '';
        $class1 = '';
        if($row['stayed_in'] == 0){
            $class = 'stayedIn';
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);


        $fielset9 = "
        {$formObj->getTBRow('Candidate Mobile Numbers', 'candidate_mobile_no', $row['candidate_mobile_no'])}
        {$formObj->getTBRow('E-mail Address', 'email_address', $row['email_address'])}
        {$formObj->getTBRow('Residential Address', 'residential_address', $row['residential_address'])}
        {$formObj->getTBRow('Home Phone Number', 'home_no', $row['home_no'])}
        {$formObj->getTBRow('Father / Mother / Wife Mobile Number', 'father_mother_no', $row['father_mother_no'])}
		";

        if ($tv['action'] == 'detail'){
            if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1){
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
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('manPower_candidate', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getTBRow('Position', 'position', $row['position'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$compAddressDD}
        {$companyAddress}
        {$chinesePos}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        {$chineseDept}
        ";

        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];
        $sqlStatus  = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $fielset3 = "
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $subscribed)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        ";


        $class = '';
        $btn1 = '';
        if($row['university2'] == ''){
            $class = 'educationDetails';
            $btn1 ="
            <div class='floatbox'>
                <a class='button' id='btnAddEducation' href='#'>Add Education</a>
            </div>
            ";
        }

        /*if(($row['university1'] == '' && $tv['record_id'] != '')
        || ($row['university1'] != '' && $row['university2'] == '' && $tv['record_id'] != '')){
            $class = 'educationDetails';
            $btn1 ="
            <div class='floatbox'>
                <a class='button show' id='btnAddEducation' href='#'>Add Education</a>
            </div>
            ";
        } else if($row['university1'] != '' && $row['university2'] != '' && $tv['record_id'] != ''){
            $class = '';
            $btn1 ="
            <div class='floatbox'>
                <a class='button hide' id='btnAddEducation' href='#'>Add Education</a>
            </div>
            ";
        }*/

        $class2 = '';
        $btn2 = '';
        if($row['university3'] == ''){
            $class2 = 'educationDetails1';
            $btn2 ="
            <div class='floatbox'>
                <a class='button' id='btnAddEducation1' href='#'>Add Education</a>
            </div>
            ";
        }

        $class3 = '';
        $btn3 = '';
        if($row['university4'] == ''){
            $class3 = 'educationDetails2';
            $btn3 ="
            <div class='floatbox'>
                <a class='button' id='btnAddEducation2' href='#'>Add Education</a>
            </div>
            ";
        }

        $class4 = '';
        $btn4 = '';
        if($row['university5'] == ''){
            $class4 = 'educationDetails3';
            $btn4 ="
            <div class='floatbox'>
                <a class='button' id='btnAddEducation3' href='#'>Add Education</a>
            </div>
            ";
        }

        //{$personalAdd}
        //{$agentDetail}

        //{$formObj->getFieldSetWrapped('Declaration by Foreign Employee', $fielset8)}
        //{$formObj->getFieldSetWrapped('Address', $addressCandidate)}

        $text = "
        {$formObj->getFieldSetWrapped('Candidate Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Other Details', $fielsetOtherDetails)}
        {$formObj->getFieldSetWrapped('Travel Document Information', $fielset4)}
        {$formObj->getFieldSetWrapped('Education Details', $fielset5)}
        {$formObj->getFieldSetWrapped('Working Experience', $fielset6)}
        {$formObj->getFieldSetWrapped('Details of  latest Employment Records', $fielset7)}
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
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */

    function getCandidateDocument($candidate_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
                       
        $text= '';
        
        $SQL = "
        SELECT d.documents_id
              ,d.title
        FROM documents d
        WHERE d.module_name = 'Candidate'
        ";
        $result = $db->sql_query($SQL);

        $formAction = "index.php?_topRm=admin&module=manPower_candidate&_spAction=candidateDocumentSubmit&showHTML=0";

        while ($row = $db->sql_fetchrow($result)) {
            $candidateDocumentRec = $fn->getRecordByCondition('candidate_documents', 
                                                      "candidate_id = '{$candidate_id}' AND documents_id = {$row['documents_id']}");
            $checked = $candidateDocumentRec['candidate_documents_id'] != '' ? "checked='checked'" : '';
            $text .= "
            <div class='documentChk'>
                <input type='checkbox' name='documents' value='1' {$checked}
                 candidate_id='{$candidate_id}' documents_id='{$row['documents_id']}' class='candidateDocument_{$row['documents_id']}'>
                <label name='documents'>{$row['title']}</label>
            </div>
            <input type='hidden' name='candidate_id' value='{$candidate_id}' />
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        
        $rows = "";

        if( $cpCfg['m.manPower.contact.showInterest'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("manPower_candidate", "common_interestLink", "Interests Linked", $row);
        }
        
        if( $cpCfg['m.manPower.contact.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("manPower_candidate", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'candidate_id');
		$candidateDocumentLink ='';

		$attachementLink ='';

		$candidateResumeButton ='';
		$candidateResumePdfButton ='';		 
		$noDueButton ='';
		$noDueWordButton ='';
		$declarationButton ='';
        $attachment = '';
        $opportunityView = '';
        $messageToStaffButton = '';
        
        $attText = "<b><u>Note : Please make sure that the uploading documents are less than 5 MB</b></u><br><br>";
        
		$urlCandidateDocument = "index.php?module=manPower_candidate&_spAction=printCandidateDocument&candidate_id={$row['candidate_id']}&showHTML=0";
		$urlCandidateDocumentResumePdf = "index.php?module=manPower_candidate&_spAction=printCandidateResumeAsPdf&candidate_id={$row['candidate_id']}&showHTML=0";
		$urlNoDuePdf = "index.php?module=manPower_candidate&_spAction=printNoDuePdf&candidate_id={$row['candidate_id']}&showHTML=0";
		$urlDeclarationPdf = "index.php?module=manPower_candidate&_spAction=printDeclarationPdf&candidate_id={$row['candidate_id']}&showHTML=0";
        $message_to_staff =  "index.php?_topRm=admin&_spAction=sendMessageToStaffByAgent&module=manPower_candidate&candidate_id={$record_id}&showHTML=0";
		$urlNoDueWord = "index.php?module=manPower_candidate&_spAction=printNoDueWord&candidate_id={$row['candidate_id']}&showHTML=0";

		$urlDeclarationWord = "index.php?module=manPower_candidate&_spAction=printDeclarationWord&candidate_id={$row['candidate_id']}&showHTML=0";

        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){

            //if ($_SESSION['userGroupType'] == 'Super Administrator') {
    			$candidateDocumentLink = "
    	        <div class='header' expanded='1'>
    	            Candidate Document Link              
    	        </div>
    	        <div class='candidateDocs'>
    	            {$this->getCandidateDocument($row['candidate_id'])}
    	        </div>
    			";
            //}

            //if ($_SESSION['userGroupType'] == 'Super Administrator') {
    			$attachementLink = "
    	        {$media->getRightPanelMediaDisplay('Passport Size Photo in Colour', 'manPower_candidate', 'attachment1', $row)}        
    	        {$media->getRightPanelMediaDisplay('Passport Copy – Front & Back in Colour', 'manPower_candidate', 'attachment2', $row)}        
    	        {$media->getRightPanelMediaDisplay('UG Convocation Degree Certificate, Mark Statement in Colour', 'manPower_candidate', 'attachment3', $row)}        
    	        {$media->getRightPanelMediaDisplay('PG Convocation Degree Certificate, Mark Statement in Colour', 'manPower_candidate', 'attachment4', $row)}        
    	        {$media->getRightPanelMediaDisplay('Other Trade Certificates in Colour', 'manPower_candidate', 'attachment5', $row)}        
    	        {$media->getRightPanelMediaDisplay('Transfer Certificate in Colour', 'manPower_candidate', 'attachment6', $row)}        
    	        {$media->getRightPanelMediaDisplay('Driving License Front & Back', 'manPower_candidate', 'attachment7', $row)}        
    			";
            //}

            if ($_SESSION['userGroupType'] != 'Agent') {
    			$candidateResumePdfButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlCandidateDocumentResumePdf}' id='candidateResume' target='_blank'>Candidate Resume Pdf</a>
    	            </div> 
    	        </div>        
    			";

    			/*
                $candidateResumeButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlCandidateDocument}' id='candidateResume' target='_blank'> Resume in Word</a>
    	            </div> 
    	        </div>        
    			";
                */

    			/*
                $noDueButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlNoDuePdf}' id='candidateResume' target='_blank'>No Due Pdf</a>
    	            </div> 
    	        </div>        
    			";
                */

    			$noDueWordButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlNoDueWord}' id='candidateResume' target='_blank'>No Due Draft</a>
    	            </div> 
    	        </div>        
    			";

    			$declarationButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlDeclarationWord}' id='candidateResume' target='_blank'>Declaration Draft</a>
    	            </div> 
    	        </div>        
    			";

                /*
    			$declarationButton = "
    	        <div class='floatbox  btnbackground'>
    	            <div class='button mb5'>
    	                <a href='{$urlDeclarationPdf}' id='candidateResume' target='_blank'>Declaration Pdf</a>
    	            </div> 
    	        </div>        
    			";
                */
    		}
    		
            if ($_SESSION['userGroupType'] == 'Agent') {
                /* Message to Staff button is shown only when the candidate is not linked to any opportunity or for 
                   earlier opportunity where he is rejected in interview */
                $sqlOppCandidate = "
                SELECT opportunity_candidate_id
                FROM opportunity_candidate
                WHERE candidate_id = {$row['candidate_id']}
                  AND process_status != 'Rejected In Interview (Staff)'
                ";
                $resultOppCandidate  = $db->sql_query($sqlOppCandidate);
                $numRowsOppCandidate = $db->sql_numrows($resultOppCandidate);
                
                $messageToStaffButton = '';
                if ($numRowsOppCandidate == 0) {                    
        			$messageToStaffButton = "
                    <div class='floatbox  btnbackground'>
                        <div class='button mb5'>
                            <a href='{$message_to_staff}' id='confirmToStaff'>Message To Staff</a>
                        </div> 
                    </div>
        			";
        		}
    		}
    		
          $actBtn = $fn->getReqParam('actBtn');
          /*    if ($actBtn =='candidateResumePdf'){
    			//$filter .= "index.php?module=manPower_candidate&_spAction=printCandidateResumeAsPdf&candidate_id={$row['candidate_id']}&showHTML=0";
                $utilCommon->redirect("index.php?module=manPower_candidate&_spAction=printCandidateResumeAsPdf&candidate_id={$row['candidate_id']}&showHTML=0");
            }
            
            if ($actBtn == 'noDuePdf'){
            }

            if ($actBtn == 'declarationPdf'){
            }

            if ($actBtn == 'messageToStaff'){
            } */

    		$filter = '';
            if ($_SESSION['userGroupType'] != 'Agent') {
        		$filter .= "
                <td>
                    <select name='actionBtn' class='candidateBtn'>
                        <option value=''>Please Select</option>
                        <option value='candidateResumePdf'>Candidate Resume Pdf</option>
                        <option value='noDuePdf'>No Due Pdf</option>
                        <option value='declarationPdf'>Declaration Pdf</option>
                        <option value='messageToStaff'>Message To Staff</option>
                    </select>
                </td>
        		";
            }

            $db = Zend_Registry::get('db');
            $SQLOpp   = "
            SELECT o.opportunity_code
            FROM opportunity_candidate op
            LEFT JOIN (opportunity o) ON ( op.opportunity_id = o.opportunity_id )
            WHERE op.candidate_id = {$row['candidate_id']}
            ";
            $resultOpp   = $db->sql_query($SQLOpp);  
            while ($rowOpp = $db->sql_fetchrow($resultOpp)) {
                $rowsHTML = $this->getRowsHTML($rowOpp['opportunity_code']);
                if ($rowsHTML != ""){
            		$opportunityView .= "
                        <table class='thinlist mt10 mb10'>
                            <thead>
                                <tr>
                                    <th>Opp Code</th>
                                    <th>View Comments</th>
                                    <th>Add Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$rowsHTML}
                            </tbody>
                        </table> 
            		";
                }   		
            }   		
            

    		/*
            if ($row['opportunity_code'] != '') {
                $rowsHTML = $this->getRowsHTML();
                if ($rowsHTML != ""){
            		$opportunityView = "
                        <table class='thinlist mt10 mb10'>
                            <thead>
                                <tr>
                                    <th>Opp Code</th>
                                    <th>View Comments</th>
                                    <th>Add Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$rowsHTML}
                            </tbody>
                        </table> 
            		";
                }   		
    		}
            */
        } else {
            $attachment ="
            {$media->getRightPanelMediaDisplay('Attachment', 'manPower_candidate', 'attachment', $row)}        
            ";
        }
        
        //$formAction = "index.php?_topRm=opportunity&module=manPower_candidate&_spAction=printPdfByDropDown&showHTML=0";

      /*  <form id='candidateSearch' method='post'>
            <table class='search'>
                <tr>
                    {$filter}
                    <td>
                        <input type='hidden' name='actBtn' value='{$actBtn}'>
                        <input type='hidden' name='candidate_id' value='{$row['candidate_id']}'>
        	            <div class='mb5'>
        	                <a href='#' id='openCandidatePdf' class='button' actBtn='{$actBtn}' candidate_id={$row['candidate_id']}>Go</a>
        	            </div>
                    </td>
                </tr>
            </table>
        </form> */

        $text = "
        <div id='positionLinkPortal'>{$this->getPositionDisplay($row['candidate_id'])}</div>
        <div id='candidateLinkPortal'>{$this->getCandidateClientDisplay($row['candidate_id'])}</div>
        {$attText}
        {$attachment}
		{$candidateResumePdfButton}
		{$noDueButton}
		{$declarationButton}
		{$candidateResumeButton}
		{$noDueWordButton}
		{$messageToStaffButton}
        {$opportunityView}
		{$attachementLink}
		{$candidateDocumentLink}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'manPower_candidate'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
        ))}
        ";

        return $text;
    }

    /**
     */
    function getPositionDisplay($candidate_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sqlPosition  = $fn->getValuelistValueAsArray('opportunityPosition','value');

        $sqlPositionCandidate ="
        SELECT position_title
        FROM position_candidate
        WHERE candidate_id={$candidate_id}
        ";
        $result = $db->sql_query($sqlPositionCandidate);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $text = "
        <div class='linkPortalWrapper manPower_candidate__manPower_candidateLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Position Linked</div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='positionlist'>
                        <tr>
                            <td class='positionCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'candidate_position', $sqlPosition ,$dataArray)}</td>
                            <input id='candidate_id' type='hidden' name='candidate_id' value='{$candidate_id}' />
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     */
    function getCandidateClientDisplay($candidate_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $clients = "";
        //$recCount = $fn->getRecordCount('opportunity_candidate', "opportunity_id = '{$opportunity_id}'");

        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Client name</th>
            <th>From Date</th>
            <th>To Date</th>
            </tr>
        </thead>
        ";

        $SQLClients = "
            SELECT p.*,c.company_name
                   ,c.company_id
            FROM `project` p
            LEFT JOIN `company` c ON (c.company_id = p.company_id)
            WHERE p.candidate_id = {$candidate_id}
            ORDER BY p.start_date ASC";
        $resultClients   = $db->sql_query($SQLClients);
        $numRows         = $db->sql_numrows($resultClients);
        $todate = '';
        $count = 1;
        while ($rowClients = $db->sql_fetchrow($resultClients)){
             $companyName = "<a href='index.php?_topRm=opportunity&module=manPower_company&_action=edit&record_id={$rowClients['company_id']}' target = '_blank'>{$rowClients['company_name']}</a>";
             $clients .= "
             <tr>
                 <td>{$count}</td>
                 <td>{$companyName}</td>
                 <td>{$rowClients['start_date']}</td>
                 <td>{$rowClients['estimated_finish_date']}</td>
             </tr>
            ";
            $start_date = $rowClients['start_date'];
            $count++;
        }

        if($numRows ==0){
            $header ="<thead></thead>";
            $clients .= "
                <tr>
                    <td class='noCandidate'>No Records Linked</td>
                </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper manPower_candidate__manPower_candidateLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Client Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$numRows})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='candidatelist'>
                        {$header}
                        <tbody id='candidateDisplayPortal'>
                            {$clients}
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;

    }


    /**
     *
     */

    function getRowsHTML($opportunity_code) {
        $fn = Zend_Registry::get('fn');

        $rows = '';

        foreach($this->model->dataArray as $row){

            $urlNewComment = "index.php?_topRm=opportunity&module=manPower_candidate&_spAction=candidateCommentForm&candidate_id={$row['candidate_id']}
            &agent_id={$row['agent_id']}&opportunity_code={$opportunity_code}&showHTML=0";
            $viewComment   = "index.php?_topRm=opportunity&module=manPower_candidate&_spAction=viewComment&candidate_id={$row['candidate_id']}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$opportunity_code}</td>
                <td><a href='{$viewComment}' class='viewComment'>View Comment</a></td>
                <td><a href='{$urlNewComment}' class='candidateComment'>Add Comment</a></td>
            </tr>
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getCandidateCommentForm() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=opportuntiy&module=manPower_candidate&_spAction=addCommentFormSubmit&showHTML=0";
        $candidate_id = $fn->getReqParam('candidate_id');
        $agent_id = $fn->getReqParam('agent_id');
        $opportunity_code = $fn->getReqParam('opportunity_code');

        $text = "
        <form id='addCommentForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTARow('Comments', "comments")}
            <input type='hidden' name='candidate_id' value={$candidate_id} />
            <input type='hidden' name='agent_id' value='{$agent_id}' />
            <input type='hidden' name='opportunity_code' value='{$opportunity_code}' />
        </form>
        ";        
        return $text;
    }
    
    /***
     */
    function getViewComment() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        
        $rows = '';
        $candidate_id = $fn->getReqParam('candidate_id');
        
        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,s.staff_login_type
        FROM comment c
        LEFT JOIN ( staff s ) ON ( c.contact_id = s.staff_id )
        WHERE c.record_id = {$candidate_id}
            AND room_name = 'manPower_candidate'
            AND c.site_id = '{$_SESSION['cp_site_id']}'
        ORDER BY c.comment_id DESC
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            
            $date = $fn->getCPDate($row['comment_date'], 'd M Y' );
            $viewComments = nl2br($row['comments']);
            $rows .= "
            <tr>
                <td>{$viewComments} <br><br>
                <b>Comments By :</b> {$row['staff_login_type']} </br>
                <b>Date :</b> {$date}
                </td>
            </tr>
            ";
        }

        $text = "
        <table class='thinlist'>
            <thead>
            <tr>
                <th>View Comments</th>
            </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>        
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
        $category       = $fn->getReqParam('category');
        $position       = $fn->getReqParam('position');
        $status         = $fn->getReqParam('status');
        $locked         = $fn->getReqParam('locked');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        //==================================================================//
        $categoryText = "";
        $interestText = "";

        $SQLStatus      = $fn->getValueListSQL('companyStatus');
        $SQLCategory    = $fn->getValueListSQL('candidatePosition');
        $sqlInterest    = $fn->getDDSql('common_interest');
        $sqlPosition    = $fn->getValueListSQL('opportunityPosition','value');


        if ($cpCfg['m.manPower.contact.showCategory'] == 1) {
            $categoryText = "
            <td>
                <select name='category'>
                    <option value=''>Category</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $category)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        if ($cpCfg['m.manPower.contact.showInterest'] == 1) {
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


        $lockArray = array(
              "Yes"
             ,"No"
        );

        /* Hiding Agent dropdown for agent login */
        $agentText = '';
        /*
        if ($_SESSION['userGroupType'] == 'Super Administrator') {

            $appendAgentSql = '';
            if($cpCfg['cp.hasMultiUniqueSites'] == true) {
                $appendAgentSql = "WHERE a.site_id = '{$_SESSION['cp_site_id']}'";
            }

            $SQLAgent = "
            SELECT a.agent_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS agent_name
            FROM agent a
            {$appendAgentSql}
            ORDER BY agent_name
            ";

            $agentText = "
            <td>
                <select name='agent_id'>
                    <option value=''>Agent</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLAgent, $agent_id)}
                </select>
            </td>
            ";
        }
        */

        /*<td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>*/

        $text = "
        {$categoryText}
        {$interestText}
        {$agentText}
        <td>
            <select name='position'>
                <option value=''>Select Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>

        <td>
            <select name='locked' >
                <option value=''>Locked</option>
                {$cpUtil->getDropDown1($lockArray, $locked)}
            </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>

        <td>
            <input type='text' value='' rel='pptxt: Documents Search' name='advanced_search'>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getSendMessageToStaffByAgent() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
               
        $candidate_id = $fn->getReqParam('candidate_id');
        
        $expNoEdit  = array('isEditable' => 0);
        $formAction = "index.php?_topRm=admin&module=manPower_candidate&_spAction=sendMessageToStaffByAgentSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar confirmToStaffForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box", '', $expNoEdit)}
            {$formObj->getTBRow('Please mention the Opportunity Code', 'opportunity_code')}
            <input type='hidden' name='candidate_id' value='{$candidate_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getConvertDocumentsIntoText($Keywords){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        require("class.filetotext.php");

        $SQLAttachment = "
        SELECT file_name
              ,record_id 
        FROM media
        WHERE room_name = 'manPower_candidate'
        AND record_type = 'attachment'
        ";
        $resultAttachment = $db->sql_query($SQLAttachment);
        $record_id = '';
        while($rowAttachment = $db->sql_fetchrow($resultAttachment)){
            $docObj = new Filetotext(realpath('../media/normal').'/'."{$rowAttachment['file_name']}");
            $contents = $docObj->convertToText();
            $contents = strtolower($contents);
            $Keywords = strtolower($Keywords);

            if( strstr( $contents, "{$Keywords}") !== false){
                $record_id .= "{$rowAttachment['record_id']},";
            }
        }

        $record_id = rtrim($record_id,',');

        return $record_id;
    }
}