<?php
/**
 * サムネイル
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Thumbnail\Test;

use Volcanus\Thumbnail\Image;

/**
 * Test for Image
 *
 * @author k.holy74@gmail.com
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{

	private $srcDirectory;
	private $dstDirectory;

	public function setUp()
	{
		$this->srcDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'src';
		$this->dstDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'tmp';
	}

	public function tearDown()
	{
		$this->clearDirectory();
	}

	public function testInitializeByPath()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png';
		$data = file_get_contents($path);
		$image = new Image(array(
			'path' => $path,
		));
		$this->assertEquals($path, $image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_PNG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByPathWithType()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png';
		$data = file_get_contents($path);
		$image = new Image(array(
			'path' => $path,
			'type' => IMAGETYPE_JPEG,
		));
		$this->assertEquals($path, $image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_JPEG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByPathSplFileInfo()
	{
		$path = new \SplFileInfo($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png');
		ob_start();
		$path->openFile('r')->fpassthru();
		$data = ob_get_contents();
		ob_end_clean();
		$image = new Image(array(
			'path' => $path,
		));
		$this->assertEquals($path, $image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_PNG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByPathSplFileInfoWithType()
	{
		$path = new \SplFileInfo($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png');
		ob_start();
		$path->openFile('r')->fpassthru();
		$data = ob_get_contents();
		ob_end_clean();
		$image = new Image(array(
			'path' => $path,
			'type' => IMAGETYPE_JPEG,
		));
		$this->assertEquals($path, $image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_JPEG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByData()
	{
		$data = file_get_contents($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png');
		$image = new Image(array(
			'data' => $data,
		));
		$this->assertNull($image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_PNG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByDataWithType()
	{
		$data = file_get_contents($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png');
		$image = new Image(array(
			'data' => $data,
			'type' => IMAGETYPE_JPEG,
		));
		$this->assertNull($image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(IMAGETYPE_JPEG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByResource()
	{
		$image = new Image(array(
			'resource' => imagecreatetruecolor(800, 600),
		));
		$this->assertNull($image->getPath());
		$this->assertNull($image->getData());
		$this->assertNull($image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByResourceWithType()
	{
		$image = new Image(array(
			'resource' => imagecreatetruecolor(800, 600),
			'type' => IMAGETYPE_PNG,
		));
		$this->assertNull($image->getPath());
		$this->assertNull($image->getData());
		$this->assertEquals(IMAGETYPE_PNG, $image->getType());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInitializeRaiseExceptionWhenInvalidType()
	{
		$image = new Image(array(
			'type' => array(),
		));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInitializeRaiseExceptionWhenUnsupportedType()
	{
		$image = new Image(array(
			'type' => IMAGETYPE_PSD,
		));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInitializeRaiseExceptionWhenInvalidFloor()
	{
		$image = new Image(array(
			'floor' => array(),
		));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInitializeRaiseExceptionWhenKeyIsUnsupported()
	{
		$image = new Image(array(
			'unsupported-key' => null,
		));
	}

	public function testClone()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$cloned = clone $image;
		$this->assertTrue(is_resource($image->getResource()));
		$this->assertStringStartsWith('gd', get_resource_type($image->getResource()));
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $cloned);
		$this->assertEquals($cloned->getPath(), $image->getPath());
		$this->assertEquals($cloned->getData(), $image->getData());
		$this->assertEquals($cloned->getType(), $image->getType());
		$this->assertEquals($cloned->getWidth(), $image->getWidth());
		$this->assertEquals($cloned->getHeight(), $image->getHeight());
		$this->assertNotEquals($cloned->getResource(), $image->getResource());
		$image->clear();
		$this->assertTrue(is_resource($cloned->getResource()));
		$this->assertStringStartsWith('gd', get_resource_type($cloned->getResource()));
	}

	public function testClear()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$image->clear();
		$this->assertNull($image->getPath());
		$this->assertNull($image->getData());
		$this->assertNull($image->getType());
		$this->assertNull($image->getWidth());
		$this->assertNull($image->getHeight());
	}

	public function testGetResourceByPath()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$resource = $image->getResource();
		$this->assertTrue(is_resource($resource));
		$this->assertStringStartsWith('gd', get_resource_type($resource));
	}

	public function testOutputWithPath()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$srcImage->output($dstPath);
		$dstImage = new Image(array(
			'path' => $dstPath,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
	}

	public function testOutputWithPathSplFileInfo()
	{
		$dstPath = new \SplFileInfo($this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__));
		$srcImage = new Image(array(
			'path' => new \SplFileInfo($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png'),
		));
		$srcImage->output($dstPath);
		$dstImage = new Image(array(
			'path' => $dstPath,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
	}

	public function testConvertPngToJpeg()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.jpg', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
			'type' => IMAGETYPE_JPEG,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_JPEG, $imageInfo[2]);
	}

	public function testConvertPngToGif()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.gif', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
			'type' => IMAGETYPE_GIF,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_GIF, $imageInfo[2]);
	}

	public function testConvertJpegToPng()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
			'type' => IMAGETYPE_PNG,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_PNG, $imageInfo[2]);
	}

	public function testConvertJpegToGif()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.gif', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
			'type' => IMAGETYPE_GIF,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_GIF, $imageInfo[2]);
	}

	public function testConvertGifToJpeg()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.jpg', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.gif',
			'type' => IMAGETYPE_JPEG,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_JPEG, $imageInfo[2]);
	}

	public function testConvertGifToPng()
	{
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.gif',
			'type' => IMAGETYPE_PNG,
		));
		$srcImage->output($dstPath);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_PNG, $imageInfo[2]);
	}

	public function testOutputJpegWithQuality()
	{
		$dstPathLowQuality = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.low.jpg', __FUNCTION__);
		$dstPathHighQuality = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.high.jpg', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$srcImage->output($dstPathLowQuality, IMAGETYPE_JPEG, 50);
		$srcImage->output($dstPathHighQuality, IMAGETYPE_JPEG, 100);
		$this->assertGreaterThan(filesize($dstPathLowQuality), filesize($dstPathHighQuality));
	}

	public function testOutputWithoutPathJpeg()
	{
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		ob_start();
		$srcImage->output();
		$data = ob_get_contents();
		ob_end_clean();
		$dstImage = new Image(array(
			'data' => $data,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
		$this->assertEquals($dstImage->getType(), IMAGETYPE_JPEG);
	}

	public function testOutputWithoutPathPng()
	{
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		ob_start();
		$srcImage->output();
		$data = ob_get_contents();
		ob_end_clean();
		$dstImage = new Image(array(
			'data' => $data,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
		$this->assertEquals($dstImage->getType(), IMAGETYPE_PNG);
	}

	public function testOutputWithoutPathGif()
	{
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.gif',
		));
		ob_start();
		$srcImage->output();
		$data = ob_get_contents();
		ob_end_clean();
		$dstImage = new Image(array(
			'data' => $data,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
		$this->assertEquals($dstImage->getType(), IMAGETYPE_GIF);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testOutputRaiseExceptionWhenUnsupportedImageTypeWasSpecified()
	{
		$srcImage = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.bmp', __FUNCTION__);
		$srcImage->output($dstPath, IMAGETYPE_BMP);
	}

	public function testResizePng()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$resizedImage = $image->resize(400, 300);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $resizedImage);
		$this->assertEquals(400, $resizedImage->getWidth());
		$this->assertEquals(300, $resizedImage->getHeight());
		$this->assertEquals(IMAGETYPE_PNG, $resizedImage->getType());
	}

	public function testResizeJpeg()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$resizedImage = $image->resize(400, 300);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $resizedImage);
		$this->assertEquals(400, $resizedImage->getWidth());
		$this->assertEquals(300, $resizedImage->getHeight());
		$this->assertEquals(IMAGETYPE_JPEG, $resizedImage->getType());
	}

	public function testResizeGif()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.gif',
		));
		$resizedImage = $image->resize(400, 300);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $resizedImage);
		$this->assertEquals(400, $resizedImage->getWidth());
		$this->assertEquals(300, $resizedImage->getHeight());
		$this->assertEquals(IMAGETYPE_GIF, $resizedImage->getType());
	}

	public function testResizeReturnSameInstanceWhenUnnecessary()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$resizedImage = $image->resize(800, 600);
		$this->assertSame($resizedImage, $image);
	}

	public function testResizeByWidth()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$thumbnail = $image->resize(400);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(400, $thumbnail->getWidth());
		$this->assertEquals(300, $thumbnail->getHeight());
	}

	public function testResizeWithFloor()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '700-525.png',
			'floor' => true,
		));
		$thumbnail = $image->resize(350);
		$this->assertEquals(350, $thumbnail->getWidth());
		$this->assertEquals(262, $thumbnail->getHeight());
	}

	public function testResizeWithCeil()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '700-525.png',
			'floor' => false,
		));
		$thumbnail = $image->resize(350);
		$this->assertEquals(350, $thumbnail->getWidth());
		$this->assertEquals(263, $thumbnail->getHeight());
	}

	public function testResizeByPercent()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$thumbnail = $image->resizeByPercent(50);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(400, $thumbnail->getWidth());
		$this->assertEquals(300, $thumbnail->getHeight());
	}

	public function testResizeByPercentZoom()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$thumbnail = $image->resizeByPercent(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(1600, $thumbnail->getWidth());
		$this->assertEquals(1200, $thumbnail->getHeight());
	}

	public function testResizeByPercentWithFloor()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '700-525.png',
			'floor' => true,
		));
		$thumbnail = $image->resizeByPercent(50);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(350, $thumbnail->getWidth());
		$this->assertEquals(262, $thumbnail->getHeight());
	}

	public function testResizeByPercentWithCeil()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '700-525.png',
			'floor' => false,
		));
		$thumbnail = $image->resizeByPercent(50);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(350, $thumbnail->getWidth());
		$this->assertEquals(263, $thumbnail->getHeight());
	}

	public function testResizeFromCenterFromOblong()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$thumbnail = $image->resizeFromCenter(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(200, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testResizeFromCenterFromVerticallyLong()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '400-600.png',
		));
		$thumbnail = $image->resizeFromCenter(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(200, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testResizeFromCenterFromSquare()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '600-600.png',
		));
		$thumbnail = $image->resizeFromCenter(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(200, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testClip()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$thumbnail = $image->clip(0, 0, 300, 200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(300, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testFlip()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$flipped = $image->flip();
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $flipped);
		$this->assertEquals(800, $flipped->getWidth());
		$this->assertEquals(600, $flipped->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$flipped->output($dstPath);
	}

	public function testFlop()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$flopped = $image->flop();
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $flopped);
		$this->assertEquals(800, $flopped->getWidth());
		$this->assertEquals(600, $flopped->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$flopped->output($dstPath);
	}

	public function testRotateByOrientationTopRight()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_TOPRIGHT);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(800, $rotated->getWidth());
		$this->assertEquals(600, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationBottomRight()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_BOTTOMRIGHT);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(800, $rotated->getWidth());
		$this->assertEquals(600, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationBottomLeft()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_BOTTOMLEFT);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(800, $rotated->getWidth());
		$this->assertEquals(600, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationLeftTop()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_LEFTTOP);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(600, $rotated->getWidth());
		$this->assertEquals(800, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('600-800.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationRightTop()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_RIGHTTOP);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(600, $rotated->getWidth());
		$this->assertEquals(800, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('600-800.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationRightBottom()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_RIGHTBOTTOM);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(600, $rotated->getWidth());
		$this->assertEquals(800, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('600-800.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	public function testRotateByOrientationLeftBottom()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$rotated = $image->rotateByOrientation(Image::ORIENTATION_LEFTBOTTOM);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(600, $rotated->getWidth());
		$this->assertEquals(800, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('600-800.%s.png', __FUNCTION__);
		$rotated->output($dstPath);
	}

	/**
	 * @medium
	 */
	public function testRotateOrientationTopLeftFromExif()
	{
		$filename = 'orientation(1).jpg';
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . $filename;
		$image = new Image(array(
			'path' => $path,
		));
		$rotated = $image->rotateByOrientation($this->getOrientationFrom($path));
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(2592, $rotated->getWidth());
		$this->assertEquals(1936, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('%s.%s.png', $filename, __FUNCTION__);
		$rotated->output($dstPath, IMAGETYPE_PNG);
	}

	/**
	 * @medium
	 */
	public function testRotateOrientationBottomRightFromExif()
	{
		$filename = 'orientation(3).jpg';
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . $filename;
		$image = new Image(array(
			'path' => $path,
		));
		$rotated = $image->rotateByOrientation($this->getOrientationFrom($path));
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(2592, $rotated->getWidth());
		$this->assertEquals(1936, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('%s.%s.png', $filename, __FUNCTION__);
		$rotated->output($dstPath, IMAGETYPE_PNG);
	}

	/**
	 * @medium
	 */
	public function testRotateOrientationRightTopFromExif()
	{
		$filename = 'orientation(6).jpg';
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . $filename;
		$image = new Image(array(
			'path' => $path,
		));
		$rotated = $image->rotateByOrientation($this->getOrientationFrom($path));
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(1936, $rotated->getWidth());
		$this->assertEquals(2592, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('%s.%s.png', $filename, __FUNCTION__);
		$rotated->output($dstPath, IMAGETYPE_PNG);
	}

	/**
	 * @medium
	 */
	public function testRotateOrientationLeftTopFromExif()
	{
		$filename = 'orientation(8).jpg';
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . $filename;
		$image = new Image(array(
			'path' => $path,
		));
		$rotated = $image->rotateByOrientation($this->getOrientationFrom($path));
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $rotated);
		$this->assertEquals(1936, $rotated->getWidth());
		$this->assertEquals(2592, $rotated->getHeight());
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('%s.%s.png', $filename, __FUNCTION__);
		$rotated->output($dstPath, IMAGETYPE_PNG);
	}

	public function testDataUri()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$imageInfo = getimagesize($image->dataUri());
		$this->assertEquals(800, $imageInfo[0]);
		$this->assertEquals(600, $imageInfo[1]);
	}

	public function testDataUriWithType()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$imageInfo = getimagesize($image->dataUri(IMAGETYPE_JPEG));
		$this->assertEquals(800, $imageInfo[0]);
		$this->assertEquals(600, $imageInfo[1]);
	}

	public function testContentTypeHeaderGif()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$this->assertEquals('Content-Type: image/gif', $image->contentTypeHeader(IMAGETYPE_GIF));
	}

	public function testContentTypeHeaderJpeg()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$this->assertEquals('Content-Type: image/jpeg', $image->contentTypeHeader(IMAGETYPE_JPEG));
	}

	public function testContentTypeHeaderPng()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$this->assertEquals('Content-Type: image/png', $image->contentTypeHeader(IMAGETYPE_PNG));
	}

	public function testContentTypeHeaderDefaultJpeg()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$this->assertEquals('Content-Type: image/jpeg', $image->contentTypeHeader());
	}

	public function testContentTypeHeaderDefaultPng()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$this->assertEquals('Content-Type: image/png', $image->contentTypeHeader());
	}

	public function testContentTypeHeaderDefaultGif()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.gif',
		));
		$this->assertEquals('Content-Type: image/gif', $image->contentTypeHeader());
	}

	public function testDestroy()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png',
		));
		$this->assertTrue(is_resource($image->getResource()));
		$before = memory_get_usage(false);
		$image->destroy();
		$after = memory_get_usage(false);
		$this->assertNull($image->getResource());
		$this->assertLessThan($before, $after);
	}

	private function clearDirectory()
	{
		$it = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($this->dstDirectory)
		);
		foreach ($it as $file) {
			if ($file->isFile() && $file->getBaseName() !== '.gitignore') {
				unlink($file);
			}
		}
	}

	private function getOrientationFrom($path)
	{
		$exif = exif_read_data($path);
		if (isset($exif['Orientation'])) {
			return $exif['Orientation'];
		}
		return null;
	}

}
