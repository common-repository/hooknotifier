<?php
define('API_PREFIX', 'https://sun.hooknotifier.com/api/notifications/');

function hooknotifier_init() {
    hooknotifier_init_actions();

    add_filter( 'plugin_action_links_hooknotifier/hooknotifier.php', 'nc_settings_link' );
    function nc_settings_link( $links ) {
        // Build and escape the URL.
        $url = esc_url( add_query_arg(
            'page',
            'hooknotifier',
            get_admin_url() . 'admin.php'
        ) );
        // Create the link.
        $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }//end nc_settings_link()
}

function hooknotifier_init_actions() {
    add_action( 'admin_menu', 'hooknotifier_add_menu' );
    add_action( 'admin_init', 'hooknotifier_settings_init' );
}

function hooknotifier_add_menu () {
    add_menu_page(
        'HookNotifier',
        'HookNotifier',
        'manage_options',
        'hooknotifier',
        'hooknotifier_admin_view',
        'data:image/svg+xml;base64,' . base64_encode('<svg version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 8 8" style="enable-background:new 0 0 8 8;" xml:space="preserve"><g transform="translate(136.147 41.803)"><path d="M-132.3-33.9c-0.5,0-0.9-0.4-0.9-0.9v0h-0.3c-0.2,0-0.3-0.1-0.3-0.3s0.1-0.3,0.3-0.3l0,0h0.9v0.6c0,0.2,0.1,0.3,0.3,0.3c0.2,0,0.3-0.1,0.3-0.3v-0.6h1.6c0.4,0,0.8-0.3,0.9-0.6h-0.6v-1.6c0-1.2-1-2.2-2.2-2.2h-0.3v-1.6c0-0.2,0.1-0.3,0.3-0.3c0.2,0,0.3,0.1,0.3,0.3v1c1.4,0.2,2.5,1.4,2.5,2.8v0.9h0.6v0.3c0,0.9-0.7,1.6-1.6,1.6h-0.9C-131.3-34.3-131.8-33.9-132.3-33.9z"/></g></svg>'),
        20
    );
}

hooknotifier_init();
 
function hooknotifier_admin_view() {
    // check user capabilities
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'hooknotifier_messages', 'hooknotifier_message', __( 'Settings Saved', 'hooknotifier' ), 'updated' );

        $option = get_option( 'hooknotifier_options' );
        $name = 'save_settings';

        if ($option['hooknotifier_field_'.$name]) {
            $object = $option['hooknotifier_field_'.$name.'_object'] === '' ? $option['hooknotifier_field_'.$name.'_object_default'] : $option['hooknotifier_field_'.$name.'_object'];
            $body = $option['hooknotifier_field_'.$name.'_body'] === '' ? $option['hooknotifier_field_'.$name.'_body_default'] : $option['hooknotifier_field_'.$name.'_body'];
            $data = $option['hooknotifier_field_'.$name.'_data'];
            $category = $option['hooknotifier_field_'.$name.'_category'] === '' ? $option['hooknotifier_field_'.$name.'_category_default'] : $option['hooknotifier_field_'.$name.'_category'];
            $color = $option['hooknotifier_field_'.$name.'_color'];

            hooknotifier_send_notification($object, $body, $category, $color);
        }
    }
 
    settings_errors( 'hooknotifier_messages' );
    ?>
    <div class="wrap ">
        <div class="header" style="width: 100%; height: 250px; display: flex; align-items: center; justify-content: center;">

            <div style="margin-left: 10px;">
                <img alt="hooknotifier logo" style="border-radius: 5px;" class="bdra-card" src="https://cdn.hooknotifier.com/resources/logo-light.svg"/>
                <a href="https://hooknotifier.com" target="_blank" class="bdra-card" style="font-weight: 700; margin: 5px 0 0 0; text-transform: uppercase; width: 200px; height: 50px; color: white; display: flex; align-items: center; justify-content: center; background: #2c3e50; border-radius: 5px; text-decoration: none;">Official website & help</a>
            </div>

            <div style="margin-left: 20px;">
                <h2>How it works?</h2>
                <ul>
                    <li>- Create an account on HookNotifier.com</li>
                    <li>- Download the application</li>
                    <li>- Fill these settings with your identifiers (available in-app)</li>
                    <li>- Enable & customize notifications</li>
                    <li>- Start receiving your notifications about your Wordpress :)</li>
                </ul>
            </div>
        </div>

        <form action="options.php" method="post">
            <?php
                settings_fields( 'hooknotifier' );
                do_settings_sections( 'hooknotifier' );
            ?>
            <a target="_blank" style="margin-top: 10px;" href="https://hooknotifier.com">You need more hooks? Contact-us.</a>
            <?
                submit_button( 'Save Settings' );
            ?>
        </form>


    </div>
    <?php
}

