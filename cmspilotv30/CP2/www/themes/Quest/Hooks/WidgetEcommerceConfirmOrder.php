<?
class CP_Www_Themes_Quest_Hooks_WidgetEcommerceConfirmOrder
{
    /**
     *
     */
    function getButtons($contObj) {

        $ln = Zend_Registry::get('ln');
        $c = &$contObj;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $shopUrl = $cpUrl->getUrlBySecType($basketArray['sectionType']);
        $shipUrl = $cpUrl->getUrlByCatType('Shipping Details', $basketArray['basketSecType']);
        $confirmUrl = '/index.php?module=ecommerce_basket&_spAction=confirmOrder&showHTML=0';

        $text = "
        <div class='floatbox shopBtns' modName='{$c->modName}'>
            <div class='float_right button'>
                <a href='{$shipUrl}'>
                    {$ln->gd($c->editDetails)}
                </a>
            </div>
            <div class='float_right button btnConfirmOrder'>
                <a href='{$confirmUrl}'>
                    {$ln->gd($c->confirmOrder)}
                </a>
            </div>
            <div class='float_right button'>
                <a href='javascript:void(0)' class='cpPrint'>
                    {$ln->gd('print')}
                </a>
            </div>
        </div>
        ";

        return $text;
    }


}