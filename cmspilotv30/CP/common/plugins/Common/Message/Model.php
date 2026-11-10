<?
class CP_Common_Plugins_Common_Message_Model extends  CP_Common_Lib_PluginModelAbstract
{
    //==================================================================//
    function getSQL() {
        $c = &$this->controller;
        $modulesArr = Zend_Registry::get('modulesArr');

        if($c->contactModule != ''){
            $tableName = $modulesArr[$c->contactModule]["tableName"];
            $keyField  = $modulesArr[$c->contactModule]["keyField"];

            $SQL = "
            SELECT c.*
                  ,CONCAT_WS(' ', b.first_name, b.last_name) AS contact_name
            FROM comment c
            LEFT JOIN ({$tableName} b) ON (c.contact_id = b.{$keyField})
            ";
        } else {
            $SQL = "
            SELECT m.*
            FROM message m
            ";
        }
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'm';
        $c = &$this->controller;

        $searchVar->sqlSearchVar[] = "m.record_id = '{$c->recordId}'";
        $searchVar->sortOrder = "creation_date DESC";
    }
    
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->setPluginDataArray($this->controller, 'common_message');
        $this->dataArray = $dataArray;
        return $dataArray;
    }

    //==================================================================//
    function getAdd() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = &$this->fieldsArray;

        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'subject');
        
        $fa['creation_date'] = date("Y-m-d H:i:s");

        if (CP_SCOPE == 'admin'){
            $fa['staff_id'] = $_SESSION['staff_id'];
        } else {
            if (isLoggedInWWW()){
                $fa['staff_id'] = $_SESSION['cpContactId'];
            }
        }
        
        $message_id = $fn->addRecord($fa, 'message');
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getNewValidate() {
        $hook = getCPPluginHook('common_message', 'newValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }
        
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $showCaptcha = $fn->getReqParam('p-common-message_showCaptcha');

        //==================================================================//
        $validate->resetErrorArray();

        if ($showCaptcha){
       	    $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    //==================================================================//
    function getSave() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $record_id = $fn->getReqParam('record_id');
        $rec = $fn->getRecordRowByID('message', 'message_id', $record_id);

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'subject');
        $fa['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE message_id = {$record_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "message", $whereCondition);
        $db->sql_query($SQL);
        
        return $validate->getSuccessMessageXML();

    }

    //==================================================================//
    function getDelete() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getReqParam('record_id');
        $contact_module = $fn->getReqParam('contact_module');

        if (!is_numeric ($record_id)) {
            return;
        }

        $rec = $fn->getRecordRowByID('message', 'message_id', $record_id);

        $SQL = "
        DELETE FROM message 
        WHERE message_id = {$record_id}
        ";
        $result = $db->sql_query($SQL);
    }
}