<?
class CP_Admin_Modules_Tradingus_Quote_Functions
{

    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingus_quote');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('quote_product')
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('tradingDuplicateQuote'
                                    ,'edit'
                                    ,'delete'
            )
           ,'actBtnsEdit' => array('tradingDuplicateQuote', 'save', 'apply')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingus_quote', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $status     = $fn->getReqParam('status');
        $enquiry_id = $fn->getReqParam('enquiry_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "q.quote_id = {$tv['record_id']}";
        }

        if ($status != ''){
            $searchVar->sqlSearchVar[] = "q.status = '{$status}'";
        }
        if ($enquiry_id != ''){
            $searchVar->sqlSearchVar[] = "q.enquiry_id = {$enquiry_id}";
        }
        $searchVar->sortOrder = "q.creation_date DESC";

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

        $statusArr = $cpCfg['m.trading.purchaseOrder.statusArr'];

        $sqlProduct = "
        SELECT product_id
              ,title
        FROM product
        ";
        $result = $db->sql_query($sqlProduct);
        $productArr = $dbUtil->getResultsetAsArrayForForm($result);

        $sqlSupplier = "
        SELECT DISTINCT c.company_id
              ,substr(c.company_name,1,5) as company_name
        FROM product_company pc
        LEFT JOIN company c ON (pc.company_id = c.company_id)
        LEFT JOIN product p ON (pc.product_id = p.product_id)
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            m_tradingus_quote_hasProductLinkForSplCase = '{$cpCfg['m.tradingsg.quote.hasProductLinkForSplCase']}'
        "));

        $result = $db->sql_query($sqlSupplier);
        $supplierArr = $dbUtil->getResultsetAsArrayForForm($result);

        $markUpTypeArr = $cpCfg['m.trading.quote.markUpTypeArr'];

