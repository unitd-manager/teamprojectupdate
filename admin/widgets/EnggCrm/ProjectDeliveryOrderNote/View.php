<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrderNote_View extends CP_Common_Lib_WidgetViewAbstract
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

    function getDeliveryOrderNotePortal($project_id = '', $delivery_note_id = '', $category = '') {

        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        
        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }
        if($delivery_note_id == ''){
            $delivery_note_id = $fn->getReqParam('delivery_note_id');
        }
        
        $SQL = "
        SELECT j.*
        
        
        FROM `delivery_note` j

        WHERE j.project_id = '{$project_id}'
        
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {
          
               $date   = $fn->getCPDate($row['delivery_note_date'], 'd-m-Y');
             

              $sqljob ="

              SELECT qi.*
              FROM delivery_note_items qi
              WHERE qi.delivery_note_id = '{$row['delivery_note_id']}'
              ";
              $resultForjob  = $db->sql_query($sqljob);
              $numRowsForjob = $db->sql_numrows($resultForjob);

              $addLineItemView = '';
              if($numRowsForjob > 0) {
                  $addLineItemView ="
                  <div class='float_right'>
                      <a href='javascript:void(0);' class='deliverLayoutShow'>View Line Items</a>
                  </div>
                  ";
              }

              $quoteActions    = '';

              $editForQuote    = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=EditForJob&project_id={$project_id}&delivery_note_id={$row['delivery_note_id']}&showHTML=0";

              $urlPrintLinkPdf = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=printDrawingQuotePdf&project_id={$project_id}&delivery_note_id={$row['delivery_note_id']}&showHTML=0";

              $formActionGroupForQuoteLineItem = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=addLineItemForQuoteForm&delivery_note_id={$row['delivery_note_id']}&project_id={$project_id}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";
              
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='float_left'>

                          <a class='editForDoNote'  href='{$editForQuote}' title='Edit Quote'><img src='{$edit_image}' class='icon'></a>

                      </div>
                      <!--<div class='float_left'>
                          <a  class='deleteAddQuote' delivery_note_id='{$row['delivery_note_id']}'>Delete</a>
                      </div>-->
                      <div class='printLink float_left'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                       <div class='float_left'>
                          <a  project_id={$row['project_id']} delivery_note_id = {$row['delivery_note_id']} class='addMultipleDeliveryItem' title='Add Line Item'><img src='{$add_image}' class='icon'></a>
                      </div>
                      
                  </div>
                  ";
              

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }



            
              $rows .= "
              <tbody class='deliverDetailRow'>
                  <tr class='addQuoteRow'>
                      <td data-label='Code'><p>{$row['delivery_note_code']}</p></td>
                      <td data-label='Date'><p>{$date}</p></td>

                      <td data-label='PO/PR NO REF' colspan='3'><p>{$row['ref_po']}</p></td>

                      
                      <td data-label='' class='' colspan='3'>{$addLineItemView}</td>
                      <td data-label='Action' colspan='3'>{$quoteActions}</td>
                  </tr>
                  {$this->getAddLineItemForQuoteListView($project_id,$row['delivery_note_id'])}
              </tbody>
              ";
            $delivery_note_id = $row['delivery_note_id'];

          }
          $text = '';

       
          if($numRows == 0) {

            $text = "

            <div class='float_box mt10 mb10'>

      <a id='addDeliveryProject' class='btn btn-primary' project_id='{$project_id}'>Create Delivery Order</a>

            </div>
            ";
          }

                 
            $text .= "
            <div id='deliveryPortal' class='linkPortalWrapper table-responsive'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='9' align='left' class='rightPanelHeading'>
                              Delivery Order
                            </th>
                        </tr>
                        <tr>
                            <th scope='col'>Code</th>
                            <th scope='col'>Delivery Date</th>
                            <th scope='col' colspan='3' >PO/PR NO REF</th>

                            <th scope='col' colspan='3' ></th>
                            <th scope='col' colspan='3' >Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";
          

          return $text;
    }

    /**
     *
     */
    function getAddLineItemForQuoteListView($project_id, $delivery_note_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        

        $SQL = "
        SELECT qt.*
      
        FROM `delivery_note_items` qt
        LEFT JOIN delivery_note d ON (qt.delivery_note_id = d.delivery_note_id)
        WHERE qt.delivery_note_id = {$delivery_note_id}
        ORDER BY qt.delivery_note_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows        = '';
        
        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image      = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image    = $cpCfg['cp.localPath']."images/delete.png";

            $editForLineItem = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=editLineItem&delivery_note_id={$row['delivery_note_id']}&delivery_note_items_id={$row['delivery_note_items_id']}&project_id={$project_id}&showHTML=0";

            $editText = "
            <div class='float_left'>

                <a class='editForDoLineItemNote' href='{$editForLineItem}' title='Edit Line Item'><img src='{$edit_image}' class='icon'></a>

            </div>
            ";

            $deleteLineItem = "
            <div class='float_left'>

                <a  class='deleteDoLineItem' delivery_note_items_id='{$row['delivery_note_items_id']}' delivery_note_id= '{$row['delivery_note_id']}'  title='Delete Line Item'><img src='{$delete_image}' class='icon'></a></td>

            </div>
            ";

          
            
              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }
             

              $rows .= "
              <tr class = 'quoteLayoutHide showAddLineRow'>
                  <td class='emptyTd'></td>
                  <td>{$row['description']}</td>

                  <td>{$row['delivery_title']}</td>
                  
                  <td>{$row['unit']}</td>
                 <td>{$row['quantity']}</td>
                 
                 
                  <!--<td>{$updation_details}</td>-->
                  <td>{$editText}</td>
              </tr>
              ";
            
        }

        $text = '';

        if ($numRows > 0)  {
            
             
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                 
                    <th></th>
                    <th class='quoteRowBackground'>Description</th>

                    <th class='quoteRowBackground'>Title</th>
                    <th class='quoteRowBackground'>Unit</th>
                    <th class='quoteRowBackground'>Quantity</th>
                    
                    <!--<th class='quoteRowBackground'>Updated By</th>-->
                    <th class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
                ";
            
            return $text;
        }
    }

    /**
     *
     */

    function getAddMultipleJobLineItem1() {

        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $project_id     = $fn->getReqParam('project_id');
        $delivery_note_id       = $fn->getReqParam('delivery_note_id');
                $delivery_note_items_id       = $fn->getReqParam('delivery_note_items_id');

        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');
        
        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowQuote   = $fn->getRecordRowByID('delivery_note_items', 'delivery_note_items_id', $delivery_note_items_id);

        

           
             $service        = "<input type='text' value='' id='service' class='text lineItemDescription' name='service[]'>";
             $accessories     = "<textarea type='text' value='' id='accessories' class='text lineItemDescription'name='accessories[]'></textarea>";
            $remarks     = "<textarea type='text' value='' id='remarks' class='text lineItemDescription' name='remarks[]'></textarea>";
            $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";
            
            $SQL="
        SELECT qt.*
        FROM quote_items qt
        LEFT JOIN quote q ON (q.delivery_note_id = qt.delivery_note_id)
        WHERE q.project_id = '{$project_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rows = '';
        while($row = $db->sql_fetchrow($result)){
            
            $rows = "
            <tr>
                
                <td>{$row['nomenclature']}</td>
                <td>{$row['manufacture']}</td>
                <td>{$row['model']}</td>
                <td>{$row['serial_no']}</td>
               
                <td>{$accessories}</td>
                <td>{$service}</td>
                 <td>{$remarks}</td>
                {$clear}
                 <input type='hidden' name='quote_items_id' value='{$row['quote_items_id']}' />
            </tr>
           
            ";  
          }

            $newRow = "
            <a  class='addRow btn btn-primary mb10' project_id='{$project_id}'>Add Line Item</a>
            ";

            $header ="
            <tr>
              {$newRow}
              
              <div class='quoteLineItemsOverallTotal'>
                Total Amount <span class='quoteLineItemsOverallTotalAmount'>0.00</span>
              </div>
            </tr>
            <tr style='background-color:#EAEAE8;'>
                
                <th width='20%'>Model</th>
                <th width='20%'>Nomenclature</th>
                <th width='20%'>Manufacture</th>
                <th width='20%'>Serial NO</th>
                <!--<th width='60%'>Description</th>-->
                <!--<th width='10%' class='txtCenter'>UoM</th>
                <th class='txtCenter'>Qty</th>
                <th width='13%' class='txtCenter'>Unit Price</th>
                <th width='15%' class='txtCenter'>Total Price</th>
                <th>Discount</th>-->
                <th>Accessories</th>
                <th>Service</th>
                <th>Remarks</th>

                <th width='2%' ></th>
            </tr>
            ";
        


        $formAction = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=addMultipleJobLineItemSubmit1&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        
        $text = "
        <form id='addMultipleJobLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>

            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='delivery_note_id' value='{$delivery_note_id}' />
            
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $project_id     = $fn->getReqParam('project_id');
        $delivery_note_id       = $fn->getReqParam('delivery_note_id');
       
        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowQuote   = $fn->getRecordRowByID('delivery_note', 'delivery_note_id', $delivery_note_id);

            $part_no     = "<input type='text' value='' id='partno' class='text lineItemPartno' name='partno[]'>";
            $description = "<textarea type='text' value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
            $title       = "<textarea type='text' value='' id='delivery_title' class='text lineItemTitle' name='delivery_title[]'></textarea>";
            $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
            $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
            $amount      = "<input type='text' value='' id='unit_price' class='text lineItemUnitPrice' name='unit_price[]'>";
            $total_cost  = "<td><input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'></td>";
            $remarks     = "<textarea type='text' value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
            $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";
            
           

            $rows = "
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                {$clear}
            </tr>
            ";  

            $newRow = "
            <a  class='addRow btn btn-primary mb10' project_id='{$project_id}'>Add Line Item</a>
            ";

            $header ="
           
            <tr style='background-color:#EAEAE8;'>
                <th width='50%'>Title</th>
                <th width='60%'>Description</th>
                <th width='10%' class='txtCenter'>UoM</th>
                <th class='txtCenter'>Qty</th>
          
                <th width='2%' ></th>
            </tr>
            ";
        

        $formAction = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=addMultipleLineItemSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        
        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='delivery_note_id' value='{$delivery_note_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getAddMultipleLineItemDrawing() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $drawingNumber   = "<textarea type='text' id='drawingNumber' class='text drawingNumber' name='drawing_number[]'></textarea>";
        $drawingTitle    = "<textarea type='text' id='drawingTitle' class='text drawingTitle' name='drawing_title[]'></textarea>";
        $drawingRevision = "<input type='text' value='' id='drawingRevision' class='text drawingRevision' name='drawing_revision[]'>";
        $clear           = "<td class='text'><a  class='clearDrawingLineItem'><u>Clear</u></a></td>";
        
        $text = "
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        ";          

        return $text;
    }

    /**
     *
     */
    function getAddLineDrawingItemRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $drawingNumber   = "<textarea type='text' id='drawingNumber' class='text drawingNumber' name='drawing_number[]'></textarea>";
        $drawingTitle    = "<textarea type='text' id='drawingTitle' class='text drawingTitle' name='drawing_title[]'></textarea>";
        $drawingRevision = "<input type='text' value='' id='drawingRevision' class='text drawingRevision' name='drawing_revision[]'>";
        $clear           = "<td class='text'><a  class='clearDrawingLineItem'><u>Clear</u></a></td>";
      
        $rows = "
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    function getEditForJob() {

        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $delivery_note_id       = $fn->getReqParam('delivery_note_id');
        $project_id     = $fn->getReqParam('project_id');

        $rowJob      = $fn->getRecordRowByID('delivery_note', 'delivery_note_id', $delivery_note_id);
        
        $rowProject    = $fn->getRecordRowByID('project', 'project_id', $project_id);


        $formActionEditForQuote = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=EditForJobSubmit&delivery_note_id={$rowJob['delivery_note_id']}&project_id={$project_id}&showHTML=0";



        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

        $sqldeliverymode = $fn->getValueListSQL('opportunityDelivery');


        


        /*$provision_by_client = '';
        $provision_by_krs = '';
        if ($rowProject['category'] == 'Scaffolding'){
            $provision_by_client = "{$formObj->getTextAreaRow('Provision by Client', 'provision_by_client',$rowJob['provision_by_client'])}";
            $provision_by_krs = "{$formObj->getTextAreaRow('Provision by KRS', 'provision_by_krs',$rowJob['provision_by_krs'])}";
        }*/


        $text = "

        <form id='editForDoNote' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>

            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getTBRow('Code', 'delivery_note_code', $rowJob['delivery_note_code'])}</td>
                        <td>{$formObj->getDateRow('Delivery Date', 'delivery_note_date',$rowJob['delivery_note_date'])}</td>
                        <td>{$formObj->getTBRow('Ref Po No', 'ref_po',$rowJob['ref_po'])}</td>

                        <!--<td>{$formObj->getTBRow('Prepared By', 'prepared_by', $_SESSION['userFullName'])}</td>-->
                    </tr>

                    <tr>
                     
                        <td>{$formObj->getDateRow('Received Date', 'received_date', $rowJob['received_date'])}</td>
                        <td>{$formObj->getTBRow('Received By', 'received_by',$rowJob['received_by'])}</td>
                        <td>{$formObj->getTARow('Note', 'note', $rowJob['note'])}</td>
                    </tr>
                  


                </table>
                <input type='hidden' name='project_id' value='{$project_id}' />
                <input type='hidden' name='delivery_note_id' value='{$delivery_note_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     * Add Line Item Edit
     */
    function getEditLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $delivery_note_items_id  = $fn->getReqParam('delivery_note_items_id');
        //$opportunity_id  = $fn->getReqParam('opportunity_id');
        $project_id      = $fn->getReqParam('project_id');

        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowQuoteItem   = $fn->getRecordRowByID('delivery_note_items', 'delivery_note_items_id', $delivery_note_items_id);
        $rowQuote       = $fn->getRecordRowByID('delivery_note', 'delivery_note_id', $rowQuoteItem['delivery_note_id']);
        
        $exp   = array('sqlType' => 'OneField');
        $expVL = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?widget=enggCrm_projectDeliveryOrderNote&_spAction=editLineItemSubmit&lnkRoom={$tv['lnkRoom']}&delivery_note_items_id={$delivery_note_items_id}&showHTML=0";
                            $sqlstatus = $fn->getValueListSQL('deliveryStatus');

        
        
          $text = "

          <form id='editForDoLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>

              <fieldset>
                  
               {$formObj->getTBRow('Title', 'delivery_title', $rowQuoteItem['delivery_title'])}
               {$formObj->getTBRow('Description', 'description', $rowQuoteItem['description'])}
                  {$formObj->getTBRow('Unit', 'unit', $rowQuoteItem['unit'])}
                  {$formObj->getTBRow('Quantity', 'quantity', $rowQuoteItem['quantity'])}
               
                  <input type='hidden' name='delivery_note_items_id' value='{$delivery_note_items_id}' />
                  <input type='hidden' name='project_id' value='{$project_id}' />
                  
              </fieldset>
          </form>
          ";
        

        return $text;
    }

    /**
     *
     */
    function getPrintLinkForPdf() {
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
        //$pdf->setPrintFooter(false);

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

        $delivery_note_id = $fn->getReqParam('delivery_note_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.s_no
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.unit_price
              ,qi.remarks
              ,p.project_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,co.mobile
              ,c.fax
              ,co.salutation
              ,co.first_name
              ,e.email AS employee_email
              ,e.mobile AS employee_mobile
              ,j.designation
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.delivery_note_id = q.delivery_note_id)
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (employee e) ON (e.employee_id = q.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = q.employee_id)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.delivery_note_id = {$delivery_note_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#000000; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }

        if($company['alternate_email'] != "") {
            $company['employee_email'] = $company['alternate_email'];
        }

        if($company['alternate_name'] != "") {
            $company['created_by'] = $company['alternate_name'];
        }

        if($company['quote_manual_code'] != "") {
            $company['quote_code'] = $company['quote_manual_code'];
        }

        $billing_address_street = '';
        if($company['billing_address_street'] != ''){
            $billing_address_street = ',<br/>: '.$company['billing_address_street'];
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation From :</td>
                    </tr>
                    <tr><td width="38%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['salutation'].'. '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].$billing_address_street.', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['phone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['fax'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Payment</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['payment_method'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['created_by'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyPhone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyFax'].'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project location</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_location'].'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project Reference</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_reference'].'</td>
                    </tr>
                </table>';

        $SQLStaff = "
        SELECT e.team, e.employee_id, e.project_manager
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $SQLDesig = "
        SELECT j.designation
        FROM project_employee pe
        LEFT JOIN (job_information j) ON (j.employee_id = pe.employee_id)
        WHERE pe.employee_id = '{$rowStaff['employee_id']}'
          AND pe.project_id = '{$company['project_id']}' 
        ";
        $resultDesig  = $db->sql_query($SQLDesig);
        $rowDesig = $db->sql_fetchrow($resultDesig);

        if ($_SESSION['userGroupName'] == 'Projects' ||
            $_SESSION['userGroupName'] == 'ENBAVALAVAN' ||
            $_SESSION['userGroupName'] == 'BILLAL' ||
            $_SESSION['userGroupName'] == 'KUMAR' ||
            $_SESSION['userGroupName'] == 'RAGU' ||
            $_SESSION['userGroupName'] == 'KANNAN' ||
            $_SESSION['userGroupName'] == 'SEET' ||
            $_SESSION['userGroupName'] == 'VICKY' ||
            $_SESSION['userGroupName'] == 'BENJAMIN' ||
            $rowDesig['designation'] == 'Engineer' ||
            $rowDesig['designation'] == 'Assistant Engineer'
          ) {
          $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr bgcolor="#92d14f">
                              <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                              <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                              <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                              <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        } else {
          $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr bgcolor="#92d14f">
                              <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                              <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                              <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE($)</th>
                              <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE($)</th>
                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        }
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px; font-weight:bold; border-left:1px solid #000;border-right:1px solid #000;"><u>'.nl2br($row['quote_item_title']).'</u><br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }
            $subtotal_amount = 0;

            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 2);
            } else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                $subtotal_amount = round($row['qty'] * $row['unit_price'], 2);
            } else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                $subtotal_amount = round($row['unit_price'], 2);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            if($row['quantity'] == 0) {
                $row['quantity'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = "";
            }

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            $QICount = $fn->getRecordCount('quote_items', "delivery_note_id = {$delivery_note_id} AND s_no != ''");

            if($QICount > 0) {
                $sNo = $row['s_no'];
            } else {
                $sNo = $count;
            }

            if ($_SESSION['userGroupName'] == 'Projects' ||
                $_SESSION['userGroupName'] == 'ENBAVALAVAN' ||
                $_SESSION['userGroupName'] == 'BILLAL' ||
                $_SESSION['userGroupName'] == 'KUMAR' ||
                $_SESSION['userGroupName'] == 'RAGU' ||
                $_SESSION['userGroupName'] == 'KANNAN' ||
                $_SESSION['userGroupName'] == 'SEET' ||
                $_SESSION['userGroupName'] == 'VICKY' ||
                $_SESSION['userGroupName'] == 'BENJAMIN' ||
                $rowDesig['designation'] == 'Engineer' ||
                $rowDesig['designation'] == 'Assistant Engineer'
              ) {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$sNo.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;"></td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;"></td>
                              </tr>
                      ';
            } else {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$sNo.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                              </tr>
                      ';              
            }

            $subtotalValue += $subtotal_amount;

            if($company['gst'] == 1) {
                $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
                $gstvalue       = $subtotalValue * $gsttaxvalue / 100;
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gstvalue);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        $fraction = $fraction + 1;
                    }

                    $gstvalue = $integer . "." . $fraction;
                }

                $totalvalue = $gstvalue + $subtotalValue;
            } else {
                $totalvalue = $subtotalValue;
            }

            $countCheck++;
            $count++;
        }

        if($company['discount'] && 
            ($_SESSION['userGroupName'] != 'Projects' &&
            $_SESSION['userGroupName'] != 'ENBAVALAVAN' &&
            $_SESSION['userGroupName'] != 'BILLAL' &&
            $_SESSION['userGroupName'] != 'KUMAR' &&
            $_SESSION['userGroupName'] != 'RAGU' &&
            $_SESSION['userGroupName'] != 'KANNAN' &&
            $_SESSION['userGroupName'] != 'SEET' &&
            $_SESSION['userGroupName'] != 'VICKY' &&
            $_SESSION['userGroupName'] != 'BENJAMIN' && 
            $rowDesig['designation'] != 'Engineer' &&
            $rowDesig['designation'] != 'Assistant Engineer')
        ) {
            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                                <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"><br/><br/>Less Discount</td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"><br/><br/>-'.number_format($company['discount'], 2).'</td>
                            </tr>
                    ';
        }

        $totalvalue      = $totalvalue - $company['discount'];
        $amount_in_words = $fn->getConvertNumber($totalvalue);

        if($company['gst'] == 1) {
          $emptyRow = 5 - $countCheck;
        } else {
          $emptyRow = 6 - $countCheck;
        }

        for($i = 0; $i <= $emptyRow; $i++) {
          $tbl3 = $tbl3.'<tr>
                            <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                            <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"></td>
                        </tr>
                  ';
        }

        if($_SESSION['userGroupName'] != 'Projects' &&
          $_SESSION['userGroupName'] != 'ENBAVALAVAN' &&
          $_SESSION['userGroupName'] != 'BILLAL' &&
          $_SESSION['userGroupName'] != 'KUMAR' &&
          $_SESSION['userGroupName'] != 'RAGU' &&
          $_SESSION['userGroupName'] != 'KANNAN' &&
          $_SESSION['userGroupName'] != 'SEET' &&
          $_SESSION['userGroupName'] != 'VICKY' &&
          $_SESSION['userGroupName'] != 'BENJAMIN' &&
          $rowDesig['designation'] != 'Engineer' &&
          $rowDesig['designation'] != 'Assistant Engineer'
          ) {
        if($company['gst'] == 1) {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">SUB TOTAL</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">'.number_format($subtotalValue - $company['discount'],2).'</td>
                          </tr>
                          <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000;">'.number_format($gstvalue, 2).'</td>
                           </tr>
                           <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL AMOUNT</td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL EXCLUDING GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        }
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td colspan="6" style="border-top:1px solid #000;"></td>
                           </tr>
                          </tbody>
                        </table>';          
        }

        $tbl4 = '
        <table border="1" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; background-color:#92d14f;">Other Comments or Special Instructions :</td>
            </tr>
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employee_id']}'
        AND room_name   = 'payroll_employee'
        AND record_type = 'digitalSign'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);

        $imageAttached = '';
        while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
        }

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"><img src="'.$imageAttached.'"></td>
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
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        ob_end_clean();
        $pdf->Output($download_title, 'I');
    }

   /**
     *
     */
    function getPrintDrawingQuotePdf() {
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
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintFooter(false);

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

        $delivery_note_id = $fn->getReqParam('delivery_note_id');

        $SQL = "
        SELECT c.*
              ,do.delivery_title
              ,do.description
              ,do.unit
              ,do.quantity
              ,o.company_id
              ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,cy.address_town AS billing_address_town
              ,cy.address_state AS billing_address_state
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
              ,cy.company_id
              ,co.email
              ,cy.fax
              ,cy.phone
              ,co.mobile
              ,co.salutation
              ,co.first_name
     
              
        FROM delivery_note c
         LEFT JOIN (delivery_note_items do) ON (c.delivery_note_id = do.delivery_note_id)
        LEFT JOIN (project o) ON (o.project_id = c.project_id)
        LEFT JOIN (company cy) ON (cy.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.delivery_note_id = '{$delivery_note_id}'
       ORDER BY do.delivery_note_id ASC

        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);


        $quote_date   = $fn->getCPDate($company['delivery_note_date'], 'd/M/Y');
        $today      = date("d-m-Y");

       

        
       

        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:26px;">DELIVERY NOTE</td>
            </tr>
        </table>
   

       
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/> '.$company['billing_address_street'];
        }

       /* $revNo = '';
        if ($company['revision']) {
            $revNo = ' - R'.$company['revision'];
        }

        if($company['alternate_email'] != "") {
            $company['employee_email'] = $company['alternate_email'];
        }

        if($company['alternate_name'] != "") {
            $company['created_by'] = $company['alternate_name'];
        }*/

        if($company['phone'] != ''){
            $phone = $company['phone'];
        } else {
            $phone = $company['mobile'];                
        }
  $tbl2 = '<table border="0" width="100%" cellpadding="0" >
                    <tr>
                        <td width="46%">
                            <table border="0" cellpadding="3" >
                                <tr>
                                   
                                    <td width="69%" style=" font-size:10px;font-weight:bold; "> To:   </td>
                                </tr>
                                <tr>
                                   
                                    <td width="69%" style=" font-size:10px;font-weight:bold; "> '.$company['company_name'].'   </td>
                                </tr>
                                <tr>
                                   
                                    <td width="69%" style="font-size:10px; "> '.$company['billing_address_flat'].$rowStreet.' <br/> '.$company['billing_address_country'].'  '.$company['billing_address_po_code'].'</td>
                                </tr> 
                             
                               
                            </table>
                        </td>
                        <td width="5%">
                           
                        </td>
                        <td width="41%">
                            <table border="0" cellpadding="3" >
                                <tr>
                                    <td align="right" width="40%" style=" font-size:10px; font-weight:bold;">Received Date</td>
                                      <td width="4%" style=" font-size:10px; fontweight-:bold;">:</td>
                                    
                                    <td width="56%" style=" font-size:10px; font-weight:bold;">'.$quote_date.'</td>

                                </tr>
                                <tr>
                                    <td align="right" width="40%" style=" font-size:10px; ">DN</td>
                                     <td width="4%" style=" font-size:10px; ">#</td>
                                    
                                    <td align="left" width="56%" style=" font-size:10px;"> '.$company['delivery_note_code'].'</td>
                                </tr>
                               
                                 
                            </table>
                        </td>
                       
                    </tr>
                </table>';

       $tbl3 ='<table border="1"  cellpadding="4"  width="100%">

                        <thead>
                           
                            <tr>
                                <th width="8%" align="center" style="font-size:8px; font-weight:bold;">SI.No</th>
                            
                                <th width="45%" align="center" style="font-size:10px; font-weight:bold;">Description</th>
                               <th width="23%" align="center" style="font-size:10px; font-weight:bold;">Qty</th>
                               <th width="24%" align="center" style="font-size:10px; font-weight:bold;">Remarks</th>
                                
                            </tr>

                     </thead>
                   <tbody style="display: table; table-layout: fixed; height: 600px;">
                         
                         ';

  
       $count      = 1;
        $countCheck = 1;
     

       while($row = $db->sql_fetchrow($result)) {
          if ($row['delivery_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="8%"  style=""></td>
                                    <td width="45%" style="font-size:10px; font-weight:bold; "><u>'.nl2br($row['delivery_title']).'</u><br/></td>
                                    <td width="23%"  style=""></td>
                                    <td width="24%"  style=""></td>
                                   

                                </tr>
                        ';
            }

         
        $tbl3 = $tbl3.'<tr>
                                    <td width="8%"  style="font-size:10px;">'.$count.'</td>
                                    <td width="45%" style="font-size:10px;">'.nl2br($row['description']).'</td>
                                    <td width="23%"  style="text-align:center;font-size:10px;">'.$row['unit'].'</td>
                                    <td width="24%"  style="text-align:center;font-size:10px;">'.$row['quantity'].'</td>
                                   

                                </tr>
                                   <br/>'; 
            $count++;
            $countCheck++;

            
        }

               
        $tbl3 = $tbl3.'</tbody></table>';  
             $tbl5 = '       

                   <table border="0" width="100%" cellpadding="4">
         
               <tr>

              <td width="14%"  style="font-size:10px;font-weight:BOLD;  ">Received By </td>
               
               </tr>

               <tr>

              <td width="14%"  style="font-size:10px;  ">Name </td>
              <td width="34%" style="font-size:10px;">'.$company['received_by'].'</td>

               </tr>
                <tr>

              <td width="14%"  style="font-size:10px;  ">Date </td>
              <td width="34%" style="font-size:10px;">'.$company['received_date'].'</td>

               </tr>
                <tr>

              <td width="14%"  style="font-size:10px;  ">Sign </td>
              <td width="34%" style="font-size:10px;"></td>

               </tr>
             
        </table>';
        


        $tbl4 = '
        <table border="0" width="100%">
            
            <tr>
                <td border="0"  style="font-size:10px;font-weight:bold"; width="90%">Materials, are Received in good condition</td>
            </tr>
            <br/>

        </table>
        ';


       
       $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(9);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['delivery_note_code'] . '-Quote.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }

    /**
     *

     */
    function getViewJobLog() {

        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $project_id = $fn->getReqParam('project_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $company_prefix = explode(' ', $companyRec['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        $SQL = "
        SELECT q.*
        FROM `quote_log` q
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        WHERE p.project_id = {$project_id}
        ORDER BY q.quote_log_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {
          if($row['drawing_nos'] == 1) {
              $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');
              
              $sqlQuoteItems ="
              SELECT *
              FROM quote_items_log qi
              WHERE qi.quote_log_id = {$row['quote_log_id']}
              ";
              $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
              $numRowsForQuoteItems = $db->sql_numrows($resultForQuoteItems);

              $addLineItemView = '';
              if($numRowsForQuoteItems > 0) {
                  $addLineItemView ="
                  <div class='float_right'>

                      <a href='javascript:void(0);' class='deliverLayoutShow'>View Line Items</a>
                  </div>
                  ";
              }

              $quoteActions    = '';
              $urlPrintLinkPdf = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=printDrawingQuoteLogPdf&quote_log_id={$row['quote_log_id']}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";
              if ($row['quote_status'] == 'Awarded' || $row['quote_status'] == 'Order Raised') {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              } else {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink float_left'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              }

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $confirmedQuoteStatus = '';
                if($row['status'] == 'Confirmed') {
                    $confirmedQuoteStatus = 'confirmedQuote';
                }


              $cancelledQuoteStatus = '';
              if($row['quote_status'] == 'Cancelled') {
                  $cancelledQuoteStatus = 'cancelledQuote';
              }

              $quote_amount = number_format($row['total_amount'] - $row['discount'], 2);
              $discount     = number_format($row['discount'], 2);

              $rows .= "
              <tbody class='deliverDetailRow'>
                  <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                      <td>{$row['revision']}</td>
                      <td>
                          <a class='creationModificationQuote' quote_log_id='{$row['quote_log_id']}'>
                            <u>{$row['quote_code']}</u>
                          </a>
                      </td>
                      <td>{$quote_date}</td>
                      <td class='quoteStatusTd'>{$row['quote_status']}</td>
                      <td class='txtRight'>{$discount}</td>
                      <td class='txtRight' colspan='2'>{$quote_amount}</td>
                      <td class='' colspan='2'>{$addLineItemView}</td>
                      <td>{$quoteActions}</td>
                  </tr>
                  {$this->getAddLineItemForQuoteLogListView($opportunity_id,$project_id,$row['quote_log_id'])}
              </tbody>
              ";
          } else {
              $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

              $sqlQuoteItems ="
              SELECT *
              FROM quote_items_log qi
              WHERE qi.quote_log_id = {$row['quote_log_id']}
              ";

              $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
              $subtotalValue = 0;
              $totalvalue    = 0;
              while ($rowQuoteItems = $db->sql_fetchrow($resultForQuoteItems)) {
                  $subtotal_amount = 0; 
                  if($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] > 0) {
                      $subtotal_amount = round($rowQuoteItems['quantity'] * $rowQuoteItems['unit_price'], 2);
                  } elseif ($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] == 0) {
                      $subtotal_amount = round($rowQuoteItems['unit_price'], 2);
                  } elseif ($rowQuoteItems['amount'] > 0) {
                      $subtotal_amount = round($rowQuoteItems['amount'], 2);
                  }

                  $subtotalValue += $subtotal_amount;
                  
                  if($row['gst'] == 1) {
                    $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
                    $gstvalue       = $subtotalValue * $gsttaxvalue / 100;

                    /* Taking two decimal values for gst amount */

                    $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gstvalue);

                        /* Checking whether 3rd decimal point is more than or equal to 5
                           If Yes, add 1 to 2nd decimal point

                         */

                        $gstDecimalMore = substr($fraction, 2, 1);
                        $fraction = substr($fraction, 0, 2);
                        if ($gstDecimalMore >= 5) {
                            $fraction = $fraction + 1;
                        }

                        $gstvalue = $integer . "." . $fraction;
                    }

                    $totalvalue = $gstvalue + $subtotalValue;
                  } else {
                    $totalvalue = $subtotalValue;
                  }
              }

              $addLineItemView = '';
              if($totalvalue > 0) {
                  $addLineItemView ="
                  <div class='float_right'>
                      <a href='javascript:void(0);' class='deliverLayoutShow'>View Line Items</a>
                  </div>
                  ";
              }

              $quoteActions = '';

              $urlPrintLinkPdf  = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=printLinkForLogPdf&quote_log_id={$row['quote_log_id']}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";
              if ($row['quote_status'] == 'Awarded' || $row['quote_status'] == 'Order Raised') {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              } else {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink float_left'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              }

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $confirmedQuoteStatus = '';
                if($row['status'] == 'Confirmed') {
                    $confirmedQuoteStatus = 'confirmedQuote';
                }


              $cancelledQuoteStatus = '';
              if($row['quote_status'] == 'Cancelled') {
                  $cancelledQuoteStatus = 'cancelledQuote';
              }

              $quote_amount = number_format($totalvalue - $row['discount'], 2);

              $discount = number_format($row['discount'], 2);

              $revision = "";
              if($row['revision'] != "") {
                  $revision = $row['revision']. ' ';
              }

              $rows .= "
              <tbody class='deliverDetailRow'>
                  <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                      <td>{$row['revision']}</td>
                      <td>
                          <a class='creationModificationQuote' quote_log_id='{$row['quote_log_id']}'>
                              <u>{$row['quote_code']}</u>
                          </a>
                      </td>
                      <td>{$quote_date}</td>
                      <td class='quoteStatusTd'>{$row['quote_status']}</td>
                      <td class='txtRight'>{$discount}</td>
                      <td class='txtRight' colspan='2'>{$quote_amount}</td>
                      <td class='' colspan='2'>{$addLineItemView}</td>
                      <td>{$quoteActions}</td>
                  </tr>
                  {$this->getAddLineItemForQuoteLogListView($opportunity_id,$project_id,$row['quote_log_id'])}
              </tbody>
              ";

          }

        }

          $text = '';

          if($numRows > 0)  {
            $ChangeHead = "<th class='txtRight' colspan='2'>Amount</th>";
            
            $text .= "
            <div id='quotesPortal' class='linkPortalWrapper'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='9' align='left' class='rightPanelHeading'>
                              Quotations
                            </th>
                        </tr>
                        <tr>
                            <th>Revision</th>
                            <th>Quote Code</th>
                            <th>Quote Date</th>
                            <th>Quote Status</th>
                            <th class='txtRight'>Discount</th>
                            {$ChangeHead}
                            <th colspan='2'></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";
          } else {
            $text = "No history records found.";
          }

          return $text;
    }


    /**

     *
     */
    function getAddLineItemForQuoteLogListView($opportunity_id, $project_id, $quote_log_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT qt.*
              ,q.drawing_nos
        FROM `quote_items_log` qt
        LEFT JOIN quote_log q ON (qt.quote_log_id = q.quote_log_id)
        WHERE q.project_id = {$project_id}
        AND qt.quote_log_id = {$quote_log_id}
        ORDER BY qt.quote_items_log_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows        = '';
        $drawing_nos = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image      = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image    = $cpCfg['cp.localPath']."images/delete.png";

            $addclass = '';
            if ($row['project_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            if($row['drawing_nos'] == "" || $row['drawing_nos'] == 0) {
              $total_amount = 0;
              if($row['unit_price'] > 0 && $row['quantity'] > 0) {
                  $total_amount = round($row['quantity'] * $row['unit_price'], 2);
              } elseif ($row['unit_price'] > 0 && $row['quantity'] == 0) {
                  $total_amount = round($row['unit_price'], 2);
              } elseif ($row['amount'] > 0) {
                  $total_amount = round($row['amount'], 2);
              }

              $total_amount = number_format($total_amount, 2);
              $unit_price   = number_format($row['unit_price'], 2);

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $rows .= "
              <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td class='descriptionWrap'>{$row['title']}</td>
                  <!--<td colspan='3' class='descriptionWrap'>{$row['description']}</td>-->
                  <td align='center'>{$row['quantity']}</td>
                  <td class='amountRow'>{$unit_price}</td>
                  <td class='amountRow'>{$total_amount}</td>
                  <!--<td>{$updation_details}</td>-->
              </tr>
              ";
            } else {
              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $rows .= "
              <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td colspan='2' class='descriptionWrap'>{$row['drawing_number']}</td>
                  <td colspan='4' class='descriptionWrap'>{$row['drawing_title']}</td>
                  <td align='center'>{$row['drawing_revision']}</td>
                  <td>{$updation_details}</td>
              </tr>";
            }

            $drawing_nos = $row['drawing_nos'];
        }

        $text = '';

        if ($numRows > 0)  {
            if($drawing_nos == "" || $drawing_nos == 0) {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <!--<th class='quoteRowBorder'></th>
                    <th class='quoteRowBordersecond'></th>-->
                    <th></th>
                    <th class='quoteRowBackground'>Title</th>
                    <!--<th colspan='3' class='quoteRowBackground'>Description</th>-->
                    <th class='quoteRowBackground txtCenter'>Qty</th>
                    <th class='quoteRowBackground txtRight'>Unit Price</th> 
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <!--<th class='quoteRowBackground'>Updated By</th>-->
                </tr>
                {$rows}
                ";
            } else {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th></th>
                    <th colspan='2' class='quoteRowBackground'>Drawing Number</th>
                    <th colspan='4' class='quoteRowBackground'>Drawing Title</th>
                    <th class='quoteRowBackground txtCenter'>Revision</th>
                    <th class='quoteRowBackground'>Updated By</th>
                </tr>
                {$rows}
                ";
            }

            return $text;
        }
    }

    /**
     *
     */
    function getPrintLinkForLogPdf() {
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
        //$pdf->setPrintFooter(false);

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

        $quote_log_id = $fn->getReqParam('quote_log_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.unit_price
              ,qi.remarks
              ,p.project_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,co.mobile
              ,c.fax
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote_log q
        LEFT JOIN (quote_items_log qi) ON (qi.quote_log_id = q.quote_log_id)
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (employee e) ON (e.employee_id = q.employee_id)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.quote_log_id = {$quote_log_id}
        ORDER BY qi.quote_items_log_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#000000; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation From :</td>
                    </tr>
                    <tr><td width="38%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].',<br/>: '.$company['billing_address_street'].', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['phone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['fax'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Payment</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['payment_method'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyPhone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyFax'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Created by</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['created_by'].'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project location</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_location'].'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project Reference</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_reference'].'</td>
                    </tr>
                </table>';


        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr bgcolor="#92d14f">
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE($)</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE($)</th>
                        </tr>
                    </thead>
                    <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px; font-weight:bold; border-left:1px solid #000;border-right:1px solid #000;"><u>'.nl2br($row['quote_item_title']).'</u><br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 2);
            } else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                $subtotal_amount = round($row['qty'] * $row['unit_price'], 2);
            } else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                $subtotal_amount = round($row['unit_price'], 2);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            if($row['quantity'] == 0) {
                $row['quantity'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = "";
            }

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;

            if($company['gst'] == 1) {
                $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
                $gstvalue       = $subtotalValue * $gsttaxvalue / 100;
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gstvalue);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        $fraction = $fraction + 1;
                    }

                    $gstvalue = $integer . "." . $fraction;
                }

                $totalvalue = $gstvalue + $subtotalValue;
            } else {
                $totalvalue = $subtotalValue;
            }

            $countCheck++;
            $count++;
        }

        if($company['discount']) {
            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                                <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"><br/><br/>Less Discount</td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"><br/><br/>-'.number_format($company['discount'], 2).'</td>
                            </tr>
                    ';
        }

        $totalvalue      = $totalvalue - $company['discount'];
        $amount_in_words = $fn->getConvertNumber($totalvalue);

        if($company['gst'] == 1) {
          $emptyRow = 7 - $countCheck;
        } else {
          $emptyRow = 8 - $countCheck;
        }

        for($i = 0; $i <= $emptyRow; $i++) {
          $tbl3 = $tbl3.'<tr>
                            <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                            <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"></td>
                        </tr>
                  ';
        }

        if($company['gst'] == 1) {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">SUB TOTAL</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">'.number_format($subtotalValue - $company['discount'],2).'</td>
                          </tr>
                          <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000;">'.number_format($gstvalue, 2).'</td>
                           </tr>
                           <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL AMOUNT</td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL EXCLUDING GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        }

        $tbl4 = '
        <table border="1" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; background-color:#92d14f;">Other Comments or Special Instructions :</td>
            </tr>
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employee_id']}'
        AND room_name   = 'payroll_employee'
        AND record_type = 'digitalSign'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);

        $imageAttached = '';
        while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
        }

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"><img src="'.$imageAttached.'"></td>
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
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintDrawingQuoteLogPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootPrintQuoteDrawing.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintFooter(false);

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

        $quote_log_id = $fn->getReqParam('quote_log_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.drawing_number
              ,qi.drawing_title
              ,qi.drawing_revision
              ,p.project_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,co.salutation
              ,co.first_name
        FROM quote_log q
        LEFT JOIN (quote_items_log qi) ON (qi.quote_log_id = q.quote_log_id)
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        WHERE q.quote_log_id = {$quote_log_id}
        ORDER BY qi.quote_items_log_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:10px;line-height:10px;">'.$company['billing_address_street'].'</td>
            </tr>
            ';
        }

        $tbl2 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px; font-weight:bold;line-height:16px;">'.$company['company_name'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_flat'].'</td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">Tel : '.$company['phone'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:30px; font-weight:bold;">Attn : '.$company['salutation'].'. '.$company['first_name'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <div style="font-size:10px;line-height:16px;">
        '.$company['intro_quote'].'
        </div>
        ';

        $tbl4 ='<table border="1"  cellpadding="4"  width="100%">
                    <thead>
                        <tr>
                            <th width="5%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S.NO</th>
                            <th width="30%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING NUMBER</th>
                            <th width="50%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING TITLE</th>
                            <th width="15%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">REVISION</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $tbl4 = $tbl4.'<tr>
                                <td width="5%"  style="border:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="30%" align="center" style="border:1px solid #000;font-size:10px;">'.nl2br($row['drawing_number']).'</td>
                                <td width="50%" style="font-size:10px;border:1px solid #000;">'.nl2br($row['drawing_title']).'</td>
                                <td width="15%" align="center" style="font-size:10px;border:1px solid #000;">'.$row['drawing_revision'].'</td>
                            </tr>
                    ';
            $count++;
        }
        
        $tbl4 = $tbl4.'</tbody></table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px;line-height:18px;">Yours sincerely,</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:18px;">'.$cpCfg['cp.companyName'].'</td>
            </tr>
        </table>';

        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-7);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }
}
