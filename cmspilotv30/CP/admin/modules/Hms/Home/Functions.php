<?
class CP_Admin_Modules_Hms_Home_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_home');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'title'         => 'Home'
        ));
    }

}
