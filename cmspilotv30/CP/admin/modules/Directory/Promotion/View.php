<?
class CP_Admin_Modules_Directory_Promotion_View extends CP_Common_Modules_Directory_Promotion_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['business_name'])}
            {$listObj->getListDataCell($row['record_type'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDateCell($row['start_date'])}
            {$listObj->getListDateCell($row['end_date'])}
            {$listObj->getListDataCell($row['start_time'])}
            {$listObj->getListDataCell($row['end_time'])}
            {$listObj->getListDataCell($row['custom_text'])}
            {$listObj->getListDataCell($row['promotion_id'], 'center')}
            {$listObj->getListRowEnd($row['promotion_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.business'), 'business_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.recordType'), 'p.record_type')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.promoTitle'), 'p.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.startDate'), 'p.start_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.endDate'), 'p.end_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.startTime'), 'p.start_time')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.endTime'), 'p.end_time')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.customText'), 'p.custom_text')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.promotion.lbl.id'), 'p.promotion_id', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.title'), 'title')}
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.customText'), 'custom_text')}
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
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        
        $formObj->mode = $tv['action'];

        $expBusiness = array('detailValue' => $row['business_name']);
        $modBusiness = getCPModuleObj('directory_business');
        $sqlBusiness = $modBusiness->model->getBusinessSQL();

        $expCard = array('detailValue' => $row['card_title']);
        $modCard = getCPModuleObj('directory_cards');
        $sqlCard = $modCard->model->getCardSQL();

        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.business'), 'business_id', $row['business_name'])}
        {$formObj->getDDRowByArr($ln->gd('m.directory.promotion.lbl.recordType'), 'record_type', $this->fns->getPromotionRecordTypeArray(), $row['record_type'])}
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.loyaltyCard'), 'card_title', $row['card_title'])}
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.title'), 'title', $row['title'])}
        {$formObj->getTBRow($ln->gd('m.directory.promotion.lbl.customText'), 'custom_text', $row['custom_text'])}
		";

        $fielset2  = "
        {$formObj->getDateRow($ln->gd('m.directory.promotion.lbl.startDate'), 'start_date', $row['start_date'])}
        {$formObj->getDateRow($ln->gd('m.directory.promotion.lbl.endDate'), 'end_date', $row['end_date'])}
        {$formObj->getTimeRow($ln->gd('m.directory.promotion.lbl.startTime'), 'start_time', $row['start_time'])}
        {$formObj->getTimeRow($ln->gd('m.directory.promotion.lbl.endTime'), 'end_time', $row['end_time'])}
        {$formObj->getDaysOfWeeksRow($ln->gd('m.directory.promotion.lbl.daysofWeek'), 'days_of_week', $row['days_of_week'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.promotion.lbl.mainDetails'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.promotion.lbl.otherDetails'), $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.promotion.link.picture'), 'directory_promotion', 'picture', $row)}
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');

        $business_id = $fn->getReqParam('business_id');
        $record_type = $fn->getReqParam('record_type');
        $card_id     = $fn->getReqParam('card_id');
        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');

        $SQLBusiness = "
        SELECT business_id
              ,business_name 
        FROM business 
        ORDER BY business_name
        ";

        $SQLCard = "
        SELECT card_id
              ,title 
        FROM cards 
        ORDER BY title
        ";
        
        $text = "
        <td class='fieldValue'>
            <select name='record_type'>
                <option value=''>{$ln->gd('m.directory.promotion.lbl.recordType')}</option>
                {$cpUtil->getDropDown1($this->fns->getPromotionRecordTypeArray(), $record_type)}
            </select>
        </td>
        <td>
            <select name='card_id'>
                <option value=''>{$ln->gd('m.directory.promotion.lbl.card')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLCard, $card_id)}
            </select>
        </td>    
        <td class='dateRange'>
            Start Date:
            <input type='text' allowEdit='1' name='start_date' class='fld_date' id='fld_start_date' value='{$start_date}' />
            End Date:
            <input type='text' allowEdit='1' name='end_date' class='fld_date' id='fld_end_date' value='{$end_date}' />
        </td>
        ";
        
        return $text;
    }
}