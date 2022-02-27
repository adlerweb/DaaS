<?php
    $pixel_per_mm = 11.8110236 * 2;
    $printer = 'DYMO_LabelMANAGER_PC_II';

    if(!isset($_POST['data'])) die('DATA?');
    $data = json_decode($_POST['data']);
    
    //generate canvas
    $img = imagecreatetruecolor($data->width * $pixel_per_mm, $data->height * $pixel_per_mm);

    //white background
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);
    
    foreach($data->items as $i) {
        $addimg = imagecreatefrompng($i->src);
        imagecopyresized($img, $addimg, imagesx($img) * $i->ix, imagesy($img) * $i->iy, 0, 0, imagesx($img) * $i->tx, imagesy($img) * $i->ty, imagesx($addimg), imagesy($addimg));
        imagedestroy($addimg);
    }

    imagepng($img, '/tmp/dymo.png');
    imagedestroy($img);

    $cmd = 'lpr -P '.escapeshellarg($printer).' -o media='.escapeshellarg('Custom.'.ceil($data->width).'x'.$data->height.'mm').' -o orientation-requested=4 -o fit-to-page /tmp/dymo.png 2>&1';
    echo($cmd."\n");
    exec($cmd, $out, $ret);
    echo 'Status: '.$ret."\n";
    echo 'Output:'."\n".implode("\n", $out);
?>