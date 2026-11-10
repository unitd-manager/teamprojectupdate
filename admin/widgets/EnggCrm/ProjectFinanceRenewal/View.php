<?
class CPL_Admin_Widgets_EnggCrm_ProjectFinanceRenewal_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getInvoiceReceiptPortalDetails($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        
         $financeButton = '';

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }

                  $projectRec = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
            $quoteRec = $fn->getRecordByCondition('quote', "quote_id = '{$projectRec['quote_id']}'", 'quote_id ASC');

        $orderRows = $fn->getRecordCount('order', "renewal_id = {$renewal_id}");
        $actionButtons = '';
        $rightPanels   = '';
 if($orderRows == 0) {
            $financeButton = "
            <div class='float_box mt10 mb10'>
              <a id='addFinanceProjectsRenewal' class='btn btn-primary' renewal_id='{$renewal_id}' quote_id='{$quoteRec['quote_id']}' >Create Finance Order</a>
            </div>
            ";
          }
        if($orderRows > 0) {
          $orderRec = $fn->getRecordRowByID('order', 'renewal_id', $renewal_id);
          
          $actionButtons .= "
          <div class='float_left btn btn-success mb5'>
              <a  class='generateInvoiceRenewal' order_id={$orderRec['order_id']}>CREATE INVOICE</a>
          </div>
          ";

          $actionButtons .="
          <div class='float_left btn btn-primary mb5'>
              <a  class='generateReceiptRenewal' order_id={$orderRec['order_id']}>CREATE RECEIPT</a>
          </div>
          ";

          $rightPanels .= "
          <div class='row mt20'>
            <div class='col-md-12'>
              <div class='invoicePortalDisplayDiv'>".
                getCPModuleObj('enggCrm_order')->view->getInvoicePortalDisplay2($orderRec['order_id'])
            ."</div>
           </div>
          </div>";
          
          $rightPanels .= "
          <div class='row mt20'>
            <div class='col-md-12'>
              <div class='receiptPortalDisplayDiv'>".
                getCPModuleObj('enggCrm_order')->view->getReceiptPortalDisplay2($orderRec['order_id'])
             ."</div>
            </div>
          </div>";
        }

        $text = "
         {$financeButton}
        <div class='row'> 
          
          <div class='col-md-12'>
            {$actionButtons}
          </div>
        </div>
        {$rightPanels}";

        return $text;
    }
    /**
     *
     */
    function getFinanceSummaryLeftRows($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        
        $orderRows = $fn->getRecordCount('order', "renewal_id = {$renewal_id}");
        $invoiceAmount = '';    
        $receiptAmount = '';    
        $balAmount     = '';    

        if($orderRows > 0) {
          $orderRec = $fn->getRecordRowByID('order', 'renewal_id', $renewal_id);


          //$invoiceAmount = '$'; 
          $invoiceAmount = $this->getInvoiceTotal($orderRec['order_id']);
          $receiptAmount = $this->getReceiptTotal($orderRec['order_id']);
          //$receiptAmount = '$';
          $balAmount     = $invoiceAmount - $receiptAmount;

          if($invoiceAmount){
            $invoiceAmount = '$' . number_format($invoiceAmount, 3);
          }

          if($receiptAmount){
            $receiptAmount = '$' . $receiptAmount;
          }
          
          if($balAmount){
            $balAmount     = '$' . number_format($balAmount, 3);
          }

          $text = "
            <table class='thinlist' id='financeSummaryTable'>
            <tr align='right' style='color:red;font-weight:bold;'>
                <td colspan=3><b>Balance Receivables: {$balAmount}</b></td>
            </tr>
            <tr align='left'>
                <th>Description</th>
                <th>Amount Invoiced</th>
                <th>Amount Received</th>
            </tr>
            <tr>
                <td align='left'>Total Invoice Raised(Total PO Amount : )</td>
                <td align='right'>{$invoiceAmount}</td>
                <td align='right'></td>
            </tr>
            <tr>
                <td align='left'>Total Payments Received</td>
                <td align='right'></td>
                <td align='right'>{$receiptAmount}</td>
            </tr>
            <tr>
                <td colspan=3>&nbsp;</td>
            </tr>
            <tr>
                <td colspan=3>&nbsp;</td>
            </tr>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getFinanceSummaryRightRows($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        
        //$orderRows = $fn->getRecordCount('order', "renewal_id = {$renewal_id}");
        $actionButtons      = '';
        $rightPanels        = '';
        $supplierAmount     = '';    
        $subconAmount       = '';    
        $supplierAmountPaid = '';    
        $subconAmountPaid   = '';    
        $balAmount      = '';    

        if($renewal_id > 0) {
          $orderRec = $fn->getRecordRowByID('order', 'renewal_id', $renewal_id);

          $supplierAmount      = $this->getSupplierTotal($renewal_id);
          $supplierAmountPaid  = $this->getSupplierTotalPaid($renewal_id);
          $subconAmountPaid    = $this->getSubconTotalPaid($renewal_id);
          $subconAmount        = $this->getSubconTotal($renewal_id);
          $balAmount           = $supplierAmount + $subconAmount - $supplierAmountPaid - $subconAmountPaid;

          if($supplierAmount){
            $supplierAmount  = '$' . number_format($supplierAmount, 3);
          }

          if($subconAmount){
            $subconAmount  = '$' . number_format($subconAmount, 3);
          }
          
          if($balAmount){
            $balAmount       = '$' . number_format($balAmount, 3);
          }
          
          if($supplierAmountPaid){
            $supplierAmountPaid       = '$' . number_format($supplierAmountPaid, 3);
          }
          
          if($subconAmountPaid){
            $subconAmountPaid       = '$' . number_format($subconAmountPaid, 3);
          }
          

          $text = "
            <table class='thinlist' id='financeSummaryTable'>
            <tr align='right' style='color:red;font-weight:bold;'>
                <td colspan=3><b>Balance Payables : {$balAmount}</b></td>
            </tr>
            <tr align='left'>
                <th>Description</th>
                <th>Invoice Received</th>
                <th>Amount Paid</th>
            </tr>
            <tr>
                <td align='left'>Supplier Invoice Amount</td>
                <td align='right'>{$supplierAmount}</td>
                <td align='right'></td>
            </tr>
            <tr>
                <td align='left'>Total Payments Made</td>
                <td align='right'></td>
                <td align='right'>{$supplierAmountPaid}</td>
            </tr>
            <tr>
                <td align='left'>Subcon Invoice Amount</td>
                <td align='right'>{$subconAmount}</td>
                <td align='right'></td>
            </tr>
            <tr>
                <td align='left'>Total Payments Made</td>
                <td align='right'></td>
                <td align='right'>{$subconAmountPaid}</td>
            </tr>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getInvoiceTotal($order_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }
        

        if($order_id > 0) {

          $SQLInv = "
          SELECT SUM(i.invoice_amount) as total
                ,SUM(i.discount) AS discount
                ,i.gst_percentage
          FROM invoice i
          WHERE i.order_id = {$order_id}
            AND i.status != 'Cancelled'
          ";
          $resultInv   = $db->sql_query($SQLInv);
          $rowInvoice = $db->sql_fetchrow($resultInv);

          $text = $fn->getAmountFractionFormattedForGst($rowInvoice['total'], $rowInvoice['gst_percentage']);
          $text = $text - $rowInvoice['discount'];
        }

        /*$text = "
        <div class='row'> 
          <div class='col-md-12'>
            {$financeSummaryText}
          </div>
          <div class='col-md-12'>
            {$actionButtons}
          </div>
        </div>
        {$rightPanels}";*/

        return $text;
    }
    /**
     *
     */
    function getReceiptTotal($order_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }
        

        if($order_id > 0) {

          $SQLInv = "
          SELECT i.*
              ,invHist.amount as prev_inv_amount
          FROM invoice i
          LEFT JOIN `order` o ON (i.order_id = o.order_id)
          LEFT JOIN invoice_receipt_history invHist ON (i.invoice_id = invHist.receipt_id)
          LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
          WHERE i.order_id = {$order_id}
              AND r.receipt_status != 'Cancelled'
              AND i.status != 'Cancelled'
          ";
          $resultInv   = $db->sql_query($SQLInv);

          while ($rowInvoice = $db->sql_fetchrow($resultInv)) {
              $text += $rowInvoice['prev_inv_amount'];
          }

        }

        return $text;
    }
    /**
     *
     */
    function getSupplierTotal($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        

        if($renewal_id > 0) {

            $SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
            FROM po_product pop 
              LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
            WHERE po.renewal_id = {$renewal_id}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal    = $db->sql_fetchrow($resultTotal);
            //$totalCost   = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
            $totalCost   = $rowTotal['total_cost'] ;

            $text = $totalCost;
        }


        return $text;
    }
    /**
     *
     */
    function getSubconTotal($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        

        if($renewal_id > 0) {

          $sqlAmt = "
          SELECT SUM(pop.amount) AS po_amount
          FROM work_order_line_items pop
          LEFT JOIN `sub_con_work_order` o ON (pop.sub_con_work_order_id = o.sub_con_work_order_id)
          WHERE o.renewal_id = {$renewal_id}
          ";
          $resultAmt = $db->sql_query($sqlAmt);
          $rowAmt    = $db->sql_fetchrow($resultAmt);
          $text      = $rowAmt['po_amount'];
        }

        return $text;
    }
    /**
     *
     */
    function getSupplierTotalPaid($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        

        if($renewal_id > 0) {

          $SQL = "
          SELECT i.*
              ,(
              SELECT SUM(supHist.amount) AS prev_sum
              FROM supplier_receipt_history supHist
              LEFT JOIN supplier_receipt r ON (r.supplier_receipt_id = supHist.supplier_receipt_id)
              WHERE supHist.purchase_order_id =  i.purchase_order_id
              AND r.receipt_status != 'Cancelled'
              ) as prev_inv_amount
          FROM purchase_order i
          LEFT JOIN `supplier` o ON (i.company_id_supplier = o.supplier_id)
          WHERE i.renewal_id = {$renewal_id}
          AND (i.payment_status != 'Cancelled' || i.payment_status IS NULL)
          ";
          $result = $db->sql_query($SQL);
          $numRows = $db->sql_numrows($result);


          while ($row = $db->sql_fetchrow($result)) {
              $text += $row['prev_inv_amount'];
          }

        }

        return $text;
    }
    /**
     *
     */
    function getSubconTotalPaid($renewal_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $text = '';

        if($renewal_id == "") {
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        

        if($renewal_id > 0) {

          $SQL = "
          SELECT i.*
              ,(
              SELECT SUM(supHist.amount) AS prev_sum
              FROM sub_con_payments_history supHist
              LEFT JOIN sub_con_payments r ON (r.sub_con_payments_id = supHist.sub_con_payments_id)
              WHERE supHist.sub_con_work_order_id =  i.sub_con_work_order_id
              AND r.status != 'Cancelled'
              ) as prev_inv_amount
          FROM sub_con_work_order i
          LEFT JOIN `sub_con` o ON (i.sub_con_id = o.sub_con_id)
          WHERE i.renewal_id = {$renewal_id}
          AND (i.status != 'Cancelled' || i.status IS NULL)
          ";
          $result = $db->sql_query($SQL);
          $numRows = $db->sql_numrows($result);


          while ($row = $db->sql_fetchrow($result)) {
              $text += $row['prev_inv_amount'];
          }

        }

        return $text;
    }
}