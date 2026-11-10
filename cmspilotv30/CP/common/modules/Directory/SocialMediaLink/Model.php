<?
class CP_Common_Modules_Directory_SocialMediaLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'social_media_id');
        $fa = $fn->addToFieldsArray($fa, 'url');

        return $fa;
    }

    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $srcRoom = $fn->getReqParam('srcRoom');

        $fa = $this->getFields();

        $tableName = '';
        if ($tv['module'] == 'directory_business' || $srcRoom == 'directory_business') {
            $fa['business_id'] = $tv['srcRoomId'];
            $tableName = 'business_social_media';

        } else if ($tv['module'] == 'directory_businessGroup' || $srcRoom == 'directory_businessGroup') {
            $fa['business_group_id'] = $tv['srcRoomId'];
            $tableName = 'bg_social_media';
        }

        $id = $fn->addRecord($fa, $tableName);
    }

    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}