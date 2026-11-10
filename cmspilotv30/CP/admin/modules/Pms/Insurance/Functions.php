<?
class CP_Admin_Modules_Pms_Insurance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_insurance');
        $modules->registerModule($modObj, array(
            'title'         => 'Insurance'
        ));
    }
}