<?
class CP_Www_Modules_Forex_RateBoard_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {

        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';

        $last_updated = '';

        foreach ($dataArray as $row){
            $exp = array('folder' => 'thumb');

            $weBuy = round($fn->getTrimmedDecimals($row['we_buy']), 4);
            $weSell = round($fn->getTrimmedDecimals($row['we_sell']), 4);
            
            $weBuy = $row['we_buy'];
            $weSell = $row['we_sell'];

            $rows .= "
            <div class='floatbox row'>
                <div class='float_left pic'>
                    {$media->getMediaPicture('forex_currency', 'picture', $row['currency_id'], $exp)}
                </div>
                <div class='float_left country'>
                    {$row['country']}
                </div>
                <div class='float_left currency'>
                    {$row['currency_name']}
                </div>
                <div class='float_left weBuy'>
                    {$weBuy}
                </div>
                <div class='float_left weSell'>
                    {$weSell}
                </div>
            </div>
            ";
            $last_updated = $row['modification_date'];
        }
        
        /*
        $wSlideshow= getCPWidgetObj('media_nivoSlider');
        $slideshow = $wSlideshow->getWidget(array(
        ));
        */

        if ($cpCfg['scrollText'] == 1){
            $message = "
            <marquee behavior='scroll' scrollamount='3'>{$ln->gd('rateBoardTopMessage')}</MARQUEE>
            {$ln->gd('rateBoardMessage')} 
            <span class='lastUpdated'>{$last_updated}</span>
            ";
        } else {
            $message = "{$ln->gd('rateBoardTopMessage')} <span class='lastUpdated'>{$last_updated}</span>";
        }
        
        $text = "
        <div class='message'>
            {$message}
        </div>
        <div class='rateBoard'>
            <div class='floatbox heading'>
                <div class='float_left pic'>
                </div>
                <div class='float_left country'>
                    {$ln->gd('currency')}
                </div>
                <div class='float_left currency'>
                </div>
                <div class='float_left weBuy'>
                    {$ln->gd('weBuy')}
                </div>
                <div class='float_left weSell'>
                    {$ln->gd('weSell')}
                </div>
            </div>
            {$rows}
        </div>
        ";

        return $text;
    }

}
