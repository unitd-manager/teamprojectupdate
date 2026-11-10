<?
class CP_Admin_Modules_Project_QuoteTemplate_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT q.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM quote q
        LEFT JOIN (staff s) ON (q.sign_staff_id = s.staff_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        
        $searchVar->sqlSearchVar[] = "q.template = 1";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "q.quote_id = '{$tv['record_id']}'";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('template_title', 'Please enter the template title');

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
        $fa['template'] =  1;
        $fa['sort_order'] = $fn->getNextSortOrder("quote");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('template_title', 'Please enter the template title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
    
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

        $fa = $fn->addToFieldsArray($fa, 'template_title');
        $fa = $fn->addToFieldsArray($fa, 'quote_type');
        $fa = $fn->addToFieldsArray($fa, 'currency_item');
        $fa = $fn->addToFieldsArray($fa, 'note');
        $fa = $fn->addToFieldsArray($fa, 'condition');
        $fa = $fn->addToFieldsArray($fa, 'sign_staff_id');
        
        return $fa;
    }
}
