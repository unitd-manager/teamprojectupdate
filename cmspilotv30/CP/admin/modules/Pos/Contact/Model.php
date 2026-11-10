<?
class CP_Admin_Modules_Pos_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $interest_id    = $fn->getReqParam('interest_id');

        $extraTableNames = '';
        $extraFlds = '';
        if ($interest_id != "") {
            $extraTableNames .= "JOIN interest_contact ic ON (c.contact_id = ic.contact_id)";
        }

        if ($cpCfg['cp.hasMultiSites']) {
            $extraTableNames .= "LEFT JOIN (site si) ON (c.site_id = si.site_id)";
            $extraFlds = ",si.title AS site_title";
        }

        $SQL   = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,gc.name AS country_name
              {$extraFlds}
        FROM contact c
        LEFT JOIN geo_country gc ON (c.address_country_code = gc.country_code)
        {$extraTableNames}
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
        $searchVar->mainTableAlias = 'c';

        $interest_id    = $fn->getReqParam('interest_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

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

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar[] = "ic.interest_id = {$interest_id}";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "c.subscribe = 1";
            }

            $searchVar->sortOrder = "c.last_name, c.first_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('email' , 'Please enter a valid email address', 'email');

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");
        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = "Email already exist.";
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

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

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

        if (!$this->getEditValidate()){
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

        $rowMember = $fn->getSettingsRowByKey('member');
        $contact_id = $fn->getPostParam('contact_id');
        $member_no = $fn->getPostParam('member_no');
        $length = $rowMember['length'] - strlen($contact_id);

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        } 
        $memberNoValue = $rowMember['value'] . $extraNo . $contact_id;

        if ($rowMember['auto_generate_no'] == 1) {
            if ($rowMember['add_separator'] == 1){
                $memberNoValue = $rowMember['value'] . '-' . $extraNo . $contact_id;
            }
            $fa['member_no'] = $memberNoValue;
        } else if ($rowMember['auto_generate_no'] == 2) {
            if($member_no == ''){
                $fa['member_no'] = $memberNoValue;
            } else {
                $fa['member_no'] = $member_no;
            }
        } else {
            $fa['member_no'] = $member_no;
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

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_district');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'language');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        $fa = $fn->addToFieldsArray($fa, 'member_no');
        $fa = $fn->addToFieldsArray($fa, 'reference_no');
        $fa = $fn->addToFieldsArray($fa, 'birth_date_year');
        $fa = $fn->addToFieldsArray($fa, 'birth_date_month');
        $fa = $fn->addToFieldsArray($fa, 'birth_date_day');
        $fa = $fn->addToFieldsArray($fa, 'sex');
        $fa = $fn->addToFieldsArray($fa, 'remark');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_from');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_to');
        $fa = $fn->addToFieldsArray($fa, 'auto_upgrade_mem_grp');

        return $fa;
    }

    /**
     *
     */
    function getContactSQL() {
        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name ) AS contact_name
        FROM contact
        WHERE published = 1
        ORDER BY contact_name
        ";

        return $SQL;
    }

    //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'first_name'      => $phpExcel->getFldObj('First Name')
             ,'last_name'       => $phpExcel->getFldObj('Last Name')
             ,'email'           => $phpExcel->getFldObj('Email')
             ,'phone_direct'    => $phpExcel->getFldObj('Phone')
             ,'mobile'          => $phpExcel->getFldObj('Mobile')
             ,'address1'        => $phpExcel->getFldObj('Address 1')
             ,'address2'        => $phpExcel->getFldObj('Address 2')
             ,'address_city'    => $phpExcel->getFldObj('City')
             ,'address_state'   => $phpExcel->getFldObj('State')
             ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
             ,'country_name'    => $phpExcel->getFldObj('Country')
        );

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
             ,'address1'             => $phpExcel->getImportFldObj('Address 1')
             ,'address2'             => $phpExcel->getImportFldObj('Address 2')
             ,'address_city'         => $phpExcel->getImportFldObj('City')
             ,'address_state'        => $phpExcel->getImportFldObj('State')
             ,'address_po_code'      => $phpExcel->getImportFldObj('Zip Code')
             ,'address_country_code' => $phpExcel->getImportFldObj('Country')
        );

        $fa['address_country_code']['specialType'] = 'geo_country';

        $config = array(
             'module'          => 'common_contact'
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
    function xgetFieldsForImport($phpExcel, $curRow) {

        $fa = array();

        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'First Name', 'first_name');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Last Name', 'last_name');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Email', 'email');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Phone', 'phone_direct');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Mobile', 'mobile');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Address 1', 'address1');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Address 2', 'address2');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'City', 'address_city');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'State', 'address_state');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'Zip Code', 'address_po_code');
        $fa = $phpExcel->addToFieldsArray($curRow, $fa, 'published', 'address_po_code');

        $country = $phpExcel->getExcelFieldValue('Country', $curRow);
        $fa['address_country_code'] = getCPModelObj('common_geoCountry')->getCodeByName($country);
        return $fa;
    }
}
