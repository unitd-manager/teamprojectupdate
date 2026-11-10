<?
class CP_Www_Themes_Dealon_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        foreach ($dataArray as $row){
        }

        $text = "
        ";

        return $text; 
    }
    
    /**
     *
     */
    function getModuleWebBasicContentControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        

        if ($tv['secType'] == 'Site Search') {
            $pSiteSearch = getCPPluginObj('common_siteSearch');
            $text = $pSiteSearch->getView();

        } else if ($tv['secType'] == 'Testimonials' || $tv['catType'] == 'Testimonials' ) {
            $text = $contObj->getList('testimonialsList');

        } else if ($tv['secType'] == 'Press Release' || $tv['catType'] == 'Press Release' ) {
            if ($tv['record_id'] > 0){
                $text = $contObj->getDetail('pressDetail');
            } else {
                $text = $contObj->getList('pressList');
            }
        } else if ($tv['secType'] == 'My Order') {
            $text = $this->getMyOrder();

        } else if ($tv['secType'] == 'Tab Content' || $tv['catType'] == 'Tab Content') {
            $text = $contObj->getList('listAsTabs');

        } else if ($tv['secType'] == 'Accordian Content' || $tv['catType'] == 'Accordian Content') {
            $text = $contObj->getList('listAsAccordian');

        } else if ($tv['secType'] == 'List in Detail' || $tv['catType'] == 'List in Detail') {
            $text = $contObj->getList('listInDetail');

        } else if ($tv['secType'] == 'List Detail Combo' || $tv['catType'] == 'List Detail Combo') {
            $text = $contObj->getList('listDetailCombo');

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $contObj->$fnName();
            
            if ($tv['secType'] == 'Refer a Friend') {
                $wEmailToFriend = getCPWidgetObj('social_emailToFriend');
                $text .= $wEmailToFriend->getWidget();
            }
        }

        return $text;
    }
    /**
     *
     */
    function getLatestNews1() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,ca.title AS category_title
        FROM content c
        LEFT JOIN category ca     ON (c.category_id     = ca.category_id)
        LEFT JOIN section s     ON (c.section_id     = s.section_id)
        WHERE c.latest = 1 
        AND section_type = 'News'
        AND c.published = 1
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id', array('secType'=>'News'));
            $title ="
            <div class='title'>
                <a href='{$url}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 70);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
            <div class='subcolumns'>
            <div class='latestNews'>
                <div class='c25l'>
                    <div class='subcl'>
                        <img src='/www/images/news.png'/>
                    </div>
                </div>
                <div class='c75r'>
                    <div class='subcr'>
                        <div class='mb5'>{$title}</div>
                        <div>{$desc}</div>
                    </div>
                </div>
            </div>
            </div>
            ";
        }

        $text = "
        <div class='news'>
        <h1>{$ln->gd('latestNews')}</h1>
        <marquee behavior='scroll' direction='up' scrollamount='1' height='170'>
        {$rows}
        </marquee>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getContentRecord1($content_type) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,ca.title AS category_title
        FROM content c
        LEFT JOIN category ca     ON (c.category_id     = ca.category_id)
        LEFT JOIN section s     ON (c.section_id     = s.section_id)
        WHERE c.published = 1 
        AND c.content_type = '{$content_type}'
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows ='';
        $pic = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $title ="
            <div class='title'>
                <h1>{$ln->gfv($row, 'title')}</h1>
            </div>
            ";

            $desc = $cpUtil->getSubString($ln->gfv($row, "description"), 100);
            $desc = (trim($desc) != "") ? trim($desc) . "..." : "";

            $exp = array('style' => '', 'folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp );

            $rows .= "
                <div class='c33l'>
                    <div class='subcl'>
                        {$pic}
                    </div>
                </div>
                <div class='c66r'>
                    <div class='subcr'>
                        <div class='mb5'>{$title}</div>
                        <div>{$desc}</div>
                    </div>
                </div>
            ";
        }

        $text = "
        <div class='homeContent'>
        {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPopupForm() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;
        $formAction = "/index.php?_theme=dealon&_spAction=popupFormSubmit&showHTML=0";
        $retUrlText = "/eng/";
                
        $emailInQstr= $fn->getReqParam('email');

        //$tempCount = $_SESSION['popup'];
        $text = "
        <form name='newsletterFormPopup' id='newsletterFormPopup' class='popupNewsletter' method='post' action='{$formAction}'>
            <fieldset>
                <div class='newsletterTitle'>{$ln->gd('cp.form.fld.yourEmail.lbl')}</div>
                <div class='floatbox'>
                    <div class='float_left'>{$formObj->getTBRow('', 'email')}</div>
                    <div class='float_left'><input type='submit' class='subBtn' value=''/></div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' onclick='history.back() />
            </fieldset>
        </form>
        ";
                //<input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('w.member.newsletterSignup.message.sucecss')) . "' />
        
        $_SESSION['cpSignupModalDisplayedAlready'] = 1;

        return $text;
    }
    /**
     *
     */
    function getPopupFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');
        
        if (!$this->getPopupFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $email = $fn->getPostParam('email');
        
        $SQL = "
        SELECT * 
        FROM contact
        WHERE email = '{$email}'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $fa = array();
        $fa['subscribe'] = 1;
        $fa['email'] = $email;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'contact');

        //-----------------------------------------------------------------------//
        if ($numRows == 0) {
            $SQLInsert    = $dbUtil->getInsertSQLStringFromArray($fa, 'contact');
            $result = $db->sql_query($SQLInsert);
            $id     = $db->sql_nextid();
        } else {
            $updateSQL = "
            UPDATE contact
            SET subscribe = 1
            WHERE email = '{$email}'
            ";    
            $result = $db->sql_query($updateSQL);
        }        

        $subject   = $ln->gd('w.member.newsletterSignup.notifySubject');
        $message = $ln->gd('w.member.newsletterSignup.notifyBody');
        $message = str_replace("[[email]]"  , $email , $message );

        $args = array(
             'toName'    => $cpCfg['cp.companyName']
            ,'toEmail'   => $cpCfg['cp.adminEmail']
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => ''
            ,'fromEmail' => $email
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getPopupFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getMyOrder() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');

        $contact_id = $_SESSION['cpContactId'];
        $text = '';

        $SQL = "
        SELECT o.*
              ,oi.item_title
        FROM `order` o
        LEFT JOIN (order_item oi) ON (oi.order_id = o.order_id)
        WHERE o.contact_id = {$contact_id} 
         AND o.order_status = 'Paid'
        ";
        $result  = $db->sql_query($SQL);    

        while ($row = $db->sql_fetchrow($result)) {    
            $date = $fn->getCPDate($row['creation_date']);
            $saveUrl = '';

            $condn = "
            record_id = {$row['order_id']} 
            AND media_type = 'attachment'
            ";
            $orderby ="media_id DESC";
            $mediaRow = $fn->getRecordByCondition('media', $condn, $orderby);
            $media_id = $mediaRow['media_id'];
            
            $download = '';

            if($media_id){
                $saveUrl = "/index.php?plugin=common_media&_spAction=saveMedia" . 
                       "&room=ecommerce_order&media_id={$media_id}&showHTML=0";
                $download ="
                <div class='mt10'>
                    <a href='{$saveUrl}'>{$ln->gd('cp.downloadVoucher')} - {$date}</a>                    
                </div>
                ";
            }
            
            $text .= "
            <div class='mb20'>
                <h4>{$row['item_title']}</h4>
                {$download}
            </div>
            ";
        }

        $text = "
        <div class='fatList'>
            <h1>{$ln->gd('cp.myOrder')}</h1>
            {$text}
        </div>    
        ";
        return $text;
    }
}