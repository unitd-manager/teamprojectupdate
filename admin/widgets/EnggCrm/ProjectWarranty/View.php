<?
class CPL_Admin_Widgets_EnggCrm_ProjectWarranty_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getProjectWarrantyPortal($project_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT pm.*
        FROM project_warranty pm
        WHERE pm.project_id = {$project_id}
        ORDER BY pm.project_warranty_id ASC
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
                $editForMaterialUsed = "index.php?widget=enggCrm_projectWarranty&_spAction=editForMaterialUsed&project_id={$project_id}&project_warranty_id={$row['project_warranty_id']}&showHTML=0";


                $urlPrintmaterialLinkPdf  = "index.php?widget=enggCrm_projectWarranty&_spAction=printmaterialLinkForPdf&project_id={$project_id}&project_warranty_id={$row['project_warranty_id']}&showHTML=0";

                $materialActions = "
                <div class='float_box clearfix'>
                    <div class='float_left'>
                        <a class='editForWarranty' href='{$editForMaterialUsed}'><u>Edit</u></a>
                    </div>
                    <div class='float_left'>
                    <a href='{$urlPrintmaterialLinkPdf}' target='_blank' class='printLink btn btn-info' project_id='{$project_id}'>Print Pdf</a>
                </div>
                </div>
                ";

            $rows .= "
            <tr>
                <td>
                  {$row['detail_of_work']}
                </td>
                <td>{$row['warranty_date']}</td>
                <td>{$row['work_completed_date']}</td>
                <td>{$row['warranty_start_date']}</td>
                <td>{$materialActions}</td>
            </tr>
            ";
        }


        $text = "
        <div id='materialsPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='9' align='left' class='rightPanelHeading'>
                          <div class='float_left rightPanelHeading'>
                              Warranty
                          </div>
                         
                          <div class='float_left'>
                              <a class='addMultipleWarranty btn btn-primary' project_id='{$project_id}'>Add Warranty</a>
                          </div>
                        </th>
                    </tr>
                    <tr>
                        <th>Detail of work</th>
                        <th>Warranty Date</th>
                        <th>Work Completed Date</th>
                        <th>Start Date</th>
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

        $project_warranty_id = $fn->getReqParam('project_warranty_id');
        $project_id           = $fn->getReqParam('project_id');

        $SQLProjMtls = "
        SELECT pm.*
             
        FROM project_warranty pm
        WHERE pm.project_warranty_id = '{$project_warranty_id}'
        ";
        $resultProjMtls = $db->sql_query($SQLProjMtls);
        $rowMaterial    = $db->sql_fetchrow($resultProjMtls);

        $exp = array('sqlType' => 'OneField');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectWarranty&_spAction=editForMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&project_warranty_id={$project_warranty_id}&showHTML=0";
        $expTitle  = array("placeholder" => "Please type and select");
        $expNoEdit = array("isEditable" => 0);
        
        $text = "
        <form id='editForWarranty' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                <td>{$formObj->getDateRow('Date', 'warranty_date',$rowMaterial['warranty_date'] )}</td>
                <td>{$formObj->getDateRow('Completed Date', 'work_completed_date',$rowMaterial['work_completed_date'])}</td>
                <td>{$formObj->getDateRow('Warranty Start Date', 'warranty_start_date',$rowMaterial['warranty_start_date'])}</td>
                <td>{$formObj->getDateRow('End Date', 'end_date',$rowMaterial['end_date'])}</td>
                </tr>
                <tr>
                <td>{$formObj->getDateRow('Po Date', 'po_issue_date',$rowMaterial['po_issue_date'])}</td>                
                <td>{$formObj->getTBRow('Warranty Issued For', 'issued_for', $rowMaterial['issued_for'])}</td>
                <td>{$formObj->getTBRow('Reference No', 'ref_no', $rowMaterial['ref_no'])}</td>
                <td>{$formObj->getTBRow('Location of work', 'location_of_work', $rowMaterial['location_of_work'])}</td>
                </tr>
                <tr>
                <td>{$formObj->getTBRow('Warranty Detail', 'warranty_details', $rowMaterial['warranty_details'])}</td>
                <td>{$formObj->getTBRow('Warranty Duration', 'warranty_duration', $rowMaterial['warranty_duration'])}</td>
                </tr>
                <tr>
                    <td colspan='4'>  
                    <label>Details Of Work</label>
                    {$formObj->getHTMLEditor('Terms & Condition', 'detail_of_work',$rowMaterial['detail_of_work'])}</td>
                </tr>
                       
                <tr>
                    <td colspan='4'>  
                    <label>Special Terms</label>
                    {$formObj->getHTMLEditor('Terms & Condition', 'special_terms', $rowMaterial['special_terms'])}</td>
                </tr>
                 
                </table>
         
                <input type='hidden' name='project_warranty_id' value='{$project_warranty_id}' />
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

       

        $formAction = "index.php?widget=enggCrm_projectWarranty&_spAction=addMultipleMaterialsSubmit&showHTML=0";

        $expEdit = array("isEditable" => 0);
        $text = "
        <form id='addMultipleWarrantyForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getDateRow('Completed Date', 'work_completed_date','')}</td>
                        <td>{$formObj->getDateRow('End Date', 'end_date','')}</td>
                        <td>{$formObj->getDateRow('Start Date', 'warranty_start_date','')}</td>
                        <td>{$formObj->getDateRow('Po Date', 'po_issue_date','')}</td>

                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Reference No', 'ref_no', '')}</td>
                        <td >{$formObj->getTBRow('Location of work', 'location_of_work', '')}</td>
                        <td >{$formObj->getTBRow('Warranty Detail', 'warranty_details', '')}</td>
                        <td >{$formObj->getTBRow('Warranty Duration', 'warranty_duration', '')}</td>

                    </tr>
                   
                    <tr>
                        <td colspan='4'>                          
                        <label>Details Of Work</label>
                        {$formObj->getHTMLEditor('Terms & Condition', 'detail_of_work','')}</td>
                    </tr>
                     <tr>
                        <td colspan='4'>                          
                        <label>Special Terms</label>
                        {$formObj->getHTMLEditor('Terms & Condition', 'special_terms', '')}</td>
                    </tr>
                 
                </table>
                <input type='hidden' name='project_id' value='{$project_id}' />
            </fieldset>
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
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

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

        $project_warranty_id = $fn->getReqParam('project_warranty_id');

        $SQL = "
        SELECT pm.*
            
        FROM project_warranty pm

        WHERE pm.project_warranty_id = {$project_warranty_id}
        ORDER BY pm.project_warranty_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today      = date("d-m-Y");
        $warranty_start_date   = $fn->getCPDate($company['warranty_start_date'], 'd/m/Y');

        $end_date   = $fn->getCPDate($company['end_date'], 'd/m/Y');

        $work_completed_date   = $fn->getCPDate($company['work_completed_date'], 'd/m/Y');
        $po_issu_date   = $fn->getCPDate($company['po_issue_date'], 'd/m/Y');
        $warranty_date   = $fn->getCPDate($company['warranty_date'], 'd/m/Y');


        $tbl1 = '
        <table border="0" width="100%" style="" cellpadding="4">
            <tr>
                <td align="center"><font style="font-size:16px; font-weight:bold; color:#078205; line-height:26px; text-decoration:underline;">Warranty Certificate</font></td>
            </tr>
        </table>
        ';

      
        $tbl2 = '
        <table border="0" width="100%" cellspacing="3" cellpadding="6">
            <tr>
                <td width="35%" style="font-size:12px; font-weight:bold;">Ref: '.$company['ref_no'].' </td>
                <td width="30%" align="right" style="font-size:12px; font-weight:bold;"></td>
                <td width="35%" align="right" style="font-size:12px; font-weight:bold;">Date: '.$warranty_date.'</td>
            </tr><br/><br/>
            <tr>
                <td width="75%" style="font-size:12px;font-weight:bold;">We hereby Gurantee and waranty as per following </td>
            </tr><br/><br/>
            <tr>
            <td width="20%" style="font-size:12px;">Warranty Issued for: </td>
              <td width="80%" style="font-size:12px;"> '.$company['issued_for'].' </td>
        </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Po Issued Date: </td>
                 <td width="80%" style="font-size:12px;"> '.$company['po_issue_date'].' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Detail OF Work: </td>
                   <td width="80%" style="font-size:12px;">'.$company['detail_of_work'].' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Location Of work: </td>
                      <td width="80%" style="font-size:12px;">'.$company['location_of_work'].' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Warranty Details: </td>
                <td width="80%" style="font-size:12px;">'.$company['warranty_details'].' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Work Completed Date:</td>
                <td width="80%" style="font-size:12px;">'.$work_completed_date.' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Warranty Duration:</td>
                  <td width="80%" style="font-size:12px;"> '.$company['warranty_duration'].' </td>

            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Start Date:</td>
                <td width="80%" style="font-size:12px;">'.$warranty_start_date.' </td>
            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">End Date:</td>
                <td width="80%" style="font-size:12px;">'.$end_date.' </td>

            </tr>
            <tr>
                <td width="20%" style="font-size:12px;">Special Terms:</td>
                <td width="80%" style="font-size:12px;"> '.$cpCfg['cp.specialTerms'].'</td>
            </tr>
           
        
          
        </table>
        ';

       

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td style="line-height:20px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="font-size:12px;font-weight:bold;" width="70%">For:<br/>A Team International</td>
            </tr>
        </table>
        ';

       

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        //$pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['warranty_date'] . '-Warranty.pdf';
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

        $project_warranty_id = $fn->getReqParam('project_warranty_id');
        $project_id           = $fn->getReqParam('project_id');
        $product_id           = $fn->getReqParam('product_id');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=returnMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&project_warranty_id={$project_warranty_id}&showHTML=0";

        $pmRec = $fn->getRecordRowByID('project_materials', 'project_warranty_id', $project_warranty_id);

        $SQLMR = "
        SELECT SUM(pm.quantity) AS return_qty
        FROM materials_returned pm
        WHERE pm.project_warranty_id = '{$project_warranty_id}'
        ";
        $resultMR = $db->sql_query($SQLMR);
        $rowMR    = $db->sql_fetchrow($resultMR);

        $qtyValidate = $pmRec['quantity'] - $rowMR['return_qty'];

        $text = "
        <form id='returnMaterialUsed' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                {$formObj->getTBRow('Enter Return Quantity', 'quantity')}
                <input type='hidden' name='project_warranty_id' value='{$project_warranty_id}' />
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

        $project_warranty_id = $fn->getReqParam('project_warranty_id');

        $rows = "";

        $SQL = "
        SELECT *
        FROM materials_returned
        WHERE project_warranty_id = {$project_warranty_id}
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