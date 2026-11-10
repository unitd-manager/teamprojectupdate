<?
class CP_Www_Modules_LawNews_Jurisdiction_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');

        $dataArray2 = $this->model->getDataArrayGroupedByRegion();

        $rows = '';
        foreach ($dataArray2 as $region){
            $innerRows = '';
            foreach($region['rows'] AS $row){
                //$tempRecordCount = $this->tempGetTotalContents($row['jurisdiction_id']);

                $url = $cpUrl->getUrlByRecord($row, 'jurisdiction_id', array('secType' => 'Jurisdiction'));
                $innerRows .= "
                <li class='jurisdiction'>
                    <a href='{$url}'>{$row['title']}</a>
                </li>";
            }

            $rows .= "
            <li class='region'>
                <h2>{$region['name']}</h2>
                <ul class='noDefault'>
                    {$innerRows}
                </ul>
            </li>
            ";
        }

        $text = "
        <div class='jurisdictionList'>
            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $mediaExp['returnFileNameOnly'] = true;
        $mediaExp['folder'] = 'thumb';
        $secondLogo = $media->getMediaPicture('common_site', 'secondLogo', $cpCfg['cp.site_id'], $mediaExp);

        $secondLogoScript = '';
        if($secondLogo != ''){
            $secondLogoScript = "
            <script>
            $(function(){
                cpm.lawNews.jurisdiction.showSecondLogoNearWHeader('{$secondLogo}');
            });
            </script>
            ";
        }


        $readMoreUrl  = $cpUrl->getUrlByRecord($row, 'jurisdiction_id', array('secType' => 'Jurisdiction'));
        $readMoreUrl .= "?sp=archive";

        $addSearchCond = "
        AND c.content_id IN (
            SELECT DISTINCT jc.content_id
            FROM jurisdiction_content jc
            LEFT JOIN (jurisdiction j) ON (j.jurisdiction_id = jc.jurisdiction_id )
            WHERE j.published = 1
              AND jc.jurisdiction_id = {$row['jurisdiction_id']}
        )
        ";

        $wRecord = getCPWidgetObj('content_record');
        $wCountryUpdate = $wRecord->getWidget(array(
             'helperFn'       => 'getWidgetByCategoryType'
            ,'sectionType'    => 'News Archive'
            ,'categoryType'   => 'Country Update'
            ,'showHeading'    => FALSE
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => true
            ,'showPic'        => FALSE
            ,'addSearchCond'  => $addSearchCond
            ,'displayLimit'   => 6
            ,'showGroupReadMore' => true
            ,'groupReadMoreUrl'  => $readMoreUrl
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wNewsAndAnalysis = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'News & Analysis'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 10
            ,'showGroupReadMore' => true
            ,'groupReadMoreUrl'  => $readMoreUrl
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wQAndA = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'Q & A'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 1
            ,'showGroupReadMore' => true
            ,'groupReadMoreUrl'  => $readMoreUrl
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wExternalLinks = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'External Links'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 2
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wNewsInBrief = $wRecord->getWidget(array(
             'helperFn'       => 'getWidgetByCategoryType'
            ,'sectionType'    => 'News Archive'
            ,'categoryType'   => 'News In Brief'
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addSearchCond'  => $addSearchCond
            ,'displayLimit'   => 10
            ,'showGroupReadMore' => true
            ,'groupReadMoreUrl'  => $readMoreUrl
        ));

        $correspondent_id = $this->model->getActiveCorrespondentId($row['jurisdiction_id']);
        $row_corres = $fn->getRecordRowByID('correspondent', 'correspondent_id', $correspondent_id);

        $corrsNameHeader = '';
        if(is_array($row_corres)){
            $corrsNameHeader = "
            <div class='float_right black'>
                {$row_corres['title']}
            </div>
            ";
        }

        $text = "
        <div class='jurisdictionDetail'>
            <h1 class='ruled'>{$ln->gfv($row, 'title', '0')}</h1>
            <div class='floatbox countryUpdate'>
                <h2 class='ruled'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            {$ln->gd('m.lawNews.jurisdiction.detail.countryUpdate.heading')}
                        </div>
                        {$corrsNameHeader}
                    </div>
                </h2>
                {$this->getCountryUpdateNoSponsorText($row)}
                {$wCountryUpdate}
            </div>
            <div class='floatbox showSecondLogo'>
                <div class='float_left newsAndAnalysis leftBox'>
                    {$wNewsAndAnalysis}
                </div>
                <div class='float_left rightBox'>
                    <div class='qAndA'>
                        {$wQAndA}
                    </div>
                    <div class='externalLinks'>
                        {$wExternalLinks}
                    </div>
                    <div class='newsInBrief'>
                        {$wNewsInBrief}
                    </div>
                </div>
            </div>
        </div>
        {$secondLogoScript}
        ";

        return $text;
    }

    /**
     *
     * @param type $row
     */
    function getCountryUpdateNoSponsorText($row){
        $ln = Zend_Registry::get('ln');

        $text = '';
        if($this->model->getActiveCorrespondentId($row['jurisdiction_id']) == 0){
            $text = "<p>{$ln->gfv($row, 'description', '0')}</p>";
        }

        return $text;

    }

    /**
     *
     */
    function getArchiveList($dataArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        if($tv['record_id'] == ''){
            return;
        }
        $archive_year = (int)$fn->getReqParam('archive_year', date('Y'));

        $addSearchCond = "
        AND c.content_id IN (
            SELECT DISTINCT jc.content_id
            FROM jurisdiction_content jc
            LEFT JOIN (jurisdiction j) ON (j.jurisdiction_id = jc.jurisdiction_id )
            WHERE j.published = 1
              AND jc.jurisdiction_id = {$tv['record_id']}
        )
        AND YEAR(c.content_date) = {$archive_year}
        ";

        $wRecord = getCPWidgetObj('content_record');
        $wQAndA = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'Q & A'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 1
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wCountryUpdate = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'Country Update'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 500
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wCountryUpdateExternal = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'Country Update External'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 500
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wNewsAndAnalysis = $wRecord->getWidget(array(
             'helperFn'          => 'getWidgetByCategoryType'
            ,'sectionType'       => 'News Archive'
            ,'categoryType'      => 'News & Analysis'
            ,'showDesc'          => FALSE
            ,'showPicInDesc'     => FALSE
            ,'showShortDesc'     => TRUE
            ,'addSearchCond'     => $addSearchCond
            ,'displayLimit'      => 500
        ));

        $wRecord = getCPWidgetObj('content_record');
        $wNewsInBrief = $wRecord->getWidget(array(
             'helperFn'       => 'getWidgetByCategoryType'
            ,'sectionType'    => 'News Archive'
            ,'categoryType'   => 'News In Brief'
            ,'showDesc'       => FALSE
            ,'showShortDesc'  => FALSE
            ,'showPic'        => FALSE
            ,'addSearchCond'  => $addSearchCond
            ,'displayLimit'   => 500
        ));

        $text = "
        <div class='jurisdictionArchiveList'>
            <h2 class='ruled'>{$ln->gfv($dataArray[0], 'title', '0')}</h2>
            {$this->getQuickSearch()}
            <div class='floatbox qAndA'>
                {$wQAndA}
            </div>
            <div class='floatbox countryUpdate'>
                {$wCountryUpdate}
            </div>
            <div class='floatbox countryUpdateExternal'>
                {$wCountryUpdateExternal}
            </div>
            <div class='floatbox newsAndAnalysis'>
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
    function getQuickSearch() {

        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $sp = $fn->getReqParam('sp');
        $archive_year  = (int)$fn->getReqParam('archive_year', date('Y'));

        $expYear = array(
             'section_type' => "News Archive"
        );

        $SQLYear= $fn->getContentYearSQL($expYear);
        $yearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $archive_year);

        $formAction = CP_REQUEST_URI;

        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
            <input type='hidden' name='sp' value='{$sp}'>
            <div class='quickSearch'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <select name='archive_year'>
                            <option value=''>{$ln->gd('cp.form.lbl.selectYear')}</option>
                            {$yearOptions}
                        </select>
                    </div>
                </div>
            </div>
        </form>
        ";

        return $text;
    }

    function tempGetTotalContents($jurisdiction_id){
        $db = Zend_Registry::get('db');
        $SQL = "
        SELECT COUNT(*) AS total_records
        FROM jurisdiction_content jc
        LEFT JOIN (content c) ON (jc.content_id = c.content_id )
        WHERE c.published = 1
          AND jc.jurisdiction_id = {$jurisdiction_id}
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['total_records'];
    }

}
