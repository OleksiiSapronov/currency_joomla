<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Css\Sprite\Handler;

\defined('_JCH_EXEC') or exit('Restricted access');
class Gd extends \JchOptimize\Core\Css\Sprite\Handler\AbstractHandler
{
    public function getSupportedFormats(): array
    {
        // get info about installed GD library to get image types (some versions of GD don't include GIF support)
        $oGD = \gd_info();
        $imageTypes = [];
        // store supported formats for populating drop downs etc later
        if (isset($oGD['PNG Support'])) {
            $imageTypes[] = 'PNG';
            $this->spriteFormats[] = 'PNG';
        }
        if (isset($oGD['GIF Create Support'])) {
            $imageTypes[] = 'GIF';
        }
        if (isset($oGD['JPG Support']) || isset($oGD['JPEG Support'])) {
            $imageTypes[] = 'JPG';
        }

        return $imageTypes;
    }

    /**
     * @param mixed $spriteWidth
     * @param mixed $spriteHeight
     * @param mixed $bgColour
     * @param mixed $outputFormat
     *
     * @return false|resource
     */
    public function createSprite($spriteWidth, $spriteHeight, $bgColour, $outputFormat)
    {
        if ($this->options['is-transparent'] && !empty($this->options['background'])) {
            $oSprite = \imagecreate($spriteWidth, $spriteHeight);
        } else {
            $oSprite = \imagecreatetruecolor($spriteWidth, $spriteHeight);
        }
        // check for transparency option
        if ($this->options['is-transparent']) {
            if ('png' == $outputFormat) {
                \imagealphablending($oSprite, \false);
                $colorTransparent = \imagecolorallocatealpha($oSprite, 0, 0, 0, 127);
                \imagefill($oSprite, 0, 0, $colorTransparent);
                \imagesavealpha($oSprite, \true);
            } elseif ('gif' == $outputFormat) {
                $iBgColour = \imagecolorallocate($oSprite, 0, 0, 0);
                \imagecolortransparent($oSprite, $iBgColour);
            }
        } else {
            if (empty($bgColour)) {
                $bgColour = 'ffffff';
            }
            $iBgColour = \hexdec($bgColour);
            $iBgColour = \imagecolorallocate($oSprite, 0xFF & $iBgColour >> 0x10, 0xFF & $iBgColour >> 0x8, 0xFF & $iBgColour);
            \imagefill($oSprite, 0, 0, $iBgColour);
        }

        return $oSprite;
    }

    /**
     * @param mixed $fileInfos
     *
     * @return false|resource
     */
    public function createBlankImage($fileInfos)
    {
        $oCurrentImage = \imagecreatetruecolor($fileInfos['original-width'], $fileInfos['original-height']);
        \imagecolorallocate($oCurrentImage, 255, 255, 255);

        return $oCurrentImage;
    }

    /**
     * @param mixed $spriteObject
     * @param mixed $currentImage
     * @param mixed $fileInfos
     */
    public function resizeImage($spriteObject, $currentImage, $fileInfos)
    {
        \imagecopyresampled($spriteObject, $currentImage, $fileInfos['x'], $fileInfos['y'], 0, 0, $fileInfos['width'], $fileInfos['height'], $fileInfos['original-width'], $fileInfos['original-height']);
    }

    /**
     * @param mixed $spriteObject
     * @param mixed $currentImage
     * @param mixed $fileInfos
     * @param mixed $resize
     */
    public function copyImageToSprite($spriteObject, $currentImage, $fileInfos, $resize)
    {
        // if already resized the image will have been copied as part of the resize
        if (!$resize) {
            \imagecopy($spriteObject, $currentImage, $fileInfos['x'], $fileInfos['y'], 0, 0, $fileInfos['width'], $fileInfos['height']);
        }
    }

    /**
     * @param mixed $imageObject
     */
    public function destroy($imageObject)
    {
        \imagedestroy($imageObject);
    }

    /**
     * @param mixed $fileInfos
     *
     * @return false|resource
     */
    public function createImage($fileInfos)
    {
        $sFile = $fileInfos['path'];

        switch ($fileInfos['ext']) {
            case 'jpg':
            case 'jpeg':
                $oImage = @\imagecreatefromjpeg($sFile);

                break;

            case 'gif':
                $oImage = @\imagecreatefromgif($sFile);

                break;

            case 'png':
                $oImage = @\imagecreatefrompng($sFile);

                break;

            default:
                $oImage = @\imagecreatefromstring($sFile);
        }

        return $oImage;
    }

    /**
     * @param mixed $imageObject
     * @param mixed $extension
     * @param mixed $fileName
     */
    public function writeImage($imageObject, $extension, $fileName)
    {
        // check if we want to resample image to lower number of colours (to reduce file size)
        if (\in_array($extension, ['gif', 'png']) && 'true-colour' != $this->options['image-num-colours']) {
            \imagetruecolortopalette($imageObject, \true, $this->options['image-num-colours']);
        }

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                // GD takes quality setting in main creation function
                \imagejpeg($imageObject, $fileName, $this->options['image-quality']);

                break;

            case 'gif':
                // force colour palette to 256 colours if saving sprite image as GIF
                // this will happen anyway (as GIFs can't be more than 256 colours)
                // but the quality will be better if pre-forcing
                if ($this->options['is-transparent'] && (-1 == $this->options['image-num-colours'] || $this->options['image-num-colours'] > 256 || 'true-colour' == $this->options['image-num-colours'])) {
                    \imagetruecolortopalette($imageObject, \true, 256);
                }
                \imagegif($imageObject, $fileName);

                break;

            case 'png':
                \imagepng($imageObject, $fileName, 0);

                break;
        }
    }
}
