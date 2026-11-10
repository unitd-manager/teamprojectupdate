<?
class CP_Admin_Modules_ManPower_OpportunityCandidate_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $sqlCompany ="
            SELECT c.company_name
            FROM opportunity_candidate oc
            LEFT JOIN opportunity o ON (o.opportunity_id = oc.opportunity_id)
            LEFT JOIN company c ON (c.company_id = o.company_id)
            WHERE oc.opportunity_candidate_id = {$row['opportunity_candidate_id']}
            ";

            $resultCompany = $db->sql_query($sqlCompany);
            $rowcompany    = $db->sql_fetchrow($resultCompany);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['opportunity_code'])}
            {$listObj->getGoToDetailText($count, $row['opportunity_title'])}
            {$listObj->getListDataCell($row['candidate_name'])}
            {$listObj->getListDataCell($row['passport_no'])}
            {$listObj->getListDataCell($rowcompany['company_name'])}
            {$listObj->getListDataCell($row['process_status'])}
            {$listObj->getListDataCell($row['response_status'])}
            {$listObj->getListDataCell($row['percent_win'])}
            {$listObj->getListRowEnd($row['opportunity_candidate_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Opportunity Code', 'o.opportunity_code')}
        {$listObj->getListHeaderCell('Opportunity Title', 'o.opportunity_title')}
        {$listObj->getListHeaderCell('Candidate Name', 'c.candidate_name')}
        {$listObj->getListHeaderCell('Passport No', 'oc.passport_no')}
        {$listObj->getListHeaderCell('Client Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Process Status', 'oc.process_status')}
        {$listObj->getListHeaderCell('Response Status', 'oc.response_status')}
        {$listObj->getListHeaderCell('Percent', 'oc.percent_win')}
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

        $fielset = "
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $sqlProStatus = $fn->getValueListSQL('processStatus','sort_order');
        $sqlResStatus = $fn->getValueListSQL('responseStatus');
        $sqlPercent   = $fn->getValueListSQL('percent');        
        
        $sqlCandidate = $fn->getDDSql('manPower_candidate');        
        $exp = array('sqlType' => 'OneField');
        $exp1 = array('isEditable' => 0);

        $expCandidate  = array('detailValue' => $row['candidate_name'], 'isEditable' => 0);

        $fielset1 = "
        {$formObj->getDDRowBySQL('Name', 'candidate_id', $sqlCandidate, $row['candidate_id'], $expCandidate)}
        {$formObj->getTBRow('Passport NO', 'travel_document_no', $row['travel_document_no'], $exp1)}
        {$formObj->getDDRowBySQL('Process Status', 'process_status', $sqlProStatus, $row['process_status'], $exp)}
        {$formObj->getDDRowBySQL('Response Status', 'response_status', $sqlResStatus, $row['response_status'], $exp)}
        {$formObj->getDDRowBySQL('Percent', 'percent_win', $sqlPercent, $row['percent_win'], $exp)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Opportunity Candidate Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $attachments = '';
        
        if( $cpCfg['cp.hasMultiUniqueSites'] == 'true'){
            $attachments ="
            {$media->getRightPanelMediaDisplay('Authorization Letter', 'manPower_opportunityCandidate', 'attachment1', $row)}        
            {$media->getRightPanelMediaDisplay('Application Amount Bank Chellan in Colour', 'manPower_opportunityCandidate', 'attachment2', $row)}        
            {$media->getRightPanelMediaDisplay('Application Copy ', 'manPower_opportunityCandidate', 'attachment3', $row)}        
            {$media->getRightPanelMediaDisplay('Approval Letter(IPA)', 'manPower_opportunityCandidate', 'attachment4', $row)}        
            {$media->getRightPanelMediaDisplay('Rejection Letter', 'manPower_opportunityCandidate', 'attachment5', $row)}        
            {$media->getRightPanelMediaDisplay('Welcome Letter', 'manPower_opportunityCandidate', 'attachment6', $row)}        
            {$media->getRightPanelMediaDisplay('Westrama & Candidate Agreement', 'manPower_opportunityCandidate', 'attachment7', $row)}        
            {$media->getRightPanelMediaDisplay('Declaration Letter', 'manPower_opportunityCandidate', 'attachment8', $row)}        
            {$media->getRightPanelMediaDisplay('With Drawal Letter', 'manPower_opportunityCandidate', 'attachment9', $row)}        
            ";
        }
        
        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'manPower_opportunityCandidate', 'attachment', $row)}
        {$attachments}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $process_status  = $fn->getReqParam('process_status');
        $response_status = $fn->getReqParam('response_status');
        $company_id      = $fn->getReqParam('company_id');
        $sqlProStatus    = $fn->getValueListSQL('processStatus');
        $sqlResStatus    = $fn->getValueListSQL('responseStatus');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN opportunity b ON (a.company_id = b.company_id)
        JOIN opportunity_candidate oc ON (oc.opportunity_id = b.opportunity_id)
        ORDER BY company_name
        ";

        $text = "

        <td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>
        <td>
            <select name='process_status' >
                <option value=''>Process Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlProStatus, $process_status)}
            </select>
        </td>
        <td>
            <select name='response_status' >
                <option value=''>Response Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlResStatus, $response_status)}
            </select>
        </td>
        ";

        return $text;
    }
}