<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopImageuploadPluginBackendImageUploadController extends waJsonController {

    public function execute() {
        try {
            $config = $this->getConfig();
            $model = new shopProductImagesModel();
            $product = waRequest::post('product', array());
            if (!isset($product['id'])) {
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

            $name = strtolower(basename($image_url));

            waFiles::upload($image_url, $file = wa()->getCachePath('plugins/imageupload/' . waLocale::transliterate($name, 'en_US')));


            if ($file && file_exists($file)) {
                if ($image = waImage::factory($file)) {
                    $data = array(
                        'product_id' => $product['id'],
                        'upload_datetime' => date('Y-m-d H:i:s'),
                        'width' => $image->width,
                        'height' => $image->height,
                        'size' => filesize($file),
                        'original_filename' => $name,
                        'ext' => pathinfo($file, PATHINFO_EXTENSION),
                    );


                    $image_changed = false;

                    /**
                     * TODO move it code into product core method
                     */
                    /**
                     * Extend add/update product images
                     * Make extra workup
                     * @event image_upload
                     */
                    $event = wa()->event('image_upload', $image);
                    if ($event) {
                        foreach ($event as $result) {
                            if ($result) {
                                $image_changed = true;
                                break;
                            }
                        }
                    }


                    if (empty($data['id'])) {
                        $image_id = $data['id'] = $model->add($data);
                    } else {
                        $image_id = $data['id'];
                        $model->updateById($image_id, $data);
                    }

                    if (!$image_id) {
                        throw new waException("Database error");
                    }

                    $image_path = shopImage::getPath($data);
                    if ((file_exists($image_path) && !is_writable($image_path)) || (!file_exists($image_path) && !waFiles::create($image_path))) {
                        $model->deleteById($image_id);
                        throw new waException(sprintf("The insufficient file write permissions for the %s folder.", substr($image_path, strlen($this->getConfig()->getRootPath()))));
                    }

                    if ($image_changed) {
                        $image->save($image_path);
                        if ($config->getOption('image_save_original') && ($original_file = shopImage::getOriginalPath($data))) {
                            waFiles::copy($file, $original_file);
                        }
                    } else {
                        waFiles::copy($file, $image_path);
                    }
                } else {
                    throw new waException(sprintf('Файл не является изображением', $file));
                }
            } elseif ($file) {
                throw new waException("Ошибка загрузки файла");
            }


            $this->response['files'][] = array(
                'id' => $image_id,
                'name' => $name,
                'type' => waFiles::getMimeType($file),
                'size' => filesize($file),
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
