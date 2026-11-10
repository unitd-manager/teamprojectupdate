<?
class CP_Admin_Modules_Accountsg_Category_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getAddMenuItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($fa['parent_id'] > 0){
            $fa['sort_order'] = $fn->getNextSortOrder('acc_category', "parent_id={$fa['parent_id']}");
        } else {
            $fa['sort_order'] = $fn->getNextSortOrder('acc_category', "parent_id=0");
        }
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
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
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'parent_id');
        $fa = $fn->addToFieldsArray($fa, 'category_type');

        return $fa;
    }

    /**
     *
     */
    function getSaveMenuItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
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
    function getCategorySQL() {
        $SQL = "
        SELECT acc_category_id
              ,title
        FROM acc_category
        ORDER BY title
        ";
        return $SQL;
    }
}
