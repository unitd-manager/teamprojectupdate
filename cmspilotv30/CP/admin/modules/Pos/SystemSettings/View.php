<?
class CP_Admin_Modules_Pos_SystemSettings_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList(){
        return getCPModuleObj('pos_globalSettings')->view->getList('', 'System');
    }

    /**
     *
     */
    function getNew(){
        return getCPModuleObj('pos_globalSettings')->view->getNew();
    }

    /**
     *
     */
    function getEdit($row){
        return getCPModuleObj('pos_globalSettings')->view->getEdit($row);
    }

    /**
     *
     */
    function getRightPanel($row){
    }

    /**
     *
     */
    function getEditFromList() {
        return getCPModuleObj('pos_globalSettings')->view->getEditFromList();
    }

}