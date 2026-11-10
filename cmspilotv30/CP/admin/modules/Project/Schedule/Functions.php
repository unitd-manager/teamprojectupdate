<?
class CP_Admin_Modules_Project_Schedule_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_schedule');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
        ));
    }
    //==================================================================//
    //==================================================================//
    //==================================================================//
    }