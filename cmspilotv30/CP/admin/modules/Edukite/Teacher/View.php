<?
class CP_Admin_Modules_Edukite_Teacher_View extends CP_Common_Modules_Edukite_Teacher_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;
		$position = '' ;
		$positionLabel = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = "<a href='mailto:{$row['email']}'>{$row['email']}</a>";

        $hostName   = $_SERVER['HTTP_HOST'];

        if(strpos($hostName, 'scbc') !== false){
	       $position = "{$listObj->getListDataCell($row['address1'])}";
		}	
        
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['teacher_code'])}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$position}
            {$listObj->getListDataCell($email)}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['teacher_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['teacher_id'])}
            {$listObj->getListRowEnd($row['teacher_id'])}
            ";
            $rowCounter++ ;
        }

        $hostName   = $_SERVER['HTTP_HOST'];

        if(strpos($hostName, 'scbc') !== false){
	       $positionLabel = "{$listObj->getListHeaderCell('Position', $row['address1'])}";
		}	

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Staff Code', 't.teacher_code')}
        {$listObj->getListHeaderCell('First Name', 't.first_name')}
        {$listObj->getListHeaderCell('Last Name', 't.last_name')}
        {$positionLabel}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);

        $hostName   = $_SERVER['HTTP_HOST'];

		$position = '' ;
        if(strpos($hostName, 'scbc') !== false){
	       $position = "{$formObj->getTBRow('Position', 'address1', $row['address1'])}";
		}	
        
        
        $fielset1 = "
        {$formObj->getTBRow('Teacher Code', 'teacher_code', $row['teacher_code'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$position}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
		";

        $fielset2 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_postal_code', $row['address_postal_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
		";
		
        $fielset3 = "
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
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
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_teacher', 'picture', $row)}
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