<?
class CP_Common_Lib_MediaArray
{

    var $mediaArray = array();

    //==================================================================//
    function __construct() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $this->mediaArray["tempFolder"] = "{$cpCfg['cp.mediaFolder']}temp/";

        /** in addMedia script the module name is passed in room variable **/
        $module = ($tv['spAction'] == '') ? $tv['module'] : $fn->getReqParam('room');

        $this->setMediaArray($module);
    }

    //==================================================================//
    function registerMedia($mediaObj, $overrideArr = array()) {
        foreach($overrideArr as $key => $value){
            $mediaObj[$key] = $value;
        }
        $this->mediaArray[$mediaObj['room']][$mediaObj['recordType']] = $mediaObj;
    }

    //==================================================================//
    function setMediaArray($module) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($module != ''){
            $fnMod = includeCPClass('ModuleFns', $module);

            $funcName = "setMediaArray";
            if (method_exists($fnMod, $funcName)) {
                $fnMod->$funcName($this);
            }

            CP_Common_Lib_Registry::arrayMerge('mediaArray', $this->mediaArray);
        }
    }

    //==================================================================//
    function getMediaObj($module, $recordType, $mediaType) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mediaFolder = $cpCfg['cp.mediaFolder'];
        $mediaFolderAlias = $cpCfg['cp.mediaFolderAlias'];

        // /** if called from front end remove the first slash **/
        if (substr($cpCfg['cp.mediaFolder'], 0, 1) == '/'){
            $mediaFolder = substr($cpCfg['cp.mediaFolder'], 1);
        }

        $arr['room']         = $module;
        $arr['recordType']   = $recordType;
        $arr['mediaType']    = $mediaType;
        $arr['normalFolder'] = "{$mediaFolder}normal/";
        $arr['normalFolderAlias'] = "{$mediaFolderAlias}normal/";
        $arr['count']        = 'multiple'; //single|multiple
        $arr['resize']       = false;
        $arr['openExpanded'] = 1;
        $arr['hasZoomImage'] = true;
        $arr['showFullFileRef'] = false;
        $arr['doNotResizeLargeImage'] = false;
        $arr['isMediaLangSpecific']   = true;
        $arr['showNormalImg'] = true; //show normal image in the right panel

        $arr['thumbFolder']             = '';
        $arr['largeFolder']             = '';
        $arr['croppedFolder']           = '';
        $arr['thumbFolderAlias']        = '';
        $arr['largeFolderAlias']        = '';
        $arr['croppedFolderAlias']      = '';
        $arr['maxWidthT']               = '';
        $arr['maxHeightT']              = '';
        $arr['maxWidthM']               = ''; //medium
        $arr['maxHeightM']              = '';
        $arr['maxWidthN']               = '';
        $arr['maxHeightN']              = '';
        $arr['maxWidthL']               = '';
        $arr['maxHeightL']              = '';
        $arr['showColorDdInEdit']       = false;
        $arr['showProductItemDdInEdit'] = false;
        $arr['hasCrop']                 = false;
        $arr['hasNew']                  = true;
        $arr['hasDelete']               = true;
        $arr['hasSave']                 = true;
        $arr['hasEditProp']             = true;
        $arr['cropInfo']                = array();
        $arr['SQL']                     = ''; // you can override the default fn: media/model->getSQL()
        $arr['hasWatermark']            = false;
        $arr['watermarkText']           = '';
        $arr['watermarkLargeFontSize']  = 20;

        if ($mediaType == 'image'){
            $arr['thumbFolder']  = "{$mediaFolder}thumb/";
            $arr['largeFolder']  = "{$mediaFolder}large/";
            $arr['croppedFolder'] = "{$mediaFolder}cropped/";
            $arr['thumbFolderAlias']  = "{$mediaFolderAlias}thumb/";
            $arr['largeFolderAlias']  = "{$mediaFolderAlias}large/";
            $arr['croppedFolderAlias']= "{$mediaFolderAlias}cropped/";
            $arr['resize'] = true;
            $arr['maxWidthT']    = '125';
            $arr['maxHeightT']   = '125';
            $arr['maxWidthN']    = '350';
            $arr['maxHeightN']   = '450';
            $arr['maxWidthL']    = '1170';
            $arr['maxHeightL']   = '1170';
            $arr['cropInfo']     = $this->getCropInfoObj();

            $arr['fileTypeExts']  = $cpCfg['cp.imageFileTypeExts'];
        } else {
            $arr['fileTypeExts']  = $cpCfg['cp.attachmentFileTypeExts'];
        }
        return $arr;
    }

    function getCropInfoObj($width = 100, $height = 100) {
        global $tv, $cfg;

        $arr = array();
        $arr['width'] = $width;
        $arr['height'] = $height;

        return $arr;
    }
}