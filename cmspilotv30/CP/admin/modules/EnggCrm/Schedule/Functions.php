<?
class CP_Admin_Modules_EnggCrm_Schedule_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('enggCrm_schedule');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
        ));
    }
    //==================================================================//
    //==================================================================//
    //==================================================================//
    }