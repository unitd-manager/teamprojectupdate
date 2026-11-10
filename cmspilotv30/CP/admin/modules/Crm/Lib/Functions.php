<?
class CP_Admin_Modules_Crm_Lib_Functions
{
    //==================================================================//
    function setModulesArray($modules){
        //====================== Ideas ==============================//
        $modObj = $modules->getModuleObj('ideas');
            $modules->registerModule($modObj, array(
                'moduleGroup'   => 'crm'
            ));

    }    

    //==================================================================//

}
