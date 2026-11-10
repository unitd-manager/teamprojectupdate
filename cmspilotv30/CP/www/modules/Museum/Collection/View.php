<?
class CP_Www_Modules_Museum_Collection_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jqPrettyPhoto-3.1.3_museum');
     /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('museum_collection', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <article class='row floatbox'>
                {$this->getStandardFatListRow($row)}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $title = "<header><h1>{$ln->gfv($row, 'title', '0')}</h1></header>";

        $exp = array('style' => 'mb5 pic', 'zoomImage' => $cpCfg['m.webBasic.content.zoomListImages']);

        $pic = $media->getMediaPicture('museum_collection', 'picture', $row['collection_id'], $exp);

        $wFlickr = getCPWidgetObj('media_flickr');
        $wPrimaryImage = '';
        if($pic == ''){
            $wPrimaryImage = $wPrimaryImage = $wFlickr->getWidget(array(
                        'api_key'   => $cpCfg['cp.flickr_api_key']
                        ,'secret'    => $cpCfg['cp.flickr_secret']
                        ,'helperFn'  => 'photosets_getPhotos_getPrimaryPhoto'
                        ,'flickrReference'  => $row['flickr_ref']
                        ,'refreshCacheSec'  => 3600 * 24 //every 24 hours
                        ,'imageSize'  => 'small'
                        ,'zoomImageSize'  => 'medium640'
                    ));
            $pic = $wPrimaryImage;
        }

        $wPhotoSets = '';
        if($row['flickr_ref'] != ''){
            $hidePrimaryImage = ($wPrimaryImage != '') ? true : false;
            $wPhotoSets = $wFlickr->getWidget(array(
                 'api_key'   => $cpCfg['cp.flickr_api_key']
                ,'secret'    => $cpCfg['cp.flickr_secret']
                ,'helperFn'  => 'photosets_getPhotos'
                ,'flickrReference'  => $row['flickr_ref']
                ,'refreshCacheSec'  => 3600 * 24 //every 24 hours
                ,'imageSize'        => 'square'
                ,'zoomImageSize'    => 'medium640'
                ,'hidePrimaryImage' => $hidePrimaryImage
            ));
        }

        if ($pic != ''){
            $pic = "<div class='float_right picWrap'>{$pic}</div>";
        }


        $text = "
        {$pic}
        {$title}
        <div class='description'>
            {$ln->gfv($row, 'description')}
        </div>
        {$wPhotoSets}
        ";

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

        $hook = getCPModuleHook('museum_collection', 'detail', $row, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $title = "<h1>{$ln->gfv($row, 'title', '0')}</h1>";
        $exp = array('style' => 'mb5');
        $pic = $media->getMediaPicture('museum_collection', 'picture', $row['collection_id'], $exp);

        if ($pic != ''){
            $pic = "<div class='float_right'>{$pic}</div>";
        }

        $text = "
        {$pic}
        {$title}
        {$ln->gfv($row, 'description', '0')}
        ";

        return $text;
    }

    /**
     *
     * @param type $dataArray
     * @return type
     */
    function getSponsor($dataArray){
        $ln = Zend_Registry::get('ln');

        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <article class=''>
                {$ln->gfv($row, 'sponsor_description', '0')}
            </article>
            ";
        }
        $text = "
        <div class='sponsor'>
            {$rows}
        </div>
        ";

        return $text;
    }

}
