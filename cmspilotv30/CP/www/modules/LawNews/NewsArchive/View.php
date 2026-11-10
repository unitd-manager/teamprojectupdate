<?

class CP_Www_Modules_LawNews_NewsArchive_View extends CP_Common_Lib_ModuleViewAbstract {

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        foreach ($dataArray as $row) {

        }

        $archive_year  = (int)$fn->getReqParam('archive_year', date('Y'));
        $archive_month = (int)$fn->getReqParam('archive_month');
        $category_type = $fn->getReqParam('category_type');

        $yearMonthSearchvar = " AND YEAR(c.content_date) = {$archive_year}";
        $yearMonthSearchvar .= ( $archive_month != '') ? " AND MONTH(c.content_date) = {$archive_month}" : '';

        $wNewsAndAnalysis = '';
        if ($category_type == '' || $category_type == 'News & Analysis') {
            $wRecord = getCPWidgetObj('content_record');
            $wNewsAndAnalysis = $wRecord->getWidget(array(
                        'helperFn' => 'getWidgetByCategoryType'
                        , 'sectionType' => 'News Archive'
                        , 'categoryType' => 'News & Analysis'
                        , 'showDesc' => FALSE
                        , 'showPicInDesc' => FALSE
                        , 'showShortDesc' => TRUE
                        , 'addSearchCond' => $yearMonthSearchvar
                        , 'displayLimit' => 1000
                    ));
        }

        $wNewsInBrief = '';
        if ($category_type == '' || $category_type == 'News In Brief') {
            $wRecord = getCPWidgetObj('content_record');
            $wNewsInBrief = $wRecord->getWidget(array(
                        'helperFn' => 'getWidgetByCategoryType'
                        , 'sectionType' => 'News Archive'
                        , 'categoryType' => 'News In Brief'
                        , 'showDesc' => FALSE
                        , 'showShortDesc' => FALSE
                        , 'showPic' => FALSE
                        , 'addSearchCond' => $yearMonthSearchvar
                        , 'displayLimit' => 1000
                    ));
        }

