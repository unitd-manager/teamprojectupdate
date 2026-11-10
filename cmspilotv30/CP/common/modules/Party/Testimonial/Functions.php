<?
class CP_Common_Modules_Party_Testimonial_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('party_testimonial');
        $modules->registerModule($modObj, array(
        ));
    }
}