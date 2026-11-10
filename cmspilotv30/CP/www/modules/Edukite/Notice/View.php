<?
class CP_Www_Modules_Edukite_Notice_View extends CP_Common_Modules_Edukite_Notice_View
{
    /**
     *
     */
    function getList($dataArray){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');
        $searchHTML = Zend_Registry::get('searchHTML');

        $status = $fn->getReqParam('status');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $launchStatus = '';

			if($row['launch_now'] == 1){
                $launchStatus = "<div class='txtRight'><img src='/cmspilotv30/CP/www/themes/Manager/images/launch-status.png'></div>";
            } else {
                $launchStatus = "<div class='txtRight'><img src='/cmspilotv30/CP/www/themes/Manager/images/not-launch-status.png'></div>";
            }

			if($row['parent_feedback'] == 1){
                $editIcon = "
                <a href='/controller/notice/edit/{$row['notice_id']}/?status={$status}' class='editIcon'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/feedback.png'>
                </a>
                ";
            } else {
                $editIcon = "
                <a href='/controller/notice/edit/{$row['notice_id']}/?status={$status}' class='editIcon'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/notice-edit.png'>
                </a>
                ";
            }

            $exp = array('class' => 'teacherName');

			$noticeAchievement = '';
	        if($cpCfg['showAcheivement'] == 1){
		        $SQL = "
		        SELECT sa.*
		        FROM achievement_student sa
	        	WHERE sa.notice_id = {$row['notice_id']}
	        	";
	        	$result  = $db->sql_query($SQL);
	            $numRows = $db->sql_numrows($result);

	            if ($numRows) {
                    $achievemnentUrl = '/'. "controller/notice/?_action=achievementPanel&notice_id={$row['notice_id']}";
    				$noticeAchievement = "
    				<td align='right'>
    				    <a href='{$achievemnentUrl}'>
	                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
	                    </a>
    				</td>
    				";
				} else {
	                $noticeAchievement = "
					<td></td>
					";
				}

			} else {
                $noticeAchievement = "
				<td></td>
				";
			}

            $SQLComment = "
            SELECT *
            FROM comment
            WHERE record_id = {$row['notice_id']}
            AND contact_id > 0
            ";
            $resultComment  = $db->sql_query($SQLComment);
            $numRowsComment = $db->sql_numrows($resultComment);

            $hostName   = $_SERVER['HTTP_HOST'];

            if ($numRowsComment) {
                //if(strpos($hostName, 'edukitedev') !== false){
                    $star = "
                    <td align='right'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                    </td>
                    ";
                //}
            } else {
                $star = "
                <td></td>
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            <td>$editIcon</td>
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListDataCell($row['teacher_name'], '', '', '', $exp)}
			{$noticeAchievement}
            {$star}
            {$listObj->getListDataCell($launchStatus)}
            ";
            $rowCounter++ ;
        }

        $statusLink = '';
        $archiveLinkUrl = '?status=Archive';
        $activeLinkUrl = '?status=Active';
        if($cpCfg['cp.schoolEnrolledCurrentYear'] != 1){
            if($status != 'Archive'){
                $statusLink = "<a href='{$archiveLinkUrl}' id='archiveLink' class='archive'>View Archive Records</a>";
            } else {
                $statusLink = "<a href='{$activeLinkUrl}' id='archiveLink' class='active'>View Active Records</a>";
            }
        }

