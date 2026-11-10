<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialsUsed_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getProjectMaterialPortal($project_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT pm.*
              ,p.product_type
        FROM project_materials pm
        LEFT JOIN (product p) ON (p.product_id = pm.product_id)
        WHERE pm.project_id = {$project_id}
        ORDER BY pm.project_materials_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $materialActions = '';
            if ($row['status'] != 'Cancelled') {
                $editForMaterialUsed = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=editForMaterialUsed&project_id={$project_id}&project_materials_id={$row['project_materials_id']}&showHTML=0";

                $returnMaterials = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=returnMaterialUsed&project_id={$project_id}&project_materials_id={$row['project_materials_id']}&product_id={$row['product_id']}&showHTML=0";

                $SQLMR = "
                SELECT SUM(pm.quantity) AS return_qty
                FROM materials_returned pm
                WHERE pm.project_materials_id = '{$row['project_materials_id']}'
                ";
                $resultMR = $db->sql_query($SQLMR);
                $rowMR    = $db->sql_fetchrow($resultMR);

                $return_qty = '';
                if($rowMR['return_qty'] > 0){
                    $return_qty = "[{$rowMR['return_qty']}]";
                }

                $materialActions = "
                <div class='float_box clearfix'>
                    <div class='float_left'>
                        <a class='editForMaterialUsed' href='{$editForMaterialUsed}'><u>Edit</u></a>
                    </div>
                    <div class='float_right'>
                        <a project_materials_id='{$row['project_materials_id']}' class='viewAllReturnedMaterialHistory'><u>View</u>
                    </div>
                    <div class='float_right'>
                        <a href='{$returnMaterials}' class='returnMaterial' project_materials_id={$row['project_materials_id']}><u>Return to Stock</u></a> {$return_qty}
                    </div>
                </div>
                ";
            }

            $add_class = '';
            if ($row['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $amountFormatted = number_format($row['amount'], 2);

            $vfInputRow = '<td></td>';
            if($row['product_type'] == 'Tools') {
                $checked = '';
                if($row['viresco_factory'] == 1){
                    $checked = "checked='checked'";
                }
                $vfInputRow = "<td><input class='virescoFactory' type='checkbox' name='viresco_factory' value='{$row['viresco_factory']}' {$checked} project_materials_id='{$row['project_materials_id']}'></td>";
            }
            $rows .= "
            <tr>
                {$vfInputRow}
                <td>
                  <a class='creationModificationMU' project_materials_id='{$row['project_materials_id']}'>
                    <u>{$row['title']}</u>
                  </a>
                </td>
                <td>{$row['unit']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['description']}</td>
                <td class='{$add_class}'>{$row['status']}</td>
                <td>{$materialActions}</td>
            </tr>
            ";
        }

        $urlPrintmaterialLinkPdf  = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=printmaterialLinkForPdf&project_id={$project_id}&showHTML=0";

        $text = "
        <div id='materialsPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='9' align='left' class='rightPanelHeading'>
                          <div class='float_left rightPanelHeading'>
                              Materials used
                          </div>
                          <div class='float_left'>
                              <a href='{$urlPrintmaterialLinkPdf}' target='_blank' class='printLink btn btn-info' project_id='{$project_id}'>Print Pdf</a>
                          </div>
                          <div class='float_left'>
                              <a class='addMultipleMaterials btn btn-primary' project_id='{$project_id}'>Add materials used</a>
                          </div>
                        </th>
                    </tr>
                    <tr>
                        <th>V.F.</th>
                        <th>Description</th>
                        <th>UoM</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class='materialsDetailRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

    /**
     * Add Line Item Edit
     */
    function getEditForMaterialUsed() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $project_materials_id = $fn->getReqParam('project_materials_id');
        $project_id           = $fn->getReqParam('project_id');

        $SQLProjMtls = "
        SELECT pm.*
              ,p.product_type
              ,(SELECT i.actual_stock
                FROM inventory i
                WHERE i.product_id = pm.product_id) AS stock
        FROM project_materials pm
        LEFT JOIN (product p) ON (p.product_id = pm.product_id)
        WHERE pm.project_materials_id = '{$project_materials_id}'
        ";
        $resultProjMtls = $db->sql_query($SQLProjMtls);
        $rowMaterial    = $db->sql_fetchrow($resultProjMtls);

        $exp = array('sqlType' => 'OneField');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=editForMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&project_materials_id={$project_materials_id}&showHTML=0";
        $expTitle  = array("placeholder" => "Please type and select");
        $expNoEdit = array("isEditable" => 0);
        
        $text = "
        <form id='editForMaterialUsed' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'material_used_date',$rowMaterial['material_used_date'] )}
                {$formObj->getTBRow('Description', 'title', $rowMaterial['title'], $expTitle)}
                <input type='hidden' name='product_id' class='product_id_hidden' value='{$rowMaterial['product_id']}'>
                <input type='hidden' id='materialStock' class='text materialStock' name='materialStock' value='{$rowMaterial['stock']}'>
                {$formObj->getTBRow('Type', 'product_type', $rowMaterial['product_type'], $expNoEdit)}
                {$formObj->getTBRow('Stock', 'stock', $rowMaterial['stock'], $expNoEdit)}
                {$formObj->getTBRow('UoM', 'unit', $rowMaterial['unit'])}
                {$formObj->getTBRow('Quantity', 'quantity', $rowMaterial['quantity'])}
                {$formObj->getTARow('Remarks', 'description', $rowMaterial['description'] )}
                <input type='hidden' name='project_materials_id' value='{$project_materials_id}' />
                <input type='hidden' name='project_id' value='{$project_id}' />
            </fieldset>
        </form>
        ";
        return $text;
    }

        /**
     *
     */
    function getAddMultipleMaterials() {
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $project_id  = $fn->getReqParam('project_id');

        $part_no   = "<input type='text' value='' id='partno' class='text materialPartno' name='partno[]'>";

        $title     = "
        <input type='text' placeholder='Please type and select' value='' id='title' class='text materialTitleFull' name='title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        <input type='hidden' id='materialStock' class='text materialStock' name='materialStock[]'>
        ";
        
        $productType = "<td class='productType'  name='productType[]'></td>";
        $stock       = "<td class='productStock' name='stock[]'></td>";
        $unit        = "<input type='text' value='' id='unit' class='text materialUnit' name='unit[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text materialQuantity' name='quantity[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text materialAmount' name='amount[]'>";
        $remarks     = "<textarea type='text' id='description' class='text materialDescription' name='description[]'></textarea>";
        $vfInputRow  = "<input class='materialVirescoFactory' type='checkbox' name='virescoFactory[]' value='1'>";

        $rows = "
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        ";

        $header ="
        <tr><a  class='addMaterialRow btn btn-primary mb10'>Add Material</a></tr>
        <tr style='background-color:#EAEAE8;text-align:left;'>
            <th width='5%'>V.F.</th>
            <th width='28%'>Description</th>
            <th width='10%' class='txtCenter'>Type</th>
            <th width='7%'  class='txtCenter'>Stock</th>
            <th width='10%' class='txtCenter'>UoM</th>
            <th width='10%' class='txtCenter'>Quantity</th>
            <th width='22%' class='txtCenter'>Remarks</th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=addMultipleMaterialsSubmit&showHTML=0";

        $expEdit = array("isEditable" => 0);
        $text = "
        <form id='addMultipleMaterialsForm' class='addMultipleMaterialsForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='materialsTable'>
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
    function getAddMaterialRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $part_no  = "<input type='text' value='' id='partno' class='text materialPartNo' name='partno[]'>";
        
        $title    = "
        <input type='text' placeholder='Please type and select' value='' id='title' class='text materialTitleFull' name='title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        <input type='hidden' value='' id='materialStock' class='text materialStock' name='materialStock[]'>
        ";

        $productType = "<td class='productType'  name='productType[]'></td>";
        $stock       = "<td class='productStock' name='stock[]'></td>";
        $unit        = "<input type='text' value='' id='unit' class='text materialUnit' name='unit[]'>";
        $quantity    = "<input type='text' value='' id='quantity' class='text materialQuantity' name='quantity[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text materialAmount' name='amount[]'>";
        $remarks     = "<textarea type='text' id='description' class='text materialDescription' name='description[]'></textarea>";
        $vfInputRow = "<input class='materialVirescoFactory' type='checkbox' name='virescoFactory[]' value='1'>";

        $rows = "
        <tr>
            <td>{$vfInputRow}</td>
            <td>{$title}</td>
            {$productType}
            {$stock}
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$remarks}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getprintmaterialLinkForPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Boxx Engg');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(4);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $project_id = $fn->getReqParam('project_id');

        $SQL = "
        SELECT p.*
              ,pm.material_used_date
              ,pm.part_no
              ,pm.title
              ,pm.quantity
              ,pm.description
              ,pm.amount
              ,pm.unit
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,c.address_po_code AS billing_address_po_code
              ,gc.name AS billing_address_country
              ,c.company_id
              ,co.salutation
              ,co.first_name
              ,co.last_name
        FROM project_materials pm
        LEFT JOIN (project p) ON (pm.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        WHERE p.project_id = {$project_id}
          AND pm.status != 'Cancelled'
        ORDER BY pm.project_materials_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center"><font style="font-size:16px; font-weight:bold; color:#078205; line-height:26px; text-decoration:underline;">MATERIALS</font></td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }

        $tbl2 = '
        <table border="0" width="100%" cellpadding="">
            <tr>
                <td width="65%" style="font-size:12px; font-weight:bold;">TO: </td>
                <td width="23%" align="right" style="font-size:12px; font-weight:bold;"></td>
                <td width="12%" align="right" style="font-size:12px; font-weight:bold;"></td>
            </tr>
            <tr>
                <td style="font-size:12px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_flat']).'</td>
                <td colspan="2"></td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                <td colspan="2"></td>
            </tr>
        </table>
        ';

        $tbl7 ='
        <table>
            <tr>
                <td style="font-size:12px; font-weight:bold;">ATTN:&nbsp;'.strtoupper($company['salutation']).' '.strtoupper($company['first_name']).'</td>
            </tr>
            <tr>
                <td style="line-height:10px;">&nbsp;</td>
            </tr>
        </table>';

        $tbl3 = '<table border="1" cellpadding="2"  width="100%">
                    <thead>
                        <tr>
                            <th width="5%"  align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                            <th width="11%" align="center" style="font-size:12px; font-weight:bold;">DATE</th>
                            <th width="30%" align="center" style="font-size:12px; font-weight:bold;">DESCRIPTION</th>
                            <th width="6%"  align="center" style="font-size:12px; font-weight:bold;">UOM</th>
                            <th width="10%" align="center" style="font-size:12px; font-weight:bold;">QTY</th>
                            <th width="12%" align="center" style="font-size:12px; font-weight:bold;">UNIT PRICE (S$)</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">TOTAL AMT (S$)</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $subtotal_amount = $row['quantity'] * $row['amount'];
            $material_date = $fn->getCPDate($row['material_used_date'], 'd-m-Y');

            $tbl3 = $tbl3.'<tr>
                                <td width="5%"  align="center" style="font-size:12px;">'.$count.'</td>
                                <td width="11%" align="center" style="font-size:12px;">'.$material_date.'</td>
                                <td width="30%" style="font-size:12px;">'.$row['title'].'</td>
                                <td width="6%"  align="center" style="font-size:12px;">'.$row['unit'].'</td>
                                <td width="10%" align="center" style="font-size:12px;">'.$row['quantity'].'</td>
                                <td width="12%" align="right" style="font-size:12px;">'.number_format($row['amount'], 2).'</td>
                                <td width="13%" align="right" style="font-size:12px;">'.number_format($subtotal_amount, 2).'</td>
                                <td width="13%" style="font-size:12px;">'.$row['description'].'</td>
                            </tr>
                    ';
            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'];
            $gstvalue = $subtotalValue * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + $subtotalValue;
            $count++;
        }

        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="6" style="font-size:12px; font-weight:bold;">SUB TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                          <td></td>
                      </tr>
                      <tr>
                          <td colspan="6" align="right" style="font-size:12px; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                          <td></td>
                       </tr>
                       <tr>
                          <td colspan="6" align="right" style="font-size:12px; font-weight:bold;">TOTAL AMOUNT</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                          <td></td>
                       </tr>
                    </table>';

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td style="line-height:20px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="font-size:12px;" width="70%">Requested by :</td>
                <td style="font-size:12px;" width="30%">Approved by :</td>
            </tr>
        </table>
        ';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td width="70%"></td>
                <td width="30%" style="border-bottom:2px solid black"></td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size:12px; font-weight:bold;">Authorised signature</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl7, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Materials.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     * Return Materials
     */
    function getReturnMaterialUsed() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $project_materials_id = $fn->getReqParam('project_materials_id');
        $project_id           = $fn->getReqParam('project_id');
        $product_id           = $fn->getReqParam('product_id');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=returnMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&project_materials_id={$project_materials_id}&showHTML=0";

        $pmRec = $fn->getRecordRowByID('project_materials', 'project_materials_id', $project_materials_id);

        $SQLMR = "
        SELECT SUM(pm.quantity) AS return_qty
        FROM materials_returned pm
        WHERE pm.project_materials_id = '{$project_materials_id}'
        ";
        $resultMR = $db->sql_query($SQLMR);
        $rowMR    = $db->sql_fetchrow($resultMR);

        $qtyValidate = $pmRec['quantity'] - $rowMR['return_qty'];

        $text = "
        <form id='returnMaterialUsed' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                {$formObj->getTBRow('Enter Return Quantity', 'quantity')}
                <input type='hidden' name='project_materials_id' value='{$project_materials_id}' />
                <input type='hidden' name='project_id' value='{$project_id}' />
                <input type='hidden' name='product_id' value='{$product_id}' />
                <input type='hidden' id='qtyValidate' class='text qtyValidate' name='qtyValidate' value='{$qtyValidate}'>
            </fieldset>
        </form>
        ";
        return $text;
    }

    /**
     */
    function getReturnedMaterialHistory(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_materials_id = $fn->getReqParam('project_materials_id');

        $rows = "";

        $SQL = "
        SELECT *
        FROM materials_returned
        WHERE project_materials_id = {$project_materials_id}
        ORDER BY materials_returned_id DESC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            $rows .= "
            <tr>
                <td>{$row['quantity']}</td>
                <td>{$row['created_by']} - {$creation_date}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Quantity Returned</th>
            <th>Created/Updated By</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }
}