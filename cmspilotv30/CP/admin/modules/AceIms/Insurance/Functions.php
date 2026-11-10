<?
class CP_Admin_Modules_AceIms_Insurance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_insurance');
        $modules->registerModule($modObj, array(
            'title'         => 'Insurance'
        ));
    }
}