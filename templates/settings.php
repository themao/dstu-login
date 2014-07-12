<div class="wrap">
    <h2>DSTU Login settings</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('dstu-login-group'); ?>
        <?php @do_settings_fields('dstu-login-group'); ?>

        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="app_id">Registered app id</label></th>
                <td><input type="text" name="app_id" id="app_id" value="<?php echo get_option('app_id'); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="auth_url">Auth url (login page)</label></th>
                <td><input type="text" name="auth_url" id="auth_url" value="<?php echo get_option('auth_url', DEFAULT_DSTU_AUTH_URL); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="cert_base">Cert API URL</label></th>
                <td><input type="text" name="cert_base" id="cert_base" value="<?php echo get_option('cert_base', DEFAULT_DSTU_CERT_BASE); ?>" /></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>
