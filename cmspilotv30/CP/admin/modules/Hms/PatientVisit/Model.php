<?
class CP_Admin_Modules_Hms_PatientVisit_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT pv.*
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
              ,p.first_name
              ,p.middle_name
              ,p.last_name
              ,p.name
              ,p.nric
              ,p.email
              ,p.mobile
              ,p.dob
              ,p.patient_code
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        ";


        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'pv';

        $status       = $fn->getReqParam('status');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
       // $company_name = $fn->getReqParam('company_name');

        if ($patient_visit_id != "") {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$patient_visit_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pv.patient_visit_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "pv.status = '{$status}'";
            }

            /*if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.first_name      LIKE '%{$tv['keyword']}%'
                    OR p.middle_name      LIKE '%{$tv['keyword']}%'
                    OR p.last_name      LIKE '%{$tv['keyword']}%'
                    OR p.nric   LIKE '%{$tv['keyword']}%'
                    OR p.email  LIKE '%{$tv['keyword']}%'
                    OR p.mobile LIKE '%{$tv['keyword']}%'
                    OR pv.visit_code  LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            $searchVar->sortOrder = "pv.check_up_date DESC, pv.patient_visit_id DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('check_up_date', 'Please select the patient check up date');

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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        //$visit_code = $fn->getSettingsValueByKey("nextPatientvisitCode");
        $fa = $this->getFields();
        //$fa['visit_code'] = $visit_code;

        $id = $fn->addRecord($fa);
        //To update patient visit code
        //$SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode'";
        //$resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);

    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();
        $validate->validateData('bill_type', 'Please select the Bill Type');
        /*$appointment_check_up_date = $fn->getPostParam('appointment_check_up_date');
        $patient_information_id    = $fn->getReqParam('patient_information_id');
        $appointment_check_up_time = $fn->getPostParam('appointment_check_up_time');

        $validate->resetErrorArray();
        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND site_id = {$cpSiteIdSession}";
        }

        if($appointment_check_up_date != ''){
            $SQL = "
            SELECT patient_information_id
            FROM appointment
            WHERE patient_information_id = {$patient_information_id}
            AND check_up_date = '{$appointment_check_up_date}'
            AND check_up_time = '{$appointment_check_up_time}'
            {$appendSqlAp}
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['appointment_check_up_time']['name'] = "appointment_check_up_time";
                $validate->errorArray['appointment_check_up_time']['msg']  = "Appointment already Created for this time";
            }
        }*/

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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $employeeVisitRec = $fn->getRecordRowByID('employee_visit', 'patient_visit_id', $patient_visit_id);

        if($fa['follow_up_date'] != '' ){
            $followUpRec = $fn->getRecordByCondition('follow_up_patient', "patient_visit_id = '{$patient_visit_id}' AND record_type = 'Follow Up'");

            $fa1 = array();
            $fa1['description']      = $fa['follow_up_notes'];
            $fa1['follow_up_date']   = $fa['follow_up_date'];
            $fa1['follow_up_time']   = date("H:i:s");

            if($followUpRec['follow_up_patient_id'] == ''){
                $fa1['record_type']      = 'Follow Up';
                $fa1['patient_visit_id'] = $patient_visit_id;
                $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
                $fa1['employee_id']      = $employeeVisitRec['employee_id'];

                if ($cpCfg['cp.hasMultiUniqueSites']) {
                  $fa1['site_id']        = $cpSiteIdSession;
                }

                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'follow_up_patient');
                $result = $db->sql_query($SQL);
            } else {
                $fa1['modification_date']    = date("Y-m-d H:i:s");
                $fa1['modified_by']       = $fn->getSessionParam('userName');

                $whereCondition = "
                WHERE follow_up_patient_id = {$followUpRec['follow_up_patient_id']}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'follow_up_patient', $whereCondition);
                $db->sql_query($SQL);
            }
        }

        if($fa['longtime_follow_up_date'] != '' ){
            $followUpRec = $fn->getRecordByCondition('follow_up_patient', "patient_visit_id = '{$patient_visit_id}' AND record_type = 'Longtime Follow Up'");

            $fa1 = array();
            $fa1['description']      = $fa['longtime_follow_up_notes'];
            $fa1['follow_up_date']   = $fa['longtime_follow_up_date'];
            $fa1['follow_up_time']   = date("H:i:s");

            if($followUpRec['follow_up_patient_id'] == ''){
                $fa1['record_type']      = 'Longtime Follow Up';
                $fa1['patient_visit_id'] = $patient_visit_id;
                $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
                $fa1['employee_id'] = $employeeVisitRec['employee_id'];

                if ($cpCfg['cp.hasMultiUniqueSites']) {
                  $fa1['site_id']        = $cpSiteIdSession;
                }

                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'follow_up_patient');
                $result = $db->sql_query($SQL);
            } else {
                $fa1['modification_date'] = date("Y-m-d H:i:s");
                $fa1['modified_by']       = $fn->getSessionParam('userName');

                $whereCondition = "
                WHERE follow_up_patient_id = {$followUpRec['follow_up_patient_id']}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'follow_up_patient', $whereCondition);
                $db->sql_query($SQL);
            }
        }

        $appointment_check_up_date = $fn->getPostParam('appointment_check_up_date');
        if($appointment_check_up_date != '' ){
            $patient_information_id    = $fn->getReqParam('patient_information_id');
            $appointment_check_up_time = $fn->getPostParam('appointment_check_up_time');
            $appointment_description   = $fn->getPostParam('appointment_description');
            $appointment_employee_id   = $fn->getPostParam('appointment_employee_id');

            /*$SQLEmpVisit = "
            SELECT employee_id
            FROM employee_visit
            WHERE patient_visit_id = {$patient_visit_id}
            ORDER BY employee_visit_id
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);*/

            $fa3 = array();

            $fa3['patient_information_id']  = $patient_information_id;
            $fa3['dr_Linked']               = $appointment_employee_id;
            $fa3['check_up_date']           = $appointment_check_up_date;
            $fa3['check_up_time']           = $appointment_check_up_time;
            $fa3['source_patient_visit_id'] = $patient_visit_id;
            $fa3['status']         = 'New';

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa3['site_id'] = $cpSiteIdSession;
            }

            $fa3['description']            = $appointment_description;

            $appointmentRec = $fn->getRecordByCondition('appointment', "source_patient_visit_id = '{$patient_visit_id}'");

            if(is_array($appointmentRec)){
                $fa3['modification_date']   = date('Y-m-d-H-i-s');
                $fa3['modified_by']         = $fn->getSessionParam('userName');

                $whereCondition = "WHERE appointment_id = {$appointmentRec['appointment_id']}";
                $sqlAppointmentUpdate    = $dbUtil->getUpdateSQLStringFromArray($fa3, "appointment", $whereCondition);
                $resultAppointmentUpdate = $db->sql_query($sqlAppointmentUpdate);
            } else {
                $fa3['creation_date'] = date("Y-m-d H:i:s");
                $fa3['created_by']    = $fn->getSessionParam('userName');

                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa3, "appointment");
                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
            }

        }

        $SQLOrderCheck = "
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
        ";
        $resultOrderCheck  = $db->sql_query($SQLOrderCheck);
        $numRowsOrderCheck = $db->sql_numrows($resultOrderCheck);
        $rowOrderCheck     = $db->sql_fetchrow($resultOrderCheck);

        if($patientVisitRec['company_id'] != ''){
            $sqlCompany = "
            SELECT company_id
                  ,company_name
                  ,address_flat
                  ,address_street
                  ,address_town
                  ,address_state
                  ,address_country
                  ,phone
            FROM company
            WHERE category = '{$patientVisitRec['bill_type']}'
            AND company_id = {$patientVisitRec['company_id']}
            ORDER BY company_name
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $faComp = array();
            $faComp['company_name']     = $rowCompany['company_name'];
            $faComp['address_flat']     = $rowCompany['address_flat'];
            $faComp['address_street']   = $rowCompany['address_street'];
            $faComp['address_town']     = $rowCompany['address_town'];
            $faComp['address_state']    = $rowCompany['address_state'];
            $faComp['address_country']  = $rowCompany['address_country'];
            $faComp['phone']            = $rowCompany['phone'];

            $whereCondition = "
            WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
            ";
            $updateComName = $dbUtil->getUpdateSQLStringFromArray($faComp, 'patient_visit', $whereCondition);
            $resultComName = $db->sql_query($updateComName);

            if($numRowsOrderCheck > 0){
                $faOrder = array();
                $faOrder['company_id']                    = $patientVisitRec['company_id'];
                $faOrder['company_name']                  = $patientVisitRec['company_name'];
                $faOrder['cust_address1']                 = $patientVisitRec['address_flat'];
                $faOrder['cust_address2']                 = $patientVisitRec['address_street'];
                $faOrder['cust_address_city']             = $patientVisitRec['address_town'];
                $faOrder['cust_address_state']            = $patientVisitRec['address_state'];
                $faOrder['cust_address_country_code']     = $patientVisitRec['address_country'];
                $faOrder['cust_phone']                    = $patientVisitRec['phone'];
                $faOrder['bill_type']                     = $patientVisitRec['bill_type'];

                $whereCondition = "
                WHERE order_id = {$rowOrderCheck['order_id']}
                ";
                $updateOrder = $dbUtil->getUpdateSQLStringFromArray($faOrder, 'order', $whereCondition);
                $resultOrder = $db->sql_query($updateOrder);
            }

        }else{
            $updateComName = "
            UPDATE patient_visit SET company_name = '' , address_flat = '', address_street = '', address_town = '', address_state = '', address_country = '', phone = ''
            WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
            ";
            $resultComName = $db->sql_query($updateComName);

            if($numRowsOrderCheck > 0){
                $updateOrder = "
                UPDATE `order` SET company_id = '' , company_name = '', cust_address1 = '', cust_address2 = '', cust_address_city = '', cust_address_state = '', cust_address_country_code = '', cust_phone = ''
                WHERE order_id = {$rowOrderCheck['order_id']}
                ";
                $resultOrder = $db->sql_query($updateOrder);
            }
        }

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'check_up_date');
        $fa = $fn->addToFieldsArray($fa, 'check_up_time');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'visit_summary');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_notes');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_notes');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_value');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_value');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'nric');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'dr_required');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days');
        $fa = $fn->addToFieldsArray($fa, 'bill_type');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'hms_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }
    /**
     *
     */
    function getHmsCompanyHmsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }
    /**
     *
     */
    function getHmsCompanyHmsDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getHmsCompanyHmsCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     */
    function getAddLabsRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $validate->validateData('supplier_category', 'Please select category');
        $validate->validateData('supplier_id', 'Please select supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddLabsRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddLabsRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');
        $labs_code          = $fn->getSettingsValueByKey("nextLabsCode");

        $supplierName = "
        SELECT title
        FROM labs_supplier
        WHERE labs_supplier_id = {$supplier_id}
        ";
        $resultSupplier = $db->sql_query($supplierName);
        $rowSupplier    = $db->sql_fetchrow($resultSupplier);

        $fa = array();
        $fa['labs_code']         = $labs_code;
        $fa['labs_date']         = date('Y-m-d');
        $fa['title']             = $rowSupplier['title'];
        $fa['supplier_id']       = $supplier_id;
        $fa['status']            = 'new';
        $fa['supplier_category'] = $supplier_category;
        $fa['patient_visit_id']  = $patient_visit_id;
        $fa['patient_information_id']  = $patient_information_id;
        $fa['site_id']           = $cpSiteIdSession;
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['created_by']        = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'labs');
        $result = $db->sql_query($SQL);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextLabsCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     */
    function getEditLabsRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $validate->validateData('supplier_category', 'Please select category');
        $validate->validateData('supplier_id', 'Please select supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditLabsRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditLabsRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $labs_id            = $fn->getReqParam('labs_id');

        $fa = array();
        $fa['supplier_id']       = $supplier_id;
        $fa['supplier_category'] = $supplier_category;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE labs_id = {$labs_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'labs', $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddDoctorRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddDoctorRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $consultation_fees  = $fn->getPostParam('consultation_fees');
        $consultation_room  = $fn->getPostParam('consultation_room');
        $notes              = $fn->getPostParam('notes');
        $employee_id        = $fn->getReqParam('employee_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['notes']             = $notes;
        $fa['employee_id']       = $employee_id;
        $fa['patient_visit_id']  = $patient_visit_id;
        $fa['consultation_room'] = $consultation_room;
        $fa['consultation_fees'] = $consultation_fees;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddDoctorRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $employee_id = $fn->getPostParam('employee_id');
        $patient_visit_id   = $fn->getPostParam('patient_visit_id');

        $recCount = $fn->getRecordCount('employee_visit', "employee_id = '{$employee_id}' AND patient_visit_id = '{$patient_visit_id}'");
        $validate->validateData('employee_id', 'Please select Doctor/Nurse');

        if($recCount > 0){
            $validate->errorArray['employee_id']['name'] = "employee_id";
            $validate->errorArray['employee_id']['msg']  = "Doctor/Nurse already added";
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
    function getEditDoctorRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditDoctorRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $consultation_room  = $fn->getPostParam('consultation_room');
        $consultation_fees  = $fn->getPostParam('consultation_fees');
        $notes              = $fn->getPostParam('notes');
        $employee_id        = $fn->getReqParam('employee_id');
        $employee_visit_id   = $fn->getReqParam('employee_visit_id');

        $fa = array();
        $fa['notes']             = $notes;
        $fa['employee_id']       = $employee_id;
        $fa['consultation_room'] = $consultation_room;
        $fa['consultation_fees'] = $consultation_fees;
        $fa['modification_date']    = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "
        WHERE employee_visit_id = {$employee_visit_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'employee_visit', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditDoctorRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddPatientRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddPatientRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $first_name       = $fn->getPostParam('first_name');
        $middle_name      = $fn->getPostParam('middle_name');
        $last_name        = $fn->getPostParam('last_name');
        $nric             = $fn->getPostParam('nric');
        $phone            = $fn->getPostParam('phone');
        $email            = $fn->getPostParam('email');
        $dob              = $fn->getPostParam('dob');
        $gender           = $fn->getPostParam('gender');
        $registration_no  = $fn->getPostParam('registration_no');
        $first_admit      = $fn->getPostParam('first_admit');
        $bill_type        = $fn->getPostParam('bill_type');
        $company_id       = $fn->getPostParam('company_id');
        $address_street   = $fn->getPostParam('address_street');
        $address_area     = $fn->getPostParam('address_area');
        $address_city     = $fn->getPostParam('address_city');
        $address_code     = $fn->getPostParam('address_code');
        $address_country  = $fn->getPostParam('address_country');
        $patient_code = $fn->getSettingsValueByKey("nextPatientCode");

        $fa = array();
        $fa['patient_code']    = $patient_code;
        $fa['first_name']      = strtoupper($first_name);
        $fa['middle_name']     = strtoupper($middle_name);
        $fa['last_name']       = strtoupper($last_name);
        $fa['nric']            = $nric;
        $fa['phone']           = $phone;
        $fa['email']           = $email;
        $fa['dob']             = $dob;
        $fa['gender']          = $gender;
        $fa['registration_no'] = $registration_no;
        $fa['first_admit']     = $first_admit;
        $fa['bill_type']       = $bill_type;
        $fa['company_id']      = $company_id;
        $fa['address_street']  = $address_street;
        $fa['address_area']    = $address_area;
        $fa['address_city']    = $address_city;
        $fa['address_code']    = $address_code;
        $fa['address_country'] = $address_country;
        $fa['creation_date']   = date("Y-m-d H:i:s");
        $fa['created_by']      = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_information');
        $result = $db->sql_query($SQL);

        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddPatientRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please Enter First Name');
        $validate->validateData('nric', 'Please Enter NRIC');
        $validate->validateData('bill_type', 'Please Select Bill Type');

        $nric = $fn->getPostParam('nric', '', true);
        $nric = str_replace('-', '', $nric);

        if ($nric != ''){
            $rec = $fn->getRecordByCondition('patient_information', "REPLACE(nric, '-', '') = '{$nric}'");
            $expNRIC = array('displayText' => 'click here', 'target' => '_blank');
            $NRIClink = $fn->getRecordDetailLink('hms_patientInformation', 'record_id', $rec['patient_information_id'], $expNRIC);

            if (is_array($rec)){
                $validate->errorArray['nric']['name'] = "nric";
                $validate->errorArray['nric']['msg']  = "NRIC already exist in system, please '{$NRIClink}'to check the detail";

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
    function getAddLabRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddLabRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $title            = $fn->getPostParam('title');
        $notes            = $fn->getPostParam('notes');
        $employee_id      = $fn->getReqParam('employee_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['title']            = $title;
        $fa['employee_id']      = $employee_id;
        $fa['patient_visit_id'] = $patient_visit_id;
        $fa['notes']            = $notes;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'lab_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddLabRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditLabRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditLabRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $title       = $fn->getPostParam('title');
        $notes = $fn->getPostParam('notes');
        $employee_id = $fn->getReqParam('employee_id');
        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $fa = array();
        $fa['title']            = $title;
        $fa['employee_id']      = $employee_id;
        $fa['notes']      = $notes;
        $fa['modification_date']    = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'lab_visit', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditLabRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getTreatmentRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getTreatmentRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $treatmentIds     = $fn->getPostParam('treatmentId', array());
        $status_arr       = $fn->getReqParam('treatment_status', array());
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $notes_arr        = $fn->getPostParam('notes', array());

        $treatVisitRec = $fn->getRecordByCondition('treatment_visit', "patient_visit_id = '{$patient_visit_id}'");

        if($treatVisitRec['treatment_visit_id'] != ''){
            /*$fa = array();
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']  = $fn->getSessionParam('userName');
            $fa['status']  = 'Old';

            $whereCondition = "
            WHERE patient_visit_id = {$patient_visit_id}
            ";
            $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'treatment_visit', $whereCondition);
            $db->sql_query($SQLInvoice);*/

            $SQLDelete = "DELETE FROM treatment_visit WHERE patient_visit_id = {$patient_visit_id}";
            $db->sql_query($SQLDelete);
        }
        $SQLDelete = "DELETE FROM follow_up_patient WHERE patient_visit_id = {$patient_visit_id} AND record_type = 'Treatment'";
        $db->sql_query($SQLDelete);

        $count = count($treatmentIds);
        for ($i= 0; $i < $count; $i++) {
            $treatment_id = $treatmentIds[$i];
            $treatment_id_explode = explode('_', $treatment_id);
            $status       = $status_arr[$treatment_id_explode[1]];
            $notes        = $notes_arr[$treatment_id_explode[1]];
            ${"fees_$treatment_id_explode[0]"."_arr"}  = $fn->getPostParam("fees_"."$treatment_id_explode[0]", array());
            $fees         = ${"fees_$treatment_id_explode[0]"."_arr"}[0];
            $future_date  = $fn->getPostParam("future_date_".$treatment_id_explode[0]);
            $future_value = $fn->getPostParam("future_value_".$treatment_id_explode[0]);

            if ($treatment_id) {
                $fa = array();
                $fa['treatment_id']     = $treatment_id_explode[0];
                $fa['status']           = $status;
                $fa['fees']             = $fees;
                $fa['notes']            = $notes;
                $fa['follow_up_date']   = $future_date;
                $fa['follow_up_value']  = $future_value;
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $fa['created_by']       = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'treatment_visit');
                $result = $db->sql_query($SQL);

                if ($future_date != '') {
                    $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
                    $employeeVisitRec = $fn->getRecordRowByID('employee_visit', 'patient_visit_id', $patient_visit_id);

                    $fa1 = array();
                    $fa1['description']      = $notes;
                    $fa1['record_type']      = 'Treatment';
                    $fa1['follow_up_date']   = $future_date;
                    $fa1['follow_up_time']   = date("H:i:s");
                    $fa1['patient_visit_id'] = $patient_visit_id;
                    $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
                    $fa1['employee_id'] = $employeeVisitRec['employee_id'];
                    $fa1['created_by']       = $fn->getSessionParam('userName');
                    $fa1['creation_date']    = date("Y-m-d H:i:s");

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'follow_up_patient');
                    $result = $db->sql_query($SQL);
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getTreatmentRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getLabRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getLabRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $titles     = $fn->getPostParam('title', array());
        $fees_arr         = $fn->getPostParam('fees', array());
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $notes_arr        = $fn->getPostParam('notes', array());

        $labRec = $fn->getRecordByCondition('lab_visit', "patient_visit_id = '{$patient_visit_id}'");
        if($labRec['lab_visit_id'] != ''){
            $SQLDelete = "DELETE FROM lab_visit WHERE patient_visit_id = {$patient_visit_id}";
            $db->sql_query($SQLDelete);
        }

        $count = count($titles);
        for ($i= 0; $i < $count; $i++) {
            $title = $titles[$i];
            $title_explode = explode('_', $title);
            $fees     = $fees_arr[$title_explode[1]];
            $notes         = $notes_arr[$title_explode[1]];

            if ($title) {
                $fa = array();
                $fa['title']     = $title_explode[0];
                $fa['fees']             = $fees;
                $fa['status']           = 'Current';
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $fa['created_by']       = $fn->getSessionParam('userName');
                $fa['notes']            = $notes;

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'lab_visit');
                $result = $db->sql_query($SQL);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getLabRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getMedicalHistorySubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getMedicalHistoryValidate()){
            return $validate->getErrorMessageXML();
        }

        $title_arr       = $fn->getPostParam('title', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');
        $allergies = $fn->getPostParam('allergies');

        $SQL = "
        SELECT m.title
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $oddTitle=array_diff($dataArray,$title_arr);

        foreach($oddTitle as $valueTitle){
            $SQLDelete="DELETE FROM medical_history_information WHERE title='{$valueTitle}' AND patient_visit_id = {$patient_visit_id}";
            $resultDelete = $db->sql_query($SQLDelete);

            $date = date('Y-m-d');
            $SQLUpdate ="
            UPDATE medical_his_information_history
            SET end_date='{$date}'
            WHERE patient_visit_id = {$patient_visit_id}
              AND title='{$valueTitle}'
              AND end_date IS NULL
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        foreach($title_arr as $value){
            $medHisRec = $fn->getRecordByCondition('medical_history_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}'");
            if($medHisRec['medical_history_information_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['status'] = 'Current';
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by'] = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_history_information');
                $result = $db->sql_query($SQL);
                $medical_history_information_id = $db->sql_nextid();
            }

            $medInfoHisRec = $fn->getRecordByCondition('medical_his_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}' AND end_date IS NULL");
            if($medInfoHisRec['medical_his_information_history_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['start_date'] = date("Y-m-d");
                $fa['medical_history_information_id'] = $medical_history_information_id;

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_his_information_history');
                $result = $db->sql_query($SQL);
            }
        }

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $fa1 = array();
        $fa1['alergies']        = $allergies;
        $whereCondition = "
        WHERE patient_information_id = {$patientVisitRec['patient_information_id']}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa1, 'patient_information', $whereCondition);
        $db->sql_query($SQLInvoice);

        $fa2 = array();
        $fa2['other_medical_history'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicalHistoryValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getSummaryPortalSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getSummaryPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $complain = $fn->getPostParam('complain');
        $treatment_summary = $fn->getPostParam('treatment_summary');
        $past_medical_history = $fn->getPostParam('past_medical_history');

        $fa = array();
        $fa['complain']          = $complain;
        $fa['treatment_summary'] = $treatment_summary;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $fa1 = array();
        $fa1['past_medical_history'] = $past_medical_history;
        $whereCondition = "
        WHERE patient_information_id = {$patientVisitRec['patient_information_id']}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa1, 'patient_information', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSummaryPortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getMedicalCetificatePortalSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getMedicalCetificatePortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id        = $fn->getPostParam('patient_visit_id');
        $no_of_days              = $fn->getPostParam('no_of_days');
        $resume_duty_on          = $fn->getPostParam('resume_duty_on');
        $medical_certficate_date = $fn->getPostParam('medical_certficate_date');

        $fa = array();
        $fa['no_of_days']              = $no_of_days;
        $fa['resume_duty_on']          = $resume_duty_on;
        $fa['medical_certficate_date'] = $medical_certficate_date;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicalCetificatePortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getLabsSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getLabsValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $supplier_id = $fn->getPostParam('supplier_id');

        $fa = array();
        $fa['supplier_id'] = $supplier_id;

        $labsRec = $fn->getRecordRowByID('labs', 'patient_visit_id', $patient_visit_id);
        if($labsRec['labs_id'] != ''){
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']        = $fn->getSessionParam('userName');

            $whereCondition = "
            WHERE labs_id = {$labsRec['labs_id']}
            ";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'labs', $whereCondition);
            $db->sql_query($SQLUpdate);
        } else {
            $fa['labs_date']        = date("Y-m-d");
            $fa['patient_visit_id'] = $patient_visit_id;
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');

            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'labs');
            $result = $db->sql_query($SQL);
        }


        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getLabsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getOralHygienicySubmit1() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getOralHygienicyValidate()){
            return $validate->getErrorMessageXML();
        }

        $title_arr       = $fn->getPostParam('title', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');

        $oralHygRec = $fn->getRecordByCondition('oral_hygienic_visit', "patient_visit_id = '{$patient_visit_id}'");
        if($oralHygRec['oral_hygienic_visit_id'] != ''){
            $fa = array();
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']  = $fn->getSessionParam('userName');
            $fa['status']  = 'Old';

            $whereCondition = "
            WHERE patient_visit_id = {$patient_visit_id}
            ";
            $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'oral_hygienic_visit', $whereCondition);
            $db->sql_query($SQLInvoice);
        }

        foreach($title_arr as $value){
            $fa = array();
            $fa['title'] = $value;
            $fa['status'] = 'Current';
            $fa['patient_visit_id'] = $patient_visit_id;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['created_by'] = $fn->getSessionParam('userName');

            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'oral_hygienic_visit');
            $result = $db->sql_query($SQL);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getOralHygienicySubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getOralHygienicyValidate()){
            return $validate->getErrorMessageXML();
        }

        $title_arr       = $fn->getPostParam('title', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');

        $SQL = "
        SELECT o.title
        FROM oral_hygienic_visit o
        WHERE o.patient_visit_id = {$patient_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $oddTitle=array_diff($dataArray,$title_arr);

        foreach($oddTitle as $valueTitle){
            $SQLDelete="DELETE FROM oral_hygienic_visit WHERE title='{$valueTitle}' AND patient_visit_id = {$patient_visit_id}";
            $resultDelete = $db->sql_query($SQLDelete);

            $date = date('Y-m-d');
            $SQLUpdate ="
            UPDATE oral_hygienic_visit_history
            SET end_date='{$date}'
            WHERE patient_visit_id = {$patient_visit_id}
              AND title='{$valueTitle}'
              AND end_date IS NULL
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        foreach($title_arr as $value){
            $oralHygRec = $fn->getRecordByCondition('oral_hygienic_visit', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}'");
            if($oralHygRec['oral_hygienic_visit_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['others'] = $others;
                $fa['status'] = 'Current';
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by'] = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'oral_hygienic_visit');
                $result = $db->sql_query($SQL);
            }

            $oralHygHisRec = $fn->getRecordByCondition('oral_hygienic_visit_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}' AND end_date IS NULL");
            if($oralHygHisRec['oral_hygienic_visit_history_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['start_date'] = date("Y-m-d");

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'oral_hygienic_visit_history');
                $result = $db->sql_query($SQL);
            }
        }

        $fa2 = array();
        $fa2['other_oral_hygienic'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getOralHygienicyValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getHabitsSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getHabitsValidate()){
            return $validate->getErrorMessageXML();
        }

        $title_arr       = $fn->getPostParam('title', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');

        $SQL = "
        SELECT h.title
        FROM habits_information h
        WHERE h.patient_visit_id = {$patient_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $oddTitle=array_diff($dataArray,$title_arr);

        foreach($oddTitle as $valueTitle){
            $SQLDelete="DELETE FROM habits_information WHERE title='{$valueTitle}' AND patient_visit_id = {$patient_visit_id}";
            $resultDelete = $db->sql_query($SQLDelete);

            $date = date('Y-m-d');
            $SQLUpdate ="
            UPDATE habits_information_history
            SET end_date='{$date}'
            WHERE patient_visit_id = {$patient_visit_id}
              AND title='{$valueTitle}'
              AND end_date IS NULL
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        foreach($title_arr as $value){
            $habInfoRec = $fn->getRecordByCondition('habits_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}'");
            if($habInfoRec['habits_information_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['status'] = 'Current';
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by'] = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'habits_information');
                $result = $db->sql_query($SQL);
            }

            $habInfoHisRec = $fn->getRecordByCondition('habits_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}' AND end_date IS NULL");
            if($habInfoHisRec['habits_information_history_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['start_date'] = date("Y-m-d");

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'habits_information_history');
                $result = $db->sql_query($SQL);
            }
        }

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $fa1 = array();
        $fa1['other_habits_history']  = $others;
        $whereCondition = "
        WHERE patient_information_id = {$patientVisitRec['patient_information_id']}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa1, 'patient_information', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getHabitsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getIntraOralExamSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getIntraOralExamValidate()){
            return $validate->getErrorMessageXML();
        }

        $remarks_arr     = $fn->getPostParam('remarks', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');
        $count = $fn->getPostParam('count');

        for($i=0;$i<$count;$i++){
            $title_arr = $fn->getPostParam('title_'.$i, array());

            foreach($title_arr as $value){
                $value_explode = explode('_', $value);
                $title = $value_explode[0];
                $title_status = $value_explode[1];
                $remarks = $remarks_arr[$value_explode[2]];

                $intraOralRec = $fn->getRecordByCondition('intra_oral_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}'");
                if($intraOralRec['intra_oral_information_id'] == ''){
                    $fa = array();
                    $fa['title'] = $title;
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['created_by'] = $fn->getSessionParam('userName');

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'intra_oral_information');
                    $result = $db->sql_query($SQL);
                    $intra_oral_information_id = $db->sql_nextid();
                } else {
                    $fa = array();
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['modification_date'] = date("Y-m-d H:i:s");
                    $fa['modified_by'] = $fn->getSessionParam('userName');

                    $whereCondition = "
                    WHERE patient_visit_id = {$patient_visit_id} AND title = '{$title}'
                    ";
                    $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'intra_oral_information', $whereCondition);
                    $db->sql_query($SQLUpdate);
                    $intra_oral_information_id = $db->sql_nextid();
                }

                $intraOralHisRec = $fn->getRecordByCondition('intra_oral_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}' AND status = '{$title_status}' AND end_date IS NULL");
                if($intraOralHisRec['intra_oral_information_history_id'] == ''){
                    $date = date('Y-m-d');
                    $SQLUpdate ="
                    UPDATE intra_oral_information_history
                    SET end_date='{$date}'
                    WHERE patient_visit_id = {$patient_visit_id}
                      AND title='{$title}'
                      AND end_date IS NULL
                    ";
                    $resultUpdate = $db->sql_query($SQLUpdate);

                    $fa = array();
                    $fa['title'] = $title;
                    $fa['status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['intra_oral_information_id'] = $intra_oral_information_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['start_date'] = date("Y-m-d");

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'intra_oral_information_history');
                    $result = $db->sql_query($SQL);
                }
            }
        }

        $fa2 = array();
        $fa2['other_intra_oral_exam'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getIntraOralExamValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getExtraOralExamSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getExtraOralExamValidate()){
            return $validate->getErrorMessageXML();
        }

        $remarks_arr     = $fn->getPostParam('remarks', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');
        $count = $fn->getPostParam('count');

        for($i=0;$i<$count;$i++){
            $title_arr = $fn->getPostParam('title_'.$i, array());

            foreach($title_arr as $value){
                $value_explode = explode('_', $value);
                $title = $value_explode[0];
                $title_status = $value_explode[1];
                $remarks = $remarks_arr[$value_explode[2]];

                $extraOralRec = $fn->getRecordByCondition('extra_oral_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}'");
                if($extraOralRec['extra_oral_information_id'] == ''){
                    $fa = array();
                    $fa['title'] = $title;
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['status'] = 'Current';
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['created_by'] = $fn->getSessionParam('userName');

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'extra_oral_information');
                    $result = $db->sql_query($SQL);
                    $extra_oral_information_id = $db->sql_nextid();
                } else {
                    $fa = array();
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['modification_date'] = date("Y-m-d H:i:s");
                    $fa['modified_by'] = $fn->getSessionParam('userName');

                    $whereCondition = "
                    WHERE patient_visit_id = {$patient_visit_id} AND title = '{$title}'
                    ";
                    $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'extra_oral_information', $whereCondition);
                    $db->sql_query($SQLUpdate);
                    $extra_oral_information_id = $db->sql_nextid();
                }

                $extraOralHisRec = $fn->getRecordByCondition('extra_oral_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}' AND status = '{$title_status}' AND end_date IS NULL");
                if($extraOralHisRec['extra_oral_information_history_id'] == ''){
                    $date = date('Y-m-d');
                    $SQLUpdate ="
                    UPDATE extra_oral_information_history
                    SET end_date='{$date}'
                    WHERE patient_visit_id = {$patient_visit_id}
                      AND title='{$title}'
                      AND end_date IS NULL
                    ";
                    $resultUpdate = $db->sql_query($SQLUpdate);

                    $fa = array();
                    $fa['title'] = $title;
                    $fa['status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['extra_oral_information_id'] = $extra_oral_information_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['start_date'] = date("Y-m-d");

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'extra_oral_information_history');
                    $result = $db->sql_query($SQL);
                }
            }
        }

        $fa2 = array();
        $fa2['other_extra_oral_exam'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getExtraOralExamValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPeridontiumSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPeridontiumValidate()){
            return $validate->getErrorMessageXML();
        }

        $remarks_arr     = $fn->getPostParam('remarks', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');
        $count = $fn->getPostParam('count');

        for($i=0;$i<$count;$i++){
            $title_arr = $fn->getPostParam('title_'.$i, array());

            foreach($title_arr as $value){
                $value_explode = explode('_', $value);
                $title = $value_explode[0];
                $title_status = $value_explode[1];
                $remarks = $remarks_arr[$value_explode[2]];

                $peridontiumRec = $fn->getRecordByCondition('peridontium_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}'");
                if($peridontiumRec['peridontium_information_id'] == ''){
                    $fa = array();
                    $fa['title'] = $title;
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['status'] = 'Current';
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['created_by'] = $fn->getSessionParam('userName');

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'peridontium_information');
                    $result = $db->sql_query($SQL);
                    $peridontium_information_id = $db->sql_nextid();
                } else {
                    $fa = array();
                    $fa['title_status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['modification_date'] = date("Y-m-d H:i:s");
                    $fa['modified_by'] = $fn->getSessionParam('userName');

                    $whereCondition = "
                    WHERE patient_visit_id = {$patient_visit_id} AND title = '{$title}'
                    ";
                    $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'peridontium_information', $whereCondition);
                    $db->sql_query($SQLUpdate);
                    $peridontium_information_id = $db->sql_nextid();
                }

                $peridontiumHisRec = $fn->getRecordByCondition('peridontium_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$title}' AND status = '{$title_status}' AND end_date IS NULL");
                if($peridontiumHisRec['peridontium_information_history_id'] == ''){
                    $date = date('Y-m-d');
                    $SQLUpdate ="
                    UPDATE peridontium_information_history
                    SET end_date='{$date}'
                    WHERE patient_visit_id = {$patient_visit_id}
                      AND title='{$title}'
                      AND end_date IS NULL
                    ";
                    $resultUpdate = $db->sql_query($SQLUpdate);

                    $fa = array();
                    $fa['title'] = $title;
                    $fa['status'] = $title_status;
                    $fa['remarks'] = $remarks;
                    $fa['patient_visit_id'] = $patient_visit_id;
                    $fa['peridontium_information_id'] = $peridontium_information_id;
                    $fa['creation_date'] = date("Y-m-d H:i:s");
                    $fa['start_date'] = date("Y-m-d");

                    $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'peridontium_information_history');
                    $result = $db->sql_query($SQL);
                }
            }
        }
        $fa2 = array();
        $fa2['other_peridontium'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPeridontiumValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDiagnosisRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getDiagnosisRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $diagnosisIds     = $fn->getPostParam('diagnosisId', array());
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $diagnosisVisitRec = $fn->getRecordByCondition('diagnosis_visit', "patient_visit_id = '{$patient_visit_id}'");

        if($diagnosisVisitRec['diagnosis_visit_id'] != ''){
            $SQLDelete = "DELETE FROM diagnosis_visit WHERE patient_visit_id = {$patient_visit_id}";
            $db->sql_query($SQLDelete);
        }

        $count = count($diagnosisIds);
        for ($i= 0; $i < $count; $i++) {
            $diagnosis_id = $diagnosisIds[$i];
            $diagnosis_id_explode = explode('_', $diagnosis_id);

            if ($diagnosis_id) {
                $fa = array();
                $fa['diagnosis_id']     = $diagnosis_id_explode[0];
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $fa['created_by']       = $fn->getSessionParam('userName');
                $fa['status']  = 'Current';

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'diagnosis_visit');
                $result = $db->sql_query($SQL);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDiagnosisRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getCreateVisitRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please select Dr/Nurse');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getCreateVisitRecordSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getCreateVisitRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id  = $fn->getPostParam('patient_information_id');
        $employee_id             = $fn->getPostParam('employee_id');
        $appointment_id          = $fn->getPostParam('appointment_id');
        $visit_code              = $fn->getSettingsValueByKey("nextPatientvisitCode");
        $cpSiteIdSession         = $fn->getSessionParam('cp_site_id');
        $currentDate  = date("Y-m-d");
        $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id',  $patient_information_id);
        
        $fa = array();

        if($patientInfoRec['bill_type'] == ''){
            $patientInfoRec['bill_type'] = 'Individual';
        }

        if($patientInfoRec['bill_type'] == 'Company' || $patientInfoRec['bill_type'] == 'Panel'){
            $sqlCompany = "
            SELECT company_id
                  ,company_name
                  ,address_flat
                  ,address_street
                  ,address_town
                  ,address_state
                  ,address_country
                  ,phone
            FROM company
            WHERE category = '{$patientInfoRec['bill_type']}'
            AND company_id = {$patientInfoRec['company_id']}
            ORDER BY company_name
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $fa['company_name']           = $rowCompany['company_name'];
            $fa['address_flat']           = $rowCompany['address_flat'];
            $fa['address_street']         = $rowCompany['address_street'];
            $fa['address_town']           = $rowCompany['address_town'];
            $fa['address_state']          = $rowCompany['address_state'];
            $fa['address_country']        = $rowCompany['address_country'];
            $fa['phone']                  = $rowCompany['phone'];
            $fa['company_id']             = $rowCompany['company_id'];
        }

        $fa['patient_information_id'] = $patient_information_id;
        $fa['bill_type']              = $patientInfoRec['bill_type'];
        $fa['status']                 = 'New';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");
        $fa['visit_code']             = $visit_code;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $fa['site_id'] = $cpSiteIdSession;
        }

        if($appointment_id != ''){
            $fa['appointment_id']         = $appointment_id;
            $fa['record_type']            = 'By Appointment';
        } else {
            $fa['record_type']            = 'Walk In';
        }

        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update patient visit code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $rowEmployee = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);

        $fa2['consultation_fees'] = $rowEmployee['consultation_fees'];
        $fa2['employee_id']       = $rowEmployee['employee_id'];
        $fa2['patient_visit_id']  = $patient_visit_id;
        $fa2['creation_date']     = date("Y-m-d H:i:s");
        $fa2['created_by']        = $fn->getSessionParam('userName');

        $insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_visit');
        $resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);

        $SQL = "
        SELECT MAX(pq.queue_no) AS queue_no
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.check_up_date = '{$currentDate}'
        AND pv.employee_id = {$employee_id}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $queue_no = $row['queue_no'];

        if($queue_no == ''){
            $queue_no = 1;
        }else{
            $queue_no = $row['queue_no'] + 1;
        }

        $fa1 = array();

        $fa1['patient_information_id'] = $patient_information_id;
        $fa1['check_up_date']          = date("Y-m-d");
        $fa1['check_up_time']          = date("H:i:s");
        $fa1['queue_no']               = $queue_no;
        $fa1['patient_visit_id']       = $patient_visit_id;
        $fa1['creation_date']          = date("Y-m-d H:i:s");
        $fa1['created_by']             = $fn->getSessionParam('userName');

        $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'patient_queue');
        $resultQueueSQL = $db->sql_query($insertQueueSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCreateVisitRecordDirectValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getCreateVisitRecordDirect(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getCreateVisitRecordDirectValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id  = $fn->getReqParam('patient_information_id');
        $employee_id             = $fn->getReqParam('dr_required');
        $appointment_id          = $fn->getReqParam('appointment_id');
        $visit_code              = $fn->getSettingsValueByKey("nextPatientvisitCode");
        $cpSiteIdSession         = $fn->getSessionParam('cp_site_id');

        $currentDate  = date("Y-m-d");

        $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id',  $patient_information_id);
        $fa = array();

        if($patientInfoRec['bill_type'] == ''){
            $patientInfoRec['bill_type'] = 'Individual';
        }
        
        if($patientInfoRec['bill_type'] == 'Company' || $patientInfoRec['bill_type'] == 'Panel'){
            $sqlCompany = "
            SELECT company_id
                  ,company_name
                  ,address_flat
                  ,address_street
                  ,address_town
                  ,address_state
                  ,address_country
                  ,phone
            FROM company
            WHERE category = '{$patientInfoRec['bill_type']}'
            AND company_id = {$patientInfoRec['company_id']}
            ORDER BY company_name
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $fa['company_name']           = $rowCompany['company_name'];
            $fa['address_flat']           = $rowCompany['address_flat'];
            $fa['address_street']         = $rowCompany['address_street'];
            $fa['address_town']           = $rowCompany['address_town'];
            $fa['address_state']          = $rowCompany['address_state'];
            $fa['address_country']        = $rowCompany['address_country'];
            $fa['phone']                  = $rowCompany['phone'];
            $fa['company_id']             = $rowCompany['company_id'];
        }

        $fa['patient_information_id'] = $patient_information_id;
        $fa['bill_type']              = $patientInfoRec['bill_type'];
        $fa['status']                 = 'New';
        $fa['record_type']            = 'Walk In';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");
        $fa['site_id']                = $cpSiteIdSession ;

        if($appointment_id != ''){
          $fa['appointment_id']      =  $appointment_id;
        }
        $fa['visit_code']             = $visit_code;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update patient visit code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $rowEmployee = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);

        $fa2['consultation_fees'] = $rowEmployee['consultation_fees'];
        $fa2['employee_id']       = $rowEmployee['employee_id'];
        $fa2['patient_visit_id']  = $patient_visit_id;
        $fa2['creation_date']     = date("Y-m-d H:i:s");
        $fa2['created_by']        = $fn->getSessionParam('userName');

        $insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_visit');
        $resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);

        $SQL = "
        SELECT MAX(pq.queue_no) AS queue_no
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.check_up_date = '{$currentDate}'
        AND pv.employee_id = {$employee_id}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $queue_no = $row['queue_no'];

        if($queue_no == ''){
            $queue_no = 1;
        }else{
            $queue_no = $row['queue_no'] + 1;
        }

        $fa1 = array();

        $fa1['patient_information_id'] = $patient_information_id;
        $fa1['check_up_date']          = date("Y-m-d");
        $fa1['check_up_time']          = date("H:i:s");
        $fa1['queue_no']               = $queue_no;
        $fa1['site_id']                = $cpSiteIdSession ;
        $fa1['patient_visit_id']       = $patient_visit_id;
        $fa1['creation_date']          = date("Y-m-d H:i:s");
        $fa1['created_by']             = $fn->getSessionParam('userName');

        $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'patient_queue');
        $resultQueueSQL = $db->sql_query($insertQueueSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddPerioChartRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $patient_visit_id = $fn->getReqParam('patient_visit_id');
      $tooth_id         = $fn->getReqParam('tooth_id');
      $symbol_name      = $fn->getReqParam('symbol_name');
      $tooth_form_type  = $fn->getReqParam('tooth_form_type');
      $labs_id          = $fn->getReqParam('labs_id');
      $tooth_part       = $fn->getReqParam('tooth_part');

      $SQLPerio = "
      SELECT tooth_id
            ,patient_visit_id
      FROM visit_perio_chart
      WHERE tooth_id = '{$tooth_id}'
      AND patient_visit_id = {$patient_visit_id}
      AND category = '{$tooth_form_type}'
      ";
      $result = $db->sql_query($SQLPerio);
      $numRows = $db->sql_numrows($result);

      $fa = array();

      $fa['patient_visit_id'] = $patient_visit_id;
      $fa['tooth_id']         = $tooth_id;
      $fa['symbol']           = $symbol_name;
      $fa['category']         = $tooth_form_type;
      $fa['labs_id']          = $labs_id;

      if($tooth_part == 'Top'){
           $fa['tooth_top']   = 1;
      }

      if($tooth_part == 'Left'){
           $fa['tooth_left']   = 1;
      }

      if($tooth_part == 'Right'){
           $fa['tooth_right']   = 1;
      }

      if($tooth_part == 'Center'){
           $fa['tooth_center']   = 1;
      }

      if($tooth_part == 'Bottom'){
           $fa['tooth_bottom']   = 1;
      }

      if($numRows >0){
        $fa['modified_by']       = $fn->getSessionParam('userName');
        $fa['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE tooth_id = '{$tooth_id}' AND patient_visit_id = {$patient_visit_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'visit_perio_chart', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);
      }
      else{
        $fa['date']             = date("Y-m-d");
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'visit_perio_chart');
        $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

    }

    /**
     *
     */
    function getAddAcrylicDentureFormRecord(){
      $fn = Zend_Registry::get('fn');
      $validate = Zend_Registry::get('validate');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      /*if (!$this->getLabsValidate()){
          return $validate->getErrorMessageXML();
      }*/

      $labs_id          = $fn->getReqParam('labs_id');
      $dentureTypeArr   = $fn->getPostParam('denture_type', array());
      $date_received    = $fn->getPostParam('date_received');
      $date_sent        = $fn->getPostParam('date_sent');
      $date_due         = $fn->getPostParam('date_due');
      $category         = $fn->getPostParam('form_type');
      $amount           = $fn->getPostParam('amount');

      $SQLDeleteDenture = "
      DELETE FROM labs_history
      WHERE labs_id = '{$labs_id}'
      AND category = '{$category}'
      ";
      $resultDeleteDenture = $db->sql_query($SQLDeleteDenture);

      foreach($dentureTypeArr as $value){
          $SQLDenture = "
          SELECT labs_id
          FROM labs_history
          WHERE labs_id = '{$labs_id}'
          AND title = '{$value}'
          AND category = '{$category}'
          ";
          $resultDenture = $db->sql_query($SQLDenture);
          $numRows = $db->sql_numrows($resultDenture);

          $fa = array();
          $fa['title']     = $value;
          $fa['labs_id']   = $labs_id;
          $fa['category']  = $category;
          $fa['creation_date']    = date("Y-m-d H:i:s");
          $fa['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

        $fa1 = array();
        $fa1['date_received']     = $date_received;
        $fa1['amount']            = $amount;
        $fa1['date_sent']         = $date_sent;
        $fa1['date_due']          = $date_due;
        $fa1['modified_by']       = $fn->getSessionParam('userName');
        $fa1['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE labs_id = {$labs_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'labs', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);

      return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getAddCeramicFormRecord(){
      $fn = Zend_Registry::get('fn');
      $validate = Zend_Registry::get('validate');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      /*if (!$this->getLabsValidate()){
          return $validate->getErrorMessageXML();
      }*/

      $labs_id              = $fn->getReqParam('labs_id');
      $title_porcelain      = $fn->getReqParam('title_group_porcelain');
      $title_fullporcelain  = $fn->getReqParam('title_group_fullporcelain');
      $title_full_Metal     = $fn->getReqParam('title_group_full_Metal');
      $title_group_Margin   = $fn->getReqParam('title_group_Margin');
      $title_group_Pontic   = $fn->getReqParam('title_group_Pontic');
      $title_group_Proximal = $fn->getReqParam('title_group_Proximal');
      $porcelainArr         = $fn->getPostParam('porcelain_bonded', array());
      $fullPorcelainArr     = $fn->getPostParam('full_porcelain', array());
      $fullMetalArr         = $fn->getPostParam('full_Metal', array());
      $marginArr            = $fn->getPostParam('Margin_Porcelain', array());
      $ponticArr            = $fn->getPostParam('pontic', array());
      $proximalArr          = $fn->getPostParam('proximal', array());
      $date_received        = $fn->getPostParam('date_received');
      $date_sent            = $fn->getPostParam('date_sent');
      $date_due             = $fn->getPostParam('date_due');
      $form_type            = $fn->getPostParam('form_type');
      $amount               = $fn->getPostParam('amount');

      $SQLDeleteCeramic = "
      DELETE FROM labs_history
      WHERE labs_id = '{$labs_id}'
      AND form_type = '{$form_type}'
      ";
      $resultDeleteCeramic = $db->sql_query($SQLDeleteCeramic);

      foreach($porcelainArr as $value){
          $fa = array();
          $fa['title']            = $value;
          $fa['labs_id']          = $labs_id;
          $fa['category']         = 'TYPE OF WORK';
          $fa['form_type']        = $form_type;
          $fa['title_group']      = $title_porcelain;
          $fa['creation_date']    = date("Y-m-d H:i:s");
          $fa['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

      foreach($fullPorcelainArr as $value){
          $fa2 = array();
          $fa2['title']            = $value;
          $fa2['labs_id']          = $labs_id;
          $fa2['category']         = 'TYPE OF WORK';
          $fa2['form_type']        = $form_type;
          $fa2['title_group']      = $title_fullporcelain;
          $fa2['creation_date']    = date("Y-m-d H:i:s");
          $fa2['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

      foreach($fullMetalArr as $value){
          $fa3 = array();
          $fa3['title']            = $value;
          $fa3['labs_id']          = $labs_id;
          $fa3['category']         = 'TYPE OF WORK';
          $fa3['form_type']        = $form_type;
          $fa3['title_group']      = $title_full_Metal;
          $fa3['creation_date']    = date("Y-m-d H:i:s");
          $fa3['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

      foreach($marginArr as $value){
          $fa4 = array();
          $fa4['title']            = $value;
          $fa4['labs_id']          = $labs_id;
          $fa4['form_type']        = $form_type;
          $fa4['title_group']      = $title_group_Margin;
          $fa4['creation_date']    = date("Y-m-d H:i:s");
          $fa4['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa4, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

      foreach($ponticArr as $value){
          $fa5 = array();
          $fa5['title']            = $value;
          $fa5['labs_id']          = $labs_id;
          $fa5['form_type']        = $form_type;
          $fa5['title_group']      = $title_group_Pontic;
          $fa5['creation_date']    = date("Y-m-d H:i:s");
          $fa5['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa5, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

      foreach($proximalArr as $value){
          $fa6 = array();
          $fa6['title']            = $value;
          $fa6['labs_id']          = $labs_id;
          $fa6['form_type']        = $form_type;
          $fa6['title_group']      = $title_group_Proximal;
          $fa6['creation_date']    = date("Y-m-d H:i:s");
          $fa6['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa6, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

        $fa1 = array();
        $fa1['date_received']     = $date_received;
        $fa1['date_sent']         = $date_sent;
        $fa1['date_due']          = $date_due;
        $fa1['amount']            = $amount;
        $fa1['modified_by']       = $fn->getSessionParam('userName');
        $fa1['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE labs_id = {$labs_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'labs', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);

      return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getAddChromeFormRecord(){
      $fn = Zend_Registry::get('fn');
      $validate = Zend_Registry::get('validate');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $labs_id              = $fn->getReqParam('labs_id');
      $chrome_typeArr       = $fn->getPostParam('chrome_type', array());
      $title_group_psv      = $fn->getReqParam('title_group_psv');
      $date_received        = $fn->getPostParam('date_received');
      $date_sent            = $fn->getPostParam('date_sent');
      $date_due             = $fn->getPostParam('date_due');
      $form_type            = $fn->getPostParam('form_type');
      $amount               = $fn->getPostParam('amount');

      $SQLDeleteChrome = "
      DELETE FROM labs_history
      WHERE labs_id = '{$labs_id}'
      AND form_type = '{$form_type}'
      ";
      $resultDeleteChrome = $db->sql_query($SQLDeleteChrome);

      foreach($chrome_typeArr as $value){
          $fa = array();
          $fa['title']            = $value;
          $fa['labs_id']          = $labs_id;
          $fa['form_type']        = $form_type;
          $fa['title_group']      = $title_group_psv;
          $fa['creation_date']    = date("Y-m-d H:i:s");
          $fa['created_by']       = $fn->getSessionParam('userName');

          $insertQueueSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_history');
          $resultQueueSQL = $db->sql_query($insertQueueSQL);
      }

        $fa1 = array();
        $fa1['date_received']     = $date_received;
        $fa1['date_sent']         = $date_sent;
        $fa1['date_due']          = $date_due;
        $fa1['amount']            = $amount;
        $fa1['modified_by']       = $fn->getSessionParam('userName');
        $fa1['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE labs_id = {$labs_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'labs', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);

      return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getDeleteDoctorRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $employee_visit_id = $fn->getReqParam('employee_visit_id');

      $SQL = "
      DELETE FROM employee_visit
      WHERE employee_visit_id = {$employee_visit_id}
      ";
      $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getDeleteLabsRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $labs_id = $fn->getReqParam('labs_id');

      $SQLLabs = "
      DELETE FROM labs
      WHERE labs_id = {$labs_id}
      ";
      $resultLabs = $db->sql_query($SQLLabs);

      $SQLLabsHistory = "
      DELETE FROM labs_history
      WHERE labs_id = {$labs_id}
      ";
      $resultLabsHistory = $db->sql_query($SQLLabsHistory);

      $SQLVisitPerio = "
      DELETE FROM visit_perio_chart
      WHERE labs_id = {$labs_id}
      ";
      $resultVisitPerio = $db->sql_query($SQLVisitPerio);
    }

    /**
     *
     */
    function getDeleteMedicineRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $medicines_visit_id = $fn->getReqParam('medicines_visit_id');

      $SQL = "
      DELETE FROM medicines_visit
      WHERE medicines_visit_id = {$medicines_visit_id}
      ";
      $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getDeletePerioChartRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $patient_visit_id = $fn->getReqParam('patient_visit_id');
      $tooth_id         = $fn->getReqParam('tooth_id');
      $category         = $fn->getReqParam('tooth_form_type');
      $labs_id          = $fn->getReqParam('labs_id');
      $tooth_part       = $fn->getReqParam('tooth_part');

      $appendSql = '';
      if($labs_id != ''){
        $appendSql = "AND labs_id = '{$labs_id}'";
      }


      $SQLPerioCheck = "
      SELECT tooth_id
             ,symbol
             ,tooth_top
             ,tooth_left
             ,tooth_right
             ,tooth_bottom
             ,tooth_center
             ,symbol
      FROM visit_perio_chart
      WHERE patient_visit_id = {$patient_visit_id}
      AND category = '{$category}'
      AND tooth_id = '{$tooth_id}'
      {$appendSql}
      ";
      $resultPerioCheck = $db->sql_query($SQLPerioCheck);
      $rowPerioCheck = $db->sql_fetchrow($resultPerioCheck);

      $checkTheToothPartSelected = $rowPerioCheck['tooth_top'] + $rowPerioCheck['tooth_left'] + $rowPerioCheck['tooth_right'] + $rowPerioCheck['tooth_bottom'] + $rowPerioCheck['tooth_center'];

          if($checkTheToothPartSelected > 0){
              if($tooth_part == 'Top'){
                   $fa['tooth_top']   = 0;
              }

              if($tooth_part == 'Left'){
                   $fa['tooth_left']   = 0;
              }

              if($tooth_part == 'Right'){
                   $fa['tooth_right']   = 0;
              }

              if($tooth_part == 'Center'){
                   $fa['tooth_center']   = 0;
              }

              if($tooth_part == 'Bottom'){
                   $fa['tooth_bottom']   = 0;
              }

              $fa['modified_by']       = $fn->getSessionParam('userName');
              $fa['modification_date'] = date("Y-m-d H:i:s");  
              $whereCondition = "WHERE tooth_id = '{$tooth_id}' AND patient_visit_id = {$patient_visit_id}";
              $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'visit_perio_chart', $whereCondition);
              $resultSQL =$db->sql_query($updateSQL);

                    $SQLPerioCheck1 = "
                    SELECT tooth_id
                    FROM visit_perio_chart
                    WHERE patient_visit_id = {$patient_visit_id}
                    AND category = '{$category}'
                    AND tooth_id = '{$tooth_id}'
                    {$appendSql}
                    ";
                    $resultPerioCheck1 = $db->sql_query($SQLPerioCheck1);
                    $rowPerioCheck1 = $db->sql_fetchrow($resultPerioCheck1);

                    $checkTheToothPartSelected1 = $rowPerioCheck1['tooth_top'] + $rowPerioCheck1['tooth_left'] + $rowPerioCheck1['tooth_right'] + $rowPerioCheck1['tooth_bottom'] + $rowPerioCheck1['tooth_center'];

                    if($checkTheToothPartSelected1 == 0){
                        $SQLPerio = "
                        DELETE FROM visit_perio_chart
                        WHERE tooth_id = '{$tooth_id}'
                        AND patient_visit_id = {$patient_visit_id}
                        AND category = '{$category}'
                        {$appendSql}
                        ";
                        $result = $db->sql_query($SQLPerio);
                    }

          }else{
              $SQLPerio = "
              DELETE FROM visit_perio_chart
              WHERE tooth_id = '{$tooth_id}'
              AND patient_visit_id = {$patient_visit_id}
              AND category = '{$category}'
              {$appendSql}
              ";
              $result = $db->sql_query($SQLPerio);
          }
    }

    /**
     *
     */
    function getAddMedicine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $patient_visit_id= $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['qty'] = 0;
        $fa['patient_visit_id'] = $patient_visit_id;
        $id = $fn->addRecord($fa, 'medicines_visit');
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title, p.price) AS label
        FROM product p
        LEFT JOIN po_product pp ON (pp.product_id = p.product_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pp.purchase_order_id)
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        AND p.published = 1
        AND pp.qty > 0
        GROUP BY pp.product_id
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
     function getUpdateProductLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $rec_id = $fn->getReqParam('rec_id');
        $id = $tv['srcRoomId'];
        $instruction = $fn->getReqParam('instruction');
        $days = $fn->getReqParam('days');
        $dosage = $fn->getReqParam('dosage');
        $qty = $fn->getReqParam('qty');
        $employee_id = $fn->getReqParam('employee_id');
        $selling_price = $fn->getReqParam('selling_price');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $arr = array();
        $arr['msg'] = '';

        $SQL    = "
        SELECT   p.title
                ,(SELECT price
                  FROM product_price pp
                  WHERE pp.product_id = p.product_id
                  AND pp.site_id = {$cpSiteIdSession}) AS product_price
                ,po.price AS PO_price
                ,po.dosage
        FROM product p
        LEFT JOIN po_product po ON (po.product_id = p.product_id)
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if($selling_price == '' || $selling_price == 0){
            $selling_price = $row['product_price'];
        }else {
            $selling_price = $fn->getReqParam('selling_price');
        }

        if($product_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set product_id = '{$product_id}'
            ,title = '{$row['title']}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($instruction != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set instruction = '{$instruction}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($days != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set days = '{$days}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($dosage != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set dosage = '{$dosage}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($selling_price != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set selling_price = '{$selling_price}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($qty != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set qty = '{$qty}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($employee_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set employee_id = '{$employee_id}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        $stock = 0;

        if($product_id != ''){
            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$product_id} AND st.from_location = {$cpSiteIdSession}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$product_id} AND st.to_location = {$cpSiteIdSession}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$product_id} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$product_id}
                  AND o.site_id = {$cpSiteIdSession}
                ) as product_qty_sold_from_quote

                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$product_id}
                AND inv.site_id = {$cpSiteIdSession}
                ) as sales_return_qty

                ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                  LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                  WHERE pp.product_id = {$product_id} AND po.site_id = {$cpSiteIdSession}
                 ) as damaged_qty
            ";
            $resultothersite = $db->sql_query($SQLOthersite);
            $rowothersite = $db->sql_fetchrow($resultothersite);


            $SqlExpenseProduct = "
            SELECT SUM(ep.qty) AS qty
            FROM expense_product ep
            LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
            WHERE ep.product_id = {$product_id}
            AND ep.status = 'Added'
            AND e.site_id = {$cpSiteIdSession}
            AND ep.stock_deducted = 1
            ";
            $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
            $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

            $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
        }

        $arr['sellingPrice'] = $selling_price;
        $arr['qty']          = 1;
        $arr['dosage']       = $row['dosage'];
        $arr['stock']        = $stock;
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getAddNoteTreatmentSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $treatment_visit_id  = $fn->getPostParam('treatment_visit_id');
        $notes         = $fn->getPostParam('notes');

        if (!$this->getAddNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['notes']     = $notes;

        $SQLUpdate    = "
        UPDATE treatment_visit
        set notes = '{$notes}'
        WHERE treatment_visit_id = {$treatment_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNoteLabSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $lab_visit_id  = $fn->getPostParam('lab_visit_id');
        $notes         = $fn->getPostParam('notes');

        if (!$this->getAddNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['notes']     = $notes;

        $SQLUpdate    = "
        UPDATE lab_visit
        set notes = '{$notes}'
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNoteFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getCreateOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $fa = array();

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);
        $patientRow = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientInfoRec['patient_information_id']);

        $fa['company_id']                    = $patientVisitRec['company_id'];
        $fa['company_name']                  = $patientVisitRec['company_name'];
        $fa['cust_address1']                 = $patientVisitRec['address_flat'];
        $fa['cust_address2']                 = $patientVisitRec['address_street'];
        $fa['cust_address_city']             = $patientVisitRec['address_town'];
        $fa['cust_address_state']            = $patientVisitRec['address_state'];
        $fa['cust_address_country_code']     = $patientVisitRec['address_country'];
        $fa['cust_phone']                    = $patientVisitRec['phone'];
        $fa['shipping_address1']             = $patientRow['address_street'];
        $fa['shipping_address_area']         = $patientRow['address_area'];
        $fa['shipping_address_city']         = $patientRow['address_city'];
        $fa['shipping_address_country_code'] = $patientRow['address_country'];
        $fa['shipping_address_po_code']      = $patientRow['address_code'];
        $fa['shipping_phone']                = $patientRow['phone'];
        $fa['primary_contact']               = $patientRow['primary_contact'];
        $fa['relationship']                  = $patientRow['relationship'];
        $fa['patient_visit_id']              = $patient_visit_id;
        $fa['check_up_date']                 = $patientVisitRec['check_up_date'];
        $fa['no_of_days']                    = $patientVisitRec['no_of_days'];

        $SQLRelation = "
        SELECT b.patient_information_id
              ,CONCAT_WS(' ', b.first_name, b.middle_name, b.last_name) AS patient_name
              ,b.nric
        FROM `patient_relationinfo` a
        LEFT JOIN (patient_information b) ON (b.patient_information_id = a.patient_information_source_id)
        WHERE a.patient_information_id = {$patientInfoRec['patient_information_id']}
        ";
        $resultRelation = $db->sql_query($SQLRelation);
        $relation = '';

        while ($rowRelation = $db->sql_fetchrow($resultRelation)) {
            $relation .= $rowRelation['patient_name'].', ';
        }

        $relation = rtrim($relation, ', ');

        $fa['relationship']                  = $relation;
        $fa['patient_information_id']        = $patientInfoRec['patient_information_id'];
        $fa['first_name']                    = $patientInfoRec['first_name'];
        $fa['middle_name']                   = $patientInfoRec['middle_name'];
        $fa['last_name']                     = $patientInfoRec['last_name'];
        $fa['nric']                          = $patientInfoRec['nric'];
        $fa['bill_type']                     = $patientInfoRec['bill_type'];
        $fa['serial_no_of_book']             = $patientInfoRec['serial_no_of_book'];
        $fa['department']                    = $patientInfoRec['department'];
        $fa['worker_id']                     = $patientInfoRec['worker_id'];

        $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$patient_visit_id}'");

        if(is_array($orderRec)){

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                $fa['site_id'] = $cpSiteIdSession;
            }

            $fa['modification_date']  = date('Y-m-d-H-i-s');
            $fa['modified_by']        = $_SESSION['userName'];

            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $order_code     = $fn->getSettingsValueByKey("nextOrderCode");

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }

            $fa['order_code']       = $order_code;
            $fa['order_date']       = date('Y-m-d');
            $fa['creation_date']    = date('Y-m-d-H-i-s');
            $fa['created_by']       = $_SESSION['userName'];
            $fa['order_status']     = 'New';

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND site_id = {$cpSiteIdSession}";
            }

            //To update Order code
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOrderCode' {$appendSql}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        $SQLDoctor = "
        SELECT  CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name
               ,ev.consultation_fees
               ,ev.notes
               ,ev.employee_visit_id
        FROM employee_visit ev
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ";
        $resultDoctor  = $db->sql_query($SQLDoctor);
        $numRowsDoctor = $db->sql_numrows($resultDoctor);

        if($numRowsDoctor > 0){

          while ($rowDoctor = $db->sql_fetchrow($resultDoctor)) {

            $fa4['record_id']       = $rowDoctor['employee_visit_id'];
            $fa4['order_id']        = $order_id;
            $fa4['record_type']     = 'Doctor/Nurse';
            $fa4['unit_price']      = $rowDoctor['consultation_fees'];
            $fa4['description']     = $rowDoctor['notes'];
            $fa4['item_title']      = $rowDoctor['employee_name'];

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowDoctor['employee_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Doctor/Nurse'");
            if(is_array($orderItemRec)){
                $fa4['modification_date']   = date('Y-m-d-H-i-s');

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa4, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa4['creation_date']   = date('Y-m-d-H-i-s');

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa4, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
          }

        }

        $SQLDiagnosisDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND record_type = 'Diagnosis'
        ";
        $db->sql_query($SQLDiagnosisDelete);

        $SQLdiagnosis = "
        SELECT  d.title
               ,dv.fees
               ,dv.diagnosis_visit_id
        FROM diagnosis_visit dv
        LEFT JOIN diagnosis d ON (d.diagnosis_id = dv.diagnosis_id)
        WHERE dv.patient_visit_id = {$patient_visit_id}
        ";

        $resultdiagnosis  = $db->sql_query($SQLdiagnosis);
        $numRowsdiagnosis = $db->sql_numrows($resultdiagnosis);

        if($numRowsdiagnosis > 0){

          while ($rowdiagnosis = $db->sql_fetchrow($resultdiagnosis)) {

            $fa1['record_id']       = $rowdiagnosis['diagnosis_visit_id'];
            $fa1['order_id']        = $order_id;
            $fa1['record_type']     = 'Diagnosis';
            $fa1['unit_price']      = $rowdiagnosis['fees'];
            $fa1['item_title']      = $rowdiagnosis['title'];

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowdiagnosis['diagnosis_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Diagnosis'");
            if(is_array($orderItemRec)){
                $fa1['modification_date']   = date('Y-m-d-H-i-s');

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa1['creation_date']   = date('Y-m-d-H-i-s');

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa1, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
          }

        }

        $SQLLabTestDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND record_type = 'Lab Test'
        ";
        $db->sql_query($SQLLabTestDelete);

        $SQLlab = "
        SELECT  lv.title
               ,lv.fees
               ,lv.notes
               ,lv.lab_visit_id
        FROM  lab_visit lv
        WHERE lv.patient_visit_id = {$patient_visit_id}
        ";
        $resultlab  = $db->sql_query($SQLlab);
        $numRowslab = $db->sql_numrows($resultlab);

        if($numRowslab > 0){

          while ($rowlab = $db->sql_fetchrow($resultlab)) {

            $fa2['record_id']       = $rowlab['lab_visit_id'];
            $fa2['order_id']        = $order_id;
            $fa2['record_type']     = 'Lab Test';
            $fa2['description']     = $rowlab['notes'];
            $fa2['unit_price']      = $rowlab['fees'];
            $fa2['item_title']      = $rowlab['title'];

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowlab['lab_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Lab Test'");

            if(is_array($orderItemRec)){
                $fa2['modification_date']   = date('Y-m-d-H-i-s');

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa2, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa2['creation_date']   = date('Y-m-d-H-i-s');

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa2, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
          }

        }

        $SQLTreatmentDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND record_type = 'Treatment'
        ";
        $db->sql_query($SQLTreatmentDelete);

        $SQLTreatment = "
        SELECT  t.title
               ,tv.fees
               ,tv.notes
               ,tv.treatment_visit_id
        FROM treatment_visit tv
        LEFT JOIN treatment t ON (t.treatment_id = tv.treatment_id)
        WHERE tv.patient_visit_id = {$patient_visit_id}
        AND status = 'Current'
        ";
        $resultTreatment  = $db->sql_query($SQLTreatment);
        $numRowsTreatment = $db->sql_numrows($resultTreatment);

        if($numRowsTreatment > 0){

          while ($rowTreatment = $db->sql_fetchrow($resultTreatment)) {

            $fa3['record_id']       = $rowTreatment['treatment_visit_id'];
            $fa3['order_id']        = $order_id;
            $fa3['record_type']     = 'Treatment';
            $fa3['unit_price']      = $rowTreatment['fees'];
            $fa3['description']     = $rowTreatment['notes'];
            $fa3['item_title']      = $rowTreatment['title'];

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowTreatment['treatment_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Treatment'");

            if(is_array($orderItemRec)){
                $fa3['modification_date']   = date('Y-m-d-H-i-s');

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa3, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa3['creation_date']   = date('Y-m-d-H-i-s');

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa3, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }

          }

        }

        $SQLMedicineDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND record_type = 'Inventory'
        ";
        $db->sql_query($SQLMedicineDelete);

        $SQLMedicine = "
        SELECT  mv.title
               ,mv.medicines_visit_id
               ,mv.qty
               ,mv.selling_price
               ,mv.instruction
               ,mv.product_id
        FROM medicines_visit mv
        WHERE mv.patient_visit_id = {$patient_visit_id}
        ";
        $resultMedicine   = $db->sql_query($SQLMedicine);
        $numRowsMedicine  = $db->sql_numrows($resultMedicine);

        if($numRowsMedicine > 0){

          while ($rowMedicine = $db->sql_fetchrow($resultMedicine)) {

              if($rowMedicine['product_id']){
                  $fa5['record_id']       = $rowMedicine['product_id'];
                  $fa5['order_id']        = $order_id;
                  $fa5['record_type']     = 'Inventory';
                  $fa5['qty']             = $rowMedicine['qty'];
                  $fa5['unit_price']      = $rowMedicine['selling_price'];
                  $fa5['description']     = $rowMedicine['instruction'];
                  $fa5['item_title']      = $rowMedicine['title'];

                  $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowMedicine['product_id']}'
                                                                          AND order_id = {$order_id}
                                                                          AND record_type = 'Inventory'");

                  if(is_array($orderItemRec)){
                      $fa5['modification_date']   = date('Y-m-d-H-i-s');

                      $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                      $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa5, "order_item", $whereCondition);
                      $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
                  } else {
                      $fa5['creation_date']   = date('Y-m-d-H-i-s');

                      $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa5, 'order_item');
                      $resultOI = $db->sql_query($SQLOI);
                  }

                    $SQLStockFrom = "
                    SELECT actual_stock{$cpSiteIdSession} AS Stock_From
                    FROM inventory
                    WHERE product_id = {$rowMedicine['product_id']}
                    ";
                    $resultStockFrom = $db->sql_query($SQLStockFrom);
                    $rowStockFrom    = $db->sql_fetchrow($resultStockFrom);
                    $stock = $rowStockFrom['Stock_From'] - $rowMedicine['qty'];

                    $SQLUpdateProduct = "
                    UPDATE product SET qty_in_stock{$cpSiteIdSession} = {$stock}
                    WHERE product_id = '{$rowMedicine['product_id']}'
                    ";
                    $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                    $SQLUpdateInventory = "
                    UPDATE inventory SET actual_stock{$cpSiteIdSession} = {$stock}
                    WHERE product_id = '{$rowMedicine['product_id']}'
                    ";
                    $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
                }

            }

        }

        $SQLUpdate = "
        UPDATE patient_visit set status = 'Order Raised' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLUpdateLabs = "
        UPDATE labs set order_id = '{$order_id}' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdateLabs = $db->sql_query($SQLUpdateLabs);

        return $order_id;
    }

    /**
     *
     */
    function getUpdateConsultingFees() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_id     = $fn->getReqParam('employee_id');
        $arr = array();

        if($employee_id != ''){
            $SQL    = "
            SELECT consultation_fees
            FROM employee
            WHERE employee_id = {$employee_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            return $row['consultation_fees'];
        }
    }

    /**
     *
     */
    function getConvertFollowUpDate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $follow_up_date     = $fn->getReqParam('follow_up_date');

        $Date = date("Y-m-d");
        $convertedDate = date("Y-m-d", strtotime($Date. " + ". $follow_up_date));

        return $convertedDate;
    }

    /**
     *
     */
    function getCancelPatientVisitRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $patient_visit_id     = $fn->getReqParam('patient_visit_id');

        $SQLPatientVisit ="
        UPDATE patient_visit SET status = 'Cancelled'
        WHERE patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPatientVisit = $db->sql_query($SQLPatientVisit);

        $SQLOrder = "
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = '{$patient_visit_id}'
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $rowOrder     = $db->sql_fetchrow($resultOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);

        if($numRowsOrder > 0){
            $SQLUpdateOrder = "
            UPDATE `order` SET order_status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultUpdateOrder = $db->sql_query($SQLUpdateOrder);

            $SQLInvoice = "
            UPDATE invoice SET status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);

            $SQLReceipt = "
            UPDATE receipt SET receipt_status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultReceipt = $db->sql_query($SQLReceipt);
        }

    }

    /**
     *
     */
    function getAddTreatmentRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $title = $fn->getPostParam('treatment_title');
        $treatment_code = $fn->getPostParam('treatment_code');

        $recCount = $fn->getRecordCount('treatment', "title = '{$title}'");
        $recCountCode = $fn->getRecordCount('treatment', "treatment_code = '{$treatment_code}'");
        $validate->validateData('treatment_code', 'Please enter treatment code');
        $validate->validateData('treatment_title', 'Please enter treatment title');

        if($recCount > 0){
            $validate->errorArray['treatment_title']['name'] = "treatment_title";
            $validate->errorArray['treatment_title']['msg']  = "treatment already added";
        }

        if($recCountCode > 0){
            $validate->errorArray['treatment_code']['name'] = "treatment_code";
            $validate->errorArray['treatment_code']['msg']  = "treatment code already added";
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
    function getAddTreatmentRecordSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddTreatmentRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $treatment_code    = $fn->getPostParam('treatment_code');
        $title             = $fn->getPostParam('treatment_title');
        $fees              = $fn->getPostParam('fees');
        $category          = $fn->getPostParam('category');
        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['treatment_code']   = $treatment_code;
        $fa['title']            = $title;
        $fa['category']         = $category;
        $fa['fees']             = $fees;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'treatment');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML($title);
    }

    /**
     *
     */
    function getAddDiagnosisRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $title = $fn->getPostParam('diagnosis_title');

        $recCount = $fn->getRecordCount('diagnosis', "title = '{$title}'");
        $validate->validateData('diagnosis_title', 'Please enter diagnosis title');

        if($recCount > 0){
            $validate->errorArray['diagnosis_title']['name'] = "diagnosis_title";
            $validate->errorArray['diagnosis_title']['msg']  = "diagnosis already added";
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
    function getAddDiagnosisRecordSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddDiagnosisRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $diagnosis_code    = $fn->getPostParam('diagnosis_code');
        $title             = $fn->getPostParam('diagnosis_title');
        $fees              = $fn->getPostParam('fees');
        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['diagnosis_code']   = $diagnosis_code;
        $fa['title']            = $title;
        $fa['fees']             = $fees;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'diagnosis');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPrintMedicalCertificateRecord() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $width  = $pdf->pixelsToUnits(740); 
        $height = $pdf->pixelsToUnits(660);

        $resolution= array($width, $height);
        $pdf->AddPage('L', $resolution);
        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        //$pdf->AddPage();

        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQL = "
        SELECT pv.no_of_days
              ,pv.resume_duty_on
              ,pv.medical_certficate_date
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
              ,p.nric
        FROM `patient_visit` pv
        LEFT JOIN `patient_information` p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.patient_visit_id = {$patient_visit_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        //============================================================================= //

        $today = date('d-m-Y');
        $resume_duty_on          = $fn->getCPDate($row['resume_duty_on'], 'd/m/Y');
        $medical_certficate_date = $fn->getCPDate($row['medical_certficate_date'], 'd/m/Y');

        $tbl1 ='<table border="0" width="100%" cellpadding="0">
                    <tr>
                        <td colspan="4" width="100%"><span style="font-size:13px;">This is to certify that i have examined</span><br/>
                            <span style="font-size:13px;">Bahawa dinyatakan saya telah memeriksa</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="12%"><span style="font-size:13px;"><br/>Mr/Mrs/Miss</span><br/>
                            <span style="font-size:13px;">Tuan / Puan</span>
                        </td>
                        <td width="55%" align="center" style="border-bottom:1px solid black;"><br/><br/><br/>
                            <span style="font-size:13px;">'.$row['patient_name'].'</span>
                        </td>
                        <td width="8%"><span style="font-size:13px;"><br/>I/C No.</span><br/>
                            <span style="font-size:13px;">No. K/P</span>
                        </td>
                        <td width="25%" align="center" style="border-bottom:1px solid black;"><br/><br/><br/>
                            <span style="font-size:13px;">'.$row['nric'].'</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="8%"><span style="font-size:13px;">of</span><br/>
                            <span style="font-size:13px;">daripada</span>
                        </td>
                        <td colspan="3" width="92%" style="border-bottom:1px solid black;">
                        </td>
                    </tr>
                    <tr>
                        <td width="74%" colspan="2"><span style="font-size:13px;"><br/>and found that he/she will be unfit for the proper performance of his/her duties for</span>
                        </td>
                        <td width="21%" align="center" style="border-bottom:1px solid black;"><br/><br/>
                            <span style="font-size:13px;">'.$row['no_of_days'].'</span>
                        </td>
                        <td width="5%"><span style="font-size:13px;"><br/>days</span></td>
                    </tr>
                    <tr>
                        <td width="59%" colspan="2"><span style="font-size:13px;">dan mendapati beliau tidak boleh menjalankan tugasnya selama</span>
                        </td>
                        <td width="36%" align="center" style="border-bottom:1px solid black;"><br/>
                            <span style="font-size:13px;">'.$row['no_of_days'].'</span>
                        </td>
                        <td width="5%"><span style="font-size:13px;">hari</span></td>
                    </tr>
                    <tr>
                        <td width="32%" colspan="2"><span style="font-size:13px;"><br/>He/She may resume duty on</span>
                        <br/><span style="font-size:13px;">beliau boleh bertugas semula pada</span>
                        </td>
                        <td width="25%" align="center" style="border-bottom:1px solid black;"><br/><br/><br/>
                            <span style="font-size:13px;">'.$resume_duty_on.'</span>
                        </td>
                        <td width="43%"></td>
                    </tr>
                </table>
                ';

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $SQLSite = "
        SELECT s.address1
              ,s.address2
              ,s.address_state
              ,s.address_town
              ,s.phone
        FROM `site` s
        WHERE s.site_id = {$cpSiteIdSession}
        ";
        $resultSite = $db->sql_query($SQLSite);
        $rowSite    = $db->sql_fetchrow($resultSite);                

        $tbl2 = '<table border="0" width="100%" cellpadding="0">
                    <tr>
                        <td width="55%"><span style="font-size:13px;">Rest Day/Cuti:</span></td>
                        <td width="45%" style="border-bottom:1px solid black;text-transform: lowercase;" colspan="6" rowspan="2"><span style="font-size:13px;">'.$rowSite['address1'].'</span><br/>
                            <span style="font-size:13px;text-transform: lowercase;">'.$rowSite['address2'].'</span><br/>
                            <span style="font-size:13px;text-transform: lowercase;">'.$rowSite['address_state'].'</span><br/>
                            <span style="font-size:13px;text-transform: lowercase;">'.$rowSite['address_town'].'</span><br/>
                            <span style="font-size:11px;text-transform: lowercase;">'.$rowSite['phone'].'</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="6%"><span style="font-size:13px;"><br/><br/>Date</span><br/>
                            <span style="font-size:13px;">Tarikh</span>
                        </td>
                        <td width="26%" align="center" style="border-bottom:1px solid black;"><br/><br/>
                            <span style="font-size:13px;"><br/><br/>'.$medical_certficate_date.'</span>
                        </td>
                        <td width="23%"></td>
                    </tr>

                    <tr>
                        <td width="55%"></td>
                        <td width="45%" align="center"><span style="font-size:13px;">Dental Surgeon / Doktor Pergigian</span></td>
                    </tr>
                </table>
                ';


        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $download_title = $patient_visit_id . '- Medical Cerificate-'.$today.'.pdf';
        $pdf->Output($download_title, 'I');
    }
}
