<?php

/**
 * @author wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
class shopImageuploadPlugin extends shopPlugin {

    public function backendProductEdit($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $template_path = wa()->getAppPath('plugins/imageupload/templates/BackendProductEdit.html', 'shop');
            $html = $view->fetch($template_path);
            return array('images' => $html);
        }
    }

}
