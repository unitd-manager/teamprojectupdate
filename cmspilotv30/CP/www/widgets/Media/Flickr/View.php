<?
class CP_Www_Widgets_Media_Flickr_View extends CP_Common_Widgets_Media_Flickr_View
{

    //========================================================//
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $rows = '';

        $text = "
        <ul id='{$c->handle}' class='{$c->appendCls}'>
            {$rows}
        </ul>
        ";

        return $text;
    }

    /**
     *
     * @return type
     */
    function photosets_getPhotos(){
        $c = &$this->controller;
        $flickrRef = $c->flickrReference;
        $f = new phpFlickr($c->api_key, $c->secret);
        if($c->enableCache){
            $this->model->enableCache($f);
        }
        $photoset = $f->photosets_getPhotos($flickrRef);

        $rows = '';
        foreach ($photoset['photoset']['photo'] as $photo) {
            if($c->hidePrimaryImage && $photo['isprimary']){
                continue;
            }

            $photoDetails = $f->photos_getInfo($photo['id']);
            $title = $photoDetails['photo']['title']['_content'];
            $description = $photoDetails['photo']['description']['_content'];
            $description = nl2br($description);

            $sizes = $f->photos_getSizes($photo['id']);

            $zoomImage = $this->getPhostoSource($sizes, $c->zoomImageSize);
            $image = $this->getPhostoSource($sizes, $c->imageSize);

            $description = htmlentities($description, ENT_QUOTES | ENT_IGNORE, "UTF-8");
            $rows .= "
            <li>
                <a href='{$zoomImage}' data-title='{$description}' rel='prettyPhoto[gal_{$flickrRef}]'>
                    <img src='{$image}' alt='{$title}'  />
                </a>
            </li>";
        }

        $text = "
        <ul id='{$c->handle}' class='noDefault {$c->appendCls}'>
            {$rows}
        </ul>
        ";

        return $text;
    }

    /**
     *
     * @return type
     */
    function photosets_getPhotos_getPrimaryPhoto(){
        $c = &$this->controller;
        $flickrRef = $c->flickrReference;
        $f = new phpFlickr($c->api_key, $c->secret);
        if($c->enableCache){
            $this->model->enableCache($f);
        }
        $photoset = $f->photosets_getPhotos($flickrRef);

        $rows = '';
        foreach ($photoset['photoset']['photo'] as $photo) {

            if($photo['isprimary']){
                $photoDetails = $f->photos_getInfo($photo['id']);
                $title = $photoDetails['photo']['title']['_content'];
                $description = $photoDetails['photo']['description']['_content'];
                $description = nl2br($description);

                $sizes = $f->photos_getSizes($photo['id']);

                $zoomImage = $this->getPhostoSource($sizes, $c->zoomImageSize);
                $image = $this->getPhostoSource($sizes, $c->imageSize);

                $description = htmlentities($description, ENT_QUOTES | ENT_IGNORE, "UTF-8");
                $rows .= "
                <a href='{$zoomImage}' data-title='{$description}' rel='prettyPhoto[gal_{$flickrRef}]'>
                    <img src='{$image}' alt='{$title}'  />
                </a>";
            }

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     * @param type $sizes
     * @param type $size
     * @return type
     */
    function getPhostoSource($sizes, $size = 'square'){

        $square         = $sizes[0]['source']; //75x75
        $large_square   = (isset($sizes[1]) && $sizes[1]['label'] == 'Large Square')? $sizes[1]['source'] : $square; //150x150
        $thumbnail      = (isset($sizes[2]) && $sizes[2]['label'] == 'Thumbnail')   ? $sizes[2]['source'] : $large_square; //100x100
        $small          = (isset($sizes[3]) && $sizes[3]['label'] == 'Small')       ? $sizes[3]['source'] : $thumbnail; //240x240
        $small320       = (isset($sizes[4]) && $sizes[4]['label'] == 'Small 320')   ? $sizes[4]['source'] : $small; //320x320
        $medium         = (isset($sizes[5]) && $sizes[5]['label'] == 'Medium')      ? $sizes[5]['source'] : $small320; //500x500
        $medium640      = (isset($sizes[6]) && $sizes[6]['label'] == 'Medium 640')  ? $sizes[6]['source'] : $medium; //640x640
        $large          = (isset($sizes[7]) && $sizes[7]['label'] == 'Large')       ? $sizes[7]['source'] : $medium640; //1024x1024

        return ${$size};
    }
    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $c = &$this->controller;
        $showCaption = $this->controller->showCaption;

        $rows = '';
        foreach($this->model->dataArray AS $row) {
            $pic = '';
            if ($row['pic'] != ''){
                $pic = "<img src='{$row['pic']}'/>";
            }

            if ($pic != '' && $row['link'] != '') {
                $pic = "<a href='{$row['link']}'>{$pic}</a>";
            }

            $caption = '';
            if ($row['caption'] != '' && $showCaption){
                $caption = "<div class='caption-bottom'>{$row['caption']}</div>";
            }

            if ($row['description'] != ''){
                $caption = ($row['caption'] != '') ? "<h1 class='caption'>{$row['caption']}</h1>" : '';

                $readMore = '';
                if ($c->showReadMore && $row['link'] != ''){
                    $readMore = "
                    <div class='readMore'>
                        <a href='{$row['link']}'>{$ln->gd($c->readMoreLbl)}</a>
                    </div>
                    ";
                }

                $inner = "
                <div class='subcolumns'>
                    <div class='{$c->subColLeft}'>
                        <div class='subcl'>
                            {$pic}
                        </div>
                    </div>
                    <div class='{$c->subColRight}'>
                        <div class='subcr'>
                            {$caption}
                            <div class='desc'>{$row['description']}</div>
                            {$readMore}
                        </div>
                    </div>
                </div>
                ";
            } else {
                $inner = "
                {$pic}
                {$caption}
                ";
            }

            $rows .="
            <li>
                {$inner}
            </li>
            ";
        }

        return $rows;
    }
}
