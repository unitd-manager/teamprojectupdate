<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrder_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getDeliveryOrderPortal($project_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $rows  = "";

        $SQL="
        SELECT do.*
        FROM delivery_order do
        WHERE project_id = '{$project_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $urlPrintLinkPdf  = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=printDeliveryOrderPdf&delivery_order_id={$row['delivery_order_id']}&showHTML=0";
            $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
            $edit_image = $cpCfg['cp.localPath']."images/edit.png";

            $editUrl = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=editDeliveryOrder&delivery_order_id={$row['delivery_order_id']}&showHTML=0";
            $editDO = "
            <a href='{$editUrl}' delivery_order_id='{$row['delivery_order_id']}' class='deliveryOrderEdit' title='Edit Delivery Order'><img src='{$edit_image}' class='icon'></a>";

            $rows .= "
            <tr>
                <td>{$date}</td>
                <td>
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            {$editDO}
                        </div>
                        <div class='float_left'>
                            <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Delivery Order'><img src='{$print_image}' class='icon'></a>
                        </div>
                    </div>
                </td>
            </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal' colspan='2'><font>No Records Linked</font></td>
                </tr>
            ";

        }

        $text = "
        <div class='linkPortalWrapper tradingsg_purchaseOrder__tradingsg_po_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Delivery Order</div>
                    <div class='txtRight'>
                        <span class='count'>({$numRows})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form class='deliveryOrderPoPortal'>
                    <table class='renewallist room-poProduct-table'>
                        <thead>
                            <th>Date</th>
                            <th></th>
                        </thead>
                        <tbody id='AddProductPortal'>
                            {$rows}
                        </tbody>
                    </table>
                    <input type='hidden' name='project_id' value='{$project_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditDeliveryOrder(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $delivery_order_id = $fn->getReqParam('delivery_order_id');

        $rows  = "";

        $statusArr = array(
            "In Progress"
           ,"Delivered"
           ,"On-hold"
           ,"Cancelled"
        );

        $SQL="
        SELECT doh.*
              ,p.title
        FROM delivery_order_history doh
        LEFT JOIN product p ON (p.product_id = doh.product_id)
        WHERE doh.delivery_order_id = '{$delivery_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
                <tr>
                    <td>{$row['title']}</td>
                    <td><input type='text' value='{$row['quantity']}' id='quantity' class='text lineItemQuantity' name='quantity[]'></td>
                    <td>
                        <select name='do_status[]'>
                            <option value=''>Please Select</option>
                            {$cpUtil->getDropDown1($statusArr, $row['status'])}
                        </select>
                    </td>
                    <td>
                        <textarea type='text' value='{$row['remarks']}' id='remarks' class='text lineItemDescription' name='remarks[]'>{$row['remarks']}</textarea>
                    </td>
                </tr>
                <input type='hidden' name='delivery_order_history_id[]' value='{$row['delivery_order_history_id']}' />
            ";
            $count++;
        }

        $formActionEditForDO = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=editForDOSubmit&showHTML=0";

        $text = "
        <form id='editForDO' class='yform columnar editForDO' method='post' action='{$formActionEditForDO}'>
            <table class='renewallist room-poProduct-table thinlist'>
                <thead>
                    <tr style='background-color:#EAEAE8'>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>    
                </thead>
                <tbody id='AddProductPortal'>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDeliveryOrderPdf() {
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

        $delivery_order_id = $fn->getReqParam('delivery_order_id');

        $SQL = "
        SELECT doh.*
              ,p.title AS product_title
              ,do.date
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,c.address_po_code AS billing_address_po_code
              ,gc.name AS billing_address_country
        FROM delivery_order_history doh
        LEFT JOIN (delivery_order do) ON (do.delivery_order_id = doh.delivery_order_id)
        LEFT JOIN (company c) ON (c.company_id = do.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (product p) ON (p.product_id = doh.product_id)
        WHERE do.delivery_order_id = {$delivery_order_id}
        ORDER BY doh.delivery_order_history_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $date   = $fn->getCPDate($company['date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:26px;">DELIVERY ORDER</td>
            </tr>
        </table>
        <table border="0">
            <tr>
                <td width="98%" align="right" style="font-weight:bold; font-size:10px; line-height:20px;">DATE : '.$date.'</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:10px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }

        $tbl2 = '
        <table border="0" width="100%" cellpadding="">
            <tr>
                <td width="65%" style="font-size:10px; font-weight:bold;">TO: </td>
                <td width="23%" align="right" style="font-size:10px; font-weight:bold;"></td>
                <td width="12%" align="right" style="font-size:10px; font-weight:bold;"></td>
            </tr>
            <tr>
                <td style="font-size:10px; font-weight:bold;">'.strtoupper($company['company_name']).'</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">'.strtoupper($company['billing_address_flat']).'</td>
                <td colspan="2"></td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td style="font-size:10px;">'.strtoupper($company['billing_address_country']).' - '.$company['billing_address_po_code'].'</td>
                <td colspan="2"></td>
            </tr>
        </table>
        ';

        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr bgcolor="#ededf0">
                            <th width="8%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="45%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">PRODUCT NAME</th>
                            <th width="15%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="32%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $tbl3 = $tbl3.'<tr>
                                <td width="8%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="45%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.$row['product_title'].'</td>
                                <td width="15%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="32%" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['remarks'].'</td>
                            </tr>
                    ';

            $countCheck++;
            $count++;
        }

        $emptyRow = 15 - $countCheck;

        for($i = 0; $i <= $emptyRow; $i++) {
          $tbl3 = $tbl3.'<tr>
                            <td width="8%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                            <td width="45%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="15%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="32%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                        </tr>
                  ';
        }

        $tbl3 = $tbl3.'<tr>
                            <td width="8%"  style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="45%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="15%"  style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="32%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                        </tr>
                        </tbody>
                    </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">Authorised Signature / Date</td>
                <td></td>
                <td style="font-size:10px;">Accepted By / Date</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-DeliveryOrder.pdf';
        $pdf->Output($download_title, 'I');
    }
}