        $text = "
        <div class='newsArchiveList'>
            {$this->getQuickSearch()}
            <div class='floatbox newsAnalysis'>
                {$wNewsAndAnalysis}
            </div>
            <div class='floatbox newsInBrief'>
                {$wNewsInBrief}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $title = ($row['show_title'] == 1) ? "<header><h1 class='ruled'>{$ln->gfv($row, 'title', '0')}</h1></header>" : '';
        $exp = array('style' => 'mb5');
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != '') {
            $pic = "<div class='float_right'>{$pic}</div>";
        }
        $pic = ''; //as per phase 2, the pic will be embedded in the description. and the attached pic will be shown only in list view.

        //Login temproarily disabled
        //if(isLoggedInWWW()){
            $this->model->updateReadCount($row['content_id']);

            $text = "
            <article>
                {$title}
                {$this->getLoggedInLinks($row['content_id'])}
                {$pic}
                {$ln->gfv($row, 'description', '0')}
                {$this->getReporters($row)}
            </article>
            ";
        //} else {
        //    $text = "
        //    {$title}
        //    {$this->getSubscribeNowPanel()}
        //    ";
        //}

        return $text;
    }

    /**
     *
     */
    function getSubscribeNowPanel(){
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $wRecord = getCPWidgetObj('content_record');
        $whyRegisterText = $wRecord->getWidget(array(
             'contentType' => 'Why Register'
            ,'heading' => $ln->gd('m.lawNews.newsArchive.detail.whyRegister.heading')
        ));

        $text = "
        <div class='subscribeNowPanel'>
            <div class='warning'>
                <p>{$ln->gd('m.lawNews.newsArchive.detail.subscribeWarning.info')}</p>
            </div>
            <div class='bordered_light'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <h2 class='section_rounded'>{$ln->gd('m.lawNews.newsArchive.detail.subscribeNow.heading')}</h2>
                        <p>{$ln->gd('m.lawNews.newsArchive.detail.subscribeNow.info')}</p>
                        <p>
                            <a href='{$cpUrl->getUrlBySecType('Subscribe')}' class='btn'>{$ln->gd('m.lawNews.newsArchive.btn.subscribe')}</a>
                        </p>
                    </div>
                    <div class='float_left rightBox'>
                        <h2 class='section_rounded'>{$ln->gd('m.lawNews.newsArchive.detail.loginNow.heading')}</h2>
                        <p>{$ln->gd('m.lawNews.newsArchive.detail.loginNow.info')}</p>
                        <p>
                            <a href='{$cpUrl->getUrlBySecType('Login')}' class='btn'>{$ln->gd('m.lawNews.newsArchive.btn.login')}</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class='whyRegister'>
                {$whyRegisterText}
                <p>
                    <a href='{$cpUrl->getUrlBySecType('Subscribe')}' class='btn'>{$ln->gd('m.lawNews.newsArchive.btn.subscribe')}</a>
                </p>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $archive_year  = (int)$fn->getReqParam('archive_year', date('Y'));
        $archive_month = (int)$fn->getReqParam('archive_month');
        $category_type = $fn->getReqParam('category_type');

        $expYear = array(
            'section_type' => "News Archive"
            , 'addSearchCond' => " AND (ca.category_type = 'News & Analysis' OR ca.category_type = 'News In Brief')"
        );
        $SQLYear = $fn->getContentYearSQL($expYear);
        $yearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $archive_year);

        $SQLMonth = getCPModuleObj('core_valuelist')->model->getValuelistSQL('months', array('useCode' => TRUE, 'orderBy' => 'code'));
        $monthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $archive_month);

        $SQLFilter = "
        SELECT c.category_type
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        WHERE s.section_type = 'News Archive'
          AND (c.category_type = 'News & Analysis' OR c.category_type = 'News In Brief')
          AND c.published = 1
        ORDER BY c.title
        ";
        $filterOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLFilter, $category_type);

        $formAction = CP_REQUEST_URI;

        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
            <div class='quickSearch'>
                <h2 class='ruled'>{$ln->gd('m.lawNews.newsArchive.form.quickSearch.heading')}</h2>
                <div class='floatbox'>
                    <div class='float_left'>
                        <select name='archive_year'>
                            <option value=''>{$ln->gd('cp.form.lbl.selectYear')}</option>
                            {$yearOptions}
                        </select>
                    </div>
                    <div class='float_left'>
                        <select name='archive_month'>
                            <option value=''>{$ln->gd('cp.form.lbl.selectMonth')}</option>
                            {$monthOptions}
                        </select>
                    </div>
                    <div class='float_left'>
                        <select name='category_type'>
                            <option value=''>{$ln->gd('cp.form.lbl.selectFilter')}</option>
                            {$filterOptions}
                        </select>
                    </div>
                </div>
            </div>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAdvancedSearchList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $keyword = $fn->getReqParam('keyword');

        $rows = '';
        foreach ($dataArray as $row) {
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $target = ($row['external_link'] != '') ? "target='_blank'" : '';
            $title = "
            <div class='title'>
                <a href='{$url}' {$target}>{$ln->gfv($row, 'title')}</a>
            </div>";

//            $content_date = $fn->getCPDate($row['content_date']);
//            $date = "
//            <span class='date'>
//                {$content_date}
//            </span>
//            ";
            $date = '';

            $shortDesc = "
            <span class='shortDesc'>
                {$ln->gfv($row, 'description_short')}
            </span>
            ";

            $rows .= "
            <li>
                {$title}
                {$date}{$shortDesc}
            </li>
            ";
        }

        $rowsHTML = '';
        if ($rows != '' && $keyword != '') {
            $theme = getCPThemeObj($cpCfg['cp.theme']);
            $rowsHTML = "
            <h2 class='ruled'>{$ln->gd('m.lawNews.newsArchive.advancedSearchResult.heading')}</h2>
            {$theme->view->getPagerPanelTop()}
            <ul class='noDefault'>
                {$rows}
            </ul>
            {$theme->view->getPagerPanel()}
            ";
        }

        $text = "
        <div class='advancedSearchList'>
            {$rowsHTML}
        </div>
        {$this->getAdvanceSearch()}
        ";

        return $text;
    }

    /**
     *
     */
    function getAdvanceSearch() {

        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');


        $keyword  = htmlentities($fn->getReqParam('keyword'), ENT_QUOTES, "UTF-8");
        $order_by = $fn->getReqParam('order_by', 'newest');
        $period   = $fn->getReqParam('period', 'all');
        $range_from_month = $fn->getReqParam('range_from_month');
        $range_from_year  = $fn->getReqParam('range_from_year');
        $range_to_month   = $fn->getReqParam('range_to_month', date('m'));
        $range_to_year    = $fn->getReqParam('range_to_year', date('Y'));
//        $orderByArr = array(
//            'relevance' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.relevance.lbl')
//            , 'newest' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.newest.lbl')
//            , 'oldest' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.oldest.lbl')
//        );
        $orderByArr = array(
              'newest' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.newest.lbl')
            , 'oldest' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.oldest.lbl')
        );

        $periodArr = array(
            'all' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.all.lbl')
            , '3Months' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.3Months.lbl')
            , '6Months' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.6Months.lbl')
            , '1Year' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.1Year.lbl')
            , 'dateRange' => $ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.dateRange.lbl')
        );

        $SQLMonth = getCPModuleObj('core_valuelist')->model->getValuelistSQL('months', array('useCode' => TRUE, 'orderBy' => 'code'));
        $rangeFromMonthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $range_from_month);
        $rangeToMonthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $range_to_month);

        $expYear = array(
            'section_type' => "News Archive"
            , 'addSearchCond' => " AND (ca.category_type != 'External Links')"
        );
        $SQLYear = $fn->getContentYearSQL($expYear);
        $rangeFromYearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $range_from_year);
        $rangeToYearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $range_to_year);

        $formAction = CP_REQUEST_URI;

        $text = "
        <form action='{$formAction}' method='get' id='advancedSearch' autoSubmitOnChange='0'>
            <div class='advancedSearch'>
                <h2 class='ruled'>{$ln->gd('m.lawNews.newsArchive.form.advancedSearch.heading')}</h2>
                <div class='floatbox'>
                    <div class='float_left'>
                        <div class='type-text editable'>
                            <label for='fld_keyword'>{$ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.keyword.lbl')}</label>
                            <input type='text' value='{$keyword}' id='fld_keyword' class='text' name='keyword' rel='pptxt: {$ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.keyword.pptxt')}'>
                        </div>
                    </div>
                    <div class='float_left submit'>
                        <a href=\"javascript:$('#advancedSearch').submit();\" class='submit'>
                            {$ln->gd('p.common.siteSearch.btn.search')}
                        </a>
                    </div>
                </div>
                <h2 class='ruled mt20'>{$ln->gd('m.lawNews.newsArchive.form.advancedSearch.searchOptions')}</h2>
                <div class='searchOptions'>
                    <div class='floatbox'>
                        <div class='float_left c50l'>
                            {$formObj->getRadioArrRow($ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.orderBy.lbl'), 'order_by', $order_by, $orderByArr, array('useKey' => 1))}
                        </div>

                        <div class='float_left c50l'>
                            {$formObj->getRadioArrRow($ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.period.lbl'), 'period', $period, $periodArr, array('useKey' => 1))}
                            <div class='floatbox dateRange'>
                                <div class='float_left'>
                                    <select name='range_from_month'>
                                        {$rangeFromMonthOptions}
                                    </select>
                                </div>
                                <div class='float_left'>
                                    <select name='range_from_year'>
                                        {$rangeFromYearOptions}
                                    </select>
                                </div>
                            </div>
                            <div class='floatbox dateRange'>
                                <div class='float_left'>
                                    <select name='range_to_month'>
                                        {$rangeToMonthOptions}
                                    </select>
                                </div>
                                <div class='float_left'>
                                    <select name='range_to_year'>
                                        {$rangeToYearOptions}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class='float_left c50l'>
                            {$this->getJurisdictionSearchRow()}
                        </div>

                    </div>
                    <div class='floatbox'>
                        <div class='float_left submit'>
                            <a href=\"javascript:$('#advancedSearch').submit();\" class='submit'>
                                {$ln->gd('p.common.siteSearch.btn.search')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        ";

        return $text;
    }

    /**
     * Jurisdiction Search row for advanced search
     */
    function getJurisdictionSearchRow() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $jurisdiction_idArr = $fn->getReqParam('jurisdiction_id', array());

        $dataArray2 = getCPModuleObj('lawNews_Jurisdiction')->model->getDataArrayGroupedByRegion();

        $rows = '';
        foreach ($dataArray2 as $region) {
            $innerRows = '';
            foreach ($region['rows'] AS $row) {
                $selected = in_array($row['jurisdiction_id'], $jurisdiction_idArr) ? "selected='selected'" : '';
                $innerRows .= "
                <option value='{$row['jurisdiction_id']}' {$selected}>{$row['title']}</a>
                ";
            }

            $rows .= "
            <optgroup label='{$region['name']}'>
            </optgroup>
            {$innerRows}
            ";
        }

        $text = "
        <label for='jurisdiction_id[]'>{$ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.jurisdiction.lbl')}</label>
        <p>{$ln->gd('m.lawNews.newsArchive.form.advancedSearch.fld.jurisdiction.info')}</p>
        <select name='jurisdiction_id[]' multiple='multiple' size='6'>
            {$rows}
        </select>
        <p class='mt10'>
            <a href='javascript:void(0)' class='btn' id='clearJurisdiction'>
                {$ln->gd('m.lawNews.newsArchive.form.advancedSearch.btn.clearSelection')}
            </a>
        </p>
        ";
        return $text;
    }

    /**
     *
     */
    function getLoggedInLinks($content_id) {
        if (!isLoggedInWWW()) {
            return;
        }

        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        getCPWidgetObj('social_emailToFriend'); // to include the jss in the modal

        $subscribeUrl = $cpUrl->getUrlBySecType('Subscribe');
        $emailToFriendUrl = "/index.php?module=lawNews_contact&_spAction=emailToFriend&showHTML=0&content_id={$content_id}";
        $clipItUrl = "/index.php?module=lawNews_contact&_spAction=saveToMyClips&showHTML=0&content_id={$content_id}";
        $text = "
        <div class='article_memeber_links floatbox'>
            <ul class='noDefault float_right'>
                <li>
                    <a href='javascript:void(0)' class='print_article'>{$ln->gd('m.lawNews.newsArchive.printArticle')}</a>
                </li>
                <li>
                    <a href='javascript:void(0)' link='{$emailToFriendUrl}' class='email_to_friend' dialogTitle='{$ln->gd('w.social.emailToFriend.heading')}'>{$ln->gd('m.lawNews.newsArchive.emailToFriend')}</a>
                </li>
                <li>
                    <a href='javascript:void(0)' link='{$clipItUrl}' class='clip_it'>{$ln->gd('m.lawNews.newsArchive.clipIt')}</a>
                </li>
            </ul>
        </div>";

        $fn->addLangKey('w.social.emailToFriend.message.success'); //to show the success message after email to friend in the modal

        return $text;
    }

    /**
     *
     * @param array $row
     */
    function getReporters($rowContent) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $dataArray = $this->model->getReportersArray($rowContent['content_id']);

        if (count($dataArray) == 0) {
            return;
        }
        $mediaExp = array(
            'folder' => 'thumb'
        );
        $rows = '';
        foreach ($dataArray AS $row) {
            $title = "<div class='title'>{$row['title']}</div>";
            $pic = '';
            $pic = $media->getMediaPicture('lawNews_reporter', 'picture', $row['reporter_id'], $mediaExp);
            if ($pic != '') {
                $pic = "<div class='pic'>{$pic}</div>";
            }
            $description = $row['description'];
            $desc = "
            <div class='desc'>
                {$pic}
                {$description}
            </div>
            ";

//            if($rowContent['category_type'] == 'Q & A'){
//                $rows .= "
//                <li>
//                    {$pic}
//                    {$title}
//                </li>
//                ";
//            } else {
//                $rows .= "
//                <li>
//                    {$title}
//                    {$desc}
//                </li>
//                ";
//            }
            $rows .= "
            <li>
                {$title}
                {$desc}
            </li>
            ";
        }

//        $addClass = ($rowContent['category_type'] == 'Q & A') ? 'qAndA' : '';
        $addClass = '';

        $text = "
        <div class='reporterList'>
            <h2 class='ruled'>{$ln->gd('m.lawNews.newsArchive.detail.reporter.heading')}</h2>
            <ul class='noDefault {$addClass}'>
            {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

}
