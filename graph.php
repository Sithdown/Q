<?php header('Content-type: image/png');
require_once "connection.php";
function Query($month=0){
    global $pdo;
    $insert = 'SELECT DAY(datetime) AS monthday, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';
    $ready = $pdo->prepare($insert);
    $result = $ready->execute();
    $result = $ready->fetchAll();
    return $result;
}
function bar( $index, $value, $max ){
    global $im, $color, $height, $width, $margin;
    $rw = $width - $margin*2;
    $rh = $height - $margin*2;
    $w = $rw/31;
    $startx = $margin + $index*$w;
    $starty = $margin + $rh - $value/$max*$rh;
    $endx = $startx + $w;
    $endy = $margin + $rh;
    imagefilledrectangle($im,$startx,$starty,$endx,$endy,$color);
}
function graph($data){
    global $im, $color, $bkg, $margin, $height, $width;
    $im = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($im, 217, 83, 79);
    $bkg = imagecolorallocate($im, 52, 52, 52);
    $txtcolor = imagecolorallocate($im, 60, 60, 60);
    $font = 'helvneue.ttf';
    $text = strtoupper(date("F"));
    $bbox = imagettfbbox(70, 0, $font, $text);
    $x = $bbox[0] + (imagesx($im) / 2) - ($bbox[4] / 2) - 25;
    $y = $bbox[1] + (imagesy($im) / 2) - ($bbox[5] / 2) - 120;
    imagefill($im, 0, 0, $bkg);
    imagettftext($im, 70, 0, $x, $y, $txtcolor, $font, $text);
    foreach ($data as $key => $value) {
        $index = $value["monthday"] -1;
        $val = $value["duration_total"];
        $max = 16*60;
        bar($index,$val,$max);
    }
    imagepng($im);
    imagedestroy($im);
}
$margin = 20;
$height = 480;
$width = 800;
graph(Query());
?>