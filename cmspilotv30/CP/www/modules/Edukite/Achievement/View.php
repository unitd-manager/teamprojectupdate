<?
class CP_Www_Modules_Edukite_Achievement_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListRowEnd($row['achievement_id'])}
            ";
            $rowCounter++ ;
        }
        $text = "
        <div class='classList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Title', 'a.title')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $media = Zend_Registry::get('media');
        $db = Zend_Registry::get('db');

        $notice_id = $fn->getReqParam('notice_id');
        $achievement_id = $fn->getReqParam('achievement_id');
        $noticeRec = $fn->getRecordRowByID('notice', 'notice_id', $notice_id);

        $SQL = "
        SELECT sa.*
              ,a.title
              ,a.achievement_code
        FROM achievement_student sa
        LEFT JOIN achievement a ON (sa.achievement_id = a.achievement_id)
        WHERE sa.notice_id = {$notice_id}
        AND sa.achievement_id = {$achievement_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $listUrl = '/'. "controller/notice/?_action=achievementPanel&notice_id={$notice_id}";
        //$noticeEditUrl = '/'. "controller/notice/?_action=edit&notice_id={$notice_id}";
        $noticeEditUrl = '/'. "controller/notice/edit/{$notice_id}/";
        $actBtns= "
        <div class='noticeSetup-btn'>
            <a href='{$noticeEditUrl}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/return-audience-tri-panel.png'>
            </a>
        </div>
        <div class='achievement-btn'>
            <a href='{$listUrl}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-list-back.png'>
            </a>
        </div>
        ";
        /*<div class='achievement-group-btn'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-tri-panel.png'>
        </div>*/

        $helpUrl ='/index.php?module=edukite_achievement&_spAction=helpContent&showHTML=0';
        //<h2 class='heading'><img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-tick.jpg'>Achievement</h2>
        $fieldset1 = "
        {$actBtns}
        <div class='achievementHelp'>
            <a href='{$helpUrl}' id='achievementHelpBtn'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/achievement-help.png'>
            </a>
        </div>
        <div class='mt10 mb20 bold'>You are focusing on this Outcome :</div>
        <div class='achievementTitle'>
            {$row['title']}
        </div>
		";

        $text = "
        {$formObj->getFieldSetWrapped('', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getHelpContent() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $text = "
        <div class='helpContent'>
	        <div class='helpContentTitle'>HOW TO USE THE OUTCOMES AUDIENCE TRI-PANEL</div><br>
			In this tri-panel select the children who have participated in the associated activity and achieved the outcome viewed in the centre panel.<br>
			The audience for this activity must first be selected in the activity notice form, if not yet selected click the <b>'Proceed to Notice Form'</b> button, select the child, class or cohort who are included in this activity & return to this page by clicking <b>'Go to EYLF Learning Outcomes'</b> button and the right arrow on the outcome chosen.<br><br>
			1. To attach an audience to this Outcome, Click on either <b>Child, Class or Cohort</b> in the left panel (the child or group you selected in the form will appear)<br>
			2. You may need to click the blue arrow within the circle to see a drop down list of children from the class or cohort<br>
			3. Click on the arrow to the right of the children's names you want to attach to this Outcome, the audience will immediately appear in the right hand panel.<br>
			4. You can now click the <b>'Return to ELYF Learning Outcomes'</b> button to select another outcome. <br>
			5. If Outcomes are completed, click <b>'Proceed to Notice Form'</b> & click the <b>'Launch to Kites'</b> button for the activity & outcomes to appear on the children's kites. <br>
			6. To make further changes simply click the <b>EDIT</b> link in the child's kite to be taken back to the Notice form and Outcomes page.
			</div>
		</div>
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

        $achievement_id = $fn->getReqParam('achievement_id');
        $notice_id       = $fn->getReqParam('notice_id');
        $liStudent = "";
        $liCohort = "";
        $liClass = "";

        $classLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND class_id > 0 AND notice_id = '{$notice_id}'");
        $cohortLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND year_group_id > 0  AND notice_id = '{$notice_id}'");
        $studentLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND  student_id > 0 AND notice_id = '{$notice_id}' AND (class_id = '' OR class_id IS NULL)
        AND (year_group_id = '' OR  year_group_id IS NULL)");

        if ($classLink['achievement_id'] != '' ) {
            $liClass = "
            <li>
                <a href='#' class='classLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/class-btn.png'>
                </a>
            </li>
            " ;
        }

        if ($cohortLink['achievement_id'] != '' ) {
            $liCohort = "
            <li>
                <a href='#' class='cohortLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png'>
                </a>
            </li>
            " ;
        }
        if ($studentLink['achievement_id'] != '' ) {
            $liStudent = "
            <li>
                <a href='#' class='studentLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/child-btn.png'>
                </a>
            </li>
            " ;
        }

        if ($classLink['achievement_id'] == ''
            AND $cohortLink['achievement_id'] == ''
            AND $studentLink['achievement_id'] == ''
        ) {
            $liClass = "
            <li>
                <a href='#' class='classLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/class-btn.png'>
                </a>
            </li>
            " ;
            $liStudent = "
            <li>
                <a href='#' class='studentLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/child-btn.png'>
                </a>
            </li>
            " ;
            $liCohort = "
            <li>
                <a href='#' class='cohortLinkInAchievement'  achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/cohort-btn.png'>
                </a>
            </li>
            " ;
        }


        $text = "
        <div class='activityGroup'>
            <img src='/cmspilotv30/CP/www/themes/Manager/images/activity-group.png'>
        </div>
        <div class='btns'>
            <ul>
            {$liStudent}
            {$liClass}
            {$liCohort}
            </ul>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getClassList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        if($notice_id == ''){
            $notice_id      = $fn->getReqParam('notice_id');
        }

        if($notice_id == ''){
            return;
        }

        $sqlClass = "
        SELECT DISTINCT lnktble.title
        ,hisTble.class_id_hook
        FROM notice_student hisTble
        LEFT JOIN (class lnktble) ON (hisTble.class_id_hook = lnktble.class_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.class_id_hook > 0
        ORDER BY lnktble.title
        ";

        $result = $db->sql_query($sqlClass);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.class_id
            FROM achievement_student hisTble
            WHERE hisTble.achievement_id = {$achievement_id}
            AND hisTble.notice_id = {$notice_id}
            AND hisTble.class_id={$row['class_id_hook']}
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
                <a href='#' class='classLinkArrow' class_id='{$row['class_id_hook']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td align='left'>
                    <a href='#' class='classLinkExpand plus' class_id='{$row['class_id_hook']}' notice_id='{$notice_id}' achievement_id='{$achievement_id}'>
                    </a>
                    <span>{$row['title']}</span>
                </td>
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
        <div id='activeLayout' value='class'></div>
        ";

        return $text;
    }
    /**
     *
     */
    function getLinkedClassList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        if($notice_id == ''){
            $notice_id      = $fn->getReqParam('notice_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT DISTINCT lnktble.title
        ,hisTble.class_id
        FROM achievement_student hisTble
        LEFT JOIN (class lnktble) ON (hisTble.class_id = lnktble.class_id)
        WHERE hisTble.achievement_id = {$achievement_id}
            AND hisTble.notice_id = {$notice_id}
        AND (hisTble.class_id != '' OR hisTble.class_id IS NOT NULL)
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='classLinkDelete' class_id='{$row['class_id']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                    <span>{$row['title']}</span>
                </td>
                <td align='right'>
                    <a href='#' class='classLinkExpand plus' class_id='{$row['class_id']}' notice_id='{$notice_id}' achievement_id='{$achievement_id}'>
                    </a>
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

        $achievement_id = $fn->getReqParam('achievement_id');
        $class_id       = $fn->getReqParam('class_id');
        $notice_id      = $fn->getReqParam('notice_id');

        $sqlLinked = "
        SELECT hisTble.student_id
        FROM notice_student hisTble
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.class_id_hook = {$class_id}
        ";
        $result = $db->sql_query($sqlLinked);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['achievement_id']= $achievement_id;
            $fa['class_id']      = $class_id;
            $fa['notice_id']     = $notice_id;
            $fa['student_id']    = $row['student_id'];

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getLinkedClassList($achievement_id, $notice_id);

        return $text;
    }

    /**
     * Left Panel - list of Year Group available for Achievement
     */
    function getCohortList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        $notice_id      = $fn->getReqParam('notice_id');

        if($notice_id == ''){
            return;
        }

        $sqlYearGroup = "
        SELECT DISTINCT lnktble.title
        ,hisTble.year_group_id_hook
        FROM notice_student hisTble
        LEFT JOIN (year_group lnktble) ON (hisTble.year_group_id_hook = lnktble.year_group_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.year_group_id_hook > 0
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlYearGroup);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.year_group_id
            FROM achievement_student hisTble
            WHERE hisTble.achievement_id = {$achievement_id}
            AND hisTble.notice_id = {$notice_id}
            AND hisTble.year_group_id ={$row['year_group_id_hook']}
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
                <a href='#' class='cohortLinkArrow' year_group_id='{$row['year_group_id_hook']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td align='left'>
                    <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id_hook']}' notice_id='{$notice_id}' achievement_id='{$achievement_id}'>
                    </a>
                    <span>{$row['title']}</span>
                </td>
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
        <div id='activeLayout' value='cohort'></div>
        ";

        return $text;
    }

    /**
     * Right Panel - list of Year Group linked to Achievement
     */
    function getLinkedCohortList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $text = "";

        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        if($notice_id == ''){
            $notice_id      = $fn->getReqParam('notice_id');
        }
        //to get classes which are linked
        $sqlLinked = "
        SELECT DISTINCT lnktble.title
        ,hisTble.year_group_id
        FROM achievement_student hisTble
        LEFT JOIN (year_group lnktble) ON (hisTble.year_group_id = lnktble.year_group_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.achievement_id = {$achievement_id}
        AND (hisTble.year_group_id != '' OR hisTble.year_group_id IS NOT NULL)
        ORDER BY lnktble.title
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='cohortLinkDelete' year_group_id='{$row['year_group_id']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                    <span>{$row['title']}</span>
                </td>
                <td align='right'>
                    <a href='#' class='cohortLinkExpand plus' year_group_id='{$row['year_group_id']}' notice_id='{$notice_id}' achievement_id='{$achievement_id}'>
                    </a>
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
     * Adding a Year Group to Student
     */
    function getLinkCohortToRightPanel() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";
        $text = '';

        $year_group_id  = $fn->getReqParam('year_group_id');
        $achievement_id = $fn->getReqParam('achievement_id');
        $notice_id      = $fn->getReqParam('notice_id');

        $sqlLinked = "
        SELECT hisTble.student_id
        FROM notice_student hisTble
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.year_group_id_hook = {$year_group_id}
        ";
        $result = $db->sql_query($sqlLinked);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['student_id']    = $row['student_id'];
            $fa['year_group_id'] = $year_group_id;
            $fa['achievement_id']= $achievement_id;
            $fa['notice_id']     = $notice_id;

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
            $insertResult        = $db->sql_query($insertSQL);
        }


        $text = $this->getLinkedCohortList($achievement_id);

        return $text;
    }

    /**
     *
     --------- STUDENT LINKING - LIST IN LEFT PANEL --------------------------------
     */
    function getStudentList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $rows = "";
        $notice_id = $fn->getReqParam('notice_id');


        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        if($notice_id == ''){
            $notice_id      = $fn->getReqParam('notice_id');
        }

        if($notice_id == ''){
            return;
        }

        $sqlStudent = "
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
        $result = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM achievement_student hisTble
            WHERE hisTble.achievement_id = {$achievement_id}
            AND hisTble.notice_id = {$notice_id}
            AND hisTble.student_id = {$row['student_id']}
            AND (hisTble.class_id = '' OR hisTble.class_id IS NULL)
            AND (hisTble.year_group_id = '' OR  hisTble.year_group_id IS NULL)
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}'>
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
            <div class='assemblyTxt'></div>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' achievement_id='{$achievement_id}'>Select All</a></td>
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
    function getLinkedStudentList($achievement_id = '', $notice_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";
        $text = "";

        if($achievement_id == ''){
            $achievement_id = $fn->getReqParam('achievement_id');
        }

        if($notice_id == ''){
            $notice_id      = $fn->getReqParam('notice_id');
        }

        //to get students who are linked
        $sqlLinked = "
        SELECT hisTble.student_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM achievement_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.achievement_id = {$achievement_id}
        AND hisTble.notice_id = {$notice_id}
        AND hisTble.student_id > 0
        AND (hisTble.class_id = '' OR hisTble.class_id IS NULL)
        AND (hisTble.year_group_id = '' OR  hisTble.year_group_id IS NULL)
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);
        $numRows = $db->sql_numrows($result);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {

            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

			$noticeAchievementStudentPanelLink = '';

	        if($cpCfg['showAcheivement'] == 1){
				$noticeAchievementStudentPanelLink = "
				<td class='noticeAchievementStudentPanelLink'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/notice_attachmentrighpanel.png'>
				</td>
				";
			}

            $rows .= "
            <tr>
                <td>
                    <a href='#' class='studentLinkDelete' student_id='{$row['student_id']}' notice_id='{$notice_id}' achievement_id='{$achievement_id}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                    <span>{$row['name']}</span>
                </td>
                <td>
                    <a href='{$kiteUrl}' class='studentInStudentLink' student_id='{$row['student_id']}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                    </a>
                </td>
                </tr>
            ";
        }

        if($numRows){
            $text = "
            <div class='row'>
                <div class='audienceTxt'></div>
                <table class='list'>
                    <!--<tr>
                        <td colspan='3'><a href='#' class='removeAllStudent button'  notice_id='{$achievement_id}'>Remove All</a></td>
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

        $notice_id      = $fn->getReqParam('notice_id');
        $achievement_id = $fn->getReqParam('achievement_id');
        $student_id     = $fn->getReqParam('student_id');

        $fa = array();
        $fa['notice_id']     = $notice_id;
        $fa['achievement_id']= $achievement_id;
        $fa['student_id']    = $student_id;

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
        $insertResult        = $db->sql_query($insertSQL);
        $achievement_student_id   = $db->sql_nextid();

        $text = $this->getLinkedStudentList($achievement_id);

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
        $achievement_id  = $fn->getReqParam('achievement_id');
        $notice_id       = $fn->getReqParam('notice_id');

        $classLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND class_id > 0 AND notice_id = '{$notice_id}'");
        $cohortLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND year_group_id > 0  AND notice_id = '{$notice_id}'");
        $studentLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND  student_id > 0 AND notice_id = '{$notice_id}'");

        //the priority of showing the default link is Class, Cohort, Student
        //Check which of the above condition satisfies and show the link accordingly.

        if ($classLink['achievement_id'] != '' ) {
            return $this->getClassList($achievement_id, $notice_id);
        } else if ($cohortLink['achievement_id'] != '' ) {
            return $this->getCohortList($achievement_id, $notice_id);
        } else if ($studentLink['achievement_id'] != '' ) {
            return $this->getStudentList($achievement_id, $notice_id);
        } else {
            return $this->getClassList($achievement_id, $notice_id);
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

        $achievement_id  = $fn->getReqParam('achievement_id');
        $notice_id       = $fn->getReqParam('notice_id');

        $classLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND notice_id = '{$notice_id}' AND class_id > 0");
        $cohortLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND notice_id = '{$notice_id}' AND year_group_id > 0");
        $studentLink = $fn->getRecordByCondition('achievement_student', "achievement_id = '{$achievement_id}' AND notice_id = '{$notice_id}' AND  student_id > 0");

        //the priority of showing the default link is Class, Cohort, Student
        //Check which of the above condition satisfies and show the link accordingly.

        if ($classLink['achievement_id'] != '' ) {
            return $this->getLinkedClassList($achievement_id, $notice_id);
        } else if ($cohortLink['achievement_id'] != '' ) {
            return $this->getLinkedCohortList($achievement_id, $notice_id);
        } else if ($studentLink['achievement_id'] != '' ) {
            return $this->getLinkedStudentList($achievement_id, $notice_id);
        } else {
            return $this->getLinkedClassList($achievement_id, $notice_id);
        }

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
        $achievement_id = $fn->getReqParam('achievement_id');
        $class_id = $fn->getReqParam('class_id');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        ,hisTble.achievement_student_id
        FROM achievement_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id = {$notice_id}
        AND hisTble.class_id = {$class_id}
        AND hisTble.achievement_id = {$achievement_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $rows .= "
            <tr>
                <td>
                    <a href='#' class='studentInClassLinkDelete' achievement_student_id='{$row['achievement_student_id']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}' student_id='{$row['student_id']}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                    <span>{$row['name']}</span>
                </td>
                <td align='right'>
                    <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                    </a>
                </td>
            </tr>
            ";
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
        $achievement_id = $fn->getReqParam('achievement_id');
        $class_id = $fn->getReqParam('class_id');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        FROM notice_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id   = {$notice_id}
          AND hisTble.class_id_hook = {$class_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.class_id
            FROM achievement_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.class_id = {$class_id}
            AND hisTble.student_id = {$row['student_id']}
            AND hisTble.achievement_id = {$achievement_id}
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
                <a href='#' class='classStudentLinkArrow' class_id='{$class_id}' notice_id='{$notice_id}' achievement_id='{$achievement_id}' student_id='{$row['student_id']}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

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
        $achievement_id = $fn->getReqParam('achievement_id');

        $fa = array();
        $fa['achievement_id']     = $achievement_id;
        $fa['notice_id']     = $notice_id;
        $fa['student_id']    = $student_id;
        $fa['class_id'] = $class_id;

        $achievementStudentChk = $fn->getRecordByCondition('achievement_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id} AND
                                                     class_id = {$class_id} AND
                                                     achievement_id = {$achievement_id}
                                                     ");

        if(is_array($achievementStudentChk)){
        } else {
            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getLinkedClassList($achievement_id, $notice_id);

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
        $achievement_id = $fn->getReqParam('achievement_id');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        FROM notice_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.year_group_id_hook = {$year_group_id}
          AND hisTble.notice_id   = {$notice_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.year_group_id
            FROM achievement_student hisTble
            WHERE hisTble.notice_id = {$notice_id}
            AND hisTble.year_group_id = {$year_group_id}
            AND hisTble.student_id = {$row['student_id']}
            AND hisTble.achievement_id = {$achievement_id}
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
                <a href='#' class='cohortStudentLinkArrow' year_group_id='{$year_group_id}' notice_id='{$notice_id}' student_id='{$row['student_id']}' achievement_id='{$achievement_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

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
    function getExpandCohortInRightPanel() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";

        $notice_id     = $fn->getReqParam('notice_id');
        $year_group_id = $fn->getReqParam('year_group_id');
        $achievement_id = $fn->getReqParam('achievement_id');

        //to get students who are linked
        $sqlLinked = "
        SELECT CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        ,hisTble.student_id
        ,hisTble.achievement_student_id
        FROM achievement_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.notice_id   = {$notice_id}
        AND hisTble.year_group_id = {$year_group_id}
        AND hisTble.achievement_id = {$achievement_id}
        ORDER BY lnktble.last_name
        ";
        $result = $db->sql_query($sqlLinked);

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];

        while ($row = $db->sql_fetchrow($result)) {
            $urlArray['sitePfxId'] = $row['student_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);

            $rows .= "
            <tr>
                <td>
                    <a href='#' class='studentInCohortLinkDelete' achievement_student_id='{$row['achievement_student_id']}' achievement_id='{$achievement_id}' notice_id='{$notice_id}' student_id='{$row['student_id']}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/delete.png'>
                    </a>
                    <span>{$row['name']}</span>
                </td>
                <td align='right'>
                    <a href='{$kiteUrl}' class='studentInClassLink' student_id='{$row['student_id']}'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'>
                    </a>
                </td>
            </tr>
            ";
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
        $achievement_id = $fn->getReqParam('achievement_id');

        $fa = array();
        $fa['notice_id']            = $notice_id;
        $fa['year_group_id']   = $year_group_id;
        $fa['student_id']           = $student_id;
        $fa['achievement_id']     = $achievement_id;

        $achievementStudentChk = $fn->getRecordByCondition('achievement_student',
                                                     "notice_id = {$notice_id} AND
                                                     student_id = {$student_id} AND
                                                     year_group_id = {$year_group_id} AND
                                                     achievement_id = {$achievement_id}
                                                     ");

        if(is_array($achievementStudentChk)){
        } else {
            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'achievement_student');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getLinkedCohortList($achievement_id, $notice_id);

        return $text;
    }
}