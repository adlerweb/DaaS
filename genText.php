<?php
    $width = 999;
    $height = 999;
    $text = $_GET['text'];
    $fontsize = $_GET['size'];
    $font = $_GET['font'];
    $r = $_GET['r'];
    $g = $_GET['g'];
    $b = $_GET['b'];

$fonts = array(
	'/usr/share/fonts/Abel-Regular.ttf',
	'/usr/share/fonts/BebasNeue-Regular.ttf',
	'/usr/share/fonts/typewr.ttf'
);

    $fontfile = $fonts[0];
    if(isset($fonts[$font])) $fontfile = $fonts[$font];

    $textbox = imageftbbox($fontsize, 0, $fontfile, $text);
    $img = imagecreate(abs($textbox[0])+abs($textbox[4]), abs($textbox[1])+abs($textbox[5]));

    // Transparent background
    $black = imagecolorallocate($img, 0, 0, 0);
    imagecolortransparent($img, $black);

    $color = imagecolorallocate($img, $r, $g, $b);
    imagettftext($img, $fontsize, 0, 0, 0 + $fontsize, $color, $fontfile, $text);

    header('Content-type: image/png');
    imagepng($img);
    imagedestroy($img);
?>
