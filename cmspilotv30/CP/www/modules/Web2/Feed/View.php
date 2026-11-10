<?
class CP_Www_Modules_Web2_Feed_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $rows = '';
        foreach ($dataArray as $row){
            $url = $cpUrl->getUrlByRecord($row, 'feed_id', array('useTopVars' => true));

            $date = '';
            if ($row['content_date'] != ''){
                $date = "
                <div class='date'>
                    {$fn->getCPDate($row['content_date'])}
                </div>
                ";
            }

            $rows .= "
            <div class='row floatbox'>
                <div class='content'>
                    <h2><a href='{$url}'>{$row['title']}</a></h2>
                    {$date}
                    {$cpUtil->getSubString($row['description'], 250)}...
                    <a class='readMore' href='{$url}'>{$ln->gd('cp.lbl.readMore')}</a>
                </div>
            </div>
            ";
        }

        $text = "
		<div class='fatList feedList'>
            <div class='floatbox'>
                <div class='float_left'>
                    <h1>{$fn->getPageTitle()}</h1>
                </div>
                <div class='float_right'>
            		<div class='pager'> 
                      	{$theme->view->getPagerPanel()}
            		</div> 		        
                </div>
            </div>
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

        $date = '';
        if ($row['content_date'] != ''){
            $date = "
            <div class='date'>
                {$fn->getCPDate($row['content_date'])}
            </div>
            ";
        }

        $text = "
        <div class='cpBackWrap'>
            <a href='javascript:void(0)' class='cpBack'>{$ln->gd('cp.lbl.back')}</a>
        </div>
        <h2>{$row['title']}</h2>
        {$date}
        {$row['description']}
        ";

        return $text;
    }
}