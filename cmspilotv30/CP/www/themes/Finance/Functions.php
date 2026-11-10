<?
class CP_Www_Themes_Finance_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        foreach ($dataArray as $row){
        }

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_s3Slider');
        $slideshow = $wSlideshow->getWidget(array(
             'speed' => $cpCfg['cp.homeSlideshowSpeed']
            ,'width' => 735
            ,'height' => 200
        ));

        $wRecord = getCPWidgetObj('content_record');
        $calloutRight = $wRecord->getWidget(array(
             'contentType'    => 'Mission'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $stockPrice = $wRecord->getWidget(array(
             'contentType'    => 'Stock Price'
        ));

        $wRecord1 = getCPWidgetObj('content_record');
        $wRecord2 = getCPWidgetObj('content_record');
        $indexPrice = $wRecord->getWidget(array(
             'contentType'    => 'Index Price'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $recentResults = $wRecord->getWidget(array(
             'contentType'    => 'Most Recent Results'
        ));

        $wRecord2 = getCPWidgetObj('content_record');

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        $text = "
        {$slideshow}
        <div class='homeTop'>
        <div class='subcolumns'>
            <div class='c75l'>
                <div class='subcl'>
                    {$title}
                    {$ln->gfv($row, 'description')}
                </div>
            </div>
            <div class='c25r'>
                <div class='subcr rightPanel'>
                    {$calloutRight}
                </div>
            </div>
        </div>
        </div>

        <div class='homeBtm'>
        <div class='subcolumns'>
            <div class='c25l stock'>
                <div class='subcl'>
                    {$stockPrice}
                </div>
            </div>

            <div class='c25l spotlight'>
                <div class='subcl'>
                    {$wRecord1->getWidget(array(
                         'contentType' => 'Record'
                        ,'showGroupReadMore' => true
                        ,'groupReadMoreUrl' => $cpUrl->getUrlBySubCatType('Content', 'Press Release')
                        ,'groupReadMoreLbl' => 'cp.lbl.next'
                        ,'showDate' => false
                        ,'categoryId' => 11
                        ,'scrollContent' => true
                        ,'heading' => $ln->gd('w.content.record.pressRelease.heading')
                        ,'displayLimit' => 10

                        ,'addUrlForTitle' => true
                        ,'showShortDesc' => false
                        ,'showDesc' => false
                    ))}
                </div>
            </div>
            <div class='c25l latestNews'>
                <div class='subcl'>
                    {$wRecord2->getWidget(array(
                         'contentType' => 'Record'
                        ,'showGroupReadMore' => true
                        ,'heading' => $ln->gd('w.content.record.whatsnew.heading')
                        ,'groupReadMoreUrl' => $cpUrl->getUrlBySubCatType('Content', 'Announcement')
                        ,'groupReadMoreLbl' => 'cp.lbl.next'
                        ,'showDate' => false
                        ,'scrollContent' => true
                        ,'categoryId' => 12
                        ,'scrollHandle' => 'simplyScroll2'
                        ,'displayLimit' => 10

                        ,'addUrlForTitle' => true
                        ,'showShortDesc' => false
                        ,'showDesc' => false
                    ))}
                </div>
            </div>
            <div class='c25l price'>
                <div class='subcl'>
                    {$recentResults}
                </div>
            </div>
        </div>
        </div>
        ";

        return $text;
    }

    //========================================================//
    function getWidgetMemberNewsletterSignupRowsHTMLHook($widgetObj) {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = $widgetObj;

        $formAction = $c->formAction;
        $heading    = $ln->gd2($c->heading);
        $infoText   = $ln->gd2($c->infoText);
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $sqlCategory = "
        SELECT value
              ,value
        FROM valuelist
        WHERE key_text='category'
        ORDER BY sort_order
        ";

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($heading != ''){
            $heading = "<h1>{$heading}</h1>";
        }

        $retUrlText = '';
        if ($c->returnUrl){
            $retUrlText = "<input type='hidden' name='returnUrl' value='{$c->returnUrl}' />";
        }

        $text = "
        <form name='loginForm' id='loginForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                {$heading}
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl') .'*', 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl') .'*', 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl') .'*', 'email')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.company.lbl'), 'company_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.jobTitle.lbl'), 'position')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax')}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'country_code', $sqlCountry)}
                {$formObj->getCheckBoxArrRowBySQL($ln->gd('cp.form.fld.category.lbl'), 'category[]', $sqlCategory)}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('w.member.newsletterSignup.message.success')) . "' />
                {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContentListHook($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $rows = '';

        $addThisIcons = '';
        if ($tv['catType'] == 'Reports' || $tv['catType'] == 'Chi Annual Report' || $tv['catType'] == 'Chi Interim Report'){
            foreach ($dataArray as $row){
                $rowText = $this->getReportRow($row);
                
                if ($rowText != ''){
                    $rows .= "
                    <article class='row'>
                        {$rowText}
                    </article>
                    ";
                }
            }

        } else if ($tv['secType'] == 'Principal Investment'){
            foreach ($dataArray as $row){
                $rowText = $this->getPrincipalInvestmentRow($row);
                
                if ($rowText != ''){
                    $rows .= "
                    <article class='row'>
                        {$rowText}
                    </article>
                    ";
                }
            }

        } else {
            foreach ($dataArray as $row){
                $rows .= "
                <article class='row floatbox'>
                    {$this->getStandardFatListRow($row)}
                </article>
                ";
            }
    
            if ($cpCfg['m.webBasic.content.showAddThis']){
                $wAddThis = getCPWidgetObj('social_addThis');
                $addThisIcons = "
                {$wAddThis->getWidget(
                )}";
            }
        }

        $text = "
        <div class='fatList'>
            {$rows}
            {$addThisIcons}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getReportRow($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');

        $rows = '';

        $attArrEng = $media->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id'], 'eng');
        $attArrChi = $media->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id'], 'chi');

        $fileEng = '';
        $fileChi = '';

        $urlEng = '';
        $urlChi = '';

        if (count($attArrEng) > 0){
            $fileEng = $attArrEng['file_normal'];

            $urlEng = "
            <a href='{$attArrEng['file_normal']}' target='_blank' class='downloadFile'>
                {$ln->gd('cp.lbl.english')}
            </a>
            ";
        }

        if (count($attArrChi) > 0){
            $fileChi = $attArrChi['file_normal'];
            $urlChi = "&nbsp;|
            <a href='{$attArrChi['file_normal']}' target='_blank' class='downloadFile'>
                {$ln->gd('cp.lbl.chinese')}
            </a>
            ";
        }

        $url = ($tv['lang'] == 'chi') ? $fileChi : $fileEng;
        
        if ($url == ''){
            return;
        }

        $picArr = $media->getFirstMediaRecord('webBasic_content', 'picture', $row['content_id']);

        $langUrls = '';
        if ($urlEng != '' && $urlChi != '' && $tv['lang'] != 'chi'){
            $langUrls = "
            <div class='readMore'>
                {$urlEng}
                {$urlChi}
            </div>
            ";
        }
                    
        if (count($picArr) > 0){
            $text = "
            <h1>{$ln->gfv($row, 'title')}</h1>
            <a href='{$url}' target='_blank' class='downloadFile'><img src='{$picArr['file_normal']}'></a>
            {$langUrls}
            ";
            return $text;
        }
    }
    
    /**
     *
     */
    function getStandardFatListRow($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $rows = '';
        $title = ($row['show_title'] == 1) ? "<header><h1>{$ln->gfv($row, 'title', '0')}</h1></header>" : '';

        $exp = array('style' => 'pic');

        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='picWrap'>{$pic}</div>";
        }

        $wRecord = getCPWidgetObj('content_record');
        $calloutRight = $wRecord->getWidget(array(
              'contentType' => 'Callout Right'
             ,'strictToPage' => true
             ,'global' => false
             ,'showPicInDesc' => false
        ));

        if ($calloutRight != ''){
            $calloutRight = "
            <div class='calloutRight'>{$calloutRight}</div>
            ";
        }

        if ($pic != ''){
            $pic = "
            <div class='floatbox'>
                {$pic}
            </div>
            ";
        }

        if ($row['embed_code'] != ''){
            $text = "
            <div class='subcolumns'>
                <div class='c50l'>
                    <div class='subcl'>
                        {$title}
                        <div class='description'>
                            {$ln->gfv($row, 'description')}
                        </div>
                        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
                    </div>
                </div>
                <div class='c50r'>
                    <div class='subcr'>
                        <div class='embedObj'>{$row['embed_code']}</div>
                    </div>
                </div>
            </div>
            ";

        } else if ($calloutRight != '' || $pic != ''){
            $class = ($pic == '') ? " hasNoPic" : '';
            $text = "
            <div class='subcolumns'>
                <div class='c66l'>
                    <div class='subcl'>
                        {$title}
                        <div class='description'>
                            {$ln->gfv($row, 'description')}
                        </div>
                        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
                    </div>
                </div>
                <div class='c33r'>
                    <div class='subcr {$class}'>
                        {$pic}
                        {$calloutRight}
                    </div>
                </div>
            </div>
            ";
        } else {
            $text = "
            {$title}
            <div class='description'>
                {$ln->gfv($row, 'description')}
            </div>
            {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
            ";
        }

       return $text;
    }

    /**
     *
     */
    function getPrincipalInvestmentRow($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $rows = '';
        $title = ($row['show_title'] == 1) ? "<header><h1>{$ln->gfv($row, 'title', '0')}</h1></header>" : '';

        $exp = array('style' => 'pic');

        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='picWrap'>{$pic}</div>";
        }

        $wRecord = getCPWidgetObj('content_record');
        $calloutRight = $wRecord->getWidget(array(
              'contentType' => 'Callout Right'
             ,'strictToPage' => true
             ,'global' => false
             ,'showPicInDesc' => false
        ));

        if ($calloutRight != ''){
            $calloutRight = "
            <div class='calloutRight'>{$calloutRight}</div>
            ";
        }

        if ($pic != ''){
            $pic = "
            <div class='floatbox'>
                {$pic}
            </div>
            ";
        }

        $text = "
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='subcl'>
                    {$title}
                    <div class='description'>
                        {$ln->gfv($row, 'description')}
                    </div>
                    {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr'>
                    {$pic}
                    {$calloutRight}
                </div>
            </div>
        </div>
        ";

       return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContactUsNewHook($viewObj) {
        $wRecord = getCPWidgetObj('content_record');

        $text = "
        <div class='subcolumns enqForm fatList'>
            <div class='c50l'>
                <div class='subcl'>
                    {$this->getEnquiryForm()}
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    {$wRecord->getWidget(array(
                         'strictToPage' => true
                        ,'global' => false
                    ))}
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEnquiryForm() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $formAction = "/index.php?module=webBasic_contactUs&_spAction=add&showHTML=0";

        $sqlCategory = "
        SELECT value
              ,value
        FROM valuelist
        WHERE key_text='category'
        ORDER BY sort_order
        ";

        $countryText = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCountryDropdown']) {
            $countryText = "
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'country_code', $sqlCountry)}
            ";
        }

        $cancelButton = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCancelButton']) {
            $cancelButton = "
            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }
        
        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}' callback='cpt.finance.afterEnquiry'>
            <input type='hidden' name='successMsg1' value='{$ln->gd('m.webBasic.contactUs.form.enquiry.message.success')}' />
            <input type='hidden' name='successHeading' value='{$ln->gd('m.webBasic.contactUs.form.enquiry.heading')}' />
            <fieldset>
                <div class='infoText'>{$ln->gd('cp.form.mandatoryInfo')}</div>
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl') .'*', 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl') .'*', 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl') .'*', 'email')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.company.lbl'), 'company')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.jobTitle.lbl'), 'position')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax')}
                {$countryText}
                {$formObj->getCheckBoxArrRowBySQL($ln->gd('cp.form.fld.category.lbl'), 'category[]', $sqlCategory)}
                {$formObj->getTARow($ln->gd('message'), 'comments')}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left m0'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            {$cancelButton}
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
    function getModuleWebBasicContactUsNewValidateHook() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
        $validate->validateData("last_name"  , $ln->gd("cp.form.fld.lastName.err")  );
        $validate->validateData("email", $ln->gd("cp.form.fld.email.err"), "email");
        $validate->validateData("comments", $ln->gd("cp.form.fld.comments.err"));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getModuleWebBasicContactUsAddHook() {
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

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'country_code', '', '', 'country');
        $fa = $fn->addToFieldsArray($fa, 'comments');

        if (isset($_POST['category'])){
            $fa = $fn->addToFieldsArray($fa, 'category');
        } else {
            $fa['category'] = '';
        }

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, 'enquiry');
        $result      = $db->sql_query($SQL);
        $contact_id  = $db->sql_nextid();
        
        //-----------------------------------------------------------------//
        $currentDate  = date('d-M-Y l h:i:s A');
        $gcRec = $fn->getRecordByCondition('geo_country', "country_code='{$fa['country']}'");

        $message = $ln->gd('m.webBasic.contactUs.form.enquiry.email.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace('[[email]]', $fa['email'], $message);
        $message = str_replace('[[company]]', $fa['company'], $message);
        $message = str_replace('[[position]]', $fa['position'], $message);
        $message = str_replace('[[phone]]', $fa['phone'], $message);
        $message = str_replace('[[fax]]', $fa['fax'], $message);
        $message = str_replace('[[country]]', $gcRec['name'], $message);
        $message = str_replace('[[category]]', $fa['category'], $message);
        $message = str_replace('[[comments]]', $fa['comments'], $message);
        $message = str_replace('[[currentDate]]', $currentDate, $message);

        $subject   = $ln->gd('m.webBasic.contactUs.form.enquiry.email.notifySubject');
        $fromName  = $fa['first_name'];
        $fromEmail = $fa['email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];                

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

}