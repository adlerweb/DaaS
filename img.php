<?php 
    $pixel_per_mm = 11.8110236*2;

    //Based on https://gist.github.com/lordastley/1342627/16aa3b0fee58c0d3f5eee55fa5a27dfcfe26bd36
    $img = imagecreatefromstring(file_get_contents($_FILES['image']['tmp_name']));
    imagefilter($img, IMG_FILTER_GRAYSCALE); 

    $width = imagesx($img);
    $height = imagesy($img);

    if($width >= $_POST['width'] * $pixel_per_mm || $height >= $_POST['height'] * $pixel_per_mm) {
        //Image is larger than canvas - resizing
        $mw = ($_POST['width']  * $pixel_per_mm) / $width;
        $mh = ($_POST['height'] * $pixel_per_mm) / $height;
        $m=min($mw, $mh);
        if($m < 1) {
            $nimg = imagecreatetruecolor(floor($width*$m), floor($height*$m));
            imagecopyresized($nimg, $img, 0, 0, 0, 0, floor($width*$m), floor($height*$m), $width, $height);
            $img = $nimg;
            $width = imagesx($img);
            $height = imagesy($img);
        }
    }

    // Parse image (can be combined with dither stage, but combining them is slower.)
    for($x=0; $x < $width; $x++){
        for($y=0; $y < $height; $y++){
            $img_arr[$x][$y] = imagecolorat($img, $x, $y);
        }
    }

    // make a b/w output image.
    $output = imagecreate($width, $height);
    $black = imagecolorallocate($output, 0, 0, 0);
    $white = imagecolorallocate($output, 0xff, 0xff, 0xff);

    // Dither image with Atkinson dither

    /* Atkinson Error Diffusion Kernel:

    1/8 is 1/8 * quantization error.

    +-------+-------+-------+-------+
    |       | Curr. |  1/8  |  1/8  |
    +-------|-------|-------|-------|
    |  1/8  |  1/8  |  1/8  |       |
    +-------|-------|-------|-------|
    |       |  1/8  |       |       |
    +-------+-------+-------+-------+

    */

    for($y=0; $y < $height; $y++){
        for($x=0; $x < $width; $x++){
            $old = $img_arr[$x][$y];
            if($old > 0xffffff*.5){ // This is the b/w threshold. Currently @ halfway between white and black.
                $new = 0xffffff;
                imagesetpixel($output, $x, $y, $white); // Only setting white pixels, because the image is already black.
            }else{
                $new = 0x000000;
            }
            $quant_error = $old-$new;
            $error_diffusion = (1/8)*$quant_error; //I can do this because this dither uses 1 value for the applied error diffusion.
            //dithering here.
            $img_arr[$x+1][$y] += $error_diffusion;
            $img_arr[$x+2][$y] += $error_diffusion;
            $img_arr[$x-1][$y+1] += $error_diffusion;
            $img_arr[$x][$y+1] += $error_diffusion;
            $img_arr[$x+1][$y+1] += $error_diffusion;
            $img_arr[$x][$y+2] += $error_diffusion;
        }
    }

    // plop out a png of the dithered image in base64
    ob_start();
    imagepng($output, NULL, 9);
    $image_data = ob_get_contents();
    ob_end_clean();
    echo base64_encode($image_data);
?>
