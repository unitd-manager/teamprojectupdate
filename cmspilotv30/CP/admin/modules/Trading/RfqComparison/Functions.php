<?
class CP_Admin_Modules_Trading_RfqComparison_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        // $modObj = $modules->getModuleObj('trading_rfqComparison');
        // $modules->registerModule($modObj, array(
        //     'moduleGroup' => 'trading'
        //    ,'tableName' => 'enquiry_product'
        //    ,'keyField' => 'enquiry_product_id'
        //    ,'title' => 'RFQ Comparison'
        //    ,'actBtnsList' => array('tradingRfqComparisonSave')
        //    ,'hideFromNav' => true
        // ));
    }

    //==================================================================//
    //==================================================================//
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $enquiry_id = $fn->getReqParam('enquiry_id');

        if ($enquiry_id != ''){
            $searchVar->sqlSearchVar[] = "ep.enquiry_id = {$enquiry_id}";
        }
        $searchVar->sortOrder = "qr.quote_request_code, qri.line_no";
    }

}