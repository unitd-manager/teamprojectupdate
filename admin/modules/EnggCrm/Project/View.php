<?
class CPL_Admin_Modules_EnggCrm_Project_View extends CP_Admin_Modules_EnggCrm_Project_View
{

  /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $currency = '';
            if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
                $currency = $row['currency'];
            }

            $stage = '';
            if ($cpCfg['m.enggCrm.project.showStage'] == 1){
                $stage = $listObj->getListDataCell($row['stage']);
            }
            $rowComp = $fn->getRecordByCondition('order', "project_id = '{$row['project_id']}'");

            $orderUrl = "/admin/index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$rowComp['order_id']}";
    

                $buttonfinanace="<div class=''>
         <a href= {$orderUrl} target='_blank' style='color:#000000;' class='button'>
              <u>Go to Finance</u>
         </a>
     </div>";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['project_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$stage}
            {$listObj->getListDataCell($buttonfinanace)}
            {$listObj->getListRowEnd($row['project_id'])}
            ";

            $rowCounter++;
        }

        $stage = '';
        if ($cpCfg['m.enggCrm.project.showStage'] == 1){
            $stage = $listObj->getListHeaderCell('Stage', 'p.statge');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.project_code', 'w50')}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Company', 'c.company_name')}
        {$listObj->getListHeaderCell('Contact', 'contact_name')}
        {$listObj->getListHeaderCell('Category', 'p.category')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
        {$stage}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$this->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $fn               = Zend_Registry::get('fn');
        $db               = Zend_Registry::get('db');
        $tv               = Zend_Registry::get('tv');
        $cpCfg            = Zend_Registry::get('cpCfg');
        $displayLinkData  = Zend_Registry::get('displayLinkData');
        $pager            = Zend_Registry::get('pager');
        $media            = Zend_Registry::get('media');
        $widgetsArrAccess = Zend_Registry::get('widgetsArrAccess');
        $widgetsArr       = Zend_Registry::get('widgetsArr');
        $comment          = getCPPluginObj('common_comment');

        $text = "";

        $rightLinkInv = "<a id='raiseInvoice' class='actionButtons' href=\"javascript:Invoice.raiseInvoice('project')\">Raise Invoice</a>";

        $costing = '';
        if ($cpCfg['m.enggCrm.project.showCostingTable'] == 1){
            $exportUrl = "index.php?_topRm=project&module=enggCrm_costing&showHTML=0&_spAction=exportCosting&project_id={$row['project_id']}";
            $rightLink = "<a class='actionButtons' href='{$exportUrl}'>Export to Excel</a>&nbsp;";
            $costing = $displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_costingLink', 'Costing Table', $row, '', $rightLink);
        }

        $record_id = $fn->getIssetParam($row, 'project_id');

        //$addQuoteBtn = '';
        $quoteRows = $fn->getRecordCount('quote', "project_id = {$row['project_id']}");
        $rowComp = $fn->getRecordByCondition('order', "project_id = '{$row['project_id']}'");

        //if ($quoteRows == 0) {
        //}

        $quoteColumn = '';
        $sqlmanpower="
        SELECT et.*
        FROM employee_timesheet et
        WHERE project_id={$row['project_id']}
        ";
        $resultmanpower = $db->sql_query($sqlmanpower);
        $rowmanpower    = $db->sql_fetchrow($resultmanpower);
        if (is_array($rowmanpower) && $rowmanpower['project_id'] != '') {
          $quoteColumn = $this->getQuoteColumnDisplay($row['project_id']);
        }

        $staffPortal = '';
        if ($_SESSION['userGroupName'] == 'Super Administrator'){
            $staffPortal = $displayLinkData->getLinkPortalMain('enggCrm_project', 'core_staffLink', 'Add Staff', $row);
        }

        /*$text = "
        <div class='mb30 mt40' id='materialLinkPortal'>{$this->getProjectMaterialPortal($row['project_id'])}</div>
        <div class='mb30 mt40' id='poLinkPortal'>{$this->getPurchaseOrderPortal($row['project_id'])}</div>
        <div class='mb30 mt40' id='materialTransferLinkPortal'>{$this->getMaterialTransferredPortal($row['project_id'])}</div>
        {$staffPortal}
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_employeeLink', 'Add Employee', $row)}
        {$this->getEmploymentTimeSheetPopupView($row['project_id'])}
        <div id='QuoteColumnLinkPortal'>{$quoteColumn}</div>
        {$displayLinkData->getLinkPortalMain('enggCrm_project', 'enggCrm_invoiceLink', 'Invoices', $row, '', $rightLinkInv)}
        {$costing}
        {$media->getRightPanelMediaDisplay('Attachment', 'enggCrm_project', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'enggCrm_project'
            ,'recordId' => $record_id
        ))}
        <div class='col-md-6 col-sm-6 col-xs-12'>
          {$staffPortal}
        </div>
        ";*/
        $wProjectJobCompletion = "";
        $wCostingSummary       = "";
        $wProjectQuote         = "";
        $wProjectMaterialsUsed = "";
        $wProjectWarranty = "";
         $wProjectMaintenanace = "";
        $wProjectPurchaseOrder = "";
        $wProjectMaterialTransferred = "";
        $wProjectWorkOrder     = "";
        $wProjectClaim         = "";
        $wProjectFinance       = "";
        $wProjectDeliveryOrder = "";
        $mobileViewWidgetLink  = "";
        $wProjectDeliveryOrderNote="";
        $desktopViewWidgetLink = "";
        if ($cpCfg['cp.hasAccessModule']) {
            // if ($widgetsArrAccess['project_projectCostingSummary']['hasAccess']) {
            //     $wCostingSummary        = getCPWidgetObj('project_projectCostingSummary')->view->getRowsHTML($row['project_id']);
            //     $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-1'>Costing Summary</a>";
            //     $desktopViewWidgetLink .= "
            //     <li class='first'>
            //         <a class='dropdown-item tabButtonLi costingSummaryLinked' data-toggle='tab' href='#tabs-1'>Costing Summary</a>
            //     </li>
            //     ";
            // }

            if ($widgetsArrAccess['enggCrm_projectQuote']['hasAccess']) {
                $wProjectQuote          = getCPWidgetObj('enggCrm_projectQuote')->view->getAddQuoteFormListView($row['opportunity_id'], $row['project_id'], $row['category']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-2'>Quotations</a>";
                $desktopViewWidgetLink .= "
                <li class='second'>
                    <a class='dropdown-item tabButtonLi quoteLinked' data-toggle='tab' href='#tabs-2'>Quotations</a>
                </li>
                ";
            }

            if ($widgetsArrAccess['enggCrm_projectPurchaseOrder']['hasAccess']) {
                $wProjectPurchaseOrder  = getCPWidgetObj('enggCrm_projectPurchaseOrder')->view->getPurchaseOrderPortal($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-3'>Materials Purchased</a>";
                $desktopViewWidgetLink .= "
                <li class='third'>
                    <a class='dropdown-item tabButtonLi materialsPurchased' data-toggle='tab' href='#tabs-3'>Materials Purchased</a>
                </li>
                ";
            }
            
            // if ($widgetsArrAccess['enggCrm_projectMaterialsUsed']['hasAccess']) {
            //     $wProjectMaterialsUsed  = getCPWidgetObj('enggCrm_projectMaterialsUsed')->view->getProjectMaterialPortal($row['project_id']);
            //     $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-4'>Materials used</a>";
            //     $desktopViewWidgetLink .= "
            //     <li class='fourth'>
            //         <a class='dropdown-item tabButtonLi materialsUsed' data-toggle='tab' href='#tabs-4'>Materials used</a>
            //     </li>
            //     ";
            // }

 

            // if ($widgetsArrAccess['enggCrm_projectMaterialTransferred']['hasAccess']) {
            //     $wProjectMaterialTransferred  = getCPWidgetObj('enggCrm_projectMaterialTransferred')->view->getMaterialTransferredPortal($row['project_id']);
            //     $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-5'>Materials Transferred</a>";
            //     $desktopViewWidgetLink .= "
            //     <li class='fifth'>
            //         <a class='dropdown-item tabButtonLi materialsTransferredFromOtherProjects' data-toggle='tab' href='#tabs-5'>Materials Transferred</a>
            //     </li>
            //     ";
            // }

            // if ($widgetsArrAccess['enggCrm_projectDeliveryOrder']['hasAccess']) {
            //     $wProjectDeliveryOrder  = getCPWidgetObj('enggCrm_projectDeliveryOrder')->view->getDeliveryOrderPortal($row['project_id']);
            //     $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-6'>Delivery Order</a>";
            //     $desktopViewWidgetLink .= "
            //     <li class='sixth'>
            //         <a class='dropdown-item tabButtonLi deliveryOrderPortal' data-toggle='tab' href='#tabs-6'>Delivery Order</a>
            //     </li>
            //     ";
            // }

            if ($widgetsArrAccess['enggCrm_projectWorkOrder']['hasAccess']) {
                $wProjectWorkOrder      = getCPWidgetObj('enggCrm_projectWorkOrder')->view->getWorkOrderListView($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-7'>Subcon Work Order</a>";
                $desktopViewWidgetLink .= "
                <li class='seventh'>
                    <a class='dropdown-item tabButtonLi subConWorkOrder' data-toggle='tab' href='#tabs-7'>Subcon Work Order</a>
                </li>
                ";
            }

            // if ($widgetsArrAccess['enggCrm_projectClaim']['hasAccess']) {
            //     $wProjectClaim          = getCPWidgetObj('enggCrm_projectClaim')->view->getAddClaimPortalListView($row['project_id']);
            //     $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-8'>Claim</a>";
            //     $desktopViewWidgetLink .= "
            //     <li class='eight'>
            //         <a class='dropdown-item tabButtonLi claimLinked' data-toggle='tab' href='#tabs-8'>Claim</a>
            //     </li>
            //     ";
            // }

            if ($widgetsArrAccess['enggCrm_projectFinance']['hasAccess']) {
                $wProjectFinance          = getCPWidgetObj('enggCrm_projectFinance')->view->getInvoiceReceiptPortalDetails($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-9'>Finance</a>";
                $desktopViewWidgetLink .= "
                <li class='ninth'>
                    <a class='dropdown-item tabButtonLi financeLinked' data-toggle='tab' href='#tabs-9'>Finance</a>
                </li>
                ";
            }

            
             if ($widgetsArrAccess['enggCrm_projectWarranty']['hasAccess']) {
                $wProjectWarranty  = getCPWidgetObj('enggCrm_projectWarranty')->view->getProjectWarrantyPortal($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-12'>Warranty</a>";
                $desktopViewWidgetLink .= "
                <li class='twelve'>
                    <a class='dropdown-item tabButtonLi materialsUsed' data-toggle='tab' href='#tabs-12'>Warranty</a>
                </li>
                ";
            }
             if ($widgetsArrAccess['enggCrm_projectMaintenanace']['hasAccess']) {

                $wProjectMaintenanace  = getCPWidgetObj('enggCrm_projectMaintenanace')->view->getProjectMaintenanacePortal($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-11'>Contract</a>";
                $desktopViewWidgetLink .= "
                <li class='eleven'>
                    <a class='dropdown-item tabButtonLi maintenanace' data-toggle='tab' href='#tabs-11'>Contract</a>
                </li>
                ";
         }

          if ($widgetsArrAccess['enggCrm_projectDeliveryOrderNote']['hasAccess']) {

                $wProjectDeliveryOrderNote  = getCPWidgetObj('enggCrm_projectDeliveryOrderNote')->view->getDeliveryOrderNotePortal($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-13'>Delivery Note</a>";
                $desktopViewWidgetLink .= "
                <li class='thirteen'>
                    <a class='dropdown-item tabButtonLi deliveryOrderNote' data-toggle='tab' href='#tabs-13'>Delivery Note</a>
                </li>
                ";
         }

          if ($widgetsArrAccess['enggCrm_projectJobCompletion']['hasAccess']) {

                $wProjectJobCompletion  = getCPWidgetObj('enggCrm_projectJobCompletion')->view->getJobCompletionPortal($row['project_id']);
                $mobileViewWidgetLink  .= "<a class='dropdown-item' data-toggle='tab' href='#tabs-14'>Job Completion</a>";
                $desktopViewWidgetLink .= "
                <li class='fourteen'>
                    <a class='dropdown-item tabButtonLi JobCompletion' data-toggle='tab' href='#tabs-14'>Job Completion</a>
                </li>
                ";
         }
        }


        $tabContentDetails = "
        <div id='myTabContent' class='tab-content col-md-12 col-sm-12 col-xs-12 noPadding'> 
            <div id='tabs-1' aria-labelledby='costingSummaryPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='costingSummaryPortal'>
                  {$wCostingSummary}
              </div>
            </div>

            <div id='tabs-2' aria-labelledby='quoteLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='quoteLinkPortal'>
                  {$wProjectQuote}
              </div>
            </div>

            <div id='tabs-3' aria-labelledby='poLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='poLinkPortal'>
                  {$wProjectPurchaseOrder}
                </div>
              </div>
            </div>

            <div id='tabs-4' aria-labelledby='materialLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='materialLinkPortal'>
                  {$wProjectMaterialsUsed}
                </div>
              </div>
            </div>

           

            <div id='tabs-5' aria-labelledby='materialTransferLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='materialTransferLinkPortal'>
                    {$wProjectMaterialTransferred}
                </div>
              </div>
            </div>

            <div id='tabs-6' aria-labelledby='deliveryOrderPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='deliveryOrderPortal'>
                  {$wProjectDeliveryOrder}
                </div>
              </div>
            </div>

            <div id='tabs-7' aria-labelledby='workOrderLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='workOrderLinkPortal'>
                    {$wProjectWorkOrder}
                </div>
              </div>
            </div>
            
            <div id='tabs-8' aria-labelledby='claimLinkedPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='mb30 mt10' id='claimLinkedPortal'>
                    {$wProjectClaim}
              </div>
            </div>

            <div id='tabs-9' aria-labelledby='financeLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='financeLinkPortal'>
                    <div class='floatbox mt10'>
                      <!--<div class='confirmedQuoteDisplayDiv'>
                        {$this->getConfirmedQuoteDetails($row['project_id'])}
                      </div>-->
                      <div class='invoiceReceiptPortalDisplayDiv'>
                        {$wProjectFinance}
                      </div>
                    </div>
                </div>
              </div>
            </div>
                 <div id='tabs-10' aria-labelledby='rightPanelsLinkedPortals-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatBox row'>
                <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                    <div class='col-md-6 col-sm-6 col-xs-12'>
                        {$media->getRightPanelMediaDisplay('Attachment', 'enggCrm_project', 'attachment', $row)}
                        {$comment->getView(array(
                             'roomName' => 'enggCrm_project'
                            ,'recordId' => $record_id
                        ))}
                    </div>
                    
                </div>
              </div>
            </div>
            
        </div>
        <div id='tabs-11' aria-labelledby='maintenanaceLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='maintenanaceLinkPortal'>
                  {$wProjectMaintenanace}
                </div>
              </div>
            </div>

         <div id='tabs-12' aria-labelledby='warrantyLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='warrantyLinkPortal'>
                  {$wProjectWarranty}
                </div>
              </div>
            </div>

            <div id='tabs-13' aria-labelledby='deliveryOrderNotePortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='deliveryOrderNotePortal'>
                  {$wProjectDeliveryOrderNote}
                </div>
              </div>
            </div>

             <div id='tabs-14' aria-labelledby='JobCompletionPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
              <div class='floatbox'>
                <div id='JobCompletionPortal'>
                  {$wProjectJobCompletion}
                </div>
              </div>
            </div>
            
        ";

        $mobileTabMenuToggle  = "";
        $desktopTabMenuToggle = "";
        if(isMobileBrowser()) {
            $mobileTabMenuToggle = "
            <div class='row col-md-12 col-sm-12 col-xs-12 desktopTabMenuMainDiv'>
                <div class='dropdown mobileTabMenuToggle'>
                  <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <div class='toggleIconBurger'></div>
                    <div class='toggleIconBurger'></div>
                    <div class='toggleIconBurger'></div>
                    <div class='toggleIconBurger'></div>
                  </button>
                  <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                    {$mobileViewWidgetLink}
                   
                  </div>
                </div>
                {$tabContentDetails}
            </div>
            ";
        } else {
            $desktopTabMenuToggle = "
            <div id='tabs' class='mb20 noPadding desktopTabMenuToggle col-md-12 col-sm-12 col-xs-12'>
                <ul>
                    {$desktopViewWidgetLink}
                    <li class='tenth'>
                        <a class='dropdown-item tabButtonLi addStaffAndEmployee' data-toggle='tab' href='#tabs-10'>Attachment</a>
                    </li>
                </ul>
                {$tabContentDetails}
            </div>
            ";
        }
        $orderUrl = "/admin/index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$rowComp['order_id']}";


        $text = " 
        <div class='linkPortalWrapper tabView'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <h2 class='rightPanelHeading'>More Details&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h2>
                    </div>
                    
                    <div class=''>
                    <a href= {$orderUrl} target='_blank' style='color:#000000;' class='button'>
                         <u>Go to Finance</u>
                    </a>
                </div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$mobileTabMenuToggle}
                    {$desktopTabMenuToggle}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];

        $totalQuotes  = 0;
        $quoteRef     = '';
        $invoiceRef   = '';
        $paymentTerms = '';

        $showSensitiveDetails = $fn->getSessionParam('showSensitiveDetails');

        if ($cpCfg['m.enggCrm.hasQuotingModule'] == 1) {
            if ($row['opportunity_id'] > 0) {
                $totalQuotes = $fn->getRecordCount('quote', "opportunity_id = {$row['opportunity_id']}");
            }
        }

        $expCode = array('isEditable' => 0);

        if ($row['opportunity_id'] > 0) {
            $oppLink   = "index.php?_topRm=project&module=enggCrm_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail";
            $linkToOpp = "<a href='{$oppLink}'><u>{$row['opportunity_code']}</u></a>";
            $quoteRef  = $formObj->getTBRow('Opportunity Ref#', 'quote_ref', $linkToOpp, $expCode);
        } else {
            $quoteRef = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        if ($cpCfg['m.enggCrm.project.showInvoiceRef'] == 1) {
            $invoiceRef = "
            {$formObj->getTBRow('Deposit Inv Ref#', 'deposit_inv_ref', $row['deposit_inv_ref'])}
            {$formObj->getTBRow('Invoice Ref#', 'invoice', $row['invoice'])}
            ";
        }

        if ($cpCfg['m.enggCrm.project.showPaymentTerms'] == 1 && $cpCfg['m.enggCrm.hasQuotingModule'] == 0) {
            $paymentTerms = $formObj->getTBRow('Payment Terms', 'payment_terms', $row['payment_terms']);
        }

        //--------------------------------------------------------------------------//
        $sqlComp = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name ASC
        ";
        $append = ($row['company_id'] > 0) ? "AND company_id = {$row['company_id']}" : '';
        $sqlCont = $fn->getDDSql('enggCrm_contact', array('condn' => "CONCAT_WS('', first_name, last_name) != '' {$append}"));
        $sqlPM = $fn->getDDSql('payroll_employee', array('condn' => "status = 'Current' AND project_manager = '1'"));
        //--------------------------------------------------------------------------//
        $expCode      = array('isEditable' => $cpCfg['m.enggCrm.project.codeEditable']);
        $expVl        = array('sqlType' => 'OneField');
        $expStartDate = array('maxDate' => date('Y-m-d'));

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlPerc   = $fn->getValueListSQL('percentCompleted');
        $sqlStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat     = $fn->getValueListSQL('projectCategory');

        $contact  = "<a href='index.php?_topRm=project&module=enggCrm_contact&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
        $company  = "<a href='index.php?_topRm=project&module=enggCrm_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

        $expComp  = array('detailValue' => $company);

        $expCont  = array('detailValue' => $contact);

        $stage = '';
        if ($cpCfg['m.enggCrm.project.showStage'] == 1){
            $sqlStage = $fn->getValueListSQL('projectStage');
            $stage = "
            {$formObj->getDDRowBySQL('Stage', 'stage', $sqlStage, $row['stage'], $expVl)}
            ";
        }

        $fieldset2 = "
        {$formObj->getDateRow('Start Date *', 'start_date', $row['start_date'], $expStartDate)}
        {$formObj->getDateRow('Estimated Finish Date *', 'estimated_finish_date', $row['estimated_finish_date'])}
        {$formObj->getDateRow('Actual Finish Date', 'actual_finish_date', $row['actual_finish_date'])}
        ";

        $fieldset3 = "
        <div id='projectValues'>
            {$this->getProjectValuesTable($row)}
        </div>
        ";

        $fieldset4 = "
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";

        $creation_date     = $dateUtil->formatDate($row['creation_date'], 'DD MMM YYYY HHH:MIN:SS');
        $modification_date = $dateUtil->formatDate($row['modification_date'], 'DD MMM YYYY HHH:MIN:SS');

                $expNoEdit  = array('isEditable' => 0);


        $sqlDC = "
        SELECT SUM(cs.amount) AS ducting_cost
        FROM actual_costing_summary cs
        WHERE cs.title = 'Ducting Cost'
          AND cs.project_id = {$row['project_id']}
        ";
        $resultDC = $db->sql_query($sqlDC);
        $rowDC = $db->sql_fetchrow($resultDC);

        //<td>{$formObj->getDateRow('Actual Finish Date', 'actual_finish_date', $row['actual_finish_date'])}</td>
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='toggle'></div>
                    <div class='float_left heading-title'>Project Details</div>
                    <div class='float_left mt5'>Code : {$row['project_code']} &nbsp;&nbsp;|</div>
                    <div class='float_left mt5'>Category : {$row['category']} &nbsp;&nbsp;|</div>
                    <div class='float_left mt5'>Company : {$row['company_name']} &nbsp;&nbsp;|</div>
                    <div class='float_left mt5'>Status : {$row['status']}</div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date}<br/>Modified : {$row['modified_by']} on {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>    
                    <div class='col-md-12 col-sm-12 col-xs-12'>
                        <div class='row'>
                            <div class='col-md-4 col-sm-6 col-xs-12'>{$formObj->getTBRow('Title', 'title', $row['title'])}</div>
                            <div class='col-md-2 col-sm-6 col-xs-12'>{$formObj->getDDRowBySQL('Category *', 'category', $sqlCat, $row['category'], $expNoEdit)}</div>
                            <div class='col-md-2 col-sm-6 col-xs-12'>{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}</div>
                            <div class='col-md-4 col-sm-6 col-xs-12'>{$formObj->getTBRow('Company', 'company_name', $row['company_name'], $expCode)}</div>
                        </div>
                        <div class='row'>
                            <div class='col-md-3 col-sm-6 col-xs-12'>{$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}</div>
                            <div class='col-md-2 col-sm-4 col-xs-12'>{$formObj->getDateRow('Start Date', 'start_date', $row['start_date'], $expStartDate)}</div>
                            <div class='col-md-2 col-sm-4 col-xs-12'>{$formObj->getDateRow('Estimated Finish Date', 'estimated_finish_date', $row['estimated_finish_date'])}</div>
                            <div class='col-md-5 col-sm-6 col-xs-12'>{$formObj->getTARow('Description', 'description', $row['description'])}</div>
                        </div>
                        <div class='row'>
                        <div class='col-md-4 col-sm-6 col-xs-12'>{$formObj->getTBRow('Client Po', 'client_po', $row['client_po'])}</div>
                        <div class='col-md-2 col-sm-6 col-xs-12'>{$formObj->getTBRow('Po Amount', 'po_amount',  (!empty($row['po_amount']) ? $row['po_amount'] : 0.00))}</div>
                        <div class='col-md-4 col-sm-6 col-xs-12'>{$formObj->getTBRow('Po Reference No', 'po_ref_no', $row['po_ref_no'])}</div>
                    </div>
                         <!-- <div class='row'>
                            <div class='col-md-3 col-sm-6 col-xs-12'>{$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'])}</div>
                            <div class='col-md-2 col-sm-6 col-xs-12'>
                                <div class='float_left'>
                                    {$formObj->getTBRow('Ducting Cost', 'ducting_cost', $rowDC['ducting_cost'], $expCode)}
                                </div>
                              <div class='float_left mt5'>
                                    <a class='addActualCharges' project_id={$row['project_id']} title='Ducting Cost'>Add</a>
                                </div>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getQuoteColumnDisplay($project_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sqlQuote  = $fn->getValuelistValueAsArray('quoteColumn','value');
        //$urlPrintquotecolumnLinkPdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printQuoteDisplayPdf&project_id={$project_id}&showHTML=0";

        $sqlQuoteColumn ="
        SELECT value
        FROM valuelist
        WHERE key_text = 'quoteColumn'
        AND value NOT IN(SELECT title
        FROM quote_columns
        WHERE project_id={$project_id})
        ";
        $result = $db->sql_query($sqlQuoteColumn);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        /*<div class='button mb5'>
            <a href='{$urlPrintquotecolumnLinkPdf}' target='_blank' class='printLink' project_id='{$project_id}'>Manpower display pdf</a>
        </div>*/

        $text = "
        <div class='linkPortalWrapper enggCrm_project__enggCrm_projectLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Manpower column display</div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='quotecolumnlist'>
                        <tr>
                            <td class='quotecolumnCheckBox'>{$formObj->getCheckBoxArrRowByArr(' ', 'title', $sqlQuote ,$dataArray)}</td>
                            <input id='project_id' type='hidden' name='project_id' value='{$project_id}' />
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;

    }   

     /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $yearMonthStart     = $fn->getReqParam('yearMonthStart');
        $yearMonthFinish    = $fn->getReqParam('yearMonthFinish');
        $company_id         = $fn->getReqParam('company_id');
        $project_month      = $fn->getReqParam('project_month');
        $status             = $fn->getReqParam('status');
        $category           = $fn->getReqParam('category');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');

        $appendSqlCompany = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlCompany = " AND a.site_id = {$cpSiteIdSession}";
        }
        $sqlCompany = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN project b ON (a.company_id = b.company_id)
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat    = $fn->getValueListSQL('projectCategory');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
           ,"Overrun"
        );


        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(start_date, '%b %Y') AS monthYear
        FROM project
        WHERE DATE_FORMAT( start_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
         ";

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
       <!-- <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCat, $category)}
            </select>
        </td>-->
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $tv['status'])}
            </select>
        </td>

        <td>
            <select name='yearMonthStart'>
                <option value=''>Start Month</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonthStart)}
            </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getprintQuoteDisplayPdf () {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

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

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();*/

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $year = $fn->getReqParam('year');
        $month = $fn->getReqParam('month');

        $SQL="
        SELECT e.employee_id
              ,SUM(employee_ot_hours) AS totalOTHours
              ,SUM(et.employee_ot_hours) AS totalOTHours
              ,SUM(et.employee_ph_hours) AS totalPHHours
              ,SUM(et.employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(et.employee_hours*hourly_rate) AS totalAmount
              ,SUM(et.employee_ph_hours*et.ph_hourly_rate) AS totalPHAmount
              ,e.first_name
              ,e.employee_work_type
              ,et.admin_charges
              ,et.transport_charges
              ,et.ot_hourly_rate 
              ,c.company_name
        FROM employee_timesheet et
        LEFT JOIN (employee e) ON (e.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$year}-{$month}'
        GROUP BY et.employee_id
        ";
        $result  = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result1);


        $current_date = date('d-m-Y');

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center">COMPANY NAME: '. $company['company_name'] .' </td>
            </tr>
        </table>
        ';

        $sqlQuoteColumn = "
        SELECT title
        FROM quote_columns
        WHERE project_id = {$project_id}
        ";
        $resultQuoteColumn = $db->sql_query($sqlQuoteColumn);
        $QuoteColumnCount  = $db->sql_numrows($resultQuoteColumn);
        $dataArrayQuoteColumn = $dbUtil->getResultsetAsArrayForForm($resultQuoteColumn);

        $countHeader      = 8 - $QuoteColumnCount;
        $ColumnWidth      = 100 / $countHeader;
        $ColumnWidth      = round($ColumnWidth);
        $ColumnWidthFirst = 6;
        $ColumnWidthShare = ($ColumnWidth - 6) / $countHeader;
        $ColumnWidth = round($ColumnWidth + $ColumnWidthShare);

        $worker_name_column = '';
        if (!in_array('Worker Name', $dataArrayQuoteColumn)) {
            $worker_name_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Worker Name</b></th>';
        }
        $admin_charges_column = '';
        if (!in_array('Admin Charges', $dataArrayQuoteColumn)) {
            $admin_charges_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Admin Charges</b></th>';
        }
        $workers_salary_column = '';
        if (!in_array('Workers Salary', $dataArrayQuoteColumn)) {
            $workers_salary_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Workers Salary</b></th>';
        }
        $transport_charges_column = '';
        if (!in_array('Transport Charges', $dataArrayQuoteColumn)) {
            $transport_charges_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Transport Charges</b></th>';
        }
        $overtime_hours_column = '';
        if (!in_array('Overtime Hours', $dataArrayQuoteColumn)) {
            $overtime_hours_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Over Time Charges</b></th>';
        }
        $overtime_rate_column = '';
        if (!in_array('PH Hours', $dataArrayQuoteColumn)) {
            $overtime_rate_column = '<th align="center" width="'.$ColumnWidth.'%"><b>Sunday /PH Charges</b></th>';
        }
      
        $tbl2 ='
        <table border="1" cellpadding="2" width="100%;border-collapse:collapse;table-layout:fixed;" style="font-size:12px;">
            <thead>
                <tr>
                    <th width="'.$ColumnWidthFirst.'%"><b>S.NO</b></th>
                    '.$worker_name_column.'
                    '.$admin_charges_column.'
                    '.$workers_salary_column.'
                    '.$transport_charges_column.'
                    '.$overtime_hours_column.'
                    '.$overtime_rate_column.'
                    <th width="'.$ColumnWidth.'%" align="center"><b>Total Amount</b></th>
                </tr>
            </thead>
        ';
        $serialNo = 1;
        $subtotalValue = 0;
        $Total = '';

        while ($row = $db->sql_fetchrow($result)) {

        $count = 1;
        $employee_name = '';
        if (!in_array('Worker Name', $dataArrayQuoteColumn)) {
            $employee_name = '<td align="center" width="'.$ColumnWidth.'%">'.$row['first_name'].'</td>';
            $count++;
        }
        $admin_charges = '';
        $admin_charges_amount = 0;
        if (!in_array('Admin Charges', $dataArrayQuoteColumn)) {
            $admin_charges = '<td align="right" width="'.$ColumnWidth.'%">'.$row['admin_charges'].'</td>';
            $admin_charges_amount = $row['admin_charges'];
            $count++;
        }
        $workers_salary = '';
        $salary_amount = 0;
        if (!in_array('Workers Salary', $dataArrayQuoteColumn)) {
            $workers_salary = '<td align="right" width="'.$ColumnWidth.'%">'.number_format($row['totalAmount'], 2).'</td>';
            $salary_amount = $row['totalAmount'];
            $count++;
        }
        $transport_charges = '';
        $transport_charges_amount = 0;
        if (!in_array('Transport Charges', $dataArrayQuoteColumn)) {
            $transport_charges = '<td align="right" width="'.$ColumnWidth.'%">'.$row['transport_charges'].'</td>';
            $transport_charges_amount = $row['transport_charges'];
            $count++;
        }
        $overtime_hours = '';
        $overtime_hours_amount = 0;
        if (!in_array('Overtime Hours', $dataArrayQuoteColumn)) {
            $overtime_hours = '<td align="right" width="'.$ColumnWidth.'%">'.$row['totalOTAmount'].'</td>';
            $overtime_hours_amount = $row['totalOTAmount'];
            $count++;
        }
        $ph_hours = '';
        $ph_rate_amount = 0;
        if (!in_array('PH Hours', $dataArrayQuoteColumn)) {
            $ph_hours = '<td align="right" width="'.$ColumnWidth.'%">'.$row['totalPHAmount'].'</td>';
            $ph_rate_amount = $row['totalPHAmount'];
            $count++;
        }
        //$subtotal_amount = $admin_charges_amount + $salary_amount +  $transport_charges_amount + ($overtime_hours_amount * $overtime_rate_amount);
        $subtotal_amount = $admin_charges_amount + $salary_amount +  $transport_charges_amount + $overtime_hours_amount + $ph_rate_amount;

        $SQLArchiveCheck = "
        SELECT SUM( et.employee_hours ) AS totalHrs
             , e.status
             , pe.active_in_project
        FROM `employee_timesheet` et
        LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        WHERE et.employee_id = {$row['employee_id']}
        AND pe.employee_id   = {$row['employee_id']}
        AND et.project_id    = {$project_id}
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
        $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
        if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
        }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
        }else{
            $tbl2 = $tbl2.'
                    <tr>
                      <td width="'.$ColumnWidthFirst.'%" align="center">'.$serialNo.'</td>
                      '.$employee_name.'
                      '.$admin_charges.'
                      '.$workers_salary.'
                      '.$transport_charges.'
                      '.$overtime_hours.'
                      '.$ph_hours.'
                      <td width="'.$ColumnWidth.'%" align="right">'.number_format($subtotal_amount, 2).'</td>
                    </tr>';
            $subtotalValue += $subtotal_amount;
            $gsttaxvalue = $cpCfg['cp.gstPercentage'];
            $gstvalue = $subtotalValue * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + $subtotalValue;
            $serialNo++;
        }

      }

        $tbl2 = $tbl2.'<tr>
                          <td align="right" colspan="'.$count.'" style="font-size:12px; font-weight:bold;">SUB TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                      </tr>
                      <tr>
                          <td colspan="'.$count.'" align="right" style="font-size:12px; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                       </tr>
                       <tr>
                          <td colspan="'.$count.'" align="right" style="font-size:12px; font-weight:bold;">NET TOTAL</td>
                          <td align="right" style="font-size:12px; font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                       </tr>
                    </table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $row['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getEmploymentTimeSheetNewAllView($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $quoteRec = $fn->getRecordRowByID('quote', 'project_id', $project_id);

        $SQL = "
        SELECT DATE_FORMAT(et.date, '%Y-%m') AS dateMonth
              ,DATE_FORMAT(et.date, '%M') AS Month
              ,DATE_FORMAT(et.date, '%m') AS month_req
              ,DATE_FORMAT(et.date, '%Y') AS year_req
              ,DATE_FORMAT(et.date, '%Y-%m') AS year_Months
              ,SUM(et.employee_hours) AS totalHours
              ,SUM(et.employee_ot_hours) AS totalOTHours
              ,SUM(et.employee_ph_hours) AS totalPHHours
              ,et.project_id
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        GROUP BY DATE_FORMAT(et.date, '%Y-%m')
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
                $editLink = "<a project_id={$project_id} year={$row['year_req']} month={$row['month_req']} class='editTimesheetForProjectEmployee' >Edit</a>";

                $SQLAmount = "
                SELECT CAST(SUM(et.employee_hours * et.hourly_rate) AS Decimal (18,2)) AS totalAmount
                      ,CAST(SUM(et.employee_ot_hours * et.ot_hourly_rate) AS Decimal (18,2)) AS totalOTAmount
                      ,CAST(SUM(et.employee_ph_hours * et.ph_hourly_rate) AS Decimal (18,2)) AS totalPHAmount
                FROM employee_timesheet et
                WHERE et.project_id = '{$row['project_id']}'
                  AND DATE_FORMAT(et.date, '%Y-%m') = '{$row['dateMonth']}'
                GROUP BY et.employee_id
                ";
                $resultAmount = $db->sql_query($SQLAmount);
                $totalAmount   = 0;
                $totalOTAmount = 0;
                $totalPHAmount = 0;
                while ($rowAmount = $db->sql_fetchrow($resultAmount)) {
                    $totalAmount   += $rowAmount['totalAmount'];
                    $totalOTAmount += $rowAmount['totalOTAmount'];
                    $totalPHAmount += $rowAmount['totalPHAmount'];
                }

                $SQL2 = "
                SELECT e.employee_name
                      ,e.employee_id
                      ,et.admin_charges
                      ,et.transport_charges
                FROM employee_timesheet et
                LEFT JOIN employee e ON(e.employee_id = et.employee_id)
                WHERE et.project_id = {$project_id}
                AND DATE_FORMAT(date, '%Y-%m') = '{$row['year_Months']}'
                GROUP BY et.employee_id
                ";

                $result2 = $db->sql_query($SQL2);
                $rows2 = '';
                $admin_charges     = 0;
                $transport_charges = 0;
                while ($row2 = $db->sql_fetchrow($result2)) {
                  $SQLArchiveCheck = "
                  SELECT SUM( et.employee_hours ) AS totalHrs
                       , e.status
                       , pe.active_in_project
                  FROM `employee_timesheet` et
                  LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
                  LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
                  WHERE et.employee_id = {$row2['employee_id']}
                  AND pe.employee_id   = {$row2['employee_id']}
                  AND et.project_id    = {$project_id}
                  AND DATE_FORMAT(et.date, '%Y-%m') = '{$row['year_Months']}'
                  ";
                  $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
                  $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
                  if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
                  }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
                  }else{
                    $rows2 .= "{$this->getTimeSheetByEmployee($project_id, $row2['employee_id'], $row['year_Months'])}";
                    $urlprintTimeSheetPdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheetPdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $urlprintTimeSheet1Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet1Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $urlprintTimeSheet2Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet2Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $admin_charges     += $row2['admin_charges'];
                    $transport_charges += $row2['transport_charges'];
                  }
                }
                
                $urlPrintquotecolumnLinkPdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printQuoteDisplayPdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                if ($quoteRec['timesheet_type'] == 'Monthly') {
                    $timesheetPrint = "
                    <div class='float_left printTimeSheetPdf'>
                        <a href='{$urlprintTimeSheetPdf}' target='_blank'>Print Timesheet</a>
                    </div>
                    ";
                } else {
                    $urlprintTimeSheet1Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet1Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";
                    $urlprintTimeSheet2Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet2Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $timesheetPrint = "
                    <div class='float_left printTimeSheetPdf'>
                        <a href='{$urlprintTimeSheet1Pdf}' target='_blank'>Print 1st Half Timesheet</a>
                    </div>
                    <div class='float_left printTimeSheetPdf'>
                        <a href='{$urlprintTimeSheet2Pdf}' target='_blank'>Print 2nd Half Timesheet</a>
                    </div>
                    ";
                }

                $addEmployeeLineItemView = $timesheetPrint . "
                <div class='float_left'><a class='employeeListShow'><u>View Staff</u></a>
                </div>
                <div class='float_left printLink'>
                    <a href='{$urlPrintquotecolumnLinkPdf}' target='_blank'>Manpower display pdf</a>
                </div>
                ";

                $overallTotalAmount = $totalAmount + $totalOTAmount + $totalPHAmount + $admin_charges + $transport_charges;
                $overallTotalAmount = number_format($overallTotalAmount, 2);

                $rows .= "
                <tbody class='employeeMonthRow'>
                    <tr class='addEmployeeRow'>
                        <td>{$row['Month']}</td>
                        <td>{$row['totalHours']}</td>
                        <td>{$row['totalOTHours']}</td>
                        <td>{$row['totalPHHours']}</td>
                        <td>{$overallTotalAmount}</td>
                        <td>
                            <div class='float_left'><u>{$editLink}</u></div>
                            {$addEmployeeLineItemView}
                        </td>
                    </tr>

                    <tr class='employeeListHide'>
                        <td></td>
                        <td colspan='5'>
                            <table class='thinlist'>
                                <tr class='employeeTrTh'>
                                    <th>Name</th>
                                    <th>Total Hours</th>
                                    <th>Total OT Hours</th>
                                    <th>Total PH Hours</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                                {$rows2}
                            </table>
                        </td>
                    </tr>

                </tbody>
                ";
                $count++;
        }

            $text = '';

            $urlOverAllPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printOverAllEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

            $overAllTimeSheetPdf = "
            <div class='float_right printTimeSheetPdf'>
                <a href='{$urlOverAllPrintEmployeePdf}' target='_blank'>Over All Print Timesheet</a>
             </div>
             ";

            if ($numRows > 0)  {
            $text = "
            <div id='employeePortal' class='linkPortalWrapper'>
                <table class='list'>
                    <thead>
                    <tr>
                        <th colspan='8' align='left'>Employee Time Sheet</th>
                    </tr>
                    <tr>
                        <th>Month</th>
                        <th>Total Hours</th>
                        <th>Total OT Hours</th>
                        <th>Total PH Hours</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    {$rows}
                </table>
            </div>
            ";

            return $text;
        }
    }

    /**
     *
     */
    function getPrintTimeSheetPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
        $pdf->SetSubject('Print Timesheet');
        $pdf->SetTitle('Print Timesheet');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year = $fn->getReqParam('year');

        switch ($month) {
            case 1: $month_name = 'January';
            break;
            case 2: $month_name = 'February';
            break;
            case 3: $month_name = 'March';
            break;
            case 4: $month_name = 'April';
            break;
            case 5: $month_name = 'May';
            break;
            case 6: $month_name = 'June';
            break;
            case 7: $month_name = 'July';
            break;
            case 8: $month_name = 'August';
            break;
            case 9: $month_name = 'September';
            break;
            case 10: $month_name = 'October';
            break;
            case 11: $month_name = 'November';
            break;
            case 12: $month_name = 'December';
            break;
        }

        $SQL = "
        SELECT et.*
             ,emp.first_name
             ,p.project_code
             ,p.title AS project_title
             ,c.company_name
             ,et.hourly_rate
             ,et.admin_charges
             ,et.transport_charges
             ,DATE_FORMAT(MIN(et.date), '%d') AS min_date
             ,DATE_FORMAT(MAX(et.date), '%d') AS max_date
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
        AND et.employee_hours != ''
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultProject = $db->sql_query($SQL);
        $rowProject    = $db->sql_fetchrow($resultProject);

        // Used to find list of holidays
        $sqlValuelist = "
        SELECT value FROM valuelist WHERE code = '{$year}' AND key_text = 'singaporeHolidays'";
        $resultValuelist = $db->sql_query($sqlValuelist);
        $rowValuelist    = $db->sql_fetchrow($resultValuelist);
        $arrValuelist    = explode(',', $rowValuelist['value']);
        $current_date    = date('d-m-Y');

        $pdf->SetFont('times');

        $dayContHeader = "";
        $dayHeaderCount = 1;
        $overall_total_of_all_employees = 0;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($j=0; $j < $count2; $j++) {
            if ($dayHeaderCount <= 9) {
                $dayHeaderCountCheck = '0'.$dayHeaderCount;
            } else {
                $dayHeaderCountCheck = $dayHeaderCount;
            }

            $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
            $dayNameDate   = $fn->getCPDate($dateTimesheet, 'D');
            $dateTimesheetCheck =  $year.'-'.$month.'-'.$dayHeaderCountCheck;

            if (in_array($dateTimesheetCheck, $arrValuelist)) {
                $dayContHeader .= '<th width="1.95%" style="background-color:#92D050; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else if ($dayNameDate == 'Sun') {
                    $dayContHeader .= '<th width="1.95%" style="background-color:yellow; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else {
                $dayContHeader .= '<th width="1.95%" style="line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            }

            $dayHeaderCount++;
        }

        $tbl1 = '
        <table border="0" width="100%">
            <!--<tr>
                <td align="center">SITE LOCATION: '. $rowProject['project_title'] .' </td>
            </tr>-->
            <tr>
                <td width="100%" align="center"><b>'. $rowProject['min_date'] . '-' . $rowProject['max_date'] . ' ' . strtoupper($month_name) . ' ' . $year.'</b></td>
                <!--<td width="40%" align="center"></td>
                <td width="30%" align="right">Client: <b>'. $rowProject['company_name'] .'</b></td>-->
            </tr>
        </table>
        ';

        $tbl2 ='
        <table border="1" cellpadding="3" width="100%" style="font-size:7px;">
            <thead>
                <tr align="center">
                    <th width="2.4%"><b>S.NO</b></th>
                    <th width="8.5%" style="line-height:300%"><b>NAME</b></th>
                    <th width="2%">DATE</th>'.
                    $dayContHeader . '
                    <th width="4%" style="line-height:300%"><b>MAN HRS</b></th>
                    <th width="3.45%" style="line-height:300%"><b>RATES</b></th>
                    <th width="4%" style="line-height:300%"><b>AMOUNT</b></th>
                    <th width="6.5%" style="line-height:300%"><b>CLAIM AMOUNT</b></th>
                    <th width="5.4%" style="line-height:300%"><b>ALLOWANCE</b></th>
                    <th width="7%" style="line-height:300%"><b>TOTAL AMOUNT</b></th>
                </tr>
            </thead>
        ';

        $serialNo = 1;
        $totalValue = 0;
        $total_claim_for_employee = 0;
        $total_hours_of_all_employees = 0; // Total hours of all employees for summary
        $total_amount_of_all_employees = 0; // Total claim (amount) of all employees for summary

        $sqlTimesheet = "
        SELECT pe.employee_id
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
              ,pe.category_type
        FROM project_employee pe
        LEFT JOIN (employee e) ON (pe.employee_id = e.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.first_name ASC
        ";
        $resultTimesheet = $db->sql_query($sqlTimesheet);
        while ($row = $db->sql_fetchrow($resultTimesheet)) {
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $employee_rate     = 0;
            $employee_ot_rate  = 0;
            $employee_ph_rate  = 0;
            $admin_charges     = 0;
            $transport_charges = 0;

            $dayContRow   = "";
            $dayContOTRow = "";
            $dayContPHRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            for ($j= 0; $j < $count2; $j++) {
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,hourly_rate
                      ,ot_hourly_rate
                      ,ph_hourly_rate
                      ,admin_charges
                      ,transport_charges
                FROM employee_timesheet
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $timesheet_days    = '';
                $timesheet_OT_days = '';
                $timesheet_PH_days = ''; 
                if ($rowTimesheetDays['employee_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_days    = $rowTimesheetDays['employee_hours'];
                    } else {
                        $timesheet_days    = $str_arr[0]; // Before decimal value
                    }
                }

                if ($rowTimesheetDays['employee_ot_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ot_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_OT_days = $rowTimesheetDays['employee_ot_hours'];
                    } else {
                        $timesheet_OT_days = $str_arr[0];
                    }
                }

                if ($rowTimesheetDays['employee_ph_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ph_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_PH_days = $rowTimesheetDays['employee_ph_hours'];
                    } else {
                        $timesheet_PH_days = $str_arr[0];
                    }
                }

                $dayContRow   .= '<td width="1.95%" align="center" style="line-height:200%">'. $timesheet_days . '</td>';
                $dayContOTRow .= '<td width="1.95%" align="center" style="line-height:200%">'. $timesheet_OT_days . '</td>';
                $dayContPHRow .= '<td width="1.95%" align="center" style="line-height:200%">'. $timesheet_PH_days . '</td>';
                
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];

                if($rowTimesheetDays['hourly_rate'] != ''){
                  $employee_rate    = $rowTimesheetDays['hourly_rate'];
                }

                if($rowTimesheetDays['ot_hourly_rate'] != ''){
                  $employee_ot_rate = $rowTimesheetDays['ot_hourly_rate'];
                }

                if($rowTimesheetDays['ph_hourly_rate'] != ''){
                  $employee_ph_rate = $rowTimesheetDays['ph_hourly_rate'];
                }

                if($rowTimesheetDays['admin_charges'] != ''){
                  $admin_charges    = $rowTimesheetDays['admin_charges'];
                }

                if($rowTimesheetDays['transport_charges'] != ''){
                  $transport_charges = $rowTimesheetDays['transport_charges'];
                }

            }

            $total_claim_for_employee = $employee_rate * $totalHoursSheet;
            $total_claim_for_employee_formatted = number_format($total_claim_for_employee,2);

            $total_claim_for_employee_ot = $employee_ot_rate * $totalOTHoursSheet;
            $total_claim_for_employee_ot_formatted = '-';
            if ($total_claim_for_employee_ot > 0) {
                $total_claim_for_employee_ot_formatted = number_format($total_claim_for_employee_ot,2);
            }

            $total_claim_for_employee_ph = $employee_ph_rate * $totalPHHoursSheet;
            $total_claim_for_employee_ph_formatted = '-';
            if ($total_claim_for_employee_ph > 0) {
                $total_claim_for_employee_ph_formatted = number_format($total_claim_for_employee_ph,2);
            }

            $overall_claim_amount = round($total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph, 2);
            $total_allowance = $admin_charges + $transport_charges;
            $total_amount_per_employee = $overall_claim_amount + $total_allowance;
            $overall_total_of_all_employees += $total_amount_per_employee;

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
            AND pe.employee_id   = {$row['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
              $totalHoursSheetFormatted = '';
              if ($totalHoursSheet > 0) {
                  $totalHoursSheetFormatted = number_format($totalHoursSheet, 1);
              }
              $total_allowance_formatted = '';
              if ($total_allowance > 0) {
                  $total_allowance_formatted = number_format($total_allowance, 2);
              }
              $totalOTHoursSheetFormatted = '-';
              if ($totalOTHoursSheet > 0) {
                  $totalOTHoursSheetFormatted = number_format($totalOTHoursSheet, 2);
              }
              $totalPHHoursSheetFormatted = '-';
              if ($totalPHHoursSheet > 0) {
                  $totalPHHoursSheetFormatted = number_format($totalPHHoursSheet, 2);
              }

              $tbl2 = $tbl2.'
                      <tr align="center">
                          <td rowspan="3" width="2.4%"  style="line-height:800%">'.$serialNo.'</td>
                          <td rowspan="3" width="8.5%">'.$row['first_name'].'<br/><br/>' . $ic_no .'<br/><br/>' . strtoupper($row['category_type']) .'</td>
                          <td width="2%" style="line-height:200%">NH</td>'.
                          $dayContRow.'
                          <td width="4%" align="right" style="line-height:200%">'.$totalHoursSheetFormatted.'</td>
                          <td width="3.45%" align="right" style="line-height:200%">'.$employee_rate.'</td>
                          <td width="4%" align="right" style="line-height:200%">'.$total_claim_for_employee_formatted.'</td>
                          <td rowspan="3" width="6.5%" align="right" style="line-height:800%;">' . number_format($overall_claim_amount, 2).'</td>
                          <td rowspan="3" width="5.4%" align="right" style="line-height:800%;">'. $total_allowance_formatted.'</td>
                          <td rowspan="3" width="7%" align="right" style="line-height:800%;">'. number_format($total_amount_per_employee, 2).'</td>
                      </tr>
                      <tr>
                          <td width="2%" height="20px" align="center" style="line-height:200%">OT</td>'.
                          $dayContOTRow.'
                          <td width="4%" align="right" style="line-height:200%">'.$totalOTHoursSheetFormatted.'</td>
                          <td width="3.45%" align="right" style="line-height:200%">'.$employee_ot_rate.'</td>
                          <td width="4%" align="right" style="line-height:200%">'.$total_claim_for_employee_ot_formatted.'</td>
                      </tr>
                      <tr>
                          <td width="2%" height="20px" align="center" style="line-height:200%">PH</td>'.
                          $dayContPHRow.'
                          <td width="4%" align="right" style="line-height:200%">'.$totalPHHoursSheetFormatted.'</td>
                          <td width="3.45%" align="right" style="line-height:200%">'.$employee_ph_rate.'</td>
                          <td width="4%" align="right" style="line-height:200%">'.$total_claim_for_employee_ph_formatted.'</td>
                      </tr>
                      ';
            $total_hours_of_all_employees  += $totalHoursSheet + $totalOTHoursSheet + $totalPHHoursSheet; // Total hours of all employees for summary
            $total_amount_of_all_employees += $total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph + $admin_charges + $transport_charges; // Total claim (amount) of all employees for summary
            $serialNo++;
          }
        }

        $colspan = $dayCount + 3;

        /* Calculate GST for the total amount */
        //if ($cpCfg['hasGST'])
        $gst_amount = 0;
        $gst_amount = (($total_amount_of_all_employees*7)/100);
        $total_amount_for_invoice = $total_amount_of_all_employees + $gst_amount;

        $total_amount_of_all_employees_formatted = number_format($total_amount_of_all_employees,2);
        $gst_amount_formatted = number_format($gst_amount, 2);
        $total_amount_for_invoice_formatted = number_format($total_amount_for_invoice, 2);

        $colspanCount = $count2 + 8;
        $tbl2 = $tbl2 . '
            <tr>
                <td colspan="'.$colspanCount.'"></td>
                <td align="right"><b>' . number_format($overall_total_of_all_employees, 2).'</b></td>
            </tr>
        </table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="53%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
                <td width="10%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
            </tr>
            <tr>
                <td width="53%"></td>
                <td width="20%" align="center">PREPARED BY</td>
                <td width="10%"></td>
                <td width="20%" align="center">CHECKED BY</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowProject['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintTimeSheet1Pdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
        $pdf->SetSubject('Print Timesheet');
        $pdf->SetTitle('Print Timesheet');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year = $fn->getReqParam('year');

        switch ($month) {
            case 1: $month_name = 'January';
            break;
            case 2: $month_name = 'February';
            break;
            case 3: $month_name = 'March';
            break;
            case 4: $month_name = 'April';
            break;
            case 5: $month_name = 'May';
            break;
            case 6: $month_name = 'June';
            break;
            case 7: $month_name = 'July';
            break;
            case 8: $month_name = 'August';
            break;
            case 9: $month_name = 'September';
            break;
            case 10: $month_name = 'October';
            break;
            case 11: $month_name = 'November';
            break;
            case 12: $month_name = 'December';
            break;
        }

        $SQL = "
        SELECT et.*
             ,emp.first_name
             ,p.project_code
             ,p.title AS project_title
             ,c.company_name
             ,et.hourly_rate
             ,et.admin_charges
             ,et.transport_charges
             ,DATE_FORMAT(MIN(et.date), '%d') AS min_date
             ,DATE_FORMAT(MAX(et.date), '%d') AS max_date
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
          AND et.employee_hours != ''
          AND et.date BETWEEN '{$year}-{$month}-01' AND '{$year}-{$month}-15'
        ";
        $resultProject = $db->sql_query($SQL);
        $rowProject    = $db->sql_fetchrow($resultProject);

        // Used to find list of holidays
        $sqlValuelist = "
        SELECT value FROM valuelist WHERE code = '{$year}' AND key_text = 'singaporeHolidays'";
        $resultValuelist = $db->sql_query($sqlValuelist);
        $rowValuelist    = $db->sql_fetchrow($resultValuelist);
        $arrValuelist    = explode(',', $rowValuelist['value']);
        $current_date    = date('d-m-Y');

        $pdf->SetFont('times');

        $dayContHeader = "";
        $dayHeaderCount = 1;
        $overall_total_of_all_employees = 0;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($j=0; $j<15; $j++) {
            if ($dayHeaderCount <= 9) {
                $dayHeaderCountCheck = '0'.$dayHeaderCount;
            } else {
                $dayHeaderCountCheck = $dayHeaderCount;
            }

            $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
            $dayNameDate   = $fn->getCPDate($dateTimesheet, 'D');
            $dateTimesheetCheck =  $year.'-'.$month.'-'.$dayHeaderCountCheck;

            if (in_array($dateTimesheetCheck, $arrValuelist)) {
                $dayContHeader .= '<th width="2.50%" style="background-color:#92D050; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else if ($dayNameDate == 'Sun') {
                    $dayContHeader .= '<th width="2.50%" style="background-color:yellow; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else {
                $dayContHeader .= '<th width="2.50%" style="line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            }

            $dayHeaderCount++;
        }

        // Setting to show values for empty min and max dates
        $min_date = $rowProject['min_date'];
        $max_date = $rowProject['max_date'];
        if ($rowProject['min_date'] == '' && $rowProject['max_date'] == '') {
            $min_date = 1;
            $max_date = 15;
        }

        $tbl1 = '
        <table border="0" width="100%">
            <!--<tr>
                <td align="center">SITE LOCATION: '. $rowProject['project_title'] .' </td>
            </tr>-->
            <tr>
                <td width="100%" align="center"><b>'. $min_date . '-' . $max_date . ' ' . strtoupper($month_name) . ' ' . $year.'</b></td>
                <!--<td width="40%" align="center"></td>
                <td width="30%" align="right">Client: <b>'. $rowProject['company_name'] .'</b></td>-->
            </tr>
        </table>
        ';

        $tbl2 ='
        <table border="1" cellpadding="3" width="100%" style="font-size:7px;">
            <thead>
                <tr align="center">
                    <th width="2.4%"><b>S.NO</b></th>
                    <th width="20.5%" style="line-height:300%"><b>NAME</b></th>
                    <th width="4%" style="line-height:300%">DATE</th>'.
                    $dayContHeader . '
                    <th width="5%" style="line-height:300%"><b>MAN HRS</b></th>
                    <th width="4.45%" style="line-height:300%"><b>RATES</b></th>
                    <th width="5%" style="line-height:300%"><b>AMOUNT</b></th>
                    <th width="7.5%" style="line-height:300%"><b>CLAIM AMOUNT</b></th>
                    <th width="7.4%" style="line-height:300%"><b>ALLOWANCE</b></th>
                    <th width="10%" style="line-height:300%"><b>TOTAL AMOUNT</b></th>
                </tr>
            </thead>
        ';

        $serialNo = 1;
        $totalValue = 0;
        $total_claim_for_employee = 0;
        $total_hours_of_all_employees = 0; // Total hours of all employees for summary
        $total_amount_of_all_employees = 0; // Total claim (amount) of all employees for summary

        $sqlTimesheet = "
        SELECT pe.employee_id
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
              ,pe.category_type
        FROM project_employee pe
        LEFT JOIN (employee e) ON (pe.employee_id = e.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.first_name ASC
        ";
        $resultTimesheet = $db->sql_query($sqlTimesheet);
        while ($row = $db->sql_fetchrow($resultTimesheet)) {
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $employee_rate     = 0;
            $employee_ot_rate  = 0;
            $employee_ph_rate  = 0;
            $admin_charges     = 0;
            $transport_charges = 0;

            $dayContRow   = "";
            $dayContOTRow = "";
            $dayContPHRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            for ($j= 0; $j<15; $j++) {
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,hourly_rate
                      ,ot_hourly_rate
                      ,ph_hourly_rate
                      ,admin_charges
                      ,transport_charges
                FROM employee_timesheet
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $timesheet_days    = '';
                $timesheet_OT_days = '';
                $timesheet_PH_days = ''; 
                if ($rowTimesheetDays['employee_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_days    = $rowTimesheetDays['employee_hours'];
                    } else {
                        $timesheet_days    = $str_arr[0]; // Before decimal value
                    }
                }

                if ($rowTimesheetDays['employee_ot_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ot_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_OT_days = $rowTimesheetDays['employee_ot_hours'];
                    } else {
                        $timesheet_OT_days = $str_arr[0];
                    }
                }

                if ($rowTimesheetDays['employee_ph_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ph_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_PH_days = $rowTimesheetDays['employee_ph_hours'];
                    } else {
                        $timesheet_PH_days = $str_arr[0];
                    }
                }

                $dayContRow   .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_days . '</td>';
                $dayContOTRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_OT_days . '</td>';
                $dayContPHRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_PH_days . '</td>';
                
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];

                if($rowTimesheetDays['hourly_rate'] != ''){
                  $employee_rate    = $rowTimesheetDays['hourly_rate'];
                }

                if($rowTimesheetDays['ot_hourly_rate'] != ''){
                  $employee_ot_rate = $rowTimesheetDays['ot_hourly_rate'];
                }

                if($rowTimesheetDays['ph_hourly_rate'] != ''){
                  $employee_ph_rate = $rowTimesheetDays['ph_hourly_rate'];
                }

                if($rowTimesheetDays['admin_charges'] != ''){
                  $admin_charges    = $rowTimesheetDays['admin_charges'];
                }

                if($rowTimesheetDays['transport_charges'] != ''){
                  $transport_charges = $rowTimesheetDays['transport_charges'];
                }

            }

            $total_claim_for_employee = $employee_rate * $totalHoursSheet;
            $total_claim_for_employee_formatted = number_format($total_claim_for_employee,2);

            $total_claim_for_employee_ot = $employee_ot_rate * $totalOTHoursSheet;
            $total_claim_for_employee_ot_formatted = '-';
            if ($total_claim_for_employee_ot > 0) {
                $total_claim_for_employee_ot_formatted = number_format($total_claim_for_employee_ot,2);
            }

            $total_claim_for_employee_ph = $employee_ph_rate * $totalPHHoursSheet;
            $total_claim_for_employee_ph_formatted = '-';
            if ($total_claim_for_employee_ph > 0) {
                $total_claim_for_employee_ph_formatted = number_format($total_claim_for_employee_ph,2);
            }

            $overall_claim_amount           = round(($total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph), 2);
            $total_allowance                = $admin_charges + $transport_charges;
            $total_amount_per_employee      = $overall_claim_amount + $total_allowance;
            $overall_total_of_all_employees += $total_amount_per_employee;

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
            AND pe.employee_id   = {$row['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
              $totalHoursSheetFormatted = '';
              if ($totalHoursSheet > 0) {
                  $totalHoursSheetFormatted = number_format($totalHoursSheet, 1);
              }
              $total_allowance_formatted = '';
              if ($total_allowance > 0) {
                  $total_allowance_formatted = number_format($total_allowance, 2);
              }
              $totalOTHoursSheetFormatted = '-';
              if ($totalOTHoursSheet > 0) {
                  $totalOTHoursSheetFormatted = number_format($totalOTHoursSheet, 2);
              }
              $totalPHHoursSheetFormatted = '-';
              if ($totalPHHoursSheet > 0) {
                  $totalPHHoursSheetFormatted = number_format($totalPHHoursSheet, 2);
              }

              $tbl2 = $tbl2.'
                      <tr align="center">
                          <td rowspan="3" width="2.4%"  style="line-height:800%">'.$serialNo.'</td>
                          <td rowspan="3" width="20.5%">'.$row['first_name'].'<br/><br/>' . $ic_no .'<br/><br/>' . strtoupper($row['category_type']) .'</td>
                          <td width="4%" style="line-height:200%">NH</td>'.
                          $dayContRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_formatted.'</td>
                          <td rowspan="3" width="7.5%" align="right" style="line-height:800%;">' . number_format($overall_claim_amount, 2).'</td>
                          <td rowspan="3" width="7.4%" align="right" style="line-height:800%;">'. $total_allowance_formatted.'</td>
                          <td rowspan="3" width="10%" align="right" style="line-height:800%;">'. number_format($total_amount_per_employee, 2).'</td>
                      </tr>
                      <tr>
                          <td width="4%" height="20px" align="center" style="line-height:200%">OT</td>'.
                          $dayContOTRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalOTHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_ot_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_ot_formatted.'</td>
                      </tr>
                      <tr>
                          <td width="4%" height="20px" align="center" style="line-height:200%">PH</td>'.
                          $dayContPHRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalPHHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_ph_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_ph_formatted.'</td>
                      </tr>
                      ';
            $total_hours_of_all_employees  += $totalHoursSheet + $totalOTHoursSheet + $totalPHHoursSheet; // Total hours of all employees for summary
            $total_amount_of_all_employees += $total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph + $admin_charges + $transport_charges; // Total claim (amount) of all employees for summary
            $serialNo++;
          }
        }

        $colspan = $dayCount + 3;

        /* Calculate GST for the total amount */
        //if ($cpCfg['hasGST'])
        $gst_amount = 0;
        $gst_amount = (($total_amount_of_all_employees*7)/100);
        $total_amount_for_invoice = $total_amount_of_all_employees + $gst_amount;

        $total_amount_of_all_employees_formatted = number_format($total_amount_of_all_employees,2);
        $gst_amount_formatted = number_format($gst_amount, 2);
        $total_amount_for_invoice_formatted = number_format($total_amount_for_invoice, 2);

        $tbl2 = $tbl2 . '
            <tr>
                <td colspan="23"></td>
                <td align="right"><b>' . number_format($overall_total_of_all_employees, 2).'</b></td>
            </tr>
        </table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="53%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
                <td width="10%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
            </tr>
            <tr>
                <td width="53%"></td>
                <td width="20%" align="center">PREPARED BY</td>
                <td width="10%"></td>
                <td width="20%" align="center">CHECKED BY</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowProject['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintTimeSheet2Pdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
        $pdf->SetSubject('Print Timesheet');
        $pdf->SetTitle('Print Timesheet');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year = $fn->getReqParam('year');

        switch ($month) {
            case 1: $month_name = 'January';
            break;
            case 2: $month_name = 'February';
            break;
            case 3: $month_name = 'March';
            break;
            case 4: $month_name = 'April';
            break;
            case 5: $month_name = 'May';
            break;
            case 6: $month_name = 'June';
            break;
            case 7: $month_name = 'July';
            break;
            case 8: $month_name = 'August';
            break;
            case 9: $month_name = 'September';
            break;
            case 10: $month_name = 'October';
            break;
            case 11: $month_name = 'November';
            break;
            case 12: $month_name = 'December';
            break;
        }

        $SQL = "
        SELECT et.*
             ,emp.first_name
             ,p.project_code
             ,p.title AS project_title
             ,c.company_name
             ,et.hourly_rate
             ,et.admin_charges
             ,et.transport_charges
             ,DATE_FORMAT(MIN(et.date), '%d') AS min_date
             ,DATE_FORMAT(MAX(et.date), '%d') AS max_date
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
          AND et.employee_hours != ''
          AND et.date BETWEEN '{$year}-{$month}-16' AND '{$year}-{$month}-31'
        ";
        $resultProject = $db->sql_query($SQL);
        $rowProject    = $db->sql_fetchrow($resultProject);

        // Used to find list of holidays
        $sqlValuelist = "
        SELECT value FROM valuelist WHERE code = '{$year}' AND key_text = 'singaporeHolidays'";
        $resultValuelist = $db->sql_query($sqlValuelist);
        $rowValuelist    = $db->sql_fetchrow($resultValuelist);
        $arrValuelist    = explode(',', $rowValuelist['value']);
        $current_date    = date('d-m-Y');

        $pdf->SetFont('times');

        $dayContHeader = "";
        $dayHeaderCount = 16;
        $overall_total_of_all_employees = 0;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($j=16; $j<=$count2; $j++) {
            if ($dayHeaderCount <= 9) {
                $dayHeaderCountCheck = '0'.$dayHeaderCount;
            } else {
                $dayHeaderCountCheck = $dayHeaderCount;
            }

            $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
            $dayNameDate   = $fn->getCPDate($dateTimesheet, 'D');
            $dateTimesheetCheck =  $year.'-'.$month.'-'.$dayHeaderCountCheck;

            if (in_array($dateTimesheetCheck, $arrValuelist)) {
                $dayContHeader .= '<th width="2.50%" style="background-color:#92D050; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else if ($dayNameDate == 'Sun') {
                    $dayContHeader .= '<th width="2.50%" style="background-color:yellow; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else {
                $dayContHeader .= '<th width="2.50%" style="line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            }

            $dayHeaderCount++;
        }

        // Setting to show values for empty min and max dates
        $min_date = $rowProject['min_date'];
        $max_date = $rowProject['max_date'];
        if ($rowProject['min_date'] == '' && $rowProject['max_date'] == '') {
            $min_date = 16;
            $max_date = $count2;
        }

        $tbl1 = '
        <table border="0" width="100%">
            <!--<tr>
                <td align="center">SITE LOCATION: '. $rowProject['project_title'] .' </td>
            </tr>-->
            <tr>
                <td width="100%" align="center"><b>'. $min_date . '-' . $max_date . ' ' . strtoupper($month_name) . ' ' . $year.'</b></td>
                <!--<td width="40%" align="center"></td>
                <td width="30%" align="right">Client: <b>'. $rowProject['company_name'] .'</b></td>-->
            </tr>
        </table>
        ';

        $tbl2 ='
        <table border="1" cellpadding="3" width="100%" style="font-size:7px;">
            <thead>
                <tr align="center">
                    <th width="2.4%"><b>S.NO</b></th>
                    <th width="20.5%" style="line-height:300%"><b>NAME</b></th>
                    <th width="4%" style="line-height:300%">DATE</th>'.
                    $dayContHeader . '
                    <th width="5%" style="line-height:300%"><b>MAN HRS</b></th>
                    <th width="4.45%" style="line-height:300%"><b>RATES</b></th>
                    <th width="5%" style="line-height:300%"><b>AMOUNT</b></th>
                    <th width="7.5%" style="line-height:300%"><b>CLAIM AMOUNT</b></th>
                    <th width="7.4%" style="line-height:300%"><b>ALLOWANCE</b></th>
                    <th width="7.5%" style="line-height:300%"><b>TOTAL AMOUNT</b></th>
                </tr>
            </thead>
        ';

        $serialNo = 1;
        $totalValue = 0;
        $total_claim_for_employee = 0;
        $total_hours_of_all_employees = 0; // Total hours of all employees for summary
        $total_amount_of_all_employees = 0; // Total claim (amount) of all employees for summary

        $sqlTimesheet = "
        SELECT pe.employee_id
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
              ,pe.category_type
        FROM project_employee pe
        LEFT JOIN (employee e) ON (pe.employee_id = e.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.first_name ASC
        ";
        $resultTimesheet = $db->sql_query($sqlTimesheet);
        while ($row = $db->sql_fetchrow($resultTimesheet)) {
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $employee_rate     = 0;
            $employee_ot_rate  = 0;
            $employee_ph_rate  = 0;
            $admin_charges     = 0;
            $transport_charges = 0;

            $dayContRow   = "";
            $dayContOTRow = "";
            $dayContPHRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 16;
            $totalHoursSheet = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            for ($j=16; $j<=$count2; $j++) {
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,hourly_rate
                      ,ot_hourly_rate
                      ,ph_hourly_rate
                      ,admin_charges
                      ,transport_charges
                FROM employee_timesheet
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $timesheet_days    = '';
                $timesheet_OT_days = '';
                $timesheet_PH_days = ''; 
                if ($rowTimesheetDays['employee_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_days    = $rowTimesheetDays['employee_hours'];
                    } else {
                        $timesheet_days    = $str_arr[0]; // Before decimal value
                    }
                }

                if ($rowTimesheetDays['employee_ot_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ot_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_OT_days = $rowTimesheetDays['employee_ot_hours'];
                    } else {
                        $timesheet_OT_days = $str_arr[0];
                    }
                }

                if ($rowTimesheetDays['employee_ph_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ph_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_PH_days = $rowTimesheetDays['employee_ph_hours'];
                    } else {
                        $timesheet_PH_days = $str_arr[0];
                    }
                }

                $dayContRow   .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_days . '</td>';
                $dayContOTRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_OT_days . '</td>';
                $dayContPHRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_PH_days . '</td>';
                
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];

                if($rowTimesheetDays['hourly_rate'] != ''){
                  $employee_rate    = $rowTimesheetDays['hourly_rate'];
                }

                if($rowTimesheetDays['ot_hourly_rate'] != ''){
                  $employee_ot_rate = $rowTimesheetDays['ot_hourly_rate'];
                }

                if($rowTimesheetDays['ph_hourly_rate'] != ''){
                  $employee_ph_rate = $rowTimesheetDays['ph_hourly_rate'];
                }

                if($rowTimesheetDays['admin_charges'] != ''){
                  $admin_charges    = $rowTimesheetDays['admin_charges'];
                }

                if($rowTimesheetDays['transport_charges'] != ''){
                  $transport_charges = $rowTimesheetDays['transport_charges'];
                }

            }

            $total_claim_for_employee = $employee_rate * $totalHoursSheet;
            $total_claim_for_employee_formatted = number_format($total_claim_for_employee,2);

            $total_claim_for_employee_ot = $employee_ot_rate * $totalOTHoursSheet;
            $total_claim_for_employee_ot_formatted = '-';
            if ($total_claim_for_employee_ot > 0) {
                $total_claim_for_employee_ot_formatted = number_format($total_claim_for_employee_ot,2);
            }

            $total_claim_for_employee_ph = $employee_ph_rate * $totalPHHoursSheet;
            $total_claim_for_employee_ph_formatted = '-';
            if ($total_claim_for_employee_ph > 0) {
                $total_claim_for_employee_ph_formatted = number_format($total_claim_for_employee_ph,2);
            }

            $overall_claim_amount = round($total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph, 2);
            $total_allowance = $admin_charges + $transport_charges;
            $total_amount_per_employee = $overall_claim_amount + $total_allowance;
            $overall_total_of_all_employees += $total_amount_per_employee;

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
            AND pe.employee_id   = {$row['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
              $totalHoursSheetFormatted = '';
              if ($totalHoursSheet > 0) {
                  $totalHoursSheetFormatted = number_format($totalHoursSheet, 1);
              }
              $total_allowance_formatted = '';
              if ($total_allowance > 0) {
                  $total_allowance_formatted = number_format($total_allowance, 2);
              }
              $totalOTHoursSheetFormatted = '-';
              if ($totalOTHoursSheet > 0) {
                  $totalOTHoursSheetFormatted = number_format($totalOTHoursSheet, 2);
              }
              $totalPHHoursSheetFormatted = '-';
              if ($totalPHHoursSheet > 0) {
                  $totalPHHoursSheetFormatted = number_format($totalPHHoursSheet, 2);
              }

              $tbl2 = $tbl2.'
                      <tr align="center">
                          <td rowspan="3" width="2.4%"  style="line-height:800%">'.$serialNo.'</td>
                          <td rowspan="3" width="20.5%">'.$row['first_name'].'<br/><br/>' . $ic_no .'<br/><br/>' . strtoupper($row['category_type']) .'</td>
                          <td width="4%" style="line-height:200%">NH</td>'.
                          $dayContRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_formatted.'</td>
                          <td rowspan="3" width="7.5%" align="right" style="line-height:800%;">' . number_format($overall_claim_amount, 2).'</td>
                          <td rowspan="3" width="7.4%" align="right" style="line-height:800%;">'. $total_allowance_formatted.'</td>
                          <td rowspan="3" width="7.5%" align="right" style="line-height:800%;">'. number_format($total_amount_per_employee, 2).'</td>
                      </tr>
                      <tr>
                          <td width="4%" height="20px" align="center" style="line-height:200%">OT</td>'.
                          $dayContOTRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalOTHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_ot_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_ot_formatted.'</td>
                      </tr>
                      <tr>
                          <td width="4%" height="20px" align="center" style="line-height:200%">PH</td>'.
                          $dayContPHRow.'
                          <td width="5%" align="right" style="line-height:200%">'.$totalPHHoursSheetFormatted.'</td>
                          <td width="4.45%" align="right" style="line-height:200%">'.$employee_ph_rate.'</td>
                          <td width="5%" align="right" style="line-height:200%">'.$total_claim_for_employee_ph_formatted.'</td>
                      </tr>
                      ';
            $total_hours_of_all_employees  += $totalHoursSheet + $totalOTHoursSheet + $totalPHHoursSheet; // Total hours of all employees for summary
            $total_amount_of_all_employees += $total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph + $admin_charges + $transport_charges; // Total claim (amount) of all employees for summary
            $serialNo++;
          }
        }

        $colspan = $dayCount + 3;

        /* Calculate GST for the total amount */
        //if ($cpCfg['hasGST'])
        $gst_amount = 0;
        $gst_amount = (($total_amount_of_all_employees*7)/100);
        $total_amount_for_invoice = $total_amount_of_all_employees + $gst_amount;

        $total_amount_of_all_employees_formatted = number_format($total_amount_of_all_employees,2);
        $gst_amount_formatted = number_format($gst_amount, 2);
        $total_amount_for_invoice_formatted = number_format($total_amount_for_invoice, 2);

        $colspanCount = $count2 - 15  + 8;
        $tbl2 = $tbl2 . '
            <tr>
                <td colspan="'.$colspanCount.'"></td>
                <td align="right"><b>' . number_format($overall_total_of_all_employees, 2).'</b></td>
            </tr>
        </table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="53%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
                <td width="10%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
            </tr>
            <tr>
                <td width="53%"></td>
                <td width="20%" align="center">PREPARED BY</td>
                <td width="10%"></td>
                <td width="20%" align="center">CHECKED BY</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowProject['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getFindTotalWorkingDaysOfEmployeeForTimesheet($project_id, $employee_id, $month, $year) {
        $db = Zend_Registry::get('db');

        $sqlCount = "
        SELECT COUNT(*) AS total_no_of_days
        FROM employee_timesheet
        WHERE project_id = {$project_id}
          AND employee_id = {$employee_id}
          AND month = {$month}
          AND year = {$year}
          AND employee_hours > 0
        ";
        $resultCount = $db->sql_query($sqlCount);
        $row = $db->sql_fetchrow($resultCount);

        return $row['total_no_of_days'];
    }

    /**
     *
     */
    function getFindTotalWorkingDaysOfOTEmployeeForTimesheet($project_id, $employee_id, $month, $year) {
        $db = Zend_Registry::get('db');

        $sqlCount = "
        SELECT COUNT(*) AS total_no_of_days
        FROM employee_timesheet
        WHERE project_id = {$project_id}
          AND employee_id = {$employee_id}
          AND month = {$month}
          AND year = {$year}
          AND employee_ot_hours > 0
        ";
        $resultCount = $db->sql_query($sqlCount);
        $row = $db->sql_fetchrow($resultCount);

        return $row['total_no_of_days'];
    }

    /**
     *
     */
    function getFindTotalWorkingDaysOfPHEmployeeForTimesheet($project_id, $employee_id, $month, $year) {
        $db = Zend_Registry::get('db');

        $sqlCount = "
        SELECT COUNT(*) AS total_no_of_days
        FROM employee_timesheet
        WHERE project_id = {$project_id}
          AND employee_id = {$employee_id}
          AND month = {$month}
          AND year = {$year}
          AND employee_ph_hours > 0
        ";
        $resultCount = $db->sql_query($sqlCount);
        $row = $db->sql_fetchrow($resultCount);

        return $row['total_no_of_days'];
    }

    /**
     *
     */
    function getNew(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name ASC
        ";
        $sqlCat     = $fn->getValueListSQL('projectCategory');
        $sqlContact = '';

        $expVl   = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Title *', 'title')}
        {$formObj->getDDRowBySQL('Company *', 'company_id', $sqlCompany)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact)}
        {$formObj->getDDRowBySQL('Category *', 'category', $sqlCat, '', $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getAddHoursProjectEmployee() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $project_id = $fn->getReqParam('project_id');

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;

        $currentMonth = date("n");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
            'hideFirstOption' => true
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         1 => 'January'
                        ,2 => 'February'
                        ,3 => 'March'
                        ,4 => 'April'
                        ,5 => 'May'
                        ,6 => 'June'
                        ,7 => 'July'
                        ,8 => 'August'
                        ,9 => 'September'
                        ,10 => 'October'
                        ,11 => 'November'
                        ,12 => 'December'
                      );

        $SQLCheckMonth = "
        SELECT month
              ,DATE_FORMAT(date, '%c') AS Month
        FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        GROUP BY month,project_id
        ";
        $resultCheckMonth    = $db->sql_query($SQLCheckMonth);
        $dataArrayCheckMonth = $dbUtil->getResultsetAsArrayForForm($resultCheckMonth);

        $monthResultArray = array_diff_key($monthArray, $dataArrayCheckMonth);

        $yearRow  = "{$formObj->getDropDownByArray('Year', 'project_Time_year', $yearArray, $currentYear, $exp)}";
        $MonthRow = "{$formObj->getDropDownByArray('Month', 'project_Time_Month', $monthArray, $currentMonth, $expmonth)}";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&showHTML=0";
        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class= 'float_box'>

                <div class= 'yearDivHoursEmployeeForm'>
                  <label>Year: </label>
                  {$yearRow}

                  <label class='monthlabelfilter'>Month: </label>
                  {$MonthRow}
                </div>
                <div class='float_right validationDivforAdd'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            </div>

            <div class= 'float_box chargesDivEmployeeForm'>
            </div>

            <div class= 'timesheetTableProj'>
                {$this->getAddDaysRowHeadTimesheet($project_id, $currentMonth, $currentYear)}
            </div>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddDaysRowHeadTimesheet($project_id= '', $currentMonth= '', $currentYear= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($currentMonth == ''){
            $currentMonth = $fn->getReqParam('selected_month');
        }

        if($currentYear == ''){
            $currentYear = $fn->getReqParam('selected_year');
        }

        $text = "";
        $rows = "";
        $header = "";

        $SQL = "
        SELECT pe.employee_id
             ,e.first_name
             ,pe.category_type
        FROM `project_employee` pe
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        WHERE pe.project_id = {$project_id}
          AND e.status = 'Current'
          AND pe.active_in_project = 1
        ORDER BY e.first_name ASC
        ";

        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $rowSplitCount = 1;
            for ($j= 0; $j < $count2; $j++) {
            $dayContHeader = "";
            $dayNameRow = "";
                $timeSheetDate =  $currentYear.'-'.$currentMonth.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }
                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayContRow .= "
                <th class='timesheetDaysTd txtCenter'>
                    {$dayNameDate}
                    <br/>
                    {$dayCount}&nbsp;
                    <input type='text' value=''  id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysNormalInput txtRight' name='TimesheetDaysProject{$dayCount}[]'>
                    <br/><br/>
                    <input type='text' value=''  id='timeSheetOTDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysOTInput txtRight' name='TimesheetDaysProjectOT{$dayCount}[]'>
                    <br/><br/>
                    <input type='text' value=''  id='timeSheetPHDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysPHInput txtRight' name='TimesheetDaysProjectPH{$dayCount}[]'>
                </th>";
                $dayCount++;

                $totalHoursSheet += $rowTimesheetDays['employee_hours'];
            }

            $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

            $SQLTimesheet = "
            SELECT hourly_rate
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            $totalHoursSheet = number_format($totalHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                    ";

            $dayContHeader = "";
            $dayNameRow = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $currentYear.'-'.$currentMonth.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hrlyRate = '';
            $SQLQuote = "
            SELECT qi.quantity
            FROM quote q
            LEFT JOIN quote_items qi ON (qi.quote_id = q.quote_id)
            WHERE q.project_id = {$project_id}
            AND (q.quote_status = 'Awarded' OR q.quote_status = 'Order Raised')
            ";
            $resultQuote = $db->sql_query($SQLQuote);
            $QuoteRec    = $db->sql_fetchrow($resultQuote);

            if ($QuoteRec['quantity'] != ''){
                $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
                if($projRec['category'] == 'Manpower Supply'){
                    $hrlyRate = $QuoteRec['quantity'];
                }
            }

            if ($row['category_type'] != "") {
                $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$row['category_type']}' AND project_id = {$project_id}");
                if ($qiRec) {
                    $hrlyRate = $qiRec['amount'];
                    $ot_rate  = $qiRec['ot_rate'];
                    $ph_rate  = $qiRec['ph_rate'];
                }
            } else {
              $SQLEc = "
              SELECT *
              FROM employee_category
              WHERE employee_id = {$row['employee_id']}
              ";
              $resultEc = $db->sql_query($SQLEc);
              $hrlyRate = '';
              $ot_rate  = '';
              $ph_rate  = '';
              while ($rowEc = $db->sql_fetchrow($resultEc)) {
                $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$rowEc['category']}' AND project_id = {$project_id}");
                if ($qiRec) {
                    $hrlyRate = $qiRec['amount'];
                    $ot_rate  = $qiRec['ot_rate'];
                    $ph_rate  = $qiRec['ph_rate'];                    
                }
              }
            }

            $rows .= "
                <table class='thinlist timesheetTableProjReltab'>
                    <thead>
                        <tr>
                            <th>S.No: <br/>{$count}</th>
                            <th colspan='3'>
                                <div class = 'float_left'>Employee Name:
                                    <div class = 'employee_name_timesheet float_right'>
                                        {$row['first_name']}
                                    </div>
                                </div>
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>Normal Rate / HR:
                                <input type='text' value='{$hrlyRate}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]' />
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>OT Rate / HR:
                                <input type='text' value='{$ot_rate}' id='timeSheetOTRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysOTRatePerHr txtRight' name='TimesheetOTRatePerHr[]' />
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>PH Rate / HR:
                                <input type='text' value='{$ph_rate}' id='timeSheetPHRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysPHRatePerHr txtRight' name='TimesheetPHRatePerHr[]' />
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total Normal HRS:
                                <input type='text' value='' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total OT HRS:
                                <input type='text' value='' id='timeSheetOTTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total PH HRS:
                                <input type='text' value='' id='timeSheetPHTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                        </tr>
                        <tr>
                            <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Admin Charges:<br/>
                                <input type='text' value='' id='admin_charges_{$row['employee_id']}' class='text adminChargesEmployee txtRight' name='adminChargesEmployee[]'>
                            </th>
                            <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Transport Charges:<br/>
                                <input type='text' value='' id='transport_charges_{$row['employee_id']}' class='text transportChargesEmployee txtRight' name='transportChargesEmployee[]'>
                            </th>
                            <th colspan='2' class='txtCenter'>Normal Rate<br/> Row 1</th>
                            <th colspan='2' class='txtCenter'>OT Rate<br/> Row 2</th>
                            <th colspan='2' class='txtCenter'>PH Rate<br/> Row 3</th>
                            <th colspan='2'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            {$daysRow}
                        </tr>
                    </tbody>
                </table>
            <br/>
            ";

            $count++;
        }

        $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

        $SQLProjectTimeSheet ="
        SELECT * FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
        ";
        $resultProjectTimeSheet  = $db->sql_query($SQLProjectTimeSheet);
        $numRowsProjectTimeSheet = $db->sql_numrows($resultProjectTimeSheet);

        if($numRowsProjectTimeSheet > 0){
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                <p class='ValidationForTimesheetRecord'> Record already created for this month. </p>
            </div>
            ";
        }else{
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                {$rows}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getEditHoursProjectEmployee() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $month = ltrim($month,"0");

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;
        $currentMonth = date("m");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
              'hideFirstOption' => true
             ,'disabled'  => true
        );

        $expmonth = array(
            'hideFirstOption' => true
            ,'useKey' => true
            ,'disabled'  => true
        );

        switch ($month) {
            case 1: $monthVal = 'January';
            break;
            case 2: $monthVal = 'February';
            break;
            case 3: $monthVal = 'March';
            break;
            case 4: $monthVal = 'April';
            break;
            case 5: $monthVal = 'May';
            break;
            case 6: $monthVal = 'June';
            break;
            case 7: $monthVal = 'July';
            break;
            case 8: $monthVal = 'August';
            break;
            case 9: $monthVal = 'September';
            break;
            case 10: $monthVal = 'October';
            break;
            case 11: $monthVal = 'November';
            break;
            case 12: $monthVal = 'December';
            break;
        }

        $SQLTimesheetDays = "
        SELECT admin_charges
              ,transport_charges
        FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        ";
        $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
        $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&showHTML=0";

        $SQLInvoiceCheck = "
        SELECT i.start_date
              ,i.end_date
        FROM `invoice` i
        LEFT JOIN `order` o ON(o.project_id = {$project_id})
        WHERE i.status != 'Cancelled'
        AND i.order_id = o.order_id
        AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
        AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
        $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
        $msg = '';
        if($numRowsInvoiceCheck > 0){
            $msg = "<div class='msgforInvoiceCreated'><font>Please cancel the related invoice to edit the below records.</font></div>";
        }

        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class= 'float_box'>
                <div class= 'float_box yearDivHoursEmployeeForm'>
                  <label>Year: &nbsp;{$year}</label>
                  <label class='monthlabelfilter'>Month: &nbsp;{$monthVal}</label>                  
                </div>

                <div class='float_right validationDivforEdit'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                {$msg}
            </div>
            <div class='timesheetTableProj'>
                {$this->getEditDaysRowHeadTimesheet($project_id, $month, $year)}
            </div>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_Time_year' value='{$year}' />
            <input type='hidden' name='project_Time_Month' value='{$month}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditDaysRowHeadTimesheet($project_id= '', $month= '', $year= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($year == ''){
            $year = $fn->getReqParam('year');
        }

        if($month == ''){
            $month = $fn->getReqParam('month');
        }

        $text = "";
        $rows = "";
        $header = "";

        $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

        $SQL = "
        SELECT e.first_name
              ,e.employee_id
              ,et.category_type
        FROM project_employee et
        LEFT JOIN employee e ON(e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        GROUP BY et.employee_id
        ORDER BY e.first_name ASC
        ";

        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet   = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            $rowSplitCount = 1;
            for ($j= 0; $j < $count2; $j++) {
            $dayContHeader = "";
            $dayNameRow = "";
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $SQLInvoice = "
                SELECT i.start_date
                      ,i.end_date
                FROM `invoice` i
                LEFT JOIN `order` o ON(o.project_id = {$project_id})
                WHERE i.status != 'Cancelled'
                AND i.order_id = o.order_id
                AND '{$timeSheetDate}' BETWEEN i.start_date AND i.end_date
                ";
                $resultInvoice   = $db->sql_query($SQLInvoice);
                $numRowsInvoice  = $db->sql_numrows($resultInvoice);

                $disabledInput = '';
                if($numRowsInvoice > 0){
                    $disabledInput = "disabled=1";
                }

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }
                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayContRow .= "
                <th class='timesheetDaysTd txtCenter'>
                      {$dayNameDate}
                      <br>
                      {$dayCount}&nbsp;
                      <input type='text' value='{$rowTimesheetDays['employee_hours']}'     id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysNormalInput txtRight' name='TimesheetDaysProject{$dayCount}[]' {$disabledInput}>
                      <br><br>
                      <input type='text' value='{$rowTimesheetDays['employee_ot_hours']}'  id='timeSheetOTDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysOTInput txtRight' name='TimesheetDaysProjectOT{$dayCount}[]' {$disabledInput}>
                      <br><br>
                      <input type='text' value='{$rowTimesheetDays['employee_ph_hours']}'  id='timeSheetPHDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysPHInput txtRight' name='TimesheetDaysProjectPH{$dayCount}[]' {$disabledInput}>
                </th>";
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];
            }

            $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

            $SQLTimesheet = "
            SELECT hourly_rate
                  ,ot_hourly_rate
                  ,ph_hourly_rate
                  ,admin_charges
                  ,transport_charges
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            if($rowTimesheet['hourly_rate'] == ''){
                /*CALCULATING HRLY RATE*/
                $hrlyRate = '';
                $SQLQuote = "
                SELECT qi.quantity
                FROM quote q
                LEFT JOIN quote_items qi ON (qi.quote_id = q.quote_id)
                WHERE q.project_id = {$project_id}
                AND (q.quote_status = 'Awarded' OR q.quote_status = 'Order Raised')
                ";
                $resultQuote = $db->sql_query($SQLQuote);
                $QuoteRec    = $db->sql_fetchrow($resultQuote);

                if ($QuoteRec['quantity'] != ''){
                    $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
                    if($projRec['category'] == 'Manpower Supply'){
                        $hrlyRate = $QuoteRec['quantity'];
                    }
                }

                if ($row['category_type'] != "") {
                    $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$row['category_type']}' AND project_id = {$project_id}");
                    if ($qiRec) {
                        $hrlyRate = $qiRec['amount'];
                        $ot_rate  = $qiRec['ot_rate'];
                        $ph_rate  = $qiRec['ph_rate'];
                    }
                } else {
                  $SQLEc = "
                  SELECT *
                  FROM employee_category
                  WHERE employee_id = {$row['employee_id']}
                  ";
                  $resultEc = $db->sql_query($SQLEc);
                  $hrlyRate = '';
                  $ot_rate  = '';
                  $ph_rate  = '';
                  while ($rowEc = $db->sql_fetchrow($resultEc)) {
                    $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$rowEc['category']}' AND project_id = {$project_id}");
                    if ($qiRec) {
                        $hrlyRate = $qiRec['amount'];
                        $ot_rate  = $qiRec['ot_rate'];
                        $ph_rate  = $qiRec['ph_rate'];                    
                    }
                  }
                }
                /*CALCULATING HRLY RATE ENDS*/
            } else{
                $hrlyRate = $rowTimesheet['hourly_rate'];
                $ot_rate  = $rowTimesheet['ot_hourly_rate'];
                $ph_rate  = $rowTimesheet['ph_hourly_rate'];                                  
            }    
            $totalHoursSheet   = number_format($totalHoursSheet, 2, '.', '');
            $totalOTHoursSheet = number_format($totalOTHoursSheet, 2, '.', '');
            $totalPHHoursSheet = number_format($totalPHHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                    ";

            $dayContHeader = "";
            $dayNameRow = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hiddenHrlyRate = '';
            $SQLInvoiceCheck = "
            SELECT i.start_date
                  ,i.end_date
            FROM `invoice` i
            LEFT JOIN `order` o ON(o.project_id = {$project_id})
            WHERE i.status != 'Cancelled'
            AND i.order_id = o.order_id
            AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
            AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
            $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
            $disabledInputHrly = '';
            $hiddenOTHrlyRate  = '';
            $hiddenPHHrlyRate  = '';
            if($numRowsInvoiceCheck > 0){
                $disabledInputHrly = "disabled=1";
                $hiddenHrlyRate   = "<input type='hidden' value='{$hrlyRate}' name='TimesheetRatePerHr[]'>";
                $hiddenOTHrlyRate = "<input type='hidden' value='{$ot_rate}' name='TimesheetOTRatePerHr[]'>";
                $hiddenPHHrlyRate = "<input type='hidden' value='{$ph_rate}' name='TimesheetPHRatePerHr[]'>";
            }

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
            FROM `employee_timesheet` et
            LEFT JOIN employee e ON (e.employee_id = et.employee_id)
            WHERE et.employee_id   = {$row['employee_id']}
              AND et.project_id    = {$project_id}
              AND DATE_FORMAT(et.date, '%Y-%m') = '{$yearMonthSelected}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);

            $projEmployee = $fn->getRecordByCondition('project_employee', "project_id = {$project_id} AND employee_id = {$row['employee_id']}");

            $noteTxt = '';
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $projEmployee['active_in_project'] == 0){
            }else{
                if($numRowsInvoice > 0) {
                    $noteTxt = "NOTE: Please note that Invoice is already generated for the month. Goto related Order record and cancel the Invoice for the month to make further changes for timesheet.";
                }

                $rows .= "
                {$noteTxt}
                  <table class='thinlist timesheetTableProjReltab mt10'>
                      <thead>
                          <tr>
                              <th>S.No: {$count}</th>
                              <th colspan='3'>
                                  <div class = 'float_left'>Employee Name:
                                      <div class = 'employee_name_timesheet float_right'>
                                          {$row['first_name']}
                                      </div>
                                  </div>
                              </th>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>Normal Rate / HR:
                                  <input type='text' {$disabledInput} value='{$hrlyRate}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]' {$disabledInputHrly}>
                                  {$hiddenHrlyRate}
                              </th>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>OT Rate / HR:
                                  <input type='text' {$disabledInput} value='{$ot_rate}' id='timeSheetOTRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysOTRatePerHr txtRight' name='TimesheetOTRatePerHr[]' {$disabledInputHrly}/>
                                  {$hiddenOTHrlyRate}
                              </th>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>PH Rate / HR:
                                  <input type='text' {$disabledInput} value='{$ph_rate}' id='timeSheetPHRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' class='text timeSheetDaysPHRatePerHr txtRight' name='TimesheetPHRatePerHr[]' {$disabledInputHrly}/>
                                  {$hiddenPHHrlyRate}
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total Normal HRS:
                                  <input type='text' value='{$totalHoursSheet}' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total OT HRS:
                                  <input type='text' value='{$totalOTHoursSheet}' id='timeSheetOTTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total PH HRS:
                                  <input type='text' value='{$totalPHHoursSheet}' id='timeSheetPHTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                          </tr>
                          <tr>
                              <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Admin Charges:<br/>
                                  <input type='text' {$disabledInput} value='{$rowTimesheet['admin_charges']}' id='admin_charges_{$row['employee_id']}' class='text adminChargesEmployee txtRight' name='adminChargesEmployee[]'>
                              </th>
                              <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Transport Charges:<br/>
                                  <input type='text' {$disabledInput} value='{$rowTimesheet['transport_charges']}' id='transport_charges_{$row['employee_id']}' class='text transportChargesEmployee txtRight' name='transportChargesEmployee[]'>
                              </th>
                              <th colspan='8'></th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr>
                              {$daysRow}
                          </tr>
                      </tbody>
                  </table>
              <br/>
              ";

            $count++;
          }

        }

        $text = "
        <div class= 'float_box timesheetTableProjRel'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getTimeSheetByEmployee($project_id, $employee_id, $year_Months) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT e.employee_id
              ,SUM(employee_hours)As employee_total_hrs
              ,SUM(employee_ot_hours) AS totalOTHours
              ,SUM(employee_ph_hours) AS totalPHHours
              ,SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
              ,e.first_name
              ,e.employee_work_type
              ,et.admin_charges
              ,et.transport_charges
              ,et.hourly_rate AS add_hourly_rate
        FROM employee_timesheet et
        LEFT JOIN (employee e) ON (e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$year_Months}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        $urlPrintEmployeePdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

        $addEmployeeLineItemView = '';
        if($row['employee_total_hrs'] > 0 ) {
            $addEmployeeLineItemView ="
            <div class='float_right'>
                <a  class='timesheetLayoutShow'>View Hours</a>
            </div>
            ";
        }

        $amount = ($row['employee_total_hrs'] * $row['add_hourly_rate']);
        $amount = $amount + $row['totalOTAmount'] + $row['totalPHAmount'] + $row['admin_charges'] + $row['transport_charges'];
        $amount = number_format($amount ,2);

        $rows = "
        <tr class='addEmployeeRow2 employeeListHide'>
            <td>{$row['first_name']}</td>
            <td>{$row['employee_total_hrs']}</td>
            <td>{$row['totalOTHours']}</td>
            <td>{$row['totalPHHours']}</td>
            <td>{$amount}</td>
            <td class='viewRowWidth'>{$addEmployeeLineItemView}</td>
        </tr>
        <tr class='timesheetLayoutHide timeSheetLayout'>
            <td colspan='1'>
            </td>
            <td colspan='5'>
                <div class = 'timeSheetTableScroll'>
                    <table class='thinlist'>
                        {$this->getEmployeeAddTimeHoursNewListView($project_id,$row['employee_id'], $year_Months)}
                    </table>
                </div>
            </td>
        </tr>
        ";

        $text = '';

        if ($numRows > 0)  {
            $text = "
            {$rows}
            ";

           return $text;
        }
    }

    /**
     *
     */
    function getEmployeeAddTimeHoursNewListView($project_id, $employee_id, $year_Months) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT et.*
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year_Months}'
        ORDER BY et.date ASC
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

            while ($row = $db->sql_fetchrow($result)) {
                $employee_date   = $fn->getCPDate($row['date'], 'd-m-Y');

                $editEmployeeView = "index.php?_topRm=project&module=enggCrm_project&_spAction=editEmploymentViewItem&project_id={$project_id}&employee_id={$row['employee_id']}&employee_timesheet_id={$row['employee_timesheet_id']}&showHTML=0";

                $editEmployeeView = "
                <div class='float_left'>
                    <a class='editForEmployeeItemView' href='{$editEmployeeView}'>Edit</a>
                </div>
                ";

                $deleteEmployeeView = "
                <div class='float_right'>
                    <a  class='deleteForEmployeeItemView' project_id='{$row['project_id']}' employee_id= '{$row['employee_id']}' employee_timesheet_id={$row['employee_timesheet_id']}>Delete</a></td>
                </div>
                ";

                $rows .= "
                    <tr class = 'employeeItemBackgroundSecond'>
                        <td>{$employee_date}</td>
                        <td>{$row['employee_hours']}</td>
                        <td>{$row['employee_ot_hours']}</td>
                        <td>{$row['employee_ph_hours']}</td>
                    </tr>
                ";
            }

            $text = '';

            if ($numRows > 0)  {
            $text = "
            <tr class='employeeTrTh'>
                <th>Date</th>
                <th>Hours</th>
                <th>OT Hours</th>
                <th>PH Hours</th>
            </tr>
            {$rows}
            ";

            return $text;

        }
    }

    /**
     *
     */
    function getAddLineItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id     = $fn->getReqParam('project_id');
        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');

        $part_no     = "<input type='text' value='' id='partno' class='text lineItemPartno' name='partno[]'>";
        $description = "<textarea type='text' value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $title       = "<textarea type='text' value='' id='title' class='text lineItemTitle' name='title[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='unit_price' class='text lineItemUnitPrice' name='unit_price[]'>";
        $total_cost  = "<td><input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'></td>";
        $remarks     = "<textarea type='text' value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
        $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";

        $category = "
        <select name='title[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory)}
        </select>
        ";

        $nationality = "
        <select name='nationality[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlNationality)}
        </select>
        ";

        $ot_rate    = "<input type='text' value='' id='ot_rate' class='text lineItemTitle' name='ot_rate[]'>";
        $ph_rate    = "<input type='text' value='' id='ph_rate' class='text lineItemTitle' name='ph_rate[]'>";
        $scaffold_code    = "<input type='text' value='' id='scaffold_code' class='text lineItemScaffoldCode' name='scaffold_code[]'>";
        $erection    = "<input type='text' value='' id='erection' class='text lineItemErection' name='erection[]'>";
        $dismantle    = "<input type='text' value='' id='dismantle' class='text lineItemDismantle' name='dismantle[]'>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td align='center'>{$unit}</td>
            <td>{$quantity}</td>
            <td align='center'>{$amount}</td>
            {$total_cost}
            <!--<td>{$remarks}</td>-->
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getConfirmedQuoteDetails($project_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if($project_id == "") {
          $project_id = $fn->getReqParam('project_id');
        }

        $SQLQuote = "
        SELECT quote_code
              ,quote_id
              ,quote_date
              ,title
              ,quote_status
              ,created_by
              ,creation_date
              ,modified_by
              ,modification_date
        FROM quote
        WHERE project_id   = '{$project_id}'
          AND quote_status = 'Awarded'
        ";
        $resultQuote = $db->sql_query($SQLQuote);
        $rows = '';
        while ($rowQuote = $db->sql_fetchrow($resultQuote)) {
            $gotoProjectBtn = '';
            $generateFinanceRecordLbl = "Generate Finance Record";
            $orderRows = $fn->getRecordCount('order', "project_id = {$project_id}");
            if ($orderRows > 0) {
                $orderRec = $fn->getRecordRowByID('order', 'project_id', $project_id);
                $urlOrderRecord = "index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$orderRec['order_id']}";
                $gotoProjectBtn = "
                <div class='btn btn-danger mb5'>
                    <a href='{$urlOrderRecord}' title='Order Record' target='_blank'>Goto Finance</a>
                </div>
                ";

                $generateFinanceRecordLbl = "Update Finance Record";
            }

            $quoteConfirmCount = $fn->getRecordCount('quote', "project_id = {$project_id} AND (quote_status = 'Awarded' OR quote_status = 'Order Raised')");

            $orderBtn = '';
            if ($quoteConfirmCount){
                $generateOrderRecordClass = "generateOrderRecords";

                $orderBtn = "
                <div class='btn btn-danger mb5 mr10'>
                    <a  class='{$generateOrderRecordClass}' quote_id='{$rowQuote['quote_id']}' project_id='{$project_id}'>{$generateFinanceRecordLbl}</a>
                </div>
                ";
            }

            $rows .= "
            <div class='row'>
              <div class='col-md-6'>
                <table class='thinlist table'>
                  <tr>
                    <th width='30%' class='txtCenter'>Quote Code: {$rowQuote['quote_code']}</th>
                    <td width='40%' class='txtCenter'>{$orderBtn}</td>
                    <td width='40%' class='txtCenter'>{$gotoProjectBtn}</td>
                  </tr>
                </table>
              </div>
            </div>
            "; 
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}