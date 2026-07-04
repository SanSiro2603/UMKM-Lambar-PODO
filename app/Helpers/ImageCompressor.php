<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageCompressor
{
    /**
     * Compress an uploaded image file in place at its temporary path.
     *
     * Supports both standard UploadedFile and Livewire TemporaryUploadedFile.
     *
     * @param mixed $file The uploaded file instance
     * @param int $maxWidth Maximum width of the image (default: 1200)
     * @param int $maxHeight Maximum height of the image (default: 1200)
     * @param int $quality Compression quality (1-100, default: 75)
     * @return bool True on success, false on failure
     */
    public static function compress($file, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 75): bool
    {
        $filePath = $file->getRealPath();
        return static::compressPath($filePath, $maxWidth, $maxHeight, $quality);
    }

    /**
     * Compress an image file at the given absolute filesystem path in-place.
     *
     * @param string $filePath Absolute path to the image file
     * @param int $maxWidth Maximum width of the image (default: 1200)
     * @param int $maxHeight Maximum height of the image (default: 1200)
     * @param int $quality Compression quality (1-100, default: 75)
     * @return bool True on success, false on failure
     */
    public static function compressPath(string $filePath, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 75): bool
    {
        if (!$filePath || !file_exists($filePath)) {
            return false;
        }

        // Get image details
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // 1. Load the image resource based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($filePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($filePath);
                break;
            default:
                return false; // Unsupported image type
        }

        if (!$image) {
            return false;
        }

        // 2. Handle EXIF Orientation for JPEG images (auto-rotate if needed)
        if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
            $exif = @exif_read_data($filePath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $rotatedImage = @imagerotate($image, 180, 0);
                        if ($rotatedImage !== false) {
                            imagedestroy($image);
                            $image = $rotatedImage;
                        }
                        break;
                    case 6:
                        $rotatedImage = @imagerotate($image, -90, 0);
                        if ($rotatedImage !== false) {
                            imagedestroy($image);
                            $image = $rotatedImage;
                            // Swap dimensions
                            $temp = $width;
                            $width = $height;
                            $height = $temp;
                        }
                        break;
                    case 8:
                        $rotatedImage = @imagerotate($image, 90, 0);
                        if ($rotatedImage !== false) {
                            imagedestroy($image);
                            $image = $rotatedImage;
                            // Swap dimensions
                            $temp = $width;
                            $width = $height;
                            $height = $temp;
                        }
                        break;
                }
            }
        }

        // 3. Resize the image if dimensions exceed the maximum
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($maxWidth / $ratio);
            } else {
                $newHeight = $maxHeight;
                $newWidth = (int) ($maxHeight * $ratio);
            }

            // Create a new canvas with the resized dimensions
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG and WebP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparentColor = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparentColor);
            }

            // Copy and resample image onto the canvas
            if (imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
                imagedestroy($image);
                $image = $resizedImage;
            } else {
                imagedestroy($resizedImage);
            }
        }

        // 4. Save/Compress the image back to its original path
        $success = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = @imagejpeg($image, $filePath, $quality);
                break;
            case 'image/png':
                // GD imagepng expects compression level 0 (none) to 9 (maximum).
                $pngCompression = 9 - (int) (($quality / 100) * 9);
                $pngCompression = max(0, min(9, $pngCompression));
                
                // Disable alphablending to write transparency details correctly
                imagealphablending($image, false);
                imagesavealpha($image, true);
                
                $success = @imagepng($image, $filePath, $pngCompression);
                break;
            case 'image/webp':
                $success = @imagewebp($image, $filePath, $quality);
                break;
            case 'image/gif':
                $success = @imagegif($image, $filePath);
                break;
        }

        imagedestroy($image);

        // Force clearing statutory file system info to reflect new file size immediately
        clearstatcache(true, $filePath);

        return $success;
    }
}
