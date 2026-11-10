<?
class CP_Common_Modules_Tuitionsg_Course_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tuitionsg_course');
        $modules->registerModule($modObj, array(
            'title' => 'Program'
        ));
    }
}