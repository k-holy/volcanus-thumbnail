<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */
$loader = include realpath(__DIR__ . '/../vendor/autoload.php');

$images_dir = realpath(__DIR__ . '/../images');

$original = new \Volcanus\Thumbnail\Image(array(
	'path' => $images_dir . DIRECTORY_SEPARATOR . '800-600.png',
));

$resized = $original->resize(400, 300);

$resizedByPercent = $original->resizeByPercent(30);

$resizedFromCenter = $original->resizeFromCenter(200);

$clipped = $original->clip(0, 0, 300, 200);

$resizeAndClipped = $original->resize(400, 300)->clip(0,0, 300, 200);

$transparencyGif = new \Volcanus\Thumbnail\Image(array(
	'path' => $images_dir . DIRECTORY_SEPARATOR . '100-100t.gif',
));

$resizedTransparencyGif = $transparencyGif->resize(50, 50);

$source = highlight_file(__FILE__, true);
?>
<html>
<style type="text/css">
body {background-color: #ccc;}
</style>
<body>

<h1>Examples for Volcanus_Thumbnail</h1>

<h2>Original (<?= $original->getWidth() ?> x <?= $original->getHeight()?>)<h2>
<img src="<?= $original->dataUri() ?>" />

<h2>Resized (<?= $resized->getWidth() ?> x <?= $resized->getHeight()?>)</h2>
<img src="<?= $resized->dataUri() ?>" />

<h2>Resized by percent (<?= $resizedByPercent->getWidth() ?> x <?= $resizedByPercent->getHeight()?>)</h2>
<img src="<?= $resizedByPercent->dataUri() ?>" />

<h2>Resized from center (<?= $resizedFromCenter->getWidth() ?> x <?= $resizedFromCenter->getHeight()?>)</h2>
<img src="<?= $resizedFromCenter->dataUri() ?>" />

<h2>Clip (<?= $clipped->getWidth() ?> x <?= $clipped->getHeight()?>)</h2>
<img src="<?= $clipped->dataUri() ?>" />

<h2>Resize and clipped (<?= $resizeAndClipped->getWidth() ?> x <?= $resizeAndClipped->getHeight()?>)</h2>
<img src="<?= $resizeAndClipped->dataUri() ?>" />

<h2>Transparency GIF (<?= $transparencyGif->getWidth() ?> x <?= $transparencyGif->getHeight()?>)</h2>
<img src="<?= $transparencyGif->dataUri() ?>" />

<h2>Resized Transparency GIF (<?= $resizedTransparencyGif->getWidth() ?> x <?= $resizedTransparencyGif->getHeight()?>)</h2>
<img src="<?= $resizedTransparencyGif->dataUri() ?>" />

<hr />
<?= $source ?>

</body>
</html>
