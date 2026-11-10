<?
class CP_Admin_Modules_Tradingsg_Enquiry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_enquiry');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
        ));
    }
    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('dbUtil');

        //-------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('tradingsg_enquiry', 'tradingsg_quoteLink', array(
            'historyTableName' => 'enquiry_quote'
           ,'displayTitleFieldName' => "q.quote_code"
           ,'hasPortalEdit' => 0
           ,'hasPortalDetail' => 1
           ,'hasPortalDelete' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType' => 'portal'
           ,'anchorFieldsArr' => array(
               'quote_code' => $inst->getLinkAnchorObj(
                    'quote_code'
                   ,'quote_id'
                   ,false
                   ,''
                   ,array('showLinkInEdit' => true)
               )
           )
           ,'fieldlabel' => array('Quote Code'
                                 ,'Title'
                                 ,'Total Amount'
                            )
        ));
        $inst->registerLinksArray($linkObj);

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('tradingsg_enquiry', 'tradingsg_productGroupLink', array(
            'historyTableName'       => 'enquiry_product_group'
           ,'linkingType'            => 'modal'
        ));
        $inst->registerLinksArray($linkObj);


    }

}