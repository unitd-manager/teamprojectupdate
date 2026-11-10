<?

/**
 *
 */
class CP_Common_Lib_Registry extends Zend_Registry
{
    public static function arrayMerge($key, $arr){
        $regArr = self::get($key);
        $regArr = array_merge($regArr, $arr);
        $regArr = self::set($key, $regArr);
    }
}
