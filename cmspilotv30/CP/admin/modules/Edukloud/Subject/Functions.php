<?
class CP_Admin_Modules_Edukloud_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_subject');
        $modules->registerModule($modObj, array(
            'title'         => 'Subject'
        ));
    }
}