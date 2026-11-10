<?
class CP_Www_Modules_Edukite_Student_View extends CP_Common_Modules_Edukite_Student_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $searchHTML = Zend_Registry::get('searchHTML');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn    = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $db    = Zend_Registry::get('db');

        $rows  = "";
        $rowCounter = 0;

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        $teacherRole = $teacherRec['role'];
        $status = $fn->getReqParam('status');
        if($status == ''){
            $status = 'Active';
        }

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);
            $kiteUrl = $kiteUrl . '?status='.$status;

            if($teacherRole == 'Teacher'){
                $editIcon = "
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/edit.png'>
                ";
                $student_name = $listObj->getListDataCell($row['student_name']);
            } else {
                $editIcon = "
                <a href='/controller/student/edit/{$row['student_id']}/' class='editIcon'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/edit.png'>
                </a>
                ";
                $student_name = $listObj->getGoToDetailText($rowCounter, $row['student_name'], '', '', $row);
            }

	        if ($cpCfg['showAcheivement'] == 1){
		        $SQL = "
		        SELECT sa.*
		        FROM achievement_student sa
	        	WHERE sa.student_id = {$row['student_id']}
				";

	        	$result  = $db->sql_query($SQL);
	            $numRows = $db->sql_numrows($result);

                $studentAchievemnentUrl = $cpUrl->getUriWithNoQstr() . "?_action=achievementOptions&student_id={$row['student_id']}";
	            if ($numRows) {
	                $studentAchievement = "
					<td align='right'>
					   <a href='{$studentAchievemnentUrl}'>
		                <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
		               </a>
					</td>
					";
				} else {
	                $studentAchievement = "
					<td></td>
					";
				}
			} else {
	                $studentAchievement = "
					<td></td>
					";
		    }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($editIcon)}
            {$student_name}
			{$studentAchievement}
            <td align='right'>
                <a href='{$kiteUrl}' class='kiteIcon'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                </a>
            </td>

            ";
            $rowCounter++ ;
        }

        $statusLink = '';
        $archiveLinkUrl = '?status=Archive';
        $activeLinkUrl = '?status=Active';
        if($status != 'Archive'){
            $statusLink = "<a href='{$archiveLinkUrl}' id='archiveLink' class='archive'>View Archive Records</a>";
            $_SESSION['record_status'] = 'Active';
        } else {
            $statusLink = "<a href='{$activeLinkUrl}' id='archiveLink'  class='active'>View Active Records</a>";
            $_SESSION['record_status'] = 'Archive';
        }
        $downloadLink = '';

        if($cpCfg['showAcheivement'] == 1){
            $downloadLinkUrl = '?_action=printAchievementAsPdfForAllStudent&showHTML=0';
            $downloadLink = "<a href='{$downloadLinkUrl}' id='' class='active'>Download All Outcomes</a>";
        }

        $text = "
        <div class='studentList'>
            <div class='archiveLink'>{$statusLink}</div>
            <div class='downloadLink'>{$downloadLink}</div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('', 'student_name')}
            {$listObj->getListHeaderCell('Name', 'last_name')}
            <th></th>
            {$listObj->getListHeaderEnd()}
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

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Family Name', 'last_name')}
        {$formObj->getTBRow('Reg.No', 'student_code')}
        {$formObj->getTBRow('Username', 'username')}
        {$formObj->getTBRow('Password', 'pass_word')}
        {$formObj->getTARow('Notes', 'pass_word')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $expCountry = array('detailValue' => $row['country']);

        $expVl   = array('sqlType' => 'OneField');
        $sqlType = $fn->getValueListSQL('status');
        $gendArr = array('Male', 'Female');
        $expNotes   = array('rowCls' => 'textAreaDiv');

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        $urlArray['sitePfxId'] = $row['student_id'];
        $kiteUrl = $cpUrl->make_seo_url($urlArray);
        $gendArr = array('Male', 'Female');
        $status = '';

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        if($teacherRec['role'] == 'Kite Master'){
            $status ="
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.edukite.statusArr'], $row['status'])}
            ";
        }

        $fieldset1 = "
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_student', 'picture', $row)}
        <div class='studentFieldset'>
            {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
            {$formObj->getTBRow('Second Name', 'known_as_name', $row['known_as_name'])}
            {$formObj->getTBRow('Family Name', 'last_name', $row['last_name'])}
            {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
            {$formObj->getTBRow('Student Code', 'student_code', $row['student_code'])}
            {$formObj->getTBRow('Username', 'username', $row['username'])}
            {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
            {$status}
            {$formObj->getTARow('Notes', 'comments', $row['comments'], $expNotes)}
        </div>
		";

        //<img src='/cmspilotv30/CP/www/themes/Manager/images/gokite.gif'>
        $text = "
        <div class='goKite'><a href='{$kiteUrl}'></a></div>
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

		$year_group_id = $fn->getReqParam ('year_group_id');
		$class_id  	   = $fn->getReqParam ('class_id');
        $academic_year = date('Y');


        $sqlClass = "
        SELECT c.class_id, c.title
        FROM class c
        WHERE c.status = 'Active'
        ORDER BY c.title
        ";

		$sqlCohort = "
		SELECT yg.year_group_id, yg.title
		FROM year_group yg
        WHERE yg.status = 'Active'
		ORDER BY yg.title
		";

        $text = "
        <div class='ddFilter'>
            <div class='floatbox'>
                <div class='float_left'>
                    <tr>
                        <select name='class_id'>
                            <option value=''>Class </option>
                            {$dbUtil->getDropDownFromSQLCols2($db,$sqlClass, $class_id)}
                        </select>
                    </tr>
                </div>
                <div class='float_left'>
                    <tr>
                        <select name='year_group_id'>
                            <option value=''>Cohort </option>
                            {$dbUtil->getDropDownFromSQLCols2($db,$sqlCohort, $year_group_id)}
                        </select>
                    </tr>
                </div>
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

        $student_id = $fn->getReqParam('record_id');

        $text = "
        <div class='btns'>
            <ul>
                <li>
                <a href='#' class='parentLinkInStudent' student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/parent-btn-active.png'></a>
                </a>
                </li>
                <li>
                <a href='#' class='classLinkInStudent'  student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/class-btn.png'></a>
                </li>
                <li>
                <a href='#' class='cohortLinkInStudent'  student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png'></a>
                </li>
            </ul>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getClassList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $student_id = $fn->getReqParam('student_id');

        $sqlClass = "
        SELECT c.class_id
              ,c.title
        FROM class c
        WHERE c.status = 'Active'
        ORDER BY c.title
        ";
        $result = $db->sql_query($sqlClass);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.class_id
            FROM class_student hisTble
            WHERE hisTble.student_id = {$student_id}
            AND class_id={$row['class_id']}
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
                <a href='#' class='classLinkArrow' class_id='{$row['class_id']}' student_id='{$student_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td class='txtRight'>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getLinkedClassList($student_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        if($student_id == ''){
            $student_id = $fn->getReqParam('student_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT lnktble.title
        ,hisTble.class_id
        FROM class_student hisTble
        LEFT JOIN (class lnktble) ON (hisTble.class_id = lnktble.class_id)
        WHERE hisTble.student_id = {$student_id}
        AND lnktble.status = 'Active'
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='classLinkDelete' class_id='{$row['class_id']}' student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                </td>
                <td class='txtRight'>
                    <span>{$row['title']}</span>
                </td>
            </tr>
            ";
        }
        if($numRows){
            $text = "
            <div class='row rightPanelSelected'>
                <div class='audienceTxt'></div>
                <table class='list'>
                {$rows}
                </table>
            </div>
            ";
        }

        return $text;
    }

    /**
     * Left Panel - list of Year Group available for Student
     */
    function getCohortList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $student_id = $fn->getReqParam('student_id');

        $sqlYearGroup = "
        SELECT yg.year_group_id
              ,yg.title
        FROM year_group yg
        WHERE yg.status = 'Active'
        ORDER BY yg.title
        ";
        $result = $db->sql_query($sqlYearGroup);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.year_group_id
            FROM student_year_group hisTble
            WHERE hisTble.student_id = {$student_id}
            AND year_group_id ={$row['year_group_id']}
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
                <a href='#' class='cohortLinkArrow' year_group_id='{$row['year_group_id']}' student_id='{$student_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td class='txtRight'>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }

    /**
     * Right Panel - list of Year Group linked to Student
     */
    function getLinkedCohortList($student_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        if($student_id == ''){
            $student_id = $fn->getReqParam('student_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT lnktble.title
        ,hisTble.year_group_id
        FROM student_year_group hisTble
        LEFT JOIN (year_group lnktble) ON (hisTble.year_group_id = lnktble.year_group_id)
        WHERE hisTble.student_id = {$student_id}
        AND lnktble.status = 'Active'
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='cohortLinkDelete' year_group_id='{$row['year_group_id']}' student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                </td>
                <td class='txtRight'>
                    <span>{$row['title']}</span>
                </td>
            </tr>
            ";
        }
        if($numRows){
            $text = "
            <div class='row rightPanelSelected'>
                <div class='audienceTxt'></div>
                <table class='list'>
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

        $student_id = $fn->getReqParam('student_id');
        $class_id  = $fn->getReqParam('class_id');

        $fa = array();
        $fa['student_id']    = $student_id;
        $fa['class_id']      = $class_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'class_student');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getLinkedClassList($student_id);

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
            }
        }

        $text = $this->getLinkedClassList($notice_id);

        return $text;
    }
    /**
     *
     --------- PARENT LINKING - LIST IN LEFT PANEL --------------------------------
     */
    function getParentList($student_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        if($student_id == ''){
            $student_id  = $fn->getReqParam('student_id');
        }

        $sqlParent = "
        SELECT p.parent_id
               ,CONCAT_WS(' ', p.first_name, p.last_name ) AS name
        FROM parent p
        WHERE p.status = 'Active'
        ORDER BY p.first_name
        ";
        $result     = $db->sql_query($sqlParent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.parent_id
            FROM student_parent hisTble
            WHERE hisTble.student_id = {$student_id}
            AND hisTble.parent_id = {$row['parent_id']}
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
                <a href='#' class='parentLinkArrow' parent_id='{$row['parent_id']}' student_id='{$student_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td class='txtRight'>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
    /**
     *
     ------------------ STUDENT LIST IN RIGHT PANEL ---------------------------------
     */
    function getLinkedParentList($student_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        if($student_id == ''){
            $student_id = $fn->getReqParam('student_id');
        }

        //to get parents which are linked
        $sqlLinked = "
        SELECT hisTble.parent_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM student_parent hisTble
        LEFT JOIN (parent lnktble) ON (hisTble.parent_id = lnktble.parent_id)
        WHERE hisTble.student_id = {$student_id}
        ORDER BY lnktble.first_name
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='parentLinkDelete' parent_id='{$row['parent_id']}' student_id='{$student_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                </td>
                <td class='txtRight'>
                    <span>{$row['name']}</span>
                </td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <div class='audienceTxt'></div>
            <table class='list'>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getLinkParentToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $parent_id   = $fn->getReqParam('parent_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['parent_id']     = $parent_id;
        $fa['student_id']    = $student_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'student_parent');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getLinkedParentList($student_id);

        return $text;
    }

    /**
     *
     --------- PARENT LINKING - LEFT PANEL DEFAULT CONTENT--------------------------------
     */
    function getLeftPanelDefaultContent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $student_id  = $fn->getReqParam('record_id');

        return $this->getParentList($student_id);
    }

    /**
     *
     ------------- PARENT LIST IN RIGHT PANEL DEFAULT CONTENT---------------------------------
     */
    function getRightPanelDefaultContent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        $student_id = $fn->getReqParam('record_id');

        return $this->getLinkedParentList($student_id);
    }

    /**
     * Adding a Year Group to Student
     */
    function getLinkCohortToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';

        $student_id     = $fn->getReqParam('student_id');
        $year_group_id  = $fn->getReqParam('year_group_id');

        $fa = array();
        $fa['student_id']    = $student_id;
        $fa['year_group_id'] = $year_group_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'student_year_group');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getLinkedCohortList($student_id);

        return $text;
    }

     /**
     *   ACHIEVEMENT THAT LINKED WITH STUDENT
     */
    function getAchievementOptions(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        $student_id  = $fn->getReqParam('student_id');
        $studentRec = $fn->getRecordRowByID('student', 'student_id', $student_id);
        $student_name = $studentRec['first_name'] . ' '. $studentRec['last_name'];

       $SQL = "
        SELECT DISTINCT sa.achievement_id
              ,sa.student_id
              ,sa.flag
              ,a.title AS achievement_title
        	  ,a.achievement_code
        	  ,n.notice_id
        FROM achievement_student sa
        LEFT JOIN (achievement a) ON (a.achievement_id = sa.achievement_id)
        LEFT JOIN (notice n) ON (sa.notice_id = n.notice_id)
        WHERE sa.student_id = {$student_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows  = "";
        $rowCounter = 0;
        $download = '';
        $noticeTitle = '';
        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        //--------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {

            /*
            $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$row['notice_id']}&achievement_id={$row['achievement_id']}";
			$achievementImageIcon = "
			<a href='{$achievemnentUrl}'>
	            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-icon.png'>
            </a>
			";
            */
            //$exp = array('secType' => 'Kite Notice');
            //$kiteUrl = $cpUrl->getUrlByRecord($row, 'notice_id', $exp);
            $noticeRec = $fn->getRecordRowByID('notice', 'notice_id', $row['notice_id']);
            $date = $dateUtil->formatDate($noticeRec['launch_date'], 'DD-MM-YYYY');
            $noticeTitle = $noticeRec['title'] . '('.$date. ')';

            //$kiteUrl = "/kite/{$row['student_id']}/kite-notice/{$row['notice_id']}/{$noticeRec['title']}.html";
            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);
            $kiteicon = "
            <a href='{$kiteUrl}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
            </a>
            ";
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['achievement_title'])}
            <td><div class='actTitle'>$noticeTitle</div></td>
            {$listObj->getListDataCell($kiteicon)}
            ";

            $rowCounter++ ;

            $urlExportAsPdf = '/'. "controller/student/?_action=printAchievementAsPdf&student_id={$student_id}&showHTML=0";
            //$downloadPdf = '/'. "controller/achievement/?_action=edit&notice_id={$row['notice_id']}&achievement_id={$row['achievement_id']}";
            $download = "
    		<a href='{$urlExportAsPdf}' target='_blank'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/printA.png'>
            </a>
            ";
        }

        $exp = array('style' => '', 'folder' => 'thumb', 'showCaption' => 0);
        $pic = $media->getMediaPicture('edukite_student', 'picture', $student_id, $exp);
        //<h3>$student_name</h3>

        $studentListUrl = '/'. "controller/student/";
        $backToStudent = "
        <a href='{$studentListUrl}'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/return-to-student-list.png'>
        </a>
        ";

        $text = "
        <div class='backToStudent'>{$backToStudent}</div>
        <div class='downloadImg'><h6>$student_name</h6>{$download}</div>
        {$this->getAchievementSearch($row['achievement_id'], $student_id)}
        <div class='studentImage'>{$pic}</div>
        {$this->getNavButtons(10)}
        <div class='achievementListView'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Achievement', 'achievement_title')}
            {$listObj->getListHeaderCell('Activity Title', 'achievement_title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
        </div>
        ";

        return $text;


	}

    /**
     *
     */
    function getAchievementSearch($achievement_id, $student_id) {
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
                       <a href='#' class='submit' achievement_id='{$achievement_id}' student_id='{$student_id}'>
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
     *   ACHIEVEMENT THAT LINKED WITH STUDENT
     */
    function getAchievementDisplayAfterSearch(){
        $cpUrl  = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $listObj = Zend_Registry::get('listObj');

        $student_id  = $fn->getReqParam('student_id');
        $achievement = $fn->getReqParam('achievement');

        $sqlAppend = '';
        if($achievement != ''){
            $sqlAppend = "
            AND (a.title LIKE '%{$achievement}%'
            OR a.achievement_code LIKE '%{$achievement}%')
            ";
        }

        $SQL = "
        SELECT sa.*
        	  ,a.title AS achievement_title
        	  ,a.achievement_code
        	  ,n.notice_id
        FROM achievement_student sa
        LEFT JOIN (achievement a) ON (a.achievement_id = sa.achievement_id)
        LEFT JOIN (notice n) ON (sa.notice_id = n.notice_id)
        WHERE sa.student_id = {$student_id}
        {$sqlAppend}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows  = "";
        $rowCounter = 0;
        $download = '';

        //--------------------------------------------------------------------------//
        while ($row = $db->sql_fetchrow($result)) {

            $achievemnentUrl = '/'. "controller/achievement/?_action=edit&notice_id={$row['notice_id']}&achievement_id={$row['achievement_id']}";
			$achievementImageIcon = "
			<a href='{$achievemnentUrl}'>
	            <img src='/cmspilotv30/CP/www/themes/Manager/images/notice_attachmentrighpanel.png'>
            </a>
			";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['achievement_code'])}
            {$listObj->getListDataCell($row['achievement_title'])}
            {$listObj->getListDataCell($achievementImageIcon)}
            ";

            $rowCounter++ ;

            $urlExportAsPdf = '/'. "controller/student/?_action=printAchievementAsPdf&student_id={$student_id}&showHTML=0";
            //$downloadPdf = '/'. "controller/achievement/?_action=edit&notice_id={$row['notice_id']}&achievement_id={$row['achievement_id']}";
            $download = "
    		<a href='{$urlExportAsPdf}' target='_blank'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/printA.png'>
            </a>
            ";
        }

        $exp = array('style' => '', 'folder' => 'thumb', 'showCaption' => 0);
        $pic = $media->getMediaPicture('edukite_student', 'picture', $student_id, $exp);

        $text = "
        <div class='downloadImg'>{$download}</div>
        <div class='studentImage'>{$pic}</div>
        <div class='achievementListView'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Code', 'achievement_code')}
            {$listObj->getListHeaderCell('Achievement', 'achievement_title')}
			<th></th>
            {$listObj->getListHeaderEnd()}
            {$rows}
        </div>
        ";

        return $text;


	}

     /**
     *
     */
    function getNavButtons($numPages, $action = '', $linkRecType = '', $exp = array()){
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $pager = Zend_Registry::get('pager');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module  = Zend_Registry::get('currentModule')->name;

        $moduleArr = $modulesArr[$module];

        $linkHasOnlyPageNo = $fn->getIssetParam($exp, 'linkHasOnlyPageNo', false);

        $text = "";

        $numPages = $numPages - 1;
        $action = ($action !== "") ? $action : $tv['action'];

        if ($pager->page == ''){
            return;
        }

        if ($action == 'achievementOptions'){
            $startRange = $pager->page;
            $endRange   = ($startRange + $numPages) > $pager->totalPages ? $pager->totalPages : ($startRange + $numPages);

            if ($endRange - $startRange <= $numPages){
                $startRange = ($endRange - $numPages) <= 0 ? 1 : ($endRange - $numPages);
            }

            $firstPage     = $pager->getPageNumbersLinks(1, 1, '...&nbsp;', $linkRecType, $exp);
            $firstPageText = ($startRange > 1) ? $firstPage : '';
            $lastPage      = $pager->getPageNumbersLinks($pager->totalPages, '', '', $linkRecType, $exp);
            $lastPageText  = ($endRange < $pager->totalPages) ? "...{$lastPage}" : "";

            $backToList = '';

            if ($action == "detail"){
                $backToList = "
                <div class='float_right backToList'>
                    {$pager->getBackButton()}
                </div>
                ";
            }

            $totalRecordsText = $pager->totalRecords;
            $showRecordCount = $fn->getReqParam('showRecordCount');
            if ($showRecordCount || $moduleArr['showRecordCount']) {
                $showRecordCount = true;
            }
            if (!$showRecordCount) {
                $totalRecordsText = 'Not counted';
            }

            $separator = '&';
            $urlViewAll = $_SERVER['REQUEST_URI'];
            if (strpos($urlViewAll, '?') === false) {
                $separator = '?';
            }
            $urlViewAll .= $separator . 'showAll=1';

            $prevBtn = "<img src='/cmspilotv30/CP/www/themes/Manager/images/ArrowL.png'>";
            $nextBtn = "<img src='/cmspilotv30/CP/www/themes/Manager/images/ArrowR.png'>";

            $text = "
            <div class='pagelinks'>
                <div class='floatbox'>
                    <!--
                    <div class='float_left viewAll'>
                        <a class='viewAll' href='{$urlViewAll}'>view all</a>
                    </div>
                    -->
                    <div class='float_left preBtn'>
                        {$pager->getPrevRecordsText($prevBtn, '', '', $linkRecType, $exp)}
                    </div>
                    <!--
                    <div class='float_left linkNos'>
                        {$firstPageText}
                        {$pager->getPageNumbersLinks($startRange, $endRange, '', $linkRecType, $exp)}
                        {$lastPageText}
                    </div>
                    -->
                    {$backToList}
                    <div class='float_left nxtBtn'>
                        {$pager->getNextRecordsText($nextBtn, '', '', $linkRecType, $exp)}
                    </div>
                    <div class='float_right totalRecs'>
                        [{$totalRecordsText}]
                    </div>
                </div>
            </div>
          ";
        }

        return $text;
    }
}