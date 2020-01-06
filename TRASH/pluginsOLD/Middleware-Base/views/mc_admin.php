<div class="grid_half">
    <form method="post" action="options.php">
        <?php settings_fields($config_name . '_group'); ?>
        <h3><?php _e('Message Central Settings'); ?></h3>
        <p>
            You can find more information <strong><a href="http://docs.threefoldsystems.com:8090/display/shareit/36601878/TRGd0977f4129fd41b881ab3bca741fcf03DCB" target="_blank">here</a>.</strong>
        </p>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="mc_orgid">
                        <?php _e('Org ID'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" value="<?php echo ( ! empty( $config[ 'mc_orgid' ] ) ? $config[ 'mc_orgid' ] : '' ); ?>" id="mc_token"
                           name="<?php echo $config_name; ?>[mc_orgid]" class="medium-text" placeholder="orgid">

                    <p class="description"><?php _e('An affiliate specific id.'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="mc_token">
                        <?php _e('API Token'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" value="<?php echo ( ! empty( $config[ 'mc_token' ] ) ? $config[ 'mc_token' ] : '' ); ?>" id="mc_token"
                           name="<?php echo $config_name; ?>[mc_token]" class="regular-text" placeholder="MC Token">

                    <p class="description"><?php _e('A random text string needed to authenticate with Message Central. Contact Publishing Services to obtain your token.'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mc_url">
                        <?php _e('API URL'); ?>
                    </label>
                </th>

                <td>
                    <input type="text" value="<?php echo ( ! empty( $config[ 'mc_url' ] ) ? $config[ 'mc_url' ] : '' ); ?>" id="mc_url"
                           name="<?php echo $config_name; ?>[mc_url]" class="regular-text">

                    <p class="description"><?php _e('The URL for the Message Central API.'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e('MC API Stack:'); ?>
                </th>

                <td>
                    <div class="mc-paid">
                        <label>
                            <input type="checkbox" class="cb" onchange="cbChange(this)"
                                   name="<?php echo $config_name; ?>[mc_is_paid]"
                                   value="1" <?php if ( ! empty( $config['mc_is_paid'] ) && $config['mc_is_paid'] == 1) echo 'checked'; ?>>
                            <?php _e('Paid Stack'); ?>
                        </label>

                        <br>

                        <label>
                            <input type="checkbox" onchange="cbChange(this)" class="cb"
                                   name="<?php echo $config_name; ?>[mc_not_paid]"
                                   value="1" <?php if ( ! empty( $config['mc_not_paid'] ) && $config['mc_not_paid'] == 1) echo 'checked'; ?>>
                            <?php _e('Free Stack'); ?>
                        </label>

                        <p class="description">
                            Emails will be sent through the paid or free stack depending on this selection. The paid
                            stack is generally faster.
                        </p>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <h3><?php _e('Forgot Password Settings'); ?></h3>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <?php _e('Enable MC Forgot Password:'); ?>
                    </th>
                    <td>
                        <div class="radio">
                            <label>
                                <input class="mailing_on" type="radio" name="<?php echo $config_name; ?>[mc_mailing]"
                                       value="1" <?php if ( ! empty( $config['mc_mailing'] ) && $config['mc_mailing'] == 1) echo 'checked'; ?>>
                                 <?php _e('On'); ?>
                            </label>
                            <br>
                            <label>
                                <input class="mailing_off" type="radio" name="<?php echo $config_name; ?>[mc_mailing]"
                                       value="0" <?php if ( empty( $config['mc_mailing'] ) ) echo 'checked'; ?>>
                                <?php _e('Off'); ?>
                            </label>
                            <p class="description">
                                Forgot password emails will be sent through the Message Central API if this is enabled.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="forgot-password-container">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="mc_list">
                                <?php _e('Listcode'); ?>
                            </label>
                        </th>

                        <td>
                            <input type="text" value="<?php echo ( ! empty( $config[ 'mc_list' ] ) ? $config[ 'mc_list' ] : '' ); ?>" id="mc_list"
                                   name="<?php echo $config_name; ?>[mc_list]" class="medium-text" placeholder="MC List">

                            <p class="description"><?php _e('The List your forgot password mailing ID will be associated with. Must correspond with an 8 digit listcode you got from pub services'); ?></p>

                            <p><a id="associate_list_mc" class="button button-primary">Create Mailing</a></p>

                            <p class="description">
                                Once you have saved your Org ID, Token, and Listcode, click the 'Create Mailing' button to set up your forgot password mailing.
                            </p>

                            <p class="associate_feedback"></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mc_mailing_id">
                                <?php _e('Mailing ID'); ?>
                            </label>
                        </th>

                        <td>
                            <input type="text" value="<?php echo ( ! empty( $config[ 'mc_mailing_id' ] ) ? $config[ 'mc_mailing_id' ] : '' ); ?>" id="mc_content"
                                   name="<?php echo $config_name; ?>[mc_mailing_id]" class="medium-text"
                                   placeholder="Mailing ID">

                            <p class="description"><?php _e('MC Mailing ID. If you leave this field blank a default will be generated for you.'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="mc_content_id">
                                <?php _e('Content ID'); ?>
                            </label>
                        </th>

                        <td>
                            <input type="text" readonly value="<?php echo ( ! empty( $config[ 'mc_content_id' ] ) ? $config[ 'mc_content_id' ] : '' ); ?>" id="mc_content"
                                   name="<?php echo $config_name; ?>[mc_content_id]" class="medium-text"
                                   placeholder="Content ID">

                            <p class="description"><?php _e('MC Content ID.  This will be generated for you.'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php submit_button(__('Save'), 'primary', 'submit', true, array('disabled' => 'disabled')); ?>

        <p class="description">
            You must enter an Org ID, Token, Listcode, and Stack in order to save your settings.
        </p>

        <hr />
    </form>
</div>

<div class="grid_half">
    <h3>Connection Status</h3>

    <p><?php _e('Your external IP will need to be cleared with Agora IT, if it changes you will need to notify Pub Services'); ?></p>

    <h3>
        <div class="indicator" id="status_light"></div>
        <span id="status_indicator"><?php _e('Checking Connection Status...'); ?></span>
    </h3>

    <p id="status_help"></p>
</div>


<div id="mc_debug_result">

</div>