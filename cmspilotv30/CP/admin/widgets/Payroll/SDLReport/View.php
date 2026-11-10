<?
class CP_Admin_Widgets_Payroll_SDLReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                    <th colspan='6'>SDL Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr> 
                        <th>S.No</th>
                        <th>Employee Name</th>
                        <th>NRIC/Fin No</th>
                        <th class='txtRight'>SDL Paid</th>
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
        $sdl_total = 0;
        foreach($this->model->dataArray as $row){
            if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $finNo = $row['nric_no'];
            }else {
                $finNo = $row['fin_no'] ;
            }

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['first_name']}</td>
                <td>{$finNo}</td>
                <td class='txtRight'>{$row['sdl']}</td>
            </tr>
            ";
            $counter++;
            $sdl_total += $row['sdl'];
        }
        
        $text = "
        {$rows}
        <tr>
            <td colspan='3' class='txtRight'><b>TOTAL</b></td>
            <td class='txtRight'><b>{$sdl_total}</b></td>
        </tr>
        ";

        return $text;
    }
}