<?
class CPL_Admin_Widgets_Project_ProjectCostingSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML($project_id='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }
        //print $project_id.'dsfdssa';

        $SQL = "
        SELECT c.*
        FROM `costing_summary` c
        WHERE c.project_id = {$project_id}
        ORDER BY c.costing_summary_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        if ($numRows == 0){
            $actionButton = "
            <div class='float_left btn btn-primary mb10'>
                <a class='addCostingSummary' project_id={$project_id}>Add Costing Summary</a>
            </div>
            ";
        } else {
            $actionButton = "
            <div class='float_left btn btn-primary mb10'>
                <a class='editCostingSummary' project_id={$project_id} costing_summary_id={$row['costing_summary_id']}>Edit Costing Summary</a>
            </div>
            ";            
        }

        $expNoEdit  = array('isEditable' => 0);

        $SQL1 = "
        SELECT SUM(po.qty * po.cost_price) AS total_material_price
        FROM po_product po
        LEFT JOIN (purchase_order p) ON (p.purchase_order_id = po.purchase_order_id)
        WHERE p.project_id = {$project_id}
        ";
        $result1  = $db->sql_query($SQL1);
        $row1 = $db->sql_fetchrow($result1);

        $sql2 = "
        SELECT SUM(cs.amount) AS transport_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Transport Charges'
          AND cs.project_id = {$project_id}
        ";
        $result2 = $db->sql_query($sql2);  
        $row2 = $db->sql_fetchrow($result2);

        $sql3 = "
        SELECT SUM(cs.amount) AS salesman_commission
        FROM actual_costing_summary cs
        WHERE cs.title = 'Salesman Commission'
          AND cs.project_id = {$project_id}
        ";
        $result3 = $db->sql_query($sql3);
        $row3 = $db->sql_fetchrow($result3);

        $sql4 = "
        SELECT SUM(cs.amount) AS finance_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Finance Charges'
          AND cs.project_id = {$project_id}
        ";
        $result4 = $db->sql_query($sql4);
        $row4 = $db->sql_fetchrow($result4);

        $sql5 = "
        SELECT SUM(cs.amount) AS office_overheads
        FROM actual_costing_summary cs
        WHERE cs.title = 'Office Overheads'
          AND cs.project_id = {$project_id}
        ";
        $result5 = $db->sql_query($sql5);
        $row5 = $db->sql_fetchrow($result5);

        $sql6 = "
        SELECT SUM(cs.amount) AS other_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Other Charges'
          AND cs.project_id = {$project_id}
        ";
        $result6 = $db->sql_query($sql6);
        $row6 = $db->sql_fetchrow($result6);

        $sql7 = "
        SELECT SUM(cs.amount) AS total_labour_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Total Labour Charges'
          AND cs.project_id = {$project_id}
        ";
        $result7 = $db->sql_query($sql7);
        $row7 = $db->sql_fetchrow($result7);

        $total_cost = $row1['total_material_price'] + $row2['transport_charges'] + $row3['salesman_commission'] + $row4['finance_charges'] + $row5['office_overheads'] + $row6['other_charges'];

        $colorTM = 'green';
        $colorTC = 'green';
        $colorLC = 'green';
        $colorTLC = 'green';
        $colorSC = 'green';
        $colorFC = 'green';
        $colorOO = 'green';
        $colorOC = 'green';
        $colorTA = 'green';

        $arrowTM = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTLC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowLC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowSC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowFC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowOO = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowOC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTA = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";

        $total_material_price_cal = ($row['total_material_price'] * $cpCfg['projectReportPercentage']) / 100;
        $total_material_price_calc = $row['total_material_price'] - $total_material_price_cal;
        if($row['total_material_price'] < $row1['total_material_price']) {
            $colorTM = 'red';
            $arrowTM = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row1['total_material_price'] > $total_material_price_calc){
            $colorTM = 'orange';
            $arrowTM = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $transport_charges_cal = ($row['transport_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $transport_charges_calc = $row['transport_charges'] - $transport_charges_cal;
        if($row['transport_charges'] < $row2['transport_charges']) {
            $colorTC = 'red';
            $arrowTC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row2['transport_charges'] > $transport_charges_calc){
            $colorTC = 'orange';
            $arrowTC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }
        /*if($row['total_labour_charges'] < $row1['total_labour_charges']) {
            $colorTLC = 'red';
        }*/
        $total_labour_charges_cal = ($row['total_labour_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $total_labour_charges_calc = $row['total_labour_charges'] - $total_labour_charges_cal;
        if($row['total_labour_charges'] < $row7['total_labour_charges']) {
            $colorLC = 'red';
            $arrowLC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row7['total_labour_charges'] > $total_labour_charges_calc){
            $colorLC = 'orange';
            $arrowLC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $salesman_commission_cal = ($row['salesman_commission'] * $cpCfg['projectReportPercentage']) / 100;
        $salesman_commission_calc = $row['salesman_commission'] - $salesman_commission_cal;
        if($row['salesman_commission'] < $row3['salesman_commission']) {
            $colorSC = 'red';
            $arrowSC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row3['salesman_commission'] > $salesman_commission_calc){
            $colorSC = 'orange';
            $arrowSC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $finance_charges_cal = ($row['finance_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $finance_charges_calc = $row['finance_charges'] - $finance_charges_cal;
        if($row['finance_charges'] < $row4['finance_charges']) {
            $colorFC = 'red';
            $arrowFC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row4['finance_charges'] > $finance_charges_calc){
            $colorFC = 'orange';
            $arrowFC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $office_overheads_cal = ($row['office_overheads'] * $cpCfg['projectReportPercentage']) / 100;
        $office_overheads_calc = $row['office_overheads'] - $office_overheads_cal;
        if($row['office_overheads'] < $row5['office_overheads']) {
            $colorOO = 'red';
            $arrowOO = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row5['office_overheads'] > $office_overheads_calc){
            $colorOO = 'orange';
            $arrowOO = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $other_charges_cal = ($row['other_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $other_charges_calc = $row['other_charges'] - $other_charges_cal;
        if($row['other_charges'] < $row6['other_charges']) {
            $colorOC = 'red';
            $arrowOC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($row6['other_charges'] > $other_charges_calc){
            $colorOC = 'orange';
            $arrowOC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $total_cost_cal = ($row['total_cost'] * $cpCfg['projectReportPercentage']) / 100;
        $total_cost_calc = $row['total_cost'] - $total_cost_cal;
        if($row['total_cost'] < $total_cost) {
            $colorTA = 'red';
            $arrowTA = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='22'/>";
        } else if ($total_cost > $total_cost_calc){
            $colorTA = 'orange';
            $arrowTA = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='22'/>";
        }

        $total_material_price = number_format($row1['total_material_price'],2);
        $transport_charges = number_format($row2['transport_charges'],2);
        $salesman_commission = number_format($row3['salesman_commission'],2);
        $total_labour_charges = number_format($row7['total_labour_charges'],2);
        $finance_charges = number_format($row4['finance_charges'],2);
        $office_overheads = number_format($row5['office_overheads'],2);
        $other_charges = number_format($row6['other_charges'],2);
        $total_cost = number_format($total_cost,2);

        $totalCostDisplay = '';
        $profitDisplay = '';
        $totalCostTDDisplay = '';
        if ($_SESSION['userGroupName'] != 'Projects') {
            $totalCostDisplay = "
            <th>Total Cost : {$row['total_cost']} <span style=color:{$colorTA};>({$total_cost})</span>{$arrowTA}</th>
            ";
            $profitDisplay = "
            <th>Profit Margin : {$row['profit_percentage']}% ({$row['profit']})</th>
            ";
            $totalCostTDDisplay = "
            <td>{$formObj->getTBRow('TOTAL COST', 'total_cost', $row['total_cost'].' <span style=color:'.$colorTA.';>('.$total_cost.')</span>'.$arrowTA, $expNoEdit)}</td>
            ";
        }

        if ($_SESSION['userGroupName'] == 'Super Administrator' || $_SESSION['userGroupName'] == 'Super Admin') {
            $costingSummaryHeader = "
            <thead>
                <tr>
                  <th align='left' class='rightPanelHeading'>
                    Costing Summary
                  </th>
                  {$totalCostDisplay}
                  <th>Po Price (S$ W/o GST) : {$row['po_price']}</th>
                  <th>Invoiced Price (S$ W/o GST) : {$row['invoiced_price']}</th>
                  {$profitDisplay}
                </tr>
            </thead>
            ";
        } else {
            $costingSummaryHeader = "
            <thead>
                <tr>
                  <th align='left' class='rightPanelHeading'>
                    Costing Summary
                  </th>
                  {$totalCostDisplay}
                </tr>
            </thead>
            ";
        }
        
        $text = "
        <div id='costSumPortal' class='linkPortalWrapper table-responsive'>
          <table class ='list'>
              {$costingSummaryHeader}
          </table>
          <table class =''>
                <tr>
                    <td>{$formObj->getTBRow('Total Material', 'total_material_price', $row['total_material_price'].' <span style=color:'.$colorTM.';>('.$total_material_price.')</span>'.$arrowTM, $expNoEdit)}</td>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Transport Charges', 'transport_charges', $row['transport_charges'].' <span style=color:'.$colorTC.';>('.$transport_charges.')</span>'.$arrowTC, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Transport Charges'>Add</a>
                        </div>
                    </td>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Total Labour Charges', 'total_labour_charges', $row['total_labour_charges'].' <span style=color:'.$colorLC.';>('.$total_labour_charges.')</span>'.$arrowLC, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Total Labour Charges'>Add</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Salesman Commission', 'salesman_commission', $row['salesman_commission'].' <span style=color:'.$colorSC.';>('.$salesman_commission.')</span>'.$arrowSC, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Salesman Commission'>Add</a>
                        </div>
                    </td>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Finance Charges', 'finance_charges', $row['finance_charges'].' <span style=color:'.$colorFC.';>('.$finance_charges.')</span>'.$arrowFC, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Finance Charges'>Add</a>
                        </div>
                    </td>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Office Overheads', 'office_overheads', $row['office_overheads'].' <span style=color:'.$colorOO.';>('.$office_overheads.')</span>'.$arrowOO, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Office Overheads'>Add</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class='float_left mr20'>
                            {$formObj->getTBRow('Other Charges', 'other_charges', $row['other_charges'].' <span style=color:'.$colorOC.';>('.$other_charges.')</span>'.$arrowOC, $expNoEdit)}
                        </div>
                        <div class='float_left mr20'>
                            <a class='addActualCharges' project_id={$project_id} title='Other Charges'>Add</a>
                        </div>
                    </td>
                    {$totalCostTDDisplay}
                </tr>
          </table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getCreateCostingSummary() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $project_id = $fn->getReqParam('project_id');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $sketch       = "<input type='text' value='' id='sketch' class='text costingSummarySketch' name='sketch[]'>";
        $supplier     = "<select name='supplier_id[]' class='costingSummarySupplier'>
                            <option value=''>Select</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
                        </select>";
        $quantity     = "<input type='text' value='' id='quantity' class='text costingSummaryQuantity' name='quantity[]'>";
        $unit         = "<input type='text' value='' id='unit' class='text costingSummaryUnit' name='unit[]'>";
        $unit_price   = "<input type='text' value='' id='unit_price' class='text costingSummaryUnitPrice' name='unit_price[]'>";
        $total_cost   = "<input type='text' value='' id='amount' class='text costingSummaryAmount' name='amount[]'>";
        $remarks      = "<textarea value='' id='remarks' class='text costingSummaryRemarks' name='remarks[]'></textarea>";
        $clear        = "<td class='text'><a class='clearcostingSummary'><u>Clear</u></a></td>";
        
        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";

        $subCon = "
        <input type='text' value='' id='subCon' placeholder='Please type and select' class='text subConTitle' name='sub_con_name[]'>
        <input type='hidden' name='sub_con_id[]' class='sub_con_id_hidden' value=''>
        ";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
        </tr>
        ";

        $newRow = "
        <a class='addRow btn btn-primary mb10' project_id='{$project_id}'>Add Line Item</a>
        <!--<a class='addMoreDetailsCostingRow btn btn-success ml10 mb10'>(+) Add More Details</a>-->
        ";

        $expdisMargTab = array("tabindex" => "-1");
        $expdisTab     = array("tabindex" => "-1", "fldPrefix" => "$");
        $expDollar     = array("fldPrefix" => "$");

        $formActionNewSupplier = "index.php?widget=project_projectCostingSummary&_spAction=addNewSupplier&showHTML=0";
        $addNewSupplier = "
        <a href='{$formActionNewSupplier}' class='addNewSupplierPopup'>New</a>
        ";

        $formActionNewProduct = "index.php?widget=project_projectCostingSummary&_spAction=AddNewProductMaster&showHTML=0";
        $addNewProduct = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup'>New</a>
        ";

        $header ="
        <tr><td colspan='8'>
            {$newRow}
            <div class='row'>
                <div class='col-md-12'>
                    <div class='linkPortalWrapper col-md-12 noPadding'>
                        <div class='header col-md-12 noPadding' expanded='1'>
                            <div class='floatbox'></div>
                        </div>
                        <div class='linkPortalDataWrapper col-md-12 noPadding'>
                            <table width='100%'>
                                <tr>
                                    <td>{$formObj->getTBRow('PO NO.', 'po_code')}</td>
                                    <td>{$formObj->getTBRow('Our Invoice No.', 'invoice_code')}</td>
                                    <td>{$formObj->getDateRow('Delivery Date', 'delivery_date')}</td>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('No. of Worker Used', 'no_of_worker_used')}</td>
                                    <td>{$formObj->getTBRow('No. of Days Worked', 'no_of_days_worked')}</td>
                                    <td>{$formObj->getTBRow('Labout Rates Per Day', 'labour_rates_per_day', '', $expDollar)}</td>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('Po Price (S$ W/o GST)', 'po_price', '', $expDollar)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Invoiced Price (S$ W/o GST)', 'invoiced_price', '', $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Profit Margin %', 'profit_percentage', '', $expdisMargTab)}</td>
                                </tr>
                                <tr>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Po Price (S$ With GST)', 'po_price_with_gst', '', $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Invoiced Price (S$ With GST)', 'invoiced_price_with_gst', '', $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Profit Margin', 'profit', '', $expdisTab)}</td>
                                    <input type='hidden' name='gst_percentage' value='{$cpCfg['cp.gstPercentage']}' />
                                </tr>
                            </table>
                         </div>    
                    </div>
                </div>
            </div>
        </td></tr>
        <tr>
            <td colspan='8'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='linkPortalWrapper col-md-12 noPadding'>
                            <div class='header col-md-12 noPadding' expanded='1'>
                                <div class='floatbox'></div>
                            </div>
                            <div class='linkPortalDataWrapper col-md-12 noPadding'>
                                <table width='100%'>
                                    <tr>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Total Material', 'total_material_price', '', $expdisTab)}</td>
                                        <td>{$formObj->getTBRow('Transport Charges %', 'transport_charges_percentage')}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Transport Charges', 'transport_charges', '', $expdisTab)}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Total Labour Charges', 'total_labour_charges', '', $expdisTab)}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Salesman Commission %', 'salesman_commission_percentage')}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Salesman Commission', 'salesman_commission', '', $expdisTab)}</td>
                                        <td>{$formObj->getTBRow('Finance Charges %', 'finance_charges_percentage')}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Finance Charges', 'finance_charges', '', $expdisTab)}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Office Overheads %', 'office_overheads_percentage')}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Office Overheads', 'office_overheads', '', $expdisTab)}</td>
                                        <td class='totalCostField'>{$formObj->getTBRow('Other Charges', 'other_charges', '', $expDollar)}</td>
                                        <td class='totalCostValue disabledInputReadable' colspan='2'>{$formObj->getTBRow('TOTAL COST', 'total_cost', '', $expdisTab)}</td>
                                    </tr>
                                </table>
                             </div>    
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Materials {$addNewProduct}</th>
            <th class='txtCenter'>Supplier {$addNewSupplier}</th>
            <th class='txtCenter'>Sub-Con</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Qty</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtCenter'>Total Cost</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=project_projectCostingSummary&_spAction=costingSummarySubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $text = "
        <form id='costingSummaryForm' class='yform columnar costingSummaryForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box1", '', $expEdit)}</div>
            <table class='thinlist'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddLineItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id = $fn->getReqParam('project_id');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $sketch       = "<input type='text' value='' id='sketch' class='text costingSummarySketch' name='sketch[]'>";
        $supplier = "<select name='supplier_id[]' class='costingSummarySupplier'>
                        <option value=''>Select</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
                    </select>";
        $quantity    = "<input type='text' value='' id='quantity' class='text costingSummaryQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text costingSummaryUnit' name='unit[]'>";
        $unit_price      = "<input type='text' value='' id='unit_price' class='text costingSummaryUnitPrice' name='unit_price[]'>";
        $total_cost  = "<input type='text' value='' id='amount' class='text costingSummaryAmount' name='amount[]'>";
        $remarks     = "<textarea value='' id='remarks' class='text costingSummaryRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a class='clearcostingSummary'><u>Clear</u></a></td>";
        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $subCon = "
        <input type='text' value='' id='subCon' placeholder='Please type and select' class='text subConTitle' name='sub_con_name[]'>
        <input type='hidden' name='sub_con_id[]' class='sub_con_id_hidden' value=''>
        ";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$supplier}</td>
            <td>{$subCon}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td align='right'>{$unit_price}</td>
            <td align='right'>{$total_cost}</td>
            {$clear}
            <input type='hidden' name='costing_summary_history_id[]' class='costing_summary_history_id_hidden' value=''>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getEditCostingSummary() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $rows = '';

        $project_id = $fn->getReqParam('project_id');
        $costing_summary_id = $fn->getReqParam('costing_summary_id');
        $row        = $fn->getRecordRowById('costing_summary', 'costing_summary_id', $costing_summary_id);

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $sqlCsh = "
        SELECT *
        FROM costing_summary_history
        WHERE costing_summary_id = {$costing_summary_id}
        ";
        $resultCsh  = $db->sql_query($sqlCsh);
        $numRowsCsh = $db->sql_numrows($resultCsh);
        while ($rowCsh = $db->sql_fetchrow($resultCsh)) {
            $rowProduct = $fn->getRecordRowById('product', 'product_id', $rowCsh['product_id']);
            $rowSubCon = $fn->getRecordRowById('sub_con', 'sub_con_id', $rowCsh['sub_con_id']);

            $sketch = "<input type='text' value='{$rowCsh['sketch']}' id='sketch' class='text costingSummarySketch' name='sketch[]'>";
            $supplier = "<select name='supplier_id[]' class='costingSummarySupplier'>
                            <option value=''>Select</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $rowCsh['supplier_id'])}
                        </select>";
            $quantity = "<input type='text' value='{$rowCsh['quantity']}' id='quantity' class='text costingSummaryQuantity' name='quantity[]'>";
            $unit_price      = "<input type='text' value='{$rowCsh['unit_price']}' id='unit_price' class='text costingSummaryUnitPrice' name='unit_price[]'>";
            $total_cost  = "<input type='text' value='{$rowCsh['amount']}' id='amount' class='text costingSummaryAmount' name='amount[]'>";
            $clear = "<td class='text'><a class='clearcostingSummary'><u>Clear</u></a></td>";
            $product = "
            <input type='text' value='{$rowProduct['title']}' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
            <input type='hidden' name='product_id[]' class='product_id_hidden' value='{$rowCsh['product_id']}'>
            ";
            $subCon = "
            <input type='text' value='{$rowSubCon['company_name']}' id='subCon' placeholder='Please type and select' class='text subConTitle' name='sub_con_name[]'>
            <input type='hidden' name='sub_con_id[]' class='sub_con_id_hidden' value='{$rowCsh['sub_con_id']}'>
            ";
            $unit = "<input type='text' value='{$rowCsh['unit']}' id='unit' class='text costingSummaryUnit' name='unit[]'>";

            $rows .= "
            <tr>
                <td>{$product}</td>
                <td>{$supplier}</td>
                <td>{$subCon}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td align='right'>{$unit_price}</td>
                <td align='right'>{$total_cost}</td>
                <td class='text'><a class='clearcostingSummary'><u>Clear</u></a></td>
                <input type='hidden' name='costing_summary_history_id[]' class='costing_summary_history_id_hidden' value='{$rowCsh['costing_summary_history_id']}'>
            </tr>
            ";
        }

        $newRow = "
        <a class='addRow btn btn-primary mb10'>Add Line Item</a>
        <!--<a class='addMoreDetailsCostingRow btn btn-success ml10 mb10'>(+) Add More Details</a>-->
        ";

        $expdisMargTab = array("tabindex" => "-1");
        $expdisTab = array("tabindex" => "-1", "fldPrefix" => "$");
        $expDollar = array("fldPrefix" => "$");
        
        $formActionNewSupplier = "index.php?widget=project_projectCostingSummary&_spAction=addNewSupplier&showHTML=0";
        $addNewSupplier = "
        <a href='{$formActionNewSupplier}' class='addNewSupplierPopup'>New</a>
        ";

        $formActionNewProduct = "index.php?widget=project_projectCostingSummary&_spAction=AddNewProductMaster&showHTML=0";
        $addNewProduct = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup'>New</a>
        ";

        //hideMoreCostingDetails
        $header ="
        <tr><td colspan='8'>
            {$newRow}
            <div class='row'>
                <div class='col-md-12'>
                    <div class='linkPortalWrapper col-md-12 noPadding'>
                        <div class='header col-md-12 noPadding' expanded='1'>
                            <div class='floatbox'></div>
                        </div>
                        <div class='linkPortalDataWrapper col-md-12 noPadding'>
                            <table width='100%'>
                                <tr>
                                    <td>{$formObj->getTBRow('PO NO.', 'po_code', $row['po_code'])}</td>
                                    <td>{$formObj->getTBRow('Our Invoice No.', 'invoice_code', $row['invoice_code'])}</td>
                                    <td>{$formObj->getDateRow('Delivery Date', 'delivery_date', $row['delivery_date'])}</td>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('No. of Worker Used', 'no_of_worker_used', $row['no_of_worker_used'])}</td>
                                    <td>{$formObj->getTBRow('No. of Days Worked', 'no_of_days_worked', $row['no_of_days_worked'])}</td>
                                    <td>{$formObj->getTBRow('Labout Rates Per Day', 'labour_rates_per_day', $row['labour_rates_per_day'], $expDollar)}</td>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('Po Price (S$ W/o GST)', 'po_price', $row['po_price'], $expDollar)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Invoiced Price (S$ W/o GST)', 'invoiced_price', $row['invoiced_price'], $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Profit Margin %', 'profit_percentage', $row['profit_percentage'], $expdisMargTab)}</td>
                                </tr>
                                <tr>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Po Price (S$ With GST)', 'po_price_with_gst', $row['po_price_with_gst'], $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Invoiced Price (S$ With GST)', 'invoiced_price_with_gst', $row['invoiced_price_with_gst'], $expdisTab)}</td>
                                    <td class='disabledInputReadable'>{$formObj->getTBRow('Profit Margin', 'profit', $row['profit'], $expdisTab)}</td>
                                    <input type='hidden' name='gst_percentage' value='{$cpCfg['cp.gstPercentage']}' />
                                </tr>
                            </table>
                         </div>    
                    </div>
                </div>
            </div>
        </td></tr>
        <tr>
            <td colspan='8'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='linkPortalWrapper col-md-12 noPadding'>
                            <div class='header col-md-12 noPadding' expanded='1'>
                                <div class='floatbox'></div>
                            </div>
                            <div class='linkPortalDataWrapper col-md-12 noPadding'>
                                <table width='100%'>
                                    <tr>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Total Material', 'total_material_price', $row['total_material_price'], $expdisTab)}</td>
                                        <td>{$formObj->getTBRow('Transport Charges %', 'transport_charges_percentage', $row['transport_charges_percentage'])}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Transport Charges', 'transport_charges', $row['transport_charges'], $expdisTab)}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Total Labour Charges', 'total_labour_charges', $row['total_labour_charges'], $expdisTab)}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Salesman Commission %', 'salesman_commission_percentage', $row['salesman_commission_percentage'])}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Salesman Commission', 'salesman_commission', $row['salesman_commission'], $expdisTab)}</td>
                                        <td>{$formObj->getTBRow('Finance Charges %', 'finance_charges_percentage', $row['finance_charges_percentage'])}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Finance Charges', 'finance_charges', $row['finance_charges'], $expdisTab)}</td>
                                    </tr>
                                    <tr>
                                        <td>{$formObj->getTBRow('Office Overheads %', 'office_overheads_percentage', $row['office_overheads_percentage'])}</td>
                                        <td class='totalCostField disabledInputReadable'>{$formObj->getTBRow('Office Overheads', 'office_overheads', $row['office_overheads'], $expdisTab)}</td>
                                        <td class='totalCostField'>{$formObj->getTBRow('Other Charges', 'other_charges', $row['other_charges'], $expDollar)}</td>
                                        <td class='totalCostValue disabledInputReadable' colspan='2'>{$formObj->getTBRow('TOTAL COST', 'total_cost', $row['total_cost'], $expdisTab)}</td>
                                    </tr>
                                </table>
                             </div>    
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Materials {$addNewProduct}</th>
            <th class='txtCenter'>Supplier {$addNewSupplier}</th>
            <th class='txtCenter'>Sub-Con</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Qty</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtCenter'>Total Cost</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=project_projectCostingSummary&_spAction=editCostingSummarySubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);
        $text = "
        <form id='costingSummaryForm' class='yform columnar costingSummaryForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box1", '', $expEdit)}</div>
            <table class='thinlist'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='costing_summary_id' value='{$costing_summary_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddActualCharges() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $project_id     = $fn->getReqParam('project_id');
        $title = $fn->getReqParam('title');

        $today = date("Y-m-d");
        
        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description'></textarea>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $date = $formObj->getDateRow('', 'date', $today);

        $rows = "
        <tr>
            <td>{$date}</td>
            <td>{$amount}</td>
            <td>{$description}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Date</th>
            <th class='txtCenter'>Amount</th>
            <th>Description</th>
        </tr>
        ";

        $formAction = "index.php?widget=project_projectCostingSummary&_spAction=actualChargesSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT c.*
        FROM `actual_costing_summary` c
        WHERE c.project_id = {$project_id}
          AND c.title = '{$title}'
        ORDER BY c.actual_costing_summary_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $rowActual .= "
            <tr>
                <td>{$date}</td>
                <td>{$row['amount']}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargesForm' class='actualChargesForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>{$title}</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='title' value='{$title}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewSupplier() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $sqlStatus  = $fn->getValueListSQL('companyStatus');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);
        $formAction = "index.php?widget=project_projectCostingSummary&_spAction=AddNewSupplierSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        $expVl      = array('sqlType' => 'OneField');

        $text = "
        <form id='AddNewSupplierPortalForm' class='AddNewSupplierPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Supplier Name *', 'company_name', '')}
            {$formObj->getTBRow('Email', 'email')}
            {$formObj->getTBRow('Fax', 'fax')}
            {$formObj->getTBRow('Mobile', 'mobile')}
            {$formObj->getTBRow('Address 1', 'address_flat')}
            {$formObj->getTBRow('Address 2', 'address_street')}
            {$formObj->getTBRow('State/ Zip', 'address_state')}
            {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, '')}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewProductMaster() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?widget=enggCrm_opportunityCostingSummary&_spAction=AddNewProductMasterSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $typeArray = array(
           "Materials"
          ,"Tools"
        );

        $text = "
        <form id='NewProductPortalForm' class='NewProductPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Product Name *', 'title', '')}
            {$formObj->getDropDownRowByArray('Product Type *', 'product_type', $typeArray, 'Materials')}
        </form>
        ";

        return $text;
    }
}