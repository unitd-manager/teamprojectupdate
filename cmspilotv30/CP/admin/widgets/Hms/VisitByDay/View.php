<?
class CP_Admin_Widgets_Hms_VisitByDay_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db	= Zend_Registry::get('db');
        $fn	= Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <h2><strong>Visit By Day</strong></h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Day</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
		</div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
		$count = 1;

        foreach($this->model->dataArray as $row){
			$check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

		    $rows .= "
			<tr>
				<td>{$check_up_date}</td>
				<td>{$row['day']}</td>
				<td>{$row['patients_visited']}</td>
			</tr>
			";
			$count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}