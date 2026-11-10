<?
class CPL_Admin_Widgets_Payroll_DormitoryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $dormitory_id = $fn->getReqParam('dormitory_id');
        $dormRec = $fn->getRecordRowById('dormitory', 'dormitory_id', $dormitory_id);

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Dormitory :</b> {$dormRec['name']}</td>
            </tr>
        </table>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th class='txtCenter'>S.No</th>
                    <th>Employee Name</th>
                    <th>Room Number</th>
                    <th>HP/Mobile No</th>
                    <th>Pass Type</th>
                    <th>Fin No</th>
                    <th>Work Permit No</th>
                    <th>Work Permit Expiry</th>
                    <th>Date of Birth</th>
                </tr>
            </thead>
            {$this->getRowsHTML()}
        </table>
        </div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){

            $rows .= "
            <tbody class='employeeSummary'>
                <tr>
                    <td class='txtCenter'>{$count}</td>
                    <td>{$row['first_name']}</a></td>
                    <td>{$row['room_no']}</a></td>
                    <td>{$row['mobile']}</td>
                    <td>{$row['citizen']}</td>
                    <td>{$row['fin_no']}</td>
                    <td>{$row['work_permit']}</td>
                    <td>{$row['work_permit_expiry_date_formatted']}</td>
                    <td>{$row['date_of_birth_formatted']}</td>
                </tr>
            </tbody>
            ";                

            $count++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}