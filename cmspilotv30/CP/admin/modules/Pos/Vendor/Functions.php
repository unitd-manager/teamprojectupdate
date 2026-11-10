<?
class CP_Admin_Modules_Pos_Vendor_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_vendor');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_vendor', 'pos_productLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'vendor_product'
            ,'showAnchorInLinkPortal' => 1
        ));
    }
}