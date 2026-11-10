<?
class CP_Admin_Modules_Trading_CurrencyRate_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_currencyRate');
        $modules->registerModule($modObj, array(
            'tableName' => 'currency_rate'
           ,'keyField' => 'currency_rate_id'
           ,'title' => 'Currency Rate'
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
        ));
    }

    function getQuickSearch() {
    }

    /**
     *
     */
    function setMediaArray($inst) {
    }
    
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "cr.currency_rate_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                       cr.currency_from LIKE '%{$tv['keyword']}%'
                    OR cr.currency_to LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function setLinksArray($inst) {
    }

}