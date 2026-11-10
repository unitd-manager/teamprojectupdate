<?
class CP_Www_Themes_Dealon_Controller extends CP_Www_Lib_ThemeControllerAbstract
{

    function getPopupForm(){
        return $this->fns->getPopupForm();
    }

    /*
     * this will be called at the end of the tempate rendering
     * in www/lib/ThemeViewAbstract->getMainThemeOutput()
     */
    function init() {
        $media = Zend_Registry::get('media');

        $wContentRec = getCPWidgetObj('content_record');
        $contentArr = $wContentRec->getWidget(array(
             'contentType' => 'Site Bg'
            ,'returnDataOnly' => true
        ));

        if (count($contentArr) > 0){
            $content_id = $contentArr[0]['content_id'];
            $arr = $media->getFirstMediaRecord('webBasic_content', 'attachment', $content_id);
            $file_normal = $arr['file_normal'];
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                    $('body.cpFullWidth').css('background', 'transparent url({$file_normal}) repeat 0 0');
            "));
        }
    }

    function getPopupFormSubmit(){
        return $this->fns->getPopupFormSubmit();
    }

}