<?php
/**
 * サムネイル
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Thumbnail;

/**
 * サムネイル画像生成クラス
 *
 * @author k.holy74@gmail.com
 */
class Image
{

	/**
	 * ファイルパス
	 * @var string
	 */
	private $path = null;

	/**
	 * データ
	 * @var string
	 */
	private $data = null;

	/**
	 * GDイメージリソース
	 * @var resource
	 */
	private $resource = null;

	/**
	 * タイプ IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG
	 * @var int
	 */
	private $type = null;

	/**
	 * 横幅
	 * @var int
	 */
	private $width = null;

	/**
	 * 高さ
	 * @var array
	 */
	private $height = null;

	/**
	 * 端数を切り上げるかどうか
	 * @var bool
	 */
	private $floor = true;

	/**
	 * コンストラクタ
	 *
	 * @param array | ArrayAccess 設定オプション
	 */
	public function __construct($configurations = array())
	{
		if (!function_exists('gd_info')) {
			throw new \RuntimeException(
				'Required GD Library.'
			);
		}
		$this->initialize($configurations);
	}

	/**
	 * オブジェクトを初期化します。
	 *
	 * @param array | ArrayAccess 設定オプション
	 */
	public function initialize($configurations = array())
	{
		$this->clear();
		if (!empty($configurations)) {
			foreach ($configurations as $name => $value) {
				switch ($name) {
				case 'path':
					$this->initializeByPath($value);
					break;
				case 'data':
					$this->initializeByData($value);
					break;
				case 'resource':
					$this->initializeByResource($value);
					break;
				case 'floor':
					if (!is_int($value) && !ctype_digit($value) && !is_bool($value)) {
						throw new \InvalidArgumentException(
							sprintf('The config "%s" only accepts bool.', $name));
					}
					$this->floor = (bool)$value;
					break;
				default:
					throw new \InvalidArgumentException(
						sprintf('The config parameter "%s" is not defined.', $name)
					);
				}
			}
		}
		return $this;
	}

	/**
	 * オブジェクトのフィールドをクリアします。
	 */
	public function clear()
	{
		$this->path = null;
		$this->data = null;
		$this->type = null;
		$this->width = null;
		$this->height = null;
		if ($this->validResource($this->resource)) {
			imagedestroy($this->resource);
		}
		$this->resource = null;
		$this->floor = true;
	}

