<?
class CP_Admin_Modules_Common_Dashboard_Functions
{
    function setModuleArray($modules){
        $tv = Zend_Registry::get('tv');
        $modObj = $modules->getModuleObj('common_dashboard');
        $modules->registerModule($modObj, array(
            'hasDb'       => false
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array()
           ,'actBtnsEdit' => array()
           ,'hasOnlyListView' => true
        ));
    }
}
