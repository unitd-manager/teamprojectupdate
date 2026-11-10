<?
class CP_Admin_Modules_Edukite_Student_View extends CP_Common_Modules_Edukite_Student_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['student_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListPublishedImage($row['published'], $row['student_id'])}
            {$listObj->getListDataCell($row['student_id'], 'center')}
            {$listObj->getListRowEnd($row['student_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Reg.No', 's.student_code')}
        {$listObj->getListHeaderCell('First Name', 's.first_name')}
        {$listObj->getListHeaderCell('Last Name', 's.last_name')}
        {$listObj->getListHeaderCell('Email', 's.email')}
        {$listObj->getListHeaderCell('Gender', 's.email')}
        {$listObj->getListHeaderCell('Phone', 's.mobile')}
        {$listObj->getListHeaderCell('Status', 's.status')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 's.student_id', 'headerCenter')}
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
        {$formObj->getTBRow('Email', 'email')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $expCountry = array('detailValue' => $row['country']);

        $expVl   = array('sqlType' => 'OneField');
        $sqlType = $fn->getValueListSQL('status');
        $gendArr = array('Male', 'Female');

        $fielset1 = "
        {$formObj->getTBRow('Reg.No', 'student_code', $row['student_code'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getRRow('Gender', 'gender', $row['gender'], $gendArr, array('rowCls' => 'yesNo'))}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
		";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Status', 'status', $sqlType, $row['status'], $expVl)}
        {$formObj->getDateRow('Joined Date', 'date_joined', $row['date_joined'])}
		";

        $fielset3 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_postal_code', $row['address_postal_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
		";

        $fielset4 = "
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Academic Details', $fielset2)}
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
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getIssetParam($row, 'student_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_student', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('edukite_student', 'edukite_parentLink', 'Parent Linked', $row)}
        {$displayLinkData->getLinkPortalMain('edukite_student', 'edukite_classLink', 'Class Linked', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }
}