(function($) {
    "use strict";
    $.imageupload = {
        options: {},
        lastView: null,
        init: function(options) {
            this.options = options;
            this.options.placeholder = this.options.placeholder || null;
            this.initImageUpload();
        },
        initImageUpload: function() {
            var $self = this;
            $('#image-upload-but').click(function() {
                var $form = $('#s-product-save');
                $('#image-upload-loading').show();
                $.ajax({
                    type: 'POST',
                    url: '?plugin=imageupload&action=imageUpload',
                    data: $form.serializeArray(),
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR) {
                        console.log(data);
                        $('#image-upload-loading').hide();
                        var $response = $('#image-upload-response');
                        if (data.status == 'ok') {
                            $response.html('<i class="icon16 yes"></i>' + data.data.message);
                            $response.css('color', '#008727');
                            $('#image-url').val('');
                            var files = data.data.files;
                            var product_id = $.product_images.product_id;
                            var placeholder = $self.options.placeholder;
                            // update images list for images tab
                            $('#s-product-image-list').append(tmpl('template-product-image-list', {
                                images: files,
                                placeholder: placeholder,
                                product_id: product_id
                            }));

                            setTimeout(function() {
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
