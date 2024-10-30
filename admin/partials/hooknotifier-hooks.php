<?php
hooknotifier_init_hooks();

function hooknotifier_init_hooks() {
    $option = get_option( 'hooknotifier_options' );
    
    if ($option['hooknotifier_field_user_login']) {
        add_action('wp_login', 'hooknotifier_login', 10, 2);
    }

    if ($option['hooknotifier_field_user_register']) {
        add_action('user_register', 'hooknotifier_register', 10, 2);
    }

    if ($option['hooknotifier_field_user_visiting']) {
        add_action('init', 'hooknotifier_user_visiting', 10, 2);
    }

    if ($option['hooknotifier_field_user_search']) {
        add_filter('posts_search', 'hooknotifier_user_search' , 10, 2);
    }

    if ($option['hooknotifier_field_wc_add_to_cart']) {
        add_action('woocommerce_add_to_cart', 'hooknotifier_wc_add_to_cart' , 10, 3);
    }
    
    if ($option['hooknotifier_field_wc_checkout_order']) {
        add_action('woocommerce_before_checkout_form', 'hooknotifier_wc_checkout_order' , 10, 3);
    }

    if ($option['hooknotifier_field_wc_new_order']) {
        add_action('woocommerce_checkout_order_processed', 'hooknotifier_wc_new_order' , 10, 3);
    }
}

function hooknotifier_get_params ($name) {
    $option = get_option( 'hooknotifier_options' );

    $object = $option['hooknotifier_field_'.$name.'_object'] === '' ? $option['hooknotifier_field_'.$name.'_object_default'] : $option['hooknotifier_field_'.$name.'_object'];
    $body = $option['hooknotifier_field_'.$name.'_body'] === '' ? $option['hooknotifier_field_'.$name.'_body_default'] : $option['hooknotifier_field_'.$name.'_body'];
    $data = $option['hooknotifier_field_'.$name.'_data'];
    $category = $option['hooknotifier_field_'.$name.'_category'] === '' ? $option['hooknotifier_field_'.$name.'_category_default'] : $option['hooknotifier_field_'.$name.'_category'];
    $color = $option['hooknotifier_field_'.$name.'_color'];

    return ['object' => $object, 'body' => $body, 'data' => $data, 'category' => $category, 'color' => $color];
}

function hooknotifier_replace_variables ($params, $var, $value) {
    $params['object'] = str_replace($var, $value, $params['object']);
    $params['body'] = str_replace($var, $value, $params['body']);

    return $params;
}

function hooknotifier_send_params ($params) {
    hooknotifier_send_notification ($params['object'], $params['body'], $params['category'], $params['color'], $params['data']);
}

/**
 * HOOKS:
 */
function hooknotifier_login( $user_login, $user ) {
    $params = hooknotifier_get_params('user_login');
    $params = hooknotifier_replace_variables($params, "%username%", $user_login);

    if ($params['data']) {
        $params['data'] = $user->to_array();
    } else {
        $params['data'] = '';
    }
    
    hooknotifier_send_params($params);
}

function hooknotifier_register( $user_id ) {
    $user = get_user_by( 'id', $user_id );

    $params = hooknotifier_get_params('user_register');
    $params = hooknotifier_replace_variables($params, "%email%", $user->user_email);
    $params = hooknotifier_replace_variables($params, "%username%", $user->user_login);

    if ($params['data']) {
        $params['data'] = $user->to_array();
    } else {
        $params['data'] = '';
    }
    
    hooknotifier_send_params($params);
}

function hooknotifier_user_visiting() {
    $params = hooknotifier_get_params('user_visiting');

    if (!$_COOKIE['hooknotifier']) {
        hooknotifier_send_params($params);
    }

    setcookie("hooknotifier", true);
}

function hooknotifier_user_search( $search_query, $wp_query ) {
    $terms = $wp_query->query_vars['search_terms'];
    if (isset($terms) && count($terms) > 0) {
        $params = hooknotifier_get_params('user_search');
        $params = hooknotifier_replace_variables($params, "%keywords%", implode(', ', $terms));

        hooknotifier_send_params($params);
    }

    return $search_query;
}

function hooknotifier_wc_add_to_cart( $key, $product_id ) {
    $params = hooknotifier_get_params('wc_add_to_cart');

    $product = wc_get_product( $product_id );
    $params = hooknotifier_replace_variables($params, "%item%", $product->get_title());

    if ($params['data']) {
        $params['data'] = $product->post->to_array();
    } else {
        $params['data'] = '';
    }

    hooknotifier_send_params($params);
}

function hooknotifier_wc_checkout_order() {
    $params = hooknotifier_get_params('wc_checkout_order');
    hooknotifier_send_params($params);
}

function hooknotifier_wc_new_order($a, $payment_method, $order) {
    $order_data = $order->get_data();

    $email = $order_data['billing']['email'];
    $total = $order_data['total'].$order_data['currency'];
    $paymentMethod = $order_data['payment_method_title'];

    $params = hooknotifier_get_params('wc_new_order');

    $params = hooknotifier_replace_variables($params, "%email%", $email);
    $params = hooknotifier_replace_variables($params, "%total%", $total);
    $params = hooknotifier_replace_variables($params, "%paymentmethod%", $paymentMethod);

    if ($params['data']) {
        $params['data'] = $order_data;
    } else {
        $params['data'] = '';
    }

    hooknotifier_send_params($params);
}
