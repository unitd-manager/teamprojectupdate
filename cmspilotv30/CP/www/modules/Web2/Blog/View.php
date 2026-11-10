<?
class CP_Www_Modules_Web2_Blog_View extends CP_Common_Lib_ModuleViewAbstract
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
                    <div class='date'>
                        <span class='posted'>{$ln->gd('cp.lbl.postedOn')}</span>
                        {$fn->getCPDate($row['content_date'])}
                    </div>
                    {$fn->getShortDescription($row)}
                    <a class='commentCount' href='{$url}#commentsList'>{$ln->gd('cp.form.fld.comments.lbl')}[{$row['comments_count']}]</a>
                    &nbsp;&nbsp;
                    <a class='readMore' href='{$url}'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
            </div>
            ";
        }
        
        $text = "
		<div class='pager'> 
          	{$theme->view->getPagerPanel()}
		</div> 		        
		<div class='blogList fatList'>
           	{$rows}
        </div>
        ";
        
        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        
        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title', '0')}</h1>" : '';
        $exp = array('style' => 'mb5', 'zoomImage' => true);
        $pic = $media->getMediaPicture('webBasic_content', 'picture', $row['content_id'], $exp);
        
        $text = "
        <div class='cpBackWrap'>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>
        <div class='pic'>
            {$pic}
        </div>
        <div class='content'>
            {$title}
            <div class='date'>
                <span class='posted'>{$ln->gd('cp.lbl.postedOn')}</span>
                {$fn->getCPDate($row['content_date'])}
            </div>
            {$ln->gfv($row, 'description', '0')}
        </div>
        {$comment->getView(array(
             'roomName' => 'webBasic_content'
            ,'recordId' => $row['content_id']
            ,'contactModule' => ''
        ))}
        ";
        
        return $text;
    }
}
