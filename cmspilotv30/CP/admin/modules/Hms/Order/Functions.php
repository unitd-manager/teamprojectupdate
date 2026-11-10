<?
class CP_Admin_Modules_Hms_Order_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('hms_order');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'actBtnsEdit'   => array('save', 'apply','cancel')
           ,'actBtnsDetail' => array('edit')
           ,'title'         => 'Billing'
        ));
    }
}