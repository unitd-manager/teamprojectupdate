<?
class CP_Admin_Modules_AgileIms_Grade_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_grade');
        $modules->registerModule($modObj, array(
            'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}