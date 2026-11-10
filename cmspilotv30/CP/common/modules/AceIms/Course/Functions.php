<?
class CP_Common_Modules_AceIms_Course_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('aceIms_course');
        $modules->registerModule($modObj, array(
            'title' => 'Program'
        ));
    }
}