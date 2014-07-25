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
		$this->srcDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'img';
		$this->dstDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'tmp';
	}

	public function tearDown()
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

	public function testInitializeByPath()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$data = file_get_contents($path);
		$image = new Image(array(
			'path' => $path,
		));
		$this->assertEquals($path, $image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByData()
	{
		$data = file_get_contents($this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg');
		$image = new Image(array(
			'data' => $data,
		));
		$this->assertNull($image->getPath());
		$this->assertEquals($data, $image->getData());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testInitializeByResource()
	{
		$resource = imagecreatetruecolor(800, 600);
		$image = new Image(array(
			'resource' => $resource,
		));
		$this->assertNull($image->getPath());
		$this->assertNull($image->getData());
		$this->assertEquals(800, $image->getWidth());
		$this->assertEquals(600, $image->getHeight());
	}

	public function testClear()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$image = new Image(array(
			'path' => $path,
		));
		$image->clear();
		$this->assertNull($image->getPath());
		$this->assertNull($image->getData());
		$this->assertNull($image->getWidth());
		$this->assertNull($image->getHeight());
	}

	public function testGetResourceByPath()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$image = new Image(array(
			'path' => $path,
		));
		$resource = $image->getResource();
		$this->assertTrue(is_resource($resource));
		$this->assertStringStartsWith('gd', get_resource_type($resource));
	}

	public function testOutputWithPath()
	{
		$srcPath = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.jpg', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $srcPath,
		));
		$srcImage->output($dstPath);
		$dstImage = new Image(array(
			'path' => $dstPath,
		));
		$this->assertEquals($srcImage->getWidth(), $dstImage->getWidth());
		$this->assertEquals($srcImage->getHeight(), $dstImage->getHeight());
	}

	public function testConvertJpegToPng()
	{
		$srcPath = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.png', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $srcPath,
		));
		$srcImage->output($dstPath, IMAGETYPE_PNG);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_PNG, $imageInfo[2]);
	}

	public function testConvertJpegToGif()
	{
		$srcPath = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.gif', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $srcPath,
		));
		$srcImage->output($dstPath, IMAGETYPE_GIF);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_GIF, $imageInfo[2]);
	}

	public function testConvertPngToJpeg()
	{
		$srcPath = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png';
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.jpg', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $srcPath,
		));
		$srcImage->output($dstPath, IMAGETYPE_JPEG);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_JPEG, $imageInfo[2]);
	}

	public function testConvertPngToGif()
	{
		$srcPath = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.png';
		$dstPath = $this->dstDirectory . DIRECTORY_SEPARATOR . sprintf('800-600.%s.gif', __FUNCTION__);
		$srcImage = new Image(array(
			'path' => $srcPath,
		));
		$srcImage->output($dstPath, IMAGETYPE_GIF);
		$imageInfo = getimagesize($dstPath);
		$this->assertEquals(IMAGETYPE_GIF, $imageInfo[2]);
	}

	public function testResize()
	{
		$path = $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg';
		$image = new Image(array(
			'path' => $path,
		));
		$resizedImage = $image->resize(400, 300);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $resizedImage);
		$this->assertEquals(400, $resizedImage->getWidth());
		$this->assertEquals(300, $resizedImage->getHeight());
	}

	public function testResizeByWidth()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$thumbnail = $image->resize(400);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(400, $thumbnail->getWidth());
		$this->assertEquals(300, $thumbnail->getHeight());
	}

	public function testResizeByPercent()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$thumbnail = $image->resizeByPercent(50);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(400, $thumbnail->getWidth());
		$this->assertEquals(300, $thumbnail->getHeight());
	}

	public function testResizeByPercentZoom()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$thumbnail = $image->resizeByPercent(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(1600, $thumbnail->getWidth());
		$this->assertEquals(1200, $thumbnail->getHeight());
	}

	public function testResizeFromCenter()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$thumbnail = $image->resizeFromCenter(200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(200, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testClip()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$thumbnail = $image->clip(0, 0, 300, 200);
		$this->assertInstanceOf('\Volcanus\Thumbnail\Image', $thumbnail);
		$this->assertEquals(300, $thumbnail->getWidth());
		$this->assertEquals(200, $thumbnail->getHeight());
	}

	public function testDataUri()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$imageInfo = getimagesize($image->dataUri());
		$this->assertEquals(800, $imageInfo[0]);
		$this->assertEquals(600, $imageInfo[1]);
	}

	public function testDataUriWithType()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$imageInfo = getimagesize($image->dataUri(IMAGETYPE_JPEG));
		$this->assertEquals(800, $imageInfo[0]);
		$this->assertEquals(600, $imageInfo[1]);
	}

	public function testContentTypeHeaderGif()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$this->assertEquals('Content-Type: image/gif', $image->contentTypeHeader(IMAGETYPE_GIF));
	}

	public function testContentTypeHeaderJpeg()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$this->assertEquals('Content-Type: image/jpeg', $image->contentTypeHeader(IMAGETYPE_JPEG));
	}

	public function testContentTypeHeaderPng()
	{
		$image = new Image(array(
			'path' => $this->srcDirectory . DIRECTORY_SEPARATOR . '800-600.jpg',
		));
		$this->assertEquals('Content-Type: image/png', $image->contentTypeHeader(IMAGETYPE_PNG));
	}

}
