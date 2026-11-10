<?
class CP_Admin_Widgets_Logistics_ListOfDriversIdle_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>List of Drivers Idle</h2>
			<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Resource Code</th>
						<th>Resource Name</th>
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
        
        $rows = '';
        $class = '';
        foreach($this->model->dataArray as $row){                            

            $rows .= "
			<tr class='{$class}'>
				<td>{$row['resource_code']}</td>
				<td>{$row['resource_name']}</td>
			</tr>
			";                
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}