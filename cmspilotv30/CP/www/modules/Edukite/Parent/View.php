<?
class CP_Www_Modules_Edukite_Parent_View extends CP_Common_Modules_Edukite_Parent_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn    = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['parent_name'], '', '', $row)}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListRowEnd($row['parent_id'])}
            ";
            $rowCounter++ ;
        }
        $status = $fn->getReqParam('status');

        $statusLink = '';
        $archiveLinkUrl = '?status=Archive';
        $activeLinkUrl = '?status=Active';
        if($status != 'Archive'){
            $statusLink = "<a href='{$archiveLinkUrl}' id='archiveLink'>View Archive Records</a>";
        } else {
            $statusLink = "<a href='{$activeLinkUrl}' id='archiveLink'>View Active Records</a>";
        }

        $text = "
        <div class='parentList'>
            <!--<div class='archiveLink'>{$statusLink}</div>-->
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Name', 'parent_name')}
            {$listObj->getListHeaderCell('Email', 'p.email')}
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

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email / User Name', 'email')}
        {$formObj->getTBRow('Password', 'pass_word')}
        {$formObj->getTBRow('Mobile', 'mobile')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $status = '';

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        if($teacherRec['role'] == 'Kite Master'){
            $status ="
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.edukite.statusArr'], $row['status'])}
            ";
        }

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email / User Name', 'email', $row['email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$status}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Parent Details', $fielset1)}
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
        $ln = Zend_Registry::get('ln');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');

        $text = "
        <div class='btns'>
            <ul>
                <li>
                <a href='#' class='studentLinkInParent'>
                    <img src='/cmspilotv30/CP/www/themes/Manager/images/child-btn-active.png'>
                </a>
                </li>
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     --------- STUDENT LINKING - LIST IN LEFT PANEL --------------------------------
     */
    function getStudentList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $parent_id  = $fn->getReqParam('parent_id');

        $sqlStudent = "
        SELECT s.student_id
               ,CONCAT_WS(' ', s.first_name, s.last_name ) AS name
        FROM student s
        WHERE s.status = 'Active'
        ORDER BY s.last_name
        ";
        $result = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM student_parent hisTble
            WHERE hisTble.parent_id = {$parent_id}
            AND hisTble.student_id = {$row['student_id']}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' parent_id='{$parent_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td>{$image}</td>
            </tr>
            ";
        }

        $text = "
        {$this->getStudentSearch($parent_id)}
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
     --------- STUDENT LINKING - LEFT PANEL DEFAULT CONTENT--------------------------------
     */
    function getLeftPanelDefaultContent() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $parent_id  = $fn->getReqParam('record_id');

        $sqlStudent = "
        SELECT s.student_id
               ,CONCAT_WS(' ', s.first_name, s.last_name ) AS name
        FROM student s
        WHERE s.status = 'Active'
        ORDER BY s.last_name
        ";
        $result = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM student_parent hisTble
            WHERE hisTble.parent_id = {$parent_id}
            AND hisTble.student_id = {$row['student_id']}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' parent_id='{$parent_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td>{$image}</td>
            </tr>
            ";
        }

        $text = "
        {$this->getStudentSearch($parent_id)}
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
     ------------- STUDENT LIST IN RIGHT PANEL DEFAULT CONTENT---------------------------------
     */
    function getRightPanelDefaultContent($parent_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";
        $text = "";

        if($parent_id == ''){
            $parent_id = $fn->getReqParam('record_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT hisTble.student_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM student_parent hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.parent_id = {$parent_id}
          AND lnktble.status = 'Active'
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
            $rows .= "
            <tr>
                <td>
                    <a href='#' class='studentLinkDelete' student_id='{$row['student_id']}' parent_id='{$parent_id}'>
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

        $parent_id   = $fn->getReqParam('parent_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['parent_id']     = $parent_id;
        $fa['student_id']    = $student_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'student_parent');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getRightPanelDefaultContent($parent_id);

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

        $parent_id   = $fn->getReqParam('parent_id');
        set_time_limit(50000);

        $sqlMain = "
        SELECT hisTble.student_id
        FROM student hisTble
        WHERE hisTble.status = 'Active'
            AND hisTble.student_id NOT IN(
            SELECT linkTble.student_id
            FROM student_parent linkTble
            WHERE linkTble.parent_id = {$parent_id}
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['parent_id']     = $parent_id;
            $fa['student_id']    = $row['student_id'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'student_parent');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getRightPanelDefaultContent($parent_id);

        return $text;
    }

    /**
     *
     */
    function getStudentSearch($parent_id) {
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
                       <a href='#' class='submit' parent_id='{$parent_id}'><img src='/cmspilotv30/CP/www/themes/Manager/images/find.png'></a>
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
    function getStudentDisplayAfterSearch($parent_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $sqlAppend = '';

        if($parent_id == ''){
            $parent_id = $fn->getReqParam('parent_id');
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
        $result = $db->sql_query($sqlStudent);

        while ($row = $db->sql_fetchrow($result)) {
            $sqlTableLinked = "
            SELECT hisTble.student_id
            FROM student_parent hisTble
            WHERE hisTble.parent_id = {$parent_id}
            AND hisTble.student_id = {$row['student_id']}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' parent_id='{$parent_id}'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/arrow.png'>
                </a>
                ";
            }

            $rows .= "
            <tr>
                <td>{$row['name']}</td>
                <td>{$image}</td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <table class='list'>
                {$rows}
            </table>
        </div>
        ";


        return $text;
    }

}