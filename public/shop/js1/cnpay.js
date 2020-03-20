/*
 * @Author: cnmobi
 * @Last Modified by:   cnmobi
 */

(function($) {
    var PopUpWin = function(ele, opts) {
        opts = $.extend({
            id: '',
            qrCodeClose: false,
            content: undefined, //内容
            closeCallback: undefined //关闭时调用的方法
        }, opts);
        this.init(ele, opts);
    }

    PopUpWin.prototype = {
        template: '<div class="pop-wraper" id="{id}">\
                <div class="pop-outer">\
                    <div class="pop-inner">\
                        <div class="pop-content">\
                            {content}\
                        </div>\
                        <div class="btn btn_cancel"><i class="ico_cancel"></i></div>\
                    </div>\
                </div>\
            </div>',
        init: function(ele, opts) {
            this.render(ele, opts);
            this.initEvent(ele, opts);
        },
        initEvent: function(ele, opts) {
            var self = this;
            ele.find('.btn_cancel').click(function() {
                ele.find('#' + self.id).remove();
                if (opts.closeCallback !== undefined && $.isFunction(opts.closeCallback)) {
                    opts.closeCallback();
                }
            });
        },
        elId: function() { //自动生成7位8进制DOM元素ID
            return 'win-xxx'.replace(/[x]/g, function(c) {
                var r = Math.random() * 16 | 0,
                    v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(8);
            }).toLocaleLowerCase();
        },
        render: function(ele, opts) {
            if (ele === undefined) {
                ele = $('body');
            }

            var content = opts.content;
            this.id = this.elId();

            if ($.isFunction(content)) {
                content = content(this);
            }
            tpl = this.template.replace(/\{id\}/, this.id).replace(/\{content\}/, content);
            ele.append(tpl);
        }
    };

    $.fn.popUpWin = function(opts) {
        return this.each(function() {
            var that = $(this);
            var popUp = new PopUpWin(that, opts);
        });
    };

})(jQuery);



function formatDate() {
    var vNow = new Date();
    var sNow = "";
    sNow += String(vNow.getFullYear());
    sNow += String(vNow.getMonth() + 1);
    sNow += String(vNow.getDate());
    sNow += String(vNow.getHours());
    sNow += String(vNow.getMinutes());
    sNow += String(vNow.getSeconds());
    sNow += String(vNow.getMilliseconds());
    return sNow;

}

window.payM = (function() {
    var tabPay = function() {
        $('li').on('click', function(event) {
            $('#auto_center').load($(this).attr('data-href') + ".html");
            $(this).addClass('cur').siblings('li').removeClass('cur')
        });
    };


    //封装ajax 网络请求
    var http = function(url, params) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'JSON',
                    data: params,
                })
                .done(function(data) {
                    console.log("success");
                    resolve(data);
                })
                .fail(function(error) {
                    console.log("error");
                    reject(error);
                })
                .always(function() {
                    console.log("complete");
                });


        });
    }

    return {
        init: function() {
            tabPay();
            /*window.onload = function() {
                $('#auto_center').load("./tpl/wx_order.html");
            }*/
        },
        orderQuery: function(orderNo) {
            var params = {
                orderNo: orderNo,
                method: 'localOrderQuery'
            }
            http("/demo/index/check", params).then(function(res) {
                if (res.status == 1) {
                    window.location.href = "/demo/index/successful";
                }else{
                    console.log(res.status);
                }
            }).catch(function(err) {
                console.log(err)
            })
        }
    }
})();

payM.init();