/**
 * HOOKNOTIFIER
 */
function hooknotifier_send_notification ($object, $body, $category, $color = "#0097A7", $data = '') {
    $option = get_option( 'hooknotifier_options' );

    $color = str_replace('#', '%23', $color);

    $url = API_PREFIX
    .$option['hooknotifier_field_id']."/"
    .$option['hooknotifier_field_key']
    ."?object=".$object."&body=".$body."&tags=".$category."&color=".$color;

    $args = [
        'body' => $data
    ];

    wp_remote_post( $url, $args );
}

/**
 * SETTINGS API:
 */

function create_settings_field($name, $type, $label, $section, $desc1, $desc2, $object = '', $body = '', $category = '') {
    $classes = '';
    if ($type === 'checkbox') {
        $classes = 'hooknotifier-card';
    }

    add_settings_field(
        'hooknotifier_field_'.$name,
        __( $label, 'hooknotifier' ),
        'hooknotifier_'.$type.'_cb',
        'hooknotifier',
        'hooknotifier_section_'.$section,
        [ 
            'label_for' => 'hooknotifier_field_'.$name,
            'desc1' => __( $desc1, 'hooknotifier' ),
            'desc2' => __( $desc2, 'hooknotifier' ),
            'object' => __($object, 'hooknotifier'),
            'body' => __($body, 'hooknotifier'),
            'category' => __($category, 'hooknotifier'),
            'class' => $classes
        ]
    );
    if ($type === 'checkbox') {
        add_settings_field(
            'hooknotifier_field_'.$name.'_object_default',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'class' => 'hidden'
            ]
        );
        add_settings_field(
            'hooknotifier_field_'.$name.'_body_default',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'class' => 'hidden'
            ]
        );
        add_settings_field(
            'hooknotifier_field_'.$name.'_category_default',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'hooknotifier_field_'.$name.'_object',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'label_for' => 'hooknotifier_field_'.$name.'_object',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'hooknotifier_field_'.$name.'_body',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'label_for' => 'hooknotifier_field_'.$name.'_body',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'hooknotifier_field_'.$name.'_data',
            __( $label.'_object', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'label_for' => 'hooknotifier_field_'.$name.'_data',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'hooknotifier_field_'.$name.'_color',
            __( $label.'_color', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'label_for' => 'hooknotifier_field_'.$name.'_color',
                'class' => 'hidden'
            ]
        );

        add_settings_field(
            'hooknotifier_field_'.$name.'_category',
            __( $label.'_color', 'hooknotifier' ),
            'hooknotifier_empty_cb',
            'hooknotifier',
            'hooknotifier_section_'.$section,
            [ 
                'label_for' => 'hooknotifier_field_'.$name.'_category',
                'class' => 'hidden'
            ]
        );
    }
}
function create_settings_section($name) {
    add_settings_section(
        'hooknotifier_section_'.$name,
        null, 'hooknotifier_section_'.$name.'_cb',
        'hooknotifier'
    );
}

/**
 * INIT
 */
function hooknotifier_settings_init() {
    register_setting( 'hooknotifier', 'hooknotifier_options' );
 
    hooknotifiersettings_init_identifier();
    // hooknotifiersettings_init_notifications();
    hooknotifiersettings_init_wp();

    if (is_plugin_active('woocommerce/woocommerce.php')) { 
        hooknotifiersettings_init_woocommerce();
    }
}

/**
 * SECTIONS
 */
function hooknotifiersettings_init_identifier() {
    create_settings_section('ids');
    create_settings_field('id', 'text', 'Your identifier', 'ids', 'You can find this identifier in your configuration page on Hook Notifier application.', 'eg: https://hooknotifier.com/15958633123456/your-key, the id is the number 15958633123456.');
    create_settings_field('key', 'text', 'Your key', 'ids', 'You can find this key in your configuration page on Hook Notifier application.', 'eg: https://hooknotifier.com/15958633123456/your-key, the id is the string "your-key".');

}

