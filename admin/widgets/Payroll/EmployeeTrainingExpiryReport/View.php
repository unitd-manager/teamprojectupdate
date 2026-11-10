<?
class CPL_Admin_Widgets_Payroll_EmployeeTrainingExpiryReport_View extends CP_Admin_Widgets_Payroll_EmployeeTrainingExpiryReport_View
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';


        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='5' class='txtCenter'>Employee Training Expiry Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Training Title</th>
                        <th>Employee Name</th>
                        <th>From Date</th>
                        <th>To Date</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            </div>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){
            $from_date = $dateUtil->formatDate($row['from_date'], 'DD-MM-YYYY');
            $to_date = $dateUtil->formatDate($row['to_date'], 'DD-MM-YYYY');

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['training_title']}</td>
                <td>{$row['first_name']}</td>
                <td>{$from_date}</td>
                <td>{$to_date}</td>
            </tr>
            ";
            $counter++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}