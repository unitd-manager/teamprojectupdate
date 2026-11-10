<?
class CPL_Admin_Modules_Tradingin_Inventory_Functions extends CP_Admin_Modules_Tradingin_Inventory_Functions{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingin_inventory');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export', 'import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
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
