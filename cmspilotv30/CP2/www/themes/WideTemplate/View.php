<?
class CP_Www_Themes_WideTemplate_View extends CP_Www_Lib_ThemeViewAbstract
{
    
    /*
     *
     */
    function getExtendedPanel() {
    }
    
    /**
     *
     */
    function getRightPanel(){
        $tv = Zend_Registry::get('tv');
        $subNav = Zend_Registry::get('subNav');
        $fn = Zend_Registry::get('fn');

        $clsName = ucfirst($tv['module']);
        $modObj  = includeCPClass('Module', $tv['module'], $clsName);

        if (method_exists($modObj, 'getRightPanel')) {
            $text = $modObj->getRightPanel();
        } else {
            $text = "
            <h1 class='vlist'>{$tv['secTitle']}</h1>
            {$subNav->getWidget()}
            ";
        }

        return $text;
    }
    
    
  
}