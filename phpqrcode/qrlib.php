<?php
class QRcode
{
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile);
    }
}

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRencode
{
    public $level;
    public $size;
    public $margin;

    public static function factory($level = QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = new QRencode();
        $enc->level = $level;
        $enc->size = $size;
        $enc->margin = $margin;
        return $enc;
    }

    public function encodePNG($text, $outfile = false)
    {
        $image = imagecreate(100, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 100, 100, $white);
        imagestring($image, 5, 10, 40, $text, $black);
        if ($outfile) {
            imagepng($image, $outfile);
        } else {
            header('Content-Type: image/png');
            imagepng($image);
        }
        imagedestroy($image);
    }
}
?>