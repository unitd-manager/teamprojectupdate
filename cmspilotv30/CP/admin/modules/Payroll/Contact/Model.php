<?
class CP_Admin_Modules_Payroll_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
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
    function getSQLForPager() {
        $tv = Zend_Registry::get('tv');
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
        
        $SQL = "
        SELECT count(*)
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
        $language       = $fn->getReqParam('language');
        $contact_id     = $fn->getReqParam('contact_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        if ($contact_id != "") {
            $searchVar->sqlSearchVar['contact_id'] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar['contact_id'] = "c.contact_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar['subscribe'] = "c.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar['subscribe_2'] = "(c.subscribe != 1 OR c.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar['flag'] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar['flag_2'] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar['published'] = "c.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar['published_2'] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }

            if ($tv['special_search'] == 'Invalid Emails' ) {
                $searchVar->sqlSearchVar['invalid_email'] = "c.invalid_email = 1";
            }

            if ($language != '' ) {
                $searchVar->sqlSearchVar['language'] = "c.language = '{$language}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar['keyword'] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($interest_id != '' ) {
                $searchVar->sqlSearchVar['interest_id'] = "ic.interest_id = {$interest_id}";
            }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar['subscribe_3'] = "c.subscribe = 1";
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

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

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
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'language');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        if ($cpCfg['m.payroll.contact.flagInvalidEmails'] && $fa['email'] != '' && !$validate->isEmail($fa['email'])){
            $fa['invalid_email'] = 1;
        }

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
             ,'subscribe'            => $phpExcel->getImportFldObj('Newsletter')
        );

        $fa['address_country_code']['specialType'] = 'geo_country';
        $fa['subscribe']['defaultValue'] = 1;

        $config = array(
             'module'          => 'payroll_contact'
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
        $validate = Zend_Registry::get('validate');

        //link to interest
        if($cpCfg['m.payroll.contact.showInterestInImport']){
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
        
        if ($cpCfg['m.payroll.contact.flagInvalidEmails'] && !$validate->isEmail($fa['email'])){
            $fa2 = array();
            $fa2['invalid_email'] = 1;
            $fa2['subscribe'] = 0;
            $fn->saveRecord($fa2, 'contact', 'contact_id', $contact_id);
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
    function getpayrollContactEventEventLinkSQL($id){
        $ln = Zend_Registry::get('ln');
        
        $lnPfx = $ln->getFieldPrefix();        
        $SQL = "
        SELECT ec.event_contact_id
              ,IF(e.{$lnPfx}title != '', e.{$lnPfx}title, e.title) AS title
        FROM event_contact ec
        LEFT JOIN (event e) ON (e.event_id = ec.event_id)
        WHERE ec.contact_id = {$id}
        ";
        
        return $SQL;
    
    }    
}
