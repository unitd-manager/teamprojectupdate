<?
class CP_Admin_Widgets_AgileIms_SubsidyPaidHistoryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Subsidy Code</th>
                        <th>Enrollment Type</th>
                        <th>Company/Student Name</th>
                        <th>Status</th>
                        <th>Paid Date</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            
            $paid_date = $dateUtil->formatDate($row['paid_date'], 'DD-MM-YYYY');

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['subsidy_code']}</td>
                <td>{$row['enrollment_type']}</td>
                <td>{$row['name']}</td>
                <td>{$row['status']}</td>
                <td>{$paid_date}</td>
            </tr>
            ";
        }

        return $rows;
    }
}