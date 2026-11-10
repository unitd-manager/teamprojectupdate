<?
class CP_Admin_Modules_Trading_Product_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $count = 0;
        $rows  = '';

        foreach ($dataArray as $row){
            $url = $this->getInventoryUrl($row);
            $stock_qty = "<a href='{$url}'>{$row['stock_qty']}</a>";

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getGoToDetailText($count, $row['web_code'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['color_inside'])}
            {$listObj->getListDataCell($row['material'])}
            {$listObj->getListDataCell($stock_qty, 'center')}
            {$listObj->getListDataCell($row['status_stock'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";

            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Product Code', 'p.product_code')}
        {$listObj->getListHeaderCell('Web Code', 'p.web_code')}
        {$listObj->getListHeaderCell('Collection', 'p.collection_name')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Colour', 'p.color')}
        {$listObj->getListHeaderCell('Colour Inside', 'p.color_inside')}
        {$listObj->getListHeaderCell('Material', 'p.material')}
        {$listObj->getListHeaderCell('Stock Qty', '', 'txtCenter')}
        {$listObj->getListHeaderCell('Status', 'status_stock')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sectionType    = 'Product';
        $modCategory    = getCPModuleObj('webBasic_category');
        $sqlCategory    = $modCategory->model->getCategorySQLByType($sectionType);

        $expVl = array('sqlType' => 'OneField');
        $sqlCollection = $fn->getValueListSQL('Collection');

        $fieldset = "
        {$formObj->getTBRow('Product Name', 'title')}
        {$formObj->getTBRow('Product Code', 'product_code')}
        {$formObj->getDDRowBySQL('Collection Name', 'collection_name', $sqlCollection, '', $expVl)}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');

        $expVl = array('sqlType' => 'OneField');
        $sqlCollection = $fn->getValueListSQL('collection');
        $sqlUnit = $fn->getValueListSQL('productUnit');
        $sqlHardware = $fn->getValueListSQL('hardware');

        $fnModCat      = includeCPClass('ModuleFns', 'webBasic_category');
        $fnModSubCat   = includeCPClass('ModuleFns', 'webBasic_subCategory');
        $fnsModProduct = includeCPClass('ModuleFns', 'trading_product');

        $sectionType    = 'Product';
        $modCategory    = getCPModuleObj('webBasic_category');
        $sqlCategory    = $modCategory->model->getCategorySQLByType($sectionType);
        $expCategory    = array('detailValue' => $row['category_title']);

        $modSubCategory = getCPModuleObj('webBasic_subCategory');
        $sqlSubCategory = $modSubCategory->model->getSubCategorySQLByCategory($row['category_id']);
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $expNoEdit = array('isEditable' => 0);
        $url = $this->getInventoryUrl($row);
        $stock_qty = "<a href='{$url}'>{$row['stock_qty']}</a>";

        //{$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.statusArr'], $row['status'])}

        $fldTextArr[] = $formObj->getTBRow('H', 'dimension_h', $row['dimension_h']);
        $fldTextArr[] = $formObj->getTBRow('W', 'dimension_w', $row['dimension_w']);
//        $fldTextArr[] = $formObj->getTBRow('D', 'dimension_d', $row['dimension_d']);
//        {$formObj->getFCRow('Dimension',$fldTextArr)}

        $go_to_module = 'trading_catalog';
        $displayText = 'view catalog';
        if ($tv['module'] == 'trading_catalog') {
            $go_to_module = 'trading_product';
            $displayText = 'view product';
        }
        $expProduct = array('displayText' => $displayText);
        $productText = $fn->getRecordDetailLink($go_to_module, 'record_id',
                                                $row['product_id'], $expProduct);
        $expProd = array('extraHtml' => $productText);
        $fieldset1 = "
        {$formObj->getTBRow('Product Code', 'product_code', $row['product_code'], $expProd)}
        {$formObj->getTBRow('Web Code', 'web_code', $row['web_code'])}
        {$formObj->getTBRow('Product Name', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Collection Name', 'collection_name', $sqlCollection, $row['collection_name'], $expVl)}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowBySQL('UOM', 'unit', $sqlUnit, $row['unit'], $expVl)}
        {$formObj->getTBRow('UOM Quantity', 'unit_qty', $row['unit_qty'])}
        {$formObj->getTBRow('Dimension H', 'dimension_h', $row['dimension_h'])}
        {$formObj->getTBRow('Dimension W', 'dimension_w', $row['dimension_w'])}
        {$formObj->getTBRow('Dimension D', 'dimension_d', $row['dimension_d'])}
        {$formObj->getTBRow('CBM per pc', 'cbm_per_pc', $row['cbm_per_pc'])}
        {$formObj->getDDRowBySQL('Hardware', 'hardware', $sqlHardware, $row['hardware'], $expVl)}
        {$formObj->getTBRow('Material', 'material', $row['material'])}
        {$formObj->getTBRow('Colour', 'color', $row['color'])}
        {$formObj->getTBRow('Colour Inside', 'color_inside', $row['color_inside'])}
        {$formObj->getTBRow('Stock Qty', 'stock_qty', $stock_qty, $expNoEdit)}
        {$formObj->getTBRow('Status', 'status_stock', $row['status_stock'], $expNoEdit)}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getTBRow('Origin', 'origin', $row['origin'])}
        {$formObj->getTBRow('Dynasty', 'dynasty', $row['dynasty'])}
        {$formObj->getTBRow('Circa', 'circa', $row['circa'])}
        {$formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web'])}
        {$formObj->getTARow('Website Comments', 'website_comments', $row['website_comments'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Item Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getIssetParam($row, 'product_id');

        $rows = "";
        $rows = "
        {$displayLinkData->getLinkPortalMain('trading_product', 'trading_rfqItemsLink', 'Selected RFQs', $row)}
        {$displayLinkData->getLinkPortalMain('trading_product', 'trading_pricingTypeLink', 'Pricing', $row)}
        {$displayLinkData->getLinkPortalMain('trading_product', 'trading_inventoryLink', 'Inventory', $row)}
        ";

        $text = "
        {$media->getRightPanelMediaDisplay('Pictures', 'trading_product', 'picture', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'trading_product'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $subCatOptions  = '';
        $collection_name = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');
        $color = $fn->getReqParam('color');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $modCat = getCPModuleObj('webBasic_category');

        $SQLCat = $modCat->model->getCategorySQLByType('Product');

        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        $subCatText = '';
        if ($tv['category_id'] != '') {
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $SQLSubCat = $modSubCat->model->getSubCategorySQLByCategory($tv['category_id']);
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $tv['sub_category_id']);
        }
        if ($tv['lnkRoom'] != '') {
            $subCatText = "
            <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
            </td>
            ";
        }

        $sqlCollection = $fn->getValueListSQL('collection');
        $product = getCPModelObj('trading_product');
        $sqlColor = $product->getColorSQL();

        $text = "
        <td>
            <select name='collection_name'>
                <option value=''>Collection</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCollection, $collection_name)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='color'>
                <option value=''>Color</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.product.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    function getInventoryUrl($row) {
        $cpUtil = Zend_Registry::get('cpUtil');
        $url = $cpUtil->getUrl(array('module' => 'trading_inventory', 'product_id' => $row['product_id']));

        return $url;
    }

    /**
     *
     */
    function getEditCostBreakdown(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');

        $this->model->getCreateProductPricingTypeHistory($product_id);

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $SQL = "
        SELECT p.*
              ,qr.buy_currency AS pt_buy_currency
              ,qri.buy_unit_price AS pt_buy_unit_price
              ,qri.buy_unit_price_base AS pt_buy_unit_price_base
        FROM product p
        LEFT JOIN quote_request_items qri
               ON qri.product_id = p.product_id AND qri.product_selected = 1
        LEFT JOIN quote_request qr
               ON qr.quote_request_id = qri.quote_request_id
        WHERE p.product_id = {$product_id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expNoEdit = array('isEditable' => 0);

        $SQLCurr = $fn->getValueListSQL('currency');
        $expCurr = array('ddSQL' => $SQLCurr);

        //---------------------------------------//
        $buyCurrencyText = $row['pt_buy_currency'];
        $buyCurrencyText = $formObj->getDisplayFldObj('pt_buy_currency', $row['pt_buy_currency']);

        $baseCurr = '<b>' . $cpCfg['m.trading.companyCurrency'] . '</b>';
        $rowBuyCurr = $fnsModGrp->getCostingRowArr(
                          'Buy currency', $buyCurrencyText,
                          '', '', $baseCurr
                      );
        $rowBuyCurr = $fnsModGrp->getCostingTableRow($rowBuyCurr);

        //---------------------------------------//
        $unitBuyPriceText = $formObj->getDisplayFldObj('pt_buy_unit_price', $row['pt_buy_unit_price']);
        $unitBuyPriceBaseText = $formObj->getDisplayFldObj('pt_buy_unit_price_base', $row['pt_buy_unit_price_base']);
        $rowUnitBuyPrice = $fnsModGrp->getCostingRowArr(
                          'Unit buy price', $unitBuyPriceText,
                          '', '', $unitBuyPriceBaseText);
        $rowUnitBuyPrice = $fnsModGrp->getCostingTableRow($rowUnitBuyPrice);

        //---------------------------------------//
        $rowOtherCostLbl = $fnsModGrp->getCostingRowArr('', 'label', 'currency', 'amount', '');
        $rowOtherCostLbl = $fnsModGrp->getCostingTableRow($rowOtherCostLbl, 'th');

        //---------------------------------------//
        $otherCost1LblText = $formObj->getTextFldObj('pt_other_costs_1_label', $row['pt_other_costs_1_label']);
        $otherCost1CurrText = $formObj->getSelectFldObj('pt_other_costs_1_curr', $row['pt_other_costs_1_curr'],
                                                        '', $expCurr);
        $otherCost1Text = $formObj->getTextFldObj('pt_other_costs_1', $row['pt_other_costs_1']);
        $otherCost1BaseText = $formObj->getDisplayFldObj('pt_other_costs_1_base', $row['pt_other_costs_1_base']);
        $rowOtherCost1 = $fnsModGrp->getCostingRowArr('Other cost 1',
                                                 $otherCost1LblText,
                                                 $otherCost1CurrText,
                                                 $otherCost1Text,
                                                 $otherCost1BaseText);
        $rowOtherCost1 = $fnsModGrp->getCostingTableRow($rowOtherCost1);

        //---------------------------------------//
        $otherCost2LblText = $formObj->getTextFldObj('pt_other_costs_2_label', $row['pt_other_costs_2_label']);
        $otherCost2CurrText = $formObj->getSelectFldObj('pt_other_costs_2_curr', $row['pt_other_costs_2_curr'],
                                                        '', $expCurr);
        $otherCost2Text = $formObj->getTextFldObj('pt_other_costs_2', $row['pt_other_costs_2']);
        $otherCost2BaseText = $formObj->getDisplayFldObj('pt_other_costs_2_base', $row['pt_other_costs_2_base']);
        $rowOtherCost2 = $fnsModGrp->getCostingRowArr('Other cost 2',
                                                 $otherCost2LblText,
                                                 $otherCost2CurrText,
                                                 $otherCost2Text,
                                                 $otherCost2BaseText);
        $rowOtherCost2 = $fnsModGrp->getCostingTableRow($rowOtherCost2);

        //---------------------------------------//
        $otherCost3LblText = $formObj->getTextFldObj('pt_other_costs_3_label', $row['pt_other_costs_3_label']);
        $otherCost3CurrText = $formObj->getSelectFldObj('pt_other_costs_3_curr', $row['pt_other_costs_3_curr'],
                                                        '', $expCurr);
        $otherCost3Text = $formObj->getTextFldObj('pt_other_costs_3', $row['pt_other_costs_3']);
        $otherCost3BaseText = $formObj->getDisplayFldObj('pt_other_costs_3_base', $row['pt_other_costs_3_base']);
        $rowOtherCost3 = $fnsModGrp->getCostingRowArr('Other cost 3',
                                                 $otherCost3LblText,
                                                 $otherCost3CurrText,
                                                 $otherCost3Text,
                                                 $otherCost3BaseText);
        $rowOtherCost3 = $fnsModGrp->getCostingTableRow($rowOtherCost3);

        //---------------------------------------//
        $totalNetCostText = $formObj->getDisplayFldObj('pt_sell_unit_price_total_net_cost_base',
                                                       $row['pt_sell_unit_price_total_net_cost_base']);
        $rowTotalNetCost = $fnsModGrp->getCostingRowArr('Total net cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $totalNetCostText);
        $rowTotalNetCost = $fnsModGrp->getCostingTableRow($rowTotalNetCost);

        $tableRows = $rowBuyCurr
                   . $rowUnitBuyPrice
                   . $rowOtherCostLbl
                   . $rowOtherCost1
                   . $rowOtherCost2
                   . $rowOtherCost3
                   . $rowTotalNetCost;
        $tableNetCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $agentPercentText = $formObj->getTextFldObj('pt_agent_comm_percentage',
                                                    $row['pt_agent_comm_percentage']);
        $agentCommBaseText = $formObj->getDisplayFldObj('pt_agent_comm_base',
                                                        $row['pt_agent_comm_base']);
        $rowAgentPercent = $fnsModGrp->getCostingRowArr(
                           'Agent %', '', '',
                           $agentPercentText,
                           $agentCommBaseText);
        $rowAgentPercent = $fnsModGrp->getCostingTableRow($rowAgentPercent);

        //---------------------------------------//
        $qcPercentText = $formObj->getTextFldObj('pt_qc_comm_percentage',
                                                 $row['pt_qc_comm_percentage']);
        $qcCommBaseText = $formObj->getDisplayFldObj('pt_qc_comm_base',
                                                     $row['pt_qc_comm_base']);
        $rowQCPercent = $fnsModGrp->getCostingRowArr(
                           'QC %', '', '',
                           $qcPercentText,
                           $qcCommBaseText);
        $rowQCPercent = $fnsModGrp->getCostingTableRow($rowQCPercent);

        //---------------------------------------//
        $exFactoryCostText = $formObj->getDisplayFldObj('pt_sell_unit_price_ex_fact_base',
                                                        $row['pt_sell_unit_price_ex_fact_base']);
        $rowExFactoryCost = $fnsModGrp->getCostingRowArr('Ex factory cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $exFactoryCostText);
        $rowExFactoryCost = $fnsModGrp->getCostingTableRow($rowExFactoryCost);

        $tableRows = $rowAgentPercent
                   . $rowQCPercent
                   . $rowExFactoryCost;
        $tableExFactoryCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $localChargesPercentText = $formObj->getTextFldObj('pt_local_charges_percentage',
                                                    $row['pt_local_charges_percentage']);
        $localChargesBaseText = $formObj->getDisplayFldObj('pt_local_charges_base',
                                                        $row['pt_local_charges_base']);
        $rowLocalCharges = $fnsModGrp->getCostingRowArr(
                           'Local charges %', '', '',
                           $localChargesPercentText,
                           $localChargesBaseText);
        $rowLocalCharges = $fnsModGrp->getCostingTableRow($rowLocalCharges);

        //---------------------------------------//
        $fobCostText = $formObj->getDisplayFldObj('pt_sell_unit_price_fob_base',
                                                  $row['pt_sell_unit_price_fob_base']);
        $rowFobCostCost = $fnsModGrp->getCostingRowArr('FOB cost',
                                                 '',
                                                 '',
                                                 '',
                                                 $fobCostText);
        $rowFobCostCost = $fnsModGrp->getCostingTableRow($rowFobCostCost);

        $tableRows = $rowLocalCharges
                   . $rowFobCostCost;
        $tableFobCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $shippingCostPercentText = $formObj->getTextFldObj('pt_shipping_cost_percentage',
                                                           $row['pt_shipping_cost_percentage']);
        $shippingCostBaseText = $formObj->getDisplayFldObj('pt_shipping_cost_base',
                                                           $row['pt_shipping_cost_base']);
        $rowShippingCost = $fnsModGrp->getCostingRowArr(
                           'Shipping %', '', '',
                           $shippingCostPercentText,
                           $shippingCostBaseText);
        $rowShippingCost = $fnsModGrp->getCostingTableRow($rowShippingCost);

        //---------------------------------------//
        $insuranceCostPercentText = $formObj->getTextFldObj('pt_insurance_cost_percentage',
                                                            $row['pt_insurance_cost_percentage']);
        $insuranceCostBaseText = $formObj->getDisplayFldObj('pt_insurance_cost_base',
                                                            $row['pt_insurance_cost_base']);
        $rowInsuranceCost = $fnsModGrp->getCostingRowArr(
                           'Insurance %', '', '',
                           $insuranceCostPercentText,
                           $insuranceCostBaseText);
        $rowInsuranceCost = $fnsModGrp->getCostingTableRow($rowInsuranceCost);

        //---------------------------------------//
        $cifCostText = $formObj->getDisplayFldObj('pt_sell_unit_price_cif_base',
                                                  $row['pt_sell_unit_price_cif_base']);
        $rowCifCost = $fnsModGrp->getCostingRowArr('CIF cost',
                                              '',
                                              '',
                                              '',
                                              $cifCostText);
        $rowCifCost = $fnsModGrp->getCostingTableRow($rowCifCost);

        $tableRows = $rowShippingCost
                   . $rowInsuranceCost
                   . $rowCifCost;
        $tableCifCost = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $taxPercentText = $formObj->getDisplayFldObj('pt_tax_percentage',
                                                     $row['pt_tax_percentage']);
        $taxPercentBaseText = $formObj->getDisplayFldObj('pt_tax_amount_base',
                                                         $row['pt_tax_amount_base']);
        $rowTaxPercent = $fnsModGrp->getCostingRowArr('VAT %',
                                                 '',
                                                 '',
                                                 $taxPercentText,
                                                 $taxPercentBaseText);
        $rowTaxPercent = $fnsModGrp->getCostingTableRow($rowTaxPercent);

        $tableRows = $rowTaxPercent;
        $tableVAT = $fnsModGrp->getCostingTable($tableRows);

        //---------------------------------------//
        //---------------------------------------//
        $tablePricingType = $this->getPricingTypes($product_id);

        //---------------------------------------//
        $btnText = "
        <div class='floatbox'>
            <a href='#' class='button float_right btnSaveCostBreakdown'>Save & Close</a>
        </div>
        ";
        $text = "
        {$btnText}
        <form id='costBreakdown' class='yform columnar mt5 mb5' method='post'>
            <div class='costs'>
                <div>{$tableNetCost}</div>
                <div class='ex-factory-cost'>{$tableExFactoryCost}</div>
                <div class='fob-cost'>{$tableFobCost}</div>
                <div class='cif-cost'>{$tableCifCost}</div>
                <div class='tax-cost'>{$tableVAT}</div>
            </div>
            <div class='markups'>
                <div class='pricing-type'>{$tablePricingType}</div>
            </div>
        </form>
        {$btnText}
        ";

        return $text;
    }

    function getPricingTypes($product_id) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $fmg = includeCPClass('ModGroup', 'Trading', 'Functions');

        $rowProd = $fn->getRecordRowByID('product', 'product_id', $product_id);
        $costingArr = array(
             'pt_sell_unit_price_ex_fact_base' => $rowProd['pt_sell_unit_price_ex_fact_base']
            ,'pt_sell_unit_price_fob_base' => $rowProd['pt_sell_unit_price_fob_base']
            ,'pt_sell_unit_price_cif_base' => $rowProd['pt_sell_unit_price_cif_base']
            ,'pt_tax_percentage' => $rowProd['pt_tax_percentage']
        );
        $dataArray = $this->model->getPricingTypesArr($product_id, $costingArr);

        $rows = '';
        foreach ($dataArray as $row){
            $sellUnitPriceBase = '';
            $fldName = "sell_unit_price_base[{$row['pricing_type_id']}]";
            if ($row['record_type'] == 'has_tax') { //has VAT
                $sellUnitPriceBase = $formObj->getDisplayFldObj($fldName,
                                                             $row['sell_unit_price_base']);
            } else {
                $sellUnitPriceBase = $formObj->getTextFldObj($fldName,
                                                             $row['sell_unit_price_base']);
            }

            $cls_pricing_type_id = "pt_{$row['pricing_type_id']}";

            $ex_fact_markup = $fmg->getRoundAmount($row['ex_fact_markup']);
            $fob_markup = $fmg->getRoundAmount($row['fob_markup']);
            $cif_markup = $fmg->getRoundAmount($row['cif_markup']);

            $calculated_cost = $fmg->getRoundAmount($row['calculated_cost']);
            if ($row['record_type'] == 'no_tax' || $row['record_type'] == 'has_tax') {
                $calculated_cost = '';
            }

            $rows .= "
            <tr class='pt {$cls_pricing_type_id}' record_type='{$row['record_type']}'>
            <th class='col0'>{$row['pricing_type']}</th>
            <td class='ex_fact_markup'><span>{$ex_fact_markup}</span> %</td>
            <td class='fob_markup'><span>{$fob_markup}</span> %</td>
            <td class='cif_markup'><span>{$cif_markup}</span> %</td>
            <td class='calculated_cost'>{$calculated_cost}</td>
            <td class='col4 sales_unit_price_base'>{$sellUnitPriceBase}</td>
            </tr>
            ";
        }

        $text = "
        <table class='costing'>
        <tr>
            <th></th>
            <th>ex fac. markup</th>
            <th>FOB markup</th>
            <th>CIF markup</th>
            <th>calc. cost</th>
            <th>&nbsp;</th>
        </tr>
        {$rows}
        </table>
        ";

        return $text;

    }

    function getProductGuidePrice($product_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT pt.pricing_type
              ,ppt.sell_unit_price_base
        FROM pricing_type pt
        JOIN product_pricing_type ppt ON ppt.pricing_type_id = pt.pricing_type_id
        WHERE ppt.product_id = {$product_id}
        ORDER BY pt.sort_order
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
            <th class='col0'>{$row['pricing_type']}</th>
            <td class='col1'>{$row['sell_unit_price_base']}</td>
            <td class='col4'></td>
            <td class='col4'></td>
            <td class='col4'></td>
            </tr>
            ";
        }

        $text = "
        <table class='costing'>
        <tr>
            <th>Pricing Type</th>
            <th>Guide price from product</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        {$rows}
        </table>
        ";

        return $text;

    }


}
