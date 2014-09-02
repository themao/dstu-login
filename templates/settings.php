<div class="wrap">
    <h2><?php _e('DSTU Login settings', 'dstu-login'); ?></h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('dstu-login-group'); ?>
        <?php @do_settings_fields('dstu-login-group'); ?>

        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="app_id"><?php _e('Registered app id', 'dstu-login'); ?></label></th>
                <td><input type="text" name="app_id" id="app_id" value="<?php echo get_option('app_id'); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="auth_url"><?php _e('Auth url (login page)', 'dstu-login'); ?></label></th>
                <td><input type="text" name="auth_url" id="auth_url" value="<?php echo get_option('auth_url', DEFAULT_DSTU_AUTH_URL); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="cert_base"><?php _e('Cert API URL', 'dstu-login'); ?></label></th>
                <td><input type="text" name="cert_base" id="cert_base" value="<?php echo get_option('cert_base', DEFAULT_DSTU_CERT_BASE); ?>" /></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>
