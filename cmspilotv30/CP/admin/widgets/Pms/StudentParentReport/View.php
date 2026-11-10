<?
class CP_Admin_Widgets_Pms_StudentParentReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $status  = $fn->getReqParam('status');
        $year  = $fn->getReqParam('year');
        $site_id = $fn->getReqParam('site_id');

        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $summaryRec = $this->model->getSqlForCount();

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='4'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Year : {$year}</td>
                    <td>Student Status : {$status}</td>
                    <td class='txtRight'>Total no of Data : {$summaryRec}</td>
                </tr>
            </table>

            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Branch</th>
                        <th>Reg No</th>
                        <th>Student Name</th>
                        <th>NRIC NO</th>
                        <th>DOB</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th>Parent Name</th>
                        <th>DDA</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $rows = '';
        $serial_no = 0;
        
        foreach($this->model->dataArray as $row) {
            $serial_no += 1;

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['branch_name']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$fn->getCPDate($row['date_of_birth'], 'd-M-Y')}</td>
                <td>{$row['age']}</td>
                <td>{$row['status']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['dda']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}