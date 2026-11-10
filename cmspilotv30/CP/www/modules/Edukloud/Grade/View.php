<?
class CP_Www_Modules_Edukloud_Grade_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */

    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        print $tv['action'];

        $rows = '';
        $staffTopItems = '';
        $newGradeUrl = $cpUrl->getUriWithNoQstr() . '?_action=newGrade';
        $topItems = '';

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff') {
            $staffTopItems .= "
            <div class='float_left ml5'>
                <a class='button' href='{$newGradeUrl}'><span>{$ln->gd('cp.createNewGrade')}</span></a>
            </div>
            ";
        }

        $topItems .= "
        <div class='floatbox'>
            {$staffTopItems}
        </div>
        ";

        foreach ($dataArray as $row){

            //$url = $cpUrl->getUrlByRecord($row, 'task_id');
            $url = $cpUrl->getUrlByCatType('Grade') . '?_action=editGrade&exam_result_id=' . $row['exam_result_id'];

            $rows .= "
            <tr>
                <td>{$row['class_title']}</td>
                <td>{$row['term']}</td>
            </tr>
            ";
        }

        $cbText = '';
        
        $tblTxt = "
        {$topItems}
        <table class='list'>
            <tr>
                <th>{$ln->gd('class')}</th>
                <th>{$ln->gd('term')}</th>
            </tr>
            {$rows}
        </table>
        ";

        $text = "
        {$tblTxt}
        ";
        return $text;
    }

    /**
     *
     */
    function getDetail1($row) {
        checkLoggedIn();
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $exam_result_id = $row['exam_result_id'];
        
        $editGradeUrl = $cpUrl->getUrlByCatType('Grade') . '?_action=editGrade&task_id=' . $exam_result_id;

        //$studentLinkDetailUrl = $fn->make_seo_url($urlArray, 0, $urlStudentLinkDetail);
        $studentLinkDetailUrl = '';

        $bulkEditUrl = "/index.php?_room=grade&_spAction=bulkEditStudentListDialog&exam_result_id={$row['exam_result_id']}&showHTML=0";
        $deleteUrl   = "/index.php?_room=grade&_spAction=deleteTask&exam_result_id={$row['exam_result_id']}&showHTML=0";
        $formActionLinkedStud = "/index.php?_room=grade&_spAction=linkedStudentsMain&showHTML=0";
        $launch_date = $fn->getCPDate($row['launch_date']);
        //========================================================//
        $tblTxt = "
        <form id='editFormCommon' class='yform columnar' method='post' action=''>
            <fieldset>
                {$formObj->getTBRow($ln->gd('title'), 'title', $row['title'])}
                {$formObj->getTBRow($ln->gd('subject'), 'subject_id', $row['subject_id'])}
                {$formObj->getTBRow($ln->gd('type'), 'type', $row['type'])}
                {$formObj->getTBRow($ln->gd('dueDate'), 'due_date', $row['due_date'])}
                {$formObj->getTBRow($ln->gd('launchDate'), 'launch_date', $row['launch_date'])}
                {$formObj->getTBRow($ln->gd('expiryDate'), 'expiry_date', $row['expiry_date'])}
                {$formObj->getTBRow($ln->gd('description'), 'description', $row['description'])}
            </fieldset>
        </form>
        ";

        $picTxt = '';
        $attTxt = '';
        $audTxt = '';
        $ytVidLinkTxt = '';
        
        $goToList = $cpUrl->getUrlByCatType('Grade');

        /********************************************************************/
        $text = "
        <div class='curveBox'>
            <h1>{$ln->gd('gradeDetail')}</h1>
            <div class='btnTopAbs'>
                <a class='ic-back back ml5' href='{$goToList}'><span>{$ln->gd('back')}</a>
            </div>
            <div class='curveBoxInner'>
                <p>
                    <a class='ic-back' href='{$editGradeUrl}'>{$ln->gd('edit')}</a>
                    <a class='ic-back' href='javascript:void(0)' id='deleteGrade' link='{$deleteUrl}'>{$ln->gd('delete')}</a>
                </p>
                <div class='subcolumns'>
                    <div class='c62l'>
                        <div class='subcl'>
                            {$tblTxt}
                            {$picTxt}
                            {$attTxt}
                            {$audTxt}
                            {$ytVidLinkTxt}
                        </div>
                    </div>
                    <div class='c38r'>
                        <div class='subcr rightPortal'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        //$tv['langKeys'][] = 'messageAfterGradeUpdate';

        return $text;
    }

    /**
     *
     */
    function getNewGrade() {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15'));

        $exam_result_id = '';
        $formAction = "/index.php?module=edukloud_grade&_spAction=newGradeSubmit&showHTML=0";

        $sqlClass = "
        SELECT class_id
              ,title AS class_title
        FROM class
        ORDER BY title
        ";

        $sqlSubject = "
        SELECT ss.subject_id
              ,s.title AS subject_title
        FROM staff_subject ss
        LEFT JOIN (subject s) ON (ss.subject_id = s.subject_id)
        WHERE ss.staff_id = {$_SESSION['cpContactId']}
        ORDER BY subject_title
        ";

        $sqlTerm = "
        SELECT value, value
        FROM valuelist
        WHERE key_text = 'term'
        ORDER BY sort_order
        ";
        
        $cancelButton = "
        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
        ";

        $sessionID = session_id();

        $staff_id = $_SESSION['cpContactId'];
        $current_date = date('Y-m-d');

        $frmTxt = "
        <form id='newFormCommon' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL($ln->gd('class'). ' * ', 'class_id', $sqlClass)}
                {$formObj->getDDRowBySQL($ln->gd('term'). ' * ', 'term', $sqlTerm)}
                {$formObj->getDDRowBySQL($ln->gd('subject'). ' * ', 'subject_id', $sqlSubject)}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                        <div class='float_left btnReset'>
                            {$cancelButton}
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' id='staff_id' name='staff_id' value='{$staff_id}' />
                <input type='hidden' name='exam_result_id' value='{$exam_result_id}' />
                <input type='hidden' name='current_date' value='{$current_date}' />
            </fieldset>
        </form>
        ";

        $text = "
        {$frmTxt}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditGrade() {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15', 'jqUploadify3.2'));
        
        $formAction = "/index.php?module=edukloud_grade&_spAction=editGradeSubmit&showHTML=0";
        $sessionID  = session_id();
        $staff_id   = $_SESSION['cpContactId'];
        $class_id = $fn->getReqParam('class_id');
        $rows = '';

        $exam_result_id = $fn->getReqParam('exam_result_id');
        if (trim($exam_result_id) == ''){
            exit('invalid access');
        }
        
        $SQL = "
        SELECT ers.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS student_name
              ,sb.title AS subject_title
              ,er.class_id
        FROM exam_result_history ers 
        LEFT JOIN (student s) ON (ers.student_id = s.student_id )
        LEFT JOIN (subject sb) ON (ers.subject_id = sb.subject_id )
        LEFT JOIN (exam_result er) ON (ers.exam_result_id = er.exam_result_id )
        WHERE er.class_id = {$class_id}
        ORDER BY student_name
        ";
        $result = $db->sql_query($SQL);

        /********************************************************************/
        //$sucRetUrl = $fn->make_seo_url($urlArray, 0, $urlTask);
        $sucRetUrl = '';
        $cancelButton = "
        <input type='reset' class='button' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
        ";
        	        
        while ($row = $db->sql_fetchrow($result)){

            $rows .= "
            <tr>
                <td>{$row['student_name']}</td>
                <td>{$row['subject_title']}</td>
                <td>{$formObj->getTBRow('', 'grade', $row['grade'])}</td>
                <input type='hidden' name='exam_result_history_id' value='{$row['exam_result_history_id']}' />
                <input type='hidden' name='exam_result_id' value='{$exam_result_id}' />
            </tr>
            ";
            //$exam_result_history_id = $row['exam_result_history_id'];
        }

        $cbExp = array('cls' => 'mt10');
        $goToList = $cpUrl->getUrlByCatType('Grade');
        $text = "
        <div class='curveBox small'>
            <h3>{$ln->gd('cp.editGrade')}</h3>
            <div class='btnTopAbs mt10'>
                <a class='ic-back ml5' href='{$goToList}'><span>{$ln->gd('cp.backToList')}</a>
            </div>
            <form id='editFormCommon' class='' method='post' action='{$formAction}'>
                <table class='list'>
                    <tr>
                        <th>{$ln->gd('StudentName')}</th>
                        <th>{$ln->gd('subject')}</th>
                        <th>{$ln->gd('grade')}</th>
                    </tr>
                    {$rows}
                </table>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' class='button' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                        <div class='float_left btnReset'>
                            {$cancelButton}
                        </div>
                    </div>
                </div>
                </form>
        </div>
        ";

        $fn->addLangKey(array('areYouSureToDelete'));

        return $text;
    }

    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $text ="
        ";
        return $text;
    }

}
