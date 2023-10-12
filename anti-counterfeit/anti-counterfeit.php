<?php

/**
 * Plugin Name: 防伪码查询插件
 * Description: 用于查询产品防伪码的WordPress插件。 简码复制→  [anti_fake_code]
 * Version: 1.2
 * Author: by_§elect_dd | 业余爱好写了一个插件，不是专业，微信号：Xzzlhh_0521
 */

//创建数据库表
function create_anti_fake_data_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'anti_fake_data';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            code VARCHAR(255) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // 插入示例数据
        $data = array(
            array('code' => '12345', 'product_name' => 'Product A'),
            array('code' => '67890', 'product_name' => 'Product B'),
        );

        foreach ($data as $item) {
            $wpdb->insert($table_name, $item);
        }
    }
}

register_activation_hook(__FILE__, 'create_anti_fake_data_table');

// 加载CSS和JS
function anti_counterfeit_enqueue_scripts()
{
    wp_enqueue_style('anti-counterfeit-css', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('anti-counterfeit-js', plugin_dir_url(__FILE__) . 'js/anti-counterfeit.js', array('jquery'), '1.0', true);

    wp_localize_script('anti-counterfeit-js', 'antiCounterfeitAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'anti_counterfeit_enqueue_scripts');

// 处理AJAX请求
function anti_counterfeit_ajax_handler()
{
    if (isset($_POST['code'])) {
        $code = sanitize_text_field($_POST['code']);
        // echo json_encode($code);
        // 执行数据库查询并发送响应
        $response = anti_counterfeit_database_lookup($code);

        echo json_encode($response);
    }
    wp_die();
}

add_action('wp_ajax_anti_counterfeit_ajax', 'anti_counterfeit_ajax_handler');
add_action('wp_ajax_nopriv_anti_counterfeit_ajax', 'anti_counterfeit_ajax_handler');

// 执行数据库查询
function anti_counterfeit_database_lookup($code)
{
    global $wpdb;

    // 获取插件配置中的表名
    $table_name = $wpdb->prefix . 'anti_fake_data';

    // 防止SQL注入
    // $code = $wpdb->prepare('%s', $code);

    // 准备查询语句
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE code = %s", $code);

    // 执行查询
    $result = $wpdb->get_results($sql, ARRAY_A);

    if (!is_wp_error($result) && !empty($result)) {
        // 防伪码存在
        $response = array(
            'success' => true,
            'message' => '您的防伪码正确    ' . $result[0]['product_name'],
        );
    } else {
        // 防伪码不存在
        $response = array(
            'success' => false,
            'message' => '请检查您是否输入正确，或者您买到了假冒伪劣产品', // 
        );
    }

    return $response;
}


// 添加名为 [anti_fake_code] 的简码
function anti_fake_code_shortcode()
{
    ob_start(); // 开始输出缓冲

    // 输出防伪码查询表单
    include(plugin_dir_path(__FILE__) . 'templates/query-form.php');

    return ob_get_clean(); // 返回缓冲内容
}

add_shortcode('anti_fake_code', 'anti_fake_code_shortcode');