        $text = "
        <div class='noticeList'>
            <div class='archiveLink'>{$statusLink}</div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell("<img src='/cmspilotv30/CP/www/themes/Manager/images/feedback.png'>", 'n.parent_feedback')}
            {$listObj->getListHeaderCell('Name', 'n.title')}
            <th></th>
            <th></th>
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderCell("<div class='txtRight'><img src='/cmspilotv30/CP/www/themes/Manager/images/launch-status.png'></div>", 'n.launch_now')}
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $template = $fn->getReqParam('template');

        $fa = $this->model->getFields();
        $fa['academic_year'] = date('Y');
        $fa['status'] = 'Active';
        $fa['teacher_id'] = $_SESSION['cpContactId'];
        $fa['launch_date'] = date('Y-m-d');
        $fa['activity_date'] = date('Y-m-d');

        if ($template =='kitePost') {
            $fa['template'] = 'Kite Post';
        } else if ($template =='dailyDiary') {
            $fa['template'] = 'Daily Diary';
        } else if ($template =='gallery') {
            $fa['template'] = 'Gallery';
        } else if ($template =='kitePostLeft'){
            $fa['template'] = 'Kite Post Left';
        } else if ($template =='task'){
            $fa['template'] = 'Task';
        }

        $id = $fn->addRecord($fa);
        $cpUtil->redirect("/controller/notice/?_action=edit&notice_id={$id}");
        return;

        $template = $fn->getReqParam('template');

        $noticeTypeArr = array(
            "Home Work"
           ,"Project"
        );

        $sqlSubject = "
        SELECT s.subject_id, s.title
        FROM subject s
        WHERE s.published = 1
        ORDER BY s.title
        ";

        $expSubject = array('firstOptionLabel' => 'Learning Area');
        $expNoticeType = array('firstOptionLabel' => 'Notice Type');

        $fieldset = "
        <div class='floatbox ym-contain-dt filter'>
            <div class='float_left'>
                {$formObj->getDDRowByArr('Notice Type', 'notice_type', $noticeTypeArr, '', $expNoticeType)}
            </div>
            <div class='float_left'>
                {$formObj->getDDRowBySQL('Learning Area', 'subject_id', $sqlSubject, '', $expSubject)}
            </div>
        </div>
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('', $fieldset)}
        <input type='hidden' name='template' value='{$template}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
        $template  = $fn->getReqParam('template');
        $statusUrl = $fn->getReqParam('status');

        $spArray = array();
        $templateArr = array(
            "Kite Post"
           ,"Daily Diary"
           ,"Gallery"
        );

        $sqlNoticeType = "
        SELECT nt.notice_type_id, nt.title
        FROM notice_type nt
        ORDER BY nt.title
        ";

        $sqlSubject = "
        SELECT s.subject_id, s.title
        FROM subject s
        WHERE s.published = 1
        ORDER BY s.title
        ";

        if ($row['template'] == 'Daily Diary' || $row['template'] == 'Kite Post Left') {
            $notes = 'Left Column';
        } else if ($row['template'] == 'Kite Post') {
            $notes = 'Centre Column';
        } else if ($row['template'] == 'Gallery') {
            $notes = 'Right Column';
        } else if ($row['template'] == 'Task') {
            $notes = 'Centre Column';
        }

        $expSubject = array('firstOptionLabel' => 'Subject', 'fieldCls' => $row['notice_id']);
        $expNoticeType = array('firstOptionLabel' => 'Type');
        $expNoEdit = array('isEditable' => 0);
        $expTeacher = array('isEditable' => 0, 'notesRight' => $notes);
        if($statusUrl == 'Archive'){
            $expNoticeId = array('isEditable' => 0, 'fldId' => $row['notice_id']);
            $expNotes  = array('isEditable' => 0, 'rowCls' => 'textAreaDiv', 'fieldCls' => $row['notice_id']);
            $expDate  = array('isEditable' => 0, 'dateFormat' => 'dd-mm-yy', 'fldId' => $row['notice_id']);
        } else {
            $expNoticeId = array('fldId' => $row['notice_id']);
            $expNotes  = array('rowCls' => 'textAreaDiv', 'fieldCls' => $row['notice_id']);
            $expDate  = array('dateFormat' => 'dd-mm-yy', 'fldId' => $row['notice_id']);
        }

        $activityDate = $fn->getCPDate($row['activity_date'], 'd-m-Y');
        $expiryDate   = $fn->getCPDate($row['expiry_date'], 'd-m-Y');
        $launchDate   = $fn->getCPDate($row['launch_date'], 'd-m-Y');

        $noticeCenterPanel ='';
        $filter = '';
        $alert = '';
        $launch_date = '';
        $expiry_date = '';
        $activity_date = '';
        $goToUpload = '';
        $homeWorkChat = '';

        if ($row['template'] == 'Task') {
            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Task');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $teacherKiteId;
            $urlArray['record_id'] = $row['notice_id'];
            $urlArray['record_title'] = $row['title'];
            $goToUploadLink = $cpUrl->make_seo_url($urlArray);

            $goToUpload ="
            <div class='taskLinkBtn'>
                <a href='{$goToUploadLink}?teacherKite=1'><img src='/www/themes/Kite/images/upload_button.png'></a>
            </div>
            ";
        }

        if ($row['template'] == 'Kite Post' || $row['template'] == 'Kite Post Left' || $row['template'] == 'Task') {
            $noticeCenterPanel ="
            <div class='centerPanel'><img src='/cmspilotv30/CP/www/themes/Manager/images/notice-center-panel.png'></div>
            ";

            $filter ="
            <div class='floatbox ym-contain-dt filter'>
                <div class='float_left'>
                    {$formObj->getDDRowBySQL('Subject', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
                </div>
                <!--<div class='float_left'>
                    {$formObj->getDDRowBySQL('Type', 'notice_type_id', $sqlNoticeType, $row['notice_type_id'], $expNoticeType)}
                </div>-->
            </div>
            ";

            if($row['template'] == 'Task'){
                $homeWorkChat ="
                <div class='homeworkChat floatbox'>
                    {$this->getHomeWorkChatOnOffPublishedImage($row['homework_chat'], $row['notice_id'])}
                </div>
                ";
            }

            $alert ="
            <div class='emailAlert'>
                {$this->getEmailOnOffPublishedImage($row['parent_email_sent'], $row['notice_id'])}
            </div>
            <div class='kiteChat'>
                {$this->getChatOnOffPublishedImage($row['parent_feedback'], $row['notice_id'])}
            </div>
            <div class='teacherKiteChat'>
                {$this->getTeacherChatOnOffPublishedImage($row['teacher_feedback'], $row['notice_id'])}
            </div>
            ";

            $launch_date ="
            {$formObj->getDateRow('', 'launch_date', $launchDate, $expDate)}
            ";

            $expiry_date ="
            {$formObj->getDateRow('', 'expiry_date', $expiryDate, $expDate)}
            ";
        } else if ($row['template'] == 'Gallery') {
            //<div class='centerPanel'><img src='/cmspilotv30/CP/www/themes/Manager/images/gallery-panel.png'></div>
            $noticeCenterPanel ="
            <div class='centerPanel'><img src='/cmspilotv30/CP/www/themes/Manager/images/notice-center-panel.png'></div>
            ";

            $alert ="
            <div class='emailAlert'>
                {$this->getEmailOnOffPublishedImage($row['parent_email_sent'], $row['notice_id'])}
            </div>
            <div class='kiteChat'>
                {$this->getChatOnOffPublishedImage($row['parent_feedback'], $row['notice_id'])}
            </div>
            <div class='teacherKiteChat'>
                {$this->getTeacherChatOnOffPublishedImage($row['teacher_feedback'], $row['notice_id'])}
            </div>
            ";

            $launch_date ="
            <div class='galleryDate'>
                {$formObj->getDateRow('', 'launch_date', $launchDate, $expDate)}
            </div>
            ";

            $expiry_date ="
            {$formObj->getDateRow('', 'expiry_date', $expiryDate, $expDate)}
            ";
        } else if ($row['template'] == 'Daily Diary') {
            $noticeCenterPanel ="
            <div class='centerPanel'><img src='/cmspilotv30/CP/www/themes/Manager/images/daily-diary-panel.png'></div>
            ";

            $activity_date ="
            {$formObj->getDateRow('', 'activity_date', $activityDate, $expDate)}
            ";

            $alert ="
            <div class='emailAlert'>
                {$this->getEmailOnOffPublishedImage($row['parent_email_sent'], $row['notice_id'])}
            </div>
            <div class='kiteChat'>
                {$this->getChatOnOffPublishedImage($row['parent_feedback'], $row['notice_id'])}
            </div>
            <div class='teacherKiteChat'>
                {$this->getTeacherChatOnOffPublishedImage($row['teacher_feedback'], $row['notice_id'])}
            </div>
            ";

            $launch_date ="
            {$formObj->getTBRow('', 'launch_date', $launchDate, $expNoEdit)}
            ";
        }

        $global_kite = '';
        if ($cpCfg['m.edukite.notice.showGlobalKite'] == 1 ) {
            $global_kite = "
            <div class='floatbox globalKite'>
                <div class='float_left mt5'>
                    Global Kite
                </div>
                <div class='float_left'>
                    {$this->getGlobalKiteOnOffPublishedImage($row['global_kite'], $row['notice_id'])}
                </div>
            </div>
            ";
        }

        $achievementLink = '';
        if($cpCfg['showAcheivement'] == 1){
            $achievemnentUrl = '/'. "controller/notice/?_action=achievementPanel&notice_id={$row['notice_id']}";
            $achievementLink = "
            <div class='achievementLink'>
                <strong><a href='{$achievemnentUrl}'><img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-list.png'></a></strong>
            </div>
            ";
        } else {
            $achievemnentUrl = '/'. "controller/notice/";
            /*$achievementLink = "
            <div class='achievementLink'>
                <strong><a href='{$achievemnentUrl}'><img src='/cmspilotv30/CP/www/themes/Manager/images/return-list.png'></a></strong>
            </div>
            ";*/
        }

        if($statusUrl == 'Archive'){
            $fieldset1 = "
            {$global_kite}
            {$formObj->getTBRow('Created By', 'teacher_id', $row['teacher_name'], $expTeacher)}
            {$formObj->getTBRow('Notice Title', 'title', $row['title'], $expNoticeId)}
            {$formObj->getTARow('Notice Text', 'description', $row['description'], $expNotes)}
            {$media->getRightPanelMediaDisplay('Picture', 'edukite_notice', 'picture', $row)}
            {$media->getRightPanelMediaDisplay('Attachment', 'edukite_notice', 'attachment', $row)}
            {$formObj->getTARow('Copy Web Links Here', 'links', $row['links'], $expNotes)}
            {$formObj->getTARow('Copy YouTube Links Here', 'youtube_links', $row['youtube_links'], $expNotes)}
            {$formObj->getTARow('Copy Vimeo Links Here', 'vimeo_links', $row['vimeo_links'], $expNotes)}
    		";
        } else {
            $fieldset1 = "
            {$global_kite}
            {$formObj->getTBRow('Created By', 'teacher_id', $row['teacher_name'], $expTeacher)}
            {$formObj->getTBRow('Notice Title', 'title', $row['title'], $expNoticeId)}
            {$formObj->getTARow('Notice Text', 'description', $row['description'], $expNotes)}
            {$media->getRightPanelMediaDisplay('Picture', 'edukite_notice', 'picture', $row)}
            {$media->getRightPanelMediaDisplay('Attachment', 'edukite_notice', 'attachment', $row)}
            {$formObj->getTARow('Copy Web Links Here', 'links', $row['links'], $expNotes)}
            {$formObj->getTARow('Copy YouTube Links Here', 'youtube_links', $row['youtube_links'], $expNotes)}
            {$formObj->getTARow('Copy Vimeo Links Here', 'vimeo_links', $row['vimeo_links'], $expNotes)}
            ";
        }
        $homework = '';

        if($cpCfg['cp.showHomework'] == 1 && $row['template'] == 'Task'){
            $homework = "
            <div class='homeworkSummary summaryLinkbutton'>
                <a href='#' class='' notice_id='{$row['notice_id']}'>Homework Summary</a>
            </div>
            ";
        }

        $text = "
        {$homework}
        {$achievementLink}
        {$this->getLaunchNowImage($row['launch_now'], $row['notice_id'])}
        {$noticeCenterPanel}
        {$activity_date}
        {$filter}
        {$goToUpload}
        {$launch_date}
        {$expiry_date}
        {$alert}
        {$homeWorkChat}
        {$formObj->getFieldSetWrapped('', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

		$template   = $fn->getReqParam ('template');
		$subject_id = $fn->getReqParam ('subject_id');
        $teacher_id = $fn->getReqParam ('teacher_id');
        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        $staffFilter = '';

		$spArray = array (
			'Kite Post'
		   ,'Daily Diary'
		   ,'Gallery'
		);

        $sqlSubject = "
        SELECT s.subject_id, s.title
        FROM subject s
        WHERE s.published = 1
        ORDER BY s.title
        ";

        $sqlTeacher = "
        SELECT t.teacher_id
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
        FROM teacher t
        WHERE t.status = 'Active'
        ORDER BY teacher_name
        ";
                        /*<select name='subject_id'>
                            <option value=''>Subject</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubject, $subject_id)}
                        </select>*/
        if ($teacherRec['role'] == 'Kite Master') {
            $staffFilter = "
            <div class='float_left'>
                <tr>
                    <select name='teacher_id'>
                        <option value=''>Staff</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlTeacher, $teacher_id)}
                    </select>
                </tr>
            </div>
            ";
        }

        $text = "
        <div class='ddFilter'>
            <div class='floatbox'>
                <div class='float_left'>
                    <tr>
                        <select name='template'>
                            <option value=''>Template</option>
                            {$cpUtil->getDropDown1($spArray, $template)}
                        </select>
                    </tr>
                </div>
                {$staffFilter}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $notice_id = $fn->getReqParam('record_id');
        $statusUrl = $fn->getReqParam('status');

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        $text = "
        <div class='btns'>
            <ul>
                <li>
                <a href='#' class='staffLinkInNotice' notice_id='{$notice_id}' status='{$statusUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/staff-btn.png'>
                </a>
                </li>
                <li>
                <a href='#' class='studentLinkInNotice'  notice_id='{$notice_id}' status='{$statusUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/child-btn.png'></a>
                </li>
                <li>
                <a href='#' class='classLinkInNotice' notice_id='{$notice_id}' status='{$statusUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/class-btn.png'></a>
                </a>
                </li>
                <li>
                <a href='#' class='cohortLinkInNotice' notice_id='{$notice_id}' status='{$statusUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png'>
                </a>
                </li>
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getClassList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $statusUrl = $fn->getReqParam('status');

        $rows = "";

        if($notice_id == ''){
           $notice_id = $fn->getReqParam('notice_id');
        }
        if($statusUrl == 'Archive'){
            $status = 'Archive';
        } else {
            $status = 'Active';
        }

        $sqlClass = "
        SELECT c.class_id
              ,c.title
        FROM class c
        WHERE c.status = '{$status}'
        ORDER BY c.title
        ";
        $result = $db->sql_query($sqlClass);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.class_id_hook
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.class_id_hook = {$row['class_id']}
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else {
                $image = "
                <a href='#' class='classLinkArrow' class_id='{$row['class_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($statusUrl == 'Archive'){
                $rows .= "
                <tr>
                    <td align='left'>
                        <a href='#' class='classLinkExpand plus' class_id='{$row['class_id']}' notice_id='{$notice_id}' status='Archive'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td></td>
                </tr>
                ";
                $selectAll = "";
            } else {
                $rows .= "
                <tr>
                    <td align='left'>
                        <a href='#' class='classLinkExpand plus' class_id='{$row['class_id']}' notice_id='{$notice_id}'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right' class='linkedArrow'>{$image}</td>
                </tr>
                ";
                $selectAll = "
                <td colspan='2'><a href='#' class='selectAllClass button' notice_id='{$notice_id}'>Select All</a></td>
                ";
            }
        }


        $text = "
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <tr>
                    {$selectAll}
                </tr>
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='class'></div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLinkedClassList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $rows = "";
        $text = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT DISTINCT lnktble.title
        ,hisTble.class_id_hook
        FROM notice_student hisTble
        LEFT JOIN (class lnktble) ON (hisTble.class_id_hook = lnktble.class_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.class_id_hook > 0
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlComment = "
            SELECT c.*
            FROM comment c
            LEFT JOIN (class_student cs) ON (cs.class_id = {$row['class_id_hook']})
            LEFT JOIN (student_parent sp) ON (sp.student_id = cs.student_id)
            WHERE c.contact_id = sp.parent_id
              AND c.record_id = {$notice_id}
              ORDER BY comment_id DESC
            ";
            $resultComment = $db->sql_query($sqlComment);
            $rowComment = $db->sql_fetchrow($resultComment);

            $star = '';
            if($rowComment['comments'] != ''){
                $star = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                ";
            }

			$achievementLinked = '';
	        if($cpCfg['showAcheivement'] == 1){
		        $SQLAchievement = "
		        SELECT ach.*
		        FROM achievement_student ach
	        	WHERE ach.class_id = {$row['class_id_hook']}
                  AND ach.notice_id = {$notice_id}
	        	";
	        	$resultAchievement  = $db->sql_query($SQLAchievement);
	            $achievementRows    = $db->sql_numrows($resultAchievement);

	            if ($achievementRows) {
    				$achievementLinked = "
    				<td align='right'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/notice_attachmentrighpanel.png'>
    				</td>
				";
				} else {
	                $achievementLinked = "
					<td></td>
					";
				}
			}

            if($status == 'Archive'){
                $rows .= "
                <tr childrenShown=0>
                    <td width=150>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right'>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='#' class='classLinkExpand plus' class_id='{$row['class_id_hook']}' notice_id='{$notice_id}' status='Archive'>
                        </a>
                    </td>

                </tr>
                ";

                $removeAll = "";
            } else {
                $rows .= "
                <tr childrenShown=0>
                    <td width=150>
                        <a href='#' class='classLinkDelete' class_id='{$row['class_id_hook']}' notice_id='{$notice_id}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right'>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='#' class='classLinkExpand plus' class_id='{$row['class_id_hook']}' notice_id='{$notice_id}'>
                        </a>
                    </td>

                </tr>
                ";

                $removeAll = "
                <td colspan='4'><a href='#' class='removeAllClass button'  notice_id='{$notice_id}'>Remove All</a></td>
                ";
            }
        }
        if($numRows){
            $text = "
            <div class='row rightPanelSelected'>
                <div class='audienceTxt'></div>
                <table class='list'>
                    <tr>

                    </tr>
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLinkClassToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';

        $notice_id = $fn->getReqParam('notice_id');
        $class_id  = $fn->getReqParam('class_id');

        $sqlLinkedOld = "
        SELECT student_id
        FROM class_student hisTble
        WHERE hisTble.class_id = {$class_id}
        ";
        $sqlLinked = "
        SELECT hisTble.student_id
        FROM class_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.class_id ={$class_id} AND lnktble.status ='Active'
        ";

        $result  = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['notice_id']     = $notice_id;
            $fa['class_id_hook'] = $class_id;
            $fa['student_id']    = $row['student_id'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                         "notice_id = {$notice_id} AND
                                                         student_id = {$row['student_id']} AND
                                                         class_id_hook = {$class_id}
                                                         ");

            if(is_array($noticeStudentChk)){
            } else {
                $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
                $insertResult        = $db->sql_query($insertSQL);
                $notice_student_id   = $db->sql_nextid();
            }

            $sql = "
            SELECT parent_id
            FROM student_parent
            WHERE student_id = {$row['student_id']}
            ";
            $resultParent  = $db->sql_query($sql);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {

                $fa1 = array();
                $fa1['notice_id']     = $notice_id;
                $fa1['notice_student_id'] = $notice_student_id;
                $fa1['student_id']    = $row['student_id'];
                $fa1['creation_date'] = date("Y-m-d H:i:s");
                $fa1['parent_id']     = $rowParent['parent_id'];

                $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                             "notice_id = {$notice_id} AND
                                                             student_id = {$row['student_id']} AND
                                                             parent_id = {$rowParent['parent_id']}
                                                             ");
                if(is_array($noticeParentChk)){
                } else{
                    $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                    $insertResult1        = $db->sql_query($insertSQL1);
                }
            }
        }

        if($numRows){
            $text = $this->getLinkedClassList($notice_id);
        }

        return $text;
    }

    /**
     *
     */
    function getLinkClassStudentToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';

        $notice_id = $fn->getReqParam('notice_id');
        $class_id  = $fn->getReqParam('class_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['notice_id']     = $notice_id;
        $fa['student_id']    = $student_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['class_id_hook'] = $class_id;

        $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id} AND
                                                     class_id_hook = {$class_id}
                                                     ");

        if(is_array($noticeStudentChk)){
            $notice_student_id = '';
        } else {
            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
            $insertResult        = $db->sql_query($insertSQL);
            $notice_student_id   = $db->sql_nextid();
        }

        $sql = "
        SELECT parent_id
        FROM student_parent
        WHERE student_id = {$student_id}
        ";
        $resultParent  = $db->sql_query($sql);
        while ($rowParent = $db->sql_fetchrow($resultParent)) {

            $fa1 = array();
            $fa1['notice_id']     = $notice_id;
            $fa1['notice_student_id'] = $notice_student_id;
            $fa1['student_id']    = $student_id;
            $fa1['creation_date'] = date("Y-m-d H:i:s");
            $fa1['parent_id']     = $rowParent['parent_id'];

            $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                         "notice_id = {$notice_id} AND
                                                         student_id = {$student_id} AND
                                                         parent_id = {$rowParent['parent_id']}
                                                         ");
            if(is_array($noticeParentChk)){
            } else{
                $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                $insertResult1        = $db->sql_query($insertSQL1);
            }
        }

        $text = $this->getLinkedClassList($notice_id);

        return $text;
    }
    /**
     *
     */
    function getLinkAllClassToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');

        $sqlMain = "
        SELECT class_id
        FROM class hisTble
        WHERE hisTble.status = 'Active' AND hisTble.class_id NOT IN(
            SELECT linkTble.class_id_hook
            FROM notice_student linkTble
            WHERE linkTble.notice_id = {$notice_id}
            AND linkTble.class_id_hook > 0
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlLinked = "
            SELECT hisTbleLinked.student_id
            FROM class_student hisTbleLinked
            WHERE hisTbleLinked.class_id = {$row['class_id']}
            ";
            $resultLinked = $db->sql_query($sqlLinked);
            while ($rowLinked = $db->sql_fetchrow($resultLinked)) {
                $fa = array();
                $fa['notice_id']     = $notice_id;
                $fa['class_id_hook'] = $row['class_id'];
                $fa['student_id']    = $rowLinked['student_id'];
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
                $insertResult        = $db->sql_query($insertSQL);
                $notice_student_id   = $db->sql_nextid();

                $sql = "
                SELECT parent_id
                FROM student_parent
                WHERE student_id = {$rowLinked['student_id']}
                ";
                $resultParent  = $db->sql_query($sql);
                while ($rowParent = $db->sql_fetchrow($resultParent)) {

                    $fa1 = array();
                    $fa1['notice_id']     = $notice_id;
                    $fa1['notice_student_id'] = $notice_student_id;
                    $fa1['student_id']    = $rowLinked['student_id'];
                    $fa1['creation_date'] = date("Y-m-d H:i:s");
                    $fa1['parent_id']     = $rowParent['parent_id'];

                    $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                                 "notice_id = {$notice_id} AND
                                                                 student_id = {$rowLinked['student_id']} AND
                                                                 parent_id = {$rowParent['parent_id']}
                                                                 ");
                    if(is_array($noticeParentChk)){
                    } else{
                        $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                        $insertResult1        = $db->sql_query($insertSQL1);
                    }
                }
            }
        }

        $text = $this->getLinkedClassList($notice_id);

        return $text;
    }
    /**
     *
     */
    function getExpandClassInRightPanel() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $class_id = $fn->getReqParam('class_id');
        $status = $fn->getReqParam('status');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        ,hisTble.notice_student_id
        FROM notice_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id   = {$notice_id}
        AND hisTble.class_id_hook = {$class_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $sqlComment = "
            SELECT c.*
            FROM comment c
            LEFT JOIN (student_parent sp) ON (sp.student_id = {$row['student_id']})
            WHERE c.contact_id = sp.parent_id
              AND c.record_id = {$notice_id}
              ORDER BY comment_id DESC
            ";
            $resultComment = $db->sql_query($sqlComment);
            $rowComment = $db->sql_fetchrow($resultComment);

            $star = '';
            //if($rowComment['comments'] != '' && $rowComment['record_type'] != 'edukite_teacher'){
            if($rowComment['comments'] != ''){
                $star = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                ";
            }

            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

			$achievementLinked = '';
	        if($cpCfg['showAcheivement'] == 1){
		        $SQLAchievement = "
		        SELECT ach.*
		        FROM achievement_student ach
	        	WHERE ach.student_id = {$row['student_id']}
                AND ach.notice_id = {$notice_id}
                AND ach.class_id = {$class_id}
	        	";
	        	$resultAchievement  = $db->sql_query($SQLAchievement);
	            $achievementRows = $db->sql_numrows($resultAchievement);

	            if ($achievementRows) {
                    $studentAchievemnentUrl = '/' . "controller/notice/?_action=achievementPanel&notice_id={$notice_id}&student_id={$row['student_id']}";
    				$achievementLinked = "
    				<td align='right'>
					   <a href='{$studentAchievemnentUrl}'>
                            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
		               </a>
    				</td>
				";
				} else {
	                $achievementLinked = "
					<td></td>
					";
				}
			}

            if($status == 'Archive'){
                $kiteUrl = $kiteUrl . '?status='.$status;
                $rows .= "
                <tr>
                    <td>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td>
                        <a href='#' class='studentInClassLinkDelete' notice_student_id='{$row['notice_student_id']}' notice_id='{$notice_id}' class_id='{$class_id}' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                </tr>
                ";
            }
        }

        $text = "
        <table class='list'>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getExpandClassInLeftPanel() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');
        $class_id = $fn->getReqParam('class_id');
        $status = $fn->getReqParam('status');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        FROM class_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.class_id   = {$class_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.class_id_hook
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.class_id_hook = {$class_id}
            AND hisTble.student_id = {$row['student_id']}
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows = $db->sql_numrows($resultLinked);

            $image ='';
            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else {
                $image = "
                <a href='#' class='classStudentLinkArrow' class_id='{$class_id}' notice_id='{$notice_id}' student_id='{$row['student_id']}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td>
                        <span style='top:0'>{$row['name']}</span>
                    </td>
                    <td align='right'>
                    </td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td>
                        <span>{$row['name']}</span>
                    </td>
                    <td align='right'>
                    {$image}
                    </td>
                </tr>
                ";
            }
        }

        $text = "
        <table class='list'>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     --------- STUDENT LINKING - LIST IN LEFT PANEL --------------------------------
     */
    function getStudentList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $rows = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }
        if($status == 'Archive'){
            $status = 'Archive';
        } else {
            $status = 'Active';
        }

        $sqlStudent = "
        SELECT s.student_id
               ,CONCAT_WS(' ', s.first_name, s.last_name ) AS name
        FROM student s
        WHERE s.status = '{$status}'
        ORDER BY s.last_name
        LIMIT 150
        ";
        $result = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.student_id = {$row['student_id']}
            AND (hisTble.class_id_hook = '' OR hisTble.class_id_hook IS NULL)
            AND (hisTble.year_group_id_hook = '' OR  hisTble.year_group_id_hook IS NULL)
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows      = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td>{$row['name']}</td>
                    <td align='right'></td>
                </tr>
                ";
                $studentSearch = '';
            } else {
                $rows .= "
                <tr>
                    <td>{$row['name']}</td>
                    <td align='right'>{$image}</td>
                </tr>
                ";

                $studentSearch = "
                {$this->getStudentSearch($notice_id)}
                ";
            }
        }

        //<a href='#' class='showAllStudent' notice_id='{$notice_id}'>Show All</a>
        $text = "
        {$studentSearch}
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' notice_id='{$notice_id}'>Select All</a></td>
                </tr>-->
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='student'></div>
        ";

        return $text;
    }

    /**
     *
     ------------------ STUDENT LIST IN RIGHT PANEL ---------------------------------
     */
    function getLinkedStudentList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $status = $fn->getReqParam('status');

        $rows = "";
        $text = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        //to get students who are linked
        $sqlLinked = "
        SELECT hisTble.student_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM notice_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.student_id > 0
        AND (hisTble.class_id_hook = '' OR hisTble.class_id_hook IS NULL)
        AND (hisTble.year_group_id_hook = '' OR  hisTble.year_group_id_hook IS NULL)
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {

            $sqlComment = "
            SELECT c.*
            FROM comment c
            LEFT JOIN (student_parent sp) ON (sp.student_id = {$row['student_id']})
            WHERE c.contact_id = sp.parent_id
              AND c.record_id = {$notice_id}
              ORDER BY comment_id DESC
            ";
            $resultComment = $db->sql_query($sqlComment);
            $rowComment = $db->sql_fetchrow($resultComment);

            $star = '';
            if($rowComment['comments'] != ''){
                $star = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                ";
            }
            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

			$achievementLinked = '';
	        if($cpCfg['showAcheivement'] == 1){
		        $SQLAchievement = "
		        SELECT sa.*
		        FROM achievement_student sa
	        	WHERE sa.student_id = {$row['student_id']}
                    AND sa.notice_id = {$notice_id}
	        	";
	        	$resultAchievement  = $db->sql_query($SQLAchievement);
	            $achievementRows = $db->sql_numrows($resultAchievement);

	            if ($achievementRows) {
                    $studentAchievemnentUrl = '/' . "controller/notice/?_action=achievementPanel&notice_id={$notice_id}&student_id={$row['student_id']}";
    				$achievementLinked = "
    				<td align='right'>
					   <a href='{$studentAchievemnentUrl}'>
                            <img src='/cmspilotv30/CP/www/themes/Manager/images/notice_attachmentrighpanel.png'>
		               </a>
    				</td>
				";
				} else {
	                $achievementLinked = "
					<td></td>
					";
				}
			}

            if($status == 'Archive'){
                $kiteUrl = $kiteUrl . '?status='.$status;
                $rows .= "
                <tr>
                    <td>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td>
                        <a href='{$kiteUrl}' class='studentInStudentLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                    </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td>
                        <a href='#' class='studentLinkDelete' student_id='{$row['student_id']}' notice_id='{$notice_id}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
    				{$achievementLinked}
                    <td>
                        <a href='{$kiteUrl}' class='studentInStudentLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                    </tr>
                ";
            }
        }

        if($numRows){
            $text = "
            <div class='row'>
                <div class='audienceTxt'></div>
                <table class='list'>
                    <!--<tr>
                        <td colspan='3'><a href='#' class='removeAllStudent button'  notice_id='{$notice_id}'>Remove All</a></td>
                    </tr>-->
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLinkStudentToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id   = $fn->getReqParam('notice_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['notice_id']     = $notice_id;
        $fa['student_id']    = $student_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
        $insertResult        = $db->sql_query($insertSQL);
        $notice_student_id   = $db->sql_nextid();

        $sql = "
        SELECT parent_id
        FROM student_parent
        WHERE student_id = {$student_id}
        ";
        $resultParent  = $db->sql_query($sql);
        while ($rowParent = $db->sql_fetchrow($resultParent)) {

            $fa1 = array();
            $fa1['notice_id']     = $notice_id;
            $fa1['notice_student_id'] = $notice_student_id;
            $fa1['student_id']    = $student_id;
            $fa1['creation_date'] = date("Y-m-d H:i:s");
            $fa1['parent_id']     = $rowParent['parent_id'];

            $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                         "notice_id = {$notice_id} AND
                                                         student_id = {$student_id} AND
                                                         parent_id = {$rowParent['parent_id']}
                                                         ");
            if(is_array($noticeParentChk)){
            } else {
                $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                $insertResult1        = $db->sql_query($insertSQL1);
            }
        }

        $text = $this->getLinkedStudentList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getLinkAllStudentToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id   = $fn->getReqParam('notice_id');
        set_time_limit(50000);

        $sqlMain = "
        SELECT hisTble.student_id
        FROM student hisTble
        WHERE hisTble.status = 'Active' AND       hisTble.student_id NOT IN(
            SELECT linkTble.student_id
            FROM notice_student linkTble
            WHERE linkTble.notice_id = {$notice_id}
            AND (linkTble.class_id_hook = '' OR linkTble.class_id_hook IS NULL)
            AND (linkTble.year_group_id_hook = '' OR  linkTble.year_group_id_hook IS NULL)
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['notice_id']     = $notice_id;
            $fa['student_id']    = $row['student_id'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
            $insertResult        = $db->sql_query($insertSQL);
            $notice_student_id   = $db->sql_nextid();

            $sql = "
            SELECT parent_id
            FROM student_parent
            WHERE student_id = {$row['student_id']}
            ";
            $resultParent  = $db->sql_query($sql);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {

                $fa1 = array();
                $fa1['notice_id']     = $notice_id;
                $fa1['notice_student_id'] = $notice_student_id;
                $fa1['student_id']    = $row['student_id'];
                $fa1['creation_date'] = date("Y-m-d H:i:s");
                $fa1['parent_id']     = $rowParent['parent_id'];

                $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                             "notice_id = {$notice_id} AND
                                                             student_id = {$row['student_id']} AND
                                                             parent_id = {$rowParent['parent_id']}
                                                             ");
                if(is_array($noticeParentChk)){
                } else {
                    $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                    $insertResult1        = $db->sql_query($insertSQL1);
                }
            }

        }

        $text = $this->getLinkedStudentList($notice_id);

        return $text;
    }

     /**
     *
     */
    function getNoticeOptions(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $kitePostUrl   = "new/?template=kitePost";
        $kitePostLeftUrl   = "new/?template=kitePostLeft";
        $dailyDiaryUrl = "new/?template=dailyDiary";
        $galleryUrl    = "new/?template=gallery";

        if($cpCfg['cp.primarySchool'] == 1){
            $text = "
            <div class='noticeType'>
                <div class='leftNotice'>
                    <div class='calendar noticeTypeSmallBg'><a href='{$dailyDiaryUrl}'><span>Activity Calendar</span></a></div>
                    <div class='news noticeTypeSmallBg'><a href='{$kitePostLeftUrl}'><span>Class News</span></a></div>
                </div>
                <div class='notice noticeTypeBg'><a href='{$kitePostUrl}'><span>Notice</span></a></div>
                <div class='locker noticeTypeBg'><a href='{$galleryUrl}'><span>Locker</span></a></div>
            </div>
            ";
        } else {
            $text = "
            <div class='noticeType'>
                <div class='dailyDiary noticeTypeBg'><a href='{$dailyDiaryUrl}'><span>Daily Diary</span></a></div>
                <div class='kitePost noticeTypeBg'><a href='{$kitePostUrl}'><span>Kite Post</span></a></div>
                <div class='gallery noticeTypeBg'><a href='{$galleryUrl}'><span>Gallery</span></a></div>
            </div>
            ";
        }

        return $text;

    }

    /**
     *
     */
    function getNavPanel2() {
        $tv         = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');

        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        $text = "
        <div class='navPanel'>
            {$searchText}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getKiteNoticeDetail($row){
        print_r ($row);
    }

    /**
     *
     */
    function getEmailOnOffPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getEmailOnOffPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__parent_email_sent__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getEmailOnOffPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/on.png'>";
        } else {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/off.png'>";
        }

        if ($editable == 1 && $status != 'Archive') {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.edukite.notice.emailPublishOnOffImage('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    /**
     *
     */
    function getEmailPublishNoticeRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET parent_email_sent = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $this->getEmailOnOffPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    /**
     *
     */
    function getChatOnOffPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getChatOnOffPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__kite_chat__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getChatOnOffPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/on.png'>";
        } else {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/off.png'>";
        }

        if ($editable == 1 && $status !='Archive') {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.edukite.notice.chatPublishOnOffImage('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    /**
     *
     */
    function getTeacherChatOnOffPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getTeacherChatOnOffPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__teacherKite_chat__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getTeacherChatOnOffPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/on.png'>";
        } else {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/off.png'>";
        }

        if ($editable == 1 && $status !='Archive') {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.edukite.notice.teacherChatPublishOnOffImage('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    /**
     *
     */
    function getHomeWorkChatOnOffPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getHomeWorkChatOnOffPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
            <div class='float_left home_Work'>Homework Chat</div>
            <div id='txt__homerwork_chat__{$id}'>
               {$publishedIcons}
            </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getHomeWorkChatOnOffPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/on.png'>";
        } else {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/off.png'>";
        }

        if ($editable == 1 && $status !='Archive') {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.edukite.notice.homeWorkChatOnOffImage('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }


    /**
     *
     */
    function getHomeWorkChatNoticeRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET homework_chat = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $this->getHomeWorkChatOnOffPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    /**
     *
     */
    function getChatPublishNoticeRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET parent_feedback = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $this->getChatOnOffPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    /**
     *
     */
    function getTeacherChatPublishNoticeRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET teacher_feedback = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $this->getTeacherChatOnOffPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    /**
     *
     */
    function getLaunchNowImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getLaunchNowImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__launch_now__{$id}' class='launchNow'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getLaunchNowImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $noticeRec = $fn->getRecordRowById('notice', 'notice_id', $id);
        $title = $noticeRec['title'];
        $description = $noticeRec['description'];

        $noticeStudentRec = $fn->getRecordByCondition('notice_student', "notice_id = '{$id}'");

        if ($noticeStudentRec['notice_id'] == '') {
            $alert = 'Yes';
        } else {
            $alert = 'No';
        }

        $imgReload = "";
        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/launched-image.png'>";
        } else {
            $imgSrc = "<a style='text-decoration:none;' href='#' class='launchNowImage' module='{$module}'
                rowId='{$id}' currentValue='{$value}' linking='{$alert}'><img src='/cmspilotv30/CP/www/themes/Manager/images/launch-kite.png'></a>";
        }

        if ($editable == 1) {
            $text = "
            {$imgSrc}
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    /**
     *
     */
    function getLaunchNowToKites() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $currentValue = $fn->getPostParam('currentValue');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        //-----------------------------------------------------//
        $noticeStudentRec = $fn->getRecordByCondition('notice_student', "notice_id = '{$record_id}'");

        //To check if there is any data in audience(right panel)
        if ($noticeStudentRec['notice_id'] == '') {
            $text = 'Yes';
        } else {
            $updateSQL = "
            UPDATE notice
            SET launch_now = {$newValue}
            WHERE notice_id = {$record_id}
            ";
            $result = $db->sql_query($updateSQL);

            $text = $this->getLaunchNowImageIcon('edukite_notice', $record_id, $newValue);
        }

        return $text;
    }

    /**
     *
     */
    function getCohortList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $rows = "";

        if($notice_id == ''){
           $notice_id = $fn->getReqParam('notice_id');
        }
        if($status == 'Archive'){
            $status = 'Archive';
        } else {
            $status = 'Active';
        }

        $sqlYearGroup = "
        SELECT yg.year_group_id
              ,yg.title
        FROM year_group yg
        WHERE yg.status = '{$status}'
        ORDER BY yg.title
        ";
        $result = $db->sql_query($sqlYearGroup);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.year_group_id_hook
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.year_group_id_hook = {$row['year_group_id']}
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='cohortLinkArrow' year_group_id='{$row['year_group_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td align='left'>
                        <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id']}' notice_id='{$notice_id}' status='Archive'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td></td>
                </tr>
                ";
                $selectAll='';
            } else {
                $rows .= "
                <tr>
                    <td align='left'>
                        <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id']}' notice_id='{$notice_id}'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right'>{$image}</td>
                </tr>
                ";

                $selectAll = "
                <td colspan='2'><a href='#' class='selectAllCohort button' notice_id='{$notice_id}'>Select All</a></td>
                ";
            }
        }

        $text = "
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <tr>
                    {$selectAll}
                </tr>
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='cohort'></div>
        ";

        return $text;
    }
    /**
     *
     */
    function getLinkedCohortList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $rows = "";
        $text = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        //to get cohorts which are linked

        $sqlLinked = "
        SELECT DISTINCT lnktble.title
        ,hisTble.year_group_id_hook
        FROM notice_student hisTble
        LEFT JOIN (year_group lnktble) ON (hisTble.year_group_id_hook = lnktble.year_group_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.year_group_id_hook > 0
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlComment = "
            SELECT c.*
            FROM comment c
            LEFT JOIN (student_year_group syg) ON (syg.year_group_id = {$row['year_group_id_hook']})
            LEFT JOIN (student_parent sp) ON (sp.student_id = syg.student_id)
            WHERE c.contact_id = sp.parent_id
              AND c.record_id = {$notice_id}
              ORDER BY comment_id DESC
            ";
            $resultComment = $db->sql_query($sqlComment);
            $rowComment = $db->sql_fetchrow($resultComment);

            $star = '';
            if($rowComment['comments'] != ''){
                $star = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                ";
            }

			$achievementLinked = '';
	        if($cpCfg['showAcheivement'] == 1){
		        $SQLAchievement = "
		        SELECT ach.*
		        FROM achievement_student ach
	        	WHERE ach.year_group_id = {$row['year_group_id_hook']}
                    AND ach.notice_id = {$notice_id}
	        	";
	        	$resultAchievement  = $db->sql_query($SQLAchievement);
	            $achievementRows    = $db->sql_numrows($resultAchievement);

	            if ($achievementRows) {
    				$achievementLinked = "
    				<td align='right'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/notice_attachmentrighpanel.png'>
    				</td>
				";
				} else {
	                $achievementLinked = "
					<td></td>
					";
				}
			}

            if($status == 'Archive'){
                $rows .= "
                <tr childrenShown=0>
                    <td width=150>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right'>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id_hook']}' notice_id='{$notice_id}' status='Archive'>
                        </a>
                    </td>
                </tr>
                ";
                $removeAll = '';
            } else {
                $rows .= "
                <tr childrenShown=0>
                    <td width=150>
                        <a href='#' class='cohortLinkDelete' year_group_id='{$row['year_group_id_hook']}' notice_id='{$notice_id}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span>{$row['title']}</span>
                    </td>
                    <td align='right'>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id_hook']}' notice_id='{$notice_id}'>
                        </a>
                    </td>
                </tr>
                ";

                $removeAll = "
                <td colspan='4'><a href='#' class='removeAllCohort button'  notice_id='{$notice_id}'>Remove All</a></td>
                ";
            }
        }
        if($numRows){
            $text = "
            <div class='row rightPanelSelected'>
                <div class='audienceTxt'></div>
                <table class='list'>
                    <tr>
                        {$removeAll}
                    </tr>
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLinkCohortToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';
        set_time_limit(10000000);

        $notice_id      = $fn->getReqParam('notice_id');
        $year_group_id  = $fn->getReqParam('year_group_id');

        $sqlLinkedOld = "
        SELECT student_id
        FROM student_year_group hisTble
        WHERE hisTble.year_group_id = {$year_group_id}
        ";
        $sqlLinked = "
        SELECT hisTble.student_id
        FROM student_year_group hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.year_group_id ={$year_group_id} AND lnktble.status ='Active'
        ";

        $result  = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['notice_id']            = $notice_id;
            $fa['year_group_id_hook']   = $year_group_id;
            $fa['student_id']           = $row['student_id'];
            $fa['creation_date']        = date("Y-m-d H:i:s");

            $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                         "notice_id = {$notice_id} AND
                                                         student_id = {$row['student_id']} AND
                                                         year_group_id_hook = {$year_group_id}
                                                         ");

            if(is_array($noticeStudentChk)){
            } else {
                $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
                $insertResult        = $db->sql_query($insertSQL);
                $notice_student_id   = $db->sql_nextid();
            }

            $sql = "
            SELECT parent_id
            FROM student_parent
            WHERE student_id = {$row['student_id']}
            ";
            $resultParent  = $db->sql_query($sql);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {

                $fa1 = array();
                $fa1['notice_id']     = $notice_id;
                $fa1['notice_student_id'] = $notice_student_id;
                $fa1['student_id']    = $row['student_id'];
                $fa1['creation_date'] = date("Y-m-d H:i:s");
                $fa1['parent_id']     = $rowParent['parent_id'];

                $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                             "notice_id = {$notice_id} AND
                                                             student_id = {$row['student_id']} AND
                                                             parent_id = {$rowParent['parent_id']}
                                                             ");
                if(is_array($noticeParentChk)){
                } else {
                    $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                    $insertResult1        = $db->sql_query($insertSQL1);
                }
            }

        }

        if($numRows){
            $text = $this->getLinkedCohortList($notice_id);
        }

        return $text;
    }

    /**
     *
     */
    function getLinkCohortStudentToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';

        $notice_id      = $fn->getReqParam('notice_id');
        $year_group_id  = $fn->getReqParam('year_group_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['notice_id']            = $notice_id;
        $fa['year_group_id_hook']   = $year_group_id;
        $fa['student_id']           = $student_id;
        $fa['creation_date']        = date("Y-m-d H:i:s");

        $noticeStudentChk = $fn->getRecordByCondition('notice_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id} AND
                                                     year_group_id_hook = {$year_group_id}
                                                     ");

        if(is_array($noticeStudentChk)){
            $notice_student_id  = '';
        } else {
            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
            $insertResult        = $db->sql_query($insertSQL);
            $notice_student_id   = $db->sql_nextid();
        }

        $sql = "
        SELECT parent_id
        FROM student_parent
        WHERE student_id = {$student_id}
        ";
        $resultParent  = $db->sql_query($sql);
        while ($rowParent = $db->sql_fetchrow($resultParent)) {

            $fa1 = array();
            $fa1['notice_id']     = $notice_id;
            $fa1['notice_student_id'] = $notice_student_id;
            $fa1['student_id']    = $student_id;
            $fa1['creation_date'] = date("Y-m-d H:i:s");
            $fa1['parent_id']     = $rowParent['parent_id'];

            $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                         "notice_id = {$notice_id} AND
                                                         student_id = {$student_id} AND
                                                         parent_id = {$rowParent['parent_id']}
                                                         ");
            if(is_array($noticeParentChk)){
            } else {
                $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                $insertResult1        = $db->sql_query($insertSQL1);
            }
        }

        $text = $this->getLinkedCohortList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getExpandCohortInRightPanel() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";

        $notice_id     = $fn->getReqParam('notice_id');
        $year_group_id = $fn->getReqParam('year_group_id');
        $status = $fn->getReqParam('status');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        ,hisTble.notice_student_id
        FROM notice_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id   = {$notice_id}
        AND hisTble.year_group_id_hook = {$year_group_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $sqlComment = "
            SELECT c.*
            FROM comment c
            LEFT JOIN (student_parent sp) ON (sp.student_id = {$row['student_id']})
            WHERE c.contact_id = sp.parent_id
              AND c.record_id = {$notice_id}
              ORDER BY comment_id DESC
            ";
            $resultComment = $db->sql_query($sqlComment);
            $rowComment = $db->sql_fetchrow($resultComment);

            $star = '';
            if($rowComment['comments'] != ''){
                $star = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/star-icon.png'>
                ";
            }

            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);
			$achievementLinked = '';

	        if($cpCfg['showAcheivement'] == 1){
		        $SQLAchievement = "
		        SELECT ach.*
		        FROM achievement_student ach
	        	WHERE ach.student_id = {$row['student_id']}
                AND ach.notice_id = {$notice_id}
                AND ach.year_group_id = {$year_group_id}
	        	";
	        	$resultAchievement  = $db->sql_query($SQLAchievement);
	            $achievementRows = $db->sql_numrows($resultAchievement);

	            if ($achievementRows) {
                    $studentAchievemnentUrl = '/' . "controller/notice/?_action=achievementPanel&notice_id={$notice_id}&student_id={$row['student_id']}";
    				$achievementLinked = "
    				<td align='right'>
					   <a href='{$studentAchievemnentUrl}'>
                            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
		               </a>
    				</td>
				    ";
				} else {
	                $achievementLinked = "
					<td></td>
					";
				}
			}

            if($status == 'Archive'){
                $kiteUrl = $kiteUrl . '?status='.$status;
                $rows .= "
                <tr>
                    <td>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td>
                        <a href='#' class='studentInCohortLinkDelete' notice_student_id='{$row['notice_student_id']}' notice_id='{$notice_id}' student_id='{$row['student_id']}' year_group_id='{$year_group_id}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span>{$row['name']}</span>
                    </td>
                    <td>
                        {$star}
                    </td>
                    {$achievementLinked}
                    <td align='right'>
                        <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                        </a>
                    </td>
                </tr>
                ";
            }
        }

        $text = "
        <table class='list'>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getExpandCohortInLeftPanel() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";

        $notice_id     = $fn->getReqParam('notice_id');
        $year_group_id = $fn->getReqParam('year_group_id');
        $status = $fn->getReqParam('status');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        FROM student_year_group hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.year_group_id = {$year_group_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.year_group_id_hook
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.year_group_id_hook = {$year_group_id}
            AND hisTble.student_id = {$row['student_id']}
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='cohortStudentLinkArrow' year_group_id='{$year_group_id}' notice_id='{$notice_id}' student_id='{$row['student_id']}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td>
                        <span style='top:0'>{$row['name']}</span>
                    </td>
                    <td align='right'>
                    </td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr>
                    <td>
                        <span>{$row['name']}</span>
                    </td>
                    <td align='right'>
                        {$image}
                    </td>
                </tr>
                ";
            }
        }

        $text = "
        <table class='list'>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getLinkAllCohortToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $notice_id = $fn->getReqParam('notice_id');

        $sqlMain = "
        SELECT year_group_id
        FROM year_group hisTble
        WHERE hisTble.status = 'Active' AND hisTble.year_group_id NOT IN(
            SELECT linkTble.year_group_id_hook
            FROM notice_student linkTble
            WHERE linkTble.notice_id = {$notice_id}
            AND linkTble.year_group_id_hook > 0
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlLinked = "
            SELECT hisTbleLinked.student_id
            FROM student_year_group hisTbleLinked
            WHERE hisTbleLinked.year_group_id = {$row['year_group_id']}
            ";
            $resultLinked = $db->sql_query($sqlLinked);
            while ($rowLinked = $db->sql_fetchrow($resultLinked)) {
                $fa = array();
                $fa['notice_id']          = $notice_id;
                $fa['year_group_id_hook'] = $row['year_group_id'];
                $fa['student_id']         = $rowLinked['student_id'];
                $fa['creation_date']      = date("Y-m-d H:i:s");
                $insertSQL                = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
                $insertResult             = $db->sql_query($insertSQL);
                $notice_student_id   = $db->sql_nextid();

                $sql = "
                SELECT parent_id
                FROM student_parent
                WHERE student_id = {$rowLinked['student_id']}
                ";
                $resultParent  = $db->sql_query($sql);
                while ($rowParent = $db->sql_fetchrow($resultParent)) {

                    $fa1 = array();
                    $fa1['notice_id']     = $notice_id;
                    $fa1['notice_student_id'] = $notice_student_id;
                    $fa1['student_id']    = $rowLinked['student_id'];
                    $fa1['creation_date'] = date("Y-m-d H:i:s");
                    $fa1['parent_id']     = $rowParent['parent_id'];

                    $noticeParentChk = $fn->getRecordByCondition('notice_parent',
                                                                 "notice_id = {$notice_id} AND
                                                                 student_id = {$rowLinked['student_id']} AND
                                                                 parent_id = {$rowParent['parent_id']}
                                                                 ");
                    if(is_array($noticeParentChk)){
                    } else {
                        $insertSQL1           = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_parent');
                        $insertResult1        = $db->sql_query($insertSQL1);
                    }
                }
            }
        }

        $text = $this->getLinkedCohortList($notice_id);

        return $text;
    }

    /**
     *
     --------- CLASS LINKING - LEFT PANEL DEFAULT CONTENT--------------------------------
     */
    function getLeftPanelDefaultContent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $notice_id  = $fn->getReqParam('record_id');

        $classLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND class_id_hook > 0");
        $cohortLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND year_group_id_hook > 0");
        $studentLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND  student_id > 0");

        $staffLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND teacher_id > 0");
        //the priority of showing the default link is Class, Cohort, Student, Staff
        //Check which of the above condition satisfies and show the link accordingly.

        if ($classLink['notice_id'] != '' ) {
            return $this->getClassList($notice_id);
        } else if ($cohortLink['notice_id'] != '' ) {
            return $this->getCohortList($notice_id);
        } else if ($studentLink['notice_id'] != '' ) {
            return $this->getStudentList($notice_id);
        } else if ($staffLink['notice_id'] != '' ) {
            return $this->getStaffList($notice_id);
        } else {
            return $this->getClassList($notice_id);
        }

    }

    /**
     *
     ------------- CLASS LIST IN RIGHT PANEL DEFAULT CONTENT---------------------------------
     */
    function getRightPanelDefaultContent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        $notice_id = $fn->getReqParam('record_id');

        $classLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND class_id_hook > 0");
        $cohortLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND year_group_id_hook > 0");
        $studentLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND  student_id > 0");

        $staffLink = $fn->getRecordByCondition('notice_student', "notice_id = '{$notice_id}' AND teacher_id > 0");
        //the priority of showing the default link is Class, Cohort, Student, Staff
        //Check which of the above condition satisfies and show the link accordingly.

        if ($classLink['notice_id'] != '' ) {
            return $this->getLinkedClassList($notice_id);
        } else if ($cohortLink['notice_id'] != '' ) {
            return $this->getLinkedCohortList($notice_id);
        } else if ($studentLink['notice_id'] != '' ) {
            return $this->getLinkedStudentList($notice_id);
        } else if ($staffLink['notice_id'] != '' ) {
            return $this->getLinkedStaffList($notice_id);
        } else {
            return $this->getLinkedClassList($notice_id);
        }

    }

    /**
     *
     --------- STAFF LINKING - LIST IN LEFT PANEL --------------------------------
     */
    function getStaffList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $status = $fn->getReqParam('status');

        $rows = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }
        if($status == 'Archive'){
            $status = 'Archive';
        } else {
            $status = 'Active';
        }

        $sqlStaff = "
        SELECT t.teacher_id
               ,CONCAT_WS(' ', t.first_name, t.last_name ) AS name
        FROM teacher t
        WHERE t.status = '{$status}'
        ORDER BY t.last_name
        ";
        $result     = $db->sql_query($sqlStaff);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.teacher_id
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.teacher_id = {$row['teacher_id']}
            AND (hisTble.class_id_hook = '' OR hisTble.class_id_hook IS NULL)
            AND (hisTble.year_group_id_hook = '' OR  hisTble.year_group_id_hook IS NULL)
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows      = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='staffLinkArrow' teacher_id='{$row['teacher_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td>{$row['name']}</td>
                    <td align='right'></td>
                </tr>
                ";

                $selectAll='';

                $staffSearch = '';
            } else {
                $rows .= "
                <tr>
                    <td>{$row['name']}</td>
                    <td align='right'>{$image}</td>
                </tr>
                ";

                $selectAll = "
                <td colspan='2'><a href='#' class='selectAllStaff button' notice_id='{$notice_id}'>Select All</a></td>
                ";

                $staffSearch = "
                {$this->getStaffSearch($notice_id)}
                ";
            }
        }

        $text = "
        {$staffSearch}
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <tr>
                    {$selectAll}
                </tr>
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='staff'></div>
        ";

        return $text;
    }

    /**
     *
     ------------------ STAFF LIST IN RIGHT PANEL ---------------------------------
     */
    function getLinkedStaffList($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $status = $fn->getReqParam('status');

        $rows = "";
        $text = "";

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        //to get students who are linked
        $sqlLinked = "
        SELECT hisTble.teacher_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM notice_student hisTble
        LEFT JOIN (teacher lnktble) ON (hisTble.teacher_id = lnktble.teacher_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.teacher_id > 0
        AND (hisTble.class_id_hook = '' OR hisTble.class_id_hook IS NULL)
        AND (hisTble.year_group_id_hook = '' OR  hisTble.year_group_id_hook IS NULL)
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $urlArray['sitePfxId'] = $row['teacher_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            if($status == 'Archive'){
                $rows .= "
                <tr>
                    <td>
                        {$row['name']}
                    </td>
                </tr>
                ";
                $removeAll = '';
            } else {
                $rows .= "
                <tr>
                    <td>
                        <a href='#' class='staffLinkDelete' teacher_id='{$row['teacher_id']}' notice_id='{$notice_id}'>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                        </a>
                        <span class='float_right'>{$row['name']}</span>
                    </td>
                </tr>
                ";

                $removeAll = "
                <td colspan='2'><a href='#' class='removeAllStaff button'  notice_id='{$notice_id}'>Remove All</a></td>
                ";
            }
        }

        if($numRows){
            $text = "
            <div class='row'>
                <div class='audienceTxt'></div>
                <table class='list'>
                    <tr>
                        {$removeAll}
                    </tr>
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getLinkStaffToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id   = $fn->getReqParam('notice_id');
        $teacher_id  = $fn->getReqParam('teacher_id');

        $fa = array();
        $fa['notice_id']     = $notice_id;
        $fa['teacher_id']    = $teacher_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getLinkedStaffList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getLinkAllStaffToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id   = $fn->getReqParam('notice_id');
        set_time_limit(50000);

        $sqlMain = "
        SELECT hisTble.teacher_id
        FROM teacher hisTble
        WHERE hisTble.status = 'Active' AND       hisTble.teacher_id NOT IN(
            SELECT linkTble.teacher_id
            FROM notice_student linkTble
            WHERE linkTble.notice_id = {$notice_id}
            AND  linkTble.teacher_id IS NOT NULL
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['notice_id']     = $notice_id;
            $fa['teacher_id']    = $row['teacher_id'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'notice_student');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getLinkedStaffList($notice_id);

        return $text;
    }

    /**
     *
     */
    function getSortMediaRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $media_id   = $fn->getReqParam('media_id');
        $mediaRec    = $fn->getRecordRowByID('media', 'media_id', $media_id );

        $updateSQL = "
        UPDATE media
        SET sort_order = 1
        WHERE media_id = {$media_id}
        ";
        $result = $db->sql_query($updateSQL);

        $updateSQL1 = "
        UPDATE media
        SET sort_order = 2
        WHERE media_id != {$media_id}
          AND record_id = {$mediaRec['record_id']}
          AND room_name = '{$mediaRec['room_name']}'
          AND media_type = '{$mediaRec['media_type']}'
        ";
        $result1 = $db->sql_query($updateSQL1);
    }

    /**
     *
     */
    function getStudentSearch($notice_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');

        $text = "
        <div id='studentSearch'>
            <form name='search' action='' id='frmKeyword'>
                <div class='floatbox'>
                    <div class='float_left keywordWrap'>
                        <input type='text' class='student' name='student' id='student' value=''>
                    </div>
                    <div class='float_left btnSubmit'>
                       <a href='#' class='submit' notice_id='{$notice_id}'><img src='/cmspilotv30/CP/www/themes/Manager/images/find.png'></a>
                    </div>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getStaffSearch($notice_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');

        $text = "
        <div id='staffSearch'>
            <form name='search' action='' id='frmKeyword'>
                <div class='floatbox'>
                    <div class='float_left keywordWrap'>
                        <input type='text' class='staff' name='staff' id='staff' value=''>
                    </div>
                    <div class='float_left btnSubmit'>
                       <a href='#' class='submit' notice_id='{$notice_id}'><img src='/cmspilotv30/CP/www/themes/Manager/images/find.png'></a>
                    </div>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     --------- STUDENT DISPLAY - AFTER SEARCH IN LEFT PANEL --------------------------------
     */
    function getStudentDisplayAfterSearch($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $sqlAppend = '';

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        $student_name = $fn->getReqParam('student_name');

        if($student_name != ''){
            $sqlAppend = "
            AND s.first_name LIKE '%{$student_name}%'
            OR s.last_name LIKE '%{$student_name}%'
            ";
        }

        $sqlStudent = "
        SELECT s.student_id
               ,CONCAT_WS(' ', s.first_name, s.last_name ) AS name
        FROM student s
        WHERE s.status = 'Active'
        {$sqlAppend}
        ORDER BY s.last_name
        ";
        $result     = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.student_id = {$row['student_id']}
            AND (hisTble.class_id_hook = '' OR hisTble.class_id_hook IS NULL)
            AND (hisTble.year_group_id_hook = '' OR  hisTble.year_group_id_hook IS NULL)
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows      = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td align='right'>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' notice_id='{$notice_id}'>Select All</a></td>
                </tr>-->
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='student'></div>
        ";

        return $text;
    }

    /**
     *
     --------- STAFF DISPLAY - AFTER SEARCH IN LEFT PANEL --------------------------------
     */
    function getStaffDisplayAfterSearch($notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $sqlAppend = '';

        if($notice_id == ''){
            $notice_id = $fn->getReqParam('notice_id');
        }

        $staff_name = $fn->getReqParam('staff_name');

        if($staff_name != ''){
            $sqlAppend = "
            AND s.first_name LIKE '%{$staff_name}%'
            OR s.last_name LIKE '%{$staff_name}%'
            ";
        }

        $sqlStaff = "
        SELECT s.teacher_id
               ,CONCAT_WS(' ', s.first_name, s.last_name ) AS name
        FROM teacher s
        WHERE s.status = 'Active'
        {$sqlAppend}
        ORDER BY s.last_name
        ";
        $result     = $db->sql_query($sqlStaff);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.teacher_id
            FROM notice_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.teacher_id = {$row['teacher_id']}
            ";
            $resultLinked = $db->sql_query($sqlTableLinked);
            $numRows      = $db->sql_numrows($resultLinked);

            if($numRows){
                $image = "
                <img src='/cmspilotv30/CP/www/themes/Manager/images/linked-arrow.png'>
                ";
            }
            else{
                $image = "
                <a href='#' class='staffLinkArrow' teacher_id='{$row['teacher_id']}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td align='right'>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' notice_id='{$notice_id}'>Select All</a></td>
                </tr>-->
                {$rows}
            </table>
        </div>
        <div id='activeLayout' value='staff'></div>
        ";

        return $text;
    }

    /**
     *
     */
    function getGlobalKiteOnOffPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getGlobalKiteOnOffPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__global_kite__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getGlobalKiteOnOffPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/on.png'>";
        } else {
            $imgSrc = "<img src='/cmspilotv30/CP/www/themes/Manager/images/off.png'>";
        }

        if ($editable == 1) {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.edukite.notice.globalKitePublishOnOffImage('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    /**
     *
     */
    function getGlobalKitePublishNoticeRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET global_kite = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $this->getGlobalKiteOnOffPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }


     /**
     *
     */
    function getAchievementPanelOld(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');

        $notice_id   = $fn->getReqParam('notice_id');
        $achievement = $fn->getReqParam('achievement');
        $student_id = $fn->getReqParam('student_id');
        $noticeRec   = $fn->getRecordRowById('notice', 'notice_id', $notice_id);

        $sqlAppend = '';
        $student_name = '';

        if ($student_id != '') {
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id)
            WHERE sa.student_id = {$student_id}
            AND sa.notice_id = {$notice_id}
            ";
            $studentRec = $fn->getRecordRowByID('student', 'student_id', $student_id);
            $student_name = $studentRec['first_name'] . ' '. $studentRec['last_name'];
        }
        else{
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id AND sa.notice_id = {$notice_id})
            ORDER BY sa.achievement_id DESC, a.achievement_id
            ";
        }

        $SQL = "
        SELECT DISTINCT sa.achievement_id AS student_acheivement_id
        , a.*
        FROM achievement a
    	{$sqlAppend}
        ";

        /*
        if ($student_id != '') {
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id)
            WHERE sa.student_id = {$student_id}
            AND sa.notice_id = {$notice_id}
            ";
            $studentRec = $fn->getRecordRowByID('student', 'student_id', $student_id);
            $student_name = $studentRec['first_name'] . ' '. $studentRec['last_name'];
        }

         $SQL = "
        SELECT a.*
        FROM achievement a
    	{$sqlAppend}
        ";
        */

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {

	        $hostorySQL = "
	        SELECT sa.*
	        FROM achievement_student sa
        	WHERE sa.notice_id = {$notice_id}
        	AND sa.achievement_id = {$row['achievement_id']}
        	";
        	$resultHistory  = $db->sql_query($hostorySQL);
            $numRows = $db->sql_numrows($resultHistory);
            $achievemnentIdIcon = "";
            $arrow = '';

            if ($numRows) {
                if ($student_id != '') {
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
                    </div>
                    ";
                }
                else{
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <a href='#' class='achievementLinkedArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
                        </a>
                    </div>
                    ";
                }

                $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$notice_id}&achievement_id={$row['achievement_id']}";
    			$title = "
                {$row['number']} {$row['title']}
    			";
                $arrow = "
                <a href='{$achievemnentUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-arrow.png'>
                </a>
                ";
            } else {

                if ($student_id != '') {
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png'>
                    </div>
                    ";
                }
                else{
                    if ($row['category'] != 'Heading') {
                        $achievemnentIdIcon = "
                        <div class = ''>
                            <a href='#' class='achievementLinkArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png'>
                            </a>
                        </div>
                        ";
                    }
                }

    			$title = "
    			{$row['number']} {$row['title']}
    			";
            }
            if ($row['category'] == 'Heading') {
                $rows .= "
                <tr class='tdbg'>
                    <td></td>
                    {$listObj->getListDataCell($title)}
                    <td></td>
                </tr>
                ";
            }
            else{
                $class='';
                $rows .= "
                <tr class=''>
                    <td>{$achievemnentIdIcon}</td>
                    {$listObj->getListDataCell($title)}
                    <td>{$arrow}</td>
                </tr>
                ";
            }


            $rowCounter++ ;
        }

        //$noticeEditUrl = '/'. "controller/notice/?_action=edit&notice_id={$notice_id}";
        $noticeEditUrl = '/'. "controller/notice/edit/{$notice_id}/";
        $backToNotice = "
        <a href='{$noticeEditUrl}'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/return-audience.png'>
        </a>
        ";

        $exp = array('style' => '', 'folder' => 'thumb', 'showCaption' => 0);
        $pic = $media->getMediaPicture('edukite_student', 'picture', $student_id, $exp);

        //<div class='backToNotice'>{$backToNotice}</div>
        $text = "
        <div class='downloadImg'><h3>$student_name</h3></div>
        {$this->getAchievementSearch($row['achievement_id'], $notice_id)}
        <div class='studentImage'>{$pic}</div>
        <div class='achievementListView'>
            <div class='noticeTitle'>{$noticeRec['title']}</div>
            {$listObj->getListHeader()}
            <th></th>
            {$listObj->getListHeaderCell('', 'a.title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
        </div>
        ";

        return $text;

	}

     /**
     *
     */
    function getAchievementPanel(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');

        $notice_id   = $fn->getReqParam('notice_id');
        $achievement = $fn->getReqParam('achievement');
        $student_id = $fn->getReqParam('student_id');
        $noticeRec   = $fn->getRecordRowById('notice', 'notice_id', $notice_id);

        $sqlAppend = '';
        $student_name = '';

        $SQL = "
        SELECT DISTINCT a.*
        FROM achievement a
        WHERE a.category = 'Heading'
        AND a.category_id IS NULL
        AND a.sub_category_id IS NULL
        ORDER BY a.achievement_id
        ";
        $resultHead  = $db->sql_query($SQL);
        $numRowsHead = $db->sql_numrows($resultHead);

        $rows  = "";
        $rowCounter = 0;
        $achievement_id = '';
        $selcRows  = '';
        //--------------------------------------------------------------------------//
        while ($rowHead = $db->sql_fetchrow($resultHead)) {

            $sqlAppend = '';
            $student_name = '';
            $sqlAppendSR = "";

            if ($student_id != '') {
                $sqlAppend = "
                LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id)
                WHERE sa.student_id = {$student_id}
                AND sa.notice_id = {$notice_id}
                AND a.category = 'Heading'
                AND a.category_id = {$rowHead['achievement_id']}
                AND a.sub_category_id IS NULL
                ";
                $studentRec = $fn->getRecordRowByID('student', 'student_id', $student_id);
                $student_name = $studentRec['first_name'] . ' '. $studentRec['last_name'];
                $sqlAppendSR = "
                    AND achHist.student_id = {$student_id}
                ";

            }
            else{
                $sqlAppend = "
                LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id AND sa.notice_id = {$notice_id})
                WHERE a.category = 'Heading'
                AND a.category_id = {$rowHead['achievement_id']}
                AND a.sub_category_id IS NULL
                ORDER BY sa.achievement_id DESC, a.achievement_id
                ";
            }

            $SQLSelectedRows = "
            SELECT
                DISTINCT achHist.achievement_id
                ,ach.title
                ,ach.number
            FROM achievement_student achHist
            LEFT JOIN achievement ach ON (achHist.achievement_id = ach.achievement_id)
            WHERE achHist.notice_id = {$notice_id}
            {$sqlAppendSR}
            ";

            $resultSelectedRows  = $db->sql_query($SQLSelectedRows);
            $numRowsSelected = $db->sql_numrows($resultSelectedRows);
            $selcRows  = '';
            $achievement_id_stud = '';
            $count = 1;

            if($numRowsSelected){
                $selcRows .="
                <tr class='subCategoryTitleEnd'>
                    <td colspan=3><b>OUTCOMES SELECTED</b></td>
                </tr>
                ";
            }

            while ($rowSelected = $db->sql_fetchrow($resultSelectedRows)) {
                $achievement_id_stud = $rowSelected['achievement_id'];
                $achievemnentIdIcon = "
                <div class = ''>
                    <a href='#' class='achievementLinkedArrow' achievement_id={$rowSelected['achievement_id']} notice_id={$notice_id}>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/ach_remove.png'>
                    </a>
                </div>
                ";

                $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$notice_id}&achievement_id={$rowSelected['achievement_id']}";
                $title = "
                {$rowSelected['number']} {$rowSelected['title']}
                ";
                $arrow = "
                <a href='{$achievemnentUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-arrow.png'>
                </a>
                ";

                $selcRows .="
                <tr class='subCategoryTitle'>
                    <td>{$achievemnentIdIcon}</td>
                    {$listObj->getListDataCell($title)}
                    <td>{$arrow}</td>
                </tr>
                ";
            }

            if($numRowsSelected && $student_id == ''){

                $selcRows .="
                <tr class='outcomesNote'>
                    <td colspan=3>
                        <b>NOTE :</b>
                        When all outcomes for this activity have been selected & displayed
                        above, proceed to the the Outcomes Audience panel by clicking the blue
                        arrow to the right of the outcome.
                        Choose the children to be connected to the outcome, then click <b>‘Return
                        to EYLF Learning Outcomes’</b> to select the next outcome on the list.
                    </td>
                </tr>
                <tr class='subCategoryTitleEnd'>
                    <td colspan=3><b>OUTCOMES</b></td>
                </tr>
                ";
            }

            $SQL = "
            SELECT DISTINCT sa.achievement_id AS student_acheivement_id
            , a.*
            FROM achievement a
            {$sqlAppend}
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            $inlineStyle  = '';

            $countHead = 1;

            while ($row = $db->sql_fetchrow($result)) {
                $achievement_id = $row['achievement_id'];

                $title = "
                {$row['title']}
                ";
                $class='';
                $classColor = '';

                $classColor = $this->getAchievementHistoryRecord($notice_id, $achievement_id);
                if($classColor == 'Yes') {
                    $inlineStyle = "style='background:#E5C9D7;font-weight:bold'";
                    $achSubHeadImg = "
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-subhead-linked.png'>
                    ";
                }
                else{
                    $inlineStyle = "style=''";
                    $achSubHeadImg = "
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-subhead.png'>
                    ";
                }

                if($countHead == 1){
                    $classColorTick = $this->getAchievementHeadHistoryRecord($notice_id, $row['category_id']);
                    if($classColorTick == 'Yes') {
                        $achHeadImg = "
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-head-linked.png'>
                        ";
                    } else {
                        $achHeadImg = "
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-head.png'>
                        ";
                    }

                    $rows .= "
                    <tr class='tdbg categoryHeadTitle'>
                        <td colspan=3>
                            <div class='categoryHead' achievement_id = '{$achievement_id}' notice_id = '{$notice_id}' student_id = '{$student_id}'>
                                {$rowHead['title']}
                            </div>
                        </td>
                    </tr>
                    ";
                }

                $rows .= "
                <tr class='subCategoryOutcomes'>
                    <td colspan=3 style='padding:0'>
                        <table class='categorySubCategoryLink'>
                            <tr class='categoryTitle' {$inlineStyle}>
                                <td colspan=3>
                                    <div class='category category_{$achievement_id} floatbox' achievement_id = '{$achievement_id}' notice_id = '{$notice_id}' student_id = '{$student_id}'>
                                        <div class=''>{$title}</div>
                                    </div>
                                </td>
                            </tr>
                            <tr class='categorySubCategoryLink2'>
                                <td colspan=3 style='padding:0'>
                                    <table class='subCategory' width='100'>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                ";

                $rowCounter++ ;
                $countHead++;
            }
        }

        //$noticeEditUrl = '/'. "controller/notice/?_action=edit&notice_id={$notice_id}";
        $noticeEditUrl = '/'. "controller/notice/edit/{$notice_id}/";
        $backToNotice = "
        <a href='{$noticeEditUrl}'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/return-audience.png'>
        </a>
        ";

        $exp = array('style' => '', 'folder' => 'thumb', 'showCaption' => 0);
        $pic = $media->getMediaPicture('edukite_student', 'picture', $student_id, $exp);
        if ($student_id != '') {
            $rows= '';
        }

        $text = "
        <div class='backToNotice'>{$backToNotice}</div>
        <div class='downloadImg'><h3>$student_name</h3></div>
        {$this->getAchievementSearch($achievement_id, $notice_id)}
        <div class='studentImage'>{$pic}</div>
        <div class='achievementListView'>
            <div class='noticeTitle'>{$noticeRec['title']}</div>
            {$listObj->getListHeader()}
            <th></th>
            {$listObj->getListHeaderCell('', 'a.title')}
            {$listObj->getListHeaderEnd()}
            {$selcRows}
            {$rows}
        </div>
        ";

        return $text;

    }

     /**
     *
     */
    function getAchievementSubCategoryDisplay(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');

        $notice_id   = $fn->getReqParam('notice_id');
        $achievement = $fn->getReqParam('achievement');
        $student_id = $fn->getReqParam('student_id');
        $noticeRec   = $fn->getRecordRowById('notice', 'notice_id', $notice_id);
        $sub_category_id = $fn->getReqParam('achievement_id');

        $sqlAppend = '';
        $student_name = '';

        if ($student_id != '') {
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id)
            WHERE sa.student_id = {$student_id}
            AND sa.notice_id = {$notice_id}
            AND a.sub_category_id = {$sub_category_id}
            ";
            $studentRec = $fn->getRecordRowByID('student', 'student_id', $student_id);
            $student_name = $studentRec['first_name'] . ' '. $studentRec['last_name'];
        }
        else{
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id AND sa.notice_id = {$notice_id})
            WHERE a.sub_category_id = {$sub_category_id}
            ORDER BY sa.achievement_id DESC, a.achievement_id
            ";
        }

        $SQL = "
        SELECT DISTINCT sa.achievement_id AS student_acheivement_id
        , a.*
        FROM achievement a
        {$sqlAppend}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {

            $hostorySQL = "
            SELECT sa.*
            FROM achievement_student sa
            WHERE sa.notice_id = {$notice_id}
            AND sa.achievement_id = {$row['achievement_id']}
            ";
            $resultHistory  = $db->sql_query($hostorySQL);
            $numRows = $db->sql_numrows($resultHistory);
            $achievemnentIdIcon = "";
            $arrow = '';

            if ($numRows) {
                if ($student_id != '') {
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
                    </div>
                    ";
                }
                else{
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <a href='#' class='achievementLinkedArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-linked.png'>
                        </a>
                    </div>
                    ";
                }

                $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$notice_id}&achievement_id={$row['achievement_id']}";
                $title = "
                {$row['number']} {$row['title']}
                ";
                $arrow = "
                <a href='{$achievemnentUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-arrow.png'>
                </a>
                ";
            } else {

                if ($student_id != '') {
                    $achievemnentIdIcon = "
                    <div class = ''>
                        <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png'>
                    </div>
                    ";
                }
                else{
                    if ($row['category'] != 'Heading') {
                        $achievemnentIdIcon = "
                        <div class = ''>
                            <a href='#' class='achievementLinkArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png'>
                            </a>
                        </div>
                        ";
                    }
                }

                $title = "
                {$row['number']} {$row['title']}
                ";
            }
            $rows .= "
            <tr class='subCategoryTitle'>
                <td>{$achievemnentIdIcon}</td>
                {$listObj->getListDataCell($title)}
                <td>{$arrow}</td>
            </tr>
            ";
            $rowCounter++ ;
        }

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
     */
    function getAchievementHistoryRecord($notice_id, $achievement_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');
        $db  = Zend_Registry::get('db');
        $text = "";

        $SQLSH = "
        SELECT ach.*
        FROM achievement ach
        WHERE ach.sub_category_id = {$achievement_id}
        ";
        $resultSH  = $db->sql_query($SQLSH);

        while ($rowSH = $db->sql_fetchrow($resultSH)) {
            $SQLHist = "
            SELECT achHist.*
            FROM achievement_student achHist
            WHERE achHist.notice_id = {$notice_id}
            AND achHist.achievement_id = {$rowSH['achievement_id']}
            ";
            $resultHist  = $db->sql_query($SQLHist);
            $numRowsHist = $db->sql_numrows($resultHist);
            if ($numRowsHist > 0){
                $text = "Yes";
            }
        }


        return $text;
    }

    /**
     *
     */
    function getAchievementSubHistoryRecord($notice_id, $achievement_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');
        $db  = Zend_Registry::get('db');
        $text = "";

        $SQLSH = "
        SELECT ach.*
        FROM achievement ach
        WHERE ach.achievement_id = {$achievement_id}
        ";
        $resultSH  = $db->sql_query($SQLSH);

        while ($rowSH = $db->sql_fetchrow($resultSH)) {
            $SQLHist = "
            SELECT achHist.*
            FROM achievement_student achHist
            WHERE achHist.notice_id = {$notice_id}
            AND achHist.achievement_id = {$rowSH['achievement_id']}
            ";
            $resultHist  = $db->sql_query($SQLHist);
            $numRowsHist = $db->sql_numrows($resultHist);
            if ($numRowsHist > 0){
                $text = "Yes";
            }
        }


        return $text;
    }

    /**
     *
     */
    function getAchievementHeadHistoryRecord($notice_id, $category_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');
        $db  = Zend_Registry::get('db');
        $text = "";

        $SQLSH = "
        SELECT ach.*
        FROM achievement ach
        WHERE ach.category_id = {$category_id}
        ";
        $resultSH  = $db->sql_query($SQLSH);

        while ($rowSH = $db->sql_fetchrow($resultSH)) {
            $SQL ="
            SELECT ach.*
            FROM achievement ach
            WHERE ach.sub_category_id = {$rowSH['achievement_id']}
            ";
            $result  = $db->sql_query($SQL);
            while ($row = $db->sql_fetchrow($result)) {
                $SQLHist = "
                SELECT achHist.*
                FROM achievement_student achHist
                WHERE achHist.notice_id = {$notice_id}
                AND achHist.achievement_id = {$row['achievement_id']}
                ";
                $resultHist  = $db->sql_query($SQLHist);
                $numRowsHist = $db->sql_numrows($resultHist);
                if ($numRowsHist > 0){
                    $text = "Yes";
                }
            }
        }

        return $text;
    }


    /**
     *
     */
    function getAchievementSearch($achievement_id, $notice_id) {
        $ln  = Zend_Registry::get('ln');
        $fn  = Zend_Registry::get('fn');

        $text = "
        <div id='achievementSearch'>
            <form name='search' action='' id='frmKeyword'>
                <div class='floatbox'>
                    <div class='float_left keywordWrap'>
                        <input type='text' class='achievement' name='achievement' id='achievement' value=''>
                    </div>
                    <div class='float_left btnSubmit'>
                       <a href='#' class='submit' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                           <img src='/cmspilotv30/CP/www/themes/Manager/images/find.png'>
                       </a>
                    </div>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

     /**
     *
     */
    function getAchievementDisplayAfterSearch(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');

        $notice_id   = $fn->getReqParam('notice_id');
        $achievement = $fn->getReqParam('achievement');
        $noticeRec   = $fn->getRecordRowById('notice', 'notice_id', $notice_id);

        $sqlAppend = '';
        $student_id = $fn->getReqParam('student_id');

        if ($student_id != '') {
            $sqlAppend = "
            LEFT JOIN achievement_student sa ON (sa.achievement_id = a.achievement_id)
            WHERE sa.student_id = {$student_id}
            AND sa.notice_id = {$notice_id}
            ";
        }

        $sqlAppend1 = '';
        if($achievement != ''){
            $sqlAppend1 = "
            WHERE (a.title LIKE '%{$achievement}%'
            OR a.achievement_code LIKE '%{$achievement}%')
            ";
        }

        $SQL = "
        SELECT a.*
        FROM achievement a
    	{$sqlAppend1}
    	{$sqlAppend}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {

	        $hostorySQL = "
	        SELECT sa.*
	        FROM achievement_student sa
        	WHERE sa.notice_id = {$notice_id}
        	AND sa.achievement_id = {$row['achievement_id']}
        	";
        	$resultHistory  = $db->sql_query($hostorySQL);
            $numRows = $db->sql_numrows($resultHistory);

            if ($numRows) {

                $achievemnentIdIcon = "
    			<div class = ''>
                    <a href='#' class='achievementLinkedArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-linked.png'>
    				</a>
    			</div>
                ";

                $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$notice_id}&achievement_id={$row['achievement_id']}";
    			$title = "
                <a href='{$achievemnentUrl}'>{$row['title']}</a>
    			";
            } else {

                $achievemnentIdIcon = "
    			<div class = ''>
                    <a href='#' class='achievementLinkArrow' achievement_id={$row['achievement_id']} notice_id={$notice_id}>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-not-linked.png'>
    				</a>
    			</div>
                ";

    			$title = "
    			{$row['title']}
    			";
            }

            $rows .= "
            <tr>
                <td>{$achievemnentIdIcon}</td>
                {$listObj->getListDataCell($row['achievement_code'])}
                {$listObj->getListDataCell($title)}
            </tr>
            ";

            $rowCounter++ ;
        }

        $noticeEditUrl = '/'. "controller/notice/?_action=edit&notice_id={$notice_id}";
        $backToNotice = "
        <a href='{$noticeEditUrl}'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/return-audience.png'>
        </a>
        ";

        $text = "
        <div class='backToNotice'>{$backToNotice}</div>
        <div class='achievementListView'>
            <div class='noticeTitle'>{$noticeRec['title']}</div>
            {$listObj->getListHeader()}
            <th></th>
            {$listObj->getListHeaderCell('', 'achievement_code')}
            {$listObj->getListHeaderCell('', 'a.title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
        </div>
        ";

        return $text;


	}

     /**
     *
     */

    function getNoticeReadSummary(){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $notice_id  = $fn->getReqParam('notice_id');
        $row ='';
        $text ='';

        $SQL = "
        SELECT np.*
              ,n.launch_date
              ,n.title
              ,CONCAT_WS(' ', p.first_name, p.last_name) AS parent_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice_parent np
        LEFT JOIN parent p ON (p.parent_id = np.parent_id)
        LEFT JOIN student s ON (s.student_id = np.student_id)
        LEFT JOIN (notice n) ON (n.notice_id = np.notice_id)
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        WHERE np.notice_id = {$notice_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $row2 = $db->sql_fetchrow($result2);

        $title ="
        <div class='noticeTitleForParentSummary'>
        Title: {$row2['title']}
        </div>
        ";

        $launch_date = $fn->getCPDate($row2['launch_date'], 'F jS Y');
        $notice ="
        <div class='floatbox'></br>
            <div class='float_left'>
                <b>BY : <span>{$row2['teacher_name']}</span></b>
            </div>
            <div class='float_right'>
                <b>Dated : <span>{$launch_date}</span></b>
            </div>
        </div>
        ";

        $AudienceCount = $db->sql_numrows($result);
        //$AudienceCount = $fn->getRecordCount('notice_student', "notice_id = {$notice_id} AND student_id != '' OR student_id != NULL");
        $ReadCount = $fn->getRecordCount('notice_parent', "notice_id = {$notice_id} AND viewed_tag = 1");
        $NotReadCount = $fn->getRecordCount('notice_parent', "notice_id = {$notice_id} AND (viewed_tag IS NULL OR viewed_tag = 0)");

        $Audience ="
        <div class='floatbox'>
            <div class='float_left'></br>
                Total Audience : <span>{$AudienceCount}</span>
                | Notice Read : <span>{$ReadCount}</span>
                | Not Read : <span>{$NotReadCount}</span>
            </div>
        </div>
        ";


        $header ="
        <table class='thinlist'></br>
            <tr class='studentParentSummaryTr'>
                <th>Parent Name</th>
                <th>Student Name</th>
                <th>Notice Read</th>
                <th>Date</th>
            </tr>
        ";

        $count=1;
        $rows ='';
        while ($row = $db->sql_fetchrow($result)) {
            if($row['viewed_tag'] == ''){
                $row['viewed_tag'] = 'NO';
            }else{
                $row['viewed_tag'] = 'YES';
            }

            if ($count % 2 == 0) {
              $backgroundADEV = "background-color:#E9EDF4";
            }else{
              $backgroundADEV = "background-color:#D0D8E8";
            }

        $creation_date = $fn->getCPDate($row['read_date'], 'd-m-Y');

            $rows .= "
            <tr style='{$backgroundADEV}'>
                <td>{$row['parent_name']}</td>
                <td>{$row['student_name']}</td>
                <td class='noticeReadStatus'>{$row['viewed_tag']}</td>
                <td>$creation_date</td>
            </tr>
            ";
            $count++;
        }

        if($AudienceCount == 0){
            $text = "<div class='noticeTitleForParentSummary'>There are no students linked to this notice, please link students to notice</div>";
        }
        else{
            $text = "
            <div class='row'>
                    <div>{$title}</div>
                    <div>{$notice}</div>
                    <div>{$Audience}</div>
                <table class='thinlist'>
                    <tr>
                        {$header}
                    </tr>
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

     /**
     *
     */

    function getHomeworkSummary(){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpUrl = Zend_Registry::get('cpUrl');

        $notice_id  = $fn->getReqParam('notice_id');
        $row ='';
        $text ='';

        $SQL = "
        SELECT ts.*
              ,n.launch_date
              ,n.title
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
              ,(SELECT COUNT(sc.student_id)
                FROM student_comment_history sc
                WHERE sc.teacher_id IS NULL
                  AND sc.record_id = {$notice_id}
                  AND sc.task_student_id = ts.task_student_id)AS comment_count
        FROM task_student ts
        LEFT JOIN student s ON (s.student_id = ts.student_id)
        LEFT JOIN (notice n) ON (n.notice_id = ts.task_id)
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (student_comment_history sc) ON (sc.task_student_id = ts.task_student_id)
        WHERE ts.task_id = {$notice_id}
          AND ts.creation_date IS NOT NULL
        GROUP BY sc.student_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $row2 = $db->sql_fetchrow($result2);

        $title ="
        <div class='noticeTitleForParentSummary'>
        Title: {$row2['title']}
        </div>
        ";

        $launch_date = $fn->getCPDate($row2['launch_date'], 'F jS Y');
        $notice ="
        <div class='floatbox'></br>
            <div class='float_left'>
                <b>BY : <span>{$row2['teacher_name']}</span></b>
            </div>
            <div class='float_right'>
                <b>Dated : <span>{$launch_date}</span></b>
            </div>
        </div>
        ";

        $AudienceCount = $db->sql_numrows($result);
        $ResponseCount = $fn->getRecordCount('student_comment_history', "record_id = {$notice_id} AND record_type = 'edukite_student'");
        $UploadCount = $fn->getRecordCount('task_student', "task_id = {$notice_id} AND creation_date IS NOT NULL");

        $Audience ="
        <div class='floatbox'>
            <div class='float_left'></br>
                Total Audience : <span>{$AudienceCount}</span>
                | Kite Chat Responses : <span>{$ResponseCount}</span>
                | Uploaded By Students : <span>{$UploadCount}</span>
            </div>
        </div>
        ";

        $header ="
        <table class='thinlist'></br>
            <tr class='studentParentSummaryTr'>
                <th>Student Name</th>
                <th>Homework Uploaded</th>
                <th>Kite Chat</th>
                <th>View</th>
            </tr>
        ";

        $count=1;
        $rows ='';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count % 2 == 0) {
              $backgroundADEV = "background-color:#E9EDF4";
            }else{
              $backgroundADEV = "background-color:#D0D8E8";
            }

            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y');

            $urlArray = array();
            $urlArray['siteType'] = 'kite';
            $secRec = getCPModelObj('webBasic_section')->getRecordByType('Kite Task');
            $urlArray['section_title'] = $secRec['title'];
            $urlArray['sitePfxId'] = $row['student_id'];
            $urlArray['record_id'] = $notice_id;
            $urlArray['record_title'] = $row['title'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $viewCommentUrl = '';
            $viewComment = "<a href='#' class='viewCommentChat' notice_id='{$row['task_id']}' student_id='{$row['student_id']}' task_student_id='{$row['task_student_id']}'>View Chat ({$row['comment_count']})</a>";

            $SQLStuComHis="
            SELECT student_comment_history_id
            FROM student_comment_history
            WHERE unread_tag IS NULL
              AND record_id = {$notice_id}
              AND student_id = '{$row['student_id']}'
            ";
            $resultStuComHis = $db->sql_query($SQLStuComHis);
            $numRows = $db->sql_numrows($resultStuComHis);

            $color ='';
            if ($numRows > 0) {
                $color = 'bgRed';
            }

            $rows .= "
            <tr style='{$backgroundADEV}'>
                <td>{$row['student_name']}</td>
                <td>{$creation_date}</td>
                <td class='noticeReadStatus {$color}'>{$viewComment}</td>
                <td>
                    <a href='{$kiteUrl}' class='studentInStudentLink' student_id='{$row['student_id']}' target='_blank'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                    </a>
                </td>
            </tr>
            ";
            $count++;
        }

        if($AudienceCount == 0){
            $text = "<div class='noticeTitleForParentSummary'>Please be informed that we have not yet received any response from students.</div>";
        }
        else{
            $text = "
            <div class='row'>
                    <div>{$title}</div>
                    <div>{$notice}</div>
                    <div>{$Audience}</div>
                <table class='thinlist'>
                    <tr>
                        {$header}
                    </tr>
                    {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

     /**
     *
     */
    function getViewCommentHistory(){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $notice_id  = $fn->getReqParam('notice_id');
        $student_id  = $fn->getReqParam('student_id');
        $task_student_id  = $fn->getReqParam('task_student_id');

        $text = '';

        $modObj = getCPModuleObj('edukiteWeb_task');

        $addComment = $modObj->view->getAddComment($notice_id, $student_id, $task_student_id);
        $displayComment = $modObj->view->getDisplayComment($notice_id, $student_id, $task_student_id);

        $text="
        <div class='myChat'>
            {$addComment}
            <div class='commentDisplay'>
                {$displayComment}
            </div>
        </div>
        ";

        return $text;
    }

     /**
     *
     */
    function getCreateGalleryRecordForStudent(){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //http://edukitedev.localhost/index.php?module=edukite_notice&_spAction=createGalleryRecordForStudent&showHTML=0
        $sql = "
        SELECT student_id
        FROM student
        WHERE status = 'Active'
        ";
        $resultStudent  = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($resultStudent)) {
            $fa = array();
            $fa['title'] = 'MY ARTWORK';
            $fa['description'] = 'In this album you will find artwork that I have created during my time at SCBC Child Care Centre.';
            $fa['teacher_id'] = 1;
            $fa['status'] = 'Active';
            $fa['launch_date'] = '2016-12-31';
            $fa['activity_date'] = date("Y-m-d");
            $fa['academic_year'] = '2016';
            $fa['template'] = 'Gallery';
            $fa['launch_now'] = '1';
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'notice');
            $insertResult = $db->sql_query($insertSQL);
            $notice_id    = $db->sql_nextid();

            $fa1 = array();
            $fa1['notice_id']     = $notice_id;
            $fa1['student_id']    = $row['student_id'];
            $fa1['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL1          = $dbUtil->getInsertSQLStringFromArray($fa1, 'notice_student');
            $insertResult1       = $db->sql_query($insertSQL1);
            $notice_student_id   = $db->sql_nextid();

            $this->getAddMedia($notice_id);
        }
    }

    function getAddMedia($id) {
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArray = Zend_Registry::get('mediaArray');
        $cpUtil = Zend_Registry::get('cpUtil');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        $module = 'edukite_notice';
        $recordType = 'picture';

        require_once("imageResize.php");
        $imageResize = new ImageResize();

        $text = "";
        set_time_limit(50000);
        ini_get('max_execution_time');
        ini_set('memory_limit', '512M');

        $lang = $fn->getReqParam('lang', 'eng');

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }
        $mediaArray = Zend_Registry::get('mediaArray');

        $keyFieldName = $modulesArr[$module]['keyField'];
        $mediaArr  = $mediaArray[$module][$recordType];
        $mediaType = $mediaArr['mediaType'];

        $imgQuality = 100;
        $hasWatermark  = $mediaArr['hasWatermark'];
        $watermarkLargeFontSize = '';
        $watermarkNormalFontSize = '';
        $watermarkMediumFontSize = '';
        $watermarkText = '';
        if ($hasWatermark) {
            $watermarkText = $mediaArr['watermarkText'];
            $watermarkLargeFontSize = $mediaArr['watermarkLargeFontSize'];

            $watermarkNormalFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthN'] / $mediaArr['maxWidthL']));
            $watermarkMediumFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthM'] / $mediaArr['maxWidthL']));
        }

        $expImgL = array(
            'hasWatermark' => $hasWatermark,
            'watermarkText' => $watermarkText,
            'watermarkFontSize' => $watermarkLargeFontSize,
        );
        $expImgN = $expImgL;
        $expImgM = $expImgL;

        $expImgN['watermarkFontSize'] = $watermarkNormalFontSize;
        $expImgM['watermarkFontSize'] = $watermarkMediumFontSize;


        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';

            $sourceFile        = $outputPath . '\ART PALLETTE.png';
            $mediaSize         = '497877';
            $actual_file_name  = 'ART PALLETTE.png';
            $contentType       = 'image/png';

            if ($actual_file_name == '' || strpos($actual_file_name, '/') !== false ||
                $actual_file_name == '..'){
                continue;
            }

            $mediaSizeMB = $mediaSize/1024/1024;
            if ($mediaSizeMB > $cpCfg['cp.maxUploadLimit']) {
                print 'Error: File size is too big';
                return;
            }

            $fa = array();
            $fa['media_type']       = 'image';
            $fa['actual_file_name'] = $actual_file_name;
            $fa['content_type']     = $contentType;
            $fa['media_size']       = $mediaSize;
            $fa['room_name']        = $module;
            $fa['record_type']      = $recordType;
            $fa['lang']             = $lang;
            $fa['record_id']        = $id;
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['alt_tag_data']     = 'ART PALLETTE';
            $fa['sort_order']       = 1;

            if ($cpCfg['cp.dbType'] == 'mysqlpdo') {
                //$SQL is an arr
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'media', array(), true);
            } else {
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
            }

            $result      = $db->sql_query($SQL);
            $media_id    = $db->sql_nextid();

            if ($media_id == "") {
                return;
            }

            $file_name = $media_id . "_" . $cpUtil->fixFileName($actual_file_name);

            $fa = array();
            $fa['file_name'] = $file_name;

            if ($cpCfg['cp.dbType'] == 'mysqlpdo') {
                $whereCondition = "WHERE media_id = :media_id";
                $paramArr = array(':media_id' => $media_id);
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition, array(), true, $paramArr);
            } else {
                $whereCondition = "WHERE media_id = {$media_id}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
            }

            $result         = $db->sql_query($SQL);

            /*$destinationFile = $mediaArray["tempFolder"] . $actual_file_name;
            $result = move_uploaded_file($sourceFile, $destinationFile);*/

            //if ($mediaArr["resize"]) {
                $tempFile    = $mediaArray["tempFolder"] . $actual_file_name;
                $result      =  move_uploaded_file($sourceFile, $tempFile);

                if (array_key_exists("thumbFolder", $mediaArr)) {
                    $dest = $mediaArr["thumbFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthT"], $mediaArr["maxHeightT"],
                                                   $imgQuality);
                }

                if (array_key_exists("mediumFolder", $mediaArr)) {
                    $dest = $mediaArr["mediumFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthM"], $mediaArr["maxHeightM"],
                                                   $imgQuality, $expImgM);
                }

                if (array_key_exists("normalFolder", $mediaArr)) {
                    $dest = $mediaArr["normalFolder"] . $file_name;
                    $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthN"], $mediaArr["maxHeightN"],
                                                   $imgQuality, $expImgN);
                }

                if (array_key_exists("largeFolder", $mediaArr)) {
                    $dest = $mediaArr["largeFolder"] . $file_name;


                    if ($mediaArr['doNotResizeLargeImage']) {
                        if (!copy($tempFile, $dest)) {
                            // not copied
                        }
                    } else {
                        $imageResize->imageCreateThumb($tempFile, $dest, $mediaArr["maxWidthL"], $mediaArr["maxHeightL"],
                                                       $imgQuality, $expImgL);
                    }
                }

            /*} else {
                $destinationFile = $mediaArr["normalFolder"] . $file_name;
                $result = move_uploaded_file($sourceFile, $destinationFile);
            }*/

        return 'success';
    }
}