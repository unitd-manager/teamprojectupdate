<?
class CP_Www_Modules_WebBasic_Section_Model extends CP_Common_Modules_WebBasic_Section_Model
{

    function getSectionIdAliasedTo() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $roomsArray = Zend_Registry::get('roomsArray');
        
        $alias_to_section_id = '';
        if($cpCfg['cp.hasAliasSectionsAcrossMUSites']){
            if ($tv['room'] != '' && is_numeric($tv['room'])){
                $roomArr = $roomsArray[$tv['room']];
                $alias_to_section_id = $roomArr['alias_to_section_id'];
            }
        }
        
        return $alias_to_section_id;
    }
}