/*function hooknotifiersettings_init_notifications() {
    create_settings_section('notifications');
    create_settings_field('business', 'text', 'Your business name', 'notifications', 'This is the first part of the object of your notification.', 'eg: Trello');
}*/

function hooknotifiersettings_init_wp() {
    create_settings_section('wp');

    create_settings_field('save_settings', 'checkbox', 'HookNotifier settings update', 'wp', 'Send a notification when this settings are saved.', '', 
        'Settings Saved', 
        'Your settings are working well, congratulation!', 
        'Wordpress');

    create_settings_field('user_login', 'checkbox', 'User login', 'wp', 'Send a notification when a user is successfully logged in.', 'Variable available: %username%', 
        'New login', 
        'A user has logged in to your site, his username is %username%.', 
        'Wordpress');

    create_settings_field('user_register', 'checkbox', 'User register', 'wp', 'Send a notification when a user registers.', 'Variables availables: %email%, %username%', 
        'New user', 
        'A user has registered to your site, his email is %email%.', 
        'Wordpress');

    create_settings_field('user_visiting', 'checkbox', 'User visiting', 'wp', 'Send a notification when a new user is visiting.', '', 
        'New visitor', 
        'A user is visiting your site', 
        'Wordpress');

    create_settings_field('user_search', 'checkbox', 'User search', 'wp', 'Send a notification when an user is searching something.', 'Variable available: %keywords%', 
        'New search', 
        'A user is searching on your site : %keywords%', 
        'Wordpress');

}

function hooknotifiersettings_init_woocommerce() {
    create_settings_section('woocommerce');

    create_settings_field('wc_add_to_cart', 'checkbox', 'Item added to cart', 'woocommerce', 'Send a notification when an user add an item to his cart.', 'Variable available: %item%', 
        'Item added to a cart', 
        '%item% was added to a cart', 
        'Woocommerce');

    create_settings_field('wc_checkout_order', 'checkbox', 'User is filling checkout form', 'woocommerce', 'Send a notification when an user is filling the checkout form.', '', 
        'User is filling checkout form', 
        'A user start has arrived on your checkout form.', 
        'Woocommerce');

    create_settings_field('wc_new_order', 'checkbox', 'New order', 'woocommerce', 'Send a notification when an user has paid his order.', 'Variable available: %total%, %email%, %paymentmethod%', 
        'New order', 
        'New order (%total%) for %email%', 
        'Woocommerce');
}

/**
 * CALLBACKS
 */
function hooknotifier_section_ids_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>" style="display: flex; align-items: center; justify-content: center; text-align:center; background-color: #2c3e50; border-radius: 5px; text-transform: uppercase; letter-spacing: 3px; padding: 10px; color: white;">
        <?php esc_html_e( 'Identifiers', 'hooknotifier' ); ?>
    </p>
    <?php
}

function hooknotifier_section_notifications_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>" style="display: flex; align-items: center; justify-content: center; text-align:center; background-color: #2c3e50; border-radius: 5px; text-transform: uppercase; letter-spacing: 3px; padding: 10px; color: white;">
        <?php esc_html_e( 'Your notifications parameters', 'hooknotifier' ); ?>
    </p>
    <?php
}

function hooknotifier_section_wp_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>" style="display: flex; align-items: center; justify-content: center; text-align:center; background-color: #2c3e50; border-radius: 5px; text-transform: uppercase; letter-spacing: 3px; padding: 10px; color: white;">
        <?php esc_html_e( 'Base Wordpress notifications', 'hooknotifier' ); ?>
    </p>
    <?php
}

function hooknotifier_section_woocommerce_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>" style="display: flex; align-items: center; justify-content: center; text-align:center; background-color: #2c3e50; border-radius: 5px; text-transform: uppercase; letter-spacing: 3px; padding: 10px; color: white;">
        <?php esc_html_e( 'Woocommerce notifications', 'hooknotifier' ); ?>
    </p>
    <?php
}

function hooknotifier_text_cb( $args ) {
    $option = get_option( 'hooknotifier_options' );
    ?>
    <input type="text" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $option[$args['label_for']]  ); ?>">

    <p class="description">
        <?php esc_html_e( $args['desc1'] ); ?><br/>
        <b><?php esc_html_e( $args['desc2'] ); ?></b>
    </p>
    <?php
}

