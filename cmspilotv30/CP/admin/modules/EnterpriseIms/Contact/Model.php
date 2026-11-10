<?
class CP_Admin_Modules_EnterpriseIms_Contact_Model extends CP_Common_Modules_EnterpriseIms_Contact_Model
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $course_id       = $fn->getReqParam('course_id');
        $interest_id     = $fn->getReqParam('interest_id');
        $course_status   = $fn->getReqParam('course_status');
        $enrollment_year = $fn->getReqParam('enrollment_year');
        
        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }
        
        $extraFieldNames = '';
        $extraTableNames = '';

        if ($interest_id != "") {
            $extraTableNames .= "JOIN interest_contact ic ON (c.contact_id = ic.contact_id)";
        }

        if ($course_id != "" || ($course_status != '' && $cpCfg['m.enterpriseIms.course.hasCourseContactStatus']) || $enrollment_year != '') {
            if ($course_status != '') {
                $extraFieldNames .= ",cc.course_status";
            } else {
                $extraFieldNames .= ",cc.batch_id";
            }
        }
        
        $SQL = "
        SELECT c.*
              ,gc.name AS country_name
              ,gc2.name AS c_country_name
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,IF(c.company_id > 0, co.title, c.company_name) AS company_title
              ,co.title                AS c_company_name
              ,co.email                AS c_email
              ,co.address1             AS c_address_flat
              ,co.address2             AS c_address_street
              ,co.address_town         AS c_address_town
              ,co.address_state        AS c_address_state
              ,co.address_po_code      AS c_address_po_code
              ,co.phone                AS c_phone
              ,co.fax                  AS c_fax
              ,co.category             AS c_category
              ,co.reg_number           AS c_reg_number
              ,(SELECT cou.title 
                FROM course cou
                JOIN course_contact cc ON (cou.course_id = cc.course_id)
                WHERE cc.year_of_enrollment = '{$enrollment_year}'
                  AND cc.contact_id = c.contact_id) as student_registered_course
              ,(SELECT ba.title 
                FROM batch ba
                JOIN course_contact cc ON (ba.batch_id = cc.batch_id)
                WHERE cc.year_of_enrollment = '{$enrollment_year}'
                  AND cc.contact_id = c.contact_id) as student_registered_batch
              {$extraFieldNames}
        FROM contact c
        LEFT JOIN (company co) ON (c.company_id = co.company_id )
        LEFT JOIN geo_country gc ON (c.address_country = gc.country_code)
        LEFT JOIN geo_country gc2 ON (co.address_country_code = gc2.country_code)
        JOIN course_contact cc ON (c.contact_id = cc.contact_id)
        {$extraTableNames}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter the id card no.');
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        
        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('enterpriseIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "Id card number already exists. '{$IdCardlink}'";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $site_id = $fn->getSessionParam('cp_site_id');
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if($cpCfg['m.enterpriseIms.contact.hasRegisterNo']){
            $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
            $current_year = date('Y');
            
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");
            
            $fa['registration_no'] = $nextRegNo;
            if ($site_id == 2) {
                $fa['registration_no'] = $nextRegNo . 'J';
            }
        }

        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('gender' , 'Please select gender');
        $validate->validateData('id_card_no' , 'Please enter id card no');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $contact_id = $fn->getReqparam('contact_id');
        
        if (!$this->getEditValidate($contact_id)){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['cp.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } else {
                //remove pass_word field from the fields array
                unset($fa['pass_word']);
            }
        }
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'year_of_joining');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'age');
        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'with_drawal');
        $fa = $fn->addToFieldsArray($fa, 'refund_payable');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $course_status  = $fn->getReqParam('course_status');
        
        if ($course_status != '' && $cpCfg['m.enterpriseIms.course.hasCourseContactStatus']) {
            $addStatusField = 1;
        } else {
            $addStatusField = '';
        }
        
        if ($addStatusField == ''){        
            $fa = array(
                  'first_name'      => $phpExcel->getFldObj('First Name')
                 ,'last_name'       => $phpExcel->getFldObj('Last Name')
                 ,'email'           => $phpExcel->getFldObj('Email')
                 ,'phone_direct'    => $phpExcel->getFldObj('Phone')
                 ,'mobile'          => $phpExcel->getFldObj('Mobile')
                 ,'address_flat'    => $phpExcel->getFldObj('Address 1')
                 ,'address_street'  => $phpExcel->getFldObj('Address 2')
                 ,'address_city'    => $phpExcel->getFldObj('City')
                 ,'address_state'   => $phpExcel->getFldObj('State')
                 ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
            );
        } else {
            $fa = array(
                  'first_name'      => $phpExcel->getFldObj('First Name')
                 ,'last_name'       => $phpExcel->getFldObj('Last Name')
                 ,'email'           => $phpExcel->getFldObj('Email')
                 ,'phone_direct'    => $phpExcel->getFldObj('Phone')
                 ,'mobile'          => $phpExcel->getFldObj('Mobile')
                 ,'address_flat'    => $phpExcel->getFldObj('Address 1')
                 ,'address_street'  => $phpExcel->getFldObj('Address 2')
                 ,'address_city'    => $phpExcel->getFldObj('City')
                 ,'address_state'   => $phpExcel->getFldObj('State')
                 ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
                 ,'course_status'   => $phpExcel->getFldObj('Status')
            );
        }

        $file_name = "Contact_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'registration_no'         => $phpExcel->getImportFldObj('Student No.')
             ,'first_name'              => $phpExcel->getImportFldObj('Name of Student')
             ,'gender'                  => $phpExcel->getImportFldObj('Gender')
             ,'date_of_birth'           => $phpExcel->getImportFldObj('Date of Birth')
             
             ,'course_code'             => $phpExcel->getImportFldObj('Class') // Course
             ,'level_code'              => $phpExcel->getImportFldObj('Level')
             ,'batch_code'              => $phpExcel->getImportFldObj('Session (Sat / Sun)') // Batch
             #,'course_title'            => $phpExcel->getImportFldObj('Course Title') // Course title
             
             ,'parent_first_name'       => $phpExcel->getImportFldObj('Parents Name')             
             ,'parent_address_flat'     => $phpExcel->getImportFldObj('Address 1')
             ,'parent_address_street'   => $phpExcel->getImportFldObj('Address 2')
             ,'parent_address_po_code'  => $phpExcel->getImportFldObj('Postcode')
             ,'parent_phone'            => $phpExcel->getImportFldObj('Tel (Res)')
             ,'parent_mobile'           => $phpExcel->getImportFldObj('Tel (Mobile)')
             ,'parent_email'            => $phpExcel->getImportFldObj("Parent's Email Address")

             ,'parent_payment_mode'     => $phpExcel->getImportFldObj('Cash/GIRO')             
             ,'bank_name'               => $phpExcel->getImportFldObj('Name of Bank')             
             ,'account_name'            => $phpExcel->getImportFldObj('Account Name')             
             ,'bank_code'               => $phpExcel->getImportFldObj('Bank code')
             ,'branch'                  => $phpExcel->getImportFldObj('Branch code')             
             ,'account_no'              => $phpExcel->getImportFldObj('Account No.')
             ,'dda'                     => $phpExcel->getImportFldObj('DDA')
             ,'jan_payment'             => $phpExcel->getImportFldObj('Jan')
             ,'feb_payment'             => $phpExcel->getImportFldObj('Feb')
             ,'mar_payment'             => $phpExcel->getImportFldObj('Mar')
             ,'apr_payment'             => $phpExcel->getImportFldObj('Apr')
             ,'may_payment'             => $phpExcel->getImportFldObj('May')
             ,'jun_payment'             => $phpExcel->getImportFldObj('Jun')
             ,'jul_payment'             => $phpExcel->getImportFldObj('Jul')
             ,'aug_payment'             => $phpExcel->getImportFldObj('Aug')
             ,'sep_payment'             => $phpExcel->getImportFldObj('Sep')
             ,'oct_payment'             => $phpExcel->getImportFldObj('Oct')
             ,'nov_payment'             => $phpExcel->getImportFldObj('Nov')
             ,'dec_payment'             => $phpExcel->getImportFldObj('Dec')
        );
        
        /* Parent fields reference */
        $fa['parent_first_name']['refOnly']     = true;
        $fa['parent_address_flat']['refOnly']   = true;
        $fa['parent_address_street']['refOnly'] = true;
        $fa['parent_address_po_code']['refOnly']= true;
        $fa['parent_phone']['refOnly']          = true;
        $fa['parent_mobile']['refOnly']         = true;
        $fa['parent_email']['refOnly']          = true;
        $fa['parent_payment_mode']['refOnly']   = true;
        $fa['bank_name']['refOnly']             = true;
        $fa['account_name']['refOnly']          = true;
        $fa['bank_code']['refOnly']             = true;
        $fa['branch']['refOnly']                = true;
        $fa['account_no']['refOnly']            = true;
        $fa['dda']['refOnly']                   = true;
        $fa['jan_payment']['refOnly']           = true;
        $fa['feb_payment']['refOnly']           = true;
        $fa['mar_payment']['refOnly']           = true;
        $fa['apr_payment']['refOnly']           = true;
        $fa['may_payment']['refOnly']           = true;
        $fa['jun_payment']['refOnly']           = true;
        $fa['jul_payment']['refOnly']           = true;
        $fa['aug_payment']['refOnly']           = true;
        $fa['sep_payment']['refOnly']           = true;
        $fa['oct_payment']['refOnly']           = true;
        $fa['nov_payment']['refOnly']           = true;
        $fa['dec_payment']['refOnly']           = true;

        /* Course field reference */
        $fa['course_code']['refOnly']           = true;
        #$fa['course_title']['refOnly']          = true;
        /* Level field reference */
        $fa['level_code']['refOnly']            = true;
        /* Batch field reference */
        $fa['batch_code']['refOnly']            = true;

        if ($fa['registration_no'] != '') {
            $config = array(
                 'module'               => 'enterpriseIms_contact'
                ,'matchFieldArr'        => array('registration_no')
                ,'fldsArr'              => $fa
                ,'callbackAfterInsert'  => 'importDataRowCallback'
            );
        }

        return $phpExcel->importData($config);
    }

    /**
     *
     * @param type $contact_id
     * @param type $fa
     */
    function importDataRowCallback($contact_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        
        $parent_first_name = $fa['parent_first_name'];
        
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $date_of_birth = date('Y', strtotime($contactRec['date_of_birth']));
        $diff = date('Y') - $date_of_birth;

        $facontact = array();
        $facontact['age'] = $diff;

        $contact_id = $fn->saveRecord($facontact, 'contact', 'contact_id', $contact_id);
        
        $parentRec = '';
        $SQLParent = "
        SELECT *
        FROM parent
        WHERE first_name = '{$parent_first_name}'
        ";
        $resultParent    = $db->sql_query($SQLParent);
        $numRowsParent   = $db->sql_numrows($resultParent);
        $parentRec       = $db->sql_fetchrow($resultParent);

        $faParent = array();
        $faParent['first_name']         = $fa['parent_first_name'];
        $faParent['address_flat']       = $fa['parent_address_flat'];
        $faParent['address_street']     = $fa['parent_address_street'];
        $faParent['address_po_code']    = $fa['parent_address_po_code'];
        $faParent['phone']              = $fa['parent_phone'];
        $faParent['mobile']             = $fa['parent_mobile'];
        $faParent['email']              = $fa['parent_email'];
        $faParent['mode_of_payment']    = $fa['parent_payment_mode'];
        $faParent['bank_name']          = $fa['bank_name'];
        $faParent['account_name']       = $fa['account_name'];
        $faParent['bank_code']          = $fa['bank_code'];
        $faParent['branch']             = $fa['branch'];
        $faParent['account_no']         = $fa['account_no'];
        $faParent['dda']                = $fa['dda'];
        
        if($numRowsParent){
            $parent_id = $fn->saveRecord($faParent, 'parent', 'parent_id', $parentRec['parent_id']);
        } else {
            $nextParentCode = $fn->getSettingsValueByKey("nextParentCode");

	        if($nextParentCode < 10) {
	            $parentCode = $fn->getSettingsValueByKey('parentCodePrefix') . '000' . $nextParentCode;
	        } else if($nextParentCode < 99) {
	            $parentCode = $fn->getSettingsValueByKey('parentCodePrefix') . '00' . $nextParentCode;
	        } else if($nextParentCode < 999) {
	            $parentCode = $fn->getSettingsValueByKey('parentCodePrefix') . '0' . $nextParentCode;
	        } else {
	            $parentCode = $fn->getSettingsValueByKey('parentCodePrefix') . $nextParentCode;
	        }

            $faParent['parent_code']  = $parentCode;
            $parent_id = $fn->addRecord($faParent, 'parent');

            $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextParentCode'";
            $result = $db->sql_query($SQL);
        }
        
        //link to parent
        $recCount = $fn->getRecordCount('parent_contact', "parent_id = '{$parent_id}' AND contact_id = '{$contact_id}'");
        if (is_numeric ($parent_id) && $recCount == 0) {
            $fa2 = array();
            $fa2['parent_id'] = $parent_id;
            $fa2['contact_id']  = $contact_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'parent_contact');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'parent_contact');
            $result = $db->sql_query($SQL);
        }
        
        $sqlCC = "
        SELECT contact_id FROM course_contact
        WHERE contact_id = {$contact_id}
        ";
        $resultCC    = $db->sql_query($sqlCC);
        $numRowsCC   = $db->sql_numrows($resultCC);
        
        if ($numRowsCC == 0) {
            
            /* Creation of Order Record with Parent details */
            $parentRec = $fn->getRecordRowByID('parent', 'parent_id', $parent_id);            
            $year_enrolled = date('Y');
            
            $faOrder = array();
            $faOrder['cust_first_name']         = $parentRec['first_name'];
            $faOrder['cust_last_name']          = $parentRec['last_name'];
            $faOrder['cust_email']              = $parentRec['email'];
            $faOrder['cust_phone']              = $parentRec['phone'];
            $faOrder['cust_address1']           = $parentRec['address_flat'];
            $faOrder['cust_address2']           = $parentRec['address_street'];
            $faOrder['cust_address_state']      = $parentRec['address_state'];
            $faOrder['cust_address_po_code']    = $parentRec['address_po_code'];
            $faOrder['parent_id']               = $parent_id;
            $faOrder['payment_method']          = $parentRec['mode_of_payment'];
            $faOrder['module']                  = 'enterpriseIms_course';
            $faOrder['order_status']            = 'Due';
            $faOrder['order_date']              =  date('Y-m-d');
            $faOrder['contact_module']          = 'enterpriseIms_parent';
            $faOrder['year_of_enrollment']      = $year_enrolled;
            $order_id = $fn->addRecord($faOrder, "order");
            
            /* Creation of Order Item Record with Course details */
            $course_code = $fa['course_code'];
            $courseRec = $fn->getRecordByCondition('course', "course_code = '{$course_code}'");
            
            $faOI = array();
            $faOI['order_id']   = $order_id;
            $faOI['module']     = 'enterpriseIms_course';
            $faOI['record_id']  = $courseRec['course_id'];
            $faOI['contact_id'] = $contact_id;
            $faOI['qty']        = 1;
            $faOI['item_title'] = $courseRec['title'];
            #$faOI['unit_price'] = $courseRec['price'];
            $faOI['unit_price'] = '60.00';
            $fn->addRecord($faOI, 'order_item');
            
            /* Creation of Course Contact Record details */
            $level_code = $fa['level_code'];
            $levelRec   = $fn->getRecordByCondition('level', "level_code = '{$level_code}'");
            
            $batch_code = $fa['batch_code'];
            $batchRec = $fn->getRecordByCondition('batch', "batch_code = '{$batch_code}' AND course_id = '{$courseRec['course_id']}'");
            
            $year_enrolled = date('Y');
            $faCourseCont = array();
            $faCourseCont['order_id']         = $order_id;
            $faCourseCont['course_id']        = $courseRec['course_id'];
            $faCourseCont['level_id']         = $levelRec['level_id'];
            $faCourseCont['parent_id']        = $parent_id;
            $faCourseCont['batch_id']         = $batchRec['batch_id'];
            $faCourseCont['contact_id']       = $contact_id;
            //$fa['course_subsidy_history_id']= $subsidy_id;
            $faCourseCont['year_of_enrollment']    = $year_enrolled;
            
            $id = $fn->addRecord($faCourseCont, 'course_contact');
            
            if ($fa['jan_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 1, $fa['jan_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['feb_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 2, $fa['feb_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['mar_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 3, $fa['mar_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['apr_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 4, $fa['apr_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['may_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 5, $fa['may_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['jun_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 6, $fa['jun_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['jul_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 7, $fa['jul_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['aug_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 8, $fa['aug_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['sep_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 9, $fa['sep_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['oct_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 10, $fa['oct_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['nov_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 11, $fa['nov_payment'], $fa['parent_payment_mode']);
            }
            if ($fa['dec_payment'] != '') {
                $this->getCreateInvoiceReceiptForImport($order_id, $contact_id, 12, $fa['dec_payment'], $fa['parent_payment_mode']);
            }
        }
    }

    /**
     *
     */
    function getCreateInvoiceReceiptForImport($order_id, $contact_id, $month, $payment_status, $payment_mode) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        /* Creation of Invoice Record details */
        $invoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");
        
        if($invoiceCode < 10) {
            $invoice_code = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $invoiceCode;
        } else if($invoiceCode < 99) {
            $invoice_code = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $invoiceCode;
        } else if($invoiceCode < 999) {
            $invoice_code = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $invoiceCode;
        } else {
            $invoice_code = $fn->getSettingsValueByKey('invoiceCodePrefix') . $invoiceCode;
        }

        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate    = $db->sql_query($SQLUpdate);

        $current_year = date('Y');
        $faInvoice = array();
        $faInvoice['order_id']         = $order_id;
        $faInvoice['contact_id']       = $contact_id;
        $faInvoice['invoice_code']     = $invoice_code;
        $faInvoice['invoice_month']    = $month;
        $faInvoice['invoice_date']     = $current_year . '-' . $month . '-' . '1';
        $faInvoice['invoice_amount']   = '60.00';
        $faInvoice['status']           = 'Due';
        $faInvoice['creation_date']    = date("Y-m-d H:i:s");
        $invoice_id = $fn->addRecord($faInvoice, 'invoice');

        if ($payment_status == 'Paid' || $payment_status == 'Cash' || $payment_status == 'cash') {
            //To update receipt codes
            $receiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

            if($receiptCode < 10) {
                $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '000' . $receiptCode;
            } else if($receiptCode < 99) {
                $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '00' . $receiptCode;
            } else if($receiptCode < 999) {
                $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '0' . $receiptCode;
            } else {
                $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . $receiptCode;
            }

            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            
            if ($payment_status == 'Cash' || $payment_status == 'cash') {
                $mode_of_payment = 'Cash';
            } else {
                $mode_of_payment = $payment_mode;
            }
            
            $faReceipt = array();
            $faReceipt['amount']         = '60.00';
            $faReceipt['order_id']       = $order_id;
            $faReceipt['receipt_code']   = $receipt_code;
            $faReceipt['mode_of_payment']= $mode_of_payment;
            /*$faReceipt['cheque_no']      = $cheque_no;
            $faReceipt['cheque_date']    = $cheque_date;
            $faReceipt['bank_name']      = $bank_name;
            $faReceipt['remarks']        = $remarks;*/
            
            $faReceipt['date']           = $current_year . '-' . $month . '-' . '1';
            $faReceipt['receipt_status'] = 'Paid';
            $faReceipt['creation_date']  = date("Y-m-d H:i:s");
            $faReceipt['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($faReceipt, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();

            $faInvPaid = array();
            $faInvPaid['status'] = 'Paid';
            $faInvPaid['invoice_paid_date'] = $current_year . '-' . $month . '-' . '1';
            $faReceipt['modification_date']  = date("Y-m-d H:i:s");
            $invoice_paid_id = $fn->saveRecord($faInvPaid, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $faIrh = array();
            $faIrh['receipt_id']    = $receipt_id;
            $faIrh['invoice_id']    = $invoice_id;
            $faIrh['amount']        = '60.00';
            $faIrh['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($faIrh, 'invoice_receipt_history');
        }        
    }

    /**
     *
     */
    function linkConactToInterest($contact_id, $interest){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $intArr = explode(',', $interest);
        if (count($intArr) == 0){
            return;
        } else {
            /******** delete all the previous interests linked ******/
            $SQL = "
            DELETE FROM interest_contact
            WHERE contact_id = {$contact_id}
            ";
            $result = $db->sql_query($SQL);
        }

        foreach($intArr AS $intTitle){
            $intRec = $fn->getRecordByCondition('interest', "title='{$intTitle}'");

            if (!is_array($intRec)){
                continue;
            }

            $interest_id = $intRec['interest_id'];
            $SQL = "
            SELECT * FROM interest_contact
            WHERE contact_id = {$contact_id}
              AND interest_id = {$interest_id}
            ";
            $result      = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows == 0){
                $fa = array();
                $fa['contact_id']    = $contact_id;
                $fa['interest_id']   = $interest_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, "interest_contact");
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getEnterpriseImsContactEnterpriseImsCourseLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $courseFld = ($formObj->mode == 'edit') ? 'c.course_id' : 'c.title AS course_title';
        $batchFld = ($formObj->mode == 'edit') ? 'b.batch_id' : 'b.title AS batch_title';
        $linkStr = "<a href='index.php?module=ecommerce_order&_spAction=printOrder&order_id'>Print</a>";
        
        $SQL = "
        SELECT cc.course_contact_id
              ,c.title AS course_title
              ,sd.title AS subsidy_title
              ,sdis.title AS discount_title
              ,b.title AS batch_title
              ,cc.year_of_enrollment
              ,IF ((cc.order_id IS NOT NULL AND cc.order_id != ''),
                (CONCAT_WS('', '<a href=\'index.php?_topRm=finance&module=enterpriseIms_order&_action=edit&order_id=', cc.order_id, '\' target=\'_blank\'>Goto Finance</a>'))
              , '')
               AS order_link
        FROM course_contact cc 
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN course_subsidy_history s ON (cc.course_subsidy_history_id = s.course_subsidy_history_id)
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        LEFT JOIN course_subsidy_history csdis ON (cc.discount = csdis.course_subsidy_history_id)
        LEFT JOIN (subsidy_discount sdis) ON (csdis.subsidy_discount_id = sdis.subsidy_discount_id and sdis.category_type = 'Discount')
        WHERE cc.contact_id = '{$id}' AND company_id is NULL
        ORDER BY cc.course_contact_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $interest_id     = $fn->getReqParam('interest_id');
        $contact_id      = $fn->getReqParam('contact_id');
        $course_id       = $fn->getReqParam('course_id');
        $batch_id        = $fn->getReqParam('batch_id');
        $class_id        = $fn->getReqParam('class_id');
        $subscribe       = $fn->getReqParam('subscribe');
        $special_search  = $fn->getReqParam('special_search');
        $course_status   = $fn->getReqParam('course_status');
        $status          = $fn->getReqParam('status');
        $enrollment_year = $fn->getReqParam('enrollment_year');

        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }

        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            //------------------------------------------------------------------------//

            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(c.subscribe != 1 OR c.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "c.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }

            if ($tv['special_search']  == 'Batch-Not-Linked') {
                $searchVar->sqlSearchVar[] = "cc.batch_id IS NULL";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name         LIKE '%{$tv['keyword']}%'
                    OR c.last_name          LIKE '%{$tv['keyword']}%'
                    OR c.company_name       LIKE '%{$tv['keyword']}%'
                    OR c.id_card_no         LIKE '%{$tv['keyword']}%'
                    OR c.registration_no    LIKE '%{$tv['keyword']}%'
                    OR c.email              LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ic.interest_id = {$interest_id}";
            }

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
                
                if ($status == 'Graduated') {
                    $searchVar->sqlSearchVar[] = "c.graduation_year = '{$enrollment_year}'";
                } else {
                    $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
                }
            } else {
                $searchVar->sqlSearchVar[] = "c.status = 'Active'";
          	}

            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.course_id = {$course_id}";
            }

            if ($course_status != '') {
                $searchVar->sqlSearchVar[] = "cc.course_status = '{$course_status}'";
            }

            if ($batch_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.batch_id = {$batch_id}";
            }

            if ($class_id != '' ) {
                $searchVar->sqlSearchVar[] = "sc.class_id = {$class_id}";
            }

            if ($enrollment_year != '' && $status == '') {
                $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            $searchVar->sortOrder = "c.registration_no";
        }
    }

    /**
     *
     */
	function getEnterpriseImsContactEnterpriseImsParentLinkSQL($id) {
        return "
        SELECT p.parent_id
              ,p.first_name
              ,p.id_card_no
              ,p.phone
              ,p.mobile
              ,p.email
        FROM parent p 
        LEFT JOIN parent_contact pc ON (p.parent_id = pc.parent_id)
        WHERE pc.contact_id = '{$id}'
        ";

    }

    /**
     *
     */
    function getChangeStatusFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        if (!$this->getChangeStatusFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $with_drawal= $fn->getPostParam('with_drawal');
        $refund_payable = $fn->getPostParam('refund_payable');
        $refund_payable_bank_ac = $fn->getPostParam('refund_payable_bank_ac');
        $contact_id = $fn->getPostParam('contact_id');
        
        $sqlUpdate = "
        UPDATE contact 
        SET with_drawal = '{$with_drawal}'
           ,refund_payable = '{$refund_payable}'
           ,refund_payable_bank_ac = '{$refund_payable_bank_ac}'
           ,status = 'Withdraw'
        WHERE contact_id = {$contact_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getChangeStatusFormValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        /*$validate->validateData('with_drawal', 'Please enter the reasons for withdrawal');
        $validate->validateData('refund_payable', 'Please enter the refund payable to');
        $validate->validateData('refund_payable_bank_ac', 'Please enter bank account');*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintWithdrawalForm() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $contact_id = $fn->getReqParam('contact_id');

        $SQL = "
        SELECT c.*
				,gc.name AS country_name
        		,cor.title AS course_title
        		,b.title AS session_title
        		,p.first_name AS parent_name
        		,p.address_flat
        		,p.address_street
        		,p.address_po_code
        		,p.address_country
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (course_contact cc) ON (cc.contact_id = c.contact_id)
        LEFT JOIN (course cor) ON (cc.course_id = cor.course_id)
        LEFT JOIN (batch b) ON (cor.course_id = b.course_id)
        LEFT JOIN geo_country gc ON (p.address_country = gc.country_code)
        WHERE c.contact_id = {$contact_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $today = date("Y-m-d");
        
        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                //$pdf->Image('images/icon-dashboard.png',10,5,45);

                /* Header */
                $pdf->SetFont('Courier','B',13);
                $pdf->SetXY(100, 0);
                $pdf->Cell(21, 20, "As-Siddiq Centre for Islamic Studies Pte Ltd", 0, 0, 'C');                
                $pdf->Ln(20);

                $pdf->SetFont('Courier','B',12);
                $pdf->SetXY(100, 8);
                $pdf->Cell(21, 20, "Student Withdrawal Form - Weekend Islamic School", 0, 0, 'C');                
                $pdf->Ln(20);

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"Student Information(1)",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Student Name",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['first_name'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"BC / NRIC No.",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['id_card_no'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Current Level",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['course_title'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Current Session",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['session_title'],1,0, 'L', 1);
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"Parent / Legal Guardian Information",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Name",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['parent_name'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Address",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['address_flat'] . $row['address_street'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(120,8,$row['country_name'],1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(35,8,"Postal Code",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(35,8,$row['address_po_code'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,25,"Reason For Withdrawal",'TLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(135,25,$row['with_drawal'],'TLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(80,8,"If there is any refund of fees, to",'TLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(110,8,$row['refund_payable'],'TLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(80,8,"whom should the cheque be payable to?",'BLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(110,8,"",'BLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(80,8,"GIRO Status",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(110,8,"Yes/No(If 'Yes', Please fill the GIRO Termination form)",1,0, 'L', 1);
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"For Office Use Only",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Refund Amount(if any):",'TL',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(135,8,"                   ",'TR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(55,8,"Cheque No.:",'L',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(55,8,"                   ",0,0, 'L', 1);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(15,8,"Date:",0,0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(65,8,"                   ",'R',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(26,8,"Remarks:",'L',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(164,8,"                                                                     ",'R',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"Name of Form Teacher:",'BL',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(50,8,"                ",'B',0, 'L', 1);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"Teacher's Signature:",'B',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(40,8,"                ",'BR',0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(15);

                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(30,8,"Date",'T',0, 'L', 1);
                $pdf->Cell(70,8,"  ",0,0, 'L', 1);
                $pdf->Cell(90,8,"Parent / Legal Guardian Information",'T',0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(15);

                $pdf->SetFont('Courier','B',12);
                $pdf->Cell(190,8,"As-Siddiq Centre for Islamic Studies Pte Ltd",'T',0, 'R', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(190,8,"152 Still Road singapore 423991 - Tel: 65474407 - Fax: 63486023 - Email: info@simplyislam.sg",0,0, 'R', 1);
            }
            
        } 
       
        /* Creation of media record of the invoice */
        $file_name = 'WITHDRAWAL_SUBMIT_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        //$pdf->Output($outputFileName , "F");
		$pdf->Output();
    }
}
