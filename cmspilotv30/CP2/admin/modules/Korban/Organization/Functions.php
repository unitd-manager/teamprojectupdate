<?
class CP_Admin_Modules_Korban_Organization_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('korban_organization');
        $modules->registerModule($modObj, array(
            'titleField'    => "name"
        ));
    }
}