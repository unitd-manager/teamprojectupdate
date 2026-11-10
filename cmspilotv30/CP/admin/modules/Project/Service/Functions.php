<?
class CP_Admin_Modules_Project_Service_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_service');
        $modules->registerModule($modObj, array(
            'moduleGroup' => 'project'
           ,'actBtnsList' => array('new')
        ));
    }
    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
    }

    //==================================================================//
    }