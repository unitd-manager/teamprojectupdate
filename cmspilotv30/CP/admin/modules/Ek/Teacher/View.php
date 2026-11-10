<?
class CP_Admin_Modules_Ek_Teacher_View extends CP_Common_Modules_Ek_Teacher_View
{
    /**
     *
     */
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
            {$listObj->getGoToDetailText($rowCounter, $row['staff_code'])}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($email)}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['subjects'])}
            {$listObj->getListDataCell($row['staff_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['staff_id'])}
            {$listObj->getListRowEnd($row['staff_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Staff Code', 'st.staff_code')}
        {$listObj->getListHeaderCell('First Name', 'st.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'st.last_name')}
        {$listObj->getListHeaderCell('Email', 'st.email')}
        {$listObj->getListHeaderCell('Gender', 'st.gender')}
        {$listObj->getListHeaderCell('Phone', 'st.phone')}
        {$listObj->getListHeaderCell('Subject(s)', 'st.subjects')}
        {$listObj->getListHeaderCell('ID', 'st.staff_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'st.published', 'headerCenter')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);
        
        $fielset1 = "
        {$formObj->getTBRow('Staff Code', 'staff_code', $row['staff_code'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
		";
		
        $fielset2 = "
        {$formObj->getTBRow('Qualification', 'qualification', $row['qualification'])}
        {$formObj->getTBRow('University', 'university', $row['university'])}
        {$formObj->getTARow('Experience Details', 'experience', $row['experience'])}
		";

        $fielset3 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'city', $row['city'])}
        {$formObj->getTBRow('State', 'state', $row['state'])}
        {$formObj->getTBRow('Zip Code', 'zip_code', $row['zip_code'])}
        {$formObj->getDDRowBySQL('Country', 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
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
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'ek_teacher', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('ek_teacher', 'ek_classLink', 'Class Linked', $row)}
        {$displayLinkData->getLinkPortalMain('ek_teacher', 'ek_subjectLink', 'Subject Linked', $row)}
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

        $subject_id = $fn->getReqParam('subject_id');

        $sqlCombo = "SELECT subject_id, title FROM subject ORDER BY title";

        $text = "
        <td>
            <select name='subject_id'>
                <option value=''>Select Subject</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $subject_id)}
            </select>
        </td>

        ";        
        
        return $text;
    }
}