<?
class CP_Admin_Modules_Museum_FacilityAvailabilityLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'facility_id');
        $fa = $fn->addToFieldsArray($fa, 'day');
        $fa = $fn->addToFieldsArray($fa, 'from_time');
        $fa = $fn->addToFieldsArray($fa, 'to_time');
        $fa = $fn->addToFieldsArray($fa, 'date_from');
        $fa = $fn->addToFieldsArray($fa, 'date_to');
        $fa = $fn->addToFieldsArray($fa, 'availability');
        
        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();                
        $fa['facility_id'] = $tv['srcRoomId'];
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
