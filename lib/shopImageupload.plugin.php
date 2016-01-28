<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopImageuploadPlugin extends shopPlugin {

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('product_id', $product['id']);
            $template_path = wa()->getAppPath('plugins/imageupload/templates/ImageUpload.html', 'shop');
            $html = $view->fetch($template_path);
            return array('images' => $html);
        }
    }

    public function backendProduct($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('product_id', $product['id']);
            $template_path = wa()->getAppPath('plugins/imageupload/templates/ImageUpload.html', 'shop');
            $html = $view->fetch($template_path);
            return array('info_section' => $html);
        }
    }

}
