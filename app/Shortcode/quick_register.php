<?php

/**
 * Tạo from đăng ký nhanh
 */

add_shortcode('WGR_get_quick_register', 'wgr_action_WGR_get_quick_register');

//
function wgr_action_WGR_get_quick_register($custom_attrs = [])
{
    ob_start();

?>
    <div class="hpsbnlbx">
        <form name="frm_dk_nhantin" method="post" action="javascript:;" target="target_eb_iframe">
            <div class="cf">
                <div class="quick-register-left quick-register-hoten">
                    <input type="text" name="data[fullname]" value="" />
                </div>
                <div class="quick-register-left quick-register-phone">
                    <input type="text" name="data[phone]" value="" />
                </div>
                <div class="quick-register-left quick-register-email">
                    <input type="email" name="data[email]" value="" autocomplete="off" aria-required="true" required />
                </div>
                <div class="quick-register-left quick-register-submit">
                    <button type="submit" class="cur">Đăng ký nhận tin</button>
                </div>
            </div>
        </form>
    </div>
<?php

    //
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
