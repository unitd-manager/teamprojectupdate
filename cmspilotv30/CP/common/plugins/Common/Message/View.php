<?
class CP_Common_Plugins_Common_Message_View extends CP_Common_Lib_PluginViewAbstract
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

        $star_rating = '';
        if ($c->showStarRating){
            $star_rating = "
            <script>
                $(function(){
                    $('.star').rating({
                    });
                });
            </script>
            ";
        }

        $scrollText = '';
        if ($c->scrollContent){
            CP_Common_Lib_Registry::arrayMerge('jssKeys', array("simplyscroll-1.0.4"));
        
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                exp = {
                     handle: '{$c->scrollHandle}'
                    ,speed: '{$c->scrollSpeed}'
                    ,scrollDirection: '{$c->scrollDirection}'
                    ,scrollClass: '{$c->scrollClass}'
                    ,autoMode: '{$c->scrollAutoMode}'
                }
                cpw.content.record.simplyScroll(exp);
            "));
        }
                        
        $id = ($c->scrollContent) ? " id='{$c->scrollHandle}'" : '';

        if ($c->scrollContent){
            $text = "
            <ul{$id}>
                {$this->getMessagesList($roomName, $record_id, $contactModule)}
            </ul>
            ";

        } else {
            $text = "
            <div class='linkPortalWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                    <div class='float_left'>
                        {$c->heading} ({$count})
                    </div>
                    <div class='float_right'>
                        <a href='javascript:void(0);' id='toggleMessages'>{$c->addReviewLbl}</a>
                    </div>
                    </div>
                </div>
                {$this->getNew($roomName, $record_id, $contactModule)}
                <div id='messagesList'>
                    <ul{$id}>
                        {$this->getMessagesList($roomName, $record_id, $contactModule)}
                    </ul>
                </div>
            </div>
            {$star_rating}
            ";
        }

        return $text;
    }

    //==================================================================//
    function getMessageTitle($module, $record_id) {
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
    function getMessagesList($roomName, $record_id, $contactModule) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $c = &$this->controller;

        $dataArray = $this->model->dataArray;

        $text  = "";
        $rows = "";

        foreach($dataArray AS $row) {
            $creation_date = $dateUtil->formatDate($row['creation_date'], "DD MMM YYYY HH:MIN:SS AM");
            $name = $row['name'];
            
            if ($name == ''){
                $name = isset($row['contact_name']) ? $row['contact_name'] : '';
            }

            $edit = '';
            $delete = '';
            
            if ($c->allowEdit){
                $edit = "<a href='javascript:void(0);' class='editMessage' record_id='{$row['message_id']}'>Edit</a>";
            } 
            
            if ($c->allowDelete){
                $delete = "<a href='javascript:void(0);' class='deleteMessage' contact_module='{$contactModule}' record_id='{$row['message_id']}'> | Delete</a>";
            } 
            
            $star_rating = '';
            if ($c->showStarRating){
                $star_rating = "
                {$formObj->getStarRatingRow('', 'rating' . $row['message_id'], $row['rating'], true)}
                ";
            }
        
            $rows .= "
            <li>
                <div class='floatbox'>
                    <h5 class='float_left name'>{$name}</h5>
                    <div class='float_right'>
                        {$edit}
                        {$delete}
                        {$star_rating}
                    </div>
                </div>
                <p class='date'>{$creation_date}</p>
                <p class='message'>". nl2br($row['description']) . "</p>
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

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_message&_spAction=add&showHTML=0";
        
        $name = '';
        $email = '';
        $subject = '';
        $star_rating = '';
        $captchaText = '';

        $formObj->mode = 'edit';
 
        if ($c->showSubject){
            $subject = "
            {$formObj->getTBRow($ln->gd('cp.form.message.fld.subject.lbl'), 'subject')}
            ";
        }

        if ($c->showStarRating){
            $star_rating = "
            {$formObj->getStarRatingRow($ln->gd('cp.form.message.fld.rating.lbl'), 'rating')}
            ";
        }

        if (CP_SCOPE == 'admin'){
            $description = "
            <div class='full'>
                {$formObj->getTARow('', 'description')}
            </div>
            ";
        } else {
            $description = "
            {$formObj->getTARow($ln->gd('cp.form.fld.messages.lbl'), 'description')}
            ";
        }
 
        if ($c->showCaptcha){
            $captchaText = "{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}";
        }

        $text = "
        <div id='messageForm'>
            <form id='frmMessage' method='post' class='{$c->formClass}' action='{$formAction}'>
                <fieldset>
                    {$star_rating}
                    {$subject}
                    {$description}
                    {$captchaText}
                    <div class='type-button'>
                        <input type='submit' value='Submit' id='addMessage' />
                        <input type='reset' value='Cancel' id='cancelMessage' />
                    </div>
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
    function getEdit() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $record_id = $fn->getReqParam('record_id');
        $rec = $fn->getRecordRowByID('message', 'message_id', $record_id);

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_message&_spAction=save&showHTML=0";
        
        $text = "
        <div class='full'>
        <form id='editMessage' method='post' class='yform' action='{$formAction}'>
            <fieldset>
                <div class='type-text'>
                    <textarea name='description' id='messagesText'>{$rec['description']}</textarea>
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