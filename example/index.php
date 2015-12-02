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

$cloned = clone $original;
$resized = $cloned->resize(400, 300);

$cloned = clone $original;
$resizedByPercent = $cloned->resizeByPercent(30);

$cloned = clone $original;
$resizedFromCenter = $cloned->resizeFromCenter(200);

$cloned = clone $original;
$clipped = $cloned->clip(0, 0, 300, 200);

$cloned = clone $original;
$resizeAndClipped = $cloned->resize(400, 300)->clip(0,0, 300, 200);

$transparencyGif = new \Volcanus\Thumbnail\Image(array(
	'path' => $images_dir . DIRECTORY_SEPARATOR . '100-100t.gif',
));

$cloned = clone $transparencyGif;
$resizedTransparencyGif = $cloned->resize(50, 50);

$source = highlight_file(__FILE__, true);
?>
<html>
<style type="text/css">
body {background-color: #ccc;}
</style>
<body>

<h1>Examples for Volcanus_Thumbnail</h1>

<?php if (isset($original)) : ?>
<h2>Original (<?= $original->getWidth() ?> x <?= $original->getHeight()?>)<h2>
<img src="<?= $original->dataUri() ?>" />
<?php endif ?>

<?php if (isset($resized)) : ?>
<h2>Resized (<?= $resized->getWidth() ?> x <?= $resized->getHeight()?>)</h2>
<img src="<?= $resized->dataUri() ?>" />
<?php endif ?>

<?php if (isset($resizedByPercent)) : ?>
<h2>Resized by percent (<?= $resizedByPercent->getWidth() ?> x <?= $resizedByPercent->getHeight()?>)</h2>
<img src="<?= $resizedByPercent->dataUri() ?>" />
<?php endif ?>

<?php if (isset($resizedFromCenter)) : ?>
<h2>Resized from center (<?= $resizedFromCenter->getWidth() ?> x <?= $resizedFromCenter->getHeight()?>)</h2>
<img src="<?= $resizedFromCenter->dataUri() ?>" />
<?php endif ?>

<?php if (isset($clipped)) : ?>
<h2>Clip (<?= $clipped->getWidth() ?> x <?= $clipped->getHeight()?>)</h2>
<img src="<?= $clipped->dataUri() ?>" />
<?php endif ?>

<?php if (isset($resizeAndClipped)) : ?>
<h2>Resize and clipped (<?= $resizeAndClipped->getWidth() ?> x <?= $resizeAndClipped->getHeight()?>)</h2>
<img src="<?= $resizeAndClipped->dataUri() ?>" />
<?php endif ?>

<?php if (isset($transparencyGif)) : ?>
<h2>Transparency GIF (<?= $transparencyGif->getWidth() ?> x <?= $transparencyGif->getHeight()?>)</h2>
<img src="<?= $transparencyGif->dataUri() ?>" />
<?php endif ?>

<?php if (isset($resizedTransparencyGif)) : ?>
<h2>Resized Transparency GIF (<?= $resizedTransparencyGif->getWidth() ?> x <?= $resizedTransparencyGif->getHeight()?>)</h2>
<img src="<?= $resizedTransparencyGif->dataUri() ?>" />
<?php endif ?>

<hr />
<?= $source ?>

</body>
</html>
