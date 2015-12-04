(function ($) {
    "use strict";
    $.imageupload = {
        options: {},
        lastView: null,
        init: function (options) {
            this.options = options;
            this.options.placeholder = this.options.placeholder || null;
            this.initImageUpload();
        },
        initImageUpload: function () {
            var $self = this;

            $('.image-upload-container').off('click', '.image-upload-but').on('click', '.image-upload-but', function () {
                var container = $(this).closest('.image-upload-container');
                $(container).find('.image-upload-loading').show();

                $.ajax({
                    type: 'POST',
                    url: '?plugin=imageupload&action=imageUpload',
                    data: {
                        product_id: $(container).find('.image-url').data('product-id'),
                        image_url: $(container).find('.image-url').val()
                    },
                    dataType: 'json',
                    success: function (data, textStatus, jqXHR) {
                        $(container).find('.image-upload-loading').hide();
                        var $response = $(container).find('.image-upload-response');
                        if (data.status == 'ok') {
                            $response.html('<i class="icon16 yes"></i>' + data.data.message);
                            $response.css('color', '#008727');
                            $(container).find('.image-url').val('');
                            var files = data.data.files;
                            var product_id = $.product_images.product_id;
                            var placeholder = $self.options.placeholder;
                            // update images list for images tab
                            if ($('#s-product-image-list').length) {
                                $('#s-product-image-list').append(tmpl('template-product-image-list', {
                                    images: files,
                                    placeholder: placeholder,
                                    product_id: product_id
                                }));
                            }
                            setTimeout(function () {
                                $response.hide();
                            }, 3000);
                        } else {

                            $response.html('<i class="icon16 no"></i>' + data.errors);
                            $response.css('color', '#FF0000');
                        }
                        $response.show();
                    }
                });
                return false;
            });
        }
    };
})(jQuery);
