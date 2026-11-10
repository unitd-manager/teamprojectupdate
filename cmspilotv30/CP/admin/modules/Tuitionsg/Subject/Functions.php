<?
class CP_Admin_Modules_Tuitionsg_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tuitionsg_subject');
        $modules->registerModule($modObj, array(
            'title'         => 'Subject'
        ));
    }
}