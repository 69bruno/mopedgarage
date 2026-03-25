<?php
namespace bruno\mopedgarage\service;

/**
 * Central image handling for the Mopedgarage extension.
 *
 * The service validates uploads, normalises images, creates thumbnails
 * and removes original files plus thumbnails when an entry is deleted.
 */
class image_manager
{
    /** @var string */
    protected $root_path;

    public function __construct($root_path)
    {
        $this->root_path = rtrim((string) $root_path, '/\\') . '/';
    }

    public function get_upload_dir_rel()
    {
        return 'images/mopedgarage';
    }

    public function get_upload_dir_abs()
    {
        return $this->root_path . $this->get_upload_dir_rel();
    }

    public function get_thumb_dir_abs()
    {
        return $this->get_upload_dir_abs() . '/thumbs';
    }

    public function ensure_directories()
    {
        $upload_dir = $this->get_upload_dir_abs();
        $thumb_dir = $this->get_thumb_dir_abs();

        if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir))
        {
            return false;
        }

        if (!is_dir($thumb_dir) && !@mkdir($thumb_dir, 0755, true) && !is_dir($thumb_dir))
        {
            return false;
        }

        return is_dir($upload_dir)
            && is_writable($upload_dir)
            && is_dir($thumb_dir)
            && is_writable($thumb_dir);
    }

    public function get_capabilities()
    {
        return [
            'gd' => function_exists('gd_info') && function_exists('imagecreatetruecolor'),
            'exif' => function_exists('exif_read_data'),
            'webp' => function_exists('imagecreatefromwebp') && function_exists('imagewebp'),
        ];
    }

    public function is_environment_ready()
    {
        $capabilities = $this->get_capabilities();
        return $capabilities['gd'];
    }

    public function is_upload_dir_ready()
    {
        $upload_dir = $this->get_upload_dir_abs();
        $thumb_dir = $this->get_thumb_dir_abs();

        return is_dir($upload_dir)
            && is_writable($upload_dir)
            && is_dir($thumb_dir)
            && is_writable($thumb_dir);
    }

    public function get_image_urls($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '')
        {
            return ['image_url' => '', 'thumb_url' => '', 'has_image' => false];
        }

        $image_url = generate_board_url() . '/' . $this->get_upload_dir_rel() . '/' . rawurlencode($filename);
        $thumb_abs = $this->get_thumb_dir_abs() . '/' . $filename;
        $thumb_url = is_file($thumb_abs)
            ? generate_board_url() . '/' . $this->get_upload_dir_rel() . '/thumbs/' . rawurlencode($filename)
            : '';

        return [
            'image_url' => $image_url,
            'thumb_url' => $thumb_url,
            'has_image' => true,
        ];
    }

    public function handle_uploaded_image($file, $user_id, $slot_index, $max_filesize_kb, $max_width, $max_height, $user)
    {
        if (empty($file) || !isset($file['error']))
        {
            return ['filename' => '', 'error' => ''];
        }

        if ((int) $file['error'] === UPLOAD_ERR_NO_FILE)
        {
            return ['filename' => '', 'error' => ''];
        }

        if (!$this->is_environment_ready())
        {
            return ['filename' => '', 'error' => $user->lang('UCP_MOPEDGARAGE_UPLOAD_GD_MISSING')];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK)
        {
            return ['filename' => '', 'error' => $user->lang('UCP_MOPEDGARAGE_UPLOAD_ERROR')];
        }

        $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        $original_name = isset($file['name']) ? (string) $file['name'] : '';
        $filesize = isset($file['size']) ? (int) $file['size'] : 0;

        if ($tmp_name === '' || $original_name === '' || !is_uploaded_file($tmp_name))
        {
            return ['filename' => '', 'error' => $user->lang('UCP_MOPEDGARAGE_UPLOAD_ERROR')];
        }

        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowed_extensions, true))
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_INVALID_FOR_FILE'), htmlspecialchars($original_name))];
        }

        if ($extension === 'webp' && !$this->get_capabilities()['webp'])
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_WEBP_UNAVAILABLE_FOR_FILE'), htmlspecialchars($original_name))];
        }

        if ($filesize <= 0)
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_FAILED_FOR_FILE'), htmlspecialchars($original_name))];
        }

        $image_info = @getimagesize($tmp_name);
        if ($image_info === false || empty($image_info[0]) || empty($image_info[1]))
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_NOT_IMAGE'), htmlspecialchars($original_name))];
        }

        $mime = !empty($image_info['mime']) ? strtolower((string) $image_info['mime']) : '';
        $allowed_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        if ($mime === '' || !isset($allowed_mimes[$extension]) || $allowed_mimes[$extension] !== $mime)
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_INVALID_FOR_FILE'), htmlspecialchars($original_name))];
        }

        if (!$this->ensure_directories() || !$this->is_upload_dir_ready())
        {
            return ['filename' => '', 'error' => $user->lang('UCP_MOPEDGARAGE_UPLOAD_DIR_ERROR')];
        }

        $new_extension = ($extension === 'jpeg') ? 'jpg' : $extension;
        try
        {
            $random = bin2hex(random_bytes(16));
        }
        catch (\Exception $e)
        {
            $random = sha1(uniqid('', true) . mt_rand());
        }

        $new_name = sprintf('%d_%d_%s.%s', (int) $user_id, (int) $slot_index + 1, $random, $new_extension);
        $target_abs = $this->get_upload_dir_abs() . '/' . $new_name;

        $src = $this->create_image_resource($tmp_name, $extension);
        if (!$src)
        {
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_FAILED_FOR_FILE'), htmlspecialchars($original_name))];
        }

        $src = $this->apply_exif_orientation($src, $tmp_name, $extension);
        $src_width = imagesx($src);
        $src_height = imagesy($src);

        if ($src_width <= 0 || $src_height <= 0)
        {
            imagedestroy($src);
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_NOT_IMAGE'), htmlspecialchars($original_name))];
        }

        $normalized = $this->resize_resource_to_fit($src, $src_width, $src_height, $max_width, $max_height, $new_extension);
        if ($normalized !== $src)
        {
            imagedestroy($src);
        }
        $src = $normalized;

        $saved = $this->save_image_resource_with_limit($src, $target_abs, $new_extension, $max_filesize_kb);
        imagedestroy($src);

        if (!$saved)
        {
            @unlink($target_abs);
            return ['filename' => '', 'error' => sprintf($user->lang('UCP_MOPEDGARAGE_UPLOAD_TOO_LARGE_AFTER_PROCESSING'), htmlspecialchars($original_name), (int) $max_filesize_kb)];
        }

        @chmod($target_abs, 0644);
        $this->create_thumbnail($target_abs, $new_name, $new_extension, 640, 420);

        return ['filename' => $new_name, 'error' => ''];
    }

    public function delete_file($filename)
    {
        $filename = basename((string) $filename);
        if ($filename === '')
        {
            return;
        }

        $paths = [
            $this->get_upload_dir_abs() . '/' . $filename,
            $this->get_thumb_dir_abs() . '/' . $filename,
        ];

        foreach ($paths as $path)
        {
            if (is_file($path))
            {
                @unlink($path);
            }
        }
    }

    protected function create_image_resource($source, $extension)
    {
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($source);

            case 'png':
                return @imagecreatefrompng($source);

            case 'gif':
                return @imagecreatefromgif($source);

            case 'webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false;
        }

        return false;
    }

    protected function create_target_canvas($width, $height, $extension)
    {
        $dst = imagecreatetruecolor($width, $height);

        if (in_array($extension, ['png', 'gif', 'webp'], true))
        {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
        }

        return $dst;
    }

    protected function save_image_resource($resource, $target_abs, $extension, $quality = null)
    {
        switch ($extension)
        {
            case 'jpg':
            case 'jpeg':
                $quality = ($quality === null) ? 90 : max(25, min(95, (int) $quality));
                return @imagejpeg($resource, $target_abs, $quality);

            case 'png':
                $compression = ($quality === null) ? 9 : max(0, min(9, (int) $quality));
                return @imagepng($resource, $target_abs, $compression);

            case 'gif':
                return @imagegif($resource, $target_abs);

            case 'webp':
                $quality = ($quality === null) ? 90 : max(25, min(95, (int) $quality));
                return function_exists('imagewebp') ? @imagewebp($resource, $target_abs, $quality) : false;
        }

        return false;
    }

    protected function resize_resource_to_fit($resource, $src_width, $src_height, $max_width, $max_height, $extension)
    {
        $ratio = min($max_width / $src_width, $max_height / $src_height, 1);
        $target_width = max(1, (int) round($src_width * $ratio));
        $target_height = max(1, (int) round($src_height * $ratio));

        if ($target_width === $src_width && $target_height === $src_height)
        {
            return $resource;
        }

        $dst = $this->create_target_canvas($target_width, $target_height, $extension);
        imagecopyresampled($dst, $resource, 0, 0, 0, 0, $target_width, $target_height, $src_width, $src_height);

        return $dst;
    }

    protected function save_image_resource_with_limit($resource, $target_abs, $extension, $max_filesize_kb)
    {
        $max_bytes = max(1, (int) $max_filesize_kb * 1024);

        if (in_array($extension, ['jpg', 'jpeg', 'webp'], true))
        {
            $qualities = [90, 85, 80, 75, 70, 65, 60, 55, 50, 45, 40, 35, 30, 25];
            foreach ($qualities as $quality)
            {
                if ($this->save_image_resource($resource, $target_abs, $extension, $quality) && is_file($target_abs) && filesize($target_abs) <= $max_bytes)
                {
                    return true;
                }
            }

            return false;
        }

        if ($extension === 'png')
        {
            return $this->save_image_resource($resource, $target_abs, $extension, 9)
                && is_file($target_abs)
                && filesize($target_abs) <= $max_bytes;
        }

        return $this->save_image_resource($resource, $target_abs, $extension)
            && is_file($target_abs)
            && filesize($target_abs) <= $max_bytes;
    }

    protected function apply_exif_orientation($resource, $source, $extension)
    {
        if (!in_array($extension, ['jpg', 'jpeg'], true) || !function_exists('imagerotate') || !function_exists('exif_read_data'))
        {
            return $resource;
        }

        $exif = @exif_read_data($source);
        $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;

        switch ($orientation)
        {
            case 2:
                if (function_exists('imageflip'))
                {
                    imageflip($resource, IMG_FLIP_HORIZONTAL);
                }
            break;

            case 3:
                $rotated = imagerotate($resource, 180, 0);
                if ($rotated)
                {
                    imagedestroy($resource);
                    $resource = $rotated;
                }
            break;

            case 4:
                if (function_exists('imageflip'))
                {
                    imageflip($resource, IMG_FLIP_VERTICAL);
                }
            break;

            case 5:
                if (function_exists('imageflip'))
                {
                    imageflip($resource, IMG_FLIP_VERTICAL);
                }
                $rotated = imagerotate($resource, -90, 0);
                if ($rotated)
                {
                    imagedestroy($resource);
                    $resource = $rotated;
                }
            break;

            case 6:
                $rotated = imagerotate($resource, -90, 0);
                if ($rotated)
                {
                    imagedestroy($resource);
                    $resource = $rotated;
                }
            break;

            case 7:
                if (function_exists('imageflip'))
                {
                    imageflip($resource, IMG_FLIP_HORIZONTAL);
                }
                $rotated = imagerotate($resource, -90, 0);
                if ($rotated)
                {
                    imagedestroy($resource);
                    $resource = $rotated;
                }
            break;

            case 8:
                $rotated = imagerotate($resource, 90, 0);
                if ($rotated)
                {
                    imagedestroy($resource);
                    $resource = $rotated;
                }
            break;
        }

        return $resource;
    }

    protected function create_thumbnail($source_abs, $filename, $extension, $max_width, $max_height)
    {
        if (!is_file($source_abs))
        {
            return;
        }

        if (!$this->ensure_directories())
        {
            return;
        }

        $src = $this->create_image_resource($source_abs, $extension);
        if (!$src)
        {
            return;
        }

        $src_width = imagesx($src);
        $src_height = imagesy($src);
        if ($src_width <= 0 || $src_height <= 0)
        {
            imagedestroy($src);
            return;
        }

        $ratio = min($max_width / $src_width, $max_height / $src_height, 1);
        $thumb_width = max(1, (int) round($src_width * $ratio));
        $thumb_height = max(1, (int) round($src_height * $ratio));

        $dst = $this->create_target_canvas($thumb_width, $thumb_height, $extension);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $thumb_height, $src_width, $src_height);

        $thumb_abs = $this->get_thumb_dir_abs() . '/' . basename((string) $filename);
        $this->save_image_resource($dst, $thumb_abs, $extension);
        @chmod($thumb_abs, 0644);

        imagedestroy($src);
        imagedestroy($dst);
    }
}
