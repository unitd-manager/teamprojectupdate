<?
class CP_Www_Modules_WebBasic_News_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $rows = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $title = ($row['show_title'] == 1) ? "<h1><a href='{$url}'>{$ln->gfv($row, 'title', '0')}</a></h1>" : '';
        
            $exp = array('folder' =>'normal', 'url' => $url);
            
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);

            $class = '';
            if ($pic != ''){
                $pic = "
                <div class='pic'>
                    {$pic}
                </div>
                ";
                
                $class = ' hasPic';
            }
            
            $rows .= "
            <div class='row floatbox{$class}'>
                {$pic}
                <div class='content'>
                    {$title}
                    {$fn->getShortDescription($row)}
                    <a class='readMore' href='{$url}'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
            </div>
            ";
        }
        
        $text = "
		<div class='pager'> 
          	{$theme->view->getPagerPanel()}
		</div> 		        
        {$this->getQuickSearch()}
		<div class='newsList'>
           	{$rows}
        </div>
        ";
        
        return $text;
    }

    function getListInDetail($dataArray) {
        return getCPModuleObj('webBasic_content')->view->getListInDetail($dataArray);
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
        $exp = array('style' => 'mb5', 'zoomImage' => true);
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
        
        if ($pic != ''){
            $pic = "<div class='float_right'>{$pic}</div>";
        }
        
        $text = "
        <div class='cpBackWrap'>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>
        {$pic}
        {$title}
        {$ln->gfv($row, 'description', '0')}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getFatList($dataArray) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $rows = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
        
            $exp = array('style' => 'mb5', 'folder' =>'large');
            
            $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
            if ($pic != ''){
                $pic = "<div class='float_right'>{$pic}</div>";
            }
            
            $desc = ($ln->gfv($row, 'description_short') != '') ? "<p>{$ln->gfv($row, 'description_short')}</p>" : $ln->gfv($row, 'description');

            $rows .= "
            <div class='subcolumns'>
                <div class='c66l'>
                    <div class='subcl '>
                        {$pic}
                    </div>
                </div>
                <div class='c33r'>
                    <div class='subcr'>
                        {$title}
                        {$desc}
                        <a href='{$url}'>{$ln->gd('cp.lbl.readMore')}</a>
                    </div>
                </div>
            </div>
            ";
        }
        
        $text = "
        <div class='newsFatList'>
			<div class='pager'> 
              	{$theme->view->getPagerPanel()}
			</div> 		
           	{$rows}
        </div>
        ";
        
        return $text;
    }

    /**
     *
     */
    function getFatDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
        $url = $cpUrl->getUrlByRecord($row, 'content_id');
    
        $exp = array('style' => 'mb5', 'folder' =>'large');
        
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
        if ($pic != ''){
            $pic = "<div class='float_right'>{$pic}</div>";
        }
        
        $text = "
        <div class='newsFatList'>
        <div class='subcolumns'>
            <div class='c66l'>
                <div class='subcl '>
                    {$pic}
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr'>
                    <a class='cpBack' href='javascript:void(0);'>{$ln->gd('cp.lbl.back')}</a>
                    {$title}
                    {$ln->gfv($row, 'description', '0')}
                </div>
            </div>
        </div>
        </div>
        ";

        return $text;
    }

    function getQuickSearch() {
        $hook = getCPModuleHook2('webBasic_news', 'quickSearch', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $yearMonth = $fn->getReqParam('yearMonth');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(content_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(content_date, '%b %Y') AS monthYear
        FROM content
        WHERE DATE_FORMAT(content_date, '%b %Y') IS NOT NULL
          AND published = 1
          AND section_id = {$tv['room']}
        ORDER BY yearMonthStart DESC
        ";
        $yearMonthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonth);
        
        $formAction = CP_REQUEST_URI;
        
        $text = "
        <form action='{$formAction}' method='get' id='quickSearch' autoSubmitOnChange='1'>
        <div class='quickSearch'>
            <div class='yearMonth'>
                <select name='yearMonth'>
                    <option value=''>{$ln->gd('m.webBasic.news.lbl.searchByMonth')}</option>
                    {$yearMonthOptions}
                </select>
            </div>
        </div>
        </form>
        ";

        return $text;
    }
}
