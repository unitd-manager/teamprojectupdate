<?
class CP_Admin_Modules_Tradingin_Inventory_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingin_inventory');
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
