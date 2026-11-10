<?
class CP_Www_Themes_Quest_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');

        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        }

        $wRecord = getCPWidgetObj('content_record');
        $wRecordLeft = getCPWidgetObj('content_record');
        $wRecordNews = getCPWidgetObj('content_record');
        $wRecordFav = getCPWidgetObj('content_record');
        $wRecord4 = getCPWidgetObj('content_record');
        $wRecord5 = getCPWidgetObj('content_record');

        $wSlideshow = getCPWidgetObj('media_anythingSlider');
       //{$fn->getSWFFile('www/images/Presentation.swf', 395, 260)}

        $text = "
        <div>
            <div class='highlights floatbox'>
                {$wRecord->getWidget(array(
                     'contentType'  => 'Highlights'
                    ,'showDesc'     => false
                    ,'showPicAsBg'  => true
                    ,'showReadMore' => true
                    ,'displayLimit' => 10
                    ,'orderBy'      => 'sort_order'
                ))}
            </div>
            <div class='subcolumns homeBtm'>
                <div class='c60l'>
                    <div class='subcl'>
                        <div class='homeVideo'>
                            {$wRecord5->getWidget(array(
                                 'contentType'  => 'Home Video'
                                ,'mediaExp'     => array('folder' => 'large')
                                ,'showDesc'     => false
                            ))}
                        </div>
                        <!--<img src='/www/images/corp_video.jpg'>-->
                        <div class='box testimonial mt10'>
                            {$wRecord4->getWidget(array(
                                 'contentType'       => 'Record'
                                ,'showPic'           => false
                                ,'heading'           => $ln->gd('w.content.record.testimonial.heading')
                                ,'showGroupReadMore' => true
                                ,'categoryType'      => 'Testimonials'
                                ,'blockQuote'        => false
                                ,'orderBy'           => 'sort_order'
                                ,'displayLimit'      => 1
                                ,'groupReadMoreUrl'  => $cpUrl->getUrlByCatType('Testimonials')
                            ))}
                        </div>                        
                    </div>
                </div>
                <div class='c40r'>
                    <div class='subcr latestNews box mt0'>123
                        {$wRecordNews->getWidget(array(
                             'contentType'    => 'Record'
                            ,'categoryType'   => 'News & Event'
                            ,'showDate'       => false
                            ,'showShortDesc'  => false
                            ,'showDesc'       => false
                            ,'showPic'        => false
                            ,'heading'        => $ln->gd('w.content.record.whatsnew.heading')
                            ,'showReadMore'   => true
                            ,'specialFilter'   => 'Latest'
                            ,'displayLimit'   => 5
                            ,'orderBy'        => 'content_id DESC'
                        ))}
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLogoutLinkHook() {
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
                
        $logoutUrl = '/index.php?plugin=member_login&_spAction=logout';
        
        $text = "
        {$ln->gd('p.member.login.lbl.welcome')} {$_SESSION['cpUserFullNameWWW']} | 
        <a class='btnLogout' href='{$logoutUrl}'>
            <span>{$ln->gd('logout')}</span>
        </a> |
        <a href='{$cpUrl->getUrlBySecType('My Profile')}'>{$ln->gd('cp.editProfile')}</a>
        ";
        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContentControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
       
        $text = '';
        if ($tv['catType'] == 'Send to Friend Form') {
            $wEmailToFriend = getCPWidgetObj('social_emailToFriend');
            $text = $wEmailToFriend->getWidget();

        } else if ($tv['secType'] == 'Site Search') {
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
        } else if ($tv['currentViewRecType'] == 'Downloads') {
            $text = $this->getDownloadsLink();

        } else if ($tv['secType'] == 'Course') {           
            $subCatId = $fn->getReqParam('sub_category_id');
            $subCat = $tv['subCat'];
            if ($subCat != '' ){
                $modCat = getCPModuleObj('webBasic_subCategory');
                $SQLSubCat = $modCat->model->getSubCategorySQL($tv['subRoom']);
                $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $subCat);

                $urlArray = array();        
                $urlArray['section_title'] = $tv['secTitle'];        
                $urlArray['category_id']    = $tv['subRoom'];
                $urlArray['category_title'] = $tv['catTitle'];
                $urlArray['sub_category_id']    = $tv['sub_category_id'];
                $urlArray['sub_category_title'] = $tv['sub_category_title'];
        
                $url = $cpUrl->make_seo_url($urlArray);

                $formAction = CP_REQUEST_URI;
        
                $text = "
                <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
                <div class='quickSearch floatbox'>
                    <div class='float_left'>
                        <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
                    </div>
                    <div class='float_right'>
                        <select name='_subCat'>
                            <option value='{$tv['subCatTitle']}'>{$ln->gd('cp.lbl.selectCourse')}</option>
                            {$subCatOptions}
                        </select>
                    </div>
                </div>
                </form>
                {$contObj->getList('listAsTabs')}
                ";
            } else if ($tv['subRoom'] != '' ){
                $subCatCount = $fn->getRecordCount('sub_category', "category_id = {$tv['subRoom']}");
                if ($subCatCount > 0){
                    $subCat = getCPWidgetObj('core_subCat');
                    $text = "
                    <div class='subcolumns'>
                        <div class='c60l'>
                            <div class='subcl'>
                                {$contObj->getList()}
                            </div>
                        </div>
                        <div class='c40r'>
                            <div class='subcr'>
                                {$subCat->getWidget(array(
                                    'heading' => $ln->gd('cp.lbl.listOfCourses')
                                ))}
                            </div>
                        </div>
                    </div>
                    ";
                } else {
                    $text = $contObj->getList('listAsTabs');
                }
            } else {
                $text = $contObj->getList();
            }

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
        }

        return $text;
    }
    

    /**
     *
     */
    function getWidgetMemberRegisterFormRowsHTMLHook($viewObj) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $viewHelper = Zend_Registry::get('viewHelper');
        $c = &$viewObj->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;
        $infoText = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        $formAction = $c->formAction;
        $expPass['password'] = 1;
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $memberType = $fn->getReqParam('memberType', 'pms_contact');
        if ($memberType == 'pms_company'){
            $introLabel = 'Registration - Sponsored by Company';
            $flds = "
            <h6>Company Details</h6>
            {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'title')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.companyRegNo.lbl'), 'reg_number')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.postalCode.lbl'), 'address_po_code')}
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'address_country_code', $sqlCountry, $fn->getIssetParam('','address_country_code', 'SG'))}
            {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax')}
            {$formObj->getTBRow($ln->gd('cp.form.fld.natureOfBusiness.lbl'), 'nature_of_business')}
            <h6>Authorized User</h6>
            ";
        } else {
            $introLabel = 'Registration - Individual';
            $flds = "
            ";
        }

        $fieldset1 = "
        {$flds}
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expPass)}
        ";
        
        $retUrl = '';
        if (@$_SESSION['cpReturnUrlAfterLogin'] != ''){
            $retUrl = $_SESSION['cpReturnUrlAfterLogin'];
            unset($_SESSION['cpReturnUrlAfterLogin']);
        } else {
            $retUrl = $cpUrl->getUrlByCatType('Shipping Details', 'Basket');
        }
        
        $text = "
        <form name='registerForm' id='registerForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                <h1>{$ln->gd('m.membership.contact.form.new.heading')}</h1>
                <div class='infoText'>{$ln->gd('m.membership.contact.form.new.info')}</div>
            </fieldset>
            {$formObj->getFieldSetWrapped($introLabel, $fieldset1)}
    	    {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                    </div>
                </div>
            </div>
            <input type='submit' name='x_submit' class='submithidden' />
            <input type='hidden' name='returnUrl' value='{$retUrl}' />
            {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getTotalTraineeToAdd() {
        $fn = Zend_Registry::get('fn');
        
        $text= '';
        $qty = $fn->getReqParam('qty');

        for ($i = 1; $i <= $qty ; $i++){
            $text .= $this->getTraineeRow('','',$i);
        }
        
        return $text;
    }

    /**
     *
     */
    function getTraineeRow($row = array(), $mode = 'edit', $count = '') {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $viewHelper = Zend_Registry::get('viewHelper');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$viewObj->controller;

        $fieldset = '';
        $companyId = $_SESSION['cpContactId'];

        $expCourseArr = array('condn' => 'published = 1');

        $sqlCourse        = $fn->getDDSql('pms_course', $expCourseArr);
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');
        $sqlGender        = $fn->getValueListSQL('gender');
        $languageArr      = $dbUtil->getArrayFromSQLForVL($fn->getValueListSQL('language'));
        
        $sqlQual          = $fn->getValueListSQL('educationalQualification');
        $sqlCountry       = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlSalaryRange   = $fn->getValueListSQL('salaryRange');

        $expVL = array('sqlType' => 'OneField');
        $formObj->mode = $mode;

        $expDDArr = array('hideFirstOption' => 0, 'dummyFirstRowText' => 'Please select already registered trainee');

        $rnd = $cpUtil->getRandomNumber();
        $pfx = $rnd . '__';

        $courseId = $fn->getIssetParam($row,'course_id');
        $expCourse = array();
        $attrForCourseDD = '';
        if ($courseId > 0){
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseId);
            $expCourse = array('detailValue' => $courseRec['title']);
            $attrForCourseDD = " course_id='{$courseId}'";
            
            if ($courseRec['course_code'] == 'FHC'){
                $languageArr['Malay'] = 'Malay';
            }
        }

        /*{$formObj->getDDRowBySQL($ln->gd('cp.form.fld.maritalStatus.lbl'), "{$pfx}marital_status", $sqlMaritalStatus, $fn->getIssetParam($row,'marital_status'), $expVL)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), "{$pfx}phone", $fn->getIssetParam($row,'phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.mobile.lbl'), "{$pfx}mobile", $fn->getIssetParam($row,'mobile'))}*/
        $scheduleUrl = $cpUrl->getUrlByCatType('Accordian Content', 'Content');
        
        $fieldset1 = "
        <div class='courseWrapper'{$attrForCourseDD}>
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.course.lbl'), "{$pfx}course_id", $sqlCourse, $fn->getIssetParam($row,'course_id'), $expCourse)}
        </div>
        <div class='wsqOnly language'>
            {$formObj->getDDRowByArr($ln->gd('cp.form.fld.language.lbl'), "{$pfx}course_language", $languageArr, $fn->getIssetParam($row,'course_language'))}
        </div>
        {$formObj->getDateRow($ln->gd('cp.form.fld.trainingDate.lbl'), "{$pfx}course_training_date", $fn->getIssetParam($row,'course_training_date'))}
        <a href='{$scheduleUrl}' target='_blank'>{$ln->gd('cp.form.fld.clickForSchedule.lbl')}</a>
        {$formObj->getYesNoRRow($ln->gd('cp.form.fld.applyingForSDF.lbl'), "{$pfx}applying_for_sdf", $fn->getIssetParam($row,'applying_for_sdf'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.referenceNo.lbl'), "{$pfx}reference_no", $fn->getIssetParam($row,'reference_no'))}
        <input type='hidden' name='trainees[]' value='{$rnd}'>
        <a href='#' class='removeTrainee removeItem'>Remove</a>
        ";
        
        $contactCount = $fn->getRecordCount('company', "company_id = {$companyId}");
        $contactDD = '';
        
        if ($contactCount > 0){
            $sqlContact = "
            SELECT contact_id 
                  ,CONCAT_WS(' ', first_name, last_name ) AS contact_name 
            FROM contact 
            WHERE company_id = {$companyId} 
            ORDER BY contact_name";
            
            $contactDD = "
            <div class='existingContact'>
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.existingContact.lbl'), "{$pfx}existing_contact_id", $sqlContact, $fn->getIssetParam($row,'existing_contact_id'), $expDDArr)}
            </div>
            ";
        }
        //{$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), "{$pfx}email", $fn->getIssetParam($row,'email'))}
        $fieldset2 = "
        <div class='wrapper' id='{$pfx}'>
            {$contactDD}
            {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), "{$pfx}first_name", $fn->getIssetParam($row,'first_name'))}
            {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), "{$pfx}last_name", $fn->getIssetParam($row,'last_name'))}
            <div class='wsqOnly'>
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.gender.lbl'), "{$pfx}gender", $sqlGender, $fn->getIssetParam($row,'gender'), $expVL)}
            </div>
            {$formObj->getTBRow($ln->gd('cp.form.fld.idCardNo.lbl'), "{$pfx}id_card_no", $fn->getIssetParam($row,'id_card_no'))}
            <div class='wsqOnly'>
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.nationality.lbl'), "{$pfx}nationality", $sqlNationality, $fn->getIssetParam($row,'nationality'), $expVL)}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.race.lbl'), "{$pfx}race", $sqlRace, $fn->getIssetParam($row,'race'), $expVL)}
                {$formObj->getDateRow($ln->gd('cp.form.fld.dateOfBirth.lbl'), "{$pfx}date_of_birth", $fn->getIssetParam($row,'date_of_birth'), array('yearStart' => 1920, 'yearEnd' => date('Y') - 10))}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.schoolHighestQualification.lbl'), "{$pfx}school_highest_qual", $sqlQual, $fn->getIssetParam($row,'school_highest_qual'), $expVL)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.designation.lbl'), "{$pfx}position", $fn->getIssetParam($row,'position'))}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.salaryRange.lbl'), "{$pfx}salary_range", $sqlSalaryRange, $fn->getIssetParam($row,'salary_range'), $expVL)}
            </div>
        </div>
        ";

        $text = "
        <div class='traineeFldsetWrapper'>
            {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.registerForTrainingCourse') . ' - Trainee ' . $count, $fieldset1)}
            {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.personalParticulars'), $fieldset2)}
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getWidgetMemberLoginFormRegisterInfoHook($viewObj) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        
        if ($tv['secType'] == 'Home') {
            return;
        }

        $c = &$viewObj->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $url = ($c->registerUrl != '') ? $c->registerUrl : $cpUrl->getUrlBySecType('Register');
        $url2 = $url . '?memberType=pms_company';

        $text = "
        <div class='registerInfo'>
            <form class='yform'>
            <fieldset>
                <h1>{$ln->gd($c->registerCaption)}</h1>
                <div class='infoText'>{$ln->gd($c->registerInfoText)}</div>
                <div class='type-button'>
                    <a class='button btnRegister individualbtn' href='{$url}'>{$ln->gd('w.member.loginForm.btn.individual')}</a>
                    <a class='button btnRegister ml10' href='{$url2}'>{$ln->gd('w.member.loginForm.btn.company')}</a>
                </div>
            </fieldset>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getWidgetMemberLoginFormRowsHTMLHook($viewObj) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$viewObj->controller;

        $formAction = $c->formAction;
        $expPass['password'] = 1;

        $infoText = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($c->returnUrlAfterLogin != ''){
            $_SESSION['cpReturnUrlAfterLogin'] = $c->returnUrlAfterLogin;
        }

        $forgotText = '';
        if ($c->hasForgotPass){
            $url = '/index.php?plugin=member_forgotPassword&_spAction=view&showHTML=0';
            $forgotText = "
            <div class='forgotPasswordLink float_left'>
                <a href='javascript:void(0)' link='{$url}' class='jqui-dialog-form' formId='forgotPasswordForm'
                    w='400' h='300' title='{$ln->gd('p.member.forgotPassword.form.heading')}'>
                    {$ln->gd('w.member.loginForm.form.lbl.forgotPassword')}
                </a>
            </div>
            ";
        }

        $registerText = '';
        if ($tv['secType'] == 'Home') {
            $link = $cpUrl->getUrlByCatType('Order Form');
            $registerText = "
            <div>
            <a href='{$link}'>
                {$ln->gd('register')}
            </a>
            </div>
            "; 
        }

        $retUrlText = '';
        if ($c->returnUrl){
            $retUrlText = "<input type='hidden' name='returnUrl' value='{$c->returnUrl}' />";
        }

        $regiserInfo = '';
        if ($c->hasRegiserInfo){
            $regiserInfo = $this->getWidgetMemberLoginFormRegisterInfoHook($viewObj);
        }

        $loginType = '';
        if (is_array($c->loginTypeArr)){
            $exp = array('useKey' => true, 'hideFirstOption' => true);
            $loginType = $formObj->getDDRowByArr($ln->gd('cp.form.fld.loginType'), 'loginType', $c->loginTypeArr, $c->loginType, $exp);
        } else {
            $loginType = "<input type='hidden' name='loginType' value='{$c->loginType}' />";
        }

        $text = "
        <form name='loginForm' id='loginForm' class='yform columnar cpJqForm' method='post' action='{$c->formAction}'>
            <fieldset>
                <h1>{$ln->gd('w.member.loginForm.heading')}</h1>
                {$infoText}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
                {$loginType}
                <div class='type-check'>
                    <input type='checkbox' id='fld_save_login' class='checkBox' name='saveLogin' value='1' />
                    <label for='fld_save_login'>{$ln->gd('w.member.loginForm.lbl.saveLogin')}</label>
                </div>
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                {$forgotText}
                {$registerText}
                {$retUrlText}
            </fieldset>
        </form>
        {$regiserInfo}
        ";

        return $text;
    }


    /**
     *
     */
    function getCourseTypeById($row = array(), $mode = 'edit') {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $courseId = $fn->getReqParam('courseId', '', true);
        $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseId);
        
        $json = array();
        if (is_array($courseRec)){
            $json['group']  = $courseRec['group'];
            $json['course_code']  = $courseRec['course_code'];
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getExistingContactInfo($row = array(), $mode = 'edit') {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $contactId = $fn->getReqParam('contactId', '', true);
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $contactId);
        
        $json = array();
        if (is_array($contactRec)){
            $json['first_name']  = $contactRec['first_name'];
            $json['last_name']  = $contactRec['last_name'];
            //$json['email']  = $contactRec['email'];
            $json['gender']  = $contactRec['gender'];
            $json['id_card_no']  = $contactRec['id_card_no'];
            $json['nationality']  = $contactRec['nationality'];
            $json['race']  = $contactRec['race'];
            $json['date_of_birth']  = $contactRec['date_of_birth'];
            $json['school_highest_qual']  = $contactRec['school_highest_qual'];
            $json['position']  = $contactRec['position'];
            $json['salary_range']  = $contactRec['salary_range'];
        }

        return json_encode($json);
    }
    /**
     *
     */
    function getTrainingHistory() {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        $contactId = $_SESSION['cpContactId'];
        $rows = "";
        
        if($memberType == 'pms_company'){
            $SQL = "
            SELECT cc.*
                  ,c.title
                  ,CONCAT_WS('', ct.first_name, ct.last_name) AS contact_name
            FROM course_contact cc
            LEFT JOIN (course c) ON (c.course_id = cc.course_id)
            LEFT JOIN (contact ct) ON (ct.contact_id = cc.contact_id)
            WHERE cc.company_id = {$contactId}
            ";
        } else {
            $SQL = "
            SELECT cc.*
                  ,c.title
            FROM course_contact cc
            LEFT JOIN (course c) ON (c.course_id = cc.course_id)
            WHERE cc.contact_id = {$contactId}
            ";
        }
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $contactName = ($memberType == 'pms_company') ? "<td>{$row['contact_name']}</td>" : '';
            
            $rows .= "
            <tr>
                {$contactName}
                <td>{$row['title']}</td>
                <td>{$row['training_date']}</td>
            </tr>
            ";
        }
                
        if ($numRows > 0){
            $contactName = ($memberType == 'pms_company') ? "<th>Contact Name</th>" : '';
            
            $text = "
            <h4 class='mb10'>{$ln->gd('youAreRegisteredForBelowCourses')}</h4>
            <table class='thinlist'>
                <tr>
                    {$contactName}
                    <th>Course Name</th>
                    <th>Date</th>
                </tr>
                {$rows}
            </table>
            ";
        } else {
            $text = "{$ln->gd('noCourseHistory')}";
        }
        
        return $text;
    }

    /**
     *
     */
    function getDownloadsLink() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');

        $download = '';

        $SQL = "
        SELECT *
        FROM content
        WHERE show_brochure = 1
          AND published    = 1
        ";
        $result  = $db->sql_query($SQL);
        $download = '';
        while ($row = $db->sql_fetchrow($result)) { 
            
            if ($row['sub_category_id'] != '') {
                $SQLCategory ="
                SELECT sc.*
                FROM sub_category sc
                WHERE sc.sub_category_id = {$row['sub_category_id']}
                ";
            } else {   
                $SQLCategory ="
                SELECT c.*
                FROM category c
                WHERE c.category_id = {$row['category_id']}
                ";
            }

            $resultCategory = $db->sql_query($SQLCategory);  
            $rowCategory = $db->sql_fetchrow($resultCategory);

            $SQLMedia ="
            SELECT m.*
            FROM media m
            WHERE m.record_id = {$row['content_id']}
            ";
            $resultMedia = $db->sql_query($SQLMedia);  
            $numRows = $db->sql_numrows($resultMedia);
            while ($rowMedia = $db->sql_fetchrow($resultMedia)) {
                if($numRows > 0){
                    $attArr = $media->model
                              ->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id']);
                    if (count($attArr) > 0){
                        $saveUrl = "/index.php?plugin=common_media&_spAction=saveMedia" . 
                                   "&media_id={$attArr['media_id']}&showHTML=0";
                    }
                    
                    $download .= "
                    <div class='mt10'>
                        <a href='{$saveUrl}'>{$rowCategory['title']} - {$row['title']}</a>                    
                    </div>
                    ";
                }
            }
        }

        $text = "
        <div class=''>
            <strong>{$ln->gd('clickToDownloadBrochure')}</strong>
            {$download}
        </div>    
        ";
        return $text;
    }
}