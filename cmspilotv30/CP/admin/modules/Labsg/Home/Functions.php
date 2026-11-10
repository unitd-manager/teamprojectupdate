<?
class CP_Admin_Modules_Labsg_Home_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_home');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'title'         => 'Home'
        ));
    }

}
