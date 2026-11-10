<?
class CP_Admin_Modules_AceIms_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_subject');
        $modules->registerModule($modObj, array(
            'title'         => 'Subject'
        ));
    }
}