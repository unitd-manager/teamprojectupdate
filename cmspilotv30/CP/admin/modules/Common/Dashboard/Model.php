<?
class CP_Admin_Modules_Common_Dashboard_Model extends CP_Common_Lib_ModuleModelAbstract
{
    //==================================================================//
    function getDasboardObj($objName, $overrideArr = array()) {
        $fn = Zend_Registry::get('fn');
        $arr = array();
        
        $arr['name'] = $objName;
        $arr['heading'] = $objName;
        $arr['cssClass'] = 'c50l';
        $arr['subClass'] = 'subcl';
        
        foreach($overrideArr as $key => $value){
            $arr[$key] = $value;
        }

        return $arr;
    }
}