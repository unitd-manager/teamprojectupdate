<?
class CP_Admin_Modules_Common_HomeLayout_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_homeLayout');
        $modules->registerModule($modObj, array(
            'hasDb'       => false
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array()
           ,'actBtnsEdit' => array()
           ,'hasOnlyListView' => true
        ));
    }
}
