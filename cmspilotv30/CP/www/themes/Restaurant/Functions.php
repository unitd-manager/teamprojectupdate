<?
class CP_Www_Themes_Restaurant_Functions
{
    /*
     *
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $text = "";
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';

            $wRecord1 = getCPWidgetObj('content_record');
            $calloutLeft = $wRecord1->getWidget(array(
                 'contentType'       => 'Callout Left'
                ,'showReadMore'      => TRUE
                ,'showShortDesc'     => FALSE
                ,'strictToPage'      => true
                ,'global'            => false
                ,'showTitleBelowPic' => true
            ));

            $text = "
            <div class='subcolumns content'>
                <div class='c33l'>
                    <div class='subcl'>
                        <div class='calloutLeft'>
                            {$calloutLeft}
                        </div>
                    </div>
                </div>
                <div class='c66r'>
                    <div class='subcr'>
                        {$title}
                        {$ln->gfv($row, 'description')}
                    </div>
                </div>
            </div>
            ";
        }
        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContentControllerHook($obj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($tv['secType'] == 'Online Sale') {
            $dataArray = $obj->getList('', array('returnDataOnly' => true));
            $text = $this->getOnlineSaleList($dataArray);

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $obj->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getModuleEcommerceProductControllerHook($obj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';

        if ($tv['catType'] == 'Sample Menus' ||
            ($tv['subRoom'] == ''
            && $tv['action'] == 'list'
            && $cpCfg['m.ecommerce.product.list.showIntroContent']
            )){

            $wRecord = getCPWidgetObj('content_record');
            $contentArr = $wRecord->getWidget(array(
                 'returnDataOnly' => true
                ,'global' => false
                ,'strictToPage' => true
            ));

            if (count($contentArr) > 0){
                $text = getCPModuleObj('webBasic_content')->view->getList($contentArr);
            }

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $obj->$fnName();
        }

        return $text;
    }

    /*
     *
     */
    function getModuleEcommerceProductListHook($dataArray) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        if ($tv['catType'] == 'Wine'){
            $list = $this->getWineList($dataArray);
        } else {
            $list = $this->getCheeseList($dataArray);
        }

        $text = "
		<div class='pager'>
          	{$theme->view->getPagerPanel()}
		</div>
        <div class='productList'>
            {$list}
        </div>
        ";

