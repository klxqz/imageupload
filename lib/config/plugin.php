<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'name' => 'Загрузка изображений',
    'description' => 'Загрузка изображений по url',
    'vendor' => '985310',
    'version' => '1.0.0',
    'img' => 'img/imageupload.png',
    'shop_settings' => false,
    'frontend' => false,
    'handlers' => array(
        'backend_product_edit' => 'backendProductEdit',
    ),
);
//EOF
