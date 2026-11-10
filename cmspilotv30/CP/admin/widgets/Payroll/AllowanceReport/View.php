<?
class CP_Admin_Widgets_Payroll_AllowanceReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';


        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Allowance Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr> 
                        <th>S.No</th>
                        <th>Name</th>
                        <th>NRIC/Fin No</th>
                        <th class='txtRight'>{$cpCfg['m.jobInformation.allowance1Lbl']}</th>
                        <th class='txtRight'>{$cpCfg['m.jobInformation.allowance2Lbl']}</th>
                        <th class='txtRight'>{$cpCfg['m.jobInformation.allowance3Lbl']}</th>
                        <th class='txtRight'>{$cpCfg['m.jobInformation.allowance4Lbl']}</th>
                        <th class='txtRight'>{$cpCfg['m.jobInformation.allowance5Lbl']}</th>
                        <th class='txtRight'>Total Allowance</th>
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
        $allowance1Total = 0;
        $allowance2Total = 0;
        $allowance3Total = 0;
        $allowance4Total = 0;
        $allowance5Total = 0;
        $overallTotal = 0;
        foreach($this->model->dataArray as $row){

            if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $finNo = $row['nric_no'];
            }else {
                $finNo = $row['fin_no'] ;
            }

            $total_allowance = $row['allowance1'] + $row['allowance2'] +$row['allowance3'] +$row['allowance4'] +$row['allowance5'];
            //$total_allowance = $row['allowance1'] + $row['allowance2'] +$row['allowance3'];
            $allowance1Total += $row['allowance1'];
            $allowance2Total += $row['allowance2'];
            $allowance3Total += $row['allowance3'];
            $allowance4Total += $row['allowance4'];
            $allowance5Total += $row['allowance5'];
            $overallTotal += $total_allowance;

            $allowance1 = number_format($row['allowance1'], 2);
            $allowance2 = number_format($row['allowance2'], 2);
            $allowance3 = number_format($row['allowance3'], 2);
            $allowance4 = number_format($row['allowance4'], 2);
            $allowance5 = number_format($row['allowance5'], 2);
            $total_allowance = number_format($total_allowance, 2);

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['first_name']}</td>
                <td>{$finNo}</td>
                <td class='txtRight'>{$allowance1}</td>
                <td class='txtRight'>{$allowance2}</td>
                <td class='txtRight'>{$allowance3}</td>
                <td class='txtRight'>{$allowance4}</td>
                <td class='txtRight'>{$allowance5}</td>
                <td class='txtRight'>{$total_allowance}</td>
            </tr>
            ";
            $counter++;
        }
        
        $allowance1Total = number_format($allowance1Total, 2);
        $allowance2Total = number_format($allowance2Total, 2);
        $allowance3Total = number_format($allowance3Total, 2);
        $allowance4Total = number_format($allowance4Total, 2);
        $allowance5Total = number_format($allowance5Total, 2);
        $overallTotal    = number_format($overallTotal, 2);

        $text = "
        {$rows}
        <tr>
            <td class='txtRight' colspan='3'><b>TOTAL</b></td>
            <td class='txtRight'><b>{$allowance1Total}</b></td>
            <td class='txtRight'><b>{$allowance2Total}</b></td>
            <td class='txtRight'><b>{$allowance3Total}</b></td>
            <td class='txtRight'><b>{$allowance4Total}</b></td>
            <td class='txtRight'><b>{$allowance5Total}</b></td>
            <td class='txtRight'><b>{$overallTotal}</b></td>
        </tr>
        ";

        return $text;
    }
}