<?
class CP_Admin_Modules_Edukloud_Student_View extends CP_Common_Modules_Edukloud_Student_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $textSalutation    = '';
        $rowsSalutation    = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];

            if($cpCfg['m.common.contact.hasSalutation'] == 1){
                $rowsSalutation = "
                {$listObj->getListDataCell($row['salutation'] )}
                ";
            }

            $site = '';
            if($cpCfg['cp.hasMultiSites']){
                $site = "
                {$listObj->getListDataCell($row['site_title'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$rowsSalutation}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$site}
            {$listObj->getListPublishedImage($row['published'], $row['student_id'])}
            {$listObj->getListRowEnd($row['student_id'])}
            ";

            $rowCounter++ ;
        }

        if($cpCfg['m.common.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$listObj->getListHeaderCell('Salutation', 'c.salutation')}
            ";
        }

        $site = '';
        if($cpCfg['cp.hasMultiSites']){
            $site = "
            {$listObj->getListHeaderCell('Site', 'site_title')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$textSalutation}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$site}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        {$formObj->getTBRow('Email', 'email')}
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
        $textSalutation = '';

        if($cpCfg['m.common.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$formObj->getTBRow('Salutation', 'c.salutation', $row['salutation'])}
            ";
        }

        $passwordRow = '';
        $emailRow = '';
        if ($cpCfg['cp.hasPasswordSalt']) {
            $has_pwd = '';
            $lblPassword = 'Password';
            if ($row['pass_word'] != '') {
                $has_pwd = 1;
                $lblPassword = 'Change Password';
            }
            $passwordRow = "
            {$formObj->getTBRow($lblPassword, 'pass_word')}
            <input type='hidden' name='has_pwd' value='{$has_pwd}' />
            ";

            $exp = array('isEditable' => 0);
            $emailRow = "
            {$formObj->getTBRow('Email', 'email', $row['email'], $exp)}
            <input type='hidden' name='email' value='{$row['email']}' />
            ";

        } else {
            $passwordRow = $formObj->getTBRow('Password', 'pass_word', $row['pass_word']);
            $emailRow = $formObj->getTBRow('Email', 'email', $row['email']);
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $sqlCourse        = $fn->getDDSql('edukloud_course');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlLanguage      = $fn->getValueListSQL('language');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expVL = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['first_name'] , $expVL)}
        {$formObj->getTBRow('ID Card No.', 'id_card_no', $row['id_card_no'])}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') - 10))}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}
        {$formObj->getTBRow('Mobile', 'emergency_contact_mobile', $row['emergency_contact_mobile'])}
        {$formObj->getTBRow('Office Contact No.', 'emergency_contact_office_no', $row['emergency_contact_office_no'])}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City / Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'])}
        ";


        $fieldset8 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$passwordRow}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $row['subscribe'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Emergency Contact Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Address Details', $fieldset3)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset8)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay("Picture", "edukloud_student", "picture", $row)}
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