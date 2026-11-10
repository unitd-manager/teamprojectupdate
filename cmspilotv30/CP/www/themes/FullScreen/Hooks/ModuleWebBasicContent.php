<?
class CP_Www_Themes_FullScreen_Hooks_ModuleWebBasicContent
{
    /**
     *
     */
    function getListInDetail($dataArray) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $list = '';
        
        //News List & Detail
        $orderBy = 'content_date DESC, sort_order';
        if ($tv['secType'] == 'Career') {
            $orderBy = 'sort_order';
        }
        $wContent = getCPWidgetObj('content_record');
        $listRecs = $wContent->getWidget(array(
             'returnDataOnly' => true
            ,'sectionId' => $tv['room']
            ,'categoryId' => $tv['subRoom']
            ,'displayLimit' => 100
            ,'orderBy' => $orderBy
        ));

        $imgPath = CP_THEMES_PATH_ALIAS . $cpCfg['cp.theme'] . '/images/';

        $noteViewVideo = $ln->gd('cp.info.viewVideo');
        $noteDownloadPDF = $ln->gd('cp.info.downloadPDF');
        $noteViewFlipbook = $ln->gd('cp.info.viewFlipbook');

        //list page in the left panel
        foreach($listRecs as $row){
            $picArr = $media->getFirstMediaRecord('webBasic_content', 'picture', $row['content_id']);
            $picUrl = '';
            if(count($picArr) > 0){
                $picUrl = $picArr['file_normal'];
            }
            $url = $cpUrl->getUrlByRecord($row, 'content_id');

            $classTxt = ($tv['record_id'] == $row['content_id']) ? "current" : '';

            $icons = '';
            $embed_code = trim($row['embed_code']);
            if ($embed_code != ''){
                $title = htmlspecialchars($row['title'], ENT_QUOTES);
                //ex: http://www.youtube.com/watch?v=Ns00LLNix94||external
                $embedArr = explode('||', $embed_code);
                $class = '';
                $external = isset($embedArr[1]) ? strtolower($embedArr[1]) : '';
                if ($external == 'external') {
                    $videoUrl = $embedArr[0];
                } else {
                    $class = 'prettyPhoto';
                    $videoUrl = "/index.php?module=webBasic_content&_spAction=videoEmbedCode" .
                                "&content_id={$row['content_id']}&showHTML=0";
                    $videoUrl = $embed_code;
                }

                $attLink = "<a class='{$class}' title='{$noteViewVideo}'
                               href='{$videoUrl}' target='_blank'>
                               <img src='{$imgPath}video.png' alt='{$title}'></a>";
                $icons .= "<span class='ml10'>{$attLink}</span>";
            }

            $attArr = $media->model
                      ->getFirstMediaRecord('webBasic_content', 'attachment', $row['content_id']);
            $attLink = '';
            if (count($attArr) > 0){
                $saveUrl = "/index.php?plugin=common_media&_spAction=saveMedia" .
                           "&media_id={$attArr['media_id']}&showHTML=0";
                $attLink = "
                <a href='{$saveUrl}' title='{$noteDownloadPDF}'>
                    <img src='{$imgPath}pdf.png'>
                </a>
                ";
                $icons .= "<span class='ml10'>{$attLink}</span>";
            }
            if (trim($row['flipbook_url']) != ''){
                $attLink = "
                <a target='_blank' href='{$row['flipbook_url']}' title='{$noteViewFlipbook}'>
                    <img src='{$imgPath}pdf.png'>
                </a>
                ";
                $icons .= "<span class='ml10'>{$attLink}</span>";
            }

            $list .= "
            <li>
                <a href='{$url}' class='{$classTxt} detailLink' pic='{$picUrl}'>{$row['title']}</a>
                {$icons}
            </li>
            ";
        }

        //detail page in the right panel
        $desc = '';
        if ($tv['record_id'] != ''){
            $row = $dataArray[0];

            $title = '';
            if ($row['show_title'] == 1) {
                $title = $ln->gfv($row, 'title', '0');
                if ($row['linkedin_profile'] != '') {
                    $linkedInUrl = "<a href='{$row['linkedin_profile']}' target='_blank'>" .
                                   "<img src='{$imgPath}icon_linkedin.png'/></a>";
                    $title = "<h1>{$title} {$linkedInUrl}</h1>";
                } else {
                    $title = "<h1>{$title}</h1>";
                }
            }
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
            //if ($row['content_date'] != ''){
            //    $content_date = $fn->getCPDate($row['content_date']);
            //    $content_date = "
            //    <div class='date'>
            //        {$content_date}
            //    </div>
            //    ";
            //}

            $desc = "
            {$title}
            {$content_date}
            {$pic}
            {$short_desc}
            <div class='desc'>
                {$ln->gfv($row, 'description', '0')}
            </div>
            ";
        } //detail content

        //list page
        $peoplePhotosInList = '';
        if ($tv['record_id'] == ''){
            $peoplePhotosInList = $this->getPeoplePhotos($dataArray);
        }


        $text = "
        <div class='listInDetail newsList'>
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
                <div class='pic-list'></div>
            </div>
        </div>

        {$peoplePhotosInList}
        ";

        $text = $fn->replaceLangKeys($text);
        return $text;
    }

    function getPeoplePhotos($dataArray) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $list = '';

        $text = '';
        $list = '';
        foreach($dataArray as $row){
            $picArr = $media->getFirstMediaRecord('webBasic_content', 'otherPicture', $row['content_id']);
            $picUrl = '';
            if(count($picArr) > 0){
                $picUrl = $picArr['file_normal'];
                $url = $cpUrl->getUrlByRecord($row, 'content_id');
                $list .= "
                <div class='pic'>
                    <a href='{$url}'><img src='{$picUrl}'></a>
                    <div class='name'>{$row['title']}</div>
                </div>
                ";
            }
        }
        $peopleCount = count($dataArray);

        $text = "
        <div class='people-photos-in-list'>
        {$list}
        </div>
        <input type='hidden' id='peopleCount' value='{$peopleCount}'>
        ";

        return $text;
    }
}