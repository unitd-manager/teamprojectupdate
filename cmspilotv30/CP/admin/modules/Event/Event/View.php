<?
class CP_Admin_Modules_Event_Event_View extends CP_Common_Modules_Event_Event_View
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $event_type = '';
            if ($cpCfg['m.event.event.showEventType']){
                $event_type = $listObj->getListDataCell($row['content_type']. '');
            }

            $event_end_date = '';
            if ($cpCfg['m.event.event.showEventEndDate']){
                $event_end_date = $listObj->getListDateCell($row['event_end_date']);
            }

            $sectionCol = '';
            if ($cpCfg['m.event.event.hasSection']) {
                $sectionCol = $listObj->getListDataCell($row['section_title']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$sectionCol}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListSortOrderField($row, 'event_id')}
            {$listObj->getListDataCell($row['event_venue'])}
            {$listObj->getListDateCell($row['event_date'])}
            {$event_end_date}
            {$event_type}
            {$listObj->getListDataCell($row['event_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['event_id'])}
            {$listObj->getListRowEnd($row['event_id'])}
            ";
            $rowCounter++;
        }

        $event_type = '';
        if ($cpCfg['m.event.event.showEventType']){
            $event_type = $listObj->getListHeaderCell('Content Type', 'e.content_type');
        }

        $event_end_date = '';
        if ($cpCfg['m.event.event.showEventEndDate']){
            $event_end_date = $listObj->getListHeaderCell($ln->gd('m.event.header.event.lbl.endDate', 'End Date'), 'e.event_end_date');
        }

        $sectionCol = '';
        if ($cpCfg['m.event.event.hasSection']) {
            $sectionCol = $listObj->getListHeaderCell('Section', 's.title');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.title', 'Title'), 'e.title')}
        {$sectionCol}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.category', 'Category'), 'category_title')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.subCategory', 'Sub Category'), 'sub_category_title')}
        {$listObj->getListSortOrderImage('e')}
        {$listObj->getListHeaderCell($ln->gd('m.event.header.event.lbl.location', 'Location'), 'e.event_venue')}
        {$listObj->getListHeaderCell($ln->gd('m.event.header.event.lbl.startDate', 'Start Date'), 'e.event_date')}
        {$event_end_date}
        {$event_type}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.id', 'ID'), 'e.event_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.published', 'Published'), 'e.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }


    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('cp.lbl.title', 'Title'), 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {

        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $expCat    = array('detailValue' => $row['category_title']);
        $expSubCat = array('detailValue' => $row['sub_category_title']);

        $modCat = getCPModuleObj('webBasic_category');
        $modSubCat = getCPModuleObj('webBasic_subCategory');

        $sqlCategory = '';
        $sectionRow = '';
        if ($cpCfg['m.event.event.hasSection']) {
            $modSec = getCPModuleObj('webBasic_section');
            $sqlSection = $modSec->model->getSectionSQL();
            $expSection = array('detailValue' => $row['section_title']);
            $sectionRow = $formObj->getDDRowBySQL($ln->gd('m.event.event.lbl.section', 'Section'), 'section_id', $sqlSection, $row['section_id'], $expSection);
            if ($row['section_id'] != ''){
                $sqlCategory = $modCat->model->getCategorySQLBySection($row['section_id']);
            }
        } else {
            $sqlCategory = $modCat->model->getCategorySQLByType("Event", "Event");
        }

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }

        $metaData         = '';
        if ($cpCfg['m.event.event.showMetaData']) {
            $metaData = $formObj->getMetaData($row);
        }

        if ($row['content_type'] == ''){
            $content_type = 'Daily Calender';
        } else {
            $content_type = $row['content_type'];
        }

        $event_type = '';
        if ($cpCfg['m.event.event.showEventType']){
            $event_type = $formObj->getDDRowByArr('Event Type', 'content_type', $cpCfg['m.event.event.recordTypeArr'], $content_type, '');
        }

        $event_end_date = '';
        if ($cpCfg['m.event.event.showEventEndDate']){
            $event_end_date = $formObj->getDateRow('End Date', 'event_end_date', $row['event_end_date']);
        }

        $holiday = '';
        if ($cpCfg['m.event.event.showIsHoliday']){
            $holiday = $formObj->getYesNoRRow('Is Holiday', 'is_holiday', $row['is_holiday'] );
        }

        $speaker = '';
        if ($cpCfg['m.event.event.showSpeaker']){
            $speaker = $formObj->getYesNoRRow('Show Speaker', 'show_speaker', $row['show_speaker'] );
        }
        $eventItem = '';
        if ($cpCfg['m.event.event.showEventItem']){
            $eventItem = $formObj->getYesNoRRow('Show Event Item', 'show_event_item', $row['show_event_item'] );
        }

        $registeration = '';
        if ($cpCfg['m.event.event.showRegisteration']){
            $registeration = $formObj->getYesNoRRow('Show Registration Form', 'show_registration', $row['show_registration'] );
        }

        $freeEvent = '';
        if ($cpCfg['m.event.event.showFreeEventRadio']){
            $freeEvent = $formObj->getYesNoRRow('Free', 'free', $row['free'] );
        }

        $showInWeb = '';
        if ($cpCfg['m.event.event.showOkForWeb']){
            $showInWeb = $formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web']);
        }

        $showInMobile = '';
        if ($cpCfg['m.event.event.showOkForMobile']){
            $showInMobile = $formObj->getYesNoRRow('OK for Mobile', 'ok_for_mobile', $row['ok_for_mobile']);
        }

        $availableSeats = '';
        if ($cpCfg['m.event.event.showAvailableSeats']){
            $availableSeats = $formObj->getTBRow('Available Seats', 'available_seats', $row['available_seats']);
        }

        $regEndDate = '';
        if ($cpCfg['m.event.event.showEventRegEndDate']){
            $regEndDate = $formObj->getDateRow('Registration End Date', 'reg_end_date', $row['reg_end_date']);
        }

        $recurringEvents = '';
        if ($cpCfg['m.event.event.hasRecurringEvents']){

            $expRepeatEvery = array('notesRight' => 'Days', 'fieldCls' => 'w100');
            $repeatEveryArr = array();

            if($row['repeat_type'] == "Daily"){
                $repeatEveryArr = $cpCfg['m.event.event.repeatDaysArr'];
                $expRepeatEvery = array('notesRight' => 'Days', 'fieldCls' => 'w100');
            } else if($row['repeat_type'] == "Weekly"){
                $expRepeatEvery = array('notesRight' => 'Weeks', 'fieldCls' => 'w100');
                $repeatEveryArr = $cpCfg['m.event.event.repeatWeeksArr'];
            } else if($row['repeat_type'] == "Monthly"){
                $expRepeatEvery = array('notesRight' => 'Months', 'fieldCls' => 'w100');
                $repeatEveryArr = $cpCfg['m.event.event.repeatMonthsArr'];
            } else if($row['repeat_type'] == "Yearly"){
                $expRepeatEvery = array('notesRight' => 'Years', 'fieldCls' => 'w100');
                $repeatEveryArr = $cpCfg['m.event.event.repeatYearArr'];
            }

            $recurringEvents= "
            {$formObj->getDDRowByArr('Repeat Type', 'repeat_type', $cpCfg['m.event.event.repeatTypeArr'], $row['repeat_type'])}
            <div id='repeatEvery'>
                {$formObj->getDDRowByArr('Repeat Every', 'repeat_every', $repeatEveryArr, $row['repeat_every'], $expRepeatEvery)}
            </div>
            <div id='repeatWeekly'>
                {$formObj->getSingleCheckBoxRow('Sun', 'repeat_on_sun', $row['repeat_on_sun'], '')}
                {$formObj->getSingleCheckBoxRow('Mon', 'repeat_on_mon', $row['repeat_on_mon'], '')}
                {$formObj->getSingleCheckBoxRow('Tue', 'repeat_on_tue', $row['repeat_on_tue'], '')}
                {$formObj->getSingleCheckBoxRow('Wed', 'repeat_on_wed', $row['repeat_on_wed'], '')}
                {$formObj->getSingleCheckBoxRow('Thu', 'repeat_on_thu', $row['repeat_on_thu'], '')}
                {$formObj->getSingleCheckBoxRow('Fri', 'repeat_on_fri', $row['repeat_on_fri'], '')}
                {$formObj->getSingleCheckBoxRow('Sat', 'repeat_on_sat', $row['repeat_on_sat'], '')}
            </div>
            <div id='repeatMonthly'>
                {$formObj->getRadioArrRow('Repeat By', 'repeat_month_by', $row['repeat_month_by'], $cpCfg['m.event.event.repeatMonthByArr'])}
            </div>
            ";
        }


        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$sectionRow}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCat)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCat)}
        {$formObj->getDateRow('Start Date', 'event_date', $row['event_date'])}
        {$event_end_date}
        {$holiday}
        {$formObj->getTBRow('Date (Display)', 'event_date_text', $ln->gfv($row, 'event_date_text', '0'))}
        {$formObj->getTBRow('Event Time', 'event_time', $ln->gfv($row, 'event_time', '0'))}
        {$event_type}
        {$recurringEvents}
        {$availableSeats}
        {$regEndDate}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Contact No', 'contact_no', $row['contact_no'] )}
        {$formObj->getTBRow('Venue', 'event_venue', $ln->gfv($row, 'event_venue', '0'))}
        {$formObj->getTARow('Speaker', 'speaker', $ln->gfv($row, 'speaker', '0'))}
        {$formObj->getTBRow('External Link', 'external_link', $ln->gfv($row, 'external_link', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )}
        {$formObj->getYesNoRRow('Latest', 'latest', $row['latest'] )}
        {$showInWeb}
        {$showInMobile}
        {$freeEvent}
        {$speaker}
        {$eventItem}
        {$registeration}
        {$formObj->getTARow('Description Short', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        ";

        $fieldset3 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Description', $fieldset3)}
        {$metaData}
        {$formObj->getCreationModificationText($row)}

        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $links = "";

        if ($cpCfg['cp.hasMultiSites']) {
            $links .= $displayLinkData->getLinkPortalMain('event_event', 'common_siteLink', 'Sites Linked', $row);
        }

        if($cpCfg['m.event.event.showContact']){
            $links .= $displayLinkData->getLinkPortalMain("event_event", "common_contactLink", "Contacts Linked", $row);
        }

        if($cpCfg['m.event.event.showEventItem']){
            $links .= $displayLinkData->getLinkPortalMain("event_event", "event_eventItemLink", "Event Item Linked", $row);
        }

        if ($cpCfg['m.event.event.showSponsorsLogo']) {
            $links .= $media->getRightPanelMediaDisplay('Sponsors', 'event_event', 'sponsorsPicture', $row);
        }

        if ($cpCfg['m.event.event.showRelatedPicture']) {
            $links .= $media->getRightPanelMediaDisplay($ln->gd('cp.lbl.relatedPicture', 'Related Picture'), 'event_event', 'relatedPicture', $row);
        }

        if($cpCfg['m.event.event.showTagsLink']){
            $links .= $displayLinkData->getLinkPortalMain("event_event", "web2_tagsLink", "Tags Linked", $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('cp.lbl.picture', 'Picture'), 'event_event', 'picture', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('cp.lbl.attachment', 'Attachments'), 'event_event', 'attachment', $row)}
        {$links}
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $special_search  = $fn->getReqParam('special_search');
        $content_type    = $fn->getReqParam('content_type');

        //==================================================================//
        $modCat = getCPModuleObj('webBasic_category');
        $sectionText ='';
        $SQLCat ='';
        if ($cpCfg['m.event.event.hasSection']) {
            $modSec = getCPModuleObj('webBasic_section');
            $sqlSection = $modSec->model->getSectionSQL();
            $sectionText = "
            <td class='fieldValue'>
                <select name='section_id'>
                    <option value=''>Section</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSection, $tv['section_id'])}
                </select>
            </td>";

            if ($tv['section_id'] != "") {
                $modCat = getCPModuleObj('webBasic_category');
                $SQLCat = $modCat->model->getCategorySQL($tv['section_id']);

            }
        } else {
            $SQLCat = $modCat->model->getCategorySQLByType('Event');
        }
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        $subCatOptions = '';
        if ($tv['category_id'] != "") {
            $SQLSubCat = $fn->getDDSql('webBasic_subCategory', array('condn' => "category_id = {$tv['category_id']}"));
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $tv['sub_category_id']);
        }

        $event_type = '';
        if ($cpCfg['m.event.event.showEventType']){
            $event_type = "
            <td class='fieldValue'>
                <select name='content_type'>
                    <option value=''>Content Type</option>
                    {$cpUtil->getDropDown1($cpCfg['m.event.event.recordTypeArr'], $content_type)}
                </select>
            </td>
            ";
        }

        $site = '';
        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');
            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td class='fieldValue'>
                <select name='site_id'>
                    <option value=''>Site</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        $text = "
        {$sectionText}
        <td>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='sub_category_id' >
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
        </td>
        {$event_type}
        {$site}
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.webBasic.content.specialSearchArr'], $special_search)}
            </select>
        </td>
        ";


        return $text;
    }
}