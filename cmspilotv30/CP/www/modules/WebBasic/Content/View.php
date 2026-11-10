<?
class CP_Www_Modules_WebBasic_Content_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('webBasic_content', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <article class='row floatbox'>
                <div class='content'>
                {$this->getStandardFatListRow($row)}
                </div>
            </article>
            ";
        }

        $addThisIcons = '';

        if ($cpCfg['m.webBasic.content.showAddThis']){
            $wAddThis = getCPWidgetObj('social_addThis');
            $addThisIcons = "
            {$wAddThis->getWidget(
            )}";
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
    function getStandardFatListRow($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $title = ($row['show_title'] == 1) ? "<header><h1>{$ln->gfv($row, 'title', '0')}</h1></header>" : '';

        $exp = array('style' => 'mb5 pic', 'zoomImage' => $cpCfg['m.webBasic.content.zoomListImages']);

        if($cpCfg['cp.isMobileDevice']){
            $exp['folder'] = 'thumb';
        }

        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='float_right picWrap'>{$pic}</div>";
        }

        $embedCode = '';
        if (isset($row['embed_code']) && $row['embed_code'] != ''){
            $embedCode = "<div class='float_right embedObj'>{$row['embed_code']}</div>";
        }

        $text = "
        {$embedCode}
        {$pic}
        {$title}
        <div class='description'>
            {$ln->gfv($row, 'description')}
        </div>
        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     * @param type $dataArray
     * @return type
     */
    function getThinList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('webBasic_content', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <article class='row floatbox'>
                <div class='content'>
                {$this->getThinListRow($row)}
                </div>
            </article>
            ";
        }

        $addThisIcons = '';

        if ($cpCfg['m.webBasic.content.showAddThis']){
            $wAddThis = getCPWidgetObj('social_addThis');
            $addThisIcons = "
            {$wAddThis->getWidget(
            )}";
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
    function getThinListRow($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');


        $exp = array('style' => 'mb5 pic', 'zoomImage' => $cpCfg['m.webBasic.content.zoomListImages']);

        if($cpCfg['cp.isMobileDevice']){
            $exp['folder'] = 'thumb';
        }

        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='float_right picWrap'>{$pic}</div>";
        }

        $detailUrl = $cpUrl->getUrlByRecord($row, 'content_id');
        $title = ($row['show_title'] == 1) ? "<header><h1><a href='{$detailUrl}'>{$ln->gfv($row, 'title', '0')}</a></h1></header>" : '';

        $text = "
        {$pic}
        {$title}
        <div class='description'>
            {$ln->gfv($row, 'description_short')}
        </div>
        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $hook = getCPModuleHook('webBasic_content', 'detail', $row, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
        $exp = array('style' => 'mb5');
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
        $backToList = "<a href='javascript:history.back();' class='backToList'>{$ln->gd('cp.lbl.back')}</a>";
        if ($pic != ''){
            $pic = "<div class='float_right'>{$pic}</div>";
        }

        $text = "
        {$backToList}
        {$pic}
        {$title}
        {$ln->gfv($row, 'description', '0')}
        {$media->getMediaFilesDisplayThin('webBasic_content', 'attachment', $row['content_id'])}
        ";

        $text = $fn->replaceLangKeys($text);

        return $text;
    }

    /**
     *
     */
    function getSpContent() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //$contentType = $fn->getReqParam('ct', 'Record', true);
        $contentType = $fn->getReqParam('ct', 'Record');

        $content_id  = (int)$fn->getReqParam('content_id', 0, true);
        $useDivCont = $fn->getReqParam('useDivCont', 0);
        $hasPrintBtn = $fn->getReqParam('hasPrintBtn', 0);
        $folder = $fn->getReqParam('folder');
        $showShortDesc = $fn->getReqParam('showShortDesc');

        $wRecord = getCPWidgetObj('content_record');

        $mediaExp = array();
        $mediaExp['folder'] = $folder;

        $parArr = array('contentType' => $contentType);
        $parArr['useDivContainer'] = $useDivCont ? true : false;
        $parArr['hasPrintBtn']     = $hasPrintBtn ? true : false;
        $parArr['showShortDesc']   = $showShortDesc ? true : false;
        $parArr['mediaExp']        = $mediaExp;
        if($content_id > 0){
            $parArr['contentId']  = $content_id;
        }

        $text = "
        <div class='spContent'>
            {$wRecord->getWidget($parArr)}
        </div>
        ";

        $text = $fn->replaceLangKeys($text);

        return $text;
    }

    /**
     *
     */
    function getTestimonialsList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $addThisIcons = '';

        if ($cpCfg['m.webBasic.content.showAddThis']){
            $wAddThis = getCPWidgetObj('social_addThis');
            $addThisIcons = "
            {$wAddThis->getWidget(
            )}";

        }

        $rows = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<cite class='float_right'>{$ln->gfv($row, 'title', '0')}</cite>" : '';

            $exp = array('style' => 'mb5 pic');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

            if ($pic != ''){
                $pic = "<div class='float_right picWrap'>{$pic}</div>";
            }

            $embedCode = '';
            if (isset($row['embed_code']) && $row['embed_code'] != ''){
                $embedCode = "<div class='float_right embedObj'>{$row['embed_code']}</div>";
            }

            $quote = '';
            if ($ln->gfv($row, 'description', '0') != ''){
                $quote = "
                <blockquote>
                    {$ln->gfv($row, 'description', '0')}
                </blockquote>
                ";
            }

            $rows .= "
            <div class='row floatbox'>
                {$embedCode}
                {$pic}
                {$quote}
                {$title}
            </div>
            ";
        }

        $text = "
        <div class='fatList'>
            {$rows}
            {$addThisIcons}
        </div>
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     */
    function getPressList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $addThisIcons = '';

        if ($cpCfg['m.webBasic.content.showAddThis']){
            $wAddThis = getCPWidgetObj('social_addThis');
            $addThisIcons = "
            {$wAddThis->getWidget(
            )}";
        }

        $rows = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<cite class='float_right'>{$ln->gfv($row, 'title', '0')}</cite>" : '';

            $exp = array('style' => 'mb5 pic');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

            if ($pic != ''){
                $pic = "<div class='float_right picWrap'>{$pic}</div>";
            }

            $attArr = $media->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id']);

            $readMore = '';
            if (count($attArr) > 0){
                $readMoreUrl  = $attArr['file_normal'];
                $readMore = "
                <div class='readMore'>
                    <a href='{$readMoreUrl}' target='_blank' class='downloadFile'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
                ";
            } else if ($ln->gfv($row, 'description', '0') != ''){
                $readMoreUrl = $cpUrl->getUrlByRecord($row, 'content_id');
                $readMore = "
                <div class='readMore'>
                    <a href='{$readMoreUrl}'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
                ";
            }

            $rows .= "
            <div class='row'>
                <div class='date'>
                    {$fn->getCPDate($row['content_date'])}
                </div>
                <div class='description'>
                    {$ln->gfv($row, 'title')}
                    {$ln->gfv($row, 'description_short')}
                    {$readMore}
                </div>
            </div>
            ";
        }

        $info = $ln->gd2('m.webBasic.content.pressList.info');
        if ($info != ''){
            $info = "
            <div class='info'>{$info}</div>
            ";
        }

        $text = "
        <div class='fatList pressList'>
            {$info}
            {$rows}
        </div>
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     */
    function getPressDetail($row) {
        $dataArray[0] = $row;

        return $this->getPressList($dataArray);
    }

    /**
     *
     */
    function getImageBlockAttachmentList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row){
            $picArr = $media->getFirstMediaRecord('webBasic_content', 'picture', $row['content_id']);
            $attArr = $media->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id']);
            $url = count($attArr) > 0 ? $attArr['file_normal'] : '#';
            $img = count($picArr) > 0 ? $picArr['file_normal'] : '';

            $rows .= "
            <article class='row'>
                <h1>{$ln->gfv($row, 'title')}</h1>
                <a href='{$url}' target='_blank' class='downloadFile'><img src='{$img}'></a>
            </article>
            ";
        }

        $text = "
        <div class='fatList imageBlockAttachmentList'>
            {$rows}
        </div>
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }
    /**
     *
     */
    function getListAsTabs($dataArray) {
        $wTabs = getCPWidgetObj('ui_tabs');

        $text = "
        <div class='fatList'>
            {$wTabs->getWidget(array(
                 'type' => 'byContentDataArray'
                ,'contentArray' => $dataArray
            ))}
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getListInDetail($dataArray) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $media = Zend_Registry::get('media');

        $hook = getCPModuleHook('webBasic_content', 'listInDetail', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $list = '';

        $wContent = getCPWidgetObj('content_record');
        $listRecs = $wContent->getWidget(array(
             'returnDataOnly' => true
            ,'sectionId'      => $tv['room']
            ,'categoryId'     => $tv['subRoom']
            ,'displayLimit'   => 100
        ));

        foreach($listRecs as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');

            $class = ($tv['record_id'] == $row['content_id']) ? " class='current'" : '';

            $list .= "
            <li>
                <a href='{$url}'{$class}>{$row['title']}</a>
            </li>
            ";
        }

        $desc = '';
        if ($tv['record_id'] != ''){
            $row = $dataArray[0];

            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
            $exp = array('style' => 'mb5');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

            if ($pic != ''){
                $pic = "<div class='picture'>{$pic}</div>";
            }

            $short_desc = $ln->gfv($row, 'description_short', '0');

            if ($short_desc != '') {
                $short_desc = "
                <div class='shortDesc'>
                    {$short_desc}
                </div>
                ";
            }

            $content_date = '';
            if ($row['content_date'] != ''){
                $content_date = $fn->getCPDate($row['content_date']);
                $content_date = "
                <div class='date'>
                    {$content_date}
                </div>
                ";
            }

            $desc = "
            {$title}
            {$content_date}
            {$pic}
            {$short_desc}
            <div class='desc'>
                {$ln->gfv($row, 'description', '0')}
            </div>
            ";
        }

        $text = "
        <div class='listInDetail'>
            <div class='list'>
                <div class='inner'>
                    <ul class='noDefault'>
                        <h1 class='catTitle'>{$fn->getPageTitle()}</h1>
                        {$list}
                    </ul>
                </div>
            </div>
            <div class='detail'>
                <div class='inner'>
                    <div class='inner2'>
                        {$desc}
                    </div>
                </div>
            </div>
        </div>
        ";

        $text = $fn->replaceLangKeys($text);

        return $text;
    }

    /**
     *
     */
    function getListAsAccordian($dataArray) {
        $wTabs = getCPWidgetObj('ui_accordian');

        $text = "
        <div class='fatList'>
            {$wTabs->getWidget(array(
                 'type' => 'byContentDataArray'
                ,'contentArray' => $dataArray
            ))}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getListDetailCombo($dataArray) {
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $media = Zend_Registry::get('media');
        $subNav = Zend_Registry::get('subNav');
        $tv = Zend_Registry::get('tv');

        $rows = '';

        foreach($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');

            $exp = array('folder' => 'thumb');
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
            $pic = trim($pic) == '' ? '&nbsp;' : $pic;
            $otherPic = $media->getMediaPicture('webBasic_content', 'otherPicture', $row['content_id'], $exp);
            $otherPic = trim($otherPic) == '' ? '&nbsp;' : $otherPic;

            $title ="
            <h1 class='mb10'>{$ln->gfv($row, 'title')}</h1>
            ";

            $shortDesc = $ln->gfv($row, 'description_short');
            $longDesc = $ln->gfv($row, 'description');
            $showHideDesc = '';
            if (trim($longDesc) != '') {
                $showHideDesc = "
                <div>
                    <a class='showHideDesc' href='#'>
                    <span class='more'>&gt; {$ln->gd('more')}</span>
                    <span class='less'>&gt; {$ln->gd('less')}</span>
                    </a>
                </div>
                ";
            }
            $rows .= "
            <div class='subcolumns row'>
                <div class='c20l'>
                    <div class='subcl txtCenter'>
                        {$pic}
                    </div>
                </div>

                <div class='c60l'>
                    <div class='subc'>
                        {$title}
                        <div class='short-description'>
                        {$shortDesc}
                        </div>
                        <div class='long-description'>
                            {$longDesc}
                        </div>
                        {$showHideDesc}
                    </div>
                </div>

                <div class='c20r'>
                    <div class='subcr'>
                        {$otherPic}
                    </div>
                </div>
            </div>
            ";
        }
        $rows = $cpUtil->getDisplayListRows($rows);

        $urlShowAll = $cpUrl->getUrlBySecType($tv['secType']);

        $subNavText = $subNav->getWidget(array(
             'class' => 'hlist list-detail-combo'
            ,'displayShowAllMenu' => true
            ,'showAllMenuText' => $ln->gd('cp.lbl.showAll')
            ,'showAllMenuUrl' => $urlShowAll
        ));
        $text = "
        <div class='floatbox'>
            <h1>{$tv['secTitle']}</h1>
            <div class=''>{$subNavText}</div>
        </div>
        <div class='rows'>
            {$rows}
        </div>
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    /**
     *
     */
    function getSitemap($dataArray) {
        $wSitemap = getCPWidgetObj('common_sitemap');

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <article class='row floatbox'>
                <div class='content'>
                    {$this->getStandardFatListRow($row)}
                </div>
            </article>
            ";
        }

        $text = "
        <div class='fatList'>
            {$rows}
            {$wSitemap->getWidget(
            )}
        </div>
        ";

        return $text;
    }
}
