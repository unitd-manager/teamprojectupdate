<?
class CP_Admin_Widgets_Pms_TraineeByMonth_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Month</th>
                        <th>Number of Students</th>
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
        $rows = '';

        foreach($this->model->dataArray as $row){
            
            switch ($row['invoice_month']) {
                case 1: $month = 'January';
                break;
                case 2: $month = 'February';
                break;
                case 3: $month = 'March';
                break;
                case 4: $month = 'April';
                break;
                case 5: $month = 'May';
                break;
                case 6: $month = 'June';
                break;
                case 7: $month = 'July';
                break;
                case 8: $month = 'August';
                break;
                case 9: $month = 'September';
                break;
                case 10: $month = 'October';
                break;
                case 11: $month = 'November';
                break;
                case 12: $month = 'December';
                break;
            }

            $rows .= "
            <tr>
                <td>{$month}</td>
                <td>{$row['attendee_count']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}