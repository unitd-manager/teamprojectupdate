<?
class CP_Admin_Modules_ManPower_CallRegistry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;

        $contactRows = '';
        $contactHeader = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            //$staff_name = $_SESSION['userFullName'];
	        $contactDate = $fn->getCPDate($row['contact_date'], 'm-d-Y');
	        $followUpDate = $fn->getCPDate($row['follow_up_date'], 'm-d-Y');
			$title = '';

			if($row['title'] == 'Others'){
				$title = $row['other_industry'];
			} else {
				$title = $row['title'];
			}

            /*$staffName = '';
            if($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator') {
                $staffName = $listObj->getListDataCell($row['staff_name']);
            }*/

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $title)}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($contactDate)}
            {$listObj->getListDataCell($followUpDate)}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListRowEnd($row['call_registry_id'])}
            ";
            $rowCounter++;
        }


        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Company Name', 'company_name')}
        {$listObj->getListHeaderCell('Contact Name', 'c.contact_name')}
        {$listObj->getListHeaderCell('Contact No', 'c.phone')}
        {$listObj->getListHeaderCell('Category', 'c.category')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
        {$listObj->getListHeaderCell('Meeting/Call Date', 'c.contact_date')}
        {$listObj->getListHeaderCell('Reminder Date', 'c.follow_up_date')}
        {$listObj->getListHeaderCell('Staff Name', 'c.staff_name')}
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
        $fn = Zend_Registry::get('fn');

        $fa = $this->model->getFields();
        $fa['staff_id']             = $_SESSION['staff_id'];
        $fa['contact_date']      	= date("Y-m-d");
        $fa['follow_up_date']       = date('Y-m-d', strtotime("+7 days"));
        $fa['call_registry_code']   = $this->model->getUpdateCallRegistryCode();

        $id = $fn->addRecord($fa);
        return $fn->returnAfterNewSave($id, 'editFromNew', false);
        return;

        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* To get the company names from Project module in CRM. Add if else condition for other modules below */
        if ($cpCfg['m.manPower.callRegistry.companyFromProjectModuleForCrm']) {
            $sqlComp  = $fn->getDDSql('project_company', array('condn' => "category = 'Client'"));
        } else {
            $sqlComp  = $fn->getDDSql('manPower_company', array('condn' => "category = 'client'"));
        }

        $fielset = "
        {$formObj->getTBRow('Client Company', 'company_name')}
        ";

        $formAction = "index.php?_topRm=opportunity&module=manPower_companyLink&_spAction=addNewCompanyForCallRegistryForm&showHTML=0";
        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";
        /*<div class='floatbox  btnbackground'>
            <div class='button mb5'>
                <a href='{$formAction}' id='addNewCompany'>New Company</a>
            </div>
        </div>*/

        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name   = $fn->getReqParam('valuelist_name');
        $call_registry_id = $fn->getReqParam('call_registry_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=manPower_callRegistry&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='call_registry_id' value='{$call_registry_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $appendSql = "";
        $append1Sql = "";
        $formObj->mode = $tv['action'];
        $userGroupType = $fn->getSessionParam('userGroupType');

        $expNoEdit = array('isEditable' => 0);
        //$sqlStatus 		= $fn->getValueListSQL('callRegistryStatus');
        $sqlIndustry	= $fn->getValueListSQL('callRegistryIndustry','sort_order');
        $sqlCategory    = $fn->getValueListSQL('callRegistryCategory');
        $sqlReffer 		= $fn->getValueListSQL('callRegistryReffer');
        $sqlPosition        = $fn->getValueListSQL('opportunityPosition','value');
        $expVl= array('sqlType' => 'OneField');

        $staff_name = $_SESSION['userFullName'];
        $sqlStaff = $fn->getDDSql('manPower_staff', array('condn' => $staff_name));

        if ($row['category'] != ''){
           $category = $formObj->getDDRowBySQL('Category', 'category', $sqlCategory, $row['category'], $expVl);
        } else {
           $category = $formObj->getDDRowBySQL('Category *', 'category', $sqlCategory, 'Tele Marketing', $expVl);
        }

        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'callRegistryStatus'
        ORDER BY value
        ";

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=manPower_callRegistry&_spAction=addNewValuelistForm&valuelist_name=opportunityPosition&call_registry_id={$row['call_registry_id']}&showHTML=0";
        $expPosition = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue'>Add</a>");

        $position = "
            <div class='positionTitle'>
             {$formObj->getDDRowBySQL('Position *', 'position', $sqlPosition, $row['position'], $expPosition)}
            </div>
            ";

		$otherIndustry = '';
		if($row['industry'] == 'Others'){
			$otherIndustry = 'otherIndustry';
			$otherIndustry = "
            <div class = '{$otherIndustry}'>
                {$formObj->getTBRow('Other Industry', 'other_industry', $row['other_industry'])}
            </div>
        ";
		}

        $callRegCode = $formObj->getTBRow('Code', 'call_registry_code', $row['call_registry_code'], $expNoEdit);
        //{$formObj->getTBRow('Title *', 'title', $row['title'])}
        //{$formObj->getTBRow('Job Title', 'job_title', $row['job_title'])}
        $fieldset1 = "
        {$callRegCode}
		{$formObj->getTBRow('Staff Name', 'staff_id', $row['staff_name'], $expNoEdit)}
        {$formObj->getTBRow('Job Title', 'title', $row['title'])}
        {$position}
        {$formObj->getDateRow('Meeting/Call Date', 'contact_date', $row['contact_date'])}
        {$formObj->getDateRow('Reminder Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expVl)}
        ";

        $fieldset2 = "
        {$formObj->getTARow('Meeting Notes', 'comments', $row['comments'])}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Company Name *', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Phone *', 'phone', $row['phone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow('Alternate Phone', 'alternate_phone', $row['alternate_phone'])}
        ";

        $fieldset4 = "
        {$formObj->getDDRowBySQL('Industry *', 'industry', $sqlIndustry, $row['industry'], $expVl)}
        {$otherIndustry}
        {$category}
        {$formObj->getDDRowBySQL('Refference', 'reffer', $sqlReffer, $row['reffer'], $expVl)}
        {$formObj->getTARow('Purpose of Meet', 'requirements', $row['requirements'])}
        ";


        $text = "
        {$formObj->getFieldSetWrapped('Lead Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fieldset3)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset4)}
        {$formObj->getFieldSetWrapped('Meeting Notes', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'call_registry_id');

		$convertOpp = '';
        $opportunityLink = '';

        if ($row['status'] == 'High Win Ratio') {
            $convertOpp ="
            <div class='button mb10'>
                <a href='#' id='convertToOpportunity' call_registry_id='{$record_id}'>Convert To Opportunity</a>
            </div>
            ";
        }

        $sqlGoto ="
        SELECT o.call_registry_id
               ,o.opportunity_id
               FROM `opportunity` o
        LEFT JOIN `call_registry` c ON (c.call_registry_id = o.call_registry_id)
        WHERE c.call_registry_id = {$row['call_registry_id']}
        ";
        $resultGoto = $db->sql_query($sqlGoto);
        $rowGoto    = $db->sql_fetchrow($resultGoto);

        $opportunityLink = "index.php?_topRm=opportunity&module=manPower_opportunity&_action=detail&opportunity_id={$rowGoto['opportunity_id']}";

        $recOpp    = $fn->getRecordRowByID('opportunity', 'call_registry_id', $record_id);
        if (is_array($recOpp)){
            $convertOpp = "
                <div class='button mb10'>
                    <a href='{$opportunityLink}' target='_balnk'>Go To Opportunity</a>
                </div>
            ";
        }

        $text = "
        {$convertOpp}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id     = $fn->getReqParam('company_id');
        $company_name   = $fn->getReqParam('company_name');
        $status         = $fn->getReqParam('status');
        $category         = $fn->getReqParam('category');
        $staff_id       = $fn->getReqParam('staff_id');
        $site_id        = $fn->getReqParam('site_id');
        $today_reminder = $fn->getReqParam('today_reminder');
        $call_date      = $fn->getReqParam('contact_date');
        $reminder_date  = $fn->getReqParam('follow_up_date');

        $sqlCategory       = $fn->getValueListSQL('callRegistryCategory');

        $appendStatusSql = '';
        $appendComSql = '';
        $appendStaffSql = '';
        if($cpCfg['cp.hasMultiUniqueSites'] == true) {
            $appendStatusSql = "AND site_id = '{$_SESSION['cp_site_id']}'";
            $appendComSql = "WHERE c.site_id = '{$_SESSION['cp_site_id']}'";
            $appendStaffSql = "WHERE s.site_id = '{$_SESSION['cp_site_id']}' AND s.staff_login_type = 'Staff'";
        }

        $SQLStatus = "
        SELECT DISTINCT value
        FROM valuelist
        WHERE key_text = 'callRegistryStatus'
        {$appendStatusSql}
        ORDER BY sort_order
        ";

        $SQLComp = "
        SELECT DISTINCT c.company_name
        FROM `call_registry` c
        {$appendComSql}
        ORDER BY company_name
        ";

        $SQLStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        {$appendStaffSql}
        ";



		/*if ($_SESSION['staff_type'] == 'Staff') {
        	$staff = '';
        } else {
			$staff = "
				 <td class='fieldValueStaff'>
		            <select name='staff_id'>
		                <option value=''>Staff</option>
		                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStaff, $staff_id)}
		            </select>
		        </td>
			";
        }*/


        $spArray = array(
            ""
           ,"Reminders for Today"
           ,"Show All"
        );

		/*
        <td>
            <select name='month'>
                <option value=''>Month Filter</option>
                <option value='01'>January</option>
                <option value='02'>February</option>
                <option value='03'>March</option>
                <option value='04'>April</option>
                <option value='05'>May</option>
                <option value='06'>June</option>
                <option value='07'>July</option>
                <option value='08'>August</option>
                <option value='09'>September</option>
                <option value='10'>October</option>
                <option value='11'>November</option>
                <option value='12'>December</option>
            </select>
        </td>
        <td>
            <select name='company_name'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLComp, $company_name)}
            </select>
        </td>
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $category)}
            </select>
        </td>
		*/

        $callRegistryDate1 = $fn->getReqParam('callRegistryDate1');
        $callRegistryDate2 = $fn->getReqParam('callRegistryDate2');
        $followUpDate1     = $fn->getReqParam('followUpDate1');
        $followUpDate2     = $fn->getReqParam('followUpDate2');
        $yearEnd = date('Y') + 10;

		if($callRegistryDate1 == ''){
			$callRegistryDate1 = 'From';
		}

		if($callRegistryDate2 == ''){
			$callRegistryDate2 = 'To';
		}

		if($followUpDate1 == ''){
			$followUpDate1 = 'From';
		}

		if($followUpDate2 == ''){
			$followUpDate2 = 'To';
		}

        //{$staff}

        $text = "
        <td class='fieldValueStatus'>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        <td>
            <select name='today_reminder'>
                <option value=''>Please Select</option
                {$cpUtil->getDropDown1($spArray, $today_reminder)}
           </select>
        </td>
        <!--<td>
            {$formObj->getDateRow('Reminder Date:', 'follow_up_date', $reminder_date)}
        </td>-->
        <td class='dateRange'>
            Meeting/Call Date:
            <input type='text' allowEdit='1' name='callRegistryDate1' class='fld_date'
                   id='fld_callRegdate1' value='{$callRegistryDate1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='callRegistryDate2' class='fld_date'
                   id='fld_callRegdate2' value='{$callRegistryDate2}' yearEnd='{$yearEnd}' />
        </td>
        <td class='dateRange'>
            Reminder Date:
            <input type='text' allowEdit='1' name='followUpDate1' class='fld_date'
                   id='fld_followupdate1' value='{$followUpDate1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='followUpDate2' class='fld_date'
                   id='fld_followupdate2' value='{$followUpDate2}' yearEnd='{$yearEnd}' />
        </td>
        ";

        return $text;
    }

     /**
     *
     */
    function getDuplicateCallDate() {
        $formObj = Zend_Registry::get('formObj');
        $fn      = Zend_Registry::get('fn');

        $formAction = 'index.php?module=manPower_callRegistry&_spAction=duplicateSubmit&showHTML=0';

        $call_registry_id     = $fn->getReqParam('call_registry_id');

        $text = "
        <form id='duplicateCallDate' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Call Date', 'contact_date')}
                <input type='hidden' name='call_registry_id' value='{$call_registry_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

}