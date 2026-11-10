<?
class CP_Admin_Modules_EzTrade_Region_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_region');
        $modules->registerModule($modObj, array(
            'tableName' => 'region'
           ,'keyField' => 'region_id'
           ,'title' => 'Region'
           ,'actBtnsList' => array('new')
        ));
    }

    /**
     *
     */
    function setMediaArray($inst) {
    }
    
    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
    }

}