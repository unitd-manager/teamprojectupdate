<?
class CP_Admin_Modules_ManPower_TaskLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('status', 'Please choose the status');
        $validate->validateData('due_date', 'Please enter the date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $spActionObj = includeCPClass('Lib', 'SpecialAction');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $spActionObj->linkRecordsByFormField('manPower_task', 'core_staffLink', $id, 'staff_ids');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $spActionObj = includeCPClass('Lib', 'SpecialAction');
        
        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $spActionObj->linkRecordsByFormField('manPower_task', 'core_staffLink', $id, 'staff_ids');
        return $validate->getSuccessMessageXML();
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
    function getAddPortalFromDashboard(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidateFromDashboard()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $row = $fn->getRecordRowByID('task', 'task_id', $id);

        return $validate->getSuccessMessageXML('', '', array('task_id' => $id, 'task_title' => $row['title']));
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'from_date');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'chargeable');
        $fa = $fn->addToFieldsArray($fa, 'staff_alert');
        $fa = $fn->addToFieldsArray($fa, 'estimated_hours');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'description');
        
        return $fa;
    }
}
