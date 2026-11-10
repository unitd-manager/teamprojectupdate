<?
class CP_Www_Widgets_Content_RssFeed_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;

        $rowsHTML = ($this->rowsHTML != '') ? $this->rowsHTML : $this->getRowsHTML();

        if ($rowsHTML != ''){
            $heading = '';
            if ($c->heading != '' && $c->showHeading) {
                $heading = "<{$c->headingTag}>{$c->heading}</{$c->headingTag}>";
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


            $contStart = "<ul class='noDefault'>";
            $contEnd   = "</ul>";
            if ($c->useDivContainer) {
                $contStart = "<div>";
                $contEnd   = "</div>";
            }

            $text = "
            {$heading}
            {$contStart}
                {$rowsHTML}
            {$contEnd}
            {$grpReadMore}
            ";

            return $text;
        }
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
        $showDate          = $c->showDate;     
        
        $addUrlForTitle    = $c->addUrlForTitle;
        $useDivContainer   = $c->useDivContainer;
        
        $truncateDesc       = $c->truncateDesc;
        $truncateDescLength = $c->truncateDescLength;
        $truncateDescEnding = $c->truncateDescEnding;

        $truncateShortDesc       = $c->truncateShortDesc;
        $truncateShortDescLength = $c->truncateShortDescLength;
        $truncateShortDescEnding = $c->truncateShortDescEnding;

        $rowTag = "li";
        if ($useDivContainer) {
            $rowTag = "div";
        }

        $rows = '';
        foreach($this->model->dataArray AS $row) {

            if ($addUrlForTitle){
                $target = '';
                if ($row['external_link'] != '') {
                    $target = "target='_blank'";
                }

                $title = "<h3 class='title'><a href='{$row['url']}' {$target}>{$row['title']}</a></h3>";
            } else {
                $title = "<h3 class='title'>{$row['title']}</h3>";
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
                $content_date = $fn->getCPDate($row['content_date']);
                $date = "
                <div class='date'>
                    {$content_date}
                </div>
                ";
            }

//            if ($showShortDesc){
//                $desc_short = ($truncateShortDesc) ? $cpUtil->truncate($row['desc_short'], $truncateShortDescLength, $truncateShortDescEnding, true, true)
//                                                   : $row['desc_short'];
//                $shortDesc = "
//                <div class='shortDesc'>
//                    {$desc_short}
//                </div>
//                ";
//            }

            if ($showDesc){
                $description = ($truncateDesc) ? $cpUtil->truncate($row['description'], $truncateDescLength, $truncateDescEnding, true, true)
                                                   : $row['description'];
                $desc = "
                <div class='desc'>
                    {$title}
                    {$description}
                </div>
                ";
            }

            $rows .= "
            <{$rowTag}>
                <div class='inner'>
                    {$date}
                    {$shortDesc}
                    {$desc}
                    {$readMore}
                </div>
            </{$rowTag}>
            ";
        }

        return $rows;
    }
}