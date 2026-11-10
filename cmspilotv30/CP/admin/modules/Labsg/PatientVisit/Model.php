<?
class CP_Admin_Modules_Labsg_PatientVisit_Model extends CP_Common_Lib_ModuleModelAbstract
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
              ,p.bill_type
              ,p.patient_code
              ,p.company_id
              ,p.registration_no
              ,p.nationality
              ,p.gender
              ,p.pass_type
              ,p.occupation
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
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

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $invoiced     = $fn->getReqParam('invoiced');

        $status       = $fn->getReqParam('status');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
       // $company_name = $fn->getReqParam('company_name');

        if ($patient_visit_id != "") {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$patient_visit_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pv.patient_visit_id');

            if ($start_date != '' && $end_date != '') {
                $searchVar->sqlSearchVar[] = "pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "pv.status = '{$status}'";
            }

            if ($invoiced != "") {
                if ($invoiced == 'Yes'){
                    $searchVar->sqlSearchVar[] = "pv.order_id != ''";
                } else if ($invoiced == 'No') {
                    $searchVar->sqlSearchVar[] = "(pv.order_id = '' OR pv.order_id IS NULL)";
                }
            }

            /*if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.name               LIKE '%{$tv['keyword']}%'
                    OR p.registration_no LIKE '%{$tv['keyword']}%'
                    OR p.email           LIKE '%{$tv['keyword']}%'
                    OR p.mobile          LIKE '%{$tv['keyword']}%'
                    OR pv.visit_code     LIKE '%{$tv['keyword']}%'
                    OR c.company_name    LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            $searchVar->sortOrder = "pv.check_up_date DESC";
        }
    }

    /**
     *
     */
    function getSQLForPager() {

        $SQL = "
        SELECT count(*)
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        ";

        return $SQL;
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

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        if($fa['follow_up_date'] != '' ){
            $followUpRec = $fn->getRecordByCondition('follow_up_patient', "patient_visit_id = '{$patient_visit_id}' AND record_type = 'Follow Up'");

            $fa1 = array();
            $fa1['follow_up_date']   = $fa['follow_up_date'];
            $fa1['follow_up_time']   = date("H:i:s");

            if($followUpRec['follow_up_patient_id'] == ''){
                $fa1['record_type']      = 'Follow Up';
                $fa1['patient_visit_id'] = $patient_visit_id;
                $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
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
        $fa = $fn->addToFieldsArray($fa, 'patient_information_id');
        $fa = $fn->addToFieldsArray($fa, 'pass_type');

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
             'module'              => 'labsg_company'
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
    function getlabsgCompanylabsgContactLinkSQL($id) {

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
    function getlabsgCompanylabsgDiscountLinkSQL($id) {

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
    function getlabsgCompanylabsgCompanyGroupLinkSQL1($id) {

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
    function getTreatmentRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getTreatmentRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $treatmentIds     = $fn->getPostParam('treatmentId', array());
        $status_arr       = $fn->getReqParam('treatment_status', array());
        $fees_arr         = $fn->getPostParam('fees', array());
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $notes_arr        = $fn->getPostParam('notes', array());
        $site_id          = $fn->getSessionParam('cp_site_id');

        $treatVisitRec = $fn->getRecordByConditionForHistoryTable('treatment_visit', "patient_visit_id = '{$patient_visit_id}'");

        if($treatVisitRec['treatment_visit_id'] != ''){
            $SQLDelete = "DELETE FROM treatment_visit WHERE patient_visit_id = {$patient_visit_id}";
            $db->sql_query($SQLDelete);
        }
        $SQLDelete = "DELETE FROM follow_up_patient WHERE patient_visit_id = {$patient_visit_id} AND record_type = 'Treatment'";
        $db->sql_query($SQLDelete);

        $count = count($treatmentIds);
        for ($i= 0; $i < $count; $i++) {
            $treatment_id         = $treatmentIds[$i];
            $treatment_id_explode = explode('_', $treatment_id);
            $status               = $status_arr[$treatment_id_explode[1]];
            $notes                = $notes_arr[$treatment_id_explode[1]];
            $fees                 = $fees_arr[$treatment_id_explode[1]];
            $future_date          = $fn->getPostParam("future_date_".$treatment_id_explode[0]);
            $future_value         = $fn->getPostParam("future_value_".$treatment_id_explode[0]);

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

                // Inserting Patient record in DICOM server START
                $recCount = $fn->getRecordCount('EXAMINATION', "AccessionNumber = '{$patient_visit_id}'");
                if ($recCount == 0) {
                    $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
                    $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id'], array('globalForAllSites' => true));
                    $current_date_time = $cpUtil->getISODateTimeStr();
                    
                    $faXray = array();
                    $faXray['PatientID']       = $patientInfoRec['patient_information_id'];
                    $faXray['PatientName']     = $patientInfoRec['name'];
                    $faXray['Sex']             = $patientInfoRec['gender'];
                    $faXray['DOB']             = $patientInfoRec['dob'];
                    $faXray['PassportNo']      = $patientInfoRec['registration_no'];
                    $faXray['AccessionNumber'] = $patient_visit_id;
                    $faXray['Status']          = "N"; //N-New Order;M-Modified Order;X–Deleted Order;U-Updated (Updated by iCare)
                    $faXray['CreatedByCMS']    = $current_date_time;

                    if ($site_id) {
                        $faXray['site_id']     = $site_id;
                    }

                    $SQLXray    = $dbUtil->getInsertSQLStringFromArray($faXray, 'EXAMINATION');
                    $resultXray = $db->sql_query($SQLXray);
                    /*
                    $link = mysql_connect("67.43.2.86","integrate","integrate");// Server, server username and password
                    mysql_select_db("INTEGRATION");//db name
                    if (!$link) {
                        die('Could not connect: ' . mysql_error());
                    }
                    echo 'Connected successfully';
                    
                    $SQLXray1    = $dbUtil->getInsertSQLStringFromArray($faXray, 'EXAMINATION');
                    $resultXray1 = $db->sql_query($SQLXray1);
                    mysql_close($link);
                    */
                } else {
                    $current_date_time = $cpUtil->getISODateTimeStr();

                    $faXray = array();
                    $faXray['LastModifiedByCMS'] = $current_date_time;
                    $faXray['Status']            = "M"; //N-New Order;M-Modified Order;X–Deleted Order;U-Updated (Updated by iCare)

                    $whereCondition = "WHERE AccessionNumber = {$patient_visit_id}";
                    $SQLXray = $dbUtil->getUpdateSQLStringFromArray($faXray, 'EXAMINATION', $whereCondition);
                    $db->sql_query($SQLXray);
                    /*
                    $link = mysql_connect("67.43.2.86","integrate","integrate");// Server, server username and password
                    mysql_select_db("INTEGRATION");//db name
                    $whereCondition1 = "WHERE AccessionNumber = {$patient_visit_id}";
                    $SQLXray1 = $dbUtil->getUpdateSQLStringFromArray($faXray, 'EXAMINATION', $whereCondition1);
                    $db->sql_query($SQLXray1);
                    mysql_close($link);
                    */
                }
                // Inserting Patient record in DICOM server END

                if ($future_date != '') {
                    $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
                    $employeeVisitRec = $fn->getRecordRowByID('employee_visit', 'patient_visit_id', $patient_visit_id, array('globalForAllSites' => true));

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
        $site_id                 = $fn->getSessionParam('cp_site_id');

        $currentDate  = date("Y-m-d");

        $fa = array();

        $fa['patient_information_id'] = $patient_information_id;
        $fa['status']                 = 'New';
        $fa['record_type']            = 'Walk In';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");
        $fa['visit_code']             = $visit_code;

        if ($appointment_id != '') {
            $fa['appointment_id']     = $appointment_id;
        }

        if ($site_id) {
            $fa1['site_id']           = $site_id;
        }

        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        //To update patient visit code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

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
     * Used in Mediway Medical
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
        $site_id                 = $fn->getSessionParam('cp_site_id');

        $currentDate  = date("Y-m-d");

        $fa = array();

        $fa['patient_information_id'] = $patient_information_id;
        $fa['status']                 = 'New';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");

        if($appointment_id != ''){
          $fa['appointment_id']       = $appointment_id;
          $fa['record_type']          = 'By Appointment';
        }else {
          $fa['record_type']          = 'Walk In';
        }

        if ($site_id) {
            $fa['site_id']            = $site_id;
        }

        $fa['visit_code']             = $visit_code;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        //To update patient visit code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode' AND site_id = '{$site_id}'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQL = "
        SELECT MAX(pq.queue_no) AS queue_no
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.check_up_date = '{$currentDate}'
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

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $site_id           = $fn->getSessionParam('cp_site_id');

        $fa = array();

        $patientVisitRec  = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec   = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id'], array('globalForAllSites' => true));
        $fa['company_id'] = $patientInfoRec['company_id'];

        $patientRow = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientInfoRec['patient_information_id'], array('globalForAllSites' => true));
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $patientInfoRec['company_id'], array('globalForAllSites' => true));
        
        $fa['company_name']                  = $companyRow['company_name'];
        $fa['cust_address1']                 = $companyRow['billing_address_flat'];
        $fa['cust_address2']                 = $companyRow['billing_address_street'];
        $fa['cust_address_city']             = $companyRow['billing_address_town'];
        $fa['cust_address_state']            = $companyRow['billing_address_state'];
        $fa['cust_address_country_code']     = $companyRow['billing_address_country'];
        $fa['cust_phone']                    = $companyRow['phone'];
        $fa['shipping_address1']             = $patientRow['address_street'];
        $fa['shipping_address_area']         = $patientRow['address_area'];
        $fa['shipping_address_city']         = $patientRow['address_city'];
        $fa['shipping_address_country_code'] = $patientRow['address_country'];
        $fa['shipping_address_po_code']      = $patientRow['address_code'];
        $fa['shipping_phone']                = $patientRow['phone'];
        $fa['bill_type']                     = $patientRow['bill_type'];
        $fa['patient_visit_id']              = $patient_visit_id;

        if ($site_id) {
            $fa['site_id']                   = $site_id;
        }

        $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$patient_visit_id}'");

        if(is_array($orderRec)){
            $fa['modification_date']  = date('Y-m-d-H-i-s');
            $fa['modified_by']        = $_SESSION['userName'];

            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $order_code     = $fn->getSettingsValueByKey("nextOrderCode");

            $fa['order_code']       = $order_code;
            $fa['order_date']       = date('Y-m-d');
            $fa['creation_date']    = date('Y-m-d-H-i-s');
            $fa['created_by']       = $_SESSION['userName'];
            $fa['order_status']     = 'New';

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();

            //To update Order code
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOrderCode' AND site_id = {$site_id}";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOrderCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        $SQLUpdate = "
        UPDATE patient_visit set status = 'Order Raised' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $order_id;
    }

    /**
     *
     */
    function getCreateOrderIndividual() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $site_id = $fn->getSessionParam('cp_site_id');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $fa = array();

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id'], array('globalForAllSites' => true));
        $fa['company_id'] = $patientInfoRec['company_id'];

        $patientRow = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientInfoRec['patient_information_id'], array('globalForAllSites' => true));
        $companyRow = $fn->getRecordRowByID('company', 'company_id', $patientInfoRec['company_id'], array('globalForAllSites' => true));
        $fa['company_name']                  = $companyRow['company_name'];
        $fa['cust_address1']                 = $companyRow['address_flat'];
        $fa['cust_address2']                 = $companyRow['address_street'];
        $fa['cust_address_city']             = $companyRow['address_town'];
        $fa['cust_address_state']            = $companyRow['address_state'];
        $fa['cust_address_country_code']     = $companyRow['address_country'];
        $fa['cust_phone']                    = $companyRow['phone'];
        $fa['shipping_address1']             = $patientRow['address_street'];
        $fa['shipping_address_area']         = $patientRow['address_area'];
        $fa['shipping_address_city']         = $patientRow['address_city'];
        $fa['shipping_address_country_code'] = $patientRow['address_country'];
        $fa['shipping_address_po_code']      = $patientRow['address_code'];
        $fa['shipping_phone']                = $patientRow['phone'];
        $fa['patient_visit_id']              = $patient_visit_id;
        $fa['patient_information_id']        = $patientInfoRec['patient_information_id'];
        $fa['first_name']                    = $patientInfoRec['name'];
        $fa['middle_name']                   = $patientInfoRec['middle_name'];
        $fa['last_name']                     = $patientInfoRec['last_name'];
        $fa['nric']                          = $patientInfoRec['registration_no'];
        $fa['bill_type']                     = $patientInfoRec['bill_type'];

        if ($site_id) {
            $fa['site_id']                   = $site_id;
        }

        $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$patient_visit_id}'");

        if(is_array($orderRec)){

            $fa['modification_date']  = date('Y-m-d-H-i-s');
            $fa['modified_by']        = $_SESSION['userName'];

            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $order_code     = $fn->getSettingsValueByKey("nextOrderCode");

            $fa['order_code']       = $order_code;
            $fa['order_date']       = date('Y-m-d');
            $fa['creation_date']    = date('Y-m-d-H-i-s');
            $fa['created_by']       = $_SESSION['userName'];
            $fa['order_status']     = 'New';

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();

            //To update Order code
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOrderCode' AND site_id = {$site_id}";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextOrderCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        $SQLDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND record_type = 'Treatment'
        ";
        $db->sql_query($SQLDelete);

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
            $fa3['patient_information_id'] = $patientInfoRec['patient_information_id'];
            $fa3['patient_visit_id'] = $patient_visit_id;
            $fa3['first_name']       = $patientInfoRec['first_name'];
            $fa3['middle_name']      = $patientInfoRec['middle_name'];
            $fa3['last_name']        = $patientInfoRec['last_name'];
            $fa3['patient_name']     = $patientInfoRec['name'];
            $fa3['nric']             = $patientInfoRec['registration_no'];

            /*
            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowTreatment['treatment_visit_id']}'
                                                                    AND order_id1 = {$order_id}
                                                                    AND record_type = 'Treatment'"
                                                                  , array('globalForAllSites' => true));
            */

            $sqlOiRec = "
            SELECT * FROM order_item
            WHERE record_id = '{$rowTreatment['treatment_visit_id']}'
              AND order_id = {$order_id}
              AND record_type = 'Treatment'
            ";
            $resultOiRec  = $db->sql_query($sqlOiRec);
            $numRowsOiRec = $db->sql_numrows($resultOiRec);

            if($numRowsOiRec > 0){
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

        $SQLUpdate = "
        UPDATE patient_visit set status = 'Order Raised' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $order_id;
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
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getAddPatientRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $first_name       = $fn->getPostParam('first_name');
        $middle_name      = $fn->getPostParam('middle_name');
        $last_name        = $fn->getPostParam('last_name');
        $name             = $fn->getPostParam('name');
        $nationality      = $fn->getPostParam('nationality');
        $nric             = $fn->getPostParam('nric');
        $phone            = $fn->getPostParam('phone');
        $mobile           = $fn->getPostParam('mobile');
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
        $pass_type        = $fn->getPostParam('pass_type');
        $occupation       = $fn->getPostParam('occupation');
        $patient_code     = $fn->getSettingsValueByKey("nextPatientCode");
        $notes            = $fn->getPostParam('notes');
        $site_id          = $fn->getSessionParam('cp_site_id');

        $fa = array();
        $fa['patient_code']    = $patient_code;
        $fa['first_name']      = $first_name;
        $fa['middle_name']     = $middle_name;
        $fa['last_name']       = $last_name;
        $fa['name']            = $name;
        $fa['nationality']     = $nationality;
        $fa['nric']            = $nric;
        $fa['phone']           = $phone;
        $fa['mobile']          = $mobile;
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
        $fa['pass_type']       = $pass_type;
        $fa['occupation']      = $occupation;

        $patient_information_id = $fn->addRecord($fa, 'patient_information');
        $visit_code             = $fn->getSettingsValueByKey("nextPatientvisitCode");
        $currentDate  = date("Y-m-d");

        $fa1 = array();
        $fa1['patient_information_id'] = $patient_information_id;
        $fa1['visit_code']             = $visit_code;
        $fa1['status']                 = 'New';
        $fa1['record_type']            = 'Walk In';
        $fa1['check_up_date']          = $currentDate;
        $fa1['check_up_time']          = date("H:i:s");
        $fa1['creation_date']          = date("Y-m-d H:i:s");
        $fa1['created_by']             = $fn->getSessionParam('userName');
        $fa1['notes']                  = $notes;

        if ($site_id) {
            $fa1['site_id']            = $site_id;
        }

        $patient_visit_id = $fn->addRecord($fa1, 'patient_visit');

        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        //To update patient visit code
        if ($site_id) {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode' AND site_id = {$site_id}";
        } else {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);

        $url ="index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$patient_visit_id}";
        return $validate->getSuccessMessageXML($url);
    }

    /**
     *
     */
    function getAddPatientRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter Name');
        $validate->validateData('gender', 'Please select Gender');
        $validate->validateData('nationality', 'Please select Nationality');
        $validate->validateData('dob', 'Please enter DOB');
        $validate->validateData('registration_no', 'Please enter Passport / ID');
        $validate->validateData('bill_type', 'Please select Bill Type');
        //$validate->validateData('occupation', 'Please enter Occupation');

        $registration_no = $fn->getPostParam('registration_no', '', true);
        $bill_type = $fn->getPostParam('bill_type');

        $registration_no = str_replace('-', '', $registration_no);

        if ($registration_no != ''){
            $rec = $fn->getRecordByCondition('patient_information', "REPLACE(registration_no, '-', '') = '{$registration_no}'");
            $expNRIC = array('displayText' => 'click here', 'target' => '_blank');
            $NRIClink = $fn->getRecordDetailLink('labsg_patientInformation', 'record_id', $rec['patient_information_id'], $expNRIC);

            if (is_array($rec)){
                $validate->errorArray['registration_no']['name'] = "registration_no";
                $validate->errorArray['registration_no']['msg']  = "Passport / ID already exist in system, please '{$NRIClink}' to check the detail";
            }
        }

        if($bill_type == 'Company' || $bill_type == 'Panel'){
            $validate->validateData('company_id', 'Please select Company Name');
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
    function getPrintLabelPatientVisitFormValidate(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('case_note', 'Please Enter Case Note');
        $validate->validateData('lab_note', 'Please Enter Lab Note');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getPrintLabelPatientVisitFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPrintLabelPatientVisitFormValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $valuelist_name  = $fn->getReqParam('valuelist_name');
        $valuelist_value = $fn->getPostParam('valuelist_value');

        if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();

        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getAddNewValuelistFormValidate($valuelist_name, $valuelist_value) {
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('valuelist_value', 'Please enter value');

        if ($valuelist_value) {
            $sql = "
            SELECT value FROM valuelist
            WHERE key_text = '{$valuelist_name}'
              AND value = '{$valuelist_value}'
            ";
            $result  = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $validate->errorArray['valuelist_value']['name'] = "valuelist_value";
                $validate->errorArray['valuelist_value']['msg']  = "Entered value already exists";
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
    function getValueByValuelistJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";
        $valuelist_name = $fn->getReqParam('valuelist_name');

        $json  = array();
        if ($valuelist_name == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT v.value
        FROM valuelist v
        WHERE v.key_text = '{$valuelist_name}'
        ORDER BY v.value ASC
        ";
        $result = $db->sql_query($SQL);
        $selected = '';
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['value'], "caption" => $row['value'], $selected);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCancelPatientVisitFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $cancelling_notes = $fn->getPostParam('cancelling_notes');

        if (!$this->getCancelPatientVisitFormSubmitValidate()){
            return $validate->getErrorMessageXML();
        }

        $current_date = date("Y-m-d H:i:s");
        $modified_by  = $fn->getSessionParam('userName');

        $pvRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        // Cancelling Receipt - START
        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
           ,cancelling_notes = '{$cancelling_notes}'
           ,modification_date = '{$current_date}'
           ,modified_by = '{$modified_by}'
        WHERE order_id = '{$pvRec['order_id']}'
        ";
        $resultRec = $db->sql_query($sqlRec);
        // Cancelling Receipt - END

        // Cancelling Invoice - START
        $sqlInv = "
        UPDATE invoice
        SET status = 'Cancelled'
           ,cancelling_notes = '{$cancelling_notes}'
           ,modification_date = '{$current_date}'
           ,modified_by = '{$modified_by}'
        WHERE order_id = '{$pvRec['order_id']}'
        ";
        $resultInv = $db->sql_query($sqlInv);
        // Cancelling Invoice - END

        // Cancelling Order - START
        $SQLUpdate = "
        UPDATE `order` SET order_status ='Cancelled'
        WHERE order_id = '{$pvRec['order_id']}'
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);
        // Cancelling Order - END

        // Cancelling Patient Visit - START
        $sqlPv = "
        UPDATE patient_visit
        SET status = 'Cancelled'
           ,cancelling_notes = '{$cancelling_notes}'
           ,modification_date = '{$current_date}'
           ,modified_by = '{$modified_by}'
        WHERE patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPv = $db->sql_query($sqlPv);
        // Cancelling Patient Visit - END

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCancelPatientVisitFormSubmitValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('cancelling_notes' , 'Please enter Notes');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
