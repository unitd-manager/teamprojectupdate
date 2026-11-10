<?
class CP_Www_Widgets_Ecommerce_ProductRecord_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $heading = $this->controller->heading;
        $ulClass = $this->controller->ulClass;

        if ($this->getRowsHTML() != ''){
            $heading = ($heading != '') ? "<h2>{$heading}</h2>" : '';
            $ulClass = ($ulClass != '') ? ' ' . $ulClass : '';

            $text = "
            {$heading}
            <ul class='noDefault{$ulClass}'>
                {$this->getRowsHTML()}
            </ul>
            ";
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
        $c->heading        = $ln->gd('w.content.record.whatsnew.heading');
        
        $this->model->dataArray = $this->model->getDataArray();
        $this->rowsHTML = $this->getRowsHTML();

        return $this->getWidget();
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        
        $c = &$this->controller;
        
        $showTitle      = $c->showTitle;
        $showShortDesc  = $c->showShortDesc;
        $showDesc       = $c->showDesc;
        $showPic        = $c->showPic;
        $mediaExp       = $c->mediaExp;
        $specialFilter  = $c->specialFilter;
        $addUrlForTitle = $c->addUrlForTitle;

        $rows = '';
        $text = '';

        foreach($this->model->dataArray AS $row) {
            $title = '';
            if ($showTitle){
                if ($addUrlForTitle){
                    $title = "<a href='{$row['url']}'>{$row['title']}</a>";
                } else {
                    $title = $row['title'];
                }
                $title = "<div class='title'>{$title}</div>";
            }
            
            $shortDesc = '';
            $desc = '';

            if ($showShortDesc){
                $shortDesc = "
                <div class='shortDesc'>
                    {$row['desc_short']}
                </div>
                ";
            }

            $pic = '';
            if ($showPic){
                $pic = $media->getMediaPicture('ecommerce_product', 'picture', $row['product_id'], $mediaExp);
                
                if ($c->addUrlForImage){
                    $pic = "<a href='{$row['url']}'>{$pic}</a>";
                }
            }
            
            if ($showDesc == true){
                $desc = "
                <div class='desc'>
                    {$row['description']}
                </div>
                ";
            }
            
            $rows .= "
            <li>
                {$title}
                {$pic}
                {$shortDesc}
                {$desc}
            </li>
            ";
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
            
        $text = "
        {$rows}
        {$grpReadMore}                                
        ";        

        return $text;
    }
}