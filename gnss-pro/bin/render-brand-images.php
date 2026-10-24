<?php
/**
 * Build the social image and the theme screenshot with DejaVu.
 */

$font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
if (!is_file($font)) {
    fwrite(STDERR, "Нет шрифта DejaVu\n");
    exit(1);
}

$root = dirname(__DIR__) . '/theme/assets/img';

function gnss_canvas(int $width, int $height): GdImage
{
    $image = imagecreatetruecolor($width, $height);
    imageantialias($image, true);
    $bg = imagecolorallocate($image, 7, 36, 33);
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    $brass = imagecolorallocate($image, 227, 177, 122);
    imagefilledrectangle($image, 0, 0, $width, 16, $brass);
    return $image;
}

function gnss_orbit(GdImage $image, int $cx, int $cy, int $radius): void
{
    $paper = imagecolorallocate($image, 242, 238, 230);
    $brass = imagecolorallocate($image, 227, 177, 122);
    $earth = imagecolorallocate($image, 20, 99, 92);
    imageellipse($image, $cx, $cy, (int) ($radius * 1.7), (int) ($radius * 0.62), $paper);
    imageellipse($image, $cx, $cy, (int) ($radius * 1.35), (int) ($radius * 0.9), $brass);
    imagefilledellipse($image, $cx, $cy, (int) ($radius * 0.42), (int) ($radius * 0.42), $earth);
    imagefilledellipse($image, $cx + (int) ($radius * 0.72), $cy - (int) ($radius * 0.28), 14, 14, $brass);
}

$og = gnss_canvas(1200, 630);
gnss_orbit($og, 980, 330, 280);
$paper = imagecolorallocate($og, 242, 238, 230);
$brass = imagecolorallocate($og, 227, 177, 122);
imagettftext($og, 64, 0, 72, 280, $paper, $font, 'GNSS.PRO');
imagettftext($og, 28, 0, 76, 360, $brass, $font, 'ГЛОНАСС и спутниковая навигация');
imagettftext($og, 22, 0, 76, 430, $paper, $font, 'Журнал об орбитах, сигналах и времени');
imagepng($og, $root . '/og.png');
imagedestroy($og);

$shot = gnss_canvas(1200, 900);
gnss_orbit($shot, 860, 470, 360);
$paper = imagecolorallocate($shot, 242, 238, 230);
$brass = imagecolorallocate($shot, 227, 177, 122);
imagettftext($shot, 58, 0, 70, 280, $paper, $font, 'GNSS.PRO');
imagettftext($shot, 26, 0, 74, 360, $brass, $font, 'Журнал о ГЛОНАСС');
imagettftext($shot, 22, 0, 74, 430, $paper, $font, 'Орбиты, сигналы, время, точность');
imagepng($shot, dirname(__DIR__) . '/theme/screenshot.png');
imagedestroy($shot);

fwrite(STDOUT, "ok\n");
