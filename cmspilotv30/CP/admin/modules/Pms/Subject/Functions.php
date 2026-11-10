<?
class CP_Admin_Modules_Pms_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_subject');
        $modules->registerModule($modObj, array(
            'title'         => 'Subject'
        ));
    }
}