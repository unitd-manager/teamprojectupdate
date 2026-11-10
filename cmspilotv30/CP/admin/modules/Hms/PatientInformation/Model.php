<?
class CP_Admin_Modules_Hms_PatientInformation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name ) AS patient_name
              ,c.company_name
              ,c.phone AS c_phone
              ,c.address_flat AS c_address_flat
              ,c.address_street AS c_address_street
              ,c.address_town AS c_address_town
              ,c.address_state AS c_address_state
              ,c.address_country AS c_address_country
              ,b.patient_information_source_id
        FROM patient_information p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN patient_relationinfo b ON (b.patient_information_source_id = p.patient_information_id)
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
        $searchVar->mainTableAlias = 'p';

        $status       = $fn->getReqParam('status');
        $patient_information_id   = $fn->getReqParam('patient_information_id');
       // $company_name = $fn->getReqParam('company_name');
        $billType   = $fn->getReqParam('bill_type');

        if ($patient_information_id != "") {
            $searchVar->sqlSearchVar[] = "p.patient_information_id = '{$patient_information_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.patient_information_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.patient_information_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }
            if ($billType != "") {
                $searchVar->sqlSearchVar[] = "p.bill_type = '{$billType}'";
            }

           /* if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }
            */

            if ($tv['keyword'] != "") {
                $nric = str_replace('-', '', $tv['keyword']);
                $searchVar->sqlSearchVar[] = "(
                    p.first_name LIKE      '%{$tv['keyword']}%'
                    OR p.middle_name LIKE  '%{$tv['keyword']}%'
                    OR p.last_name  LIKE   '%{$tv['keyword']}%'
                    OR REPLACE(p.nric, '-', '') LIKE '%{$nric}%'
                    OR p.email LIKE        '%{$tv['keyword']}%'
                    OR p.patient_code LIKE '%{$tv['keyword']}%'
                    OR p.mobile LIKE       '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the First name');
        $validate->validateData('nric', 'Please enter NRIC');
        $validate->validateData('bill_type', 'Please select the Bill Type');

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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_code = $fn->getSettingsValueByKey("nextPatientCode");
        $fa = $this->getFields();
       // $fa['bill_type']    = 'Individual';
        $fa['patient_code'] = $patient_code;
        $fa['first_admit']  = date('Y-m-d');

        $id = $fn->addRecord($fa);
        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('nric', 'Please enter NRIC');
        $validate->validateData('first_name', 'Please enter the First name');
        $validate->validateData('bill_type', 'Please select the Bill Type');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $nric = $fn->getPostParam('nric', '', true);

        if ($nric != ''){
            $nric = str_replace('-', '', $nric);
            $rec = $fn->getRecordByCondition('patient_information', "REPLACE(nric, '-', '') = '{$nric}' AND patient_information_id != {$patient_information_id}");
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
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
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'nric');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');

        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'dob');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        $fa = $fn->addToFieldsArray($fa, 'first_admit');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'father_name');
        $fa = $fn->addToFieldsArray($fa, 'mother_name');
        $fa = $fn->addToFieldsArray($fa, 'spuse_name');
        $fa = $fn->addToFieldsArray($fa, 'primary_contact');
        $fa = $fn->addToFieldsArray($fa, 'alergies');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'bill_type');
        $fa = $fn->addToFieldsArray($fa, 'worker_id');
        $fa = $fn->addToFieldsArray($fa, 'father_nric');
        $fa = $fn->addToFieldsArray($fa, 'mother_nric');
        $fa = $fn->addToFieldsArray($fa, 'relationship');
        $fa = $fn->addToFieldsArray($fa, 'serial_no_of_book');
        $fa = $fn->addToFieldsArray($fa, 'department');

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
              'nric'                 => $phpExcel->getImportFldObj('MRID')
             ,'registration_no'      => $phpExcel->getImportFldObj('IC / PP')
             ,'gender'               => $phpExcel->getImportFldObj('Gender')
             ,'dob'                  => $phpExcel->getImportFldObj('DOB')
             ,'race'                 => $phpExcel->getImportFldObj('Race')
             ,'phone'                => $phpExcel->getImportFldObj('Contact Number Phone')
             ,'mobile'               => $phpExcel->getImportFldObj('Contact Number Mobile')
             ,'email'                => $phpExcel->getImportFldObj('Email')
             ,'address_country'      => $phpExcel->getImportFldObj('Country')
        );


        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'hms_patientInformation'
            ,'matchFieldArr'       => array()
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
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
    function getUpdateCompanyDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $company_id     = $fn->getReqParam('company_id');
        $arr = array('phone' => '', 'address_flat' => '', 'address_street' => '', 'address_town' => '', 'address_state' => '' , 'address_country' => '');

        if($company_id != ''){
            $SQL    = "
            SELECT c.*
                  ,gc.name AS c_address_country
            FROM company c
            LEFT JOIN geo_country gc ON (gc.country_code = c.address_country)
            WHERE company_id = {$company_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            $arr['phone']         = $row['phone'];
            $arr['address_flat']  = $row['address_flat'];
            $arr['address_street']= $row['address_street'];
            $arr['address_town']  = $row['address_town'];
            $arr['address_state']  = $row['address_state'];
            $arr['address_country']  = $row['c_address_country'];
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getHmsPatientInformationHmsPatientInformationLinkSQL($id) {

        return "
        SELECT a.patient_information_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS patient_name
              ,a.nric
        FROM `patient_information` a
        LEFT JOIN (patient_relationinfo b) ON (b.patient_information_id = a.patient_information_id)
        WHERE b.patient_information_source_id = {$id}
        UNION
        SELECT a.patient_information_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS patient_name
              ,a.nric
        FROM `patient_information` a
        LEFT JOIN (patient_relationinfo b) ON (b.patient_information_source_id = a.patient_information_id)
        WHERE b.patient_information_id = {$id}
        ";
    }
}
