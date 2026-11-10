<?
class CP_Admin_Modules_Logistics_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.* 
              ,gc.name AS country_name
        FROM company c
        LEFT JOIN geo_country gc ON (c.country_code = gc.country_code)
        ";
        
        return $SQL;
	
    }

    /**
     *
     */
    function getLogisticsCompanyLogisticsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
        FROM contact a, company c
        WHERE c.company_id = a.company_id 
          AND c.company_id = {$id}
        ";
    }
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $status       = $fn->getReqParam('status');
        $type         = $fn->getReqParam('type');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');
		$country_code = $fn->getReqParam ('country_code');
			
        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.company_id');
    
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($type != "") {
                $searchVar->sqlSearchVar[] = "type = '{$type}'";
            }

			if ($country_code != "") {
				$searchVar->sqlSearchVar[] = "c.country_code ='($country_code)'";				
			}   
            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.membership LIKE '%{$tv['keyword']}%'  
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }
    
            $searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

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
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'membership');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');

        return $fa;
    }


    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'company_name'	    => $phpExcel->getImportFldObj('COMPANY NAME')
             ,'address_flat' 		=> $phpExcel->getImportFldObj('ADDRESS')
             ,'country_code'        => $phpExcel->getImportFldObj('COUNTRY')
             ,'address_state'  		=> $phpExcel->getImportFldObj('ZIPCODE')
             ,'phone'            	=> $phpExcel->getImportFldObj('PHONE')
             ,'fax'            		=> $phpExcel->getImportFldObj('FAX')
             ,'mobile'              => $phpExcel->getImportFldObj('HP')
        );


        /****************************************/
        $config = array(
             'module'              => 'logistics_company'
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }
}
