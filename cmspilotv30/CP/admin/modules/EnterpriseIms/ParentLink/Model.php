<?
class CP_Admin_Modules_EnterpriseIms_ParentLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');
        $validate->validateData('id_card_no' , 'Please enter the NRIC no.');

        $id_card_no = $fn->getPostParam('id_card_no', '', true);

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('enterpriseIms_parent', 'record_id', $rec['parent_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC no. already exists. '{$IdCardlink}'";
            }
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
    function getEditPortalValidate() {
        return $this->getNewValidate();
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'relationship_to_student');
        $fa = $fn->addToFieldsArray($fa, 'occupation');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        
        return $fa;
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('enterpriseIms_parent');
        $modObj->model->setSearchVar($linkRecType);

        //$searchVar->sqlSearchVar[] = "c.subscribe = 1";
                
    }
    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['batch_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}
