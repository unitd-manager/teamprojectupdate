<?
class CP_Admin_Modules_WebBasic_CallRegistry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        
        $SQL = "
        SELECT c.* 
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              ,co.company_name
        FROM `call_registry` c
        LEFT JOIN (contact cont) ON (c.contact_id = cont.contact_id)
        LEFT JOIN (company co)ON (c.company_id = co.company_id)
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

        $call_registry_id  = $fn->getReqParam('call_registry_id');
        $status      			 = $fn->getReqParam('status');

        if ($call_registry_id != "") {
            $searchVar->sqlSearchVar[] = "c.call_registry_id = '{$call_registry_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.call_registry_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.call_registry_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }


            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(   
                    co.company_name LIKE '%{$tv['keyword']}%'
                 OR c.status  LIKE '%{$tv['keyword']}%'
                 OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }
        }
        
        //$searchVar->sortOrder = "c.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('contact_id', 'Please enter the contact name');

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

        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'company_address');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_date');
        $fa = $fn->addToFieldsArray($fa, 'contact_time');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'call_registry_id');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_type');
        $fa = $fn->addToFieldsArray($fa, 'reminder');

        return $fa;
    }

    /**
     *
     */
    function getWebBasicCallRegistryProjectContactLinkSQL($id) {

        $SQL = "
        SELECT cr.call_registry_contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
        FROM call_registry_contact cr 
        LEFT JOIN contact c ON (c.contact_id = cr.contact_id)
        WHERE cr.call_registry_id = '{$id}'
        ORDER BY cr.call_registry_contact_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getWebBasicCallRegistryProjectCompanyLinkSQL($id) {

        $SQL = "
        SELECT cr.call_registry_company_id
              ,c.company_name
        FROM call_registry_company cr 
        LEFT JOIN company c ON (c.company_id = cr.company_id)
        WHERE cr.call_registry_id = '{$id}'
        ORDER BY cr.call_registry_company_id
        ";

        return $SQL;
    }
}
