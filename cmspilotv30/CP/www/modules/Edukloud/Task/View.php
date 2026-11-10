<?
class CP_Www_Modules_Edukloud_Task_View extends CP_Common_Lib_ModuleViewAbstract
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

        $rows = '';
        $staffTopItems = '';
        //$newTaskUrl = "/index.php?module=edukloud_task&_spAction=newTask&showHTML=0";
        $newTaskUrl = $cpUrl->getUriWithNoQstr() . '?_action=newTask';
        $topItems = '';

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff') {
            $staffTopItems .= "
            <div class='float_left ml5'>
                <a class='squarebutton' href='{$newTaskUrl}'><span>{$ln->gd('createNewTask')}</span></a>
            </div>
            ";
        }

        $topItems .= "
        <div class='floatbox'>
            {$staffTopItems}
        </div>
        ";

        foreach ($dataArray as $row){
            $cmtRead = '';
            $cbText = '';
            $tdComment = '';
            $thComment = '';

            if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff') {
                $cbText = "
                <td>
                    <input type='checkbox' class='tagFlagToDel' name='task_id[]' value='{$row['task_id']}' notchecked='notchecked'>
                </td>
                ";
            }
            //$url = $cpUrl->getUrlByRecord($row, 'task_id');
            $url = $cpUrl->getUrlByCatType('Task') . '?_action=editTask&task_id=' . $row['task_id'];

            $rows .= "
            <tr>
                <td>{$row['task_id']}</td>
                <td class='title'><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['type']}</td>
                <td>{$row['subject_title']}</td>
                <td>{$row['launch_date']}</td>
                <td>{$row['due_date']}</td>
                {$tdComment}
                <td>{$row['status']}</td>
            </tr>
            ";
        }

        $cbText = '';
        
        $tblTxt = "
        {$topItems}
        <table class='list'>
            <tr>
                <th>{$ln->gd('code')}</th>
                <th>{$ln->gd('title')}</th>
                <th>{$ln->gd('type')}</th>
                <th>{$ln->gd('subject')}</th>
                <th>{$ln->gd('launchDate')}</th>
                <th>{$ln->gd('dueDate')}</th>
                {$thComment}
                <th>{$ln->gd('status')}</th>
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
    function getDetail($row) {
        checkLoggedIn();
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $task_id = $row['task_id'];
        
        $editTaskUrl = $cpUrl->getUrlByCatType('Task') . '?_action=editTask&task_id=' . $task_id;

        //$studentLinkDetailUrl = $fn->make_seo_url($urlArray, 0, $urlStudentLinkDetail);
        $studentLinkDetailUrl = '';

        $bulkEditUrl = "/index.php?_room=task&_spAction=bulkEditStudentListDialog&task_id={$row['task_id']}&showHTML=0";
        $deleteUrl   = "/index.php?_room=task&_spAction=deleteTask&task_id={$row['task_id']}&showHTML=0";
        $formActionLinkedStud = "/index.php?_room=task&_spAction=linkedStudentsMain&showHTML=0";
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
        
        $goToList = $cpUrl->getUrlByCatType('Task');

        /********************************************************************/
        $text = "
        <div class='curveBox'>
            <h1>{$ln->gd('taskDetail')}</h1>
            <div class='btnTopAbs'>
                <a class='ic-back back ml5' href='{$goToList}'><span>{$ln->gd('back')}</a>
            </div>
            <div class='curveBoxInner'>
                <p>
                    <a class='ic-back' href='{$editTaskUrl}'>{$ln->gd('edit')}</a>
                    <a class='ic-back' href='javascript:void(0)' id='deleteTask' link='{$deleteUrl}'>{$ln->gd('delete')}</a>
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
    function getNewTask() {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15'));

        $task_id = '';
        $formAction = "/index.php?module=edukloud_task&_spAction=newTaskSubmit&showHTML=0";

        $sqlSubject = "
        SELECT subject_id
              ,title AS subject_title
        FROM subject
        WHERE subject_id IN 
              (
               SELECT subject_id
               FROM staff_subject
               WHERE staff_id = {$_SESSION['cpContactId']}
              )
        ORDER BY title
        ";

        $sqlType = "
        SELECT value
              ,value
        FROM valuelist
        WHERE key_text = 'taskType'
        AND value != 'EAE'
        ORDER BY sort_order
        ";
        $cancelButton = "
        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
        ";

        $sessionID = session_id();

        $staff_id = $_SESSION['cpContactId'];
        $current_date = date('Y-m-d');
        $expiry_date  = date('Y') . '-12-31';

        $frmTxt = "
        <form id='editFormCommon' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow($ln->gd('title'). ' * ', 'title')}
                {$formObj->getDDRowBySQL($ln->gd('subject'). ' * ', 'subject_id', $sqlSubject)}
                {$formObj->getDDRowBySQL($ln->gd('type'). ' * ', 'type', $sqlType)}
                {$formObj->getDateRow($ln->gd('dueDate'). ' * ', 'due_date', '')}
                {$formObj->getDateRow($ln->gd('launchDate'), 'launch_date', $current_date)}
                {$formObj->getDateRow($ln->gd('expiryDate'), 'expiry_date', $expiry_date)}
                {$formObj->getHTMLEditor($ln->gd('description') . ' * ', 'description', '')}
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
                <input type='hidden' name='task_id' value='{$task_id}' />
                <input type='hidden' name='current_date' value='{$current_date}' />
            </fieldset>
        </form>
        ";

        $text = "
        {$frmTxt}
        <p><strong>{$ln->gd('mandatoryFieldText')}</strong></p>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditTask() {
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
        
        $formAction = "/index.php?module=edukloud_task&_spAction=editTaskSubmit&showHTML=0";
        $sessionID  = session_id();
        $staff_id   = $_SESSION['cpContactId'];

        $task_id = $fn->getReqParam('task_id');
        if (trim($task_id) == ''){
            exit('invalid access');
        }
        
        $SQL = "
        SELECT t.*
              ,s.title AS subject_title
        FROM task t 
        LEFT JOIN (subject s) ON (s.subject_id = t.subject_id )
        WHERE t.staff_id = {$staff_id}
          AND t.task_id = {$task_id}
          AND t.published = 1
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlSubject = "
        SELECT subject_id
              ,title AS subject_title
        FROM subject
        WHERE subject_id IN (
               SELECT subject_id
               FROM   staff_subject
               WHERE  staff_id = '{$_SESSION['cpContactId']}'
               )
        ORDER BY title
        ";

        $sqlType = "
        SELECT value
              ,value
        FROM valuelist
        WHERE key_text = 'taskType'
        AND value != 'EAE'
        ORDER BY sort_order
        ";
        
        /************** Media related stuff *********************/
        //$default = $media->getDefaultMediaRecord('record_id', $task_id);
        $picExp = array('zoomImage' => 1, 'zoomPlugin' => 'colorbox', 'folder' => 'thumb');
        //$attPic = $media->getMediaAttachmentPicture('task', 'record_id', $row['task_id'], 'attachment', $picExp);
        $default = '';
        $attPic = '';

        $attPicTxt = "
        <div class='floatbox'>
            <div class='float_left mt10 picDefaultWrap'>
                {$default}
            </div>    
            <div class='smallPicDisplayWrap'>
                {$attPic}
            </div>    
        </div>    
        ";

        $attTxt = "
        <div id='attachments' class='uploadWrap'>
            <div id='fileQueueAtt'></div>
            <div class='floatbox'>
                <div class='float_left mr10'>
                    <input type='file' name='uploadifyAtt' id='uploadifyAtt' />
                </div>
                <div class='float_left'>
                    <a class='uploadQueue' href='' record_type='attachment'><img src='/images/icons/btn_upload_files.png' /></a>
                </div>
                <div class='float_left'>
                    <a class='clearQueue'  href=''><img src='/images/icons/btn_clear_queue.png' /></a>
                </div>
            </div>
        </div>
        ";

        $att2Txt = "
        <div id='attachments2' class='uploadWrap'>
            <div id='fileQueueAtt2'></div>
            <div class='floatbox'>
                <div class='float_left mr10'>
                    <input type='file' name='uploadifyAtt2' id='uploadifyAtt2' />
                </div>
                <div class='float_left'>
                    <a class='uploadQueue' href='' record_type='audio'><img src='/images/icons/btn_upload_files.png' /></a>
                </div>
                <div class='float_left'>
                    <a class='clearQueue'  href=''><img src='/images/icons/btn_clear_queue.png' /></a>
                </div>
            </div>
        </div>
        ";

        $linkTxt = "
        <div class='videoLinks'>
        </div>
        ";

        /********************************************************************/
        $launch_date = $row['launch_date'];
        $current_date = date('Y-m-d');
        
        //$sucRetUrl = $fn->make_seo_url($urlArray, 0, $urlTask);
        $sucRetUrl = '';
        $cancelButton = "
        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
        ";
        	
        $expDate = array('fieldCls' => 'dateFld');
        $expDesc = array('fieldCls' => 'htmlArea');
        $frmTxt = "
        <form id='editFormCommon' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getTBRow($ln->gd('title'), 'title', $row['title'])}
                {$formObj->getDDRowBySQL($ln->gd('subject'), 'subject_id', $sqlSubject, $row['subject_id'])}
                {$formObj->getDDRowBySQL($ln->gd('type'), 'type', $sqlType, $row['type'])}
                {$formObj->getDateRow($ln->gd('dueDate'), 'due_date', $row['due_date'])}
                {$formObj->getDateRow($ln->gd('launchDate'), 'launch_date', $row['launch_date'])}
                {$formObj->getDateRow($ln->gd('expiryDate'), 'expiry_date', $row['expiry_date'])}
                {$formObj->getHTMLEditor($ln->gd('description'), 'description', $row['description'])}
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
            </fieldset>
            <input type='hidden' id='record_id' name='task_id' value='{$task_id}' />
        </form>
        ";
        
        $cbExp = array('cls' => 'mt10');
        $goToList = $cpUrl->getUrlByCatType('Task');
        $text = "
        <div class='curveBox small'>
            <h3>{$ln->gd('editTask')}</h3>
            <div class='btnTopAbs'>
                <a class='ic-back ml5' href='{$goToList}'><span>{$ln->gd('cp.backToList')}</a>
            </div>
            <div class='curveBoxInner'>
                <div class='subcolumns'>
                    <div class='c62l editLeftCommon'>
                        <div class='subcl'>
                            {$frmTxt}
                            {$this->getRightPanel($row)}
                        </div>
                    </div>
                    <div class='c38r'>
                        <div class='subcr rightPortal'>
                            {$displayLinkData->getLinkPortalMain('edukloud_task', 'edukloud_classLink', 'Classes Linked', $row)}
                            {$displayLinkData->getLinkPortalMain('edukloud_task', 'edukloud_studentLink', 'Students Linked', $row)}
                        </div>
                    </div>
                </div>
            </div>
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
        {$media->getRightPanelMediaDisplay('Picture', 'edukloud_task', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'edukloud_task', 'attachment', $row)}
        ";
        return $text;
    }

}
