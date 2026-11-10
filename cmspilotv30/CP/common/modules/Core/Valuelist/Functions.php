<?
class CP_Common_Modules_Core_Valuelist_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('core_valuelist');
        $modules->registerModule($modObj, array(
            'titleField' => 'value'
        ));
    }
}