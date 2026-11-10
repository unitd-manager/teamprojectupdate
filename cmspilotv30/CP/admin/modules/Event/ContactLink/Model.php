<?
class CP_Admin_Modules_Event_ContactLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        /** actually the value stored is the contact id **/
        $fa = $fn->addToFieldsArray($fa, 'contact_id'); 
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'tries');
        $fa = $fn->addToFieldsArray($fa, 'points');
        $fa = $fn->addToFieldsArray($fa, 'comment');
        
        return $fa;
    }

    //==================================================================//
    function getAddNewGridItem(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $fa['fixture_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    //==================================================================//
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}
