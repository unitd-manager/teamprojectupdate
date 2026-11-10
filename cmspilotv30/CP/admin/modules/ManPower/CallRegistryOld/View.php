<?
class CP_Admin_Modules_ManPower_CallRegistry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        //**** DELETE EMPTY RECORDS ***//
        $site_id    = $fn->getSessionParam('cp_site_id');
         $sqlAppend = '';

        if($site_id){
            $sqlAppend = " AND site_id  = {$_SESSION['cp_site_id']}";
        }

        $SQL1 = "
        DELETE FROM call_registry
        WHERE title IS NULL
          AND company_name IS NULL
          AND phone IS NULL
          AND status IS NULL
          AND staff_id = {$_SESSION['staff_id']}
        ";
        $result1 = $db->sql_query($SQL1);

        /*
        $refCount  = $fn->getReqParam('refCount');
        if($refCount == ''){
            $refCount = '';
            $cpUtil->redirect("/admin/index.php?_topRm=marketing&module=manPower_callRegistry&_action=list&refCount=1");
        }
        */

        $rows  = "";
        $rowCounter = 0;

        $contactRows = '';
        $contactHeader = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $staff_name = $_SESSION['userFullName'];
	        $contactDate = $fn->getCPDate($row['contact_date'], 'd-m-Y');
	        $followUpDate = $fn->getCPDate($row['follow_up_date'], 'd-m-Y');
			$title = '';

			if($row['title'] == 'Others'){
				$title = $row['other_industry'];
			} else {
				$title = $row['title'];
			}

            $staffName = '';
            if($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator') {
                $staffName = $listObj->getListDataCell($row['staff_name']);
            }

			/*$SQL = "
			SELECT CONCAT(c.contact_date, ' ', c.contact_time) AS contact_time
	        FROM `call_registry` c
			";*/

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $title)}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($contactDate . ' ' . $row['contact_time'])}
            <!--{$listObj->getListDataCell($row['contact_time'])}-->
            {$listObj->getListDataCell($followUpDate)}
            {$staffName}
            {$listObj->getListRowEnd($row['call_registry_id'])}
            ";
            $rowCounter++;
        }

        $staffName = '';
        if($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator') {
            $staffName = $listObj->getListHeaderCell('Staff Name', 'staff_name');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Industry', 'title')}
        {$listObj->getListHeaderCell('Company Name', 'company_name')}
        {$listObj->getListHeaderCell('Contact Name', 'c.contact_name')}
        {$listObj->getListHeaderCell('Contact No', 'c.phone')}
        {$listObj->getListHeaderCell('Category', 'c.category')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
        {$listObj->getListHeaderCell('Call Date & Time', 'c.contact_date'. ' ' . 'c.contact_time')}
        <!--{$listObj->getListHeaderCell('Call Time', 'c.contact_time')}-->
        {$listObj->getListHeaderCell('Reminder Date', 'c.follow_up_date')}
        {$staffName}
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
        $fa['contact_date']         = date('Y-m-d');
        $fa['contact_time']         = date('H:i:s');
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
        $expVl= array('sqlType' => 'OneField');

        $staff_name = $_SESSION['userFullName'];
        $sqlStaff = $fn->getDDSql('manPower_staff', array('condn' => $staff_name));

        if ($row['category'] != ''){
           $category = $formObj->getDDRowBySQL('Category', 'category', $sqlCategory, $row['category'], $expVl);
        } else {
           $category = $formObj->getDDRowBySQL('Category *', 'category', $sqlCategory, 'Tele Marketing', $expVl);
        }

        $site_id    = $fn->getSessionParam('cp_site_id');

        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }

        if ($row['category'] == 'Direct Marketing'){
            $append1Sql .= "AND value != 'Not in Use'";
        } else if ($row['category'] == 'Internet Marketing') {
            $append1Sql .= "AND value != 'Not in Use' AND value != 'Not Interested'";
        }

        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'callRegistryStatus'
          {$append1Sql}
          {$appendSql}
        ORDER BY value
        ";

		$otherIndustry = '';
		if($row['title'] != 'Others'){
			$otherIndustry = 'otherIndustry';
		}

		$reminderDate = '';
		if($row['status'] != 'Follow up'){
			$reminderDate = 'reminderDate';
		}

		$noOfCandidate = '';
		if($row['status'] != 'High Win Ratio'){
			$noOfCandidate = 'noOfCandidate';
		}

		$staffName = '';
		$callRegCode = '';
        if($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator') {
            $staffName = $formObj->getTBRow('Staff Name', 'staff_id', $row['staff_name'], $expNoEdit);
            $callRegCode = $formObj->getTBRow('Call Registry Code', 'call_registry_code', $row['call_registry_code'], $expNoEdit);
        }

        $fieldset1 = "
        {$formObj->getTBRow('', "error_box", '', $expNoEdit)}
        {$callRegCode}
        {$staffName}
        <!--{$formObj->getTimeRow('Call Time', 'contact_time', $row['contact_time'], $expNoEdit)}-->
		{$formObj->getDateRow('Call Time & Date', 'contact_date', $row['contact_date'] . ' ' . $row['contact_time'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Industry *', 'title', $sqlIndustry, $row['title'], $expVl)}
		<div class = '{$otherIndustry}'>
	        {$formObj->getTBRow('Other Industry', 'other_industry', $row['other_industry'])}
	    </div>

        {$formObj->getTBRow('Company Name *', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Phone *', 'phone', $row['phone'])}
        {$formObj->getTBRow('Alternate Phone', 'alternate_phone', $row['alternate_phone'])}
        {$formObj->getTBRow('Contact Name', 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow('Job Title', 'job_title', $row['job_title'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
		{$category}
        {$formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Refference', 'reffer', $sqlReffer, $row['reffer'], $expVl)}
		<div class = 'reminderDateDisplay {$reminderDate}'>
            {$formObj->getDateRow('Reminder Date', 'follow_up_date', $row['follow_up_date'])}
	    </div>
        <!--{$formObj->getTARow('Purpose of call', 'requirements', $row['requirements'])}-->
        <div class ='noOfCandidateDisplay {$noOfCandidate}'>
            {$formObj->getTBRow('Number of Candidates Required', 'no_of_candidates', $row['no_of_candidates'])}
        </div>
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $row['description'], '0')}
        ";


        //{$formObj->getFieldSetWrapped('Description', $fieldset2)}
        $text = "
        {$formObj->getFieldSetWrapped('Call Registry Details', $fieldset1)}
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
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'call_registry_id');

        $convertOpp = '';
        $sendProfileToClient = '';

	    $formActionSendProfile = "index.php?module=manPower_callRegistry&_spAction=sendProfileToClientForm&call_registry_id={$record_id}&showHTML=0";

        if ($row['status'] == 'High Win Ratio') {
            $convertOpp ="
            <div class='floatbox  btnbackground'>
                <div class='button mb5'>
                    <a href='#' id='convertToOpportunity' call_registry_id='{$record_id}'>Convert To Opportunity</a>
                </div>
                ";

                $sendProfileToClient ="
                <div class='button mb5'>
                    <a href='{$formActionSendProfile}' id='sendProfileToClient'>Send Profile To Client</a>
                </div>
            </div>
            ";
        }
        if ($row['status'] == 'Follow up') {
                $sendProfileToClient ="
                <div class='button mb5'>
                    <a href='{$formActionSendProfile}' id='sendProfileToClient'>Send Profile To Client</a>
                </div>
            </div>
            ";
        }

        $recOpp    = $fn->getRecordRowByID('opportunity', 'call_registry_id', $record_id);
        if (is_array($recOpp)){
            $convertOpp = "
            <div class='floatbox btnbackground'>
                <div class='float_left mt10 mb5'>Converted to Opportunity</div>
            ";
        }

        $text = "
        {$convertOpp}
        <!--<div class='button mb5'>
            <a href='index.php?module=manPower_callRegistry&_spAction=duplicateCallDate&call_registry_id={$record_id}&showHTML=0' id='duplicate' call_registry_id='{$row['call_registry_id']}'>Duplicate</a>
        </div>
        <div class='button mb5'>
            <a href='#' id='createClientRec' call_registry_id='{$record_id}'>Create Client</a>
        </div>-->
        {$sendProfileToClient}
        <!--{$displayLinkData->getLinkPortalMain('manPower_callRegistry', 'manPower_opportunityLink', 'Candidate Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'manPower_callRegistry', 'attachment', $row)}
        -->
        {$comment->getView(array(
             'roomName' => 'manPower_callRegistry'
            ,'recordId' => $record_id
            ,'contactModule' => 'manPower_staff'
            ,'allowEdit' => false
            ,'allowDelete' => false
            ,'addReviewLbl' => 'Add Activity'
            ,'heading' => 'Activities'
        ))}
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



		if ($_SESSION['staff_type'] == 'Staff') {
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
        }


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

        $text = "
        <td class='fieldValueStatus'>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        {$staff}
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
            Call Date:
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

    /**
     *
     */
     function getSendProfileToClientForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
	    $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $localPath = CP_LOCAL_PATH_ALIAS;

        $call_registry_id  = $fn->getReqParam('call_registry_id');

        $site_id    = $fn->getSessionParam('cp_site_id');

        if($site_id){
            $sqlAppend = " AND site_id  = {$_SESSION['cp_site_id']}";
        }
        $emailDraft =  '';

        if($site_id ==  1){
            $emailDraft = $cpCfg['profileEmailDraftForSingapore'];
        }else if($site_id ==  2){
            $emailDraft = $cpCfg['profileEmailDraftForAbudhabi'];
        }
        if($emailDraft = ''){
            return "Please create a template for Email";
        }

        $formAction = "index.php?_topRm=marketing&module=manPower_callRegistry&_spAction=sendProfileToClientFormSubmit&showHTML=0";
        $rowCallRegistry     = $fn->getRecordRowByID('call_registry', 'call_registry_id', $call_registry_id);
        $rowStaff     = $fn->getRecordRowByID('staff', 'staff_id', $rowCallRegistry['staff_id']);
        $exp = array('appendSiteUrl' => 1);
        $pic = $media->getMediaPicture('manPower_staff', 'signature', $rowCallRegistry['staff_id'], $exp);

        $staffName    = $rowStaff['first_name'] . ' ' . $rowStaff['last_name'];
        $logo = "<img src='{$cpCfg['cp.siteUrl']}{$localPath}images/email_logo.png' border='0'>";
        $qrCode = "<img src='{$cpCfg['cp.siteUrl']}{$localPath}images/qr-code.jpg' border='0'>";
        $facebook = "<a href='https://www.facebook.com/WestramaManagementCompany'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/facebook.png' border='0'></a>";
        $google = "<a href='https://plus.google.com/103869950261474615388/'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/google.png' border='0'></a>";
        $linkedin = "<a href='http://sg.linkedin.com/company/westrama-management-company'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/linkedin.png' border='0'></a>";
        $twitter = "<a href='https://twitter.com/westrama'><img src='{$cpCfg['cp.siteUrl']}{$localPath}images/twitter.png' border='0'></a>";
        $link = $cpCfg['cp.siteUrl'] . 'Westrama Profile.pdf';

        $emailDraft = str_replace('[[staff_sign]]', $pic, $emailDraft);
        $emailDraft = str_replace('[[staff_name]]', $staffName, $emailDraft);
        $emailDraft = str_replace('[[designation]]', $rowStaff['designation'], $emailDraft);
        $emailDraft = str_replace('[[phone]]', $rowStaff['phone'], $emailDraft);
        $emailDraft = str_replace('[[logo]]', $logo, $emailDraft);
        $emailDraft = str_replace('[[qr_code]]', $qrCode, $emailDraft);
        $emailDraft = str_replace('[[facebook]]', $facebook, $emailDraft);
        $emailDraft = str_replace('[[google]]', $google, $emailDraft);
        $emailDraft = str_replace('[[linkedin]]', $linkedin, $emailDraft);
        $emailDraft = str_replace('[[twitter]]', $twitter, $emailDraft);
        $emailDraft = str_replace('[[link]]', $link, $emailDraft);

        $expNoEdit = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', 'profile_message', $emailDraft, $expNoEdit)}
            <input type='hidden' name='call_registry_id' value='{$call_registry_id}' />
        </form>
        ";
        return $text;

    }
}