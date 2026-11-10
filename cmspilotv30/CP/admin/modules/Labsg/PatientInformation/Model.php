<?
class CP_Admin_Modules_Labsg_PatientInformation_Model extends CP_Common_Lib_ModuleModelAbstract
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
              ,gc.name AS c_address_country
        FROM patient_information p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN geo_country gc ON (gc.country_code = c.address_country)
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
        $company_id = $fn->getReqParam('company_id');

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

                if ($billType == 'Company' && $company_id){
                    $searchVar->sqlSearchVar[] = "p.company_id = '{$company_id}'";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       p.name  LIKE '%{$tv['keyword']}%'
                    OR p.patient_code LIKE '%{$tv['keyword']}%'
                    OR p.registration_no LIKE '%{$tv['keyword']}%'
                    OR p.mobile LIKE '%{$tv['keyword']}%'
                    OR p.email LIKE '%{$tv['keyword']}%'
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
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter Name');
        $validate->validateData('registration_no', 'Please enter Passport / ID');

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
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_code = $fn->getSettingsValueByKey("nextPatientCode");
        $fa = $this->getFields();
        $fa['bill_type']    = 'Individual';
        $fa['patient_code'] = $patient_code;

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
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $bill_type = $fn->getPostParam('bill_type');

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter Name');
        $validate->validateData('nationality', 'Please select Nationality');
        $validate->validateData('dob', 'Please enter DOB');
        $validate->validateData('gender', 'Please select Gender');
        $validate->validateData('registration_no', 'Please enter Passport / ID');
        $validate->validateData('bill_type', 'Please select Bill Type');

        if($bill_type == 'Company'){
            $validate->validateData('company_id', 'Please select the Company');
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
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'dob');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'pass_type');
        $fa = $fn->addToFieldsArray($fa, 'bill_type');
        $fa = $fn->addToFieldsArray($fa, 'occupation');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'first_admit');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'father_name');
        $fa = $fn->addToFieldsArray($fa, 'mother_name');
        $fa = $fn->addToFieldsArray($fa, 'spuse_name');
        $fa = $fn->addToFieldsArray($fa, 'primary_contact');
        $fa = $fn->addToFieldsArray($fa, 'alergies');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        $fa = $fn->addToFieldsArray($fa, 'nric');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');

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
             'module'              => 'labsg_patientInformation'
            ,'matchFieldArr'       => array()
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
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

}
