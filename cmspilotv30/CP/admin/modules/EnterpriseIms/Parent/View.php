<?
class CP_Admin_Modules_EnterpriseIms_Parent_View extends CP_Common_Modules_EnterpriseIms_Parent_View
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

            if($cpCfg['m.enterpriseIms.parent.hasSalutation'] == 1){
                $rowsSalutation = "
                {$listObj->getListDataCell($row['salutation'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$rowsSalutation}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            {$listObj->getListDataCell($row['id_card_no'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListPublishedImage($row['published'], $row['parent_id'])}
            {$listObj->getListRowEnd($row['parent_id'])}
            ";

            $rowCounter++ ;
        }

        if($cpCfg['m.enterpriseIms.parent.hasSalutation'] == 1){
            $textSalutation = "
            {$listObj->getListHeaderCell('Salutation', 'p.salutation')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$textSalutation}
        {$listObj->getListHeaderCell('First Name', 'p.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'p.last_name')}
        {$listObj->getListHeaderCell('NRIC No.', 'p.id_card_no')}
        {$listObj->getListHeaderCell('Email', 'p.email')}
        {$listObj->getListHeaderCell('Phone', 'p.phone')}
        {$listObj->getListHeaderCell('Mobile', 'p.mobile')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
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
        {$formObj->getTBRow('NRIC No.', 'id_card_no')}
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

        if($cpCfg['m.enterpriseIms.parent.hasSalutation'] == 1){
            $textSalutation = "
            {$formObj->getTBRow('Salutation', 'salutation', $row['salutation'])}
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
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
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
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('City / Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'])}
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
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        $rows = "";

        $record_id = $fn->getIssetParam($row, 'parent_id');

        if ($cpCfg['m.enterpriseIms.parent.hasStudentEnrollment']) {
            $rows .= $this->getCourseStudentDisplay($row);
        }

        $formAction = "index.php?_topRm=main&module=enterpriseIms_parent&_spAction=parentTransferForm&showHTML=0&parent_id={$record_id}";
        $text = "
        <div>
            <a href='{$formAction}' id='parentTransfer' class='button mt5 ml5 mb10' parent_id={$record_id}>Transfer to other Branch</a>
        </div>
        {$media->getRightPanelMediaDisplay('Picture', 'enterpriseIms_parent', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('enterpriseIms_parent', 'enterpriseIms_contactLink', 'Student Linked', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'enterpriseIms_parent'
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

        $special_search = $fn->getReqParam('special_search');
        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $spArrayContinuation = array(
              "Yes"
             ,"No"
        );

        $mode_of_payment          = $fn->getReqParam('mode_of_payment');
        $sqlModeOfPayment         = $fn->getValueListSQL('paymentType');
        $continuing_to_next_year  = $fn->getReqParam('continuing_to_next_year');
        $enrollment_year 		  = $fn->getReqParam('enrollment_year');
        $course_id       		  = $fn->getReqParam('course_id');

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

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        WHERE c.published = 1
        ORDER BY c.sort_order ASC
        ";

        $text = "
        <td>
            <select name='enrollment_year'>
                <option value=''>Select Year</option>
                {$cpUtil->getDropDown1($yearArray, $enrollment_year)}
            </select>
        </td>

        <td>
            <select name='course_id' >
                <option value=''>Class</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>

        <td>
            <select name='mode_of_payment'>
                <option value=''>Mode of Payment</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlModeOfPayment, $mode_of_payment)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
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
     * Right panel of Students linked along with their enrollment history
     */
    function getCourseStudentDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $rows = "";
        $links= "";

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.creation_date
              ,cc.discount
              ,cc.year_of_enrollment
              ,cc.evaluate_status
              ,c.title AS course_title
              ,l.title AS level_title
              ,b.title AS batch_title
              ,sd.title AS subsidy_title
              ,sdis.title AS discount_title
              ,cont.contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              ,cont.id_card_no AS contact_id_card_no
              ,o.order_date
              ,o.order_id
        FROM course_contact cc 
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN contact cont ON (cont.contact_id = cc.contact_id)
        LEFT JOIN parent_contact pc ON (cont.contact_id = pc.contact_id)
        LEFT JOIN `order` o ON (o.order_id = cc.order_id)
        LEFT JOIN level l ON (l.level_id = cc.level_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN course_subsidy_history s ON (cc.course_subsidy_history_id = s.course_subsidy_history_id)
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        LEFT JOIN course_subsidy_history csdis ON (cc.discount = csdis.course_subsidy_history_id)
        LEFT JOIN (subsidy_discount sdis) ON (csdis.subsidy_discount_id = sdis.subsidy_discount_id and sdis.category_type = 'Discount')
        WHERE pc.parent_id = {$row['parent_id']}
        ORDER BY o.order_date DESC, o.order_id
        ";
        $result   = $db->sql_query($SQL);  
        $order_id = '';
        $exp = array('isEditable' => 0);

        while ($rowCC = $db->sql_fetchrow($result)) {
            if ($order_id != $rowCC['order_id']){
                $printUrl    = "index.php?module=enterpriseIms_company&_spAction=printVoucher&id={$row['company_id']}&order_id={$rowCC['order_id']}&showHTML=0";
                $orderUrl = "/admin/index.php?_topRm=finance&module=enterpriseIms_order&_action=detail&order_id={$rowCC['order_id']}";
                $editurl = "index.php?_topRm=main&_spAction=bulkParentStudentEnrollment&module=enterpriseIms_orderLink&srcRoom=enterpriseIms_company&lnkRoom=enterpriseIms_orderLink&parent_id={$row['parent_id']}&order_id={$rowCC['order_id']}&showHTML=0";
                $order_date = $fn->getCPDate($rowCC['order_date'], 'd-m-Y');
                
                $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td colspan='6'>
                        <div class=''>
                            <a href= {$orderUrl} target='_blank' style='color:#000000;' class='button'>
                                 <u>Go to Finance</u>
                            </a>
                        </div>
                    </td>
                    <td class='portalActBtns'>
                        <div style='float:right'>
                            <a class='editPortalRecord1' h='475' w='1200' recid={$rowCC['order_id']} dialogtitle='Bulk Parent Contact Link' link='{$editurl}' href='javascript:void(0);'>
                                <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                            </a>                        
                            <a class='deletePortalRecord' srcroomid={$row['parent_id']} link='/admin/index.php?_spAction=deletePortalRecordByID&srcRoom=enterpriseIms_parent&lnkRoom=enterpriseIms_orderLink&id={$rowCC['order_id']}&showHTML=0' href='javascript:void(0);'>
                                <img border='0'  style='margin: 0px 3px 3px 3px;' title='Delete Record' src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                            </a>
                            <!--
                            <a href= '{$printUrl}' target = '_blank' >
                                <img border='0' title='Print Invoice' src='/cmspilotv30/CP/admin/images/icons/btn_print.png'>
                            </a>
                            -->
                        </div>
                    </td>
                </tr>
                ";
            }
            $enrol_date = $dateUtil->formatDate($rowCC['creation_date'], 'DD-MM-YYYY');
            
            $contact_link = "/admin/index.php?_topRm=main&module=enterpriseIms_contact&_action=detail&record_id={$rowCC['contact_id']}";

            $rows .= "
            <tr>
                <td>{$enrol_date}</td>
                <td><a href='{$contact_link}'>{$rowCC['contact_name']} {$rowCC['contact_id_card_no']}</a></td>
                <td>{$rowCC['course_title']}</td>
                <td>{$rowCC['level_title']}</td>
                <td>{$rowCC['batch_title']}</td>
                <td>{$rowCC['subsidy_title']}</td>
                <td>{$rowCC['year_of_enrollment']}</td>
            </tr>
            ";
            $order_id =  $rowCC['order_id'];
        }
        
        $header ="
        <th>Date</th>
        <th>Student</th>
        <th>{$modulesArr['enterpriseIms_course']['title']}</th>
        <th>{$modulesArr['enterpriseIms_level']['title']}</th>
        <th>{$modulesArr['enterpriseIms_batch']['title']}</th>
        <th>Subsidy</th>
        <th>Enrollment Year</th>
        ";

        $text = "
        <div class='linkPortalWrapper enterpriseIms_parent__enterpriseIms_orderLink' id='enterpriseIms_parent#enterpriseIms_orderLink'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='bulkParentCourseLink' class='' dialogtitle='Bulk Parent Contact Link' href='index.php?_topRm=main&_spAction=bulkParentStudentEnrollment&module=enterpriseIms_orderLink&srcRoom=enterpriseIms_parent&lnkRoom=enterpriseIms_orderLink&parent_id={$row['parent_id']}&showHTML=0';'> 
                            <u>Click here for Enrollment</u>
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

    /**
     * Parent transfer form
     */
     function getParentTransferForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');
               
        $parent_id = $fn->getReqParam('parent_id');
        $site_id   = $fn->getSessionParam('cp_site_id');

        $sqlSite = "
        SELECT site_id, title FROM site
        WHERE published = 1
            AND site_id != {$site_id}
        ";
        
        $sqlStudent = "
        SELECT c.contact_id, c.first_name
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        WHERE pc.parent_id = {$parent_id}
          AND c.site_id = {$site_id}
        ";
        $staffArray = array('commaToArray' => true, 'sqlType' => 'OneField', 'disabled' => false);

        $formAction = "index.php?_topRm=main&module=pms_parent&_spAction=parentTransferFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar parentTransferForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Branch', 'site_id', $sqlSite)}
            {$formObj->getCheckBoxArrRowBySQL($modulesArr['pms_contact']['title'], 'contact_ids[]', $sqlStudent, $staffArray)}
            <input type='hidden' name='parent_id' value='{$parent_id}' />
        </form>
        ";

        return $text;
    }
}