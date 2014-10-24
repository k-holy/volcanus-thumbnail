<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */
namespace Acme;

require_once __DIR__ . '/../bootstrap.php';

class Thumbnail
{
	public $image;
	public $title;
	public $callScript = false;

	public function __construct($props)
	{
		if (isset($props['image'])) {
			$this->image = $props['image'];
		}
		if (isset($props['title'])) {
			$this->title = $props['title'];
		}
		if (isset($props['callScript'])) {
			$this->callScript = $props['callScript'];
		}
	}
}

function h($var) {
	return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
}

function getOrientationFrom($path) {
	$exif = exif_read_data($path);
	if (isset($exif['Orientation'])) {
		return $exif['Orientation'];
	}
	return null;
}

$images_dir = __DIR__ . DIRECTORY_SEPARATOR . '/images';

$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $images_dir . DIRECTORY_SEPARATOR . '800-600.png',
));

if (isset($_GET['thumbnail'])) {
	$extension = pathinfo($_GET['thumbnail'], PATHINFO_EXTENSION);
	$conditions = explode('x', pathinfo($_GET['thumbnail'], PATHINFO_FILENAME));
	$image = clone $original;
	$thumbnail = $image->resize(
		(isset($conditions[0])) ? intval($conditions[0]) : null,
		(isset($conditions[1])) ? intval($conditions[1]) : null
	);
	$imageType = IMAGETYPE_PNG;
	switch(strtolower($extension)) {
	case 'gif':
		$imageType = IMAGETYPE_GIF;
		break;
	case 'jpg':
	case 'jpeg':
		$imageType = IMAGETYPE_JPEG;
		break;
	case 'png':
	default:
		$imageType = IMAGETYPE_PNG;
		break;
	}
	header('Content-Type', image_type_to_mime_type($imageType));
	$thumbnail->output(null, $imageType, 100);
	exit;
}

$thumbnails = array();

$thumbnails[] = new Thumbnail(array(
	'image' => $original,
	'title' => 'オリジナル',
));

// resize to 400x300
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resize(400, 300),
	'title' => 'resize(400, 300)',
	'callScript' => true,
));

// resize to 600
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resize(600),
	'title' => 'resize(600)',
	'callScript' => true,
));

// resize to 30%
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeByPercent(30),
	'title' => 'resizeByPercent(30)',
));

$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeFromCenter(200),
	'title' => 'resizeFromCenter(200)',
));

// clip from (0,0) to 300x200
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->clip(0, 0, 300, 200),
	'title' => 'clip(0, 0, 300, 200)',
));

// resize to 400x300 then clip from (0,0) to 300x200
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resize(400, 300)->clip(0, 0, 300, 200),
	'title' => 'resize(400, 300)->clip(0, 0, 300, 200)',
));

// resize 10% then rotate orientation(1)
$path = $images_dir . DIRECTORY_SEPARATOR . 'orientation(1).jpg';
$orientation = getOrientationFrom($path);
$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $path,
));
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeByPercent(10)->rotateByOrientation($orientation),
	'title' => sprintf('resizeByPercent(10)->rotateByOrientation(%d)', $orientation),
));

// resize 10% then rotate orientation(3)
$path = $images_dir . DIRECTORY_SEPARATOR . 'orientation(3).jpg';
$orientation = getOrientationFrom($path);
$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $path,
));
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeByPercent(10)->rotateByOrientation($orientation),
	'title' => sprintf('resizeByPercent(10)->rotateByOrientation(%d)', $orientation),
));

// resize 10% then rotate orientation(6)
$path = $images_dir . DIRECTORY_SEPARATOR . 'orientation(6).jpg';
$orientation = getOrientationFrom($path);
$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $path,
));
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeByPercent(10)->rotateByOrientation($orientation),
	'title' => sprintf('resizeByPercent(10)->rotateByOrientation(%d)', $orientation),
));

// resize 10% then rotate orientation(8)
$path = $images_dir . DIRECTORY_SEPARATOR . 'orientation(8).jpg';
$orientation = getOrientationFrom($path);
$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $path,
));
$image = clone $original;
$thumbnails[] = new Thumbnail(array(
	'image' => $image->resizeByPercent(10)->rotateByOrientation($orientation),
	'title' => sprintf('resizeByPercent(10)->rotateByOrientation(%d)', $orientation),
));

?>
<!DOCTYPE html>
<html>
<meta charset="utf-8" />
<title>Thumbnailテスト</title>
</head>
<body>

<h1>Thumbnailテスト</h1>

<ul>
<?php foreach ($thumbnails as $thumbnail) : ?>
	<li>
		<div class="thumbnail">
			<img src="<?=h($thumbnail->image->dataUri())?>" />
			<h3>PNG (DataURI) <?=h($thumbnail->image->getWidth())?> x <?=h($thumbnail->image->getHeight())?></h3>
			<p><?=h($thumbnail->title)?></p>
		</div>
	</li>
<?php if ($thumbnail->callScript) : ?>
	<li>
		<div class="thumbnail">
			<img src="?thumbnail=<?=rawurlencode($thumbnail->image->getWidth())?>x<?=rawurlencode($thumbnail->image->getHeight())?>.jpg" />
			<h3>JPEG (スクリプトコール) <?=h($thumbnail->image->getWidth())?> x <?=h($thumbnail->image->getHeight())?></h3>
			<p><?=h($thumbnail->title)?></p>
		</div>
	</li>
<?php endif ?>
<?php endforeach ?>
</ul>

</body>
</html>