function hooknotifier_checkbox_cb( $args ) {
    $option = get_option( 'hooknotifier_options' );
    ?>
    <section>
        <input type="checkbox" class="hooknotifier_activer" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'] ); ?>]" <?php echo isset($option[$args['label_for']]) ? 'checked' : ''; ?> value="true">
        
        <p class="description">
            <?php esc_html_e( $args['desc1'] ); ?>
            <br><b><?php esc_html_e( $args['desc2'] ); ?></b>
        </p>

        <div style="width: 100%; margin-top: 10px;">
            <input type="hidden" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_object_default' ); ?>]" value="<?php echo esc_attr($args['object']); ?>"/>
            <input type="hidden" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_body_default' ); ?>]" value="<?php echo esc_attr($args['body']); ?>"/>
            <input type="hidden" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_category_default' ); ?>]" value="<?php echo esc_attr($args['category']); ?>"/>
            
            <label>
                <b><?php echo __('Object ', 'hooknotifier'); ?></b>
            </label><br/>
            <input placeholder="<?php echo esc_attr($args['object']); ?>" style="width: 100%; margin-top: 4px;" type="text" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_object' ); ?>]" value="<?php echo esc_attr( $option[$args['label_for'].'_object']  ); ?>"><br/>
            <br/>
            <label>
                <b><?php echo __('Body ', 'hooknotifier'); ?></b>
            </label><br/>
            <input placeholder="<?php echo esc_attr($args['body']); ?>" style="width: 100%; margin-top: 4px;" type="text" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_body' ); ?>]" value="<?php echo esc_attr( $option[$args['label_for'].'_body']  ); ?>"><br/>
            <br/>
            <label>
                <b><?php echo __('Tags (separated by commas)', 'hooknotifier'); ?></b>
            </label><br/>
            <input placeholder="<?php echo esc_attr($args['category']); ?>" style="width: 100%; margin-top: 4px;" type="text" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_category' ); ?>]" value="<?php echo esc_attr( $option[$args['label_for'].'_category']  ); ?>"><br/>
            <br/>
            <label>
                <b><?php echo __('Color ', 'hooknotifier'); ?></b>
            </label><br/>
            <input style="width: 100%; margin-top: 4px;" type="color" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_color' ); ?>]" value="<?php echo esc_attr( $option[$args['label_for'].'_color']  ); ?>"><br/>
            <br/>
            <label>
                <b><?php echo __('Include datas ', 'hooknotifier'); ?></b>
            </label>
            <input style="margin-left: 5px;" type="checkbox" name="hooknotifier_options[<?php echo esc_attr( $args['label_for'].'_data' ); ?>]" <?php echo isset($option[$args['label_for'].'_data']) ? 'checked' : ''; ?> value="true">
        </div>
    </section>

    <style>
        .bdra-card {
            box-shadow: rgba(24, 42, 60, 0.12) 0px 1px 3px, rgba(24, 42, 60, 0.24) 0px 1px 2px;
        }

        .hooknotifier-card {
            position: relative;
            display: inline-block;
            box-shadow: rgba(24, 42, 60, 0.12) 0px 1px 3px, rgba(24, 42, 60, 0.24) 0px 1px 2px;
            border-radius: 5px;
            background: white;
            width: 300px;
            margin-right: 20px;
            margin-bottom: 20px;
            vertical-align: top;
            padding: 10px;
        }

        .hooknotifier-card th {
            display: block;
            padding: 10px;
        }

        .hooknotifier-card td {
            width: 100%;
            display: block;
            box-sizing: border-box;
            padding-top: 0;
            padding: 0 10px;
        }

        .hooknotifier-card th label {
            font-size: 16px!important;
        }
        .hooknotifier-card .hooknotifier_activer {
            position: absolute;
            top: 30px;
            right: 20px;
        }

        .hooknotifier-card .description {
            padding: 10px;
            background-color: #EEE;
            border-radius: 5px;
        }

        @media (max-width: 768px) { 
            .header {
                    display: flex!important;
                    flex-direction: column!important;
                    margin: 40px 0;
            }
        }

    </style>
    <?php
}

function hooknotifier_empty_cb($args) {}