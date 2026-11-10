<?
class CP_Admin_Modules_Pms_Contact_View extends CP_Common_Modules_Pms_Contact_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $textSalutation     = '';
        $rowsSalutation     = '';
        $textRegistrationNo = '';
        $rowsRegistrationNo = '';
        $textCompany        = '';
        $rowsCompany        = '';
        $textSubscribed     = '';
        $rowsSubscribed     = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];

            if($cpCfg['m.common.contact.hasSalutation'] == 1){
                $rowsSalutation = "
                {$listObj->getListDataCell($row['salutation'] )}
                ";
            }

            // This is used to dispaly Registration No : Used for Mass IMS
            if($cpCfg['m.pms.contact.hasRegistrationNo']){
                $rowsRegistrationNo = "
                {$listObj->getListDataCell($row['registration_no'])}
                ";
            }

            // This is used to dispaly Company in list view : Used for Mass IMS
            if($cpCfg['m.pms.contact.showCompanyInList']){
                $rowsCompany = "
                {$listObj->getListDataCell($row['company_title'])}
                ";
            }

            // This is used to dispaly Subscribe in list view : Used for Mass IMS
            if($cpCfg['m.pms.contact.showSubscribedInList']){
                $rowsSubscribed = "
                {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
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
            {$rowsRegistrationNo}
            {$listObj->getListDataCell($row['id_card_no'])}
            {$rowsCompany}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$site}
            {$listObj->getListPublishedImage($row['published'], $row['contact_id'])}
            {$rowsSubscribed}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        if($cpCfg['m.common.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$listObj->getListHeaderCell('Salutation', 'c.salutation')}
            ";
        }

        // This is used to dispaly Registration No : Used for Mass IMS
        if($cpCfg['m.pms.contact.hasRegistrationNo']){
            $textRegistrationNo = "
            {$listObj->getListHeaderCell('Registration No', 'c.registration_no')}
            ";
        }

        // This is used to dispaly Company in list view : Used for Mass IMS
        if($cpCfg['m.pms.contact.showCompanyInList']){
            $textCompany = "
            {$listObj->getListHeaderCell('Company Name', 'company_title')}
            ";
        }

        // This is used to dispaly Subscribe in list view : Used for Mass IMS
        if($cpCfg['m.pms.contact.showSubscribedInList']){
            $textSubscribed = "
            {$listObj->getListHeaderCell('Subscribed', 'c.subscribe', 'headerCenter')}
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
        {$textRegistrationNo}
        {$listObj->getListHeaderCell('NRIC / Passport No. / FIN', 'c.id_card_no')}
        {$textCompany}
        {$listObj->getListHeaderCell('Phone', 's.phone')}
        {$listObj->getListHeaderCell('Mobile', 's.mobile')}
        {$site}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
        {$textSubscribed}
        {$listObj->getListHeaderEnd()}
    		{$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
    */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('NRIC / Passport No.', 'id_card_no')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
    */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode  = $tv['action'];

        $textSalutation = '';
        $textRegisterNo = '';
        $textParentIDCardNo = '';

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

        $sqlCourse        = $fn->getDDSql('pms_course');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlLanguage      = $fn->getValueListSQL('language');
        $sqlQual          = $fn->getValueListSQL('educationalQualification');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlSalaryRange   = $fn->getValueListSQL('salaryRange');
        $sqlCompanyRegistrationType = $fn->getValueListSQL('companyRegistrationType');
        $expVL = array('sqlType' => 'OneField');

        //$date_of_birth = $dateUtil->formatDate($row['date_of_birth'], 'YYYY MM DD');

        $sqlComp = $fn->getDDSql('pms_company');
        $expComp  = array('detailValue' => $row['c_company_name']);
        $expCountryName  = array('detailValue' => $row['country_name']);
        $expNoEdit = array('isEditable' => 0);

        // This is used to dispaly Register No : Used for Mass IMS
        if($cpCfg['m.pms.contact.hasRegisterNo']){
            $textRegisterNo = "
            {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'])}
            ";
        }

        // This is used to dispaly Student Pass : Used for Mass IMS
        $textStudentPass = '';
        if($cpCfg['m.pms.contact.hasStudentPass']){
            $textStudentPass = "
            {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', $row['student_pass_holder'])}
            ";
        }

        // This is used to dispaly Parent ID Card NO : Used for Mass IMS
        if($cpCfg['m.pms.contact.hasParentIDCardNo']){
            $textParentIDCardNo = "
            {$formObj->getTBRow('NRIC / Passport No.', 'parent_id_card_no', $row['parent_id_card_no'])}
            ";
        }

        $additionalDetails = '';
        // This is used to dispaly Other Details for Pvt : Used for Mass IMS
        if($cpCfg['m.pms.contact.otherDetailsPvt']){
            $hide = ($row['is_citizen'] == 1 || $row['student_pass_holder'] == 1) ? " hideme" : '';
            $additionalDetails = "
            <div class='citizenNo{$hide}'>
                {$formObj->getDateRow('Date of Arrival', 'date_of_arrival', $row['date_of_arrival'])}
                {$formObj->getDDRowBySQL('Passport Country of Issue', 'passport_country_issued', $sqlCountry, $row['passport_country_issued'])}
                {$formObj->getTBRow('Overseas Address 1', 'overseas_address_flat', $row['overseas_address_flat'])}
                {$formObj->getTBRow('Overseas Address 2', 'overseas_address_street', $row['overseas_address_street'])}
                {$formObj->getTBRow('Overseas Postal Code', 'overseas_address_po_code', $row['overseas_address_po_code'])}
                {$formObj->getDDRowBySQL('Overseas Country', 'overseas_address_country', $sqlCountry, $row['overseas_address_country'])}
                {$formObj->getTBRow('Overseas Number', 'overseas_contact_no', $row['overseas_contact_no'])}
                {$formObj->getDDRowBySQL('Parent Passport Country of Issue', 'parent_passport_country_issued', $sqlCountry, $row['parent_passport_country_issued'])}
                {$formObj->getDDRowBySQL('Parent Nationality', 'parent_nationality', $sqlNationality, $row['parent_nationality'], $expVL)}
                {$formObj->getTBRow('Parent Occupation', 'parent_occupation', $row['parent_occupation'])}
                {$formObj->getDateRow('STP Application Date', 'stp_application_date', $row['stp_application_date'])}
                {$formObj->getDateRow('STP IPA Date', 'stp_ipa_date', $row['stp_ipa_date'])}
                {$formObj->getDateRow('STP Issue Date', 'stp_issue_date', $row['stp_issue_date'])}
                {$formObj->getDateRow('STP Expiry Date', 'stp_expiry_date', $row['stp_expiry_date'])}
                {$formObj->getDateRow('STP Cancellation Date', 'stp_cancellation_date', $row['stp_cancellation_date'])}
            </div>
            ";
        }

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$textRegisterNo}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
        {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
        {$formObj->getTBRow('NRIC / Passport No. / FIN', 'id_card_no', $row['id_card_no'])}
        {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
        {$textStudentPass}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";

        $companyNewLink = "index.php?module=pms_company&_spAction=companyNew&showHTML=0";
        $fieldset2 = "
        <div><a href='{$companyNewLink}' class='newCompany'><strong><u>Click to Add New Company</a></u></strong></div>
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getTBRow('Business Reg. No.', 'c_reg_number', $row['c_reg_number'], $expNoEdit)}
        {$formObj->getTBRow('Address 1', 'c_address_flat', $row['c_address_flat'], $expNoEdit)}
        {$formObj->getTBRow('Address 2', 'c_address_street', $row['c_address_street'], $expNoEdit)}
        {$formObj->getTBRow('Country', 'c_country_name', $row['c_country_name'], $expNoEdit)}
        {$formObj->getTBRow('Postal Code', 'c_address_po_code', $row['c_address_po_code'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'c_phone', $row['c_phone'], $expNoEdit)}
        {$formObj->getTBRow('Type of Reg.', 'c_category', $row['c_category'], $expNoEdit)}
        <div class='companyDetails'>
        </div>
        ";

		if ($row['address_country'] == ''){
			$country = 'SG';
		} else {
			$country = $row['address_country'];
		
		}

        $fieldset3 = "
        {$formObj->getTBRow('Parent Name', 'emergency_contact_name', $row['emergency_contact_name'])}
        {$textParentIDCardNo}
        {$formObj->getTBRow('Mobile', 'emergency_contact_mobile', $row['emergency_contact_mobile'])}
        {$formObj->getTBRow('Office Contact No.', 'emergency_contact_office_no', $row['emergency_contact_office_no'])}
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $country, $expCountryName)}
        ";

        $fieldset4 = "
        {$formObj->getTBRow('School / College Name', 'school_name', $row['school_name'])}
        {$formObj->getDDRowBySQL('Country', 'school_country', $sqlCountry, $row['school_country'])}
        {$formObj->getTBRow('From', 'school_from', $row['school_from'])}
        {$formObj->getTBRow('To', 'school_to', $row['school_to'])}
        {$formObj->getDDRowBySQL('Highest Qualification', 'school_highest_qual', $sqlQual, $row['school_highest_qual'], $expVL)}
        ";

        /*$fieldset5 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'])}
        ";*/

        $fieldset6 = "
        {$formObj->getTBRow('Phone (Direct)', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Designation', 'position', $row['position'])}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        {$formObj->getTBRow('Years of Working Experience', 'yr_of_exp', $row['yr_of_exp'])}
        {$formObj->getDDRowBySQL('Salary Code', 'salary_range', $sqlSalaryRange, $row['salary_range'], $expVL)}
        {$formObj->getYesNoRRow('Applying for SDF?', 'apply_for_sdf', $row['apply_for_sdf'])}
        ";

        $fieldset7 = "
        {$additionalDetails}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$passwordRow}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $row['subscribe'])}
        ";

        if($cpCfg['m.pms.contact.otherDetailsPvt'] == false){
            $companyFields = $formObj->getFieldSetWrapped('Company Details', $fieldset2);
            $educationLevel = $formObj->getFieldSetWrapped('Educational Level', $fieldset4);
            $employmentDetails = $formObj->getFieldSetWrapped('Employment Details', $fieldset6);
        }
        else{
            $companyFields = '';
            $educationLevel = '';;
            $employmentDetails = '';
        }

        /*<div class='header' expanded = 1>&nbsp;</div>
        <div class='toggle plus minus'>&nbsp;</div>
        <div class='linkPortalWrapper' style='display: block;'>
        </div>*/
        $text = "
        {$formObj->getFieldSetWrapped('Mandatory Fields', '')}
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$companyFields}
            {$formObj->getFieldSetWrapped('Non Mandatory Fields', '')}
            {$formObj->getFieldSetWrapped('Emergency Contact Details', $fieldset3)}
            {$educationLevel}
            {$employmentDetails}
            {$formObj->getFieldSetWrapped('Other Details', $fieldset7)}
            {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
    */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $rows = "";
        $links= "";
        $printText = '';
        // This is nullify the session variable stored for subject
        if (isset($_SESSION['selectedSubjectIds'])){
            unset($_SESSION['selectedSubjectIds']);
        }
        $_SESSION['selectedSubjectIds'] = array();


        /*
        $contractUrl = "index.php?module=pms_contact&_spAction=createStudentContract&id={$row['contact_id']}&showHTML=0";
        $rows .="
        <div class='floatbox'>
            <div class='float_right button mb5 mr5'>
                <a href='{$contractUrl}' id='createStudentContract'>Create Student Contract</a>
            </div>
        </div>
        ";
        */

        if($cpCfg['m.pms.contact.showCourseLink']){
            $rows .= $displayLinkData->getLinkPortalMain("pms_contact", "pms_courseLink", "Courses Linked", $row);
        }

        // This is used to display Course Link portal for Pvt : Used in Mass IMS
        if($cpCfg['m.pms.contact.showCourseLinkPvt']){
            $rows .= $this->getCoursePortalDisplay($row);
        }

        if($cpCfg['m.common.contact.showEvent'] == 1){
            $rows .= $displayLinkData->getLinkPortalMain("pms_contact", "event_eventLink", "Events Linked", $row);
        }

        if($cpCfg['m.common.contact.showContentLink'] == 1){
            $links .= $displayLinkData->getLinkPortalMain("pms_contact", "webBasic_contentLink", "Content Linked", $row);
        }

        if($cpCfg['m.pms.contact.showParentLink']){
            $links .= $displayLinkData->getLinkPortalMain("pms_contact", "pms_parentLink", "Parent Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'contact_id');


        /*
        // This is used to display the print buttons : Used IN Mass IMS
        if($cpCfg['m.pms.contact.hasPrintBtns']){
            $urlForm12 = "index.php?module=pms_contact&_spAction=printForm12&id={$record_id}&showHTML=0";
            $urlStudentContract = "index.php?module=pms_contact&_spAction=printStudentContract&id={$record_id}&showHTML=0";
            $urlOfferLetter = "index.php?module=pms_contact&_spAction=printOfferLetter&id={$record_id}&showHTML=0";
            $printText .="
            <div class='floatbox actionBtnsDetail'>
                <div class='float_right button mb5'>
                    <a href='{$urlForm12}' id='printForm12'>Print Form 12</a>
                </div>
                <div class='float_right button mb5'>
                    <a href='{$urlStudentContract}' id='printStudentContract'>Print Student Contract</a>
                </div>
                <div class='float_right button mb5'>
                    <a href='{$urlOfferLetter}' id='printOfferLetter'>Print Offer Letter</a>
                </div>
                <div class='float_right mb5'>
                    <h3>PRINT</h3>
                </div>
            </div>
            ";
        }
        */

        $text = "
        {$printText}
        {$media->getRightPanelMediaDisplay('Picture', 'pms_contact', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'pms_contact', 'attachment', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'pms_contact'
            ,'recordId' => $record_id
        ))}
        {$links}
        ";

        return $text;
    }

    /**
    */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $course_id      = $fn->getReqParam('course_id');
        $batch_id       = $fn->getReqParam('batch_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $course_status  = $fn->getReqParam('course_status');

        $sqlCourseStatus = $fn->getValueListSQL('courseStatus');

        $interestText   = "";
        $batchText = '';
        $whereCondtBatch= "";
        $sqlBatch = '';

        $sqlInterest = "
        SELECT a.interest_id, a.title
        FROM interest a
        ";

        // This is used to display Interest in Search
        /*if($cpCfg['m.pms.contact.showInterestInSearch']){
            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
                </select>
            </td>
            ";
        }*/

        // This is used to display Batch in Search
        if($cpCfg['m.pms.contact.showBatchInSearch']){
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
                    <option value=''>Batch</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
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
             ,"Batch-Not-Linked"
        );

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        WHERE c.published = 1
        ORDER BY c.sort_order ASC
        ";

        $text = "
        <td>
            <select name='course_id' >
                <option value=''>Course</option>
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
            <select name='course_status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCourseStatus, $course_status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
    */
    function getImportInstructions() {
        return;
        $cpPaths = Zend_Registry::get('cpPaths');
        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=pms_contact&filename=contact-import-template.xls';
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
    */
    function getPopulateCompanyDetails(){
        $fn = Zend_Registry::get('fn');

        $company_id = $fn->getReqParam('company_id');
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $company_id);
        $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$companyRec['address_country_code']}'");

        $text = "
        <div class='type-text non-editable'>
            <label>Business Reg. No.</label>
            <div class='txt'>{$companyRec['reg_number']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Address 1</label>
            <div class='txt'>{$companyRec['address1']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Address 2</label>
            <div class='txt'>{$companyRec['address2']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Country</label>
            <div class='txt'>{$countryRec['name']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Postal Code</label>
            <div class='txt'>{$companyRec['address_po_code']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Phone</label>
            <div class='txt'>{$companyRec['phone']}</div>
        </div>

        <div class='type-text non-editable'>
            <label>Type of Reg.</label>
            <div class='txt'>{$companyRec['category']}</div>
        </div>
        ";

        return $text;
    }

    /**
    */
    function getCoursePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $rows = "";
        $links= "";

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.discount
              ,cc.course_status
              ,cc.registration_type
              ,c.title AS course_title
              ,c.course_type
              ,c.valid_date_from
              ,c.valid_date_to
              ,b.title AS batch_title
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              ,o.order_date
              ,o.order_id
        FROM course_contact cc
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN contact cont ON (cont.contact_id = cc.contact_id)
        LEFT JOIN `order` o ON (o.order_id = cc.order_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        WHERE cc.contact_id = {$row['contact_id']}
        ORDER BY o.order_date DESC, o.order_id
        ";
        $result   = $db->sql_query($SQL);
        $order_id = '';
        $subject_titles = '<u>Subject :</u>'. "<br>";
        $count = '';

        $exp = array('isEditable' => 0);

        while ($rowCC = $db->sql_fetchrow($result)) {

            if($count == ''){
                $SQLSubject = "
                    SELECT subj.title as subject_title
                        , crseSubj.subject_id
                    FROM course_contact_subject_history crseSubj
                    LEFT JOIN subject subj ON (crseSubj.subject_id = subj.subject_id)
                    WHERE crseSubj.course_contact_id = {$rowCC['course_contact_id']}
                ";
                $resultSubject   = $db->sql_query($SQLSubject);
                while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                    if($rowSubject['subject_title'] != 'Science Lab'){
                        $subject_titles .= $rowSubject['subject_title'] . "<br>";
                    }
                }

                $SQLPvt = "
                SELECT oi.*
                      ,o.order_id
                      ,o.contact_module
                      ,o.registration_type
                      ,o.medical_insurance
                      ,o.add_registration_fee
                      ,o.full_time
                      ,cc.no_of_months
                FROM order_item oi
                LEFT JOIN `order` o ON (o.order_id = oi.order_id)
                LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
                WHERE oi.order_id = {$rowCC['order_id']}
                ORDER BY oi.order_item_id
                ";
                $resultCourseTotal   = $db->sql_query($SQLPvt);  
                $modObj = getCPModuleObj('pms_order');
                $netTotal = $modObj->view->getTotalForPvtInst($resultCourseTotal);
            }

            $count++;    
            $printRows= '';
            
            $urlForm12 = "index.php?module=pms_contact&_spAction=printForm12&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";

            $urlStudentContract = "index.php?module=pms_contact&_spAction=printStudentContract&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";

            $urlOfferLetter = "index.php?module=pms_contact&_spAction=printOfferLetter&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";

            if($rowCC['registration_type'] == 'Registration & Enrollment' &&
                $rowCC['course_type'] == 'Long Term'){
                $rows1 = "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td colspan='1'>
                        <a href='{$urlStudentContract}' id='printStudentContract'  style='color:#000000;'>
                        <u>Print Student Contract</u>
                        </a>
                    </td>
                    <td colspan='3'>
                        <a href='{$urlForm12}' id='printForm12'  style='color:#000000;'>
                        <u>Print Form 12</u>
                        </a>
                    </td>
                    <td colspan='3'>
                        <a href='{$urlOfferLetter}' id='printOfferLetter'  style='color:#000000;'>
                        <u>Print Offer Letter</u>
                        </a>
                    </td>
                </tr>
                ";
                $printRows .= "
                    <div style='width: 125px;background-color: #F4F4F4; color:#000000;margin-bottom: 4px;'>
                        <a href='{$urlStudentContract}' id='printStudentContract'  style='color:#000000;'>
                        <u>Print Student Contract</u>
                        </a>
                    </div>
                    <div style='width: 125px;background-color: #F4F4F4; color:#000000;margin-bottom: 4px;'>
                        <a href='{$urlForm12}' id='printForm12'  style='color:#000000;'>
                        <u>Print Form 12</u>
                        </a>
                    </div>
                    <div style='width: 125px;background-color: #F4F4F4; color:#000000;margin-bottom: 4px;'>
                        <a href='{$urlOfferLetter}' id='printOfferLetter'  style='color:#000000;'>
                        <u>Print Offer Letter</u>
                        </a>
                    </div>
                ";
            }

            $editurl = "index.php?_topRm=main&_spAction=editCoursePvtLink&module=pms_courseLink&srcRoom=pms_contact&lnkRoom=pms_courseLink&contact_id={$row['contact_id']}&order_id={$rowCC['order_id']}&course_contact_id={$rowCC['course_contact_id']}&showHTML=0
            ";

            $printUrl    = "index.php?module=pms_company&_spAction=printVoucher&id={$row['contact_id']}&order_id={$rowCC['order_id']}&showHTML=0";

            $orderUrl = "/admin/index.php?_topRm=finance&module=pms_order&_action=detail&order_id={$rowCC['order_id']}";

            $rows .= "
            <tr>
                <td>{$fn->getCPDate($rowCC['order_date'], 'd-M-Y')}</td>
                <td>{$rowCC['course_title']}</td>
                <td>{$fn->getCPDate($rowCC['valid_date_from'], 'd-M-Y')}</td>
                <td>{$fn->getCPDate($rowCC['valid_date_to'], 'd-M-Y')}</td>
                <td>{$rowCC['course_status']}</td>
                <td>
                <a href='index.php?_topRm=finance&module=pms_order&_action=edit&order_id={$rowCC['order_id']}'  target=''><u>Goto Finance</u></a></td>
                <td class=''>
                    <a class='editPortalPvtRecord' h='350' w='650' order_id={$rowCC['order_id']} dialogtitle='Edit OrderLink' link='{$editurl}' href='javascript:void(0);'>
                        <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </td>
                <td class=''>
                    <a class='deletePortalRecord' srcroomid={$row['contact_id']} link='/admin/index.php?_spAction=deletePortalRecordByID&srcRoom=pms_company&lnkRoom=pms_orderLink&id={$rowCC['order_id']}&showHTML=0' href='javascript:void(0);'>
                        <img border='0'  style='' title='Delete Record' src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </td>
                    <!--
                    <a href= '{$printUrl}' target = '_blank' >
                        <img border='0' title='Print Invoice' src='/cmspilotv30/CP/admin/images/icons/btn_print.png'>
                    </a>
                    -->
            </tr>
            <tr>
                <td colspan=3>{$subject_titles}</td>
                <td colspan=2>{$printRows}</td>
                <td colspan=3>Course Fees Payable : {$netTotal}INR</td>
            </tr>
            ";
            $order_id =  $rowCC['order_id'];
        }
        $header ="
        <th>Date Joined</th>
        <th>Course Title</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Status</th>
        <th>Finance</th>
        <th>Edit</th>
        <th>Delete</th>
        ";

        $text = "
        <div class='linkPortalWrapper pms_contact__pms_courseLink' id='pms_contact#pms_courseLink'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='traineeLinkPvt' class='' dialogtitle='Trainee Link' href='index.php?_topRm=main&_spAction=newCoursePvtLink&module=pms_courseLink&srcRoom=pms_contact&lnkRoom=pms_courseLink&contact_id={$row['contact_id']}&showHTML=0';'>
                            <h3>Click for Registration/Enrollment</h3>
                        </a>
                    </div>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}
