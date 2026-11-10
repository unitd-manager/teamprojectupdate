<?
class CP_Admin_Modules_Ek_Class_Model extends CP_Common_Modules_Ek_Class_Model
{
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'class_staff_id');
        $fa = $fn->addToFieldsArray($fa, 'class_leader_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        
        //-----------------------------------------------------------------------//
        /*if($cpCfg['generateSEOUrl'] == 1 && ($tv['lang'] == "eng" || $tv['lang'] == "")){
            $fa['seo_title'] = strtolower( $fn->_prepare_url_text($fa[$titleLang]));
        }*/

        return $fa;
    }

    /**
     *
     */
    function getClassSQL() {
        
        $sql = "
        SELECT class_id
              ,title as class_title
        FROM class
        ORDER BY class_id
        ";
        return $sql;
    }
}
