<?
class CP_Admin_Modules_Project_Registry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        
        $tags_id = $fn->getReqParam('tags_id');
        
        if ($tags_id != "") {
           $extraTableNames = "
           JOIN tags_history th ON (a.registry_id = th.record_id)
           JOIN tags t ON (t.tags_id = th.tags_id)
           ";
        }

        $SQL = "
        SELECT a.*
        FROM registry a
        {$extraTableNames}
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
            
        $status  = $fn->getReqParam('status');
        $location  = $fn->getReqParam('location');
        $location = addslashes($location);
        $tags_id  = $fn->getReqParam('tags_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.registry_id = '{$tv['record_id']}'";
        }

        if ($status != '' ) {
            $searchVar->sqlSearchVar[] = "a.status = '{$status}'";
        }
        
        if ($location != '' ) {
            $searchVar->sqlSearchVar[] = "a.hosting_server = '{$location}'";
        }
            
        if ($tags_id != ""){
           $this->sqlSearchVar[] = "t.tags_id = '{$tags_id}'";
        }
            
        
        if ($tv['keyword'] != "") {
           $searchVar->sqlSearchVar[] = "(
             a.title LIKE '%{$tv['keyword']}%'  OR
             a.domain  LIKE '%{$tv['keyword']}%'  OR
             a.domain_registrar  LIKE '%{$tv['keyword']}%'  OR 
             a.notes      LIKE '%{$tv['keyword']}%'
           )";
        } 

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

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
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
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
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'domain');
        $fa = $fn->addToFieldsArray($fa, 'domain_registrar');
        $fa = $fn->addToFieldsArray($fa, 'domain_cpanel');
        $fa = $fn->addToFieldsArray($fa, 'dns_server');
        $fa = $fn->addToFieldsArray($fa, 'domain_other_details');
        $fa = $fn->addToFieldsArray($fa, 'dev_cms');
        $fa = $fn->addToFieldsArray($fa, 'test_cms');
        $fa = $fn->addToFieldsArray($fa, 'live_cms');
        $fa = $fn->addToFieldsArray($fa, 'dev_db');
        $fa = $fn->addToFieldsArray($fa, 'test_db');
        $fa = $fn->addToFieldsArray($fa, 'live_db');
        $fa = $fn->addToFieldsArray($fa, 'test_ftp');
        $fa = $fn->addToFieldsArray($fa, 'live_ftp');
        $fa = $fn->addToFieldsArray($fa, 'analytics_ac_code');
        $fa = $fn->addToFieldsArray($fa, 'analytics_access');
        $fa = $fn->addToFieldsArray($fa, 'adwords_access');
        $fa = $fn->addToFieldsArray($fa, 'hosting_server');
        $fa = $fn->addToFieldsArray($fa, 'host_alloted_space');
        $fa = $fn->addToFieldsArray($fa, 'host_alloted_bwidth');
        $fa = $fn->addToFieldsArray($fa, 'host_used_space');
        $fa = $fn->addToFieldsArray($fa, 'host_used_bwidth');
        $fa = $fn->addToFieldsArray($fa, 'no_of_smtp_relays');
        $fa = $fn->addToFieldsArray($fa, 'used_smtp_relays');
        $fa = $fn->addToFieldsArray($fa, 'host_last_verified');
        $fa = $fn->addToFieldsArray($fa, 'hosting_notes');
        $fa = $fn->addToFieldsArray($fa, 'email_details');
        $fa = $fn->addToFieldsArray($fa, 'live_date');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'payment_schedule');
        $fa = $fn->addToFieldsArray($fa, 'hosting_fee');
        $fa = $fn->addToFieldsArray($fa, 'last_invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'payment_status');
        $fa = $fn->addToFieldsArray($fa, 'next_invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'payment_notes');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        return $fa;
    }
}
