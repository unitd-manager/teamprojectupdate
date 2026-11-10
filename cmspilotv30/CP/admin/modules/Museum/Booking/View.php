<?
class CP_Admin_Modules_Museum_Booking_View extends CP_Common_Modules_Museum_Booking_View
{
    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";
        $tagsGroupHeader = '';
        $tagsGroupValue = '';

        foreach ($dataArray as $row){
            $availability = $fn->getIssetParam($cpCfg['m.museum.booking.availabilityArr'], $row['availability']);
            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['facility_title'])}
            {$listObj->getListDataCell($row['date'])}
            {$listObj->getListDataCell($row['from_time'])}
            {$listObj->getListDataCell($row['to_time'])}
            {$listObj->getListDataCell($row['organisation'])}
            {$listObj->getListDataCell($availability)}
            {$listObj->getListDataCell($row['manual_booking'])}
            {$listObj->getListRowEnd($row['facility_id'])}
            ";

            $rowCounter++;
        }
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Facility', 'facility_title')}
    	{$listObj->getListHeaderCell('Date', 'b.date')}
    	{$listObj->getListHeaderCell('From', 'b.from_time')}
    	{$listObj->getListHeaderCell('To', 'b.to_time')}
    	{$listObj->getListHeaderCell('Organisation', 'b.organisation')}
    	{$listObj->getListHeaderCell('Availability', 'b.availability')}
    	{$listObj->getListHeaderCell('Manual Booking', 'b.manual_booking')}
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
        $fn = Zend_Registry::get('fn');

        $sqlFacility = $fn->getDDSql('museum_facility');

        $fieldset = "
        {$formObj->getDDRowBySQL('Facility', 'facility_id', $sqlFacility)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];               
        $expFacility = array('detailValue' => $row['facility_title']);
        $sqlFacility = $fn->getDDSql('museum_facility');

        $modVl = getCPModuleObj('core_valuelist');
        $expVl = array('sqlType' => 'TwoFields');
        $sqlOrganisationType = $modVl->model->getValuelistSQL('organisationType', array('useEngValueAsKey' => 1 ));
        $sqlVisitType = $modVl->model->getValuelistSQL('visitType', array('useEngValueAsKey' => 1 ));

        $fieldset1  = "
        {$formObj->getDDRowBySQL('Facility', 'facility_id', $sqlFacility, $row['facility_id'], $expFacility)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTimeRow('From', 'from_time', $row['from_time'])}
        {$formObj->getTimeRow('To', 'to_time', $row['to_time'])}
        {$formObj->getDDRowByArr('Availability', 'availability', $cpCfg['m.museum.booking.availabilityArr'], $row['availability'], array('useKey' => 1))}
        ";

        $fieldset2  = "
        {$formObj->getTBRow('First name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Organisation', 'organisation', $row['organisation'])}
        {$formObj->getDDRowBySQL('Organisation type', 'organisation_type', $sqlOrganisationType, $row['organisation_type'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        ";

        $fieldset3  = "
        {$formObj->getTBRow('Total number of visitors', 'number_of_visitor', $row['number_of_visitor'])}
        {$formObj->getTBRow('Number of students', 'number_of_students', $row['number_of_students'])}
        {$formObj->getTBRow('Number of adults', 'number_of_adults', $row['number_of_adults'])}
        {$formObj->getTBRow('Number of elderly', 'number_of_elderly', $row['number_of_elderly'])}
        {$formObj->getTBRow('Number of youth', 'number_of_youth', $row['number_of_youth'])}
        {$formObj->getTBRow('Group leader', 'group_leader', $row['group_leader'])}
        {$formObj->getTBRow('Group leader mobile', 'group_leader_mobile', $row['group_leader_mobile'])}
        {$formObj->getYesNoRRow('Would you like to prearrange a visit?', 'pre_visit', $row['pre_visit'])}
        {$formObj->getDateRow('Pre visit date', 'date_pre_visit', $row['date_pre_visit'])}
        {$formObj->getTARow('Special disability requirements? ', 'disability_requirement', $row['disability_requirement'])}
        {$formObj->getDDRowBySQL(' Type of Visit', 'visit_type', $sqlVisitType, $row['visit_type'])}
        {$formObj->getTARow('Comments', 'comments', $row['comments'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('', $fieldset2)} 
        {$formObj->getFieldSetWrapped('', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'museum_booking', 'attachment', $row)}
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

        $facility_id = $fn->getReqParam('facility_id');

        $sqlFacility = $fn->getDDSQL('museum_facility');

        $text = "
        <td>
            <select name='facility_id'>
                <option value=''>Facility</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlFacility, $facility_id)}
            </select>
        </td>
        ";

        return $text;
    }
    
}