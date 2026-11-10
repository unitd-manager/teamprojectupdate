<?
class CP_Www_Themes_Soccer_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        foreach ($dataArray as $row){
        }

        $wRecord = getCPWidgetObj('content_record');

        $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
        $text = "
        <div class='subcolumns mt20'>
            <div class='c66l'>
                <div class='subcl'>
                    {$title}
                    {$ln->gfv($row, 'description')}
                </div>
            </div>
            <div class='c33r'>
                <div class='subcr'>
                {$wRecord->getWidget(array(
                    'helperFn' => 'getWidgetLatestNews'
                ))}
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}