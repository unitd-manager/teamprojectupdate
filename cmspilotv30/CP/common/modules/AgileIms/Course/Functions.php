<?
class CP_Common_Modules_AgileIms_Course_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('agileIms_course');
        $modules->registerModule($modObj, array(
            'title'       => 'Program'
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}