<?
class CP_Admin_Widgets_Payroll_LocalWorkers_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $rows = '';
        $sql = "
        SELECT first_name
              ,nric_no
              ,date_of_birth
        FROM employee
        WHERE status = 'Current'
          AND (citizen = 'Citizen' OR citizen = 'PR')
          {$appendSqlSite}
        ORDER BY first_name ASC
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        if ($numRows) {
            $count = 1;
            while ($row = $db->sql_fetchrow($result)) {
                /* Find difference of age - START */
                $age = '';
                if ($row['date_of_birth']) {
                    $dob_for_age = $dateUtil->formatDate($row['date_of_birth'], 'DD-MM-YYYY');
                    $dob_for_age = "01-" . substr($dob_for_age, 3);

                    $current_Month = date('m') - 1;
                    if ($current_Month <= 9 && $current_Month > 0) {
                        $current_Month = 0 . $current_Month;
                    } else if ($current_Month == 0) {
                        $current_Month = 12;
                    } else {
                        $current_Month = $current_Month;
                    }

                    if ($current_Month == 12) {
                        $current_Year  = date('Y') - 1;
                    } else {
                        $current_Year  = date('Y');
                    }

                    $payslipdate_for_age = "01-" . $current_Month . '-' . $current_Year;
                    $modObj = getCPModuleObj('payroll_payrollManagement');
                    $age = $modObj->model->getFindage($dob_for_age, $payslipdate_for_age);
                }
                /* Find difference of age - END */

                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['first_name']}</td>
                    <td>{$row['nric_no']}</td>
                    <td>{$dateUtil->formatDate($row['date_of_birth'], 'DD MMM YYYY')}</td>
                    <td>{$age}</td>
                </tr>
                ";
                $count++;
            }
        } else {
            $rows .= "
            <tr>
                <td colspan='3'>No employee found</td>
            </tr>
            ";
        }

        $text = "
        <h2>List of Local workers</h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>NRIC No</th>
                    <th>DoB</th>
                    <th>Age</th>
                </tr>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}