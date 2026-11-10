<?
class CP_Www_Modules_WebBasic_Home_View extends CP_Common_Lib_ModuleViewAbstract
{
    /*
     *
     */
    function getList($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $hook = getCPModuleHook('webBasic_home', 'list', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        /** create an instance of the widget **/
        $wSlideshow = getCPWidgetObj('media_anythingSlider');
        $slideshow = $wSlideshow->getWidget(array(
        ));

        $content = '';
        if (!$cpCfg['cp.fullWidthTemplte']) {
            $content = $this->getContent($dataArray);
        }

        $text = "
        {$slideshow}
        {$content}
        ";

        return $text;
    }

    /*
     *
     */
    function getContent($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('webBasic_home', 'content', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $title = '';
        $desc = '';
        foreach ($dataArray as $row){
            $title = ($row['show_title'] == 1) ? "<h1>{$ln->gfv($row, 'title')}</h1>" : '';
            $desc = $ln->gfv($row, 'description');
        }

        /** create an instance of the widget **/
        $wRecord = getCPWidgetObj('content_record');

        $text = "
        <div class='content'>
            <div class='subcolumns'>
                <div class='c66l'>
                    <div class='subcl'>
                        {$title}
                        {$desc}
                    </div>
                </div>
                <div class='c33r'>
                    <div class='subcr'>
                    {$wRecord->getWidget(array(
                         'contentType' => 'Record'
                        ,'helperFn' => 'getWidgetLatestNews'
                    ))}
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /*
     *
     */
    function getExtendedPanel($dataArray) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $hook = getCPModuleHook('webBasic_home', 'extendedPanel', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $content = $this->getContent($dataArray);

        return $content;
    }

    /*
     *
     */
    function getDetail($row) {
        return $this->getList($row);
    }
}
