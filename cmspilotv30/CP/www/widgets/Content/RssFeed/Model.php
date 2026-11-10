<?
class CP_Www_Widgets_Content_RssFeed_Model extends CP_Common_Lib_WidgetModelAbstract
{
 
    //========================================================//
    function getDataArray() {
        $c = $this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        require_once 'Zend/Feed.php';
        
        $dataArray = array();
        if($c->feedUrl != ''){
            try {
                $rssChannel = Zend_Feed::import($c->feedUrl);
            } catch (Zend_Feed_Exception $e) {
                // feed import failed
                echo "Exception caught importing feed: {$e->getMessage()}\n";
                exit;
            }       

            $counter = 0;
            foreach ($rssChannel as $row) {
                $arrTemp = &$dataArray[$counter];
                $arrTemp['title'] = $row->title();
                $arrTemp['url'] = $row->link();
                $arrTemp['external_link'] = $row->link();
                $arrTemp['description']   = $row->content();
                $arrTemp['content_date']  = $row->pubDate();
                $counter++;      
            }
        }
        return $dataArray;
    }
}
