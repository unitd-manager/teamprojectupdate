<?
class CP_Admin_Modules_Edukloud_Insurance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_insurance');
        $modules->registerModule($modObj, array(
            'title'         => 'Insurance'
        ));
    }
}