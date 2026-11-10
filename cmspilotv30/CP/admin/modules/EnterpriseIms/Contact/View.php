<?
class CP_Admin_Modules_EnterpriseIms_Contact_View extends CP_Common_Modules_EnterpriseIms_Contact_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $rows = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];
			$name = strtoupper($row['first_name']);	

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['registration_no'])}
            {$listObj->getGoToDetailText($rowCounter, $name)}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDateCell($row['date_of_birth'])}
            {$listObj->getListDataCell($row['age'])}
            {$listObj->getListDataCell($row['student_registered_course'])}
            {$listObj->getListDataCell($row['student_registered_batch'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('REG. NO', 'c.registration_no')}
        {$listObj->getListHeaderCell('NAME', 'c.first_name')}
        {$listObj->getListHeaderCell('GENDER', 'c.gender')}
        {$listObj->getListHeaderCell('DOB', 'c.date_of_birth')}
        {$listObj->getListHeaderCell('AGE', 'c.age')}
        {$listObj->getListHeaderCell(strtoupper($modulesArr['enterpriseIms_course']['title']), 'student_registered_course')}
        {$listObj->getListHeaderCell(strtoupper($modulesArr['enterpriseIms_batch']['title']), 'student_registered_batch')}
        {$listObj->getListHeaderCell('STATUS', 'c.status')}
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
        {$formObj->getTBRow('Name', 'first_name')}
        {$formObj->getTBRow('ID Card No.', 'id_card_no')}
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

        $sqlGender = $fn->getValueListSQL('gender');
        $sqlAcademicSchool = $fn->getValueListSQL('academicSchool');
        $sqlStatus = $fn->getValueListSQL('studentStatus');
        $expVL = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $formActionStatus = "index.php?module=enterpriseIms_contact&_spAction=changeStatusForm&contact_id={$row['contact_id']}&showHTML=0";
        
        $sqlParent = "
        UPDATE student SET status = 'Active'
        WHERE status = 'Withdraw'
        ";

        if($row['status'] == 'Withdraw') {
            $statusBtn ="
            <div class='button'>
                <a id='actBtn_statusToActive' contact_id={$row['contact_id']} href='#'>Change to Active</a>
            </div>
            ";
        } else {
            $statusBtn ="
            <div class='button'>
                <a id='actBtn_status' href='{$formActionStatus}' contact_id={$row['contact_id']}>Change Status</a>
            </div>
            ";
        }
        
        $formatDisplay = '';
        if ($tv['action'] == 'edit') {
            $formatDisplay = "
            <div class='non-editable type-text ym-fbox-text'>
                <label></label>
                <div class='txt'>YYYY-MM-DD</div>
            </div>
            ";
        }
        
        $fieldset1 = "
        {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'], $expNoEdit)}
        {$formObj->getTBRow('Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('NRIC No.', 'id_card_no', $row['id_card_no'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formatDisplay}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y')))}
        {$formObj->getTBRow('Age', 'age', $row['age'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
        {$formObj->getTBRow('Year of Joining', 'year_of_joining', $row['year_of_joining'])}
        <!--
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'] , $expVL)}
        -->
        {$formObj->getTBRow('Status', 'status', $row['status'], $expNoEdit)}
        <!--
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        -->
        {$statusBtn}
        ";

        $contact_id = $row['contact_id'];

        $sqlParent = "
        SELECT p.first_name AS parent_name
        FROM parent_contact pc
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (contact c) ON (c.contact_id = pc.contact_id)
        WHERE c.contact_id = {$contact_id}
        ";
        $expPar = array('sqlType' => 'OneField');

        $fieldset2 = "
		{$formObj->getTARow('Reasons for Withdrawal', 'with_drawal', $row['with_drawal'])}
		{$formObj->getDDRowBySQL('Refund Payable To', 'refund_payable', $sqlParent, $row['refund_payable'], $expPar)}
		{$formObj->getTBRow('Bank Ac', 'refund_payable_bank_ac', $row['refund_payable_bank_ac'])}
		";
		
        $fieldset3 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $row['subscribe'])}
        ";
        
        $withdrawDetails = '';
        if($row['with_drawal'] != '' && $row['refund_payable'] != '' && $row['refund_payable_bank_ac'] != ''){
            $withdrawDetails = $formObj->getFieldSetWrapped('Withdraw Details', $fieldset2);
        }
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$withdrawDetails}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
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

        $record_id = $fn->getIssetParam($row, 'contact_id');
		$urlWithdrawalForm = "index.php?module=enterpriseIms_contact&_spAction=printWithdrawalForm&contact_id={$row['contact_id']}&showHTML=0";

		$withdrawalForm = "";
		if($row['status'] == 'Withdraw' || $row['refund_payable'] || $row['refund_payable']){
			$withdrawalForm = "
	            <div class='button mb5'>
	                <a href='{$urlWithdrawalForm}' target='_blank' id='withdrawalForm'>Withdrawal Form</a>
	            </div> 
			";
		}

        $text = "
		{$withdrawalForm}
        {$media->getRightPanelMediaDisplay('Picture', 'enterpriseIms_contact', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'enterpriseIms_contact', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('enterpriseIms_contact', 'enterpriseIms_courseLink', 'Courses Linked', $row)}
        {$displayLinkData->getLinkPortalMain('enterpriseIms_contact', 'enterpriseIms_parentLink', 'Parent Linked', $row)}
        {$comment->getView(array(
             'roomName' => 'enterpriseIms_contact'
            ,'recordId' => $record_id
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id     		  = $fn->getReqParam('interest_id');
        $course_id       		  = $fn->getReqParam('course_id');
        $batch_id        		  = $fn->getReqParam('batch_id');
        $subscribe       		  = $fn->getReqParam('subscribe');
        $special_search  		  = $fn->getReqParam('special_search');
        $category        		  = $fn->getReqParam('category');
        $status          		  = $fn->getReqParam('status');
        $enrollment_year 		  = $fn->getReqParam('enrollment_year');
        $continuing_to_next_year  = $fn->getReqParam('continuing_to_next_year');
        
        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }
        
        $previous_year = date('Y') - 1;
        $next_year = date('Y') + 1;
        $yearArray = array(
              $previous_year
             ,date('Y')
             ,$next_year
        );
        $sqlStudentStatus = $fn->getValueListSQL('studentStatus');

        $interestText   = "";
        $batchText = '';
        $whereCondtBatch= "";
        $sqlBatch = '';

        $sqlInterest = "
        SELECT a.interest_id, a.title
        FROM interest a
        ";

        // This is used to display Batch in Search
        if ($course_id){
            $sqlBatch = "
            SELECT Distinct b.batch_id, b.title
            FROM course_contact cc
            LEFT JOIN (batch b) ON ( b.batch_id = cc.batch_id )
            WHERE cc.course_id = $course_id ;
            ";
        }

        $batchText = "
        <td>
            <select name='batch_id' >
                <option value=''>Session</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            </select>
        </td>
        ";

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
             ,"Batch-Not-Linked"
        );

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        WHERE c.published = 1
        ORDER BY c.sort_order ASC
        ";

        $spArrayContinuation = array(
              "Yes"
             ,"No"
        );

        $text = "
        <td>
            <select name='course_id' >
                <option value=''>Class</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>

        {$batchText}

        {$interestText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>

        <td>
            <select name='enrollment_year'>
                <option value=''>Select Year</option>
                {$cpUtil->getDropDown1($yearArray, $enrollment_year)}
            </select>
        </td>

        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStudentStatus, $status)}
            </select>
        </td>
        <td>
            <select name='continuing_to_next_year'>
                <option value=''>Continuation Form Received</option>
                {$cpUtil->getDropDown1($spArrayContinuation, $continuing_to_next_year)}
            </select>
        </td>
        
        ";

        return $text;
    }

    /**
     *
     */
    function getImportInstructions() {
        return;
        $cpPaths = Zend_Registry::get('cpPaths');
        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=enterpriseIms_contact&filename=contact-import-template.xls';
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";

        return $text;
    }

    /**
     * @return string
    */
    function getAdditionalImportFields(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if($cpCfg['m.common.contact.showInterestInImport']){
            $sqlInterest = $fn->getDDSql('common_interest');
            $text .= $formObj->getDDRowBySQL('Interest', 'interest_id', $sqlInterest);
        }

        return $text;
    }

    /**
     *
     */
    function getChangeStatusForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        
        $contact_id  = $fn->getReqParam('contact_id');

        $formAction = "index.php?_topRm=order&module=enterpriseIms_contact&_spAction=changeStatusFormSubmit&showHTML=0";
        $contactRec = $fn->getRecordRowById('contact', 'contact_id', $contact_id);

        $sqlParent = "
        SELECT p.first_name AS parent_name
        FROM parent_contact pc
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (contact c) ON (c.contact_id = pc.contact_id)
        WHERE c.contact_id = {$contact_id}
        ";
        $expPar = array('sqlType' => 'OneField');
        
        $text = "
        <form id='changeStatusForm' class='yform columnar' method='post' action='{$formAction}'>
    		{$formObj->getTARow('Reasons for Withdrawal', 'with_drawal', $contactRec['with_drawal'])}
			{$formObj->getDDRowBySQL('Refund Payable To', 'refund_payable', $sqlParent, $contactRec['refund_payable'], $expPar)}
    		{$formObj->getTBRow('Bank Ac', 'refund_payable_bank_ac', $contactRec['refund_payable_bank_ac'])}
            <input type='hidden' name='contact_id' value='{$contact_id}' />
        </form>
        ";
        return $text;

    }
    
    /**
     *
     */    
     function getChangeStatusToActive() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $contact_id = $fn->getReqParam('contact_id');        
        $contactRec = $fn->getRecordRowById('contact', 'contact_id', $contact_id);

        $SQL = "
        UPDATE contact SET status = 'Active'
        WHERE contact_id = {$contact_id}
        ";
        $result = $db->sql_query($SQL);
        
    }
}
