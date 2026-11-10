<?
class CP_Www_Modules_Edukite_Class_View extends CP_Common_Modules_Edukite_Class_View
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
            {$listObj->getListRowEnd($row['class_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        <div class='classList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Class Name', 'c.title')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formObj->mode = $tv['action'];

        $sqlStaff = "
        SELECT  a.staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
        FROM staff a
        ORDER BY staff_name
        ";

        $status = '';

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        if($teacherRec['role'] == 'Kite Master'){
            $status ="
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.edukite.statusArr'], $row['status'])}
            ";
        }

        $fielset1 = "
        {$formObj->getTBRow('Class Name', 'title', $row['title'])}
        {$status}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Class Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getLeftPanel(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $class_id = $fn->getReqParam('record_id');
        $text = "
        <div class='btns'>
            <ul>
                <li>
                <a href='#' class='studentLinkInClass' class_id='{$class_id}'>
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

        $class_id  = $fn->getReqParam('class_id');

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
            FROM class_student hisTble
            WHERE hisTble.class_id = {$class_id}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' class_id='{$class_id}'>
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
        {$this->getStudentSearch($class_id)}
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' class_id='{$class_id}'>Select All</a></td>
                </tr>-->
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

        $class_id  = $fn->getReqParam('record_id');

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
            FROM class_student hisTble
            WHERE hisTble.class_id = {$class_id}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' class_id='{$class_id}'>
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
        {$this->getStudentSearch($class_id)}
        <div class='row'>
            <div class='assemblyTxt'></div>
            <table class='list'>
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' class_id='{$class_id}'>Select All</a></td>
                </tr>-->
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
    function getRightPanelDefaultContent($class_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = "";
        $text = "";

        if($class_id == ''){
            $class_id = $fn->getReqParam('record_id');
        }

        //to get classes which are linked
        $sqlLinked = "
        SELECT hisTble.student_id
              ,CONCAT_WS(' ', lnktble.first_name, lnktble.last_name ) AS name
        FROM class_student hisTble
        LEFT JOIN (student lnktble) ON (hisTble.student_id = lnktble.student_id)
        WHERE hisTble.class_id = {$class_id}
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
                    <a href='#' class='studentLinkDelete' student_id='{$row['student_id']}' class_id='{$class_id}'>
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
                        <td colspan='2'><a href='#' class='removeAllStudent button'  class_id='{$class_id}'>Remove All</a></td>
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

        $class_id   = $fn->getReqParam('class_id');
        $student_id  = $fn->getReqParam('student_id');

        $fa = array();
        $fa['class_id']      = $class_id;
        $fa['student_id']    = $student_id;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'class_student');
        $insertResult        = $db->sql_query($insertSQL);

        $text = $this->getRightPanelDefaultContent($class_id);

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

        $class_id   = $fn->getReqParam('class_id');
        set_time_limit(50000);

        $sqlMain = "
        SELECT hisTble.student_id
        FROM student hisTble
        WHERE hisTble.status = 'Active'
            AND hisTble.student_id NOT IN(
            SELECT linkTble.student_id
            FROM class_student linkTble
            WHERE linkTble.class_id = {$class_id}
        )
        ";
        $result = $db->sql_query($sqlMain);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['class_id']      = $class_id;
            $fa['student_id']    = $row['student_id'];
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $insertSQL           = $dbUtil->getInsertSQLStringFromArray($fa, 'class_student');
            $insertResult        = $db->sql_query($insertSQL);
        }

        $text = $this->getRightPanelDefaultContent($class_id);

        return $text;
    }

    /**
     *
     */
    function getStudentSearch($class_id) {
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
                       <a href='#' class='submit' class_id='{$class_id}'><img src='/cmspilotv30/CP/www/themes/Manager/images/find.png'></a>
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
    function getStudentDisplayAfterSearch($class_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";
        $sqlAppend = '';

        if($class_id == ''){
            $class_id = $fn->getReqParam('class_id');
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
            FROM class_student hisTble
            WHERE hisTble.class_id = {$class_id}
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
                <a href='#' class='studentLinkArrow' student_id='{$row['student_id']}' class_id='{$class_id}'>
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
                <!--<tr>
                    <td colspan='2'><a href='#' class='selectAllStudent button' class_id='{$class_id}'>Select All</a></td>
                </tr>-->
            {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}