        if($cpCfg['m.tradingsg.quote.displayNoDiscountInQuote'] == 1){
            if($cpCfg['m.tradingsg.quote.hasProductLinkForSplCase'] == 1){
                $linkObj = $inst->getLinksArrayObj('tradingus_quote', 'tradingsg_productLink', array(
                    'historyTableName' => 'quote_product'
                   ,'historyTableKeyField' => 'quote_product_id'
                   ,'hasPortalEdit' => 0
                   ,'hasPortalDelete' => 1
                   ,'linkingType' => 'grid'
                   ,'portalListLimit' => 100
                   ,'showLinkPanelInNew'  => 0
                   ,'showLinkPanelInEdit' => 1
                   ,'showRowSerialNo' => false
                   ,'showAnchorInLinkPortal' => false
                   ,'fieldlabel' => array('Item Code'
                                         ,'Name of the Item'
                                         ,'Department'
                                         ,'Supplier'
                                         ,'UP'
                                         ,'UOM'
                                         ,'Qty'
                                         ,'TUP'
                                         ,'Service Cost%'
                                         ,'USP'
                                         ,'Total Selling Price'
                                         ,'Remarks'
                                    )
                   ,'gridFieldTypeArray'  => array(
                        array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox')
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'dropdown', 'ddArr' => $supplierArr, 'useKey' => 1)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'value' => 1)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox')
                   )
                   , 'fieldClassArray' => array(
                         0 => ''
                       , 1 => 'w25p'
                       , 2 => 'w50'
                       , 3 => 'w100 client_id'
                       , 4 => 'al-right w50'
                       , 5 => 'w50'
                       , 6 => 'w50'
                       , 7 => 'al-right w50 totalCp'
                       , 8 => 'al-right w50 serviceCostSum'
                       , 9 => 'al-right w50'
                       , 10 => 'al-right w50 totalSp'
                       , 11 => 'w200'
                   )
                   ,'summaryFieldsArray' => array(
                        'total_cost_price'
                       ,'mark_up_amount'
                       ,'total_selling_price'
                    )
                ));
            } else {
                $linkObj = $inst->getLinksArrayObj('tradingus_quote', 'tradingsg_productLink', array(
                    'historyTableName' => 'quote_product'
                   ,'historyTableKeyField' => 'quote_product_id'
                   ,'hasPortalEdit' => 0
                   ,'hasPortalDelete' => 1
                   ,'linkingType' => 'grid'
                   ,'portalListLimit' => 100
                   ,'showLinkPanelInNew'  => 0
                   ,'showLinkPanelInEdit' => 1
                   ,'showRowSerialNo' => false
                   ,'showAnchorInLinkPortal' => false
                   ,'fieldlabel' => array('Item Code'
                                         ,'Name of the Item'
                                         ,'Group'
                                         ,'Supplier'
                                         ,'CP'
                                         ,'UOM'
                                         ,'Qty'
                                         ,'TUP'
                                         ,'Profit Type'
                                         ,'Profit'
                                         ,'SP'
                                         ,'Total Selling Price'
                                    )
                   ,'gridFieldTypeArray'  => array(
                        array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox')
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'dropdown', 'ddArr' => $supplierArr, 'useKey' => 1)
                       ,array('type' => 'textbox')
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'value' => 1)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'dropdown', 'ddArr' => $markUpTypeArr)
                       ,array('type' => 'textbox')
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox', 'editable' => 0)
                       ,array('type' => 'textbox')
                   )
                   , 'fieldClassArray' => array(
                         0 => ''
                       , 1 => 'w25p'
                       , 2 => 'w50'
                       , 3 => 'w100 client_id'
                       , 4 => 'al-right w50'
                       , 5 => 'w50'
                       , 6 => 'w50'
                       , 7 => 'al-right w50 totalCp'
                       , 8 => 'w50'
                       , 9 => 'al-right w50 serviceCostSum'
                       , 10 => 'al-right w50'
                       , 11 => 'al-right w50 totalSp'
                       , 12 => 'w200'
                   )
                   ,'summaryFieldsArray' => array(
                        'total_cost_price'
                       ,'mark_up_amount'
                       ,'total_selling_price'
                    )
                ));
            }
        } else{
        //----------------------APPLIED FOR TRADING DEMO
        /*
            $linkObj = $inst->getLinksArrayObj('tradingsg_quote', 'tradingsg_productLink', array(
            'historyTableName' => 'quote_product'
           ,'historyTableKeyField' => 'quote_product_id'
           ,'hasPortalEdit' => 0
           ,'hasPortalDelete' => 1
           ,'linkingType' => 'grid'
           ,'portalListLimit' => 100
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'showRowSerialNo' => false
           ,'showAnchorInLinkPortal' => false
           ,'fieldlabel' => array('Item Code'
                                 ,'Name of the Item'
                                 ,'Group'
                                 ,'Supplier'
                                 ,'UP'
                                 ,'UOM'
                                 ,'Qty'
                                 ,'TUP'
                                 ,'Discount%'
                                 ,'Profit%'
                                 ,'SP'
                                 ,'TSP'
                                 ,'Remarks'
                                 ,'Delete'
                            )
           ,'gridFieldTypeArray'  => array(
                                  array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox')
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'dropdown', 'ddArr' => $supplierArr, 'useKey' => 1)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'value' => 1)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox', 'editable' => 0)
                                 ,array('type' => 'textbox')
                                 ,array('type' => 'singleCheckbox')
                            )
           , 'fieldClassArray' => array(
                                  0 => ''
                                , 1 => 'w25p'
                                , 2 => 'w50'
                                , 3 => 'w100 client_id'
                                , 4 => 'al-right w50'
                                , 5 => 'w50'
                                , 6 => 'w50'
                                , 7 => 'al-right w50 totalCp'
                                , 8  => 'al-right w50 discountSum'
                                , 9 => 'al-right w50 serviceCostSum'
                                , 10 => 'al-right w50'
                                , 11 => 'al-right w50 totalSp'
                                , 12 => 'w200'
                            )
           ,'summaryFieldsArray' => array(
                                 'total_cost_price'
                                ,'mark_up_amount'
                                ,'discount_percentage_amount'
                                ,'total_selling_price'
                            )
            ));
        }
        $inst->registerLinksArray($linkObj);
        */
        $markUpTypeArr = $cpCfg['m.trading.quote.markUpTypeArr'];

        $linkObj = $inst->getLinksArrayObj('tradingus_quote', 'tradingsg_productLink', array(
            'historyTableName' => 'quote_product'
           ,'historyTableKeyField' => 'quote_product_id'
           ,'hasPortalEdit' => 0
           ,'hasPortalDelete' => 1
           ,'linkingType' => 'grid'
           ,'portalListLimit' => 100
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'showRowSerialNo' => false
           ,'showAnchorInLinkPortal' => false
           ,'fieldlabel' => array('Item Code'
                                 ,'Name of the Item'
                                 ,'Supplier'
                                 ,'CP'
                                 ,'UOM'
                                 ,'Qty'
                                 ,'History'
                                 ,'TCP'
                                 ,'Mark Up Type'
                                 ,'Mark Up'
                                 ,'SP'
                                 ,'Discount%'
                                 ,'TSP-Disc'
                                 ,'Delete'
                            )
           ,'gridFieldTypeArray'  => array(
                array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox')
               ,array('type' => 'dropdown', 'ddArr' => $supplierArr, 'useKey' => 1)
               ,array('type' => 'textbox')
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'value' => 1)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'dropdown', 'ddArr' => $markUpTypeArr, 'useKey' => 1)
               ,array('type' => 'textbox')
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'singleCheckbox')
           )
           , 'fieldClassArray' => array(
                 0 => 'w25'
               , 1 => 'w25p'
               , 2 => 'w100 client_id'
               , 3 => 'w50'
               , 4 => ''
               , 5 => 'w50'
               , 6 => 'view-history w50'
               , 7 => 'al-right w50 totalCp'
               , 8 => 'w100'
               , 9 => 'w25 serviceCostSum'
               , 10  => 'al-right w50'
               , 11 => 'al-right discountSum'
               , 12 => 'al-right w50 totalSp'
               , 13 => 'w25'
           )
           ,'summaryFieldsArray' => array(
                'total_cost_price'
               ,'discount_percentage_amount'
               ,'mark_up_amount'
               ,'total_selling_price'
            )
        ));
        }
        $inst->registerLinksArray($linkObj);

        //-------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('tradingus_quote', 'tradingsg_expenseLink', array(
            'historyTableName'      => 'expense'
           ,'historyTableKeyField'  => 'expense_id'
           ,'hasPortalEdit'         => 0
           ,'hasPortalDelete'       => 1
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 650
           ,'portalDialogHeight'    => 350
        ));
        $inst->registerLinksArray($linkObj);

        //-------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('tradingus_quote', 'tradingsg_purchaseOrderLink', array(
             'historyTableName' => 'po_product'
            ,'hasPortalEdit' => 0
            ,'hasPortalDetail' => 0
            ,'hasPortalDelete' => 0
            ,'hasPortalNew'=> 0
            ,'linkingType' => 'portal'
            ,'anchorFieldsArr' => array(
                'po_code' => $inst->getLinkAnchorObj(
                     'po_code'
                    ,'purchase_order_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )
            )
            ,'fieldlabel' => array(
                 'PO Code'
                ,'Supplier'
            )
        ));
        $inst->registerLinksArray($linkObj);
    }

    function setReportsArray($repInst) {

        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('trading_quote', 'quote');
        $arr = &$repInst->reportsArray['trading_quote']['quote'];
        $arr['jasperFileName'] = 'quote.jasper';
    }

    /**
     *
    */
    function gettradingusQuoteTradingsgProductLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $product_group_id   = $fn->getReqParam('product_group_id');

        $sqlProductGroup = "
        SELECT DISTINCT pg.product_group_id, pg.title
        FROM product_group pg
        LEFT JOIN (product p)        ON (pg.product_group_id = p.product_group_id)
        LEFT JOIN (quote_product qp) ON (p.product_id        = qp.product_id)
        ";

        /*<div class='float_right'>
            <input type='text' name='keyword' class='w100' value=''>
            <input type='submit' value='GO' class='button'>
          </div>*/

        $text = "

        <div>
            <select name='product_group_id' class='float_right m5'>
                <option value=''>Product Department</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProductGroup, $product_group_id)}
            </select>
        </div>
        ";

        return $text;
    }

}