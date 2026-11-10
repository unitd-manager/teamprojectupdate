<?
class CPL_Admin_Modules_EnggCrm_Home_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_home');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'title'         => 'Home'
        ));
    }

}
