<?
class ImageResize
{
   function imageCreateThumb($src, $dest, $maxWidth, $maxHeight, $quality=100, $exp = array()) {
        if (file_exists($src) && isset($dest)) {
            $this->smart_resize_image($src, $maxWidth, $maxHeight, true, $dest, false, false, $exp);
            return true;
        } else {
            return false;
        }
    }

    function smart_resize_image($file, $maxWidth, $maxHeight, $proportional = true, $output = 'file',  
                                $delete_original = true,  $use_linux_commands = false, $exp = array()){
        
        if ($maxWidth <= 0 && $maxHeight <= 0) return false;
        
        $info = getimagesize($file);
        $imageSizeX = $info[0];
        $imageSizeY = $info[1];
        $imageType  = $info[2];
        
        $thumbSizeX = $maxWidth;
        $thumbSizeY = $maxHeight;
        
        if( $imageSizeX == ''){
          return;
        }
        
        $hasWatermark      = isset($exp['hasWatermark'])      ? $exp['hasWatermark']      : false;
        $watermarkText     = isset($exp['watermarkText'])     ? $exp['watermarkText']     : false;
        $watermarkFontSize = isset($exp['watermarkFontSize']) ? $exp['watermarkFontSize'] : false;
        
        $scale = min($maxWidth/$imageSizeX, $maxHeight/$imageSizeY); 

        # If the image is larger than the max, shrink it 
        if ($scale < 1) { 

            $final_width = floor($scale*$imageSizeX); 
            $final_height = floor($scale*$imageSizeY); 

            # Loading image to memory according to type
            switch ($info[2] ) {
              case IMAGETYPE_GIF:   $source_image = imagecreatefromgif($file);   break;
              case IMAGETYPE_JPEG:  $source_image = imagecreatefromjpeg($file);  break;
              case IMAGETYPE_PNG:   $source_image = imagecreatefrompng($file);   break;
              default: return false;
            }

            # This is the resizing/resampling/transparency-preserving magic
            $image_resized = imagecreatetruecolor( $final_width, $final_height );
            if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
              $transparency = imagecolortransparent($source_image);
            
              if ($transparency >= 0) {
                $transparent_color  = imagecolorsforindex($source_image, $trnprt_indx);
                $transparency       = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                imagefill($image_resized, 0, 0, $transparency);
                imagecolortransparent($image_resized, $transparency);
              }
              elseif ($info[2] == IMAGETYPE_PNG) {
                imagealphablending($image_resized, false);
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                imagefill($image_resized, 0, 0, $color);
                imagesavealpha($image_resized, true);
              }
            }
            imagecopyresampled($image_resized, $source_image, 0, 0, 0, 0, $final_width, $final_height, $imageSizeX, $imageSizeY);
            
            # Taking care of original, if needed
            if ( $delete_original ) {
              if ( $use_linux_commands ) exec('rm ' . $file);
              else @unlink($file);
            }
            
            # Preparing a method of providing result
            switch ( strtolower($output) ) {
              case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
              break;
              case 'file':
                $output = $file;
              break;
              case 'return':
                return $image_resized;
              break;
              default:
                $output = $output;
              break;
            }

            # Writing image according to type to the output destination
            switch ( $info[2] ) {
              case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
              case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, 100);   break;
              case IMAGETYPE_PNG:   imagepng($image_resized, $output);    break;
              default: return false;
            }
        } else {
            copy($file, $output);
        }
        
        //watermarking
        if ($hasWatermark){
            $this->watermarkImage($output, $watermarkText, $output, $watermarkFontSize);
        }
        
        return true;
    }

    function watermarkImage($SourceFile, $WaterMarkText, $DestinationFile, $watermarkFontSize = '15') {
        //$SourceFile is source of the image file to be watermarked
        //$WaterMarkText is the text of the watermark
        //$DestinationFile is the destination location where the watermarked images will be placed
        //Delete if destinaton file already exists
        //@unlink($DestinationFile);

        //Path to the font file on the server. Do not miss to upload the font file
        $font = CP_LIBRARY_PATH . 'fonts/Arial/arial.ttf';
        $font_size = $watermarkFontSize;
        $dimensions = imagettfbbox($font_size, 0, $font, $WaterMarkText);
        $fontWidth = $dimensions[2];

        //This is the vertical center of the image
        list($width, $height) = getimagesize($SourceFile);
        $top = $height / 2;
        $left = ($width / 2) - ($fontWidth / 2);


        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($SourceFile);

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        //Give a white shadow
        $white = imagecolorallocatealpha($image_p, 255, 255, 255, 50);
        imagettftext($image_p, $font_size, 0, $left, $top, $white, $font, $WaterMarkText);

        //Print in black color
        $black = imagecolorallocatealpha($image_p, 0, 0, 0, 50);
        imagettftext($image_p, $font_size, 0, $left-2, $top - 1, $black, $font, $WaterMarkText);

        if ($DestinationFile <> '') {
            imagejpeg($image_p, $DestinationFile, 80);
        } else {
            //header('Content-Type: image/jpeg');
            imagejpeg($image_p, null, 100);
        };

        imagedestroy($image);
        imagedestroy($image_p);
    }
}