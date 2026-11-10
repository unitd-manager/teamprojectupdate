<?
class CP_Www_Widgets_Content_Record_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $c = &$this->controller;

        $rowsHTML = ($this->rowsHTML != '') ? $this->rowsHTML : $this->getRowsHTML();

        if ($rowsHTML != ''){
            $heading = '';
            if ($c->heading != '' && $c->showHeading) {
                $heading = "<{$c->headingTag}>{$c->heading}</{$c->headingTag}>";
            }

            $scrollText = '';
            if ($c->scrollContent){
                CP_Common_Lib_Registry::arrayMerge('jssKeys', array("simplyscroll-1.0.4"));

                CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    exp = {
                         handle: '{$c->scrollHandle}'
                        ,speed: '{$c->scrollSpeed}'
                        ,frameRate: '{$c->scrollFrameRate}'
                        ,scrollDirection: '{$c->scrollDirection}'
                        ,scrollClass: '{$c->scrollClass}'
                        ,autoMode: '{$c->scrollAutoMode}'
                    }
                    cpw.content.record.simplyScroll(exp);
                "));
            }

            $grpReadMore = '';
            if ($c->showGroupReadMore){
                $grpReadMore = "
                <div class='readMore'>
                    <a href='{$ln->gd($c->groupReadMoreUrl)}'>
                        {$ln->gd($c->groupReadMoreLbl)}
                    </a>
                </div>
                ";
            }

            $id = ($c->scrollContent) ? " id='{$c->scrollHandle}'" : '';

            $contStart = "<ul class='noDefault'{$id}>";
            $contEnd   = "</ul>";
            if ($c->useDivContainer) {
                $contStart = "<div>";
                $contEnd   = "</div>";
            }

            $printBtnText = '';
            if ($c->hasPrintBtn) {
                $printBtnText = "
                <div><a href='javascript:window.print()' class='print-btn'></a></div>
                ";
            }

            $text = "
            {$printBtnText}
            {$heading}
            {$contStart}
            {$rowsHTML}
            {$contEnd}
            {$grpReadMore}
            ";

            $text = $fn->replaceLangKeys($text);
            return $text;
        }
    }

    //========================================================//
    function getWidgetLatestNews() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;

        $c->contentType    = 'Record';
        $c->specialFilter  = 'Latest';
        $c->showShortDesc  = false;
        $c->showDesc       = false;
        $c->showDate       = true;
        $c->addUrlForTitle = true;
        $c->heading        = ($c->heading != '') ? $c->heading : $ln->gd('w.content.record.whatsnew.heading');

        $this->model->dataArray = $this->model->getDataArray();
        $this->rowsHTML = $this->getRowsHTML();

        return $this->getWidget();
    }

    //========================================================//
    function getWidgetBySectionType() {
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;
        $secRec = getCPModelObj('webBasic_section')->getRecordByType($c->sectionType);

        $c->contentType    = ($c->contentType != '') ? $c->contentType : 'Record';
        $c->specialFilter  = 'By Section Type';
        $c->addUrlForTitle = true;
        $c->heading = ($c->heading != '') ? $c->heading : $secRec['title'];

        if(count($c->mediaExp) == 0){
            $c->mediaExp = array(
                'folder' => 'thumb'
            );
        }

        $this->model->dataArray = $this->model->getDataArray();
        $this->rowsHTML = $this->getRowsHTML();

        return $this->getWidget();
    }

    //========================================================//
    function getWidgetByCategoryType() {
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;
        $catRec = getCPModelObj('webBasic_category')->getRecordByType($c->categoryType, $c->sectionType);
        $c->contentType    = ($c->contentType != '') ? $c->contentType : 'Record';
        $c->specialFilter  = 'By Category Type';
        $c->addUrlForTitle = true;
        $c->heading        = ($c->heading != '') ? $c->heading : $catRec['title_lang'];
        if(count($c->mediaExp) == 0){
            $c->mediaExp = array(
                'folder' => 'thumb'
            );
        }

        $this->model->dataArray = $this->model->getDataArray();
        $this->rowsHTML = $this->getRowsHTML();

        return $this->getWidget();
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $cpUtil = Zend_Registry::get('cpUtil');

        $c = &$this->controller;

        $showShortDesc     = $c->showShortDesc;
        $showDesc          = $c->showDesc;
        $showPic           = $c->showPic;
        $showDate          = $c->showDate;
        $mediaExp          = $c->mediaExp;
        $specialFilter     = $c->specialFilter;
        $addUrlForTitle    = $c->addUrlForTitle;
        $useDivContainer   = $c->useDivContainer;
        $blockQuote        = $c->blockQuote;
        $showTitleBelowPic = $c->showTitleBelowPic;
        $showRecordTitle   = $c->showRecordTitle;

        $truncateDesc       = $c->truncateDesc;
        $truncateDescLength = $c->truncateDescLength;
        $truncateDescEnding = $c->truncateDescEnding;

        $truncateShortDesc       = $c->truncateShortDesc;
        $truncateShortDescLength = $c->truncateShortDescLength;
        $truncateShortDescEnding = $c->truncateShortDescEnding;
        $headTag = $c->recordTitleHeadTag;
        
        $rowTag = "li";
        if ($useDivContainer) {
            $rowTag = "div";
        }

        $rows = '';
        $title = '';
        foreach($this->model->dataArray AS $row) {
            if ($addUrlForTitle){
                $target = '';
                if ($row['external_link'] != '') {
                    $target = "target='_blank'";
                }

                if ($showRecordTitle) {
                    $title = "
                    <{$headTag} class='title'>
                        <a href='{$row['url']}' {$target}>{$row['title']}</a>
                    </{$headTag}>
                    ";
                }
            } else {
                
                if ($showRecordTitle) {
                    $title = ($row['show_title'] == 1) ? 
                             "<{$headTag} class='title'>{$row['title']}</{$headTag}>" :
                             '';   
                }
            }

            $readMore = '';
            if ($c->showReadMore){
                $readMore = "
                <div class='readMore'>
                    <a href='{$row['url']}'>{$ln->gd($c->readMoreLbl)}</a>
                </div>
                ";
            }

            $date = '';
            $shortDesc = '';
            $desc = '';

            if ($showDate){
                $content_date = $fn->getCPDate($row['content_date'], $c->dateFormat);
                $date = "
                <div class='date'>
                    {$content_date}
                </div>
                ";
            }

            $pic = '';
            $picDesc = '';
            $picAsBgStyle = '';

            if ($showPic){
                if ($c->showPicAsBg){
                    $mediaExp['returnFileNameOnly'] = true;
                    $filename = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $mediaExp);
                    $picAsBgStyle = " style='background: url({$filename})'";

                } else if ($c->showPicInDesc){
                    $picDesc = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $mediaExp);

                    if ($picDesc != ''){
                        $picDesc = "<div class='pic'>{$picDesc}</div>";
                    }

                } else {
                    $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $mediaExp);

                    if ($pic != ''){
                        $pic = "<div class='pic'>{$pic}</div>";
                    }
                }
            }

            if ($showShortDesc){
                $desc_short = ($truncateShortDesc) ? $cpUtil->truncate($row['desc_short'], $truncateShortDescLength, $truncateShortDescEnding, true, true)
                                                   : $row['desc_short'];
                $shortDesc = "
                <div class='shortDesc'>
                    {$picDesc}
                    {$desc_short}
                </div>
                ";
            }

            if ($showDesc){
                $description = ($truncateDesc) ? $cpUtil->truncate($row['description'], $truncateDescLength, $truncateDescEnding, true, true)
                                                   : $row['description'];
                $titleBelowPic = $showTitleBelowPic ? $title : '';

                $desc = "
                <div class='desc'>
                    {$picDesc}
                    {$titleBelowPic}
                    {$description}
                </div>
                ";
            }

            $titleAbovePic = $showTitleBelowPic ? '' : $title;

            if ($blockQuote){
                $rows .= "
                <{$rowTag}>
                    <div class='inner'{$picAsBgStyle}>
                        <blockquote>
                            {$desc}
                        </blockquote>
                        <cite>{$title}</cite>
                        {$shortDesc}
                        {$readMore}
                    </div>
                </{$rowTag}>
                ";
            } else { 
                $template  = str_replace("[[rowTag]]"        , $rowTag , $c->template );
                $template  = str_replace("[[picAsBgStyle]]"  , $picAsBgStyle , $template );
                $template  = str_replace("[[pic]]"           , $pic , $template );
                $template  = str_replace("[[titleAbovePic]]" , $titleAbovePic , $template );
                $template  = str_replace("[[date]]"          , $date , $template );
                $template  = str_replace("[[shortDesc]]"     , $shortDesc , $template );
                $template  = str_replace("[[desc]]"          , $desc , $template );
                $template  = str_replace("[[readMore]]"      , $readMore , $template );
                $template  = str_replace("[[section_title]]" , $row['section_title'] , $template );
                $template  = str_replace("[[category_title]]" , $row['category_title'] , $template );
                $template  = str_replace("[[sub_category_title]]" , $row['sub_category_title'] , $template );
                $rows .= $template;
            }
        }

        return $rows;
    }
}