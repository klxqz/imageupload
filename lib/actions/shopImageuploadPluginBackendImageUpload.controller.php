<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopImageuploadPluginBackendImageUploadController extends waJsonController {

    public function execute() {
        try {
            $product_id = waRequest::post('product_id', null, waRequest::TYPE_INT);
            if (empty($product_id)) {
                throw new waException("Не определен идентификатор товара");
            }
            $image_url = waRequest::post('image_url');
            if (!trim($image_url)) {
                throw new waException("Не указана ссылка на изображение");
            }

            $u = @parse_url($image_url);

            if (!$u || !(isset($u['scheme']) && isset($u['host']) && isset($u['path']))) {
                throw new waException("Некорректная ссылка на изображение");
            } elseif (in_array($u['scheme'], array('http', 'https', 'ftp', 'ftps'))) {
                
            } else {
                throw new waException("Неподдерживаемый файловый протокол " . $u['scheme']);
            }

            $name = preg_replace('@[^a-zA-Zа-яА-Я0-9\._\-]+@', '', basename(urldecode($image_url)));
            waFiles::upload($image_url, $file_path = wa()->getCachePath('plugins/imageupload/' . waLocale::transliterate($name, 'en_US')));

            if (!file_exists($file_path)) {
                throw new waException("Ошибка загрузки файла");
            }

            // check image
            if (!($image = waImage::factory($file_path))) {
                throw new waException('Incorrect image');
            }

            $image_changed = false;

            /**
             * Extend upload proccess
             * Make extra workup
             * @event image_upload
             */
            $event = wa()->event('image_upload', $image);
            if ($event) {
                foreach ($event as $plugin_id => $result) {
                    if ($result) {
                        $image_changed = true;
                    }
                }
            }

            if ($this->getConfig()->getOption('image_filename')) {
                $filename = basename($file_path);
                if (!preg_match('//u', $filename)) {
                    $tmp_name = @iconv('windows-1251', 'utf-8//ignore', $filename);
                    if ($tmp_name) {
                        $filename = $tmp_name;
                    }
                }
                $filename = preg_replace('/\s+/u', '_', $filename);
                if ($filename) {
                    foreach (waLocale::getAll() as $l) {
                        $filename = waLocale::transliterate($filename, $l);
                    }
                }
                $filename = preg_replace('/[^a-zA-Z0-9_\.-]+/', '', $filename);
                if (!strlen(str_replace('_', '', $filename))) {
                    $filename = '';
                }
            } else {
                $filename = '';
            }

            $data = array(
                'product_id' => $product_id,
                'upload_datetime' => date('Y-m-d H:i:s'),
                'width' => $image->width,
                'height' => $image->height,
                'size' => filesize($file_path),
                'filename' => $filename,
                'original_filename' => basename($file_path),
                'ext' => pathinfo($file_path, PATHINFO_EXTENSION),
            );
            $model = new shopProductImagesModel();
            $image_id = $data['id'] = $model->add($data);

            if (!$image_id) {
                throw new waException("Database error");
            }

            /**
             * @var shopConfig $config
             */
            $config = $this->getConfig();

            $image_path = shopImage::getPath($data);
            if ((file_exists($image_path) && !is_writable($image_path)) || (!file_exists($image_path) && !waFiles::create($image_path))) {
                $this->model->deleteById($image_id);
                throw new waException(
                sprintf("The insufficient file write permissions for the %s folder.", substr($image_path, strlen($config->getRootPath()))
                ));
            }

            if ($image_changed) {
                $image->save($image_path);

                // save original
                $original_file = shopImage::getOriginalPath($data);
                if ($config->getOption('image_save_original') && $original_file) {
                    $file->moveTo($original_file);
                }
            } else {
                waFiles::copy($file_path, $image_path);
            }
            unset($image);        // free variable

            shopImage::generateThumbs($data, $config->getImageSizes());

            $this->response['files'][] = array(
                'id' => $image_id,
                'name' => $name,
                'type' => waFiles::getMimeType($file_path),
                'size' => filesize($file_path),
                'url_thumb' => shopImage::getUrl($data, $config->getImageSize('thumb')),
                'url_crop' => shopImage::getUrl($data, $config->getImageSize('crop')),
                'url_crop_small' => shopImage::getUrl($data, $config->getImageSize('crop_small')),
                'description' => ''
            );


            $this->response['message'] = "Изображение успешно загружено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
