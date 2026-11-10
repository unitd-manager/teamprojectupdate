<?
class CP_Www_Themes_Exchange_View extends CP_Www_Lib_ThemeViewAbstract
{
    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$ln->gd('cp.footer.leftText')}
            </div>
            <div class='float_right'>
                {$ln->gd('cp.footer.rightText')}
                <img src='/www/images/footer_logo.jpg'>                
            </div>
            
        </div>
        ";

        return $text;
    }
    
}