        return $text;
    }

    /*
     *
     */
    function getWineList($dataArray) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUrl = Zend_Registry::get('cpUrl');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'product_id');

            $rows .= "
            <tr>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['sub_category_title']}</td>
                <td>{$row['year']}</td>
                <td>{$row['country']}</td>
            </tr>
            ";
        }

        $subCat = $fn->getReqParam('_subCat');
        $year = $fn->getReqParam('year');
        $country = $fn->getReqParam('country');

        /*********************************************************/
        $SQLSubCat = "
        SELECT sc.sub_category_id
              ,sc.title
        FROM sub_category sc
        JOIN category c ON (c.category_id = sc.category_id)
        WHERE sc.published = 1
          AND c.category_type = 'Wine'
        ORDER BY sc.title
        ";
        $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $subCat);

        /*********************************************************/
        $SQLYear = "
        SELECT DISTINCT year
        FROM product p
        WHERE p.published = 1
          AND year != ''
          AND year IS NOT NULL
        ORDER BY year
        ";
        $yearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $year);

        /*********************************************************/
        $SQLCountry = "
        SELECT DISTINCT country
        FROM product p
        WHERE p.published = 1
          AND country != ''
          AND country IS NOT NULL
        ORDER BY country
        ";
        $countryOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLCountry, $country);

        $text = "
        <form id='search' method='GET' action='{$_SERVER['REQUEST_URI']}'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>{$ln->gd('m.ecommerce.product.lbl.name')}</th>
                        <th class='type'>
                            {$ln->gd('m.ecommerce.product.lbl.type')}
                            <select name='_subCat'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$subCatOptions}
                            </select>
                        </th>
                        <th class='year'>
                            {$ln->gd('m.ecommerce.product.lbl.year')}
                            <select name='year'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$yearOptions}
                            </select>
                        </th>
                        <th class='country'>
                            {$ln->gd('m.ecommerce.product.lbl.country')}
                            <select name='country'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$countryOptions}
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";

        return $text;
    }

    /*
     *
     */
    function getCheeseList($dataArray) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'product_id');

            $rows .= "
            <tr>
                <td><a href='{$url}'>{$row['title']}</a></td>
                <td>{$row['sub_category_title']}</td>
                <td>{$row['milk_source']}</td>
                <td>{$row['milk_type']}</td>
                <td>{$row['origin']}</td>
            </tr>
            ";
        }

        $subCat      = $fn->getReqParam('_subCat');
        $milk_type   = $fn->getReqParam('milk_type');
        $milk_source = $fn->getReqParam('milk_source');
        $origin      = $fn->getReqParam('origin');

        /*********************************************************/
        $SQLSubCat = "
        SELECT sc.sub_category_id
              ,sc.title
        FROM sub_category sc
        JOIN category c ON (c.category_id = sc.category_id)
        WHERE sc.published = 1
          AND c.category_type = 'Cheese'
        ORDER BY sc.title
        ";
        $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $subCat);

        /*********************************************************/
        $SQLMilkType = "
        SELECT DISTINCT milk_type
        FROM product p
        WHERE p.published = 1
          AND milk_type != ''
          AND milk_type IS NOT NULL
        ORDER BY milk_type
        ";
        $milkTypeOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLMilkType, $milk_type);

        /*********************************************************/
        $SQLMilkSource= "
        SELECT DISTINCT milk_source
        FROM product p
        WHERE p.published = 1
          AND milk_source != ''
          AND milk_source IS NOT NULL
        ORDER BY milk_source
        ";
        $milkSourceOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLMilkSource, $milk_source);

        /*********************************************************/
        $SQLOrigin = "
        SELECT DISTINCT origin
        FROM product p
        WHERE p.published = 1
          AND origin != ''
          AND origin IS NOT NULL
        ORDER BY origin
        ";
        $originOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLOrigin, $origin);

        $text = "
        <form id='search' method='GET' action='{$_SERVER['REQUEST_URI']}'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>{$ln->gd('m.ecommerce.product.lbl.name')}</th>
                        <th class='type'>
                            {$ln->gd('m.ecommerce.product.lbl.type')}
                            <select name='_subCat'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$subCatOptions}
                            </select>
                        </th>
                        <th class='type'>
                            {$ln->gd('m.ecommerce.product.lbl.milkSource')}
                            <select name='milk_source'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$milkSourceOptions}
                            </select>
                        </th>
                        <th class='type'>
                            {$ln->gd('m.ecommerce.product.lbl.milkType')}
                            <select name='milk_type'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$milkTypeOptions}
                            </select>
                        </th>
                        <th class='country'>
                            {$ln->gd('m.ecommerce.product.lbl.origin')}
                            <select name='origin'>
                                <option value=''>{$ln->gd('cp.form.lbl.pleaseSelect')}</option>
                                {$originOptions}
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";

        return $text;
    }

    /*
     *
     */
    function getModuleEcommerceProductDetailHook($row) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['catType'] == 'Wine'){
            $info = "
            {$ln->gd('m.ecommerce.product.lbl.year')}: {$row['year']} <br>
            {$ln->gd('m.ecommerce.product.lbl.country')}: {$row['country']}
            ";
        } else {
            $info = "
            {$ln->gd('m.ecommerce.product.lbl.milkSource')}: {$row['milk_source']} <br>
            {$ln->gd('m.ecommerce.product.lbl.milkType')}: {$row['milk_type']} <br>
            {$ln->gd('m.ecommerce.product.lbl.production')}: {$row['production']} <br>
            {$ln->gd('m.ecommerce.product.lbl.origin')}: {$row['origin']}
            ";
        }

        $text = "
        <div>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>
        <div class='productDetail'>
            <h1>{$row['title']}</h1>

            <div class='info'>
                <h2>{$ln->gd('m.ecommerce.product.lbl.information')}:</h2>
                {$ln->gd('m.ecommerce.product.lbl.name')}: {$row['title']} <br>
                {$ln->gd('m.ecommerce.product.lbl.type')}: {$row['sub_category_title']} <br>
                {$info}
            </div>
            <div class='desc'>
                {$row['description']}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getOnlineSaleList($dataArray) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
            
        $intro = '';
        $rows = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
            
            $exp = array('folder' =>'normal');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
            
            $wRecord = getCPWidgetObj('content_record');
            $intro = $wRecord->getWidget(array(
                 'contentType'       => 'Intro'
                ,'recordTitleHeadTag'=> 'h1'
                ,'categoryId'        => $row['category_id']
            ));            

            $rows .= "
            <div class='row subcolumns'>
                {$title}
                <div class='c25l'>
                    <div class='subcl'>
                        {$pic}
                    </div>
                </div>
                <div class='c75r'>
                    <div class='subcr'>
                        {$ln->gfv($row, 'description')}
                        {$row['embed_code']}
                    </div>
                </div>
            </div>
            ";
        }

        //$intro = '';
        $text = "
        {$intro}
        <div class='onlineSale'>
           	{$rows}
        </div>
        ";

        return $text;
    }

    function getModuleWebBasicContactUsControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($tv['secType'] == 'Enquiry Form' || $tv['catType'] == 'Enquiry Form') {
            $text = $contObj->view->getNew();
        } else if ($tv['catType'] == 'Catering Form') {
            $text = $this->getCateringForm();
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $contObj->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getModuleWebBasicContactUsNewHook($viewObj) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "/index.php?module=webBasic_contactUs&_spAction=add&showHTML=0";

        $infoText = '';

        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.webBasic.contactUs.form.enquiry.message.success')}' />
            <fieldset>
                <legend>{$ln->gd('m.webBasic.contactUs.form.enquiry.heading')}</legend>
                <div class='infoText'>{$ln->gd('m.webBasic.contactUs.form.enquiry.info')}</div>
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                {$formObj->getTARow($ln->gd('cp.form.fld.comments.lbl'), 'comments')}
      	    	{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
                <div class='type-button floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
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
    function getCateringForm() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "/index.php?_theme=restaurant&_spAction=cateringAdd&showHTML=0";

        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.webBasic.contactUs.form.catering.message.success')}' />
            <fieldset>
                <legend>{$ln->gd('m.webBasic.contactUs.form.catering.heading')}</legend>
                <div class='infoText'>{$ln->gd('m.webBasic.contactUs.form.catering.info')}</div>
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
                {$formObj->getDDRowByVL($ln->gd('cp.form.fld.clasifiedlocation.lbl'), 'location', 'location', '', array('orderBy' => 'sort_order'))}
                {$formObj->getTBRow($ln->gd('cp.form.fld.noOfGuest.lbl'), 'no_of_guests')}
                {$formObj->getDateRow($ln->gd('cp.form.fld.dateFirstChoice.lbl'), 'date_of_event1')}
                {$formObj->getDateRow($ln->gd('cp.form.fld.dateSecondChoice.lbl'), 'date_of_event2')}
                {$formObj->getTimeRow($ln->gd('cp.form.fld.startTime.lbl'), 'start_time')}
                {$formObj->getTimeRow($ln->gd('cp.form.fld.endTime.lbl'), 'end_time')}
                {$formObj->getDDRowByVL($ln->gd('cp.form.fld.typeOfEvent.lbl'), 'type_of_event', 'eventType', '', array('orderBy' => 'sort_order'))}
                {$formObj->getDDRowByVL($ln->gd('cp.form.fld.venueOption.lbl'), 'venue_option', 'venueOption', '', array('orderBy' => 'sort_order'))}
                {$formObj->getTARow($ln->gd('cp.form.fld.specialRequest.lbl'), 'comments')}
      	    	{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
                <div class='type-button floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
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
    function getCateringAdd() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$this->getCateringValidate()){
            return $validate->getErrorMessageXML();
        }

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['first_name']     = $fn->getPostParam('first_name');
        $fa['last_name']      = $fn->getPostParam('last_name');
        $fa['company']        = $fn->getPostParam('company');
        $fa['email']          = $fn->getPostParam('email');
        $fa['phone']          = $fn->getPostParam('phone');
        $fa['location']       = $fn->getPostParam('location');
        $fa['no_of_guests']   = $fn->getPostParam('no_of_guests');
        $fa['date_of_event1'] = $fn->getPostParam('date_of_event1');
        $fa['date_of_event2'] = $fn->getPostParam('date_of_event2');
        $fa['start_time']     = $fn->getPostParam('start_time');
        $fa['end_time']       = $fn->getPostParam('end_time');
        $fa['type_of_event']  = $fn->getPostParam('type_of_event');
        $fa['venue_option']  = $fn->getPostParam('venue_option');
        $fa['comments']       = $fn->getPostParam('comments');
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['enquiry_type']   = 'Catering Enquiry';

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "enquiry");

        $result      = $db->sql_query($SQL);
        $contact_id  = $db->sql_nextid();

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd("m.webBasic.contactUs.form.catering.email.notifyBody");
        $message = str_replace("[[first_name]]"    , $fa["first_name"]    , $message );
        $message = str_replace("[[last_name]]"     , $fa["last_name"]     , $message );
        $message = str_replace("[[company]]"       , $fa["company"]     , $message );
        $message = str_replace("[[email]]"         , $fa["email"]         , $message );
        $message = str_replace("[[phone]]"         , $fa["phone"]         , $message );
        $message = str_replace("[[location]]"      , $fa["location"]      , $message );
        $message = str_replace("[[no_of_guests]]"  , $fa["no_of_guests"]  , $message );

        $message = str_replace("[[date_of_event1]]", $fa["date_of_event1"], $message );
        $message = str_replace("[[date_of_event2]]", $fa["date_of_event2"], $message );
        $message = str_replace("[[start_time]]"    , $fa["start_time"]    , $message );
        $message = str_replace("[[end_time]]"      , $fa["end_time"]      , $message );
        $message = str_replace("[[phone]]"         , $fa["phone"]         , $message );
        $message = str_replace("[[type_of_event]]" , $fa["type_of_event"] , $message );
        $message = str_replace("[[venue_option]]"  , $fa["venue_option"] , $message );
        $message = str_replace("[[comments]]"      , $fa["comments"]      , $message );
        $message = str_replace("[[currentDate]]"   , $currentDate         , $message );

        $subject     = $ln->gd("m.webBasic.contactUs.form.catering.email.notifySubject");
        $fromName    = $fa['first_name'] . " " . $fa['last_name'];
        $fromEmail   = $fa['email'];
        $toName      = $cpCfg['cp.companyName'];
        $toEmail     = $cpCfg['cp.cateringAdminEmail'];

        if ($fa['type_of_event'] != ''){
            $vlRec = $fn->getRecordByCondition('valuelist', "key_text = 'eventType' AND value = '{$fa['type_of_event']}'");

            if (is_array($vlRec) && $vlRec['code'] != ''){
                $toEmail = $vlRec['code'];
            }
        }

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
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
    function getCateringValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err")  );
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err")   );
        $validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");
        $validate->validateData("comments"    , $ln->gd("cp.form.fld.comments.err")   );

       	$captcha_code = $fn->getPostParam('captcha_code');
        require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        $img = new Securimage;
        if ($img->check($captcha_code) == false) {
            $validate->errorArray['captcha_code']['name'] = "captcha_code";
            $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getModuleWebBasicContactUsAddHook($modelObj) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        if (!$modelObj->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        //-----------------------------------------------------------------------//
        $fa = array();

        $fa['first_name']    = $fn->getPostParam('first_name');
        $fa['last_name']     = $fn->getPostParam('last_name');
        $fa['email']         = $fn->getPostParam('email');
        $fa['phone']         = $fn->getPostParam('phone');
        $fa['comments']      = $fn->getPostParam('comments');
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['enquiry_type']   = 'General Enquiry';

        $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "enquiry");
        $result      = $db->sql_query($SQL);
        $contact_id  = $db->sql_nextid();

        //------------------------ ADMIN EMAIL -----------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $message = $ln->gd("m.webBasic.contactUs.form.enquiry.email.notifyBody");
        $message = str_replace("[[first_name]]"   , $fa["first_name"]   , $message );
        $message = str_replace("[[last_name]]"    , $fa["last_name"]    , $message );
        $message = str_replace("[[email]]"        , $fa["email"]        , $message );
        $message = str_replace("[[phone]]"        , $fa["phone"]        , $message );
        $message = str_replace("[[comments]]"     , $fa["comments"]     , $message );
        $message = str_replace("[[currentDate]]"  , $currentDate        , $message );

        $subject     = $ln->gd("m.webBasic.contactUs.form.enquiry.email.notifySubject");
        $fromName    = $fa['first_name'] . " " . $fa['last_name'];
        $fromEmail   = $fa['email'];
        $toName      = $cpCfg['cp.companyName'];
        $toEmail     = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();

        //subscribe to mailchimp newsletter
//        $mailChimp = getCPPluginObj('common_mailChimp');
//        $mailChimp = $mailChimp->model;
//        $retVal = $mailChimp->listSubscribe($fromEmail, $fa['first_name'], $fa['last_name']);

        //------------------- USER EMAIL ----------------------------------------------//
        $message = $ln->gd('m.webBasic.contactUs.form.enquiry.userEmail.notifyBody');
        $message = str_replace('[[first_name]]', $fa['first_name'], $message);
        $message = str_replace('[[last_name]]', $fa['last_name'], $message);
        $message = str_replace("[[email]]"        , $fa["email"]        , $message );
        $message = str_replace("[[phone]]"        , $fa["phone"]        , $message );
        $message = str_replace("[[comments]]"     , $fa["comments"]     , $message );
        $message = str_replace("[[currentDate]]"  , $currentDate        , $message );

        $subject   = $ln->gd('m.webBasic.contactUs.form.enquiry.email.notifySubject');
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $fa['first_name'] . ' ' . $fa['last_name'];
        $toEmail   = $fa['email'];

        $args1 = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args1));
        $emailMsg->sendEmail();

        return $validate->getSuccessMessageXML();
    }

    function getModuleWebBasicNewsQuickSearchHook1() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');

        $yearMonth = $fn->getReqParam('yearMonth');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(content_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(content_date, '%b %Y') AS monthYear
        FROM content
        WHERE DATE_FORMAT(content_date, '%b %Y') IS NOT NULL
          AND published = 1
          AND section_id = {$tv['room']}
        ORDER BY yearMonthStart DESC
        ";
        $yearMonthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonth);

        $formAction = $cpUrl->getUrlByCatType('Content', 'News');

        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
        <div class='quickSearch'>
            <div class='yearMonth'>
                <select name='yearMonth'>
                    <option value=''>{$ln->gd('m.webBasic.news.lbl.searchByMonth')}</option>
                    {$yearMonthOptions}
                </select>
            </div>
        </div>
        </form>
        ";

        return $text;
    }

}