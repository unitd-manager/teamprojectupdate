<?
class CP_Admin_Modules_Logistics_Vehicle_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
	        {$listObj->getListDataCell($row['vehicle_code'])}
	        {$listObj->getListDataCell($row['vehicle_no'])}
	        {$listObj->getListDataCell($row['vehicle_model'])}
	        {$listObj->getListDataCell($row['make_year'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['vehicle_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Vehicle Code', 'v.vehicle_code')}
        {$listObj->getListHeaderCell('Vehicle No', 'v.vehicle_no')}
        {$listObj->getListHeaderCell('Vehicle Model', 'vehicle_model')}
        {$listObj->getListHeaderCell('Make/Year', 'make_year')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Vehicle No', 'vehicle_no')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);
        $sqlModel       = $fn->getValueListSQL('vehicleModel');
        $sqlStatus       = $fn->getValueListSQL('vehicleStatus');
       
       
        $sqlResouce = "
        SELECT resource_id
              ,resource_name 
        FROM resource
		WHERE published = 1 
        ";


        $fieldset1 = "
        {$formObj->getTBRow('Vehicle Code', 'vehicle_code', $row['vehicle_code'], $expNoEdit)}
        {$formObj->getTBRow('Vehicle No', 'vehicle_no', $row['vehicle_no'])}
        {$formObj->getTBRow('Bill To Vehicle', 'bill_to_vehicle', $row['bill_to_vehicle'])}
        {$formObj->getDDRowBySQL('Vehicle Model', 'vehicle_model', $sqlModel, $row['vehicle_model'], $expVl)}
        {$formObj->getDDRowBySQL('Driver', 'resource_id', $sqlResouce, $row['resource_id'])}        
        {$formObj->getTBRow('Make/Year', 'make_year', $row['make_year'])}
        {$formObj->getTBRow('Staff', 'staff', $row['staff'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
		";
		
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

		
        $text = "
        {$formObj->getFieldSetWrapped('Vehicle Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $record_id = $fn->getIssetParam($row, 'vehicle_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'logistics_vehicle', 'attachment', $row)}
            {$comment->getView(array(
             'roomName' => 'logistics_vehicle'
            ,'recordId' => $record_id
        ))}
    ";
        
        return $text;
    }
    
    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');
        $model        = $fn->getReqParam('model');

        $sqlStatus = $fn->getValueListSQL('vehicleStatus');
        $sqlModel     = $fn->getValueListSQL('vehicleModel');

        $text = "
        <td>
            <select name='model'>
                <option value=''>Vehicle Model</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlModel, $model)}
            </select>
        </td>    
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>    
        ";        
        
        return $text;
    }
}