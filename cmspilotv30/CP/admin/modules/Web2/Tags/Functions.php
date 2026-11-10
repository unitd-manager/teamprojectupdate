<?
class CP_Admin_Modules_Web2_Tags_Functions extends CP_Common_Modules_Web2_Tags_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('web2_tags');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }

    /**
     *
     * @return <type>
     */
    function getTagsGroupArray(){
        $arr=
        array(
        );

        return $arr;
    }

}