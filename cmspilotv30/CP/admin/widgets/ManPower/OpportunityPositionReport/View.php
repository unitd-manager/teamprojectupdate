<?
class CP_Admin_Widgets_ManPower_OpportunityPositionReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Serial No</th>
                        <th>Position</th>
                        <th>Month</th>
                        <th>No. of Oppurtunity</th>
                        <th>No. of Position</th>
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
        $fn  = Zend_Registry::get('fn');
        $db  = Zend_Registry::get('db');

        $rows         = '';
        $serial_no    = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $creation_date = $fn->getCPDate($row['creation_date'], 'M');

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['position']}</td>
                <td>{$creation_date}</td>
                <td>{$row['no_of_oppurtunity']}</td>
                <td>{$row['no_of_positions']}</td>
            </tr>
            ";

        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}