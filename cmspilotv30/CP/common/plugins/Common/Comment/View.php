<?
class CP_Common_Plugins_Common_Comment_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getView() {
        $c = &$this->controller;
        $roomName      = $c->roomName;
        $record_id     = $c->recordId;
        $contactModule = $c->contactModule;

        $tv = Zend_Registry::get('tv');

        if ($tv['action'] == 'new' || $tv['action'] == 'search'){
            return;
        }

        $dataArray = $this->model->dataArray;
        $count = count($dataArray);

        $scrollText = '';
        if ($c->scrollContent){
            CP_Common_Lib_Registry::arrayMerge('jssKeys', array("simplyscroll-1.0.4"));

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {
                     handle: '{$c->scrollHandle}'
                    ,speed: '{$c->scrollSpeed}'
                    ,frameRate: '{$c->scrollFrameRate}'
                    ,scrollDirection: '{$c->scrollDirection}'
                    ,scrollClass: '{$c->scrollClass}'
                    ,autoMode: '{$c->scrollAutoMode}'
                }
                cpw.content.record.simplyScroll(exp);
            "));
        }

        $id = ($c->scrollContent) ? " id='{$c->scrollHandle}'" : '';
        
        $addReviewBtn = '';
        if ($c->allowAdd){
            $addReviewBtn = "
            <div class='addReviewBtnWrapper'>
                <a href='javascript:void(0);' id='toggleComments'>{$c->addReviewLbl}</a>
            </div>
            ";
        }
        
        if ($c->scrollContent){
            $text = "
            <ul{$id}>
                {$this->getCommentsList($roomName, $record_id, $contactModule)}
            </ul>
            ";

        } else {
            $text = "
            <div class='linkPortalWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                        <div class='countWrapper'>
                            {$c->heading} ({$count})
                        </div>
                        {$addReviewBtn}
                    </div>
                </div>
                {$this->getNew($roomName, $record_id, $contactModule)}
                <div id='commentsList'>
                    <ul{$id}>
                        {$this->getCommentsList($roomName, $record_id, $contactModule)}
                    </ul>
                </div>
            </div>
            ";
        }

