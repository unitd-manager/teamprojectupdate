<?
class CP_Www_Modules_Gallery_Project_View extends CP_Common_Modules_Gallery_Project_View
{
    /**
     *
     */
    function getList($dataArray) {
        $hook = getCPModuleHook('gallery_project', 'list', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';

        foreach ($dataArray as $row){
            $exp = array('zoomImage' => false, 'folder' => $cpCfg['m.gallery.project.list.picSize']);
            $pic = $media->getMediaPicture('gallery_project', 'picture', $row['project_id'], $exp);
            $url = $cpUrl->getUrlByRecord($row, 'project_id');

            $rows .= "
            <tr>
                <td><a href='{$url}'>{$ln->gfv($row, 'title')}</a></td>
                <td>{$row['location']}</td>
                <td>{$row['project_year']}</td>
                <td></td>
            </tr>
            ";
        }

        $text = "
        <div class='projectList'>
            {$this->getQuickSearch()}
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>{$ln->gd('m.gallery.project.lbl.title')}</th>
                        <th>{$ln->gd('m.gallery.project.lbl.location')}</th>
                        <th>{$ln->gd('m.gallery.project.lbl.date')}</th>
                        <th>{$ln->gd('m.gallery.project.lbl.size')}</th>
                    </tr>
                </thead>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getGridList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        foreach ($dataArray as $row){
            $exp = array('zoomImage' => false, 'folder' => $cpCfg['m.gallery.project.list.picSize']);
            $pic = $media->getMediaPicture('gallery_project', 'picture', $row['project_id'], $exp);
            $url = $cpUrl->getUrlByRecord($row, 'project_id');

            $rows .= "
            <li>
                <div class='inner'>
                    <div class='pic'><a href='{$url}'>{$pic}</a>&nbsp;</div>
                    <div class='title'>{$ln->gfv($row, 'title')}</div>
                    <a href='{$url}'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
            </li>
            ";
        }

        $text = "
        <div class='projectList'>
            {$this->getQuickSearch()}
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
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $hook = getCPModuleHook('gallery_project', 'detail', $row);
        if($hook['status']){
            return $hook['html'];
        }

        $wImagesSlider = getCPWidgetObj('media_imagesSlider');

        $exp = array('style' => 'mb5', 'folder' => 'thumb');
        
        $pic = '';
        if ($cpCfg['m.gallery.project.detail.showPic']){
            $pic = "
            {$wImagesSlider->getWidget(array(
                 'module'    => 'gallery_project'
                ,'record_id' => $row['project_id']
                ,'height'    => 400
                ,'width'     => 350
            ))}
            ";
        }
        
        $text = "
        <div class='subcolumns projectDetail'>
            <div class='c50l'>
                <div class='subcl'>
                    <h3>{$tv['catTitle']}</h3>
                    <h1>{$ln->gfv($row, 'title')}</h1>
                    <div class='desc'>
                        {$ln->gfv($row, 'description')}
                    </div>
                </div>
            </div>
            <div class='c50r'>
                <div class='subcr'>
                    <div class='float_right'>
                        <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
                    </div>
                    {$pic}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $location = $fn->getReqParam('location');
        $project_year = $fn->getReqParam('project_year');
        
        $SQLLocation = "
        SELECT DISTINCT location
        FROM project
        ORDER BY location
        ";
        $locOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLLocation, $location);

        $SQLYear= "
        SELECT DISTINCT project_year
        FROM project
        ORDER BY project_year
        ";
        $yearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $project_year);
        
        $formAction = CP_REQUEST_URI;
        
        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
        <div class='quickSearch'>
            <div>
                <select name='location'>
                    <option value=''>{$ln->gd('location')}</option>
                    {$locOptions}
                </select>
            </div>
            <div>
                <select name='project_year'>
                    <option value=''>{$ln->gd('year')}</option>
                    {$yearOptions}
                </select>
            </div>
        </div>
        </form>
        ";

        return $text;
    }
}