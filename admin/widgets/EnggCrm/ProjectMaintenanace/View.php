<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaintenanace_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getProjectMaintenanacePortal($project_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT pm.*
        FROM renewal pm
        WHERE pm.project_id = {$project_id}
        ORDER BY pm.renewal_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
                $renewal = "<a target='_blank' href='index.php?_topRm=finance&module=enggCrm_renewal&renewal_id={$row['renewal_id']}&_action=edit'><u>{$row['store']}</u></a>";

                $urlPrintquotecolumnLinkPdf  = "index.php?widget=enggCrm_projectMaintenanace&_spAction=printRenewalDisplayPdf&renewal_id={$row['renewal_id']}&showHTML=0";


            $rows .= "
            <tr>
                <td>
                    {$renewal}
                </td>
                <td>{$row['date']}</td>
                <td>{$row['time']}</td>
                <td>{$row['completed_by']}</td>
                <td>{$row['service_type']}</td>
                <!--<td> <a href='{$urlPrintquotecolumnLinkPdf}' target='_blank' class='printLink' renewal_id={$row['renewal_id']}'>Renewal display pdf</a></td>-->
            </tr>
            ";
        }


        $text = "
        <div id='renewalPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th  align='left' class='rightPanelHeading'>
                          <div class='float_left rightPanelHeading'>
                              Maintenanace
                          </div>
                          <div class='float_left'>
                              <a class='addMultipleMaintain btn btn-primary' project_id='{$project_id}'>Add Maintenance</a>
                          </div>
                        </th>
                    </tr>
                    <tr>
                        <th>Store</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Completed By</th>
                        <th>Service Type</th>
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
     *
     */
    function getprintRenewalDisplayPdf() {
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
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);


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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
        SELECT q.*
            ,qi.title AS checklist_title
            ,qi.monthly,qi.quaterly,qi.annually
            ,qi.remarks
        FROM renewal q
        LEFT JOIN (renewal_chechlist_history qi) ON (qi.renewal_id = q.renewal_id)
    
        WHERE q.renewal_id = {$renewal_id}
        ORDER BY q.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $renewal_date   = $fn->getCPDate($company['date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="1" width="100%" >
            <tr>
                <td width="20%" align="center" style="font-size:16px;padding:30px;  font-weight:bold;"><br/><br/>AMC</td>
                 <td width="60%" align="center" style="font-size:16px;padding:30px; font-weight:bold;"><br/><br/>ANNEXURE<br/>Preventive Maintenance Report</td>
                  <td width="20%" align="center" style="font-size:16px; font-weight:bold;"><img src="images/A-Team.jpg" width="90" /></td>
            </tr>
            <tr>
                        <td width="70%" style="font-size:10px; font-weight:bold;"><br/><br/> Store/Location : '.$company['store'].'</td>
                          <td width="30%" style="font-size:10px; font-weight:bold;"><br/><br/> Date/Time : '.$renewal_date.'/'.$company['time'].'</td>
                      
                    </tr>
                      <tr>
                        <td width="70%" style="font-size:10px; font-weight:bold;"> <br/><br/>Completed By : '.$company['completed_by'].'</td>
                          <td width="30%" style="font-size:10px; font-weight:bold;"> <br/><br/>Service type : '.$company['service_type'].'</td>
                      
                    </tr>
        </table>
        ';

          $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr >
                              <th rowspan="2" width="10%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                              <th rowspan="2" width="10%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">Check</th>
                              <th colspan="1" width="50%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">Task Description</th>
                              <th colspan="3" width="15%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">Frequency</th>
                              <th rowspan="2" width="15%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">Remarks</th>
                             
                          </tr>
                          <tr>

                              <td  width="50%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">AHU/FCU ;NO</td>
                              <td width="5%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">M</td>
                              <td width="5%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">Q</td>
                              <td width="5%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">A</td>

                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        $monthly='';
        $quaterly='';
        $annually='';
        while ($row = $db->sql_fetchrow($result)) {
            if($row['monthly'] == 0 && $row['monthly'] == '') {
                $monthly ='<img src="images/wrong.png" width="10" />';

            }else{
                $monthly ='<img src="images/blueright.jpeg" width="10" />';

            }
            if($row['quaterly'] == 0 && $row['quaterly'] == '') {
                $quaterly ='<img src="images/wrong.png" width="10" />';

            }else{
                $quaterly ='<img src="images/blueright.jpeg" width="10" />';

            }
            if($row['annually'] == 0 && $row['annually'] == '') {
                $annually ='<img src="images/wrong.png" width="10" />';

            }else{
                $annually ='<img src="images/blueright.jpeg" width="10" />';

            }
              $tbl3 = $tbl3.'<tr>
                                  <td width="10%"  style="border:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                    <td width="10%"  style="border:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center"><img src="images/checkbox.png" width="20" /></td>
                                  <td width="50%" style="border:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['checklist_title']).'</td>
                                 <td width="5%"  align="center" style="font-size:10px;border:1px solid #000;">'.$monthly.'</td>
                                  <td width="5%"  align="center" style="font-size:10px;border:1px solid #000;border-right:1px solid #000;">'.$quaterly.'</td>
                                  <td width="5%" align="right" style="font-size:10px;border:1px solid #000;border-right:1px solid #000;">'.$annually.'</td>
                                  <td width="15%" align="left" style="font-size:10px;border:1px solid #000;">'.$row['remarks'].'</td>
                              </tr>
                      ';              
            

            $countCheck++;
            $count++;
        }

            

      
            $tbl3 = $tbl3.'
                          </tbody>
                        </table>';          
        

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px;border-right:1px solid #000;border-left:1px solid #000; font-weight:bold;">General notes:</td><br/>
            </tr>
          

         
        </table>';

        $tbl5 = '
            <table border="0" width="100%" >
           <tr>
           
                <td border="0"  style="font-size:10px;border-left:1px solid #000;" width="15%"><br/><br/><br/><br/>Technican Sign :</td>
                <td border="0"  style="font-size:10px;border-bottom:1px solid black" width="20%"></td>
                <td border="0"   width="15%"></td>
                <td border="0"  style="font-size:10px;" width="15%"><br/><br/><br/><br/>Client Sign :</td>
                <td border="0"  style="font-size:10px;border-bottom:1px solid black;" width="20%"></td>
                <td border="0"  style="font-size:10px;border-right:1px solid #000;"   width="15%"></td>

            </tr>
             <tr>
                <td width="100%"  style="border-right:1px solid #000;border-left:1px solid #000;border-bottom:1px solid black;" ><img src="images/footer.jpg" /></td>
            </tr>
        </table>
        ';

       

        $pdf->writeHTML($tbl1, true, false, false, false, '');
                $pdf->ln(-5);

        $pdf->writeHTML($tbl3, true, false, false, false, '');
                        $pdf->ln(-7);

        $pdf->writeHTML($tbl4, true, false, false, false, '');
                                $pdf->ln(-7);

        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-Contract.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     * Add Line Item Edit
     */
    function getEditForMaterialUsed() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $renewal_id = $fn->getReqParam('renewal_id');
        $project_id           = $fn->getReqParam('project_id');

        $SQLProjMtls = "
        SELECT pm.*
             
        FROM renewal pm
        WHERE pm.renewal_id = '{$renewal_id}'
        ";
        $resultProjMtls = $db->sql_query($SQLProjMtls);
        $rowMaterial    = $db->sql_fetchrow($resultProjMtls);

        $exp = array('sqlType' => 'OneField');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectMaintenanace&_spAction=editForMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&renewal_id={$renewal_id}&showHTML=0";
        $expTitle  = array("placeholder" => "Please type and select");
        $expNoEdit = array("isEditable" => 0);
        
        $text = "
        <form id='editForWarranty' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'date',$rowMaterial['date'] )}
                <td>{$formObj->getTIMERow('Time', 'time',$rowMaterial['time'])}</td>
                {$formObj->getTBRow('Store/Location', 'store', $rowMaterial['store'])}
                {$formObj->getTBRow('Completed By', 'completed_by', $rowMaterial['completed_by'])}
                {$formObj->getTBRow('Location of work', 'location_of_work', $rowMaterial['location_of_work'])}
                {$formObj->getTBRow('Warranty Detail', 'warranty_details', $rowMaterial['warranty_details'])}
                {$formObj->getTBRow('Warranty Duration', 'warranty_duration', $rowMaterial['warranty_duration'])}
                <label>Details Of Work</label>
                {$formObj->getHTMLEditor('Terms & Condition', 'detail_of_work',$rowMaterial['detail_of_work'])}
                <label>Special Terms</label>
                {$formObj->getHTMLEditor('Terms & Condition', 'special_terms', $rowMaterial['special_terms'])}
          
         
                <input type='hidden' name='renewal_id' value='{$renewal_id}' />
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

       

        $formAction = "index.php?widget=enggCrm_projectMaintenanace&_spAction=addMultipleMaterialsSubmit&showHTML=0";

        $expEdit = array("isEditable" => 0);
          $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');
        $today = date("m-d-Y");

        $text = "
        <form id='addMultipleRenewalForm' class='yform columnar addMultipleRenewalForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <fieldset>
                <table width='100%'>
                    <tr>
                     <td>{$formObj->getDateRow('Date', 'date',$today)}<td>
                <td>{$formObj->getTimeRow('Time', 'time','')}<td>
                <td>{$formObj->getTBRow('Store/Location', 'store', '')}<td>
                        
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Completed By', 'completed_by', '')}<td>
                        <td>{$formObj->getTBRow('Service Type', 'service_type', '')}</td>
                        <td>{$formObj->getTBRow('AHU/FCU:NO', 'ahu_fcu_no', '')}</td>
                        <td>{$formObj->getDDRowBySQL('Type *', 'contract_type', $sqlType, '', $expVl)}</td>

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
        include_once(CP_LOCAL_PATH.'lib/headfoot5.php');

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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
        SELECT pm.*
            
        FROM renewal pm

        WHERE pm.renewal_id = {$renewal_id}
        ORDER BY pm.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today      = date("d-m-Y");
        $start_date   = $fn->getCPDate($company['start_date'], 'd/m/Y');

        $end_date   = $fn->getCPDate($company['end_date'], 'd/m/Y');

        $work_completed_date   = $fn->getCPDate($company['work_completed_date'], 'd/m/Y');
        $po_issu_date   = $fn->getCPDate($company['po_issue_date'], 'd/m/Y');
        $warranty_date   = $fn->getCPDate($company['warranty_date'], 'd/m/Y');


        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
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
            <td width="100%" style="font-size:12px;">Warranty Issued for:   '.$company['issued_for'].' </td>
        </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Po Issued Date:   '.$company['po_issue_date'].' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Detail OF Work:   '.$company['detail_of_work'].' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Location Of work:   '.$company['location_of_work'].' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Warranty Details:   '.$company['warranty_details'].' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Work Completed Date:   '.$work_completed_date.' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Warranty Duration:   '.$company['warranty_duration'].' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Start Date:   '.$start_date.' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">End Date:   '.$end_date.' </td>
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">Special Terms:   '.$company['special_terms'].' </td>
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

        $renewal_id = $fn->getReqParam('renewal_id');
        $project_id           = $fn->getReqParam('project_id');
        $product_id           = $fn->getReqParam('product_id');

        $formActionMaterialUsed = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=returnMaterialUsedSubmit&lnkRoom={$tv['lnkRoom']}&renewal_id={$renewal_id}&showHTML=0";

        $pmRec = $fn->getRecordRowByID('project_materials', 'renewal_id', $renewal_id);

        $SQLMR = "
        SELECT SUM(pm.quantity) AS return_qty
        FROM materials_returned pm
        WHERE pm.renewal_id = '{$renewal_id}'
        ";
        $resultMR = $db->sql_query($SQLMR);
        $rowMR    = $db->sql_fetchrow($resultMR);

        $qtyValidate = $pmRec['quantity'] - $rowMR['return_qty'];

        $text = "
        <form id='returnMaterialUsed' class='yform columnar' method='post' action='{$formActionMaterialUsed}'>
            <fieldset>
                {$formObj->getTBRow('Enter Return Quantity', 'quantity')}
                <input type='hidden' name='renewal_id' value='{$renewal_id}' />
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $rows = "";

        $SQL = "
        SELECT *
        FROM materials_returned
        WHERE renewal_id = {$renewal_id}
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