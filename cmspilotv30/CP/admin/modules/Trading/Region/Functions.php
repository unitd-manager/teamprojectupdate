<?
class CP_Admin_Modules_Trading_Region_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_region');
        $modules->registerModule($modObj, array(
            'tableName' => 'region'
           ,'keyField' => 'region_id'
           ,'title' => 'Region'
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'titleField' => 'region_name'
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