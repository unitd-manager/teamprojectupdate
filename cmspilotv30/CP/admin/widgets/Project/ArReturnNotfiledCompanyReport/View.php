<?
class CP_Admin_Widgets_Project_ArReturnNotfiledCompanyReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $group_name = $fn->getReqParam('group_name');

        $prefix_month = $dateUtil->getLongMonthName($group_name);

        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th>AR Return not filed Report</th>
                </thead>
                <tr>
                    <td>Group Name : {$prefix_month} Year End</td>
                </tr>
            </table>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Company Name</th>
                        <th>Project Title</th>
                        <th>Task Title</th>
                        <th>Due Date</th>
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

        $rows      = '';
        $serial_no = 1;

        foreach($this->model->dataArray as $row){                             
            $date = $fn->getCPDate($row['due_date'],"d-m-Y");

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['project_title']}</td>
                <td>{$row['title']}</td>
                <td>{$date}</td>
            </tr>
            ";
            $serial_no += 1;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}