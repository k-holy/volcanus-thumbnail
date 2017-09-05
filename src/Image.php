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

    const ORIENTATION_UNKNOWN = 0;
    const ORIENTATION_TOPLEFT = 1;
    const ORIENTATION_TOPRIGHT = 2;
    const ORIENTATION_BOTTOMRIGHT = 3;
    const ORIENTATION_BOTTOMLEFT = 4;
    const ORIENTATION_LEFTTOP = 5;
    const ORIENTATION_RIGHTTOP = 6;
    const ORIENTATION_RIGHTBOTTOM = 7;
    const ORIENTATION_LEFTBOTTOM = 8;

    /**
     * ファイルパス
     *
     * @var string
     */
    private $path = null;

    /**
     * データ
     *
     * @var string
     */
    private $data = null;

    /**
     * GDイメージリソース
     *
     * @var resource
     */
    private $resource = null;

    /**
     * タイプ IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG
     *
     * @var int
     */
    private $type = null;

    /**
     * 横幅
     *
     * @var int
     */
    private $width = null;

    /**
     * 高さ
     *
     * @var array
     */
    private $height = null;

    /**
     * 端数を切り上げるかどうか
     *
     * @var bool
     */
    private $floor = true;

    /**
     * コンストラクタ
     *
     * @param array | ArrayAccess 設定オプション
     */
    public function __construct($configurations = [])
    {
        if (!function_exists('gd_info')) {
            throw new \RuntimeException(
                'Required GD Library.'
            );
        }
        $this->initialize($configurations);
    }

    /**
     * 複製したオブジェクトを返します。
     */
    public function __clone()
    {
        $original = $this->resource;
        $copy = imagecreatetruecolor($this->width, $this->height);
        imagecopy($copy, $original, 0, 0, 0, 0, $this->width, $this->height);
        $this->resource = $copy;
    }

    /**
     * オブジェクトを初期化します。
     *
     * @param array | ArrayAccess 設定オプション
     */
    public function initialize($configurations = [])
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
                    case 'type':
                        if (!is_int($value)) {
                            throw new \InvalidArgumentException(
                                sprintf('The config %s only accepts integer. type:%s', $name, (is_object($value))
                                    ? get_class($value) : gettype($value)
                                )
                            );
                        }
                        if (!$this->supportedType($value)) {
                            throw new \InvalidArgumentException(
                                sprintf('The config %s is unsupported type.', $name)
                            );
                        }
                        $this->type = $value;
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
        $this->floor = true;
        $this->destroy();
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
     * 画像の種別を返します。
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
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
    public function resize($maxWidth, $maxHeight = null)
    {
        $srcW = $this->width;
        $srcH = $this->height;
        if (empty($maxHeight)) {
            $maxHeight = $maxWidth;
        }
        $dstW = $srcW;
        $dstH = $srcH;
        if ((!empty($maxWidth) && $srcW > $maxWidth) || (!empty($maxHeight) && $srcH > $maxHeight)) {
            list($dstW, $dstH) = $this->getSize($srcW, $srcH, $maxWidth, $maxHeight, $this->floor);
        }
        if ($srcW === $dstW && $srcH === $dstH) {
            return $this;
        }
        return $this->transform(0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
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
        return $this->transform(0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
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
            $srcW = ($srcH > $size) ? $srcW - $workSize : $srcH;
        } elseif ($srcH > $srcW) {
            $workSize = ($srcH - $srcW);
            $startY = ($this->floor) ? floor($workSize / 2) : ceil($workSize / 2);
            $srcH = ($srcW > $size) ? $srcH - $workSize : $srcW;
        } elseif ($srcW > $size || $srcH > $size) {
            list($dstW, $dstH) = $this->getSize($srcW, $srcH, $size, $size, $this->floor);
        }
        return $this->transform(0, 0, $startX, $startY, $dstW, $dstH, $srcW, $srcH);
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
        return $this->transform(0, 0, $startX, $startY, $width, $height, $width, $height);
    }

    /**
     * 上下反転した画像を返します。
     *
     * @return object Acme\Thumbnail\Image 上下反転した画像
     */
    public function flip()
    {
        if (function_exists('imageflip')) {
            imageflip($this->resource, IMG_FLIP_VERTICAL);
            return $this;
        }
        $width = $this->width;
        $height = $this->height;
        return $this->transform(0, 0, 0, $height, $width, $height, $width, -$height);
    }

    /**
     * 左右反転した画像を返します。
     *
     * @return object Acme\Thumbnail\Image 左右反転した画像
     */
    public function flop()
    {
        if (function_exists('imageflip')) {
            imageflip($this->resource, IMG_FLIP_HORIZONTAL);
            return $this;
        }
        $width = $this->width;
        $height = $this->height;
        return $this->transform(0, 0, $width, 0, $width, $height, -$width, $height);
    }

    /**
     * 画像を回転して返します。
     *
     * @param float 角度
     * @param int 背景色
     * @param int 透過色を無視するかどうか
     * @return object Acme\Thumbnail\Image 回転した画像
     */
    public function rotate($angle, $backgroundColor = 0, $ignoreTransparent = 0)
    {
        $image = new self([
            'resource' => imagerotate($this->resource, $angle, $backgroundColor, $ignoreTransparent),
            'type' => $this->type,
        ]);
        $this->destroy();
        return $image;
    }

    /**
     * Exif情報のOrientation値を元に画像を回転します。
     *
     * @param int Orientation値 (0-8)
     * @return object Acme\Thumbnail\Image 回転した画像
     */
    public function rotateByOrientation($orientation)
    {
        switch ($orientation) {
            // 0 Androidのカメラアプリが0を返すので…
            case self::ORIENTATION_UNKNOWN:
                // 1
            case self::ORIENTATION_TOPLEFT:
                return $this;
            // 2
            case self::ORIENTATION_TOPRIGHT:
                return $this->flop();
            // 3
            case self::ORIENTATION_BOTTOMRIGHT:
                return $this->rotate(180);
            // 4
            case self::ORIENTATION_BOTTOMLEFT:
                return $this->flip();
            // 5
            case self::ORIENTATION_LEFTTOP:
                return $this->rotate(270)->flop();
            // 6
            case self::ORIENTATION_RIGHTTOP:
                return $this->rotate(270);
            // 7
            case self::ORIENTATION_RIGHTBOTTOM:
                return $this->rotate(90)->flop();
            // 8
            case self::ORIENTATION_LEFTBOTTOM:
                return $this->rotate(90);
        }
        throw new \InvalidArgumentException(
            sprintf('Could not rotate by orientation "%s"', $orientation)
        );
    }

    /**
     * バイナリを文字列で返します。
     *
     * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @return string バイナリ文字列
     */
    public function binary($type = null)
    {
        if ($type === null) {
            $type = ($this->type === null) ? IMAGETYPE_PNG : $this->type;
        }
        ob_start();
        $this->output(null, $type);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
     * BASE64エンコード文字列で返します。
     *
     * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @return string BASE64エンコード文字列
     */
    public function base64Encode($type = null)
    {
        return base64_encode($this->binary($type));
    }

    /**
     * DataURIを返します。
     *
     * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @return string DataURI
     */
    public function dataUri($type = null)
    {
        return $this->buildDataUri($this->binary($type), image_type_to_mime_type($type));
    }

    /**
     * 指定したタイプのContent-Typeヘッダを返します。
     *
     * @param int 画像ファイルのフォーマット定数 (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @return string Content-Typeヘッダ
     */
    public function contentTypeHeader($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        return sprintf('Content-Type: %s', image_type_to_mime_type($type));
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
        if ($path instanceof \SplFileInfo) {
            $path = $path->__toString();
        }
        switch ($type) {
            case IMAGETYPE_GIF:
                if ($path !== null) {
                    imagegif($this->resource, $path);
                } else {
                    imagegif($this->resource);
                }
                return $this;
            case IMAGETYPE_JPEG:
                if ($quality === null) {
                    if ($path !== null) {
                        imagejpeg($this->resource, $path);
                    } else {
                        imagejpeg($this->resource);
                    }
                } else {
                    imagejpeg($this->resource, ($path !== null) ? $path : null, $quality);
                }
                return $this;
            case IMAGETYPE_PNG:
                if ($path !== null) {
                    imagepng($this->resource, $path);
                } else {
                    imagepng($this->resource);
                }
                return $this;
        }
        throw new \InvalidArgumentException('Unsupported image type.');
    }

    /**
     * GDイメージリソースを破棄します。
     */
    public function destroy()
    {
        if ($this->validResource($this->resource)) {
            imagedestroy($this->resource);
        }
        $this->resource = null;
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
    private function getSize($width, $height, $maxWidth, $maxHeight, $floor = true)
    {
        $size = [];
        $wpercent = (100 * $maxWidth) / $width;
        $hpercent = (100 * $maxHeight) / $height;
        if ($wpercent < $hpercent) {
            $size[0] = $maxWidth;
            $size[1] = (int)max(1, ($floor) ? floor(($height * $wpercent) / 100) : ceil(($height * $wpercent) / 100));
        } else {
            $size[0] = (int)max(1, ($floor) ? floor(($width * $hpercent) / 100) : ceil(($width * $hpercent) / 100));
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
    private function getSizeByPercent($width, $height, $percent, $floor = true)
    {
        $size = [];
        $size[0] = max(1, ($floor) ? floor(($width * $percent) / 100) : ceil(($width * $percent) / 100));
        $size[1] = max(1, ($floor) ? floor(($height * $percent) / 100) : ceil(($height * $percent) / 100));
        return $size;
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
    private function transform($dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
    {
        $srcR = $this->resource;
        $dstR = imagecreatetruecolor($dstW, $dstH);
        switch ($this->type) {
            case IMAGETYPE_GIF:
                $colorIndex = imagecolortransparent($srcR);
                if ($colorIndex >= 0) {
                    $colors = imagecolorsforindex($srcR, $colorIndex);
                    $transparent = imagecolorallocate($dstR, $colors['red'], $colors['green'], $colors['blue']);
                    imagepalettecopy($dstR, $srcR);
                    imagefill($dstR, $dstX, $dstY, $transparent);
                    imagecolortransparent($dstR, $transparent);
                }
                break;
            case IMAGETYPE_PNG:
                imagealphablending($dstR, false);
                $transparentColor = imagecolorallocatealpha($dstR, 0, 0, 0, 127);
                imagepalettecopy($dstR, $srcR);
                imagefill($dstR, $dstX, $dstY, $transparentColor);
                imagesavealpha($dstR, true);
                break;
            case IMAGETYPE_JPEG:
            default:
                break;
        }
        $result = imagecopyresampled($dstR, $srcR, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        if ($result === true) {
            $this->destroy();
            return new self([
                'resource' => $dstR,
                'type' => $this->type,
            ]);
        }
        throw new \RuntimeException('Could not create GD resource.');
    }

    private function validResource($resource)
    {
        return (is_resource($resource) && strcmp('gd', get_resource_type($resource)) === 0);
    }

    private function initializeByPath($path)
    {
        if ($path instanceof \SplFileInfo) {
            $path = $path->__toString();
        }
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
        if ($this->type === null) {
            $this->type = $imageInfo[2];
        }
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
            : @getimagesize($this->buildDataUri($data));
        if (!is_array($imageInfo)) {
            throw new \InvalidArgumentException('Invalid image data.');
        }
        if ($imageInfo[2] !== IMAGETYPE_GIF && $imageInfo[2] !== IMAGETYPE_JPEG && $imageInfo[2] !== IMAGETYPE_PNG) {
            throw new \InvalidArgumentException('Unsupported file type.');
        }
        $this->data = $data;
        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
        if ($this->type === null) {
            $this->type = $imageInfo[2];
        }
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

    private function buildDataUri($data, $mimeType = null)
    {
        return sprintf('data:%s;base64,%s',
            ($mimeType === null) ? 'application/octet-stream' : $mimeType,
            base64_encode($data)
        );
    }

    private function supportedType($type)
    {
        $_type = 0;
        switch ($type) {
            case IMAGETYPE_GIF:
                $_type = IMG_GIF;
                break;
            case IMAGETYPE_JPEG:
                $_type = IMG_JPG;
                break;
            case IMAGETYPE_PNG:
                $_type = IMG_PNG;
                break;
            case IMAGETYPE_WBMP:
                $_type = IMG_WBMP;
                break;
        }
        return (($_type & imagetypes()) !== 0);
    }

}
