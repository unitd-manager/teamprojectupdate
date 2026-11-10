<?
class CP_Admin_Modules_Web2_Poll_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT p.* 
        FROM poll p
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                p.title    LIKE '%{$tv['keyword']}%'
            )";
        }
        
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.poll_id = '{$tv['record_id']}'";
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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'show_title');
        $fa = $fn->addToFieldsArray($fa, 'published');    
        $fa = $fn->addToFieldsArray($fa, 'latest');    

        return $fa;
    }

    /**
     *
     */
    function getPollPollHistoryLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT poll_history_id
              ,title AS title
              ,sort_order
              ,answer_count
        FROM poll_history
        WHERE poll_id = {$id}
        ORDER BY sort_order
        ";

        return $SQL;
    }
}
