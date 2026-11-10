<?
class CP_Admin_Modules_AgileIms_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_subject');
        $modules->registerModule($modObj, array(
            'title'       => 'Subject'
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}