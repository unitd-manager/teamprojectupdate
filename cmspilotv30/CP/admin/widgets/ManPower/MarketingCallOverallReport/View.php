<?
class CP_Admin_Widgets_ManPower_MarketingCallOverallReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Status</th>
                        <th>No of Records</th>
                        <th>Month</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $serial_no = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            
            $month = $fn->getReqParam('month');
            $year  = $fn->getReqParam('year');
        
            $sqlAppend = $this->model->getCountValueForMonth($year, $month);
        
            $sqlCount = "
            SELECT COUNT(*) AS total_count_status FROM call_registry cr
            WHERE status = '{$row['status']}'
                  {$sqlAppend}
            ";
            $resultCount = $db->sql_query($sqlCount);
            $rowCount    = $db->sql_fetchrow($resultCount);
            
            if ($month == '') {
                $month_val = $current_month = date('F'); 
            } else {
                switch ($month) {
                    case '01': $month_val = 'January';
                    break;
    
                    case '02': $month_val = 'February';
                    break;
    
                    case '03': $month_val = 'March';
                    break;
    
                    case '04': $month_val = 'April';
                    break;
    
                    case '05': $month_val = 'May';
                    break;
    
                    case '06': $month_val = 'June';
                    break;
    
                    case '07': $month_val = 'July';
                    break;
    
                    case '08': $month_val = 'August';
                    break;
    
                    case '09': $month_val = 'September';
                    break;
    
                    case '10': $month_val = 'October';
                    break;
    
                    case '11': $month_val = 'November';
                    break;
    
                    case '12': $month_val = 'December';
                    break;
                }
            }
            
            if ($rowCount['total_count_status']) {
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['status']}</td>
                    <td>{$rowCount['total_count_status']}</td>
                    <td>{$month_val}</td>
                </tr>
                ";
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}