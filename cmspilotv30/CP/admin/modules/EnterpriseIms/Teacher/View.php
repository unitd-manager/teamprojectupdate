<?
class CP_Admin_Modules_EnterpriseIms_Teacher_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = "<a href='mailto:{$row['email']}'>{$row['email']}</a>";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['teacher_code'])}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($email)}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['teacher_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['teacher_id'])}
            {$listObj->getListRowEnd($row['teacher_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Teacher Code', 't.teacher_code')}
        {$listObj->getListHeaderCell('First Name', 't.first_name')}
        {$listObj->getListHeaderCell('Last Name', 't.last_name')}
        {$listObj->getListHeaderCell('Email', 't.email')}
        {$listObj->getListHeaderCell('Gender', 't.gender')}
        {$listObj->getListHeaderCell('Phone', 't.phone')}
        {$listObj->getListHeaderCell('ID', 't.teacher_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 't.published', 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl   = array('sqlType' => 'OneField');
        $sqlGender = $fn->getValueListSQL('gender');
        $sqlTrainer = $fn->getValueListSQL('trainerType');
        $sqlWorking = $fn->getValueListSQL('modeOfWorking');
        $sqlAvailability = $fn->getValueListSQL('workAvailability');
        $sqlSalutation = $fn->getValueListSQL('salutation');
        $sqlPayment = $fn->getValueListSQL('paymentType');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);
        
        $fielset1 = "
        {$formObj->getTBRow('Teacher Code', 'teacher_code', $row['teacher_code'])}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVl)}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getDDRowBySQL('Trainer Type', 'trainer_type', $sqlTrainer, $row['trainer_type'], $expVl)}
        {$formObj->getDDRowBySQL('Mode of Working', 'mode_of_working', $sqlWorking, $row['mode_of_working'], $expVl)}
        {$formObj->getDDRowBySQL('Work Availability', 'work_availability', $sqlAvailability, $row['work_availability'], $expVl)}
        {$formObj->getDDRowBySQL('Payment Type', 'payment_type', $sqlPayment, $row['payment_type'], $expVl)}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTARow('Bank Details', 'bank_details', $row['bank_details'])}
		";
		
        $fielset2 = "
        {$formObj->getTBRow('Qualification', 'qualification', $row['qualification'])}
        {$formObj->getTBRow('University', 'university', $row['university'])}
        {$formObj->getTARow('Experience Details', 'experience', $row['experience'])}
		";

		if ($row['country_code'] == ''){
			$country = 'SG';
		} else {
			$country = $row['country_code'];
		
		}

        $fielset3 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'city', $row['city'])}
        {$formObj->getTBRow('State', 'state', $row['state'])}
        {$formObj->getTBRow('Zip Code', 'zip_code', $row['zip_code'])}
        {$formObj->getDDRowBySQL('Country', 'country_code', $sqlCountry, $country, $expCountry)}
		";
		
        $fielset4 = "
        {$formObj->getYesNoRRow('Login Enabled', 'login_enabled', $row['login_enabled'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTARow('Remarks', 'remarks', $row['remarks'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Career Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Address Details', $fielset3)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'enterpriseIms_teacher', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'enterpriseIms_teacher', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('enterpriseIms_teacher', 'enterpriseIms_courseLink', 'Course Linked', $row)}
        {$displayLinkData->getLinkPortalMain('enterpriseIms_teacher', 'enterpriseIms_batchLink', 'Batch Linked', $row)}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $trainer_type = $fn->getReqParam('trainer_type');
        $course_id   = $fn->getReqParam('course_id');

        $sqlTrainerType = $fn->getValueListSQL('trainerType');
        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        ";

        $text = "
        <td>
            <select name='trainer_type'>
                <option value=''>Trainer Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlTrainerType, $trainer_type)}
            </select>
        </td>    
        <td>
            <select name='course_id' >
                <option value=''>Course</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>
        ";
        
        return $text;
    }
}