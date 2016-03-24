<?php

$server_ip = isset($_GET['ip']) ? $_GET['ip'] : 'play.craftworldmc.com';
$server_title = 'CraftWorld';

$font_regular = './static/font/regular.ttf';
$font_bold    = './static/font/bold.ttf';

$start = microtime(true);

// Disable browser caching
header('cache-control:no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('expires: Thu, 00 Nov 1980 00:00:00 GMT');
header('pragma: no-cache');

require_once './include/minecraftserverstatus.class.php';
$status = new MinecraftServerStatus();

// Server Status
$response = $status->getStatus($server_ip);
//die(json_encode($response));
$data = explode(',', $response['raw_data']->favicon);
$favicon = imagecreatefromstring(base64_decode($data[1]));

header('Content-Type: image/png');

// Load Assets
$dirt = imagecreatefrompng('./static/texture.png');
$banner = imagecreatetruecolor(620, 90);

// Load Colors
$black = imagecolorallocate($banner, 0, 0, 0);
$dark_blue = imagecolorallocate($banner, 0, 0, 170);
$dark_green = imagecolorallocate($banner, 0, 170, 0);
$dark_aqua = imagecolorallocate($banner, 0, 170, 170);
$dark_red = imagecolorallocate($banner, 170, 0, 0);
$dark_purple = imagecolorallocate($banner, 170, 0, 170);
$gold = imagecolorallocate($banner, 255, 170, 0);
$gray = imagecolorallocate($banner, 170, 170, 170);
$dark_gray = imagecolorallocate($banner, 85, 85, 85);
$blue = imagecolorallocate($banner, 85, 85, 255);
$green = imagecolorallocate($banner, 85, 255, 85);
$aqua = imagecolorallocate($banner, 85, 255, 255);
$red = imagecolorallocate($banner, 255, 85, 85);
$light_purple = imagecolorallocate($banner, 255, 85, 255);
$yellow = imagecolorallocate($banner, 255, 255, 85);
$white = imagecolorallocate($banner, 255, 255, 255);

// Create Background
for($x = 0; $x <= 620; $x += 32) {
	for($y = 0; $y <= 76; $y += 32) {
		//imagecopyresampled($banner, $dirt, $x, $y, 0, 0, 32, 32, 32, 32);
		imagecopymerge($banner, $dirt, $x, $y, 0, 0, 32, 32, 25);
	}
}

// Favicon
imagecopyresampled($banner, $favicon, 6, 13, 0, 0, 64, 64, 64, 64);

// Title
imagettftext($banner, 12, 0, 76, 20, $white, $font_regular, $server_title);

// Description
$base_text = $response['raw_data']->description->text;
$x = 76;

if(strlen($base_text) > 0) {
	imagettftext($banner, 12, 0, $x, 40, $dark_green, $font_regular, $base_text);

	$bbox = imagettfbbox(12, 0, $font_regular, $base_text);
	$x += $bbox[2] - $bbox[0];
}

$current_description = 0;

foreach($response['raw_data']->description->extra as $extra) {
	$text = $extra->text;
	$color = $white;
	if(isset($extra->color)) {
		$c = $extra->color;
		$color = $$c; // TODO: I should really do this in a better way
	}
	$font = $font_regular;
	if(isset($extra->bold) && $extra->bold == true) {
		$font = $font_bold;
	}

	if (strpos($text, "\n") !== false) {
		$split = explode("\n", $text);

		if(strlen($split[0]) > 0) {
			imagettftext($banner, 12, 0, $x, $current_description == 0 ? 40 : 60, $color, $font, $split[0]);

			$bbox = imagettfbbox(12, 0, $font, $split[0]);
			$x += $bbox[2] - $bbox[0];
		}

		if($current_description > 0) {
			break;
		}
		$current_description += 1;
		$x = 76;

		if(strlen($split[1]) > 0) {
			imagettftext($banner, 12, 0, $x, 60, $color, $font, $split[1]);

			$bbox = imagettfbbox(12, 0, $font, $split[1]);
			$x += $bbox[2] - $bbox[0];
		}
	} else {
		if(strlen($text) > 0) {
			imagettftext($banner, 12, 0, $x, $current_description == 0 ? 40 : 60, $color, $font, $text);

			$bbox = imagettfbbox(12, 0, $font, $text);
			$x += $bbox[2] - $bbox[0];
		}
	}
}

imagettftext($banner, 12, 0, 76, 40, $dark_green, $font_regular, $description[0]);
imagettftext($banner, 12, 0, 76, 60, $dark_green, $font_regular, $description[1]);

// Server IP
imagettftext($banner, 12, 0, 76, 80, $dark_gray, $font_regular, $server_ip);

// Online Player Counter
$players_text = $response['raw_data']->players->online . '/' . $response['raw_data']->players->max;
$bbox = imagettfbbox(12, 0, $font_regular, $players_text);
$x = $bbox[2] - $bbox[0];

imagettftext($banner, 12, 0, 593 - $x, 19, $white, $font_regular, $players_text);

// Latency
$black_foreground = imagecolorallocate($banner, 91, 91, 91);
$black_background = imagecolorallocate($banner, 56, 56, 56);
$green_foreground = imagecolorallocate($banner, 0, 255, 33);
$green_background = imagecolorallocate($banner, 0, 135, 15);

$x = 595;
$y = 5;

$ping = $response['ping'];
if($ping > 0) {
	if($ping < 150) {
		$ping = 5;
	} elseif($ping < 300){
		$ping = 4;
	} elseif($ping < 600){
		$ping = 3;
	} elseif($ping < 1000){
		$ping = 2;
	} else {
		$ping = 1;
	} 
} else {
	$ping = -1;
}

switch($ping) {
	case 1:
		$fills = [
			$green_background,
			$green_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground
		];
		break;
	case 2:
		$fills = [
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground
		];
		break;
	case 3:
		$fills = [
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground
		];
		break;
	case 4:
		$fills = [
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$black_background,
			$black_foreground
		];
		break;
	case 5:
		$fills = [
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground,
			$green_background,
			$green_foreground
		];
		break;
	default:
		$fills = [
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground,
			$black_background,
			$black_foreground
		];
		break;
}

imagerectangle($banner, $x + 1 * 2, $y + 5 * 2, $x + 2  * 2 - 1, $y + 7 * 2 - 1, $fills[0]);
imagerectangle($banner, $x + 0 * 2, $y + 4 * 2, $x + 1  * 2 - 1, $y + 6 * 2 - 1, $fills[1]);
imagerectangle($banner, $x + 3 * 2, $y + 4 * 2, $x + 4  * 2 - 1, $y + 7 * 2 - 1, $fills[2]);
imagerectangle($banner, $x + 2 * 2, $y + 3 * 2, $x + 3  * 2 - 1, $y + 6 * 2 - 1, $fills[3]);
imagerectangle($banner, $x + 5 * 2, $y + 3 * 2, $x + 6  * 2 - 1, $y + 7 * 2 - 1, $fills[4]);
imagerectangle($banner, $x + 4 * 2, $y + 2 * 2, $x + 5  * 2 - 1, $y + 6 * 2 - 1, $fills[5]);
imagerectangle($banner, $x + 7 * 2, $y + 2 * 2, $x + 8  * 2 - 1, $y + 7 * 2 - 1, $fills[6]);
imagerectangle($banner, $x + 6 * 2, $y + 1 * 2, $x + 7  * 2 - 1, $y + 6 * 2 - 1, $fills[7]);
imagerectangle($banner, $x + 9 * 2, $y + 1 * 2, $x + 10 * 2 - 1, $y + 7 * 2 - 1, $fills[8]);
imagerectangle($banner, $x + 8 * 2, $y + 0 * 2, $x + 9  * 2 - 1, $y + 6 * 2 - 1, $fills[9]);

// Banner Generation Completed
echo imagepng($banner);

?>
