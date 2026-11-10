<?
class CP_Admin_Modules_Ads_Banner_Model extends CP_Common_Modules_Ads_Banner_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (b.banner_id = sl.record_id AND sl.module ='ads_banner')";
            }        
        }

        $SQL = "
        SELECT b.*
        FROM banner b
        {$extraTableNames}
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        parent::setSearchVar($linkRecType);
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";   
            }                  
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.title LIKE '%{$tv['keyword']}%'
            OR s.section_type LIKE '%{$tv['keyword']}%'
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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
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
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'external_link');
        $fa = $fn->addToFieldsArray($fa, 'published');    

        return $fa;
    }
}
