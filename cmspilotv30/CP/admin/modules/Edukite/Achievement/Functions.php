<?
class CP_Admin_Modules_Edukite_Achievement_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_achievement');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
        ));
    }

}