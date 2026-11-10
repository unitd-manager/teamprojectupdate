<?
class CP_Www_Themes_MegaNav_View extends CP_Www_Lib_ThemeViewAbstract
{
    var $jssKeys = array('jqColorbox-1.3.15');

    /**
     *
     */
    function getNavPanel(){
        $cpCfg = Zend_Registry::get('cpCfg');

		$text = '';
	    $mainNav = Zend_Registry::get('mainNav');
        $widget = "{$mainNav->getWidget(array(
        ))}
        ";

		$text = "
        <div id='nav'>
            <a id='navigation' name='navigation'></a>
            {$widget}
        </div>
		";
        return $text;
    }
    
    /**
     *
     */
    function getHeaderPanel(){
        $tv = Zend_Registry::get('tv');
        /** create an instance of the widget **/
        
        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getFooterPanel(){
        $ln = Zend_Registry::get('ln');
        $text = "
        ";

        return $text;
    }
}