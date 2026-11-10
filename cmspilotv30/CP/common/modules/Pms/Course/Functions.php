<?
class CP_Common_Modules_Pms_Course_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_course');
        $modules->registerModule($modObj, array(
            'title' => 'Program'
        ));
    }
}