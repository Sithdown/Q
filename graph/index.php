<?php header('Content-type: image/png');
require_once "../connection.php";
function Query($month=0){
    global $pdo;
    $userid=1;
    if(isset($_GET["userid"])){
        $userid=$_GET["userid"];
    }
    if(isset($_GET["user"])){
        $userid=getID($_GET["user"]);
    }
    $insert = 'SELECT DAY(datetime) AS monthday, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) AND userid='.$userid.' GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';
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
    if(($index+1)%5==0){
        $font = 'helvneue.ttf';
        $text = strtoupper($index+1);
        $tw = $w/2;
        $bbox = imagettfbbox($tw, 0, $font, $text);
        $x = $startx + $w/2 - $tw*strlen($text)/2.5;
        $y = $bbox[1] + $endy - 2;
        $txtcolor = imagecolorallocate($im, 236, 169, 167);
        imagettftext($im, $tw, 0, $x, $y, $txtcolor, $font, $text);
    }
}

function hints($max,$every=2){
    global $im, $color, $height, $width, $margin, $txtcolor;
    $total = $max/60;
    $advance = ($height - $margin*2) / $total;
    for ($i=0; $i < $total; $i+=$every) {
        $x1 = $margin;
        $x2 = $width - $margin;
        $y1 = $margin + $i*$advance;
        $y2 = $y1;
        imageline($im, $x1, $y1, $x2, $y2, $txtcolor);
    }
}

function graph($data){
    global $im, $color, $bkg, $margin, $height, $width, $txtcolor;
    $im = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($im, 217, 83, 79);
    $bkg = imagecolorallocate($im, 52, 52, 52);
    $txtcolor = imagecolorallocate($im, 60, 60, 60);
    $font = 'helvneue.ttf';
    $text = strtoupper(date("F"));
    $tw = $width * 0.0875;
    $bbox = imagettfbbox($tw, 0, $font, $text);
    $x = $bbox[0] + (imagesx($im) / 2) - ($bbox[4] / 2) - 25;
    $y = $bbox[1] + (imagesy($im) / 2) - ($bbox[5] / 2) - 120;
    imagefill($im, 0, 0, $bkg);
    
    $max = 16*60;
    $every = 2;

    hints($max);

    imagettftext($im, $tw, 0, $x, $y, $txtcolor, $font, $text);
    foreach ($data as $key => $value) {
        $index = $value["monthday"] -1;
        $val = $value["duration_total"];
        bar($index,$val,$max);
    }
    imagepng($im);
    imagedestroy($im);
}
function getID($name){
    global $pdo;
    $insert = 'SELECT ID FROM users WHERE name="'.$name.'"';
    $ready = $pdo->prepare($insert);
    $ready->execute();
    $result = $ready->fetchAll();
    return $result[0]["ID"];
}
$margin = 20;
$height = 480;
$width = 800;
if(isset($_GET["width"])){
    $width = $_GET["width"];
}
if(isset($_GET["height"])){
    $height = $_GET["height"];
}
if($width*$height>13500000){
    $width = $width/10;
    $height = $height/10;
}
graph(Query());
?>