<?
class CP_Admin_Modules_Payroll_MonthlyPayslip_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $db = Zend_Registry::get('db');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $SQLpayslip = "
            SELECT payroll_management_id
                  ,count(employee_id) AS no_of_payslip
            FROM `payroll_management` 
            WHERE payroll_year = {$row['payroll_year']}
            AND payroll_month = {$row['payroll_month']}
            ";
            $resultpayslip = $db->sql_query($SQLpayslip);
            $rowpayslip = $db->sql_fetchrow($resultpayslip);
            $no_of_payslip =($rowpayslip['no_of_payslip']);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['payroll_year'])}
            {$listObj->getListDataCell($row['payroll_month'])}
            {$listObj->getListDataCell($no_of_payslip)}
            {$listObj->getListRowEnd($row['payroll_management_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Payslip Year', 'p.payroll_year')}
        {$listObj->getListHeaderCell('Payslip Month', 'p.payroll_month')}
        {$listObj->getListHeaderCell('No of payslips', '')}
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

        $fieldset = "
        {$formObj->getTBRow('Employee ID', 'employee_id')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $fielset = "
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'payroll_management_id');
        $payroll_management_id  = $fn->getReqParam('payroll_management_id');
        $employee_id = $fn->getReqParam('employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_monthlyPayslip', 'attachment', $row)}
        ";


        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $text = "
        
        ";

        return $text;
    }
}