<?
class CP_Www_Modules_WebBasic_Gallery_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $hook = getCPModuleHook('webBasic_gallery', 'list', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $title = ($row['show_title'] == 1) ? "<h1><a href='{$url}'>{$ln->gfv($row, 'title', '0')}</a></h1>" : '';
            
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], array('url' => $url, 'style' => 'pic'));
            
            $rows .= "
            <li>
                <div class='inner'>
                    {$pic}
                    {$title}
                </div>
            </li>
            ";
        }
        
        $text = "
        <div class='galleryList'>
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
        $hook = getCPModuleHook('webBasic_gallery', 'list', $row);
        if($hook['status']){
            return $hook['html'];
        }

        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');
        
        $text = "
        <div class='cpBackWrapper'>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>
        {$title}
        {$wImagesSlider->getWidget(array(
             'module'    => 'webBasic_content'
            ,'record_id' => $row['content_id']
            ,'width'     => $cpCfg['m.webBasic.gallery.slideshowWidth']
            ,'height'    => $cpCfg['m.webBasic.gallery.slideshowHeight']
        ))}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getPictureOnlyList($dataArray) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row){
            $exp = array('style' => 'pic', 'zoomImage' => 1, 'folder' => 'thumb');

            $rows .= "
            {$media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp)}
            ";
        }
        
        $text = "
        <div class='pictureGrid'>
            {$rows}
        </div>
        ";
        
        return $text;
    }
}
