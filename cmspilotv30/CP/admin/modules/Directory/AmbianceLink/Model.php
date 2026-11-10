<?
class CP_Admin_Modules_Directory_AmbianceLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'ambiance_id');
        $fa = $fn->addToFieldsArray($fa, 'description');

        return $fa;
    }
    
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }
    
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['business_id'] = $tv['srcRoomId'];
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