        return $text;
    }

    //==================================================================//
    function getCommentTitle($module, $record_id) {
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');

        $tableName = $modulesArr[$module]["tableName"];
        $keyField  = $modulesArr[$module]["keyField"];

        $SQL     = "SELECT title FROM {$tableName} AS title WHERE {$keyField} = {$record_id}";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //==================================================================//
        if ($numRows > 0) {
            $row    = $db->sql_fetchrow($result);
            return $row["title"];
        } else {
            return "record deleted";
        }
    }

    //==================================================================//
    function getCommentsList($roomName, $record_id, $contactModule) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $formObj = Zend_Registry::get('formObj');

        $c = &$this->controller;

        $dataArray = $this->model->dataArray;

        $text  = "";
        $rows = "";

        foreach($dataArray AS $row) {
            $comment_date = $dateUtil->formatDate($row['comment_date'], "DD MMM YYYY HH:MIN:SS AM");
            $name = $row['name'];

            if ($name == ''){
                $name = isset($row['contact_name']) ? $row['contact_name'] : '';
            }

            $edit = '';
            $delete = '';

            if ($c->allowEdit){
                $edit = "<a href='javascript:void(0);' class='editComment' record_id='{$row['comment_id']}'>Edit</a>";
            }

            if ($c->allowDelete){
                $delete = "<a href='javascript:void(0);' class='deleteComment' contact_module='{$contactModule}' record_id='{$row['comment_id']}'> | Delete</a>";
            }

            $star_rating = '';
            if ($c->showStarRating){
                $rating = $cpUtil->ceilWithSignificance($row['rating'], 0.5);
                $rating = str_replace('.', '', $rating * 10);
                $rating = $rating > 0 ? $rating : 0;
    
                $star_rating = "
                <span class='rating-static rating-{$rating}'></span>
                ";
            }

            $contactName = "<h5 class='name'>{$name}</h5>";
            $picTxt = '';

            if ($c->showContactPic){
                $pic = '';

                if ($row['contact_module'] != ''){                
                    $exp = array('folder' => 'thumb');
                    $pic = $media->getMediaPicture($row['contact_module'], 'picture', $row['contact_id'], $exp);
                }
    
                if ($pic == ''){
                    $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/contact-icon.png' />";
                }
                
                $url = $cpUrl->getUrlByCatType('Public Profile Dashboard') . "?cpi={$row['contact_id']}";
                $picTxt = "
                <a href='{$url}'>{$pic}</a>
                ";
            }

            $headerRow = "
            <div class='headerRow'>
                {$contactName}
                <div class='date'>{$comment_date}</div>
                <div class='float_right '>
                    {$edit}
                    {$delete}
                    {$star_rating}
                </div>
            </div>
            ";

            $commentPics = '';
            if ($c->displayCommentPics){
                $exp = array('folder' => 'thumb', 'zoomImage' => true, 'style' => 'thumb');
                $commentPics = $media->getMediaPicture('common_comment', 'picture', $row['comment_id'], $exp);
            }            
            
            if ($commentPics != ''){
                $commentPics = "<div class='commentPics'>{$commentPics}</div>";
            }
            
            $reportAbuseText = '';
            if ($c->hasReportAbuse){
                $reportAbuseText = "
                <div class='reportAbuse'>
                    <a href='javascript:void(0);' cid='{$row['comment_id']}'>Report Abuse</a>
                </div>
                ";
            }            

            $commentTxt = "<p class='comment'>". nl2br($row['comments']) . "</p>";

            if ($c->hasReportAbuse && $row['abusive'] == 1 && $row['verified'] == 0){
                $commentTxt = $ln->gd('cp.form.comment.reportAbuse.message.pendingVerification');
                $commentPics = '';
            }
            
            if($c->showContactPic){
                $rowTxt = "
                <div class='withPic'>
                    <div class='pic'>
                        {$picTxt}
                    </div>
                    <div class='txt'>
                        {$headerRow}
                        {$commentTxt}
                        {$commentPics}
                        {$reportAbuseText}
                    </div>
                </div>
                ";
            } else {
                $rowTxt = "
                {$headerRow}
                {$commentTxt}
                {$reportAbuseText}
                ";
            }
            
            $rows .= "
            <li>
                {$rowTxt}
            </li>
            ";
        }

        return $rows;
    }

    //==================================================================//
    function getNew($roomName, $record_id, $contactModule) {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        $viewHelper = Zend_Registry::get('viewHelper');

        $addBtnText = "";

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_comment&_spAction=add&showHTML=0";
        
        if (CP_SCOPE == 'www' && $c->loginRequiredToPost && !isLoggedInWWW()){
            $text = "
            <div id='commentForm' class='loginRequiredText'>
                {$c->loginRequiredText}
            </div>
            ";
            
            return $text;
        }
        
        $name = '';
        $email = '';
        $subject = '';
        $star_rating = '';
        $captchaText = '';

        $formObj->mode = 'edit';

        if ($c->nameRequired){
            $nameVal = '';

            if (CP_SCOPE == 'www' && isLoggedInWWW()){
                $sLoginType = $fn->getSessionParam('cpLoginTypeWWW');
                $sContactId = $fn->getSessionParam('cpContactId');

                if ($sLoginType != ''){
                    $tableName = $modulesArr[$sLoginType]['tableName'];
                    $keyField  = $modulesArr[$sLoginType]['keyField'];
                    $userRec = $fn->getRecordRowByID($tableName, $keyField, $sContactId);

                    if (isset($userRec['alias_name']) && $userRec['alias_name'] != ''){
                        $nameVal = $userRec['alias_name'];
                    } else {
                        $nameVal = $_SESSION['cpUserFullNameWWW'];
                    }
                }
            }

            $name = "
            {$formObj->getTBRow($ln->gd('cp.form.fld.name.lbl'), 'name', $nameVal)}
            ";
        }

        if ($c->emailRequired){
            $emailNotes = $ln->gd2('cp.form.comment.fld.email.notes');

            $exp = array();
            if ($emailNotes != ''){
                $exp = array('notes' => $emailNotes);
            }

            $email = "
            {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', '', $exp)}
            ";
        }

        if ($c->showSubject){
            $subject = "
            {$formObj->getTBRow($ln->gd('cp.form.comment.fld.subject.lbl'), 'subject')}
            ";
        }

        if ($c->showStarRating){
            $star_rating = "
            {$formObj->getStarRatingRow($ln->gd('cp.form.comment.fld.rating.lbl'), 'rating')}
            ";
        }

        if (CP_SCOPE == 'admin'){
            $comments = "
            <div class='full'>
                {$formObj->getTARow('', 'comments')}
            </div>
            ";
        } else {
            $comments = "
            {$formObj->getTARow($ln->gd('cp.form.fld.comments.lbl'), 'comments')}
            ";
        }

        if ($c->showCaptcha){
            $captchaText = "{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}";
        }
        
        $fileUpload = '';
        if ($c->hasFileUpload){
            $fileUpload = "{$formObj->getUploadRow($ln->gd('cp.form.comment.fld.file.lbl'), 'fileName[]')}";
        }

        $notifyCbox = '';
        if ($c->showNotifyCbox){
            $notifyCbox = "
            {$formObj->getSingleCheckBoxRow($ln->gd('cp.form.fld.notify.lbl'), 'notify')}
            ";
        }

        $text = "
        <div id='commentForm'>
            <form id='frmComment' method='post' class='{$c->formClass}' action='{$formAction}'>
                <fieldset>
                    {$star_rating}
                    {$subject}
                    {$name}
                    {$email}
                    {$comments}
                    {$captchaText}
                    {$fileUpload}
                    {$notifyCbox}
                    <div class='type-button'>
                        <input type='submit' value='Submit' id='addComment' />
                        <input type='reset' value='Cancel' id='cancelComment' />
                    </div>
                    <input type='hidden' name='room_name' value='{$roomName}'>
                    <input type='hidden' name='record_id' value='{$record_id}'>
                    <input type='hidden' name='contact_module' value='{$contactModule}'>
                    {$viewHelper->getPluginPropertiesInHiddenVariable($c->name, $c)}
                </fieldset>
            </form>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15'));

        return $text;
    }

    //==================================================================//
    function getSpecialRatingDisplay($comment_id) {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $action = '/index.php?plugin=common_comment&_spAction=saveSpecialRating&showHTML=0';

        $text = "
        <div id='commentSpecialRating'>
            <form id='frmSpecialRating' method='post' class='yform columnar' action='{$action}'>
                <fieldset>
                    {$formObj->getStarRatingRow($ln->gd('cp.form.comment.fld.rating.food'), 'rating_food')}
                    {$formObj->getStarRatingRow($ln->gd('cp.form.comment.fld.rating.value'), 'rating_value')}
                    {$formObj->getStarRatingRow($ln->gd('cp.form.comment.fld.rating.atmosphere'), 'rating_atmosphere')}
                    {$formObj->getStarRatingRow($ln->gd('cp.form.comment.fld.rating.service'), 'rating_service')}
                    <input type='hidden' name='comment_id' value='{$comment_id}'>
                    <input type='hidden' name='dialogMessage' value='{$ln->gd('cp.lbl.updatedSuccessfully')}'>
                </fieldset>
            </form>
        </div>
        ";

        return $text;
    }

    //==================================================================//
    function getEdit() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $record_id = $fn->getReqParam('record_id');
        $rec = $fn->getRecordRowByID('comment', 'comment_id', $record_id);

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_comment&_spAction=save&showHTML=0";

        $name = '';

        $c = $this->controller;

        if (array_key_exists('name', $rec) && $c->allowEditName){
            $name = $formObj->getTBRow('Name', 'name', $rec['name']);
        }

        $text = "
        <div class='full'>
        <form id='editComment' method='post' class='yform' action='{$formAction}'>
            <fieldset>
                {$name}
                <div class='type-text'>
                    <textarea name='comments' id='commentsText'>{$rec['comments']}</textarea>
                </div>
                <input type='hidden' name='record_id' value='{$record_id}'>
            </fieldset>
        </form>
        </div>
        ";

        return $text;
    }

    //==================================================================//
}