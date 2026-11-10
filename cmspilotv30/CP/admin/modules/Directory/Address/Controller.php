<?
class CP_Admin_Modules_Directory_Address_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getAddressRecord() {
        $cpUtil = Zend_Registry::get('cpUtil');

        $row = $this->model->getAddressRecord();
        $text = $cpUtil->getJsonFromArray($row);
        return $text;        
    }
    
}