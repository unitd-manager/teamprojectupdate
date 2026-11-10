<?
class CP_Admin_Modules_Directory_BusinessLink_Model extends CP_Common_Modules_Directory_BusinessLink_Model
{
    /**
     *
     */
    function getSave(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['srcRoom'] == 'directory_businessContact') {
            $validate = Zend_Registry::get('validate');
            $fa = array();
            $fa = $fn->addToFieldsArray($fa, 'position');
            $id = $fn->saveRecord($fa);
            return $validate->getSuccessMessageXML();
        }
    }

}
