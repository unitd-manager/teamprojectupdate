<?
class CP_Admin_Modules_WebBasic_Enquiry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $countryAppendSQL = "";
        $countryJoinSQL   = "";

        if ($cpCfg['m.webBasic.enquiry.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL   = "LEFT JOIN (country co) ON (e.country_id = co.country_id)";            
        }

        if ($cpCfg['m.webBasic.enquiry.showStaff'] == 1){
            $SQL = "
            SELECT e.* 
                  ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
                  {$countryAppendSQL}
            FROM `enquiry` e
            LEFT JOIN staff s ON (s.staff_id = e.staff_id)
            {$countryJoinSQL}
            ";
        } else {
            $SQL   = "
            SELECT e.* 
                   {$countryAppendSQL}
            FROM `enquiry` e
            {$countryJoinSQL}
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'e';

        $status         = $fn->getReqParam('status');
        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $staff_id       = $fn->getReqParam('staff_id');
        $client_type    = $fn->getReqParam('client_type');
        $country_id     = $fn->getReqParam('country_id');

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "e.country_id = {$country_id}";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.enquiry_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$status}'";
            }

            if ($staff_id != "") {
                $searchVar->sqlSearchVar[] = "e.staff_id = '{$staff_id}'";
            }

            if ($client_type != "") {
                $searchVar->sqlSearchVar[] = "e.client_type = '{$client_type}'";
            }

            if ($creation_date1 != "" && $creation_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(e.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(   
                    e.comments   LIKE '%{$tv['keyword']}%'
                 OR e.first_name LIKE '%{$tv['keyword']}%'
                 OR e.last_name  LIKE '%{$tv['keyword']}%'
                 OR e.email      LIKE '%{$tv['keyword']}%'
                )";
            }
        }
        
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $searchVar = $fnModCountry->setCountrySearch($searchVar, 'e');

        $searchVar->sortOrder = "e.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'subject');
        $fa = $fn->addToFieldsArray($fa, 'comments');

        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_type');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'client_type');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'country_id');

        return $fa;
    }

    //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'first_name'      => $phpExcel->getFldObj('First Name')
             ,'last_name'       => $phpExcel->getFldObj('Last Name')
             ,'email'           => $phpExcel->getFldObj('Email')
             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comments'        => $phpExcel->getFldObj('Comments')
             ,'notes'           => $phpExcel->getFldObj('Admin Comments')
             ,'creation_date'   => $phpExcel->getFldObj('Creation Date')
        );

        $file_name = "Enquiry_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}
