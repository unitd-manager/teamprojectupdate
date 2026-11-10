<?
class CP_Admin_Modules_AceIms_Order_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('aceIms_order');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'printOrder')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
        ));
    }

    /**
     *
     */
    function setActionsArray($actArrayObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        
        //=============== Print Order =================//
        $actObj = $actArrayObj->getActionObj('printOrder');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Print'
           ,'url' => "index.php?module=aceIms_order&_spAction=printOrder"
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('aceIms_order', 'ecommerce_orderItemLink');
        $productArr = $fn->getDdDataAsArray($cpCfg['m.ecommerce.order.itemsMainModule']);
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'order_item'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'order_item_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
            ,'fieldlabel'             => array('Product', 'Unit Price', 'Qty')
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $productArr)
            )
            ,'additionalFieldsArray' => array(
                 'b.item_title'
                ,'b.unit_price'
                ,'b.qty'
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('aceIms_order', 'aceIms_insuranceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'student_insurance'
           ,'historyTableKeyField'  => 'student_insurance_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'showAnchorInLinkPortal'=> 0
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 600
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array('Course Name'
                                            ,'Insurance Company'
                                            ,'Certificate of Insurance'
                                            ,'Protected Amount'
                                            ,'Start Date'
                                            ,'End Date'
                                       )
        ));  
		
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_order', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}