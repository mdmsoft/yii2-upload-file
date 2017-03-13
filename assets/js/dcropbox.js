(function () {

    function updateCoords(c) {
        var $element = this.$el;
        var factor = this.zoomFactor;
        $element.find('input[data-attr="x"]').val(c.x * factor);
        $element.find('input[data-attr="y"]').val(c.y * factor);
        $element.find('input[data-attr="w"]').val(c.w * factor);
        $element.find('input[data-attr="h"]').val(c.h * factor);
    }
    
    function inputChange() {
        var input = this;
        var $el = $(this).closest('div.dcorpbox');
        var id = $el.attr('id');
        var imgId = id + '-img';
        var opts = $el.data('dCropBox');

        if (input.files && input.files[0]) {
            if (opts.api) {
                opts.api.destroy();
            }
            $el.children('div.container').html(opts.imgTemplate);

            var reader = new FileReader();
            reader.onload = function (e) {
                $el.trigger('beforeLoadFile');
                $el.find('input[data-attr="x"]').val('');
                var $img = $('#' + imgId);
                $img.attr('src', e.target.result);
                var img = new Image();

                img.onload = function () {
                    if ((opts.minWidth && img.width < opts.minWidth) ||
                        (opts.minHeight && img.width < opts.minHeight)) {
                        alert(opts.toSmallMsg);
                        return;
                    }
                    var factor = img.width / $img.width();
                    var params = {
                        onSelect: updateCoords,
                    };
                    var selection;
                    if (opts.minWidth || opts.minHeight) {
                        var minW = opts.minWidth / factor;
                        var minH = opts.minHeight / factor;
                        selection = [0, 0, minW, minH];
                        params = $.extend({}, params, {
                            minSize: [minW, minH],
                        });
                    }
                    opts.api = $.Jcrop('#' + imgId, $.extend({}, params, {
                        aspectRatio: opts.aspectRatio,
                    }, opts.jcrop || {}));
                    opts.api.$el = $el;
                    opts.api.zoomFactor = factor;
                    
                    if (selection){
                        opts.api.setSelect(selection);
                    }
                    $el.trigger('afterLoadFile');
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $this = $(this);
                var opts = $.extend({}, defaults, options || {});

                if (opts.minWidth && opts.minHeight == undefined) {
                    opts.minHeight = opts.minWidth / opts.aspectRatio;
                } else if (opts.minHeight && opts.minWidth == undefined) {
                    opts.minWidth = opts.minHeight * opts.aspectRatio;
                }
                if(opts.button){
                    $(opts.button).click(function (){
                        methods.selectFile.call($this);
                    });
                }
                $this.data('dCropBox', opts);
                $this.children(':input.file-input').change(inputChange);
            });
        },
        selectFile: function () {
            return this.each(function () {
                $(this).children(':input.file-input').trigger('click');
            });
        }
    }

    var defaults = {
        aspectRatio: 1,
        toSmallMsg: 'Image to small',
    }

    $.fn.dCropBox = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.dCropBox');
            return false;
        }
    }
})();