	/**
	 * 画像のパスを返します。
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * 画像を文字列で返します。
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * 元画像の横幅を返します。
	 *
	 * @return int 横幅(px)
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * 元画像の高さを返します。
	 *
	 * @return int 横幅(px)
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * GDリソースをまだ生成されていなければ生成して返します。
	 *
	 * @return resource GDリソース
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * 横幅および高さの最大値を指定して、リサイズ画像を生成します。
	 *
	 * @param int 横幅最大値
	 * @param int 高さ最大値
	 * @return object Acme\Thumbnail\Image リサイズした画像
	 */
	public function resize($maxWidth = null, $maxHeight = null)
	{
		$srcW = $this->width;
		$srcH = $this->height;
		if (!empty($maxWidth) && empty($maxHeight)) {
			$maxHeight = $maxWidth;
		}
		if (!empty($maxHeight) && empty($maxWidth)) {
			$maxWidth = $maxHeight;
		}
		$dstW  = $srcW;
		$dstH = $srcH;
		if ((!empty($maxWidth) && $srcW > $maxWidth) || (!empty($maxHeight) && $srcH > $maxHeight)) {
			list($dstW, $dstH) = $this->getSize($srcW, $srcH, $maxWidth, $maxHeight, $this->floor);
		}
		if (1 > $dstW || 1 > $dstH) {
			return false;
		}
		if ($srcW === $dstW && $srcH === $dstH) {
			return $this;
		}
		return $this->createImage(0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
	}

	/**
	 * 倍率を指定して、リサイズ画像を生成します。
	 *
	 * @param int 倍率
	 * @return object Acme\Thumbnail\Image リサイズした画像
	 */
	public function resizeByPercent($percent)
	{
		$srcW = $this->width;
		$srcH = $this->height;
		list($dstW, $dstH) = $this->getSizeByPercent($srcW, $srcH, $percent, $this->floor);
		return $this->createImage(0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
	}

	/**
	 * サイズを指定して、中央から正方形に切り抜いた画像を生成します。
	 *
	 * @param int サイズ
	 * @return object Acme\Thumbnail\Image リサイズした画像
	 */
	public function resizeFromCenter($size)
	{
		$startX = 0;
		$startY = 0;
		$srcW = $this->width;
		$srcH = $this->height;
		$dstW = $size;
		$dstH = $size;
		if ($srcW > $srcH) {
			$workSize = ($srcW - $srcH);
			$startX = ($this->floor) ? floor($workSize / 2) : ceil($workSize / 2);
			$srcW  = ($srcH > $size) ? $srcW - $workSize : $srcH;
		} elseif ($srcH > $srcW) {
			$workSize = ($srcH - $srcW);
			$startY = ($this->floor) ? floor($workSize / 2) : ceil($workSize / 2);
			$srcH = ($srcW > $size) ? $srcH - $workSize : $srcW;
		} elseif ($srcW > $size || $srcH > $size) {
			list($dstW, $dstH) = $this->getSize($srcW, $srcH, $size, $size, $this->floor);
		}
		return $this->createImage(0, 0, $startX, $startY, $dstW, $dstH, $srcW, $srcH);
	}

	/**
	 * X座標、Y座標、横幅、高さを指定して、切り抜き画像を生成します。
	 *
	 * @param int 切り抜きを開始するX座標
	 * @param int 切り抜きを開始するY座標
	 * @param int 横幅
	 * @param int 高さ
	 * @return object Acme\Thumbnail\Image リサイズした画像
	 */
	public function clip($startX, $startY, $width, $height)
	{
		$srcW = $this->width;
		$srcH = $this->height;
		if ($width > $srcW) {
			$width = $srcW;
		}
		if ($height > $srcH) {
			$height = $srcH;
		}
		if (($startX + $width) > $srcW) {
			$startX = $srcW - $width;
		}
		if (($startY + $height) > $srcH) {
			$startY = $srcH - $height;
		}
		if ($startX < 0) {
			$startX = 0;
		}
		if ($startY < 0) {
			$startY = 0;
		}
		return $this->createImage(0, 0, $startX, $startY, $width, $height, $width, $height);
	}

	/**
	 * 最大値を指定して、横幅と高さの数値を算出します。
	 *
	 * @param int 横幅
	 * @param int 高さ
	 * @param int 横幅最大値
	 * @param int 高さ最大値
	 * @param bool 端数を切り上げるかどうか
	 * @return array 要素0に横幅、要素1に高さの値を格納した配列
	 */
	public function getSize($width, $height, $maxWidth, $maxHeight, $floor = true)
	{
		$size = array();
		$wpercent = (100 * $maxWidth) / $width;
		$hpercent = (100 * $maxHeight) / $height;
		if ($wpercent < $hpercent) {
			$size[0] = $maxWidth;
			$size[1] = (int)max(1, ($floor) ? floor(($height * $wpercent) / 100) : ceil(($height * $wpercent) / 100));
		} else {
			$size[0] = (int)max(1, ($floor) ? floor(($width  * $hpercent) / 100) : ceil(($width  * $hpercent) / 100));
			$size[1] = $maxHeight;
		}
		return $size;
	}

	/**
	 * 倍率を指定して、横幅と高さの数値を算出します
	 *
	 * @param int 横幅
	 * @param int 高さ
	 * @param int 倍率
	 * @param bool 端数を切り上げるかどうか
	 * @return array 要素0に横幅、要素1に高さの値を格納した配列
	 */
	public function getSizeByPercent($width, $height, $percent, $floor = true)
	{
		$size = array();
		$size[0] = max(1, ($floor) ? floor(($width  * $percent) / 100) : ceil(($width  * $percent) / 100));
		$size[1] = max(1, ($floor) ? floor(($height * $percent) / 100) : ceil(($height * $percent) / 100));
		return $size;
	}

	/**
	 * 指定したタイプのGDイメージを出力します。
	 *
	 * @param string 出力画像ファイルパス。省略時は標準出力
	 * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
	 * @param int 画像の品質 (JPEGのみ有効)
	 * @return bool 実行結果
	 */
	public function dataUri()
	{
		$tempPath = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid('', true));
		$this->output($tempPath, IMAGETYPE_PNG);
		$mimeTypeFrom = new \finfo(FILEINFO_MIME_TYPE);
		$uri = sprintf('data:%s;base64,%s', $mimeTypeFrom->file($tempPath), base64_encode(file_get_contents($tempPath)));
		unlink($tempPath);
		return $uri;
	}

	/**
	 * 指定したタイプのGDイメージを出力します。
	 *
	 * @param string 出力画像ファイルパス。省略時は標準出力
	 * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
	 * @param int 画像の品質 (JPEGのみ有効)
	 * @return bool 実行結果
	 */
	public function output($path = null, $type = null, $quality = null)
	{
		if ($type === null) {
			$type = $this->type;
		}
		if ($path === null) {
			$this->outputHeader($type);
		}
		switch ($type) {
		case IMAGETYPE_GIF:
		case 'gif':
			return ($path !== null)
				? imagegif($this->resource, $path)
				: imagegif($this->resource);
		case IMAGETYPE_JPEG:
		case 'jpeg':
			if ($quality === null) {
				return ($path !== null)
					? imagejpeg($this->resource, $path)
					: imagejpeg($this->resource);
			}
			return imagejpeg($this->resource, ($path !== null) ? $path : null, $quality);
		case IMAGETYPE_PNG:
		case 'png':
			return ($path !== null)
				? imagepng($this->resource, $path)
				: imagepng($this->resource);
		}
		return false;
	}

	/**
	 * リサイズする画像の座標・横幅・高さを指定して、リサイズ画像を生成します。
	 *
	 * @param int リサイズ先のX座標
	 * @param int リサイズ先のY座標
	 * @param int リサイズ元のX座標
	 * @param int リサイズ元のY座標
	 * @param int リサイズ先の横幅
	 * @param int リサイズ先の高さ
	 * @param int リサイズ元の横幅
	 * @param int リサイズ元の高さ
	 */
	private function createImage($dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
	{
		$srcR = $this->resource;
		$dstR = imagecreatetruecolor($dstW, $dstH);
		$function = 'imagecopyresampled';
		switch ($this->type) {
		case IMAGETYPE_GIF:
			$colorIndex = imagecolortransparent($srcR);
			if ($colorIndex >= 0) {
				$colors = imagecolorsforindex($srcR, $colorIndex);
				$color = imagecolorallocate($dstR, $colors['red'], $colors['green'], $colors['blue']);
				imagepalettecopy($dstR, $srcR);
				imagefill($dstR, $dstX, $dstY, $color);
				imagecolortransparent($dstR, $color);
			}
			break;
		case IMAGETYPE_PNG:
			imagealphablending($dstR, false);
			$transparentColor = imagecolorallocatealpha($dstR, 0, 0, 0, 127);
			imagepalettecopy($dstR, $srcR);
			imagefill($dstR, $dstX, $dstY, $transparentColor);
			imagesavealpha($dstR, true);
			$function = 'imagecopyresized';
			break;
		case IMAGETYPE_JPEG:
		default:
			break;
		}
		$result = $function($dstR, $srcR, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		if ($result === true) {
			return new self(array(
				'resource' => $dstR,
			));
		}
		throw new \RuntimeException('Could not create GD resource.');
	}

	private function validResource($resource)
	{
		return (is_resource($resource) && strcmp('gd', get_resource_type($resource)) === 0);
	}

	private function initializeByPath($path)
	{
		$imageInfo = @getimagesize($path);
		if (!is_array($imageInfo)) {
			throw new \InvalidArgumentException('Invalid image path.');
		}
		if ($imageInfo[2] !== IMAGETYPE_GIF && $imageInfo[2] !== IMAGETYPE_JPEG && $imageInfo[2] !== IMAGETYPE_PNG) {
			throw new \InvalidArgumentException('Unsupported file type.');
		}
		$this->data = file_get_contents($path);
		$this->path = $path;
		$this->width = $imageInfo[0];
		$this->height = $imageInfo[1];
		$this->type = $imageInfo[2];
		$resource = imagecreatefromstring($this->data);
		if (!$this->validResource($resource)) {
			throw new \InvalidArgumentException('Could not create GD resource.');
		}
		$this->resource = $resource;
	}

	private function initializeByData($data)
	{
		$imageInfo = (function_exists('getimagesizefromstring'))
			? @getimagesizefromstring($data)
			: @getimagesize(sprintf('data://application/octet-stream;base64,%s', base64_encode($data)));
		if (!is_array($imageInfo)) {
			throw new \InvalidArgumentException('Invalid image data.');
		}
		if ($imageInfo[2] !== IMAGETYPE_GIF && $imageInfo[2] !== IMAGETYPE_JPEG && $imageInfo[2] !== IMAGETYPE_PNG) {
			throw new \InvalidArgumentException('Unsupported file type.');
		}
		$this->data = $data;
		$this->width = $imageInfo[0];
		$this->height = $imageInfo[1];
		$this->type = $imageInfo[2];
		$resource = imagecreatefromstring($this->data);
		if (!$this->validResource($resource)) {
			throw new \InvalidArgumentException('Could not create GD resource.');
		}
		$this->resource = $resource;
	}

	private function initializeByResource($resource)
	{
		if (!$this->validResource($resource)) {
			throw new \InvalidArgumentException('Invalid GD resource.');
		}
		$this->width = imagesx($resource);
		$this->height = imagesy($resource);
		$this->resource = $resource;
	}

	private function outputHeader($type)
	{
		$mimeType = null;
		if (function_exists('image_type_to_mime_type')) {
			$mimeType = image_type_to_mime_type($type);
		} else {
			switch ($type) {
			case IMAGETYPE_GIF:
				$mimeType = 'image/gif';
				break;
			case IMAGETYPE_JPEG:
				$mimeType = 'image/jpeg';
				break;
			case IMAGETYPE_PNG:
				$mimeType = 'image/png';
				break;
			default:
				break;
			}
		}
		if ($mimeType !== null) {
			header('Content-type: ' . $mimeType);
		}
	}

}
