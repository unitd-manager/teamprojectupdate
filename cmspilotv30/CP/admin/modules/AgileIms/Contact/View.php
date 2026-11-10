<?
class CP_Admin_Modules_AgileIms_Contact_View extends CP_Common_Modules_AgileIms_Contact_View
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
        {$listObj->getListHeaderCell('Full Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Registration No', 'c.registration_no')}
        {$listObj->getListHeaderCell('NRIC / Passport No. / FIN', 'c.id_card_no')}
        {$listObj->getListHeaderCell('Phone', 's.phone')}
        {$listObj->getListHeaderCell('Mobile', 's.mobile')}
        {$site}
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
            			        <td>{$formObj->getTBRow('Full Name', 'first_name')}</td>
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVL = array('sqlType' => 'OneField');

        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlCompany       = $fn->getDDSql('agileIms_company');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountryName  = array('detailValue' => $row['country_name']);
        $expEdit = array('isEditable' => 0);
        $expCompany  = array('detailValue' => $row['company_title']);

		if ($row['address_country'] == ''){
			$country = 'SG';
		} else {
			$country = $row['address_country'];
		}

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='0' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Student Information)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
            		<table class='thinlist'>
            			<tbody>
            			    <tr>
            			        <td>{$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'])}</td>
            			        <td>{$formObj->getTBRow('Full Name *', 'first_name', $row['first_name'])}</td>
            			        <td>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVL)}</td>
                                <td>{$formObj->getDateRow('Date of Birth *', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}</td>
            			    </tr>

            			    <tr>
                                <td>{$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}</td>
            			        <td>{$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', $row['id_card_no'])}</td>
                                <td>{$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}</td>
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
                                <td>{$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}</td>
            			    </tr>

                            <tr>
                                <td colspan='2'>{$formObj->getTBRow('Educational Qualification', 'qualification', $row['qualification'])}</td>
                                <td colspan='2'>{$formObj->getTBRow('Years of Experience', 'yr_of_exp', $row['yr_of_exp'])}</td>
                            </tr>

                            <tr>
                                <td colspan='2'>{$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}</td>
                                <td colspan='2'>{$formObj->getYesNoRRow('English read & write', 'eng_read_write', $row['eng_read_write'])}</td>
                            </tr>

                            <tr>
                                <td colspan='2'>{$formObj->getYesNoRRow('Physical activities confirmation', 'physical_activities', $row['physical_activities'])}</td>
                                <td colspan='2'>{$formObj->getYesNoRRow('Need to bring safety shoe', 'safety_shoe', $row['safety_shoe'])}</td>
                            </tr>

                            <tr>
                                <td colspan='4' class='innerHeading'>Company Information</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getDDRowBySQL('Company', 'company_id', $sqlCompany, $row['company_id'], $expCompany)}</td>
                                <td>{$formObj->getTBRow('Company Contact Person', 'company_contact_person', $row['company_contact_person'], $expEdit)}</td>
                                <td>{$formObj->getTBRow('Office No', 'office_no', $row['office_no'], $expEdit)}</td>
                                <td>{$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'], $expEdit)}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'company_address_flat', $row['company_address_flat'], $expEdit)}</td>
                                <td>{$formObj->getTBRow('Address 2', 'company_address_street', $row['company_address_street'], $expEdit)}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'company_address_po_code', $row['company_address_po_code'], $expEdit)}</td>
                                <td>{$formObj->getTBRow('Country', 'company_address_country', $row['company_address_country'], $expEdit)}</td>
                            </tr>

                            <!--<tr>
                                <td>{$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}</td>
                                <td>{$formObj->getTBRow('Company Contact Person', 'company_contact_person', $row['company_contact_person'])}</td>
                                <td>{$formObj->getTBRow('Office No.', 'office_no', $row['office_no'])}</td>
                                <td>{$formObj->getTBRow('Fax', 'company_fax', $row['company_fax'])}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'company_address_flat', $row['company_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address 2', 'company_address_street', $row['company_address_street'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'company_address_po_code', $row['company_address_po_code'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'company_address_country', $sqlCountry, $country, $expCountryName)}</td>
                            </tr>-->
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
        {$media->getRightPanelMediaDisplay('Picture', 'agileIms_contact', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'agileIms_contact', 'attachment', $row)}
        <div class='clearfix'></div>

        {$comment->getView(array(
             'roomName' => 'agileIms_contact'
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
        if($cpCfg['m.agileIms.contact.showBatchInSearch']){
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
              "Flagged"
             ,"Not-Flagged"
             ,"Batch-Not-Linked"
        );

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
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
        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=agileIms_contact&filename=contact-import-template.xls';
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
              ,cc.company_id
              ,cc.course_id
              ,cc.batch_id
              ,cc.discount
              ,cc.course_status
              ,cc.registration_type
              ,cc.add_registration_fee
              ,c.title AS course_title
              ,c.course_type
              ,c.valid_date_from
              ,c.valid_date_to
              ,b.title AS batch_title
              ,cont.first_name AS contact_name
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
        $count = '';
        $rowCounter = 1;

        $exp = array('isEditable' => 0);
        while ($rowCC = $db->sql_fetchrow($result)) {
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

            $modObj = getCPModuleObj('agileIms_order');
            $netTotal = $modObj->model->getTotalAmountFromOrderItem($rowCC['order_id']);

            $count++;

            $printUrl = "index.php?module=agileIms_company&_spAction=printVoucher&id={$row['contact_id']}&order_id={$rowCC['order_id']}&showHTML=0";
            $orderUrl = "/admin/index.php?_topRm=finance&module=agileIms_order&_action=detail&order_id={$rowCC['order_id']}";

            $editurl  = "index.php?_topRm=main&_spAction=editStudentEnrollment&module=agileIms_courseLink&srcRoom=agileIms_contact&lnkRoom=agileIms_courseLink&contact_id={$row['contact_id']}&order_id={$rowCC['order_id']}&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";

            if ($rowCounter % 2 == 0) {
                $class = 'even';
            } else {
                $class = 'odd';
            }

            $editRow = '';
            $cancelRow = 'Enrollment Cancelled';
            $cancelledClass = 'highlightClass';
            if ($rowCC['course_status'] != 'Cancelled') {
                $editRow = "
                <a class='editStudentEnrollment' h='350' w='650' order_id={$rowCC['order_id']} dialogtitle='Edit OrderLink' link='{$editurl}' href='javascript:void(0);'>
                    <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                </a>
                ";

                $cancelRow = "
                <a class='cancelEnrollment' order_id={$rowCC['order_id']} href='javascript:void(0);'>
                    <u>Cancel Enrollment</u>
                </a>
                ";
                $cancelledClass = '';
            }

            if ($rowCC['company_id']) {
                $editCancel = "
                <td class='txtCenter' colspan='2'>Company Enrollment</td>
                ";
                $editRow = '';
                $cancelRow = '';
            } else {
                $editCancel = "
                <td class=''>{$editRow}</td>
                <td class=''>{$cancelRow}</td>
                ";
            }

            $urlStudentEnrollmentPdf = "index.php?module=agileIms_contact&_spAction=printEnrollmentPdf&course_contact_id={$rowCC['course_contact_id']}&showHTML=0";
            $rows .= "
            <tr class='{$class}'>
                <td>{$fn->getCPDate($rowCC['order_date'], 'd M Y')}</td>
                <td>
                Course : <a href='index.php?_topRm=main&module=agileIms_course&_action=edit&course_id={$rowCC['course_id']}'><u>{$rowCC['course_title']}</u></a><br/>
                Batch: <a href='index.php?_topRm=main&module=agileIms_batch&_action=edit&batch_id={$rowCC['batch_id']}'><u>{$rowCC['batch_title']}</u></a>
                </td>
                <td>{$fn->getCPDate($rowCC['valid_date_from'], 'd-M-Y')}</td>
                <td>{$fn->getCPDate($rowCC['valid_date_to'], 'd-M-Y')}</td>
                <td class='{$cancelledClass}'>{$rowCC['course_status']}</td>
                <td>
                <a href='index.php?_topRm=finance&module=agileIms_order&_action=edit&order_id={$rowCC['order_id']}'  target=''><u>Goto Finance</u></a></td>
                {$editCancel}
            </tr>
            <tr class='{$class}'>
                <td colspan=5 class='txtCenter'>
                    <a href='{$urlStudentEnrollmentPdf}' target='_blank' course_contact_id={$rowCC['course_contact_id']}><u>Print Enrollment Pdf</u></a>
                </td>
                <td colspan=3 class='txtRight highlightClass'>Course Fees Payable : {$netTotal} SGD</td>
            </tr>
            ";
            $order_id =  $rowCC['order_id'];
            $rowCounter++;
        }

        $header ="
        <th>Date Enrolled</th>
        <th>Course Title / Batch</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Status</th>
        <th>Finance</th>
        <th>Edit</th>
        <th>Cancel</th>
        ";

        $text = "
        <div class='linkPortalWrapper agileIms_contact__agileIms_courseLink' id='agileIms_contact#agileIms_courseLink'>
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
                                <a id='traineeNewEnrollment' class='' dialogtitle='Trainee Link' href='index.php?_topRm=main&_spAction=new&module=agileIms_courseLink&srcRoom=agileIms_contact&lnkRoom=agileIms_courseLink&contact_id={$row['contact_id']}&showHTML=0';'>
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
        $eng_read_write      = ($row['eng_read_write'] == 0)      ? "No" : "Yes";
        $physical_activities = ($row['physical_activities'] == 0) ? "No" : "Yes";
        $safety_shoe         = ($row['safety_shoe'] == 0)         ? "No" : "Yes";

        $text = "
        <form class='yform columnar' method='post' action=''>
            <h2>Contact Details</h2>
            {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'], $exp)}
            {$formObj->getTBRow('Full Name', 'first_name', $row['first_name'], $exp)}
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
            {$formObj->getTBRow('Singapore Citizen / PR', 'is_citizen', $is_citizen, $exp)}
            {$formObj->getTBRow('English read & write', 'eng_read_write', $eng_read_write, $exp)}
            {$formObj->getTBRow('Physical activities confirmation', 'physical_activities', $physical_activities, $exp)}
            {$formObj->getTBRow('Need to bring safety shoe', 'safety_shoe', $safety_shoe, $exp)}
        </form>
        ";

        return $text;
     }

    /**
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

        $formAction ='index.php?module=agileIms_contact&_spAction=contactSave&showHTML=0';
        $text = "
        <form name='portalForm' id='contactEditForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('Registration No', 'registration_no', $row['registration_no'])}
            {$formObj->getTBRow('Full Name *', 'first_name', $row['first_name'])}
            {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', $row['id_card_no'])}
            {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVL)}
            {$formObj->getDateRow('Date of Birth *', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
            {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
            {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
            {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
            {$formObj->getTBRow('Email', 'email', $row['email'])}
            {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
            {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
            {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}
            {$formObj->getTBRow('Educational Qualification', 'qualification', $row['qualification'])}
            {$formObj->getTBRow('Years of Experience', 'yr_of_exp', $row['yr_of_exp'])}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
            {$formObj->getYesNoRRow('English read & write', 'eng_read_write', $row['eng_read_write'])}
            {$formObj->getYesNoRRow('Physical activities confirmation', 'physical_activities', $row['physical_activities'])}
            {$formObj->getYesNoRRow('Need to bring safety shoe', 'safety_shoe', $row['safety_shoe'])}
            <input type='hidden' name='contact_id' value='{$contact_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
     }

    /**
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

        $formAction ='index.php?module=agileIms_contact&_spAction=contactAddSubmit&showHTML=0';
        $text = "
        <form name='portalForm' id='contactAddForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('Full Name *', 'first_name')}
            {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', '')}
            {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, '', $expVL)}
            {$formObj->getDateRow('Date of Birth *', 'date_of_birth', '', array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
            {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, '', $expVL)}
            {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, '', $expVL)}
            {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, '', $expVL)}
            {$formObj->getTBRow('Email', 'email', '')}
            {$formObj->getTBRow('Password', 'pass_word', '')}
            {$formObj->getTBRow('Phone', 'phone', '')}
            {$formObj->getTBRow('HP/Mobile No.', 'mobile', '')}
            {$formObj->getTBRow('Educational Qualification', 'qualification', '')}
            {$formObj->getTBRow('Years of Experience', 'yr_of_exp', '')}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', '')}
            {$formObj->getYesNoRRow('English read & write', 'eng_read_write', '')}
            {$formObj->getYesNoRRow('Physical activities confirmation', 'physical_activities', '')}
            {$formObj->getYesNoRRow('Need to bring safety shoe', 'safety_shoe', '')}
            <input type='hidden' name='company_id' value='{$company_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
     }

    /**
     * Student Enrollment PDF
     */
    function getPrintEnrollmentPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Student Enrollment Pdf');
        $pdf->SetTitle('Print Student Enrollment Pdf');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE,8);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START
        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('Courier','B',10);
        $pdf->AddPage();
        $row = '';

        $id = $fn->getReqParam('course_contact_id');

        $SQL = "
        SELECT c.*
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.address_country)
                AS address_country
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.company_address_country)
                AS company_address_country
               ,cc.contact_id
               ,co.title AS course_title
               ,co.price AS course_fees
               ,co.description
               ,co.regular_class
               ,co.part_time_class
               ,co.sunday_class
               ,co.min_attendance_requirement
               ,co.passing_mark
               ,co.course_entry_requirements
               ,co.class_size
               ,co.replacement_notes
               ,co.general_rules
               ,co.legal_requirement
               ,co.aim_of_course
               ,b.venue AS batch_venue
        FROM contact c
        LEFT JOIN course_contact cc ON (cc.contact_id = c.contact_id)
        LEFT JOIN course co ON (co.course_id = cc.course_id)
        LEFT JOIN batch b   ON (cc.batch_id  = b.batch_id)
        WHERE cc.course_contact_id = $id
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $batchVenue = $fn->getRecordByCondition("valuelist", "key_text = 'batchVenue' AND value = '{$row['batch_venue']}'");

        $batch_venue_details = '';
        if ($batchVenue['code']) {
            $batch_venue_details = $batchVenue['code'];
        }

        /* Formatting of all values from SQL */
        $eng_val               = ($row['eng_read_write'] == 0 ? "No" : "Yes");
        $safety_shoe_val       = ($row['safety_shoe'] == 0 ? "No" : "Yes");
        $physical_activity_val = ($row['physical_activities'] == 0 ? "No" : "Yes");
        $course_fees           = number_format($row['course_fees'], 2);

        $pdf->SetFont('Courier','',9);
        $tbl1 = '
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td width="75%" align="center" style="font-size:16px; font-weight:bold;">'.strtoupper($row['course_title']).'</td>
                <td width="9%" align="left" style="border-right:2px solid #fff; font-size:14px; background-color:#7F7F7F; color:#fff; font-weight:bold;">&nbsp;FEES</td>
                <td width="16%" style="border-right:2px solid #fff; font-size:14px; background-color:#7F7F7F; color:#fff; font-weight:bold;">&nbsp;S$ '. $course_fees .'</td>
            </tr>
            <tr>
                <td width="35%" style="font-size:13px; font-weight:bold;" bgcolor="#7F7F7F" color="#fff">COURSE CONTENT</td>
                <td colspan="2"></td>
            </tr>
        </table>
        <table cellspacing="0" border="0" cellpadding="0">
            <tr>
                <td style="height: 230px;" bgcolor="#D8D8D8">'.$row['description'].'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table cellspacing="" cellpadding="5" border="0" width="100%">
            <tr>
                <td width="35%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">COURSE DATE & TIME</td>
                <td width="65%"></td>
            </tr>
        </table>
        <table cellspacing="" cellpadding="5" border="0">
            <tr style="font-weight:bold;" bgcolor="#D9D9D9">
                <td width="30%" style="border-right:2px solid #fff;">REGULAR CLASSES</td>
                <td width="35%" style="border-right:2px solid #fff;">PART TIME EVENING CLASSES</td>
                <td width="35%" style="border-right:2px solid #fff;">SUNDAY CLASSES</td>
            </tr>
            <tr style="font-weight:normal; border:1px solid #DDE4FF;" bgcolor="#D9D9D9">
                <td width="30%" style="border-bottom: 5px solid #fff; border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['regular_class'].'</td>
                <td width="35%" style="border-bottom: 5px solid #fff; border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['part_time_class'].'</td>
                <td width="35%" style="border-bottom: 5px solid #fff; border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['sunday_class'].'</td>
            </tr>
        </table>
        <table border="0" width="100%" cellpadding="5" cellspacing="">
            <tr style="font-weight:bold;" bgcolor="#D9D9D9">
                <td width="15%" style="border-right:2px solid #fff;">ATTENDANCE</td>
                <td width="35%" style="border-right:2px solid #fff;">ENTRY QUALIFICATION</td>
                <td width="35%" style="border-right:2px solid #fff;">PASSING MARK</td>
                <td width="15%" style="border-right:2px solid #fff;">CLASS SIZE</td>
            </tr>
            <tr style="font-weight:normal;" bgcolor="#D9D9D9">
                <td width="15%" style="border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['min_attendance_requirement'].'</td>
                <td width="35%" style="border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['course_entry_requirements'].'</td>
                <td width="35%" style="border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['passing_mark'].'</td>
                <td width="15%" style="border-top:2px solid #fff; border-right:2px solid #fff;">'.$row['class_size'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4">
                <tr style="font-size:13px; font-weight:bold;" bgcolor="#7F7F7F" color="#fff">
                        <td width="65%" style="border-right:3px solid white";>REPLACEMENT /POSTPONEMENT / CANCELLATION/ RESCEHDULE</td>
                        <td width="35%" style="border-right:3px solid white";>GENERAL RULES</td>
                </tr>
                <tr style="font-weight:normal; border:1px solid #DDE4FF;" bgcolor="#D9D9D9">
                    <td style="border-right:3px solid white"; width="65%">'.$row['replacement_notes'].'</td>
                    <td style="border-right:3px solid white"; width="35%">'.$row['general_rules'].'</td>
                </tr>
        </table>
        ';

        $pdf->SetFont('Courier','',9);
        $tbl4 ='
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td width="75%" align="center" style="border-bottom:5px solid #fff; font-size:16px; font-weight:bold;">'.strtoupper($row['course_title']).'</td>
                <td width="9%" align="left" style="border-right:2px solid #fff; border-bottom:5px solid #fff; font-size:14px; background-color:#7F7F7F; color:#fff; font-weight:bold;">&nbsp;FEES</td>
                <td width="16%" style="border-right:2px solid #fff; border-bottom:5px solid #fff; font-size:14px; background-color:#7F7F7F; color:#fff; font-weight:bold;">&nbsp;S$ '. $course_fees .'</td>
            </tr>
        </table>
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" align="left">
                     <table border="0" cellpadding="4">
                        <br>
                        <tr style="font-size:13px; font-weight:bold;" bgcolor="#7F7F7F" color="#fff">
                            <td width="103%">PARTICULARS OF THE PARTICIPANT</td>
                         </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                            <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Full Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                            <td width="65%" style="border-bottom: 1px solid #000;">'.$row['first_name'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                            <td width="38%" style="border-bottom: 1px solid #D9D9D9;"></td>
                            <td width="65%" style="border-bottom: 1px solid #D9D9D9;">(As in the NRIC/WP/PASSPORT)</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Gender&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['gender'].'</td>
                        </tr>
                        <tr style=" font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Date of Birth&nbsp;&nbsp; : </td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$dateUtil->formatDate($row['date_of_birth'], 'DD-MM-YYYY').'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">NRIC/FIN/WORK Permit NO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/>'.$row['id_card_no'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Nationality&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['nationality'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Educational Qualification&nbsp;&nbsp; :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/>'.$row['qualification'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Years of Experience&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/>'.$row['yr_of_exp'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Telephone/HP No :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['mobile'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Address&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['address_flat'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;"></td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['address_street'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;"></td>
                              <td width="65%" style="border-bottom: 1px solid #000;">'.$row['address_country'].'  '.$row['address_po_code'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">English Language can read and <br/> write :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/><br/>'. $eng_val .'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Understand that need to bring Safety Shoe for training :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/><br/><br/>'. $safety_shoe_val .'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="38%" style="border-bottom: 1px solid #D9D9D9;">Understand this course involves physical activities like climbing ladder & scaffolding etc :</td>
                              <td width="65%" style="border-bottom: 1px solid #000;"><br/><br/><br/><br/><br/><br/>'. $physical_activity_val .'</td>
                        </tr>
                        <tr bgcolor="#D9D9D9"><td colspan="2"></td></tr>
                     </table>
                </td>
                <td width="50%">
                    <table border="0" width="100%" cellspacing="0" cellpadding="4">
                        <br>
                        <tr style="font-size:13px; font-weight:bold;" bgcolor="#7F7F7F" color="#fff">
                            <td width="103%">COMPANY INFORMATION</td>
                         </tr>
                        <tr style=" font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="50%" style="border-bottom: 1px solid #D9D9D9;">Company Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                              <td width="53%" style="border-bottom: 1px solid #000;">'.strtoupper($row['company_name']).'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                            <td width="50%" style="border-bottom: 1px solid #D9D9D9;">Company Address&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :</td>
                            <td width="53%" style="border-bottom: 1px solid #000;">'.$row['company_address_flat'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                            <td width="50%" style="border-bottom: 1px solid #D9D9D9;"></td>
                            <td width="53%" style="border-bottom: 1px solid #000;">'.$row['company_address_street'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                            <td width="50%" style="border-bottom: 1px solid #D9D9D9;"></td>
                            <td width="53%" style="border-bottom:1px solid #000;">'.$row['company_address_country'] . ' ' . $row['company_address_po_code'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="50%" style="border-bottom: 1px solid #D9D9D9;">Company Contact Person :</td>
                              <td width="53%" style="border-bottom: 1px solid #000;">'.$row['company_contact_person'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="50%" style="border-bottom: 1px solid #D9D9D9;">Tel No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                              <td width="53%" style="border-bottom: 1px solid #000;">'.$row['office_no'].'</td>
                        </tr>
                        <tr style="font-size:10px; font-weight:normal; border:0;" bgcolor="#D9D9D9">
                              <td width="50%" style="border-bottom: 1px solid #D9D9D9;">Fax &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                              <td width="53%" style="border-bottom: 1px solid #000;">'.$row['company_fax'].'</td>
                        </tr>
                        <tr bgcolor="#D9D9D9"><td colspan="2"></td></tr>
                        <tr>
                            <td width="103%"></td>
                        </tr>
                    </table>
                    ';

                    if ($row['legal_requirement']) {
                        $tbl4 = $tbl4 . '
                        <table border="0" width="100%" cellpadding="4">
                            <br>
                            <tr>
                                <td width="100%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">LEGAL REQUIREMENTS</td>
                            </tr>
                            <tr style="font-weight:normal; font-size:10px; border:1px solid #DDE4FF;" bgcolor="#D9D9D9">
                                <td width="100%" height="128">'.$row['legal_requirement'].'</td>
                            </tr>
                            <tr>
                                <td width="103%"></td>
                            </tr>
                        </table>
                        ';
                    } else {
                        $tbl4 = $tbl4 . '
                        <table border="0" width="100%" cellpadding="4">
                            <br>
                            <tr>
                                <td width="100%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">AIM OF THE COURSE</td>
                            </tr>
                            <tr style="font-weight:normal; font-size:10px; border:1px solid #DDE4FF;" bgcolor="#D9D9D9">
                                <td width="100%" height="128">'.$row['aim_of_course'].'</td>
                            </tr>
                            <tr>
                                <td width="103%"></td>
                            </tr>
                        </table>
                        ';
                    }

                    $tbl4 = $tbl4 . '
                    <table border="0" width="100%" cellpadding="4">
                        <br>
                        <tr>
                            <td width="100%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">TRAINING VENUE</td>
                        </tr>
                        <tr style="font-weight:normal; font-size:10px; border:1px solid #DDE4FF;" bgcolor="#D9D9D9">
                            <td height="107" width="100%">'.$batch_venue_details.'</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ';

        $tbl5 ='
        <table border="0" width="100%">
            <tr>
                <td>
                    <table border="0" width="100%" cellpadding="4">
                        <br>
                        <tr>
                            <td width="103%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">STUDENT CONFIRMATION</td>
                        </tr>
                        <tr>
                            <td width="103%" bgcolor="#D9D9D9" style="border: 1px solid #D9D9D9;">I have read and understood the course requirements and training centre requirements.
                            </td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="38%" style="border: 1px solid #D9D9D9;">Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="65%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="38%" style="border: 1px solid #D9D9D9;">Signature &nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="65%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9"><td colspan="2"></td></tr>
                    </table>
                </td>
                <td>
                    <table border="0" width="100%" cellpadding="4">
                        <br>
                        <tr>
                            <td width="100%" style="color:#fff; font-size:13px; font-weight:bold; background-color:#7F7F7F;">COURSE CONFIRMATION</td>
                        </tr>
                        <tr>
                            <td width="100%" bgcolor="#D9D9D9" style="border: 1px solid #D9D9D9;">For Official Use Only</td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="50%" style="border: 1px solid #D9D9D9;">Receipt No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="50%" style="border: 1px solid #D9D9D9;">Date received &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td border="0" align="left" width="50%" style="border: 1px solid #D9D9D9;">Batch No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td border="0" align="left" width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="50%" style="border: 1px solid #D9D9D9;">Course Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="50%" style="border: 1px solid #D9D9D9;">Fees Paid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
                            <td width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9">
                            <td width="50%" style="border: 1px solid #D9D9D9;">Staffs Signature &nbsp;&nbsp;&nbsp;:</td>
                            <td width="50%" style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr bgcolor="#D9D9D9"><td colspan="2"></td></tr>
                    </table>
                </td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(2);
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $pdf->Output('print_student_enrollment.pdf', 'I');
    }
}
