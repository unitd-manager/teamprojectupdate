 <?
class CP_Admin_Modules_Project_DomainHosting_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $rows  = "";
        $projectLink = '';
        $rowCounter = 0;
//            {$listObj->getListDataCell($row['company_name'])}

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $staffRec = $fn->getRecordRowById('staff', 'staff_id', $row['remind_to']);

            //{$listObj->getListDateCell($fn->getYesNo($row['auto_reminder']), "center")}
            //{$listObj->getListDataCell($staffRec['first_name'].' '.$staffRec['last_name'])}

            $sqlInvoice = "
            SELECT invoice_due_date
                   ,status
                   ,invoice_id
                   ,invoice_code
            FROM invoice
            WHERE renewal_id = {$row['renewal_id']}
            AND status != 'Cancelled'
            ";
            $resultInvoice  = $db->sql_query($sqlInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);

            $invoiceLink = '';
            if($numRowsInvoice > 0){

                $rowInvoice = $db->sql_fetchrow($resultInvoice);
                $currentDate      = date('Y-m-d');
                $invoice_due_date = $rowInvoice['invoice_due_date'];
                $due_date         = date("Y-m-d", strtotime("$invoice_due_date +1 week"));

                $invoiceLink = "<a href='index.php?_topRm=project&module=project_invoice&invoice_id={$rowInvoice['invoice_id']}&_action=detail'><u>{$rowInvoice['invoice_code']}</u></a>";

                if($currentDate >= $due_date){
                    if($rowInvoice['status'] != 'Paid'){

                        $renewalsUpdate = "
                        UPDATE renewals SET renewal_status = 'Late'
                        WHERE renewal_id = {$row['renewal_id']}
                        ";

                        $resultRenewalsUpdate  = $db->sql_query($renewalsUpdate);
                    }
                }

                if($rowInvoice['status'] == 'Paid'){

                    $renewalsStatus = "
                    UPDATE renewals SET renewal_status = 'Paid'
                    WHERE renewal_id = {$row['renewal_id']}
                    ";

                    $resultRenewalsStatus  = $db->sql_query($renewalsStatus);

                }
            }

            if ($row['renewal_status'] == 'Late'){
                $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter, 'projectList2');
            }
            else{
                $hightlightDueTasks = $listObj->getListRowHeader($row, $rowCounter);
            }


            $sqlProject = "
            SELECT project_code
                   ,project_id
            FROM project
            WHERE renewal_id = {$row['renewal_id']}
            ";
            $resultProject  = $db->sql_query($sqlProject);
            $rowProject     = $db->sql_fetchrow($resultProject);
            $numRowsProject = $db->sql_numrows($resultProject);

            $projectLink = '';
            if($numRowsProject > 0){
                $projectLink = "<a href='index.php?_topRm=project&module=project_project&project_id={$rowProject['project_id']}&_action=detail'><u>{$rowProject['project_code']}</u></a>";
            }

            $end_date = $fn->getCPDate($row['end_date'],"d-m-Y");

            $company = "<a href='index.php?_topRm=opportunity&module=project_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";

            $rows .= "
            {$hightlightDueTasks}
            {$listObj->getGoToDetailText($rowCounter, $row['renewal_type'])}
            {$listObj->getListDataCell($company, $row['company_name'])}
            {$listObj->getListDataCell($row['domain'])}
            {$listObj->getListDataCell($end_date)}
            {$listObj->getListDataCell($row['registrar'])}
            {$listObj->getListDataCell($row['server_name'])}
            {$listObj->getListDataCell($row['currency'])}
            {$listObj->getListDataCell($row['amount'],'right')}
            {$listObj->getListDataCell($projectLink)}
            {$listObj->getListDataCell($invoiceLink)}
            {$listObj->getListDataCell($row['renewal_status'])}
            {$listObj->getListRowEnd($row['renewal_id'])}
            ";
            $rowCounter++ ;
        }

        //{$listObj->getListHeaderCell('Auto Reminder ', 'auto_reminder')}
        //{$listObj->getListHeaderCell('Remind To ', 'remind_to')}

        $countText = "
        <div class='row moduleWidgetsDisplayRow'>
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-white'> 
                    <div class='widget-icon bg-blue pull-left fa fa-microphone'> 
                    </div> 
                    <div class='overflow-hidden'> 
                        <span class='widget-title'>8,372K</span> 
                        <span class='widget-subtitle'>Registered users</span> 
                    </div> 
                </div> 
            </div> 
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-white'> 
                    <div class='widget-icon bg-danger pull-left fa fa-tint'> 
                    </div> 
                    <div class='overflow-hidden'> 
                        <span class='widget-title percent'>86</span> 
                        <span class='widget-subtitle'>Revenue increase</span> 
                    </div> 
                </div> 
            </div> 
            <div class='col-sm-6 col-md-3'> 
                <div class='widget bg-white'> 
                    <div class='widget-icon bg-success pull-left fa fa-paper-plane'> 
                    </div> 
                    <div class='overflow-hidden'> 
                        <span class='widget-title'>7,355K</span> 
                        <span class='widget-subtitle'>Pending orders</span> 
                    </div> 
                </div> 
            </div>  
        </div>";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Renewal Type', 'renewal_type')}
        {$listObj->getListHeaderCell('Company Name', 'company_name')}
        {$listObj->getListHeaderCell('Domain', 'domain')}
        {$listObj->getListHeaderCell('End Date', 'end_date')}
        {$listObj->getListHeaderCell('Registrar', 'registrar')}
        {$listObj->getListHeaderCell('Server Name ', 'server_name')}
        {$listObj->getListHeaderCell('Currency ', 'currency')}
        {$listObj->getListHeaderCell('Amount ', 'amount')}
        {$listObj->getListHeaderCell('Project Code')}
        {$listObj->getListHeaderCell('Invoice Code')}
        {$listObj->getListHeaderCell('Status', 'amount')}
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $sqlRenewalType 		= $fn->getValueListSQL('renewalType');

        $expVl = array('sqlType' => 'OneField');

        $sqlCompany = "
        SELECT company_id
               ,company_name
        FROM company
        ";

        $fielset = "
        {$formObj->getDDRowBySQL('Renewal', 'renewal_type', $sqlRenewalType, '', $expVl)}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

		$company_id 	= $fn->getReqParam('company_id');

        $sqlCurrency 		 = $fn->getValueListSQL('currency');
        $sqlRegistrar 		 = $fn->getValueListSQL('registrar');
        $sqlServerName 		 = $fn->getValueListSQL('serverName');
        $sqlRemind 			 = $fn->getValueListSQL('domainHostingRemindTo');
        $sqlRenewalType 	 = $fn->getValueListSQL('renewalType');
        $domainRenewalStatus = $fn->getValueListSQL('domainRenewalStatus');

        $expVl = array('sqlType' => 'OneField');

        $sqlCompany = "
        SELECT company_id
			   ,company_name
        FROM company
        ";

        $sqlStaffname = "
        SELECT staff_id
               ,CONCAT_WS(' ', first_name, last_name) AS staff_name
        FROM `staff`
        ";
        $expStaff = array('detailValue' => $row['remind_to']);

        $expComp = array('detailValue' => $row['company_name']);

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Renewal Type', 'renewal_type', $sqlRenewalType, $row['renewal_type'], $expVl)}
        {$formObj->getDDRowBySQL('Status', 'renewal_status', $domainRenewalStatus, $row['renewal_status'], $expVl)}
        {$formObj->getTBRow('Domain', 'domain', $row['domain'])}
        {$formObj->getDDRowBySQL('Registrar', 'registrar', $sqlRegistrar, $row['registrar'], $expVl)}
        {$formObj->getDDRowBySQL('Server Name', 'server_name', $sqlServerName, $row['server_name'], $expVl)}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $row['company_id'],$expComp)}
        {$formObj->getDateRow('Start Date', 'renewal_start_date', $row['start_date'])}
        {$formObj->getDateRow('End Date', 'renewal_end_date', $row['end_date'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], $expVl)}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getYesNoRRow('Chargeable', 'renewal_chargeable',$row['chargeable'])}
        {$formObj->getYesNoRRow('Auto Reminder', 'auto_reminder', $row['auto_reminder'])}
        {$formObj->getDDRowBySQL('Remind To', 'remind_to', $sqlStaffname,$row['remind_to'])}
        {$formObj->getTARow('Notes', 'renewal_notes', $row['notes'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Domain Hosting Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        //{$formObj->getTBRow('Amount For Domain', 'amount_for_domain', $row['amount_for_domain'])}
        //{$formObj->getTBRow('Amount For Dns', 'amount_for_dns', $row['amount_for_dns'])}

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $linksArray = Zend_Registry::get('linksArray');
        $comment = getCPPluginObj('common_comment');
        $tv = Zend_Registry::get('tv');

        $record_id = $fn->getIssetParam($row, 'domain_hosting_id');

        $links   = "";
        $expProj = array();


        $links .= $displayLinkData->getLinkPortalMain('project_company', 'project_projectLink', 'Projects Linked', $row, $expProj);
        $links .= $displayLinkData->getLinkPortalMain('project_company', 'project_invoiceLink', 'Invoices Linked', $row);

        $text ="
        {$comment->getView(array(
             'roomName' => 'project_domainHosting'
            ,'recordId' => $record_id
        ))}
        <div id='projectLinkPortal'>{$this->getProjectLinkDisplay($row['renewal_id'])}</div>
        <div id='invoiceLinkPortal'>{$this->getInvoiceLinkDisplay($row['renewal_id'])}</div>
        ";
        return $text;
    }

    /**
     *
     */
    function getProjectLinkDisplay($renewal_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';

        $recCount  = $fn->getRecordCount('project', "renewal_id = '{$renewal_id}'");

        $sqlProject = "
        SELECT project_code
               ,title
               ,project_value
               ,status
               ,project_id
        FROM project
        WHERE renewal_id = {$renewal_id}
        ";
        $result  = $db->sql_query($sqlProject);

        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Project Code</th>
            <th>Title</th>
            <th class = 'txtRight'>Project Value</th>
            <th>Status</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){

            $header ="<thead></thead>";

            $rows .= "
                <tr>
                    <td class='noRecordsList'>No Records Linked</td>
                </tr>
            ";
        }

        $serialNo = 1;

        while($projetRec = $db->sql_fetchrow($result)){

            $project_value = number_format($projetRec['project_value'],2);

            $projectLink = "<a href='index.php?_topRm=project&module=project_project&project_id={$projetRec['project_id']}&_action=edit'><u>{$projetRec['project_code']}</u></a>";

            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$projectLink}</td>
                <td>{$projetRec['title']}</td>
                <td class = 'txtRight'>{$project_value}</td>
                <td>{$projetRec['status']}</td>
            </tr>
            ";

            $serialNo++;

        }


        $text = "
        <div class='linkPortalWrapper project_company__project_companyRenewalLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Project(s) Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='projectlist'>
                        {$header}
                        <tbody id='renewalDisplayPortal'>
                            {$rows}
                        </tbody>
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
    function getInvoiceLinkDisplay($renewal_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';

        $recCount  = $fn->getRecordCount('invoice', "renewal_id = '{$renewal_id}'");

        $sqlInvoice = "
        SELECT invoice_code
               ,invoice_amount
               ,status
               ,invoice_id
               ,invoice_date
        FROM invoice
        WHERE renewal_id = {$renewal_id}
        ";
        $result  = $db->sql_query($sqlInvoice);

        $header ="
        <thead>
            <tr>
            <th>#</th>
            <th>Invoice Code</th>
            <th>Invoice Date</th>
            <th class = 'txtRight'>Invoice Amount</th>
            <th>Status</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){

            $header ="<thead></thead>";

            $rows .= "
                <tr>
                    <td class='noRecordsList'>No Records Linked</td>
                </tr>
            ";
        }

        $serialNo = 1;

        while($invoiceRec = $db->sql_fetchrow($result)){

            $invoice_Amount = number_format($invoiceRec['invoice_amount'],2);

            $invoiceLink = "<a href='index.php?_topRm=project&module=project_invoice&invoice_id={$invoiceRec['invoice_id']}&_action=edit'><u>{$invoiceRec['invoice_code']}</u></a>";

            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$invoiceLink}</td>
                <td>{$invoiceRec['invoice_date']}</td>
                <td class = 'txtRight'>{$invoice_Amount}</td>
                <td>{$invoiceRec['status']}</td>
            </tr>
            ";

            $serialNo++;

        }


        $text = "
        <div class='linkPortalWrapper project_company__project_companyRenewalLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Invoice(s) Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='projectlist'>
                        {$header}
                        <tbody id='renewalDisplayPortal'>
                            {$rows}
                        </tbody>
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
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $end_date1           = $fn->getReqParam('end_date_1');
        $end_date2           = $fn->getReqParam('end_date_2');
		$company_id 		 = $fn->getReqParam('company_id');
        $renewal_status      = $fn->getReqParam('renewal_status');
		$renewal_type 		 = $fn->getReqParam('renewal_type');
		$registrar 			 = $fn->getReqParam('registrar');
		$server_name 		 = $fn->getReqParam('server_name');
		$currency 			 = $fn->getReqParam('currency');
		$auto_reminder 		 = $fn->getReqParam('auto_reminder');
        $sqlCurrency 		 = $fn->getValueListSQL('currency');
        $sqlRegistrar 		 = $fn->getValueListSQL('registrar');
        $sqlServerName 		 = $fn->getValueListSQL('serverName');
        $sqlRemind 			 = $fn->getValueListSQL('domainHostingRemindTo');
        $sqlRenewalType 	 = $fn->getValueListSQL('renewalType');
        $domainRenewalStatus = $fn->getValueListSQL('domainRenewalStatus');



        $spArray = array(
			""
           ,"Yes"
           ,"No"
        );

		$sqlCompany = "
		SELECT company_id
			  ,company_name
		FROM company
        ORDER BY company_name ASC
		";

        /*<td>
            <select name='registrar'>
                <option value=''>Registrar</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlRegistrar, $registrar)}
            </select>
        </td>
        <td>
            <select name='server_name'>
                <option value=''>Server Name</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlServerName, $server_name)}
            </select>
        </td>
        <td>
            <select name='currency'>
                <option value=''>Currency</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCurrency, $currency)}
            </select>
        </td>*/

        $text = "
        <td>
            {$formObj->getDateRangeRow('End Date:', 'end_date', $end_date1, $end_date2)}
        </td>
        <td>
            <select name='company_id'>
                <option value=''>Company Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        <td>
            <select name='renewal_type'>
                <option value=''>Renewal Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlRenewalType, $renewal_type)}
            </select>
        </td>
        <td>
            <select name='auto_reminder'>
                <option value=''>Auto Reminder</option
                {$cpUtil->getDropDown1($spArray, $auto_reminder)}
           </select>
        </td>
        <td>
            <select name='renewal_status'>
                {$dbUtil->getDropDownFromSQLCols1($db, $domainRenewalStatus, $renewal_status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getSendRenewalsForMonth() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        //http://studioussv3.localhost/admin/index.php?_topRm=project&module=project_domainHosting&_spAction=sendRenewalsForMonth&showHTML=0

        $to_date   = date('Y-m-t');        
        $from_date = date('Y-m-01');
        
        $SQL = "
        SELECT dh.*
              ,c.company_name
        FROM renewals dh
        LEFT JOIN company c ON (dh.company_id = c.company_id)
        WHERE (dh.end_date BETWEEN '{$from_date}' AND '{$to_date}')
        ORDER BY c.company_name
        ";
        $result  = $db->sql_query($SQL);
            
        $rowText = "";
        $serialNo = 1;
        while($row = $db->sql_fetchrow($result)) {

            $sqlProject = "
            SELECT project_code
                   ,title
                   ,project_value
                   ,status
                   ,project_id
            FROM project
            WHERE renewal_id = {$row['renewal_id']}
            ";
            $resultProject  = $db->sql_query($sqlProject);

            $projectLink = '';
            while($rowProject = $db->sql_fetchrow($resultProject)) {

                $projectLink = "<a href='index.php?_topRm=project&module=project_project&project_id={$rowProject['project_id']}&_action=edit'><u>{$rowProject['project_code']}</u></a>";

                $rowText .= "
                <tr>
                    <td width='30'>{$serialNo}</td>
                    <td width='80'>{$projectLink}</td>
                    <td width='200'>{$rowProject['title']}</td>
                    <td width='200'>{$row['domain']}</td>
                    <td width='100'>{$row['registrar']}</td>
                </tr>
                ";

                $serialNo++;
            }
        }
            
        $headerText = "
        <tr>
            <td width='30'><b>S.NO</b></td>
            <td width='80'><b>Code</b></td>
            <td width='200'><b>Project Title</b></td>
            <td width='200'><b>Domain</b></td>
            <td width='100'><b>Registered In</b></td>
        </tr>
        ";

        $text = "
        <table border='1'>
            <tbody>
                {$headerText}
                {$rowText}
            </tbody>
        </table>
        ";

        $MonthFullName = date('F');

        $message     = $text;
        $subject     = "USS Renewals For The Month: {$MonthFullName}";
        $fromName    = "Universal Software Solutions";
        $fromEmail   = "usstech@usoftsolutions.com";
        $toName      = "USS Admin";
        $toEmail     = "syed@usoftsolutions.com, arif@usoftsolutions.com";
        //$toEmail     = "ansari@usoftsolutions.com";

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $exp = array('showHeader' => false);
        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail($exp);

        return $text;
    }

}