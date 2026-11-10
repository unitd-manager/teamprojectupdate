<?
class CP_Www_Modules_Forex_RateBoardAcc_View extends CP_Common_Lib_ModuleViewAbstract
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

        $last_updated = date('Y-m-d H:i:s');


        $buy_rate_fld = 'exch_rate_buy';
        $sell_rate_fld = 'exch_rate_sell';
        if ($fn->getSettingsValueByKey('m.account.currencyConvert.rateboardShowEveningRate') == 1) {
            $buy_rate_fld .= '_evening';
            $sell_rate_fld .= '_evening';
        }
        foreach ($dataArray as $row){
            $exp = array('folder' => 'thumb');

            $weBuy = round($fn->getTrimmedDecimals($row['exch_rate_buy']), 4);
            $weSell = round($fn->getTrimmedDecimals($row['exch_rate_sell']), 4);

            $weBuy = $row[$buy_rate_fld];
            $weSell = $row[$sell_rate_fld];

            $rows .= "
            <div class='floatbox row'>
                <div class='float_left pic'>
                    {$media->getMediaPicture('account_currency', 'picture', $row['currency_id'], $exp)}
                </div>
                <div class='float_left country'>
                    {$row['country']}
                </div>
                <div class='float_left currency'>
                    {$row['currency_code']}
                </div>
                <div class='float_left weBuy'>
                    {$weBuy}
                </div>
                <div class='float_left weSell'>
                    {$weSell}
                </div>
            </div>
            ";
        }


        /*
        $wSlideshow= getCPWidgetObj('media_nivoSlider');
        $slideshow = $wSlideshow->getWidget(array(
        ));
        */

        $text = "
        <div class='message'>
            {$ln->gd('m.forex.rateboardAcc.rateBoardTopMessage')} <span class='lastUpdated'>{$last_updated}</span>
        </div>
        <div class='RateBoardAcc'>
            <div class='floatbox heading'>
                <div class='float_left pic'>
                </div>
                <div class='float_left country'>
                    {$ln->gd('cp.lbl.currency')}
                </div>
                <div class='float_left currency'>
                </div>
                <div class='float_left weBuy'>
                    {$ln->gd('m.forex.rateboardAcc.weBuy')}
                </div>
                <div class='float_left weSell'>
                    {$ln->gd('m.forex.rateboardAcc.weSell')}
                </div>
            </div>
            {$rows}
        </div>
        ";

        return $text;
    }

}
