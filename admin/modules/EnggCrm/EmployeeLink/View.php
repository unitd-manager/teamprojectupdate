<?
class CPL_Admin_Modules_EnggCrm_EmployeeLink_View extends CP_Admin_Modules_EnggCrm_EmployeeLink_View
{
    function getList($dataArray, $linkRecType) {
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row) {

            $jobInfo = $fn->getRecordByCondition('job_information', "employee_id = {$row['employee_id']} AND status = 'Current'");

            $rate = '';
            if ($jobInfo['payment_type'] == 'Hourly') {
                $rate = number_format($jobInfo['hourly_pay_rate'],2);
            } else {
                $rate = number_format($jobInfo['basic_pay'], 2);
            }

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($rate)}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['employee_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'employee_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Basic Salary', 'employee_name')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn     = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_type   = $fn->getReqParam('employee_type');
        $employee_status = $fn->getReqParam('employee_status');

        if ($employee_status == '') {
            $employee_status = 'Current';
        }

        $employee_type_array = array(
              "In house"
             ,"Others"
        );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $text = "
        <td>
            <select name='employee_type'>
                <option value=''>Employee Type</option>
                {$cpUtil->getDropDown1($employee_type_array, $employee_type)}
            </select>
        </td>
        <td>
            <select name='employee_status'>
                <option value=''>Employee Status</option>
                {$cpUtil->getDropDown1($status, $employee_status)}
            </select>
        </td>
        ";

        return $text;

    }
}
