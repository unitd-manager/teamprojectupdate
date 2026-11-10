<?
class CP_Admin_Modules_Tuitionsg_Contact_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];
            $site = '';
            if($cpCfg['cp.hasMultiSites']){
                $site = "
                {$listObj->getListDataCell($row['site_title'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($row['registration_no'])}
            {$listObj->getListDataCell($row['id_card_no'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$site}
            {$listObj->getListPublishedImage($row['published'], $row['contact_id'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        $site = '';
        if($cpCfg['cp.hasMultiSites']){
            $site = "
            {$listObj->getListHeaderCell('Site', 'site_title')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Registration No', 'c.registration_no')}
        {$listObj->getListHeaderCell('NRIC / Passport No. / FIN', 'c.id_card_no')}
        {$listObj->getListHeaderCell('Phone', 's.phone')}
        {$listObj->getListHeaderCell('Mobile', 's.mobile')}
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

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Key Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'first_name')}</td>
                                <td>{$formObj->getTBRow('NRIC / Passport No.', 'id_card_no')}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode  = $tv['action'];

        if ($cpCfg['cp.hasFullWidthForContactEdit']) {
            return $this->getFullWidthContactEdit($row);
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

        $sqlCourse        = $fn->getDDSql('aceIms_course');
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

        $sqlComp = $fn->getDDSql('aceIms_company');
        $expComp  = array('detailValue' => $row['c_company_name']);
        $expCountryName  = array('detailValue' => $row['country_name']);
        $expNoEdit = array('isEditable' => 0);

        $additionalDetails = '';
        // This is used to dispaly Other Details for Pvt : Used for Mass IMS
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

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'])}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
        {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
        {$formObj->getTBRow('NRIC / Passport No. / FIN', 'id_card_no', $row['id_card_no'])}
        {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
        {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', $row['student_pass_holder'])}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";

        $companyNewLink = "index.php?module=aceIms_company&_spAction=companyNew&showHTML=0";
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
        {$formObj->getTBRow('NRIC / Passport No.', 'parent_id_card_no', $row['parent_id_card_no'])}
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

        if($cpCfg['m.tuitionsg.contact.otherDetailsPvt'] == false){
            $companyFields = $formObj->getFieldSetWrapped('Company Details', $fieldset2);
            $educationLevel = $formObj->getFieldSetWrapped('Educational Level', $fieldset4);
            $employmentDetails = $formObj->getFieldSetWrapped('Employment Details', $fieldset6);
        }
        else{
            $companyFields = '';
            $educationLevel = '';;
            $employmentDetails = '';
        }

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
     *
     */
    function getFullWidthContactEdit($row){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVL     = array('sqlType' => 'OneField');
        $newRecord = $fn->getReqParam('newRecord');
        $errorCountValidate = $fn->getReqParam('errorCount');

        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountryName  = array('detailValue' => $row['country_name']);

        if ($row['address_country'] == ''){
            $country = 'SG';
        } else {
            $country = $row['address_country'];
        }

        $expandValue = 0;
        if($newRecord == 1){
            $expandValue = 1;
        }

        $exp = array('isEditable' => 0);
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='{$expandValue}' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Student Information) <i class='stepsStudentName'>Name: {$row['first_name']}</i></div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <div class=''>{$formObj->getTBRow('', "error_box", '', $exp)}</div>
                            <tr>
                                <td>{$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'], $exp)}</td>
                                <td>{$formObj->getTBRow('Name *', 'first_name', $row['first_name'])}</td>
                                <td>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVL)}</td>
                                <td>{$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}</td>
                                <td>{$formObj->getTBRow('NRIC / Passport No. / FIN *', 'id_card_no', $row['id_card_no'])}</td>
                                <td>{$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}</td>
                                <td>{$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $country, $expCountryName)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td>{$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}</td>
                                <td>{$formObj->getTBRow('Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Parent Email', 'parent_email', $row['parent_email'])}</td>
                                <td colspan='2'>{$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}</td>
                                <td colspan='2'>{$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', $row['student_pass_holder'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

        // This is nullify the session variable stored for subject
        if (isset($_SESSION['selectedSubjectIds'])){
            unset($_SESSION['selectedSubjectIds']);
        }
        $_SESSION['selectedSubjectIds'] = array();

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$this->getCoursePortalDisplay($row)}
        {$media->getRightPanelMediaDisplay('Picture', 'tuitionsg_contact', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'tuitionsg_contact', 'attachment', $row)}
        <div class='clearfix'></div>

        {$comment->getView(array(
             'roomName' => 'aceIms_contact'
            ,'recordId' => $record_id
        ))}
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

        // This is used to display Batch in Search
        if($cpCfg['m.tuitionsg.contact.showBatchInSearch']){
            if ($course_id){
                $sqlBatch = "
                SELECT Distinct b.batch_id, b.title
                FROM course_contact cc
                LEFT JOIN (batch b) ON ( b.batch_id = cc.batch_id )
                WHERE cc.course_id = $course_id ;
                ";
            }
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
        ";

        $text = "
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
        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=aceIms_contact&filename=contact-import-template.xls';
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
     *
     */
    function getCoursePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $rows = "";

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
        ORDER BY o.order_date DESC, o.order_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $order_id = '';
        $count = '';
        $rowCounter = 1;
        $serialNo = 1;

        $exp = array('isEditable' => 0);
        while ($rowCC = $db->sql_fetchrow($result)) {

            $subject_titles = '';
            if($rowCC['course_type'] == 'Long Term'){
                $subject_titles = '<u>Subject :</u>'. "<br>";

                $SQLSubject = "
                SELECT subj.title as subject_title
                      ,crseSubj.subject_id
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
            }

            $SQLPvt = "
            SELECT oi.*
                  ,o.order_id
                  ,o.contact_module
                  ,o.registration_type
                  ,o.medical_insurance
                  ,o.add_registration_fee
                  ,o.full_time
                  ,cc.course_id
            FROM order_item oi
            LEFT JOIN `order` o ON (o.order_id = oi.order_id)
            LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
            WHERE oi.order_id = {$rowCC['order_id']}
            ORDER BY oi.order_item_id
            ";
            $resultCourseTotal   = $db->sql_query($SQLPvt);
            $modObj = getCPModuleObj('aceIms_order');
            $netTotal = $modObj->view->getTotalForPvtInst($resultCourseTotal);
            $netTotal = number_format($netTotal, 2);

            $count++;
            $printRows= '';

            $urlForm12 = "index.php?module=aceIms_contact&_spAction=printForm12&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";
            $urlStudentContract = "index.php?module=aceIms_contact&_spAction=printStudentContract&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";
            $urlOfferLetter = "index.php?module=aceIms_contact&_spAction=printOfferLetter&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";

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

            $editurl  = "index.php?_topRm=main&_spAction=editCoursePvtLink&module=aceIms_courseLink&srcRoom=aceIms_contact&lnkRoom=aceIms_courseLink&contact_id={$row['contact_id']}&order_id={$rowCC['order_id']}&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";
            $printUrl = "index.php?module=aceIms_company&_spAction=printVoucher&id={$row['contact_id']}&order_id={$rowCC['order_id']}&showHTML=0";
            $orderUrl = "/admin/index.php?_topRm=finance&module=aceIms_order&_action=detail&order_id={$rowCC['order_id']}";

            if ($rowCounter % 2 == 0) {
                $class = 'even';
            } else {
                $class = 'odd';
            }

            $rows .= "
            <tr class='{$class}'>
                <td>{$serialNo}</td>
                <td>{$fn->getCPDate($rowCC['order_date'], 'd-M-Y')}</td>
                <td>{$rowCC['course_title']}</td>
                <td>{$fn->getCPDate($rowCC['valid_date_from'], 'd-M-Y')}</td>
                <td>{$fn->getCPDate($rowCC['valid_date_to'], 'd-M-Y')}</td>
                <td>{$rowCC['course_status']}</td>
                <td style='background-color: #98C2E2;'>
                    <a href='index.php?_topRm=finance&module=aceIms_order&_action=edit&order_id={$rowCC['order_id']}'  target=''><u>Goto Finance</u></a></td>
                <td class=''>
                    <a class='editPortalPvtRecord' h='350' w='650' order_id={$rowCC['order_id']} dialogtitle='Edit OrderLink' link='{$editurl}' href='javascript:void(0);'>
                        <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </td>
                <td class=''>
                    <!--<a class='deletePortalRecord' srcroomid={$row['contact_id']} link='/admin/index.php?_spAction=deletePortalRecordByID&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&id={$rowCC['order_id']}&showHTML=0' href='javascript:void(0);'>-->
                    <a class='deletePortalRecordContact' order_id={$rowCC['order_id']} srcroomid={$row['contact_id']} href='#'>
                        <img border='0'  style='' title='Delete Record' src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </td>
                    <!--
                    <a href= '{$printUrl}' target = '_blank' >
                        <img border='0' title='Print Invoice' src='/cmspilotv30/CP/admin/images/icons/btn_print.png'>
                    </a>
                    -->
            </tr>
            <tr class='{$class}'>
                <td colspan=3>{$subject_titles}</td>
                <td colspan=2>{$printRows}</td>
                <td colspan=3 class='txtRight fontBigAndBold'>Course Fees Payable : {$netTotal} SGD</td>
            </tr>
            ";
            $order_id =  $rowCC['order_id'];
            $rowCounter++;
            $serialNo++;
        }

        $header ="
        <th>S.No</th>
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
        <div class='linkPortalWrapper aceIms_contact__aceIms_courseLink' id='aceIms_contact#aceIms_courseLink'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 2 (Student Enrollment)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns enrollmentHeader'>
                            <h3 class='button'>
                                <a id='traineeLinkPvt' class='' dialogtitle='Trainee Link' href='index.php?_topRm=main&_spAction=newCoursePvtLink&module=aceIms_courseLink&srcRoom=aceIms_contact&lnkRoom=aceIms_courseLink&contact_id={$row['contact_id']}&showHTML=0';'>
                                Click for Registration/Enrollment</a>
                            </h3>
                    </div>
                    <table class='thinlist enrollmentList'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getContactNew(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $company_id       = $fn->getReqParam('company_id');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $expVL            = array('sqlType' => 'OneField');

        $formAction ='index.php?module=aceIms_contact&_spAction=contactAddSubmit&showHTML=0';
        $text = "
        <form name='portalForm' id='contactAddForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('Name *', 'first_name')}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', '')}
            {$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, '', $expVL)}
            {$formObj->getDateRow('Date of Birth *', 'date_of_birth', '', array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
            {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, '', $expVL)}
            {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', '')}
            {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, '', $expVL)}
            {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, '', $expVL)}
            {$formObj->getTBRow('Email *', 'email', '')}
            {$formObj->getTBRow('Password', 'pass_word', '')}
            {$formObj->getTBRow('Phone', 'phone', '')}
            {$formObj->getTBRow('HP/Mobile No. *', 'mobile', '')}
            {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', '')}
            <input type='hidden' name='company_id' value='{$company_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";
        return $text;
     }

    /**
     *
     */
    function getContactEdit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $contact_id       = $fn->getReqParam('contact_id');
        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $expVL            = array('sqlType' => 'OneField');

        $row = $fn->getRecordRowById('contact', 'contact_id', $contact_id);

        $exp = array('isEditable' => 0);

        $formAction ='index.php?module=aceIms_contact&_spAction=contactSave&showHTML=0';
        $text = "
        <form name='portalForm' id='contactEditForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'], $exp)}
            {$formObj->getTBRow('Name *', 'first_name', $row['first_name'])}
            {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', $row['id_card_no'])}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
            {$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, $row['gender'], $expVL)}
            {$formObj->getDateRow('Date of Birth *', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
            {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
            {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
            {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
            {$formObj->getTBRow('Email *', 'email', $row['email'])}
            {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
            {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
            {$formObj->getTBRow('HP/Mobile No. *', 'mobile', $row['mobile'])}
            {$formObj->getTBRow('Educational Qualification', 'qualification', $row['qualification'])}
            {$formObj->getTBRow('Years of Experience', 'yr_of_exp', $row['yr_of_exp'])}
            {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', $row['student_pass_holder'])}
            <input type='hidden' name='contact_id' value='{$contact_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";
        return $text;
     }

    /**
     *
    */
    function getContactDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';

        $contact_id = $fn->getReqParam('contact_id');
        $row = $fn->getRecordRowById('contact', 'contact_id', $contact_id);

        $exp = array('isEditable' => 0);

        $date = $dateUtil->formatDate($row['date_of_birth'], 'DD-MM-YYYY');
        $is_citizen          = ($row['is_citizen'] == 0)          ? "No" : "Yes";
        $student_pass_holder = ($row['student_pass_holder'] == 0) ? "No" : "Yes";

        if ($row['registration_no']) {
            $reg_no = 'ESIS-' . $row['registration_no'];
        } else {
            $reg_no = '';
        }

        $text = "
        <form class='yform columnar' method='post' action=''>
            <h2>Contact Details</h2>
            {$formObj->getTBRow('Registration No', 'registration_no', $reg_no, $exp)}
            {$formObj->getTBRow('Name', 'first_name', $row['first_name'], $exp)}
            {$formObj->getTBRow('Singapore Citizen / PR', 'is_citizen', $is_citizen, $exp)}
            {$formObj->getTBRow('Gender', 'gender', $row['gender'], $exp)}
            {$formObj->getTBRow('Date of Birth', 'date_of_birth', $date, $exp)}
            {$formObj->getTBRow('Marital Status', 'marital_status', $row['marital_status'], $exp)}
            {$formObj->getTBRow('NRIC/FIN/Work Permit No.', 'id_card_no', $row['id_card_no'], $exp)}
            {$formObj->getTBRow('Nationality', 'nationality', $row['nationality'], $exp)}
            {$formObj->getTBRow('Race', 'race', $row['race'], $exp)}
            {$formObj->getTBRow('Email', 'email', $row['email'], $exp)}
            {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'], $exp)}
            {$formObj->getTBRow('Phone', 'phone', $row['phone'], $exp)}
            {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'], $exp)}
            {$formObj->getTBRow('Educational Qualification', 'qualification', $row['qualification'], $exp)}
            {$formObj->getTBRow('Years of Experience', 'yr_of_exp', $row['yr_of_exp'], $exp)}
            {$formObj->getTBRow('Student Pass Holder', 'student_pass_holder', $student_pass_holder, $exp)}
        </form>
        ";
        return $text;
     }
}
