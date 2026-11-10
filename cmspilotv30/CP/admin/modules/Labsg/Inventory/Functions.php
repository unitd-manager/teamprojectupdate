<?
class CP_Admin_Modules_Labsg_Inventory_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_inventory');
        $modObj['tableName'] = 'inventory';
        $modObj['keyField']  = 'inventory_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export')
           ,'actBtnsDetail' => array('edit')
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

    }

    function setLinksArray($inst) {

    }
}
