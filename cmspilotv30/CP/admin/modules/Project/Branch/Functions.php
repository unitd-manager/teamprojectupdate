<?
class CP_Admin_Modules_Project_Branch_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_branch');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 0
           ,'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    /**
     *
     * @return <type>
     */
    function setLocalArrayValues() {
        $tv = Zend_Registry::get('tv');

    }

    /**
     *
     * @return <type>
     */    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//

    }

}