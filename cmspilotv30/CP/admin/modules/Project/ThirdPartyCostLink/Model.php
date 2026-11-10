<?
class CP_Admin_Modules_Project_ThirdPartyCostLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('item_title', 'Please select the title');
        $validate->validateData('budget_amount', 'Please enter the budget amount');
        $validate->validateData('actual_amount', 'Please enter the actual amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        return $this->getNewValidate();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'item_title');
        $fa = $fn->addToFieldsArray($fa, 'budget_amount');
        $fa = $fn->addToFieldsArray($fa, 'actual_amount');
        $fa = $fn->addToFieldsArray($fa, 'project_id');

        return $fa;
    }
}
