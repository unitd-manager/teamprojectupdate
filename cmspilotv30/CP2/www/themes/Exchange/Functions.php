<?
class CP_Www_Themes_Exchange_Functions
{
    /*
     * 
     */
    function getModuleWebBasicHomeListHook($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $wBanner = getCPWidgetObj('media_banner');

        $wRecord = getCPWidgetObj('content_record');
        $homeRecord = $wRecord->getWidget(array(
            'sectionType' => 'Home'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $bankTransfer = $wRecord->getWidget(array(
            'contentType' => 'Bank Transfer'
           ,'showDesc' => 'true'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $corresBank = $wRecord->getWidget(array(
            'contentType' => 'Corresponding Bank'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $latestNews = $wRecord->getWidget(array(
            'contentType'  => 'Record'
           ,'addSearchCond'=> 'AND latest = 1'
        ));

        $text = "
        {$wBanner->getWidget()}
        <div class='subcolumns'>
            <div class='homeContent'>
                <div class='c60l'>
                    <div class='subcl'>
                        {$homeRecord}
                        {$bankTransfer}
                        {$corresBank}
                        <img src='/www/images/cust_care.png'>
                        <img src='/www/images/rss.png'>
                    </div>
                </div>
                <div class='c40r '>
                    <div class='subcr'> 
                        <div class='currency mb20'>
                            {$this->getCurrency()}
                        </div>                        
                        <div class='latestNews mb20'> 
                            <blockquote>             
                                {$latestNews}
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getCurrency() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';

        $SQL    = "SELECT * 
        FROM currency
        WHERE published = 1
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)){
                        
            $rows .= "
            <tr>
                <td>{$row['currency_name']}</td>
                <td class='txtCenter'>{$row['we_buy']}</td>
                <td class='txtCenter'>{$row['we_sell']}</td>
            </tr>
            ";
        }

        $text = "
        <table class='thinList' id='bodyList'>
            <tr>
                <th class='col1'>Currency</th>
                <th class='col2'>We Buy</th>
                <th class='col3'>We Sell</th>
            </tr>
            {$rows}
        </table>        
        ";

        return $text;
    }
}