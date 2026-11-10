<?
class CP_Admin_Modules_Hms_Inventory_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_inventory');
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
