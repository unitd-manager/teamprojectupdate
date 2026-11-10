<?
class CP_Admin_Modules_Project_Registry_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = "";
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}                                  
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['domain'])}
            {$listObj->getListDataCell($row['hosting_server'])}
            {$listObj->getListDataCell($row['last_invoice_date'])}
            {$listObj->getListDataCell($row['payment_status'])}
            {$listObj->getListDataCell($row['next_invoice_date'])}
            {$listObj->getListDataCell($row['registry_id'])}
            {$listObj->getListRowEnd($row['registry_id'])}
            
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Project Name', 'a.title')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Domain', 'a.domain')}
        {$listObj->getListHeaderCell('Location', 'a.hosting_server')}
        {$listObj->getListHeaderCell('Last Invoice', 'a.last_invoice_date')}
        {$listObj->getListHeaderCell('Payment Status', 'a.payment_status')}
        {$listObj->getListHeaderCell('Next Invoice', 'a.next_invoice_date')}
        {$listObj->getListHeaderCell('ID', 'a.registry_id', 'headerCenter')}        
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

       
    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $dateUtil = Zend_Registry::get('dateUtil');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sqlregistryStatus = $fn->getValueListSQL('registryStatus');        
        $sqlpaymentStatus  = $fn->getValueListSQL('paymentStatus');        
        $sqlpaymentTerms   = $fn->getValueListSQL('paymentTerms');        
        $sqlhostingserver  = $fn->getValueListSQL('hostingserver');        
        $sqldnsServer      = $fn->getValueListSQL('dnsServer');     
        
        $formObj->mode = $tv['action'];

        $published        = '';
        $text = '';

        $exp = array('sqlType' => 'OneField');
        $recordUrl = '';

        if ($formObj->mode == 'detail'){
            $link  .= isset($row['category_id']) ? "&_subRoom={$row['category_id']}" : "";
            $link  .= isset($row['sub_category_id']) ? "&sub_category_id={$row['sub_category_id']}" : "";
            $recordUrl = $formObj->getTBRow("Record URL", "record_url", $link  );
        }

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Project Name', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlregistryStatus, $row['status'], $expVl)}
        ";      
        $fieldset2 = "
        {$formObj->getTBRow('Domain', 'domain', $row['domain'])}
        {$formObj->getTBRow('Registrar', 'domain_registrar', $row['domain_registrar'])}
        {$formObj->getTARow('Control Panel', 'domain_cpanel', $row['domain_cpanel'])}
        {$formObj->getDDRowBySQL('DNS Server', 'dns_server', $sqldnsServer, $row['dns_server'], $expVl)}
        {$formObj->getTARow('Other Domain Details', 'domain_other_details', $row['domain_other_details'])}
        ";
        $fieldset3 = "
        {$formObj->getTARow('Dev CMS', 'dev_cms', $row['dev_cms'], $exp)}
        {$formObj->getTARow('Test CMS', 'test_cms', $row['test_cms'], $exp)}
        {$formObj->getTBRow('Live CMS', 'live_cms', $row['live_cms'], $exp)}
        ";
        $fieldset4 = "
        {$formObj->getTARow('Dev DB details', 'dev_db',  $row['dev_db'], $exp)}
        {$formObj->getTARow('Test DB details', 'test_db',  $row['test_db'], $exp)}
        {$formObj->getTARow('Live DB details', 'live_db',  $row['live_db'], $exp)}
        ";
        $fieldset5 = "
        {$formObj->getTARow('Test FTP Directory', 'test_ftp',  $row['test_ftp'], $exp)}
        {$formObj->getTARow('Live FTP Directory', 'live_ftp',  $row['live_ftp'], $exp)}
        ";
        $fieldset6 = "
        {$formObj->getTBRow('Account Code', 'analytics_ac_code',  $row['analytics_ac_code'], $exp)}
        {$formObj->getTARow('Access Details', 'analytics_access',  $row['analytics_access'], $exp)}
        ";
        $fieldset7 = "
        {$formObj->getTARow('Access Details', 'adwords_access',  $row['adwords_access'], $exp)}
        ";
        $fieldset8 = "
        {$formObj->getDDRowBySQL('Hosting Server', 'hosting_server', $sqlhostingserver, $row['hosting_server'], $expVl)}
        {$formObj->getTBRow('Alloted Space (GB)', 'host_alloted_space',  $row['host_alloted_space'], $exp)}
        {$formObj->getTBRow('Alloted Bandwidth (GB)', 'host_alloted_bwidth',  $row['host_alloted_bwidth'], $exp)}
        {$formObj->getTBRow('Used Space (GB)', 'host_used_space',  $row['host_used_space'], $exp)}
        {$formObj->getTBRow('Used Bandwidth (GB)', 'host_used_bwidth',  $row['host_used_bwidth'], $exp)}
        {$formObj->getTBRow('No. of SMTP Relays', 'no_of_smtp_relays',  $row['no_of_smtp_relays'], $exp)}
        {$formObj->getTBRow('Used SMTP Relays', 'used_smtp_relays',  $row['used_smtp_relays'], $exp)}
        {$formObj->getDateRow('Last Verified Date', 'host_last_verified',  $row['host_last_verified'], $exp)}
        {$formObj->getTARow('Hosting Notes', 'hosting_notes', $row['hosting_notes'], $exp)}
        ";
 
        $fieldset9 = "
        {$formObj->getTARow('Email Details', 'email_details', $row['email_details'], $exp)}
        ";
        $fieldset10 = "
        {$formObj->getDateRow('Go Live Date', 'live_date', $row['live_date'], $exp)}
        {$formObj->getDDRowBySQL('Payment Terms', 'payment_terms', $sqlpaymentTerms, $row['payment_terms'], $expVl)}
        {$formObj->getTBRow('Payment Schedule', 'payment_schedule', $row['payment_schedule'], $exp)}
        {$formObj->getTBRow('Hosting Fee', 'hosting_fee',  $row['hosting_fee'], $exp)}
        {$formObj->getDateRow('Last Invoice Date', 'last_invoice_date',  $row['last_invoice_date'], $exp)}
        {$formObj->getDDRowBySQL('Payment Status', 'payment_status', $sqlpaymentStatus, $row['payment_status'], $expVl)}
        {$formObj->getDateRow('Next Invoice Date', 'next_invoice_date', $row['next_invoice_date'], $exp)}
        {$formObj->getTARow('Payment Notes', 'payment_notes', $row['payment_notes'])}
        ";
        $fieldset11 = "
        {$formObj->getTARow('Other Notes', 'notes', $row['notes'], $exp)}
        ";
       
        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Domain Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('CMS Access', $fieldset3)}
        {$formObj->getFieldSetWrapped('Database', $fieldset4)}
        {$formObj->getFieldSetWrapped('FTP', $fieldset5)}
        {$formObj->getFieldSetWrapped('Google Analytics', $fieldset6)}
        {$formObj->getFieldSetWrapped('Adwords', $fieldset7)}
        {$formObj->getFieldSetWrapped('Hosting Details', $fieldset8)}
        {$formObj->getFieldSetWrapped('Emails', $fieldset9)}
        {$formObj->getFieldSetWrapped('Hosting Payment Details', $fieldset10)}
        {$formObj->getFieldSetWrapped('All Other Notes', $fieldset11)}
        
        ";
        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    }