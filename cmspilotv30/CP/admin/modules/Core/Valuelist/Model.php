<?
class CP_Admin_Modules_Core_Valuelist_Model extends CP_Common_Modules_Core_Valuelist_Model
{

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('key_text', 'Please choose the valuelist name');
        $validate->validateData('value', 'Please enter the value');

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
        $fa['sort_order'] = $fn->getNextSortOrder("valuelist");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('key_text', 'Please choose the valuelist name');
        $validate->validateData('value', 'Please enter the value');

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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'key_text');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'is_group_heading');
        $fa = $fn->addToFieldsArray($fa, 'value', '', true);

        return $fa;
    }

    function getValueListFieldParamArr($valuelistName){
        $formObj = Zend_Registry::get('formObj');
        
        $module = 'core_valuelist';
        $url = "index.php?_topRm=main&module={$module}" . 
                    "&_spAction=showValuesInModal" . 
                    "&key_text={$valuelistName}&showHTML=0";
        $exp = array();        
        if ($formObj->mode != 'detail') {
            $exp = array(
                 'rowCls' => 'hasNotesRight valuelist'
                ,'notesRight' => "<input type='button' value='Choose' class='w50' " . 
                                 "link='{$url}' class='choose-value' />"
            );
        } 
        return $exp;
    }

}
