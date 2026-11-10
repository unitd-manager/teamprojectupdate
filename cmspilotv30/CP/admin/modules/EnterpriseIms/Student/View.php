<?
class CP_Admin_Modules_EnterpriseIms_Student_View extends CP_Common_Modules_EnterpriseIms_Student_View
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
            {$listObj->getListDataCell($row['company_title'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$site}
            {$listObj->getListPublishedImage($row['published'], $row['student_id'])}
            {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
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
        {$listObj->getListHeaderCell('Company Name', 'company_title')}
        {$listObj->getListHeaderCell('Phone', 's.phone')}
        {$listObj->getListHeaderCell('Mobile', 's.mobile')}
        {$site}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Subscribed', 'c.subscribe', 'headerCenter')}
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

        $sqlCourse        = $fn->getDDSql('enterpriseIms_course');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlLanguage      = $fn->getValueListSQL('language');
        $sqlQual          = $fn->getValueListSQL('educationalQualification');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlSalaryRange   = $fn->getValueListSQL('salaryRange');
        $expVL = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['first_name'] , $expVL)}
        {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
        {$formObj->getTBRow('ID Card No.', 'id_card_no', $row['id_card_no'])}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') - 10))}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}
        {$formObj->getTBRow('Mobile', 'emergency_contact_mobile', $row['emergency_contact_mobile'])}
        {$formObj->getTBRow('Office Contact No.', 'emergency_contact_office_no', $row['emergency_contact_office_no'])}
        ";

        $sqlComp = $fn->getDDSql('enterpriseIms_company');
        $expComp  = array('detailValue' => $row['c_company_name']);

        $fieldset3 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City / Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'])}
        ";

        $fieldset4 = "
        {$formObj->getTBRow('School / College Name', 'school_name', $row['school_name'])}
        {$formObj->getDDRowBySQL('Country', 'school_country', $sqlCountry, $row['school_country'])}
        {$formObj->getTBRow('From', 'school_from', $row['school_from'])}
        {$formObj->getTBRow('To', 'school_to', $row['school_to'])}
        {$formObj->getDDRowBySQL('Highest Qualification', 'school_highest_qual', $sqlQual, $row['school_highest_qual'], $expVL)}
        ";

        $fieldset5 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        ";

        $fieldset6 = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('ROC No.', 'company_roc_no', $row['company_roc_no'])}
        {$formObj->getTARow('Address', 'company_address', $row['company_address'])}
        {$formObj->getTBRow('Postal Code', 'company_po_code', $row['company_po_code'])}
        {$formObj->getTBRow('Phone (Main)', 'company_phone', $row['company_phone'])}
        {$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'])}
        ";

        $fieldset7 = "
        {$formObj->getTBRow('Phone (Direct)', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Designation', 'position', $row['position'])}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        {$formObj->getTBRow('Years of Working Experience', 'yr_of_exp', $row['yr_of_exp'])}
        {$formObj->getDDRowBySQL('Salary Code', 'salary_range', $sqlSalaryRange, $row['salary_range'], $expVL)}
        {$formObj->getYesNoRRow('Applying for SDF?', 'apply_for_sdf', $row['apply_for_sdf'])}
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
        {$formObj->getFieldSetWrapped('Highest Education Qualification', $fieldset4)}
        {$formObj->getFieldSetWrapped('Company Details (relational)', $fieldset5)}
        {$formObj->getFieldSetWrapped('Company Details (local)', $fieldset6)}
        {$formObj->getFieldSetWrapped('Employment Details', $fieldset7)}
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
        {$media->getRightPanelMediaDisplay("Picture", "enterpriseIms_student", "picture", $row)}
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