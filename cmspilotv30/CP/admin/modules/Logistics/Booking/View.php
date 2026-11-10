<?
class CP_Admin_Modules_Logistics_Booking_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;
        

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $booking_date = $fn->getCPDate($row['booking_date'], 'd-m-Y');
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['booking_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['booking_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['booking_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Booking Code', 'booking_code')}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Company Name', 'company_name')}
        {$listObj->getListHeaderCell('Booking Date', 'booking_date')}
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
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $sqlStatus = $fn->getValueListSQL('bookingStatus');
        $sqlPriority = $fn->getValueListSQL('bookingPriority');
        $sqlType = $fn->getValueListSQL('bookingType');
        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);

        $sqlContact = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name ) AS contact_name 
        FROM contact
        ";

        $sqlCompany = "
        SELECT company_id
              ,company_name 
        FROM company
        ";
      
        $fielset1 = "
        {$formObj->getTBRow('Booking Code', 'booking_code', $row['booking_code'], $expNoEdit)}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $row['company_id'])}        
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id', $sqlContact, $row['contact_id'])}        
        {$formObj->getDateRow('Booking Date', 'booking_date', $row['booking_date'])}
        {$formObj->getDDRowBySQL('Booking Type', 'booking_type', $sqlType, $row['booking_type'], $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}
				";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";
		
        $text = "
        {$formObj->getFieldSetWrapped('Booking Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'booking_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'logistics_booking', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('logistics_booking', 'logistics_resourceLink', 'Resources Linked', $row)}
        {$displayLinkData->getLinkPortalMain('logistics_booking', 'logistics_vehicleLink', 'Vehicles Linked', $row)}
        {$comment->getView(array(
             'roomName' => 'logistics_booking'
            ,'recordId' => $record_id
            ,'allowEdit' => false
        ))}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $booking_type        = $fn->getReqParam('booking_type');
        $sqlType     = $fn->getValueListSQL('bookingType');
        $bookingDate1        = $fn->getReqParam('bookingDate1');
        $bookingDate2        = $fn->getReqParam('bookingDate2');
        $yearEnd = date('Y') + 10;

		if ($bookingDate1 == ''){
			$bookingDate1 = 'From';
		}

		if ($bookingDate2 == ''){
			$bookingDate2 = 'To';
		}

        $text = "
        <td>
            <select name='booking_type'>
                <option value=''>Booking Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlType, $booking_type)}
            </select>
        </td>    
        <td class='dateRange'>
            Booking Date:
            <input type='text' allowEdit='1' name='bookingDate1' class='fld_date' 
                   id='fld_callRegdate1' value='{$bookingDate1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='bookingDate2' class='fld_date' 
                   id='fld_callRegdate2' value='{$bookingDate2}' yearEnd='{$yearEnd}' />
        </td>
        ";        
        
        return $text;
    }
}