<?
class CP_Admin_Modules_Edukloud_Contact_Model extends CP_Common_Modules_Edukloud_Contact_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('id_card_no' , 'Please enter the id card no.');
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        
        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('edukloud_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if($cpCfg['m.edukloud.contact.hasRegisterNo']){
            $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
            $current_year = date('Y');
            
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");
            
            /*
            if($nextRegNo < 10){
                $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
            }
            else if($nextRegNo < 100){
                $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextRegNo;
            }
            else if($nextRegNo > 99){
                $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextRegNo;
            }
            else{
                $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
            }
            */

            $fa['registration_no'] = $nextRegNo;
        }

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate($contact_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        
        $SQLContact = "
        SELECT cc.* FROM course_contact cc
        LEFT JOIN (course c) ON (cc.course_id = c.course_id)
        WHERE cc.contact_id = {$contact_id}
          AND c.course_type = 'Long Term'
        ";
        $resultContact  = $db->sql_query($SQLContact);  
        $numRowsContact = $db->sql_numrows($resultContact);
        
        if ($numRowsContact > 0) {
            $validate->validateData('email' , 'Please enter email address');
        }
        
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('gender' , 'Please select gender');
        $validate->validateData('id_card_no' , 'Please enter id card no');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');
        
        if($cpCfg['m.edukloud.contact.otherDetailsPvt'] == false){
            $validate->validateData('company_id' , 'Please select the company');
	        $validate->validateData('phone' , 'Please enter phone number');
	        $validate->validateData('race' , 'Please select race');
	        $validate->validateData('marital_status' , 'Please select marital status');
	        //$validate->validateData('email' , 'Please enter email address');
	        $validate->validateData('mobile' , 'Please enter mobile number');
        }

        /*$validate->validateData('company_name' , 'Please enter company name');
        $validate->validateData('company_roc_no' , 'Please enter company roc no');
        $validate->validateData('company_address' , 'Please enter company address');
        $validate->validateData('company_po_code' , 'Please enter company postal code');
        $validate->validateData('company_phone' , 'Please enter company phone number');
        $validate->validateData('company_registration_type' , 'Please select company registration type');*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave(){
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
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'contract_no');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'student_pass_holder');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'nric_type');
        
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        /*$fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'company_roc_no');
        $fa = $fn->addToFieldsArray($fa, 'company_address');
        $fa = $fn->addToFieldsArray($fa, 'company_po_code');
        $fa = $fn->addToFieldsArray($fa, 'company_phone');
        $fa = $fn->addToFieldsArray($fa, 'company_registration_type');*/

        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_name');
        $fa = $fn->addToFieldsArray($fa, 'parent_id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_mobile');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_office_no');
        $fa = $fn->addToFieldsArray($fa, 'address_details');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');

        $fa = $fn->addToFieldsArray($fa, 'school_name');
        $fa = $fn->addToFieldsArray($fa, 'school_country');
        $fa = $fn->addToFieldsArray($fa, 'school_from');
        $fa = $fn->addToFieldsArray($fa, 'school_to');
        $fa = $fn->addToFieldsArray($fa, 'school_highest_qual');
        
        //$fa = $fn->addToFieldsArray($fa, 'company_id');
        //$fa = $fn->addToFieldsArray($fa, 'company_fax');
        
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
        $fa = $fn->addToFieldsArray($fa, 'salary_range');
        $fa = $fn->addToFieldsArray($fa, 'apply_for_sdf');
        
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');

        $fa = $fn->addToFieldsArray($fa, 'date_of_arrival');
        $fa = $fn->addToFieldsArray($fa, 'passport_country_issued');
        $fa = $fn->addToFieldsArray($fa, 'overseas_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'overseas_address_street');
        $fa = $fn->addToFieldsArray($fa, 'overseas_address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'overseas_address_country');
        $fa = $fn->addToFieldsArray($fa, 'overseas_contact_no');
        $fa = $fn->addToFieldsArray($fa, 'parent_passport_country_issued');
        $fa = $fn->addToFieldsArray($fa, 'parent_nationality');
        $fa = $fn->addToFieldsArray($fa, 'parent_occupation');

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
        
        if ($course_status != '' && $cpCfg['m.edukloud.course.hasCourseContactStatus']) {
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

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'first_name'           => $phpExcel->getImportFldObj('First Name')
             ,'last_name'            => $phpExcel->getImportFldObj('Last Name')
             ,'email'                => $phpExcel->getImportFldObj('Email')
             ,'phone_direct'         => $phpExcel->getImportFldObj('Phone')
             ,'mobile'               => $phpExcel->getImportFldObj('Mobile')
             ,'address_flat'         => $phpExcel->getImportFldObj('Address 1')
             ,'address_street'       => $phpExcel->getImportFldObj('Address 2')
             ,'address_city'         => $phpExcel->getImportFldObj('City')
             ,'address_state'        => $phpExcel->getImportFldObj('State')
             ,'address_po_code'      => $phpExcel->getImportFldObj('Zip Code')
             ,'address_country'      => $phpExcel->getImportFldObj('Country')
             ,'subscribe'            => $phpExcel->getImportFldObj('Newsletter')
        );

        $fa['address_country']['specialType'] = 'geo_country';
        $fa['subscribe']['defaultValue'] = 1;

        $config = array(
             'module'          => 'edukloud_contact'
            ,'matchFieldArr'   => array('email')
            ,'fldsArr'         => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

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

        //link to interest
        if($cpCfg['m.common.contact.showInterestInImport']){
            $interest_id = $fn->getPostParam('interest_id', '', true);
            $recCount = $fn->getRecordCount('interest_contact', "interest_id = '{$interest_id}' AND contact_id = '{$contact_id}'");
            if (is_numeric ($interest_id) && $recCount == 0) {
                $fa2 = array();
                $fa2['interest_id'] = $interest_id;
                $fa2['contact_id']  = $contact_id;
                $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'interest_contact');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'interest_contact');
                $result = $db->sql_query($SQL);
            }
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
    function getEdukloudContactEdukloudCourseLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $courseFld = ($formObj->mode == 'edit') ? 'c.course_id' : 'c.title AS course_title';
        $batchFld = ($formObj->mode == 'edit') ? 'b.batch_id' : 'b.title AS batch_title';
        $linkStr = "<a href='index.php?module=ecommerce_order&_spAction=printOrder&order_id'>Print</a>";
        
      /*
      ,IF ((cc.order_id IS NOT NULL AND cc.order_id != ''),
        (CONCAT_WS('', '<a href=\'index.php?module=edukloud_order&_spAction=printOrder&order_id=', cc.order_id, '\' target=\'_blank\'>Click to Print</a>'))
      , '')
       AS invoice_print_txt
      */
      
        $SQL = "
        SELECT cc.course_contact_id
              ,c.title AS course_title
              ,sd.title AS subsidy_title
              ,sdis.title AS discount_title
              ,b.title AS batch_title
              ,cc.year_of_enrollment
              ,IF ((cc.order_id IS NOT NULL AND cc.order_id != ''),
                (CONCAT_WS('', '<a href=\'index.php?_topRm=finance&module=edukloud_order&_action=edit&order_id=', cc.order_id, '\' target=\'_blank\'>Goto Finance</a>'))
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

        $interest_id    = $fn->getReqParam('interest_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $course_id      = $fn->getReqParam('course_id');
        $batch_id       = $fn->getReqParam('batch_id');
        $class_id       = $fn->getReqParam('class_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $course_status  = $fn->getReqParam('course_status');

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

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            if($cpCfg['m.edukloud.contact.hasRegistrationNo']){
                $searchVar->sortOrder = "c.registration_no";
            } else {
                $searchVar->sortOrder = "c.last_name, c.first_name";
            }
        }
    }

    /**
     *
     */
        function getPrintForm12() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $course_contact_id = $fn->getReqParam('course_contact_id');
        $courseContactRec  = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        
        //$contact_id = $fn->getReqParam('id');
        $contact_id = $courseContactRec['contact_id'];
        
        $template = 'Form 12.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Form 12_' . $contact_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $current_year =  date('Y');
        $dob_year = explode('-', $contactRec['date_of_birth']);
        $dob_year_value  = $dob_year[0];
        $year_difference = $current_year - $dob_year_value;

        if($year_difference > 18){
            $student_name = $contactRec['first_name'] . ' ' .$contactRec['last_name'];
            $parent_name  = '-';
            $id_card_no = $contactRec['id_card_no'];
        }
        else{
            $parent_name   = $contactRec['first_name'] . ' ' .$contactRec['last_name'];
            $student_name  = $contactRec['emergency_contact_name'];
            $id_card_no    = $contactRec['parent_id_card_no'];
        }

        $valArr = array();
        $valArr['student_name']         = $student_name;
        $valArr['student_id_card_no']   = $id_card_no;
        $valArr['date_of_birth']        = $contactRec['date_of_birth'];
        $valArr['phone']                = $contactRec['phone'];
        $valArr['address_flat']         = $contactRec['address_flat'];
        $valArr['parent_name']          = $parent_name;
        $valArr['parent_id_no']         = $contactRec['parent_id_card_no'];
        $valArr['date']                 = $today;
        $valArr['institute_name']       = $cpCfg['printCompanyNamePvt'];

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
        function getPrintStudentContract() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $full_part_time_value = '';
        $medical_insurance_value = '';
        
        $course_contact_id = $fn->getReqParam('course_contact_id');
        $courseContactRec  = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        
        $contact_id = $courseContactRec['contact_id'];
        $order_id   = $courseContactRec['order_id'];
        
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $SQLScienceLab = "
        SELECT item_title FROM order_item
        WHERE item_title = 'Science Lab'
          AND order_id = {$order_id}
        ";
        $resultScienceLab = $db->sql_query($SQLScienceLab);  
        $numRowsScienceLab = $db->sql_numrows($resultScienceLab);
        
        if ($numRowsScienceLab > 0 && $orderRec['medical_insurance'] == 1){
            $courseFeeText = ", science practical lab fees & medical insurance";
        } else if ($numRowsScienceLab > 0 && $orderRec['medical_insurance'] == 0){
            $courseFeeText = "& science practical lab fees";
        } else if ($numRowsScienceLab == 0 && $orderRec['medical_insurance'] == 1){
            $courseFeeText = "& medical insurance";
        } else {
            $courseFeeText = "";
        }

        $sqlAppend = '';
        if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = ',o.registration_type ,o.medical_insurance, add_registration_fee';
        }
        
        $SQLTotalAmt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $SQLTotalAmt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,o.registration_type 
              ,o.medical_insurance
              ,o.add_registration_fee
              ,o.full_time
              ,cc.no_of_months
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt = $db->sql_query($SQLTotalAmt);  
        $resultForPvtNet = $db->sql_query($SQLTotalAmt);  

        if($orderRec['medical_insurance'] == 1){
            $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");                
       }

        $modObj = getCPModuleObj('edukloud_order');
        
        //to get the total after deducting the discount
        $total_invoice_amount = $modObj->view->getTotalForPvtInst($resultForPvt);
        $add_registration_fee = $orderRec['add_registration_fee'];
        if($add_registration_fee == 1){
            $total_course_fees = $total_invoice_amount + $fn->getSettingsValueByKey("registrationFee");;
        } else {
            $total_course_fees = $total_invoice_amount;
        }
        
        //to get the total without deducting the discount
        $netTotal  = $modObj->view->getTotalForPvtInst($resultForPvtNet, 'getTotalOnly');
        $netTotal += $medical_insurance_value;
        
        $discountTotal = '';
        
        //to get the discount total
        $expDiscount = array('condn' => " AND module='edukloud_discount'");
        $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', 
        $order_id, $expDiscount);
        if($orderItemRecDiscount['unit_price'] > 0){
            $discountPer   = $orderItemRecDiscount['unit_price'];
            $discountTotal = ($netTotal *  $discountPer)/100;
            $discountTotal = round($discountTotal, 2);
        }            
        if($discountTotal == ''){
            $discountTotal = '-';
        }
        $template = 'Student Contract.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Student Contract_' . $contact_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
        
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $date_of_birth = $dateUtil->formatDate($contactRec['date_of_birth'], 'DD/MM/YYYY');

        $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
        
        /* Fetching Insurance Details */
        $SQLInsurance = "
        SELECT i.title FROM insurance i
        LEFT JOIN (student_insurance si) ON (i.insurance_id = si.insurance_id)
        WHERE si.course_contact_id = {$course_contact_id}
          AND si.contact_id = {$contact_id} 
        ";
        $resultInsurance = $db->sql_query($SQLInsurance);
        $rowInsurance = $db->sql_fetchrow($resultInsurance);

        $countryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$contactRec['address_country']}'");
        $overseasCountryRec = $fn->getRecordByCondition('geo_country', "country_code = '{$contactRec['overseas_address_country']}'");
        $studentPassortRec = $fn->getRecordByCondition('geo_country', "country_code = '{$contactRec['passport_country_issued']}'");
        $parentPassortRec = $fn->getRecordByCondition('geo_country', "country_code = '{$contactRec['parent_passport_country_issued']}'");

        /* To show list of subjects */
        $outcome = '';
        $synopsys = '';
        $SQLSubjectHist = "
        SELECT subject_id FROM course_contact_subject_history
        WHERE course_contact_id = {$course_contact_id}
        ";
        $resultSubjectHist  = $db->sql_query($SQLSubjectHist);
        $numRows  = $db->sql_numrows($resultSubjectHist);
        while ($rowSubjectHist = $db->sql_fetchrow($resultSubjectHist)) {
            $SQLSubject = "
            SELECT * FROM subject
            WHERE subject_id = {$rowSubjectHist['subject_id']}
            ";
            $resultSubject  = $db->sql_query($SQLSubject);
            while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                $outcome .= $rowSubject['title'] . ' - ' . $rowSubject['code'] . ' ' . $rowSubject['outcome'];
                $synopsys .= $rowSubject['title'] . ' (SUBJECT CODE: ' . $rowSubject['code'] . ')' . $rowSubject['synopsys'];
            }
        }
        
        $blkInstallSchedule = array();
        $blkInstallAmt = array();
        $blkDueDate = array();
        $SQLInstallment = "
        SELECT inst.*
        FROM installment inst
        LEFT JOIN (invoice i) ON (inst.invoice_id = i.invoice_id)
        WHERE i.order_id = {$order_id}
        ";
        $resultInstallment = $db->sql_query($SQLInstallment);
        while ($rowInstallment = $db->sql_fetchrow($resultInstallment)) {
            $arrSchedule = array('title' => $rowInstallment['title']);
            $blkInstallSchedule[] = $arrSchedule;

            $installment_amount = number_format($rowInstallment['amount'], 2);
            $arrAmt = array('installment_amount' => $installment_amount);
            $blkInstallAmt[] = $arrAmt;

            $due_date = $dateUtil->formatDate($rowInstallment['invoice_date'], 'DD/MM/YYYY');
            $arrDueDate = array('due_date' => $due_date);
            $blkDueDate[] = $arrDueDate;
        }
        
        if($numRows > 4){
            $full_part_time_value = 'Full Time';
        }
        elseif($numRows < 4){
            $full_part_time_value = 'Part Time';
        }
        
        $from_date  = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
        $to_date    = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
        $award_date = $fn->getCPDate($courseRec['award_date'], 'dS F Y');
        $award_date = $award_date .'**';
        
        
        $date_of_arrival = '-NA-';
        if($contactRec['date_of_arrival'] != ''){
            $date_of_arrival = $fn->getCPDate($courseRec['date_of_arrival'], 'dS F Y');
        }

        $valArr = array();
        /* Contact Details */
        $valArr['student_name']         = $contactRec['first_name'] . ' ' .$contactRec['last_name'];
        $valArr['student_id_card_no']   = $contactRec['id_card_no'];
        $valArr['date_of_birth']        = $date_of_birth;
        $valArr['nationality']          = $contactRec['nationality'];
        $valArr['phone']                = $contactRec['phone'];
        $valArr['mobile']               = $contactRec['mobile'];
        $valArr['address_flat']         = $contactRec['address_flat'];
        $valArr['address_street']       = $contactRec['address_street'];
        $valArr['address_country']      = $countryRec['name'];
        $valArr['address_po_code']      = $contactRec['address_po_code'];
        $valArr['date']                 = $today;
        $valArr['parent_name']          = $contactRec['emergency_contact_name'];
        $valArr['parent_id_no']         = $contactRec['parent_id_card_no'];
        $valArr['parent_mobile']        = $contactRec['emergency_contact_mobile'];
        $valArr['student_passport_country'] = $studentPassortRec['name'];

        $valArr['overseas_address_flat']        = $contactRec['overseas_address_flat'];
        $valArr['overseas_address_street']      = $contactRec['overseas_address_street'];
        $valArr['overseas_address_country']     = $overseasCountryRec['name'];
        $valArr['overseas_address_po_code']     = $contactRec['overseas_address_po_code'];
        $valArr['overseas_contact_no']          = $contactRec['overseas_contact_no'];
        $valArr['parent_passport_country']      = $parentPassortRec['name'];
        $valArr['parent_nationality']           = $contactRec['parent_nationality'];
        $valArr['parent_occupation']            = $contactRec['parent_occupation'];
        $valArr['arrival_date']                 = $date_of_arrival;
        
        /* Course Details */
        $valArr['course_title']         = $courseRec['title'];
        $valArr['part_full_time']       = $full_part_time_value;
        $valArr['from_date']            = $from_date;
        $valArr['to_date']              = $to_date;
        $valArr['course_duration']      = $courseRec['duration'];
        $valArr['course_duration_type'] = $courseRec['month_or_hour'];
        $valArr['type_of_qualification']= $courseRec['qualification_type'];
        $valArr['org_development_course']= $courseRec['developed_by'];
        $valArr['org_awards_course']    = $courseRec['award_course'];
        $valArr['course_entry_req']     = $courseRec['course_entry_requirements'];
        $valArr['award_date']           = $award_date;
        $valArr['change']               = '** Subject to Change';
        $valArr['scheduled_holidays']   = $courseRec['scheduled_holidays'];
        $valArr['examination_assessment'] = $courseRec['examination_assessment'];
        $valArr['examination_results']  = $courseRec['examination_results'];
        $valArr['course_fee_text']      = $courseFeeText;

        $valArr['course_fee']           = number_format($netTotal, 2);
        $valArr['discount_fee']         = number_format($discountTotal, 2);
        $valArr['contract_no']          = $courseContactRec['contract_no'];
        
        /* Insurance Details */
        $valArr['insurance_company']   = $rowInsurance['title'];

        /* Order Details */
        if ($orderRec['add_registration_fee'] == 1){
            $registration_fee = $cpCfg['registrationFeePvt'];
        } else {
            $registration_fee = '-';
        }
        
        $valArr['reg_fee']              = $registration_fee;
        $valArr['no_of_installment']    = $orderRec['no_of_installment'];
        $valArr['fees_payable']         = number_format($total_course_fees, 2);
        $valArr['installment_amt']      = number_format($total_invoice_amount / $orderRec['no_of_installment'], 2);
        
        /* FPS Calculation on page 9 */
        $course_fees_amt = $total_invoice_amount / $courseRec['duration'];
        $fee_protected = $course_fees_amt * 6;
        $valArr['fee_protected']         = number_format($fee_protected, 2);

        /* Subject Details */
        $valArr['subject_outcome']     = $outcome;
        $valArr['subject_synopsys']    = $synopsys;

        /* Institute Details */
        $valArr['institute_name']           = $cpCfg['printCompanyNamePvt'];
        $valArr['institute_cpe_no']         = $cpCfg['cpeRegistrationNoPvt'];
        $valArr['institute_address_flat']   = $cpCfg['addressFlatPvt'];
        $valArr['institute_address_street'] = $cpCfg['addressStreetPvt'];
        $valArr['institute_address_country']= $cpCfg['addressCountryAndCodePvt'];
        $valArr['institute_contact_no']     = $cpCfg['contactNoPvt'];
        $valArr['institute_email']          = $cpCfg['printCompanyEmailPvt'];
        $valArr['institute_fax']            = $cpCfg['printCompanyFaxPvt'];
        $valArr['medical_insurance_provider'] = $cpCfg['medicalInsuranceProviderPvt'];
        $valArr['current_date']             = $today;
        

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkInstallSchedule', $blkInstallSchedule);         
        $TBS->MergeBlock('blkInstallAmt', $blkInstallAmt);         
        $TBS->MergeBlock('blkDueDate', $blkDueDate);         
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
        function getPrintOfferLetter() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $course_contact_id = $fn->getReqParam('course_contact_id');
        $courseContactRec  = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        
        //$contact_id = $fn->getReqParam('id');
        $contact_id = $courseContactRec['contact_id'];
        
        $template = 'Offer Letter.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Offer Letter_' . $contact_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
        $todayMonth =  date('dS F Y');
        $todayWithDay =  date('l, d F Y');
        
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$contactRec['address_country']}'");

        $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);

        $valArr = array();
        /* Contact Details */
        $valArr['student_name']         = $contactRec['first_name'] . ' ' .$contactRec['last_name'];
        $valArr['address_flat']         = $contactRec['address_flat'];
        $valArr['address_street']       = $contactRec['address_street'];
        $valArr['address_country']      = $countryNameRec['name'];
        $valArr['address_po_code']      = $contactRec['address_po_code'];
        $valArr['date']                 = $today;
        $valArr['date_month']           = $todayMonth;
        $valArr['date_day']             = $todayWithDay;

        /* Course Details */
        $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd F Y');
        $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd F Y');
        $valArr['course_title']         = $courseRec['title'];
        $valArr['from_date']            = $from_date;
        $valArr['to_date']              = $to_date;

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }
}
