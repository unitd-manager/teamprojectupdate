<?
class CP_Www_Themes_FullScreen_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
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

        } else if ($tv['catType'] == 'Flipbook Form') {
            $text = $contObj->getFlipBookForm();

        } else if ($tv['secType'] == 'List in Detail' 
                || $tv['catType'] == 'List in Detail'  
                || $tv['catType'] == 'People'
                || $tv['subCatType'] == 'People2') {
            $text = $contObj->getList('listInDetail');

        } else if ($tv['secType'] == 'News' || $tv['secType'] == 'Career') {
            $text = $contObj->getList('listInDetail');

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $contObj->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContactUsListHook($dataArray) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        if ($tv['catType'] == 'Enquiry Form'){
            return $this->getModuleWebBasicContactUsNewHook();
        }
                
        $text = '';
        foreach ($dataArray as $row){
            $text = "
            <div class='subcolumns contactUsList'>
                <div class='c40l'>
                    <div class='subcl'>
                        <h1 class='catTitle'>{$tv['catTitle']}</h1>
                        <div class='embedObj'>{$row['embed_code']}</div>
                    </div>
                </div>
                <div class='c60r'>
                    <div class='subcr'>
                        {$ln->gfv($row, 'description')}
                    </div>
                </div>
            </div>
            ";
            $text = $fn->replaceLangKeys($text);
        }

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContactUsNewHook() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "/index.php?module=webBasic_contactUs&_spAction=add&showHTML=0";
        
        $infoText = '';
        
        if ($ln->gd2('m.webBasic.contactUs.form.enquiry.info') != ''){
            $infoText = "<p>{$ln->gd('m.webBasic.contactUs.form.enquiry.info')}</p>";
        }
        
        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.webBasic.contactUs.form.enquiry.message.success')}' />
            <fieldset>
                <h1>{$ln->gd('m.webBasic.contactUs.form.enquiry.heading')}</h1>
                {$infoText}
                <div class='subcolumns'>
                    <div class='c50l'>
                        <div class='subcl'>
                            {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                            {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                            {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                            {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                        </div>
                    </div>
                    <div class='c50r'>
                        <div class='subcr'>
                            {$formObj->getTBRow($ln->gd('cp.form.fld.subject.lbl'), 'subject')}
                            {$formObj->getTARow($ln->gd('cp.form.fld.enquiry.lbl'), 'comments')}
      	    	            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
                        </div>
                    </div>
                </div>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_right btnSubmitWrap'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />                
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContactUsAddHook1() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getModuleWebBasicContactUsNewValidateHook()){
            return $validate->getErrorMessageXML();
        }

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['name']          = $fn->getPostParam('name');
        $fa['email']         = $fn->getPostParam('email');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['subject']       = $fn->getPostParam('subject');
        $fa['comments']      = $fn->getPostParam('comments');
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "enquiry");
        $result      = $db->sql_query($SQL);
        $contact_id  = $db->sql_nextid();

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd("m.webBasic.contactUs.form.enquiry.email.notifyBody");
        $message = str_replace("[[name]]"        , $fa["name"]       , $message );
        $message = str_replace("[[email]]"       , $fa["email"]      , $message );
        $message = str_replace("[[phone]]"       , $fa["phone"]      , $message );
        $message = str_replace("[[subject]]"     , $fa["subject"]    , $message );
        $message = str_replace("[[comments]]"    , $fa["comments"]   , $message );
        $message = str_replace("[[currentDate]]" , $currentDate      , $message );

        $subject     = $ln->gd("m.webBasic.contactUs.form.enquiry.email.notifySubject");
        $fromName    = $fa['name'];
        $fromEmail   = $fa['email'];
        $toName      = $cpCfg['cp.companyName'];
        $toEmail     = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $cpCfg['cp.companyName']
            ,'toEmail'   => $cpCfg['cp.adminEmail']
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getModuleGalleryProjectListHook($dataArray) {
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $subCatRec = '';
        $subRoom = '';
        $description = '';
        
        if($tv['subRoom'] != ''){
            $subRoom = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);

            if (is_array($subRoom)){
                $subRoom = $ln->gfv($subRoom, 'description_short');
            }
        }

        if($tv['subCat'] != ''){
            $subCatRec = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['subCat']);

            if (is_array($subCatRec)){
                $subCatRec = $ln->gfv($subCatRec, 'description_short');
            }
        }

        foreach ($dataArray as $row){
            //$exp = array('zoomImage' => false, 'folder' => $cpCfg['m.gallery.project.list.picSize']);
            $url = $cpUrl->getUrlByRecord($row, 'project_id');

            $picArr = $media->getFirstMediaRecord('gallery_project', 'picture', $row['project_id']);
            
            $pic = '';
            if(count($picArr) > 0){
                $pic = $picArr['file_normal'];
            }
            
            $rows .= "            
            <div class='projectTitle'>
                <a href='{$url}' pic='{$pic}'>{$ln->gfv($row, 'title')}</a>
            </div>
            ";
            
            $description = $row['description'];
        }
        
        if($tv['subCat'] == ''){  
            $text ="
            <div class='desc'>{$subRoom}</div>
            ";
        } else {
            $text = "            
            <div class='subcolumns projectList'>
                <div class='c40l'>
                    <div class='subcl'>
                        <h1 class='catTitle'>{$tv['subCatTitle']}</h1>
                        <div class='pic'></div>
                        <div class='desc'>{$subCatRec}</div>
                    </div>
                </div>
                <div class='c60r'>
                    <div class='subcr'>
                        <h1>{$ln->gd('m.gallery.project.selectProject.title')}</h1>
                        {$rows}
                    </div>
                </div>
            </div>
            ";
        }


        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     */
    function getModuleGalleryProjectDetailHook($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = "            
        <div class='subcolumns projectDetail'>
            <div class='c50l'>
                <div class='subcl'>
                    <h1 class='catTitle'><a href='{$cpUrl->getCurrentCategoryUrl()}'>{$tv['subCatTitle']}</a></h1>
                    <div class='inner'>
                        <h2>{$ln->gfv($row, 'title')}</h2>
                        <div class='desc'>
                            {$ln->gfv($row, 'description')}
                        </div>
                    </div>
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    <div class='floatbox'>
                        <div class='float_right'>
                            <a href='{$cpUrl->getCurrentCategoryUrl()}' class='cpBack'>
                                {$ln->gd('cp.lbl.backToProject')}
                            </a>
                        </div>
                    </div>
                	<!-- 
                	<div id='prevthumb'></div>
                	<div id='nextthumb'></div>
                	-->
        
                	<div id='supersizeThumbTray' class='load-item'>
                	    <!-- 
                		<div id='thumb-back'></div>
                		<div id='thumb-forward'></div>
                	    -->
                	</div>
                </div>
            </div>
        </div>
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }
}