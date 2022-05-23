// chờ vuejs nạp xong để khởi tạo nội dung
function WGR_vuejs(app_id, obj, _callBack, max_i) {
    if (typeof max_i != 'number') {
        max_i = 100;
    } else if (max_i < 0) {
        console.log('%c Max loaded Vuejs', 'color: red');
        return false;
    }

    //
    if (typeof Vue != 'function') {
        setTimeout(function () {
            WGR_vuejs(app_id, obj, _callBack, max_i - 1);
        }, 100);
        return false;
    }

    // chưa tìm ra hàm định dạng ngày tháng tương tự angular -> tự viết hàm riêng vậy
    // -> xác định giờ theo múi giờ hiện tại của user
    var tzoffset = (new Date()).getTimezoneOffset() * 60000; // offset in milliseconds
    //console.log('tzoffset:', tzoffset);
    obj.datetime = function (t, len) {
        if (typeof len != 'number') {
            len = 19;
        }
        return (new Date(t - tzoffset)).toISOString().split('.')[0].replace('T', ' ').substr(0, len);
    };
    obj.date = function (t) {
        return (new Date(t - tzoffset)).toISOString().split('T')[0];
    };
    obj.time = function (t, len) {
        if (typeof len != 'number') {
            len = 8;
        }
        return (new Date(t - tzoffset)).toISOString().split('.')[0].split('T')[1].substr(0, len);
    };
    obj.number_format = function (n) {
        return (new Intl.NumberFormat().format(n));
    };

    //
    //console.log(obj);
    //console.log(obj.data);
    new Vue({
        el: app_id,
        data: obj,
        mounted: function () {
            $(app_id + '.ng-main-content').addClass('loaded');

            //
            if (typeof _callBack == 'function') {
                _callBack();
            }

            //
            if (taxonomy_ids_unique.length == 0) {
                action_each_to_taxonomy();
            }
        },
    });
}
