<?php
/**
 * Sakura functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Sakura
 */

define('SAKURA_VERSION', wp_get_theme()->get('Version'));
define('BUILD_VERSION', '3');

//ini_set('display_errors', true);
//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);

if (!function_exists('akina_setup')):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */

    if (!function_exists('optionsframework_init')) {
        define('OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/inc/');
        require_once dirname(__FILE__) . '/inc/options-framework.php';
    }

    function akina_setup()
{
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on Akina, use a find and replace
         * to change 'akina' to the name of your theme in all the template files.
         */
        load_theme_textdomain('sakura', get_template_directory() . '/languages');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(150, 150, true);

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'primary' => __('Nav Menus', 'sakura'), //å¯¼èˆ?œ?•
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        /*
         * Enable support for Post Formats.
         * See https://developer.wordpress.org/themes/functionality/post-formats/
         */
        add_theme_support('post-formats', array(
            'aside',
            'image',
            'status',
        ));

        // Set up the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('akina_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        )));

        add_filter('pre_option_link_manager_enabled', '__return_true');

        // ä¼˜?Œ–ä»£ç?
        //?»é™¤å¤´?ƒ¨å†—ä½™ä»£ç?
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_generator'); //?š?—wordpress?‰ˆ?œ?
        remove_filter('the_content', 'wptexturize'); //?–æ¶ˆ??‡?‚¹ç??·è½¬ä¹‰

        //remove_action('rest_api_init', 'wp_oembed_register_route');
        //remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
        //remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        //remove_filter('oembed_response_data', 'get_oembed_response_data_rich', 10, 4);
        //remove_action('wp_head', 'wp_oembed_add_discovery_links');
        //remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);

        function coolwp_remove_open_sans_from_wp_core()
    {
            wp_deregister_style('open-sans');
            wp_register_style('open-sans', false);
            wp_enqueue_style('open-sans', '');
        }
        add_action('init', 'coolwp_remove_open_sans_from_wp_core');

        /**
         * Disable the emoji's
         */
        function disable_emojis()
    {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
        }
        add_action('init', 'disable_emojis');

        /**
         * Filter function used to remove the tinymce emoji plugin.
         *
         * @param    array  $plugins
         * @return   array             Difference betwen the two arrays
         */
        function disable_emojis_tinymce($plugins)
    {
            if (is_array($plugins)) {
                return array_diff($plugins, array('wpemoji'));
            } else {
                return array();
            }
        }

        // ç§»é™¤èœ?•?†—ä½™ä»£ç?
        add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1);
        add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1);
        add_filter('page_css_class', 'my_css_attributes_filter', 100, 1);
        function my_css_attributes_filter($var)
    {
            return is_array($var) ? array_intersect($var, array('current-menu-item', 'current-post-ancestor', 'current-menu-ancestor', 'current-menu-parent')) : '';
        }

    }
endif;
add_action('after_setup_theme', 'akina_setup');

function admin_lettering()
{
    echo '<style type="text/css">body{font-family: Microsoft YaHei;}</style>';
}
add_action('admin_head', 'admin_lettering');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function akina_content_width()
{
    $GLOBALS['content_width'] = apply_filters('akina_content_width', 640);
}
add_action('after_setup_theme', 'akina_content_width', 0);

/**
 * Enqueue scripts and styles.
 */
function sakura_scripts()
{
    if (akina_option('jsdelivr_cdn_test')) {
        wp_enqueue_script('js_lib', get_template_directory_uri() . '/cdn/js/lib.js', array(), SAKURA_VERSION . akina_option('cookie_version', ''), true);
    } else {
        wp_enqueue_script('js_lib', 'https://cdn.jsdelivr.net/gh/mashirozx/Sakura@' . SAKURA_VERSION . '/cdn/js/lib.min.js', array(), SAKURA_VERSION, true);
    }
    if (akina_option('app_no_jsdelivr_cdn')) {
        wp_enqueue_style('saukra_css', get_stylesheet_uri(), array(), SAKURA_VERSION);
        wp_enqueue_script('app', get_template_directory_uri() . '/js/sakura-app.js', array(), SAKURA_VERSION, true);
    } else {
        wp_enqueue_style('saukra_css', 'https://cdn.jsdelivr.net/gh/mashirozx/Sakura@' . SAKURA_VERSION . '/style.min.css', array(), SAKURA_VERSION);
        wp_enqueue_script('app', 'https://cdn.jsdelivr.net/gh/mashirozx/Sakura@' . SAKURA_VERSION . '/js/sakura-app.min.js', array(), SAKURA_VERSION, true);
    }
    wp_enqueue_script('github_card', 'https://cdn.jsdelivr.net/github-cards/latest/widget.js', array(), SAKURA_VERSION, true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    // 20161116 @Louie
    $mv_live = akina_option('focus_mvlive') ? 'open' : 'close';
    $movies = akina_option('focus_amv') ? array('url' => akina_option('amv_url'), 'name' => akina_option('amv_title'), 'live' => $mv_live) : 'close';
    $auto_height = akina_option('focus_height') ? 'fixed' : 'auto';
    $code_lamp = 'close';
    // if (wp_is_mobile()) {
    //     $auto_height = 'fixed';
    // }
    //?‹¦æˆ?§»?Š¨ç?
    version_compare($GLOBALS['wp_version'], '5.1', '>=') ? $reply_link_version = 'new' : $reply_link_version = 'old';
    $gravatar_url = akina_option('gravatar_proxy') ?: 'secure.gravatar.com/avatar';
    wp_localize_script('app', 'Poi', array(
        'pjax' => akina_option('poi_pjax'),
        'movies' => $movies,
        'windowheight' => $auto_height,
        'codelamp' => $code_lamp,
        'ajaxurl' => admin_url('admin-ajax.php'),
        'order' => get_option('comment_order'), // ajax comments
        'formpostion' => 'bottom', // ajax comments é»˜è®¤ä¸ºbottomï¼Œå¦‚?œä½??š„è¡¨å•?œ¨é¡¶?ƒ¨åˆ™è®¾ç½®ä¸?top?€‚
        'reply_link_version' => $reply_link_version,
        'api' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'google_analytics_id' => akina_option('google_analytics_id', ''),
        'gravatar_url' => $gravatar_url
    ));
}
add_action('wp_enqueue_scripts', 'sakura_scripts');

/**
 * load .php.
 */
require get_template_directory() . '/inc/decorate.php';
require get_template_directory() . '/inc/swicher.php';
require get_template_directory() . '/inc/api.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * function update
 */
require get_template_directory() . '/inc/theme_plus.php';
require get_template_directory() . '/inc/categories-images.php';

//Comment Location Start
function convertip($ip)
{
    error_reporting(E_ALL ^ E_NOTICE);
    $dat_path = dirname(__FILE__) . '/inc/QQWry.Dat';
    if (!$fd = @fopen($dat_path, 'rb')) {
        return 'IP date file not exists or access denied';
    }
    $ip = explode('.', $ip);
    $ipNum = intval($ip[0]) * 16777216 + intval($ip[1]) * 65536 + intval($ip[2]) * 256 + intval($ip[3]);
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $ipbegin = implode('', unpack('L', $DataBegin));
    if ($ipbegin < 0) {
        $ipbegin += pow(2, 32);
    }

    $ipend = implode('', unpack('L', $DataEnd));
    if ($ipend < 0) {
        $ipend += pow(2, 32);
    }

    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
    $BeginNum = 0;
    $EndNum = $ipAllNum;
    while ($ip1num > $ipNum || $ip2num < $ipNum) {
        $Middle = intval(($EndNum + $BeginNum) / 2);
        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if (strlen($ipData1) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip1num = implode('', unpack('L', $ipData1));
        if ($ip1num < 0) {
            $ip1num += pow(2, 32);
        }

        if ($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }
        $DataSeek = fread($fd, 3);
        if (strlen($DataSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if (strlen($ipData2) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if ($ip2num < 0) {
            $ip2num += pow(2, 32);
        }

        if ($ip2num < $ipNum) {
            if ($Middle == $BeginNum) {
                fclose($fd);
                return 'Unknown';
            }
            $BeginNum = $Middle;
        }
    }
    $ipFlag = fread($fd, 1);
    if ($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if (strlen($ipSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }
    if ($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if (strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while (($char = fread($fd, 1)) != chr(0)) {
            $ipAddr2 .= $char;
        }

        $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
        fseek($fd, $AddrSeek);
        while (($char = fread($fd, 1)) != chr(0)) {
            $ipAddr1 .= $char;
        }

    } else {
        fseek($fd, -1, SEEK_CUR);
        while (($char = fread($fd, 1)) != chr(0)) {
            $ipAddr1 .= $char;
        }

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while (($char = fread($fd, 1)) != chr(0)) {
            $ipAddr2 .= $char;
        }
    }
    fclose($fd);
    if (preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1 $ipAddr2";
    $ipaddr = preg_replace('/CZ88.Net/is', '', $ipaddr);
    $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
    if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = 'Unknown';
    }
    $ipaddr = iconv('gbk', 'utf-8//IGNORE', $ipaddr);
    if ($ipaddr != '  ') {
        return $ipaddr;
    } else {
        $ipaddr = 'Unknown';
    }

    return $ipaddr;
}
//Comment Location End

/**
 * COMMENT FORMATTING
 *
 * ??‡?‡†?š„ lazyload è¾“?‡ºå¤´?ƒ
 * <?php echo str_replace( 'src=', 'src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.0.1/img/svg/loader/index.ajax-spinner-preloader.svg" onerror="imgError(this,1)" data-src=', get_avatar( $comment->comment_author_email, '80', '', get_comment_author(), array( 'class' => array( 'lazyload' ) ) ) ); ?>
 *
 * å¦‚?œä¸å»¶æ—¶æ˜??™??·çš„
 * <?php echo get_avatar( $comment->comment_author_email, '80', '', get_comment_author() ); ?>
 *
 */
if (!function_exists('akina_comment_format')) {
    function akina_comment_format($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        ?>
		<li <?php comment_class();?> id="comment-<?php echo esc_attr(comment_ID()); ?>">
			<div class="contents">
				<div class="comment-arrow">
					<div class="main shadow">
						<div class="profile">
							<a href="<?php comment_author_url();?>" target="_blank" rel="nofollow"><?php echo str_replace('src=', 'src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.0.2/img/svg/loader/trans.ajax-spinner-preloader.svg" onerror="imgError(this,1)" data-src=', get_avatar($comment->comment_author_email, '80', '', get_comment_author(), array('class' => array('lazyload')))); ?></a>
						</div>
						<div class="commentinfo">
							<section class="commeta">
								<div class="left">
									<h4 class="author"><a href="<?php comment_author_url();?>" target="_blank" rel="nofollow"><?php echo get_avatar($comment->comment_author_email, '24', '', get_comment_author()); ?><span class="bb-comment isauthor" title="<?php _e('Author', 'sakura');?>"><?php _e('Blogger', 'sakura'); /*?šä¸?*/?></span> <?php comment_author();?> <?php echo get_author_class($comment->comment_author_email, $comment->user_id); ?></a></h4>
								</div>
								<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'])));?>
								<div class="right">
									<div class="info"><time datetime="<?php comment_date('Y-m-d');?>"><?php echo poi_time_since(strtotime($comment->comment_date_gmt), true); //comment_date(get_option('date_format'));  ?></time><?php echo siren_get_useragent($comment->comment_agent); ?><?php echo mobile_get_useragent_icon($comment->comment_agent); ?>&nbsp;<?php _e('Location', 'sakura'); /*?¥è‡?*/?>: <?php echo convertip(get_comment_author_ip()); ?>
    									<?php if (current_user_can('manage_options') and (wp_is_mobile() == false)) {
            $comment_ID = $comment->comment_ID;
            $i_private = get_comment_meta($comment_ID, '_private', true);
            $flag .= ' <i class="fa fa-snowflake-o" aria-hidden="true"></i> <a href="javascript:;" data-actionp="set_private" data-idp="' . get_comment_id() . '" id="sp" class="sm" style="color:rgba(0,0,0,.35)">' . __("Private", "sakura") . ': <span class="has_set_private">';
            if (!empty($i_private)) {
                $flag .= __("Yes", "sakura") . ' <i class="fa fa-lock" aria-hidden="true"></i>';
            } else {
                $flag .= __("No", "sakura") . ' <i class="fa fa-unlock" aria-hidden="true"></i>';
            }
            $flag .= '</span></a>';
            $flag .= edit_comment_link('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> ' . __("Edit", "mashiro"), ' <span style="color:rgba(0,0,0,.35)">', '</span>');
            echo $flag;
        }?></div>
								</div>
							</section>
						</div>
						<div class="body">
							<?php comment_text();?>
						</div>
					</div>
					<div class="arrow-left"></div>
				</div>
			</div>
			<hr>
		<?php
}
}

/**
 * ?·å–è®¿å?VIP??·å?
 */
function get_author_class($comment_author_email, $user_id)
{
    global $wpdb;
    $author_count = count($wpdb->get_results(
        "SELECT comment_ID as author_count FROM $wpdb->comments WHERE comment_author_email = '$comment_author_email' "));
    if ($author_count >= 1 && $author_count < 5) //?•°å?—??‡??Œä¿?”¹ï?Œä»£è¡¨è¯„è®ºæ??•°ã€‚
    {
        echo '<span class="showGrade0" title="Lv0"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_0.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 6 && $author_count < 10) {
        echo '<span class="showGrade1" title="Lv1"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_1.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 10 && $author_count < 20) {
        echo '<span class="showGrade2" title="Lv2"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_2.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 20 && $author_count < 40) {
        echo '<span class="showGrade3" title="Lv3"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_3.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 40 && $author_count < 80) {
        echo '<span class="showGrade4" title="Lv4"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_4.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 80 && $author_count < 160) {
        echo '<span class="showGrade5" title="Lv5"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_5.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    } else if ($author_count >= 160) {
        echo '<span class="showGrade6" title="Lv6"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/level/level_6.svg" style="height: 1.5em; max-height: 1.5em; display: inline-block;"></span>';
    }

}

/**
 * post views
 */
function restyle_text($number)
{
    switch (akina_option('statistics_format')) {
        case "type_2": //23,333 æ¬¡è??—?
            return number_format($number);
            break;
        case "type_3": //23 333 æ¬¡è??—?
            return number_format($number, 0, '.', ' ');
            break;
        case "type_4": //23k æ¬¡è??—?
            if ($number >= 1000) {
                return round($number / 1000, 2) . 'k';
            } else {
                return $number;
            }
            break;
        default:
            return $number;
    }
}

function set_post_views()
{
    if (is_singular()) {
        global $post;
        $post_id = intval($post->ID);
        if ($post_id) {
            $views = (int) get_post_meta($post_id, 'views', true);
            if (!update_post_meta($post_id, 'views', ($views + 1))) {
                add_post_meta($post_id, 'views', 1, true);
            }
        }
    }
}

add_action('get_header', 'set_post_views');

function get_post_views($post_id)
{
    if (akina_option('statistics_api') == 'wp_statistics') {
        if (!function_exists('wp_statistics_pages')) {
            return __('Please install pulgin <a href="https://wordpress.org/plugins/wp-statistics/" target="_blank">WP-Statistics</a>', 'sakura');
        } else {
            return restyle_text(wp_statistics_pages('total', 'uri', $post_id));
        }
    } else {
        $views = get_post_meta($post_id, 'views', true);
        if ($views == '') {
            return 0;
        } else {
            return restyle_text($views);
        }
    }
}

/*
 * Ajax?‚¹è?
 */
add_action('wp_ajax_nopriv_specs_zan', 'specs_zan');
add_action('wp_ajax_specs_zan', 'specs_zan');
function specs_zan()
{
    global $wpdb, $post;
    $id = $_POST["um_id"];
    $action = $_POST["um_action"];
    if ($action == 'ding') {
        $specs_raters = get_post_meta($id, 'specs_zan', true);
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
        setcookie('specs_zan_' . $id, $id, $expire, '/', $domain, false);
        if (!$specs_raters || !is_numeric($specs_raters)) {
            update_post_meta($id, 'specs_zan', 1);
        } else {
            update_post_meta($id, 'specs_zan', ($specs_raters + 1));
        }
        echo get_post_meta($id, 'specs_zan', true);
    }
    die;
}

/*
 * ?‹?ƒ…?“¾æ?
 */
function get_the_link_items($id = null)
{
    $bookmarks = get_bookmarks('orderby=date&category=' . $id);
    $output = '';
    if (!empty($bookmarks)) {
        $output .= '<ul class="link-items fontSmooth">';
        foreach ($bookmarks as $bookmark) {
            if (empty($bookmark->link_description)) {
                $bookmark->link_description = __('This guy is so lazy ?•?(?•?–½â•?)?•?', 'sakura');
            }

            if (empty($bookmark->link_image)) {
                $bookmark->link_image = 'https://view.moezx.cc/images/2017/12/30/Transparent_Akkarin.th.jpg';
            }

            $output .= '<li class="link-item"><a class="link-item-inner effect-apollo" href="' . $bookmark->link_url . '" title="' . $bookmark->link_description . '" target="_blank" rel="friend"><img class="lazyload" onerror="imgError(this,1)" data-src="' . $bookmark->link_image . '" src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.0.2/img/svg/loader/trans.ajax-spinner-preloader.svg"><span class="sitename">' . $bookmark->link_name . '</span><div class="linkdes">' . $bookmark->link_description . '</div></a></li>';
        }
        $output .= '</ul>';
    }
    return $output;
}

function get_link_items()
{
    $linkcats = get_terms('link_category');
    if (!empty($linkcats)) {
        foreach ($linkcats as $linkcat) {
            $result .= '<h3 class="link-title"><span class="link-fix">' . $linkcat->name . '</span></h3>';
            if ($linkcat->description) {
                $result .= '<div class="link-description">' . $linkcat->description . '</div>';
            }

            $result .= get_the_link_items($linkcat->term_id);
        }
    } else {
        $result = get_the_link_items();
    }
    return $result;
}

/*
 * Gravatarå¤´åƒä½¿ç”¨ä¸­?›½æœ?Š¡å™?
 */
function gravatar_cn($url)
{    
    $gravatar_url = array('0.gravatar.com/avatar','1.gravatar.com/avatar','2.gravatar.com/avatar','secure.gravatar.com/avatar');
    //return str_replace($gravatar_url, 'cn.gravatar.com', $url);
    //å®˜?–¹æœ?Š¡å™¨è?‘?œŸå¤§é™†è®¿é—? 429ï¼Œå»ºè?ä½¿ç”¨é•œ?ƒ
    return str_replace( $gravatar_url, akina_option('gravatar_proxy'), $url );
}
if(akina_option('gravatar_proxy')){
    add_filter('get_avatar_url', 'gravatar_cn', 4);
}

/*
 * ?‡??šä¹‰é»˜è®¤å¤´?ƒ
 */
add_filter('avatar_defaults', 'mytheme_default_avatar');

function mytheme_default_avatar($avatar_defaults)
{
    //$new_avatar_url = get_template_directory_uri() . '/images/default_avatar.png';
    $new_avatar_url = 'https://cn.gravatar.com/avatar/b745710ae6b0ce9dfb13f5b7c0956be1';
    $avatar_defaults[$new_avatar_url] = 'Default Avatar';
    return $avatar_defaults;
}

/*
 * ?˜»æ?ç«™?†…?–‡ç«?äº’?›?Pingback
 */
function theme_noself_ping(&$links)
{
    $home = get_option('home');
    foreach ($links as $l => $link) {
        if (0 === strpos($link, $home)) {
            unset($links[$l]);
        }
    }

}
add_action('pre_ping', 'theme_noself_ping');

/*
 * è®¢åˆ?bodyç±?
 */
function akina_body_classes($classes)
{
    // Adds a class of group-blog to blogs with more than 1 published author.
    if (is_multi_author()) {
        $classes[] = 'group-blog';
    }
    // Adds a class of hfeed to non-singular pages.
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }
    // å®š?ˆ¶ä¸­?–‡å­—ä½“class
    $classes[] = 'chinese-font';
    /*if(!wp_is_mobile()) {
    $classes[] = 'serif';
    }*/
    $classes[] = $_COOKIE['dark'.akina_option('cookie_version', '')] == '1' ? 'dark' : ' ';
    return $classes;
}
add_filter('body_class', 'akina_body_classes');

/*
 * ?›¾ç‰‡CDN
 */
add_filter('upload_dir', 'wpjam_custom_upload_dir');
function wpjam_custom_upload_dir($uploads)
{
    $upload_path = '';
    $upload_url_path = akina_option('qiniu_cdn');

    if (empty($upload_path) || 'wp-content/uploads' == $upload_path) {
        $uploads['basedir'] = WP_CONTENT_DIR . '/uploads';
    } elseif (0 !== strpos($upload_path, ABSPATH)) {
        $uploads['basedir'] = path_join(ABSPATH, $upload_path);
    } else {
        $uploads['basedir'] = $upload_path;
    }

    $uploads['path'] = $uploads['basedir'] . $uploads['subdir'];

    if ($upload_url_path) {
        $uploads['baseurl'] = $upload_url_path;
        $uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];
    }
    return $uploads;
}

/*
 * ?ˆ??™¤è‡?¸¦å°å·¥å…?
 */
function unregister_default_widgets()
{
    unregister_widget("WP_Widget_Pages");
    unregister_widget("WP_Widget_Calendar");
    unregister_widget("WP_Widget_Archives");
    unregister_widget("WP_Widget_Links");
    unregister_widget("WP_Widget_Meta");
    unregister_widget("WP_Widget_Search");
    //unregister_widget("WP_Widget_Text");
    unregister_widget("WP_Widget_Categories");
    unregister_widget("WP_Widget_Recent_Posts");
    //unregister_widget("WP_Widget_Recent_Comments");
    //unregister_widget("WP_Widget_RSS");
    //unregister_widget("WP_Widget_Tag_Cloud");
    unregister_widget("WP_Nav_Menu_Widget");
}
add_action("widgets_init", "unregister_default_widgets", 11);

/**
 * Jetpack setup function.
 *
 * See: https://jetpack.com/support/infinite-scroll/
 * See: https://jetpack.com/support/responsive-videos/
 */
function akina_jetpack_setup()
{
    // Add theme support for Infinite Scroll.
    add_theme_support('infinite-scroll', array(
        'container' => 'main',
        'render' => 'akina_infinite_scroll_render',
        'footer' => 'page',
    ));

    // Add theme support for Responsive Videos.
    add_theme_support('jetpack-responsive-videos');
}
add_action('after_setup_theme', 'akina_jetpack_setup');

/**
 * Custom render function for Infinite Scroll.
 */
function akina_infinite_scroll_render()
{
    while (have_posts()) {
        the_post();
        if (is_search()):
            get_template_part('tpl/content', 'search');
        else:
            get_template_part('tpl/content', get_post_format());
        endif;
    }
}

/*
 * ç¼–è¾‘?™¨å?å¼?
 */
function enable_more_buttons($buttons)
{
    $buttons[] = 'hr';
    $buttons[] = 'del';
    $buttons[] = 'sub';
    $buttons[] = 'sup';
    $buttons[] = 'fontselect';
    $buttons[] = 'fontsizeselect';
    $buttons[] = 'cleanup';
    $buttons[] = 'styleselect';
    $buttons[] = 'wp_page';
    $buttons[] = 'anchor';
    $buttons[] = 'backcolor';
    return $buttons;
}
add_filter("mce_buttons_3", "enable_more_buttons");
// ä¸‹è½½æŒ‰?’?
function download($atts, $content = null)
{
    return '<a class="download" href="' . $content . '" rel="external"
target="_blank" title="ä¸‹è½½åœ°å€">
<span><i class="iconfont down icon-pulldown"></i>Download</span></a>';}
add_shortcode("download", "download");

add_action('after_wp_tiny_mce', 'bolo_after_wp_tiny_mce');
function bolo_after_wp_tiny_mce($mce_settings)
{
    ?>
<script type="text/javascript">
QTags.addButton( 'download', 'ä¸‹è½½æŒ‰?’?', "[download]ä¸‹è½½åœ°å€[/download]" );
function bolo_QTnextpage_arg1() {
}
</script>
<?php }

/*
 * ??°ç™»å?•é¡?
 * @M.J
 */
//Login Page style
function custom_login()
{
    //echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/inc/login.css" />'."\n";
    echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/inc/login.css?' . SAKURA_VERSION . '" />' . "\n";
    //echo '<script type="text/javascript" src="'.get_bloginfo('template_directory').'/js/jquery.min.js"></script>'."\n";
    echo '<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/jquery/jquery@1.8.2/jquery.min.js"></script>' . "\n";
}

add_action('login_head', 'custom_login');

//Login Page Title
function custom_headertitle($title)
{
    return get_bloginfo('name');
}
add_filter('login_headertitle', 'custom_headertitle');

//Login Page Link
function custom_loginlogo_url($url)
{
    return esc_url(home_url('/'));
}
add_filter('login_headerurl', 'custom_loginlogo_url');

//Login Page Footer
function custom_html()
{
    if (akina_option('login_bg')) {
        $loginbg = akina_option('login_bg');
    } else {
        $loginbg = 'https://cdn.jsdelivr.net/gh/mashirozx/Sakura@3.2.7/images/hd.png';
    }
    echo '<script type="text/javascript" src="' . get_template_directory_uri() . '/js/login.js"></script>' . "\n";
    echo '<script type="text/javascript">' . "\n";
    echo 'jQuery("body").prepend("<div class=\"loading\"><img src=\"https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/login_loading.gif\" width=\"58\" height=\"10\"></div><div id=\"bg\"><img /></div>");' . "\n";
    echo 'jQuery(\'#bg\').children(\'img\').attr(\'src\', \'' . $loginbg . '\').load(function(){' . "\n";
    echo '	resizeImage(\'bg\');' . "\n";
    echo '	jQuery(window).bind("resize", function() { resizeImage(\'bg\'); });' . "\n";
    echo '	jQuery(\'.loading\').fadeOut();' . "\n";
    echo '});';
    echo '</script>' . "\n";
    echo '<script>
	function verificationOK(){
		var x, y, z = "verification";
		var x=$(\'#loginform\').find(\'input[name="verification"]\').val();
		//var x=document.forms["loginform"]["verification"].value; //?Ÿ?”Ÿjså®??
		var y=$(\'#registerform\').find(\'input[name="verification"]\').val();
		var z=$(\'#lostpasswordform\').find(\'input[name="verification"]\').val();
		if (x=="verification" || y=="verification" || z=="verification"){
		  alert("Please slide the block to verificate!");
		  return false;
	  }
	}
	$(document).ready(function(){
		$( \'<p><div id="verification-slider"><div id="slider"><div id="slider_bg"></div><span id="label">Â»</span><span id="labelTip">Slide to Verificate</span></div><input type="hidden" name="verification" value="verification" /></div><p>\' ).insertBefore( $( ".submit" ) );
		$(\'form\').attr(\'onsubmit\',\'return verificationOK();\');
        $(\'h1 a\').attr(\'style\',\'background-image: url(' . akina_option('logo_img') . '); \');
		$(".forgetmenot").replaceWith(\'<p class="forgetmenot">Remember Me<input name="rememberme" id="rememberme" value="forever" type="checkbox"><label for="rememberme" style="float: right;margin-top: 5px;transform: scale(2);margin-right: -10px;"></label></p>\');
	});
	</script>';
    echo '<script type="text/javascript">
		var startTime = 0;
		var endTime = 0;
		var numTime = 0;
		$(function () {
			var slider = new SliderUnlock("#slider",{
			successLabelTip : "OK"
		},function(){
			var sli_width = $("#slider_bg").width();
			$(\'#verification-slider\').html(\'\').append(\'<input id="verification-ok" class="input" type="text" size="25" value="OK!" name="verification" disabled="true" />\');

			endTime = nowTime();
			numTime = endTime-startTime;
			endTime = 0;
			startTime = 0;
			// ?·å–?ˆ°æ?‘?Š¨ä½¿?”¨çš„?—¶é—? æ»‘?Š¨çš„å®½åº¦
			// alert( numTime );
			// alert( sli_width );
		});
			slider.init();
		})

		/**
		* ?·å–?—¶é—´ç²¾ç¡?ˆ°æ?ç§’
		* @type
		*/
		function nowTime(){
			var myDate = new Date();
			var H = myDate.getHours();//?·å–å°?—?
			var M = myDate.getMinutes(); //?·å–?ˆ†?’Ÿ
			var S = myDate.getSeconds();//?·å–ç§’
			var MS = myDate.getMilliseconds();//?·å–æ¯??’
			var milliSeconds = H * 3600 * 1000 + M * 60 * 1000 + S * 1000 + MS;
			return milliSeconds;
		}
	</script>
	<script type="text/javascript" src="' . get_template_directory_uri() . '/user/verification.js"></script>';
}
add_action('login_footer', 'custom_html');

//Login message
//* Add custom message to WordPress login page
function smallenvelop_login_message($message)
{
    if (empty($message)) {
        return '<p class="message"><strong>You may try 3 times for every 5 minutes!</strong></p>';
    } else {
        return $message;
    }
}
//add_filter( 'login_message', 'smallenvelop_login_message' );

//Fix password reset bug </>
function resetpassword_message_fix($message)
{
    $message = str_replace("<", "", $message);
    $message = str_replace(">", "", $message);
    return $message;
}
add_filter('retrieve_password_message', 'resetpassword_message_fix');

//Fix register email bug </>
function new_user_message_fix($message)
{
    $show_register_ip = "æ³¨å†ŒIP | Registration IP: " . get_the_user_ip() . " (" . convertip(get_the_user_ip()) . ")\r\n\r\nå¦‚??œ?ºº?“ä½œè¯·å¿½?•¥æ??‚?»¶ | Please ignore this email if this was not your operation.\r\n\r\n";
    $message = str_replace("To set your password, visit the following address:", $show_register_ip . "?œ¨æ?è®¾ç½®å¯†?? | To set your password, visit the following address:", $message);
    $message = str_replace("<", "", $message);
    $message = str_replace(">", "\r\n\r\nè®¾ç½®å¯†????œ¨æ??™»é™† | Login here after setting password: ", $message);
    return $message;
}
add_filter('wp_new_user_notification_email', 'new_user_message_fix');

/*
 * è¯„è®ºé‚?»¶?›å¤
 */
function comment_mail_notify($comment_id)
{
    $mail_user_name = akina_option('mail_user_name') ? akina_option('mail_user_name') : 'poi';
    $comment = get_comment($comment_id);
    $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
    $spam_confirmed = $comment->comment_approved;
    $mail_notify = akina_option('mail_notify') ? get_comment_meta($parent_id, 'mail_notify', false) : false;
    $admin_notify = akina_option('admin_notify') ? '1' : (get_comment($parent_id)->comment_author_email != get_bloginfo('admin_email') ? '1' : '0');
    if (($parent_id != '') && ($spam_confirmed != 'spam') && ($admin_notify != '0') && (!$mail_notify)) {
        $wp_email = $mail_user_name . '@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
        $to = trim(get_comment($parent_id)->comment_author_email);
        $subject = 'ä½??œ? [' . get_option("blogname") . '] ?š„?•™è¨€?œ‰äº†?›åº”';
        $message = '
      <div style="background: white;
      width: 95%;
      max-width: 800px;
      margin: auto auto;
      border-radius: 5px;
      border:orange 1px solid;
      overflow: hidden;
      -webkit-box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.12);
      box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.18);">
        <header style="overflow: hidden;">
            <img style="width:100%;z-index: 666;" src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.4/img/other/head.jpg">
        </header>
        <div style="padding: 5px 20px;">
        <p style="position: relative;
        color: white;
        float: left;
        z-index: 999;
        background: orange;
        padding: 5px 30px;
        margin: -25px auto 0 ;
        box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.30)">Dear&nbsp;' . trim(get_comment($parent_id)->comment_author) . '</p>
        <br>
        <h3>?‚¨æœ‰ä¸€?¡æ¥è‡?<a style="text-decoration: none;color: orange " target="_blank" href="' . home_url() . '/">' . get_option("blogname") . '</a>?š„?›å¤</h3>
        <br>
        <p style="font-size: 14px;">?‚¨åœ¨æ–‡ç«??€Š' . get_the_title($comment->comment_post_ID) . '?€‹ä¸Š?‘è¡¨çš„è¯„è®ºï?š</p>
        <p style="border-bottom:#ddd 1px solid;border-left:#ddd 1px solid;padding-bottom:20px;background-color:#eee;margin:15px 0px;padding-left:20px;padding-right:20px;border-top:#ddd 1px solid;border-right:#ddd 1px solid;padding-top:20px">'
        . trim(get_comment($parent_id)->comment_content) . '</p>
        <p style="font-size: 14px;">' . trim($comment->comment_author) . ' ç»™?‚¨çš„?›å¤å¦‚ä¸‹ï¼š</p>
        <p style="border-bottom:#ddd 1px solid;border-left:#ddd 1px solid;padding-bottom:20px;background-color:#eee;margin:15px 0px;padding-left:20px;padding-right:20px;border-top:#ddd 1px solid;border-right:#ddd 1px solid;padding-top:20px">'
        . trim($comment->comment_content) . '</p>

      <div style="text-align: center;">
          <img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.4/img/other/hr.png" alt="hr" style="width:100%;
                                                                                                  margin:5px auto 5px auto;
                                                                                                  display: block;">
          <a style="text-transform: uppercase;
                      text-decoration: none;
                      font-size: 14px;
                      border: 2px solid #6c7575;
                      color: #2f3333;
                      padding: 10px;
                      display: inline-block;
                      margin: 10px auto 0; " target="_blank" href="' . htmlspecialchars(get_comment_link($parent_id)) . '">?‚¹å‡»æŸ¥çœ‹?›å¤?š„å®Œ?•´å…§å?</a>
      </div>
        <p style="font-size: 12px;text-align: center;color: #999;">?œ?‚?»¶ä¸ºç³»ç»Ÿ?‡?Š¨å‘?‡ºï?Œè¯·å‹¿ç›´æ¥å›å¤<br>
        &copy; ' . date(Y) . ' ' . get_option("blogname") . '</p>
      </div>
    </div>
';
        $message = convert_smilies($message);
        $message = str_replace("{{", '<img src="https://cdn.jsdelivr.net/gh/moezx/cdn@2.9.4/img/bili/hd/ic_emoji_', $message);
        $message = str_replace("}}", '.png" alt="emoji" style="height: 2em; max-height: 2em;">', $message);

        $message = str_replace('{UPLOAD}', 'https://i.loli.net/', $message);
        $message = str_replace('[/img][img]', '[/img^img]', $message);

        $message = str_replace('[img]', '<img src="', $message);
        $message = str_replace('[/img]', '" style="width:80%;display: block;margin-left: auto;margin-right: auto;">', $message);

        $message = str_replace('[/img^img]', '" style="width:80%;display: block;margin-left: auto;margin-right: auto;"><img src="', $message);
        $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
        $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
        wp_mail($to, $subject, $message, $headers);
    }
}
add_action('comment_post', 'comment_mail_notify');

/*
 * ?“¾æ¥æ–°ç?—?£æ‰“å¼€
 */
function rt_add_link_target($content)
{
    $content = str_replace('<a', '<a rel="nofollow"', $content);
    // use the <a> tag to split into segments
    $bits = explode('<a ', $content);
    // loop though the segments
    foreach ($bits as $key => $bit) {
        // fix the target="_blank" bug after the link
        if (strpos($bit, 'href') === false) {
            continue;
        }

        // fix the target="_blank" bug in the codeblock
        if (strpos(preg_replace('/code([\s\S]*?)\/code[\s]*/m', 'temp', $content), $bit) === false) {
            continue;
        }

        // find the end of each link
        $pos = strpos($bit, '>');
        // check if there is an end (only fails with malformed markup)
        if ($pos !== false) {
            // get a string with just the link's attibutes
            $part = substr($bit, 0, $pos);
            // for comparison, get the current site/network url
            $siteurl = network_site_url();
            // if the site url is in the attributes, assume it's in the href and skip, also if a target is present
            if (strpos($part, $siteurl) === false && strpos($part, 'target=') === false) {
                // add the target attribute
                $bits[$key] = 'target="_blank" ' . $bits[$key];
            }
        }
    }
    // re-assemble the content, and return it
    return implode('<a ', $bits);
}
add_filter('comment_text', 'rt_add_link_target');

// è¯„è®ºé€šè¿‡BBCode?’?…¥å›¾ç‰‡
function comment_picture_support($content)
{
    $content = str_replace('http://', 'https://', $content); // å¹²æ‰ä»»ä?•??ƒ½çš„ http
    $content = str_replace('{UPLOAD}', 'https://i.loli.net/', $content);
    $content = str_replace('[/img][img]', '[/img^img]', $content);
    $content = str_replace('[img]', '<br><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.0.2/img/svg/loader/trans.ajax-spinner-preloader.svg" data-src="', $content);
    $content = str_replace('[/img]', '" class="lazyload comment_inline_img" onerror="imgError(this)"><br>', $content);
    $content = str_replace('[/img^img]', '" class="lazyload comment_inline_img" onerror="imgError(this)"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.0.2/img/svg/loader/trans.ajax-spinner-preloader.svg" data-src="', $content);
    return $content;
}
add_filter('comment_text', 'comment_picture_support');

/*
 * ä¿?”¹è?„è®ºè¡¨?ƒ…è°ƒ?”¨è·¯å¾„
 */
add_filter('smilies_src', 'custom_smilies_src', 1, 10);
function custom_smilies_src($img_src, $img, $siteurl)
{
    return 'https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/smilies/' . $img;
}
// ç®€?•??†ç³»ç?Ÿè¡¨æƒ…åº“ï¼Œä»Š?åº”?€ƒ?™‘??‡è¯†è¡¨æƒ…?Œ…??€”?€”ä½¿ç”¨å??Š??š„?‰©å?•?ï¼Œ?Œ?—¶ä??•™?Ÿ?œ‰?‹“å±•?
// è¿˜?œ‰ä¸€ä¸?€è·?˜??¹æ?¡¨?ƒ…è°ƒ?”¨è·¯å¾„?¥åˆ¤å?š<-- æ­¤æ?•?œ€å¥½ï?
// è´´å?
function push_smilies()
{
    global $wpsmiliestrans;
    foreach ($wpsmiliestrans as $k => $v) {
        $Sname = str_replace(":", "", $k);
        $Svalue = $v;
        $return_smiles = $return_smiles . '<span title="' . $Sname . '" onclick="grin(' . "'" . $Sname . "'" . ')"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@3.1.9/img/Sakura/images/smilies/' . $Svalue . '" /></span>';
    }
    return $return_smiles;
}

function smilies_reset()
{
    global $wpsmiliestrans;
// don't bother setting up smilies if they are disabled
    if (!get_option('use_smilies')) {
        return;
    }

    $wpsmiliestrans = array(
        ':good:' => 'icon_good.gif',
        ':han:' => 'icon_han.gif',
        ':spray:' => 'icon_spray.gif',
        ':Grievance:' => 'icon_Grievance.gif',
        ':shui:' => 'icon_shui.gif',
        ':reluctantly:' => 'icon_reluctantly.gif',
        ':anger:' => 'icon_anger.gif',
        ':tongue:' => 'icon_tongue.gif',
        ':se:' => 'icon_se.gif',
        ':haha:' => 'icon_haha.gif',
        ':rmb:' => 'icon_rmb.gif',
        ':doubt:' => 'icon_doubt.gif',
        ':tear:' => 'icon_tear.gif',
        ':surprised2:' => 'icon_surprised2.gif',
        ':Happy:' => 'icon_Happy.gif',
        ':ku:' => 'icon_ku.gif',
        ':surprised:' => 'icon_surprised.gif',
        ':theblackline:' => 'icon_theblackline.gif',
        ':smilingeyes:' => 'icon_smilingeyes.gif',
        ':spit:' => 'icon_spit.gif',
        ':huaji:' => 'icon_huaji.gif',
        ':bbd:' => 'icon_bbd.gif',
        ':hu:' => 'icon_hu.gif',
        ':shame:' => 'icon_shame.gif',
        ':naive:' => 'icon_naive.gif',
        ':rbq:' => 'icon_rbq.gif',
        ':britan:' => 'icon_britan.gif',
        ':aa:' => 'icon_aa.gif',
        ':niconiconi:' => 'icon_niconiconi.gif',
        ':niconiconi-t:' => 'icon_niconiconi_t.gif',
        ':niconiconit:' => 'icon_niconiconit.gif',
        ':awesome:' => 'icon_awesome.gif',
    );
}
smilies_reset();

function push_emoji_panel()
{
    return '
        <a class="emoji-item">(?Œ’?–½âŒ’)</a>
        <a class="emoji-item">ï¼ˆï¿£â–½ï¿£ï¼‰</a>
        <a class="emoji-item">(=?ƒ»Ï‰?ƒ?=)</a>
        <a class="emoji-item">(ï½€?ƒ»Ï‰?ƒ»Â?)</a>
        <a class="emoji-item">(?€œï¿£â–³ï¿£)?€œ</a>
        <a class="emoji-item">(ï½¥âˆ€ï½?)</a>
        <a class="emoji-item">(Â°?ˆ€Â°)ï¾‰</a>
        <a class="emoji-item">(ï¿?3ï¿?)</a>
        <a class="emoji-item">?•?(ï¿£â–½ï¿£)?•?</a>
        <a class="emoji-item">(Â´_?‚ï½€)</a>
        <a class="emoji-item">?†_?†</a>
        <a class="emoji-item">?†’_?†’</a>
        <a class="emoji-item">(&lt;_&lt;)</a>
        <a class="emoji-item">(&gt;_&gt;)</a>
        <a class="emoji-item">(;Â¬_Â¬)</a>
        <a class="emoji-item">("?–”?–¡â–”)/</a>
        <a class="emoji-item">(ï¾Ÿ?”ï¾Ÿ?‰¡ï?ŸĞ´ï¾Ÿ)!?</a>
        <a class="emoji-item">Î£(ï¾ŸĞ´ï¾Ÿ;)</a>
        <a class="emoji-item">Î£(ï¿£â–¡ï¿£||)</a>
        <a class="emoji-item">(?€™ï¼›?‰ï¼›?€˜)</a>
        <a class="emoji-item">ï¼ˆ/T?”T)/</a>
        <a class="emoji-item">(^?ƒ»Ï‰?ƒ?^ )</a>
        <a class="emoji-item">(ï½¡ï½¥?‰ï½¥ï½¡)</a>
        <a class="emoji-item">(?—ï¿?(ï½?)ï¿£â—)</a>
        <a class="emoji-item">Îµ=Îµ=(?ƒ?‰§âˆ‡?‰?)?ƒ</a>
        <a class="emoji-item">(?€™ï½?_ï½¥â€˜)</a>
        <a class="emoji-item">(-_-#)</a>
        <a class="emoji-item">ï¼ˆï¿£ã¸ï¿£ï¼‰</a>
        <a class="emoji-item">(ï¿£Î?(#ï¿?)Î£</a>
        <a class="emoji-item">?ƒ?(?€˜?”?€™)ï¾‰</a>
        <a class="emoji-item">ï¼ˆ#-_-)?”?”?”?</a>
        <a class="emoji-item">(?•?°å£Â?)?•?(?”´â€”?”?</a>
        <a class="emoji-item">?†?—¡â†</a>
        <a class="emoji-item">( ?™¥Ğ´â™?)</a>
        <a class="emoji-item">_(:3?€?ˆ?)_</a>
        <a class="emoji-item">Î£&gt;?€•(?€ƒÂ°?‰Â°?€ƒ)?™¡â†’</a>
        <a class="emoji-item">?„(?„ ?„?€¢â„?‰?„?€¢â„ ?„)?„</a>
        <a class="emoji-item">(?•??ŸĞ´ï¾Ÿ)?–„ï¸»â”»â”³â•ä¸€</a>
        <a class="emoji-item">ï½?*ï½?:?‰?(?€€Îµ:)</a>
        <a class="emoji-item">(ç¬‘)</a>
        <a class="emoji-item">(æ±—)</a>
        <a class="emoji-item">(æ³?)</a>
        <a class="emoji-item">(?‹¦ç?‘)</a>
    ';
}

function get_wp_root_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base)))) {
        $path = dirname(dirname($base));
    } else
    if (@file_exists(dirname(dirname(dirname($base))))) {
        $path = dirname(dirname(dirname($base)));
    } else {
        $path = false;
    }

    if ($path != false) {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}

// bilibili smiles
$bilismiliestrans = array();
function push_bili_smilies()
{
    global $bilismiliestrans;
    $smiles_path = __DIR__ . "/images/smilies/bili/";
    $name = array('baiyan', 'fadai', 'koubi', 'qinqin', 'weiqu', 'bishi', 'fanu', 'kun', 'se', 'weixiao', 'bizui', 'ganga', 'lengmo', 'shengbing', 'wunai', 'chan', 'guilian', 'liubixue', 'shengqi', 'xiaoku', 'daku', 'guzhang', 'liuhan', 'shuizhao', 'xieyanxiao', 'dalao', 'haixiu', 'liulei', 'sikao', 'yiwen', 'dalian', 'heirenwenhao', 'miantian', 'tiaokan', 'yun', 'dianzan', 'huaixiao', 'mudengkoudai', 'tiaopi', 'zaijian', 'doge', 'jingxia', 'nanguo', 'touxiao', 'zhoumei', 'facai', 'keai', 'outu', 'tuxue', 'zhuakuang');
    $return_smiles = '';
    for ($i = 0; $i < count($name); $i++) {
        $img_size = getimagesize($smiles_path . $name[$i] . ".png");
        $img_height = $img_size["1"];
        // ?€‰?‹©é¢ç‰ˆ
        $return_smiles = $return_smiles . '<span class="emotion-secter emotion-item emotion-select-parent" onclick="grin(' . "'" . $name[$i] . "'" . ',type = \'Math\')" style="background-image: url(https://cdn.jsdelivr.net/gh/moezx/cdn@2.9.4/img/bili/hd/ic_emoji_' . $name[$i] . '.png);"><div class="img emotion-select-child" style="background-image: url(https://cdn.jsdelivr.net/gh/moezx/cdn@2.9.4/img/bili/' . $name[$i] . '.png);
        animation-duration: ' . ($img_height / 32 * 40) . 'ms;
        animation-timing-function: steps(' . ($img_height / 32) . ');
        transform: translateY(-' . ($img_height - 32) . 'px);
        height: ' . $img_height . 'px;
        "></div></span>';
        // æ­£æ–‡è½??
        $bilismiliestrans['{{' . $name[$i] . '}}'] = '<span class="emotion-inline emotion-item"><img src="https://cdn.jsdelivr.net/gh/moezx/cdn@2.9.4/img/bili/' . $name[$i] . '.png" class="img" style="/*background-image: url();*/
        animation-duration: ' . ($img_height / 32 * 40) . 'ms;
        animation-timing-function: steps(' . ($img_height / 32) . ');
        transform: translateY(-' . ($img_height - 32) . 'px);
        height: ' . $img_height . 'px;
        "></span>';
    }
    return $return_smiles;
}
push_bili_smilies();

function bili_smile_filter($content)
{
    global $bilismiliestrans;
    $content = str_replace(array_keys($bilismiliestrans), $bilismiliestrans, $content);
    return $content;
}
add_filter('the_content', 'bili_smile_filter'); //?›¿æ¢æ–‡ç«??…³é”??
add_filter('comment_text', 'bili_smile_filter'); //?›¿æ¢è?„è®ºå…³é”??

function featuredtoRSS($content)
{
    global $post;
    if (has_post_thumbnail($post->ID)) {
        $content = '<div>' . get_the_post_thumbnail($post->ID, 'medium', array('style' => 'margin-bottom: 15px;')) . '</div>' . $content;
    }
    return $content;
}
add_filter('the_excerpt_rss', 'featuredtoRSS');
add_filter('the_content_feed', 'featuredtoRSS');

//
function bili_smile_filter_rss($content)
{
    $content = str_replace("{{", '<img src="https://cdn.jsdelivr.net/gh/moezx/cdn@2.9.4/img/bili/hd/ic_emoji_', $content);
    $content = str_replace("}}", '.png" alt="emoji" style="height: 2em; max-height: 2em;">', $content);
    $content = str_replace('[img]', '<img src="', $content);
    $content = str_replace('[/img]', '" style="display: block;margin-left: auto;margin-right: auto;">', $content);
    return $content;
}
add_filter('comment_text_rss', 'bili_smile_filter_rss'); //?›¿æ¢è?„è®?rss?…³é”??

function toc_support($content)
{
    $content = str_replace('[toc]', '<div class="has-toc have-toc"></div>', $content); // TOC ?”?Œ
    $content = str_replace('[begin]', '<span class="begin">', $content); // é¦–å­—??¼å??”?Œ
    $content = str_replace('[/begin]', '</span>', $content); // é¦–å­—??¼å??”?Œ
    return $content;
}
add_filter('the_content', 'toc_support');
add_filter('the_excerpt_rss', 'toc_support');
add_filter('the_content_feed', 'toc_support');

// ?˜¾ç¤ºè®¿å?å½“?‰ IP
function get_the_user_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return apply_filters('wpb_get_ip', $ip);
}

add_shortcode('show_ip', 'get_the_user_ip');

/*æ­Œè¯*/
function hero_get_lyric()
{
    /** These are the lyrics to Hero */
    $lyrics = "";

    // Here we split it into lines
    $lyrics = explode("\n", $lyrics);

    // And then randomly choose a line
    return wptexturize($lyrics[mt_rand(0, count($lyrics) - 1)]);
}

// This just echoes the chosen line, we'll position it later
function hello_hero()
{
    $chosen = hero_get_lyric();
    echo $chosen;
}

/*ç§å¯†è¯„è®?*/
add_action('wp_ajax_nopriv_siren_private', 'siren_private');
add_action('wp_ajax_siren_private', 'siren_private');
function siren_private()
{
    $comment_id = $_POST["p_id"];
    $action = $_POST["p_action"];
    if ($action == 'set_private') {
        update_comment_meta($comment_id, '_private', 'true');
        $i_private = get_comment_meta($comment_ID, '_private', true);
        if (!empty($i_private)) {
            echo '??';
        } else {
            echo '?˜?';
        }
    }
    die;
}

//?—¶é—´å??ˆ—
function memory_archives_list()
{
    if (true) {
        $output = '<div id="archives"><p style="text-align:right;">[<span id="al_expand_collapse">' . __("All expand/collapse", "sakura") /*?…¨éƒ¨å?•å¼€/?”¶ç¼©*/ . '</span>]<!-- (æ³?: ?‚¹å‡»æœˆä»½å?»¥å±•å¼€)--></p>';
        $the_query = new WP_Query('posts_per_page=-1&ignore_sticky_posts=1&post_type=post'); //update: ?Š?ä¸Šå¿½ç•¥ç½®é¡¶æ–‡ç«?
        $year = 0;
        $mon = 0;
        $i = 0;
        $j = 0;
        while ($the_query->have_posts()): $the_query->the_post();
            $year_tmp = get_the_time('Y');
            $mon_tmp = get_the_time('m');
            $y = $year;
            $m = $mon;
            if ($mon != $mon_tmp && $mon > 0) {
                $output .= '</ul></li>';
            }

            if ($year != $year_tmp && $year > 0) {
                $output .= '</ul>';
            }

            if ($year != $year_tmp) {
                $year = $year_tmp;
                $output .= '<h3 class="al_year">' . $year . __(" ", "year", "sakura") . /*å¹?*/' </h3><ul class="al_mon_list">'; //è¾“?‡ºå¹´ä»?
            }
            if ($mon != $mon_tmp) {
                $mon = $mon_tmp;
                $output .= '<li class="al_li"><span class="al_mon"><span style="color:#0bf;">' . get_the_time('M') . '</span> (<span id="post-num"></span>' . __(" post(s)", "sakura") /*ç¯‡?–‡ç«?*/ . ')</span><ul class="al_post_list">'; //è¾“?‡ºæœˆä»?
            }
            $output .= '<li>' . '<a href="' . get_permalink() . '"><span style="color:#0bf;">' /*get_the_time('d'.__(" ","sakura")) ?—?*/ . '</span>' . get_the_title() . ' <span>(' . get_post_views(get_the_ID()) . ' <span class="fa fa-fire" aria-hidden="true"></span> / ' . get_comments_number('0', '1', '%') . ' <span class="fa fa-commenting" aria-hidden="true"></span>)</span></a></li>'; //è¾“?‡ºæ–‡ç«??—¥æœŸ?’Œ??‡é¢˜
        endwhile;
        wp_reset_postdata();
        $output .= '</ul></li></ul> <!--<ul class="al_mon_list"><li><ul class="al_post_list" style="display: block;"><li>?šå®¢å·²ç»?Œ?Œ?“’è¿è¡Œäº†<span id="monitorday"></span>å¤?</li></ul></li></ul>--></div>';
        #update_option('memory_archives_list', $output);
    }
    echo $output;
}

/*
 * ?š?— Dashboard
 */
/* Remove the "Dashboard" from the admin menu for non-admin users */
function remove_dashboard()
{
    global $current_user, $menu, $submenu;
    wp_get_current_user();

    if (!in_array('administrator', $current_user->roles)) {
        reset($menu);
        $page = key($menu);
        while ((__('Dashboard') != $menu[$page][0]) && next($menu)) {
            $page = key($menu);
        }
        if (__('Dashboard') == $menu[$page][0]) {
            unset($menu[$page]);
        }
        reset($menu);
        $page = key($menu);
        while (!$current_user->has_cap($menu[$page][1]) && next($menu)) {
            $page = key($menu);
        }
        if (preg_match('#wp-admin/?(index.php)?$#', $_SERVER['REQUEST_URI']) &&
            ('index.php' != $menu[$page][2])) {
            wp_redirect(get_option('siteurl') . '/wp-admin/profile.php');
        }
    }
}
add_action('admin_menu', 'remove_dashboard');

/**
 * Filter the except length to 20 words. ?™?ˆ¶æ‘˜è¦?•¿åº¦
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */

function GBsubstr($string, $start, $length)
{
    if (strlen($string) > $length) {
        $str = null;
        $len = 0;
        $i = $start;
        while ($len < $length) {
            if (ord(substr($string, $i, 1)) > 0xc0) {
                $str .= substr($string, $i, 3);
                $i += 3;
            } elseif (ord(substr($string, $i, 1)) > 0xa0) {
                $str .= substr($string, $i, 2);
                $i += 2;
            } else {
                $str .= substr($string, $i, 1);
                $i++;
            }
            $len++;
        }
        return $str;
    } else {
        return $string;
    }
}

function excerpt_length($exp)
{
    if (!function_exists('mb_substr')) {
        $exp = GBsubstr($exp, 0, 80);
    } else {
        /*
         * To use mb_substr() function, you should uncomment "extension=php_mbstring.dll" in php.ini
         */
        $exp = mb_substr($exp, 0, 80);
    }
    return $exp;
}
add_filter('the_excerpt', 'excerpt_length');

/*
 * ??°è·¯å¾„
 */
/*
add_filter('site_url',  'wpadmin_filter', 10, 3);
function wpadmin_filter( $url, $path, $orig_scheme ) {
$old  = array( "/(wp-admin)/");
$admin_dir = WP_ADMIN_DIR;
$new  = array($admin_dir);
return preg_replace( $old, $new, $url, 1);
}
 */

function admin_ini()
{
    wp_enqueue_style('admin-styles-fix-icon', get_site_url() . '/wp-includes/css/dashicons.css');
    wp_enqueue_style('cus-styles-fit', get_template_directory_uri() . '/inc/css/dashboard-fix.css');
    wp_enqueue_script('lazyload', 'https://cdn.jsdelivr.net/npm/lazyload@2.0.0-beta.2/lazyload.min.js');
}
add_action('admin_enqueue_scripts', 'admin_ini');

function custom_admin_js()
{
    echo '<script>
    window.onload=function(){
        lazyload();

        try{
            document.querySelector("#scheme-tip .notice-dismiss").addEventListener("click", function(){
                location.href="?scheme-tip-dismissed' . BUILD_VERSION . '";
            });
        } catch(e){}
    }
    </script>';
}
add_action('admin_footer', 'custom_admin_js');

/*
 * ??°é€š?Ÿ?
 */
function scheme_tip()
{
    $msg = '<b>Why not try the new admin dashboard color scheme <a href="/wp-admin/profile.php">here</a>?</b>';
    if (get_user_locale(get_current_user_id()) == "zh_CN") {
        $msg = '<b>è¯•ä¸€è¯•?–°å?°ç•Œ??<a href="/wp-admin/profile.php">?…?‰²æ–¹æ?ˆ</a>?§ï?Ÿ</b>';
    }
    if (get_user_locale(get_current_user_id()) == "zh_TW") {
        $msg = '<b>è©¦ä?€è©¦æ–°å?Œ?°ç•Œ??<a href="/wp-admin/profile.php">?‰²å½©?…ç½?</a>?§ï?Ÿ</b>';
    }
    if (get_user_locale(get_current_user_id()) == "ja") {
        $msg = '<b>?–°ã—?„<a href="/wp-admin/profile.php">ç®¡ç†?”»é??…?‰?</a>?‚’è©¦ã—?¾ã™?‹ï¼Ÿ</b>';
    }
    if (get_user_locale(get_current_user_id()) == "ja-JP") {
        $msg = '<b>?–°ã—?„<a href="/wp-admin/profile.php">ç®¡ç†?”»é??…?‰?</a>?‚’è©¦ã—?¾ã™?‹ï¼Ÿ</b>';
    }

    $user_id = get_current_user_id();
    if (!get_user_meta($user_id, 'scheme-tip-dismissed' . BUILD_VERSION)) {
        echo '<div class="notice notice-success is-dismissible" id="scheme-tip"><p><b>' . $msg . '</b></p></div>';
    }
}

add_action('admin_notices', 'scheme_tip');

function scheme_tip_dismissed()
{
    $user_id = get_current_user_id();
    if (isset($_GET['scheme-tip-dismissed' . BUILD_VERSION])) {
        add_user_meta($user_id, 'scheme-tip-dismissed' . BUILD_VERSION, 'true', true);
    }

}
add_action('admin_init', 'scheme_tip_dismissed');

//dashboard scheme
function dash_scheme($key, $name, $col1, $col2, $col3, $col4, $base, $focus, $current, $rules = "")
{
    $hash = "color_1=" . str_replace("#", "", $col1) .
    "&color_2=" . str_replace("#", "", $col2) .
    "&color_3=" . str_replace("#", "", $col3) .
    "&color_4=" . str_replace("#", "", $col4) .
    "&rules=" . urlencode($rules);

    wp_admin_css_color(
        $key,
        $name,
        get_template_directory_uri() . "/inc/dash-scheme.php?" . $hash,
        array($col1, $col2, $col3, $col4),
        array('base' => $base, 'focus' => $focus, 'current' => $current)
    );
}

//Sakura
dash_scheme($key = "sakura", $name = "Sakura?ŸŒ?",
    $col1 = '#8fbbb1', $col2 = '#bfd8d2', $col3 = '#fedcd2', $col4 = '#df744a',
    $base = "#e5f8ff", $focus = "#fff", $current = "#fff",
    $rules = "#adminmenu .wp-has-current-submenu .wp-submenu a,#adminmenu .wp-has-current-submenu.opensub .wp-submenu a,#adminmenu .wp-submenu a,#adminmenu a.wp-has-current-submenu:focus+.wp-submenu a,#wpadminbar .ab-submenu .ab-item,#wpadminbar .quicklinks .menupop ul li a,#wpadminbar .quicklinks .menupop.hover ul li a,#wpadminbar.nojs .quicklinks .menupop:hover ul li a,.folded #adminmenu .wp-has-current-submenu .wp-submenu a{color:#f3f2f1}body{background-image:url(https://view.moezx.cc/images/2018/01/03/sakura.png);background-attachment:fixed;}#wpcontent{background:rgba(255,255,255,.0)}.wp-core-ui .button-primary{background:#bfd8d2!important;border-color:#8fbbb1 #8fbbb1 #8fbbb1!important;color:#fff!important;box-shadow:0 1px 0 #8fbbb1!important;text-shadow:0 -1px 1px #8fbbb1,1px 0 1px #8fbbb1,0 1px 1px #8fbbb1,-1px 0 1px #8fbbb1!important}");

//custom
dash_scheme($key = "custom", $name = "Custom",
    $col1 = akina_option('dash_scheme_color_a'), $col2 = akina_option('dash_scheme_color_b'), $col3 = akina_option('dash_scheme_color_c'), $col4 = akina_option('dash_scheme_color_d'),
    $base = akina_option('dash_scheme_color_base'), $focus = akina_option('dash_scheme_color_focus'), $current = akina_option('dash_scheme_color_current'),
    $rules = akina_option('dash_scheme_css_rules'));

//Set Default Admin Color Scheme for New Users
function set_default_admin_color($user_id)
{
    $args = array(
        'ID' => $user_id,
        'admin_color' => 'sunrise',
    );
    wp_update_user($args);
}
//add_action('user_register', 'set_default_admin_color');

//Stop Users From Switching Admin Color Schemes
//if ( !current_user_can('manage_options') ) remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

// WordPress Custom Font @ Admin
function custom_admin_open_sans_font()
{
    echo '<link href="js/google-fonts.css" rel="stylesheet">' . PHP_EOL;
    echo '<style>body, #wpadminbar *:not([class="ab-icon"]), .wp-core-ui, .media-menu, .media-frame *, .media-modal *{font-family:"Noto Serif SC","Source Han Serif SC","Source Han Serif","source-han-serif-sc","PT Serif","SongTi SC","MicroSoft Yahei",Georgia,serif !important;}</style>' . PHP_EOL;
}
add_action('admin_head', 'custom_admin_open_sans_font');

// WordPress Custom Font @ Admin Frontend Toolbar
function custom_admin_open_sans_font_frontend_toolbar()
{
    if (current_user_can('administrator')) {
        echo '<link href="js/google-fonts.cssv" rel="stylesheet">' . PHP_EOL;
        echo '<style>#wpadminbar *:not([class="ab-icon"]){font-family:"Noto Serif SC","Source Han Serif SC","Source Han Serif","source-han-serif-sc","PT Serif","SongTi SC","MicroSoft Yahei",Georgia,serif !important;}</style>' . PHP_EOL;
    }
}
add_action('wp_head', 'custom_admin_open_sans_font_frontend_toolbar');

// WordPress Custom Font @ Admin Login
function custom_admin_open_sans_font_login_page()
{
    if (stripos($_SERVER["SCRIPT_NAME"], strrchr(wp_login_url(), '/')) !== false) {
        echo '<link href="js/google-fonts.css" rel="stylesheet">' . PHP_EOL;
        echo '<style>body{font-family:"Noto Serif SC","Source Han Serif SC","Source Han Serif","source-han-serif-sc","PT Serif","SongTi SC","MicroSoft Yahei",Georgia,serif !important;}</style>' . PHP_EOL;
    }
}
add_action('login_head', 'custom_admin_open_sans_font_login_page');

// ?˜»æ??ƒ?œ¾æ³¨?†Œ
add_action('register_post', 'codecheese_register_post', 10, 3);

function codecheese_register_post($sanitized_user_login, $user_email, $errors)
{

    // Blocked domains
    $domains = array('net.buzzcluby.com',
        'buzzcluby.com',
        'mail.ru',
        'h.captchaeu.info',
        'edge.codyting.com');

    // Get visitor email domain
    $email = explode('@', $user_email);

    // Check and display error message for the registration form if exists
    if (in_array($email[1], $domains)) {
        $errors->add('invalid_email', __('<b>ERROR</b>: This email domain (<b>@' . $email[1] . '</b>) has been blocked. Please use another email.'));
    }

}

// html ??‡ç­¾å?„?†?™?
function html_tag_parser($content)
{
    if (!is_feed()) {
        if (akina_option('lazyload') && akina_option('lazyload_spinner')) {
            $content = preg_replace(
                '/<img(.+)src=[\'"]([^\'"]+)[\'"](.*)>/i',
                "<img $1 class=\"lazyload\" data-src=\"$2\" src=\"" . akina_option('lazyload_spinner') . "\" onerror=\"imgError(this)\" $3 >\n<noscript>$0</noscript>",
                $content
            );
        }

        //Fancybox
        /* Markdown Regex Pattern for Matching URLs:
         * https://daringfireball.net/2010/07/improved_regex_for_matching_urls
         */
        $url_regex = '((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?Â«Â»?€œ?€?€˜?€™]))';

        //With Thumbnail: !{alt}(url)[th_url]
        if (preg_match_all('/\!\{.*?\)\[.*?\]/i', $content, $matches)) {
            for ($i = 0; $i < sizeof($matches); $i++) {
                $content = str_replace($matches[$i], preg_replace(
                    '/!\{([^\{\}]+)*\}\(' . $url_regex . '\)\[' . $url_regex . '\]/i',
                    '<a data-fancybox="gallery"
                        data-caption="$1"
                        class="fancybox"
                        href="$2"
                        alt="$1"
                        title="$1"><img src="$7" target="_blank" rel="nofollow" class="fancybox"></a>',
                    $matches[$i]),
                    $content);
            }
        }

        //Without Thumbnail :!{alt}(url)
        $content = preg_replace(
            '/!\{([^\{\}]+)*\}\(' . $url_regex . '\)/i',
            '<a data-fancybox="gallery"
                data-caption="$1"
                class="fancybox"
                href="$2"
                alt="$1"
                title="$1"><img src="$2" target="_blank" rel="nofollow" class="fancybox"></a>',
            $content
        );

        //Github cards
        $content = preg_replace(
            '/\[github repo=[\'"]([^\'"]+)[\'"]\]/i',
            '
            <iframe frameborder="0" scrolling="0" allowtransparency="true"
                    src="https://api.2heng.xin/github-card/?repo=$1"
                    width="400" height="153"
                    style="margin-left: 50%; transform: translateX(-50%);"></iframe>
            ',
            $content
        );
    }
    //html tag parser for rss
    if (is_feed()) {
        //Fancybox
        $url_regex = '((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?Â«Â»?€œ?€?€˜?€™]))';
        if (preg_match_all('/\!\{.*?\)\[.*?\]/i', $content, $matches)) {
            for ($i = 0; $i < sizeof($matches); $i++) {
                $content = str_replace(
                    $matches[$i],
                    preg_replace('/!\{([^\{\}]+)*\}\(' . $url_regex . '\)\[' . $url_regex . '\]/i', '<a href="$2"><img src="$7" alt="$1" title="$1"></a>', $matches[$i]),
                    $content
                );
            }
        }
        $content = preg_replace('/!\{([^\{\}]+)*\}\(' . $url_regex . '\)/i', '<a href="$2"><img src="$2" alt="$1" title="$1"></a>', $content);

        //Github cards
        $content = preg_replace(
            '/\[github repo=[\'"]([^\'"]+)[\'"]\]/i',
            '<a href="https://github.com/$1">',
            $content
        );
    }
    return $content;
}
add_filter('the_content', 'html_tag_parser'); //?›¿æ¢æ–‡ç«??…³é”??
//add_filter( 'comment_text', 'html_tag_parser' );//?›¿æ¢è?„è®ºå…³é”??

/*
 * QQ è¯„è®?
 */
// ?•°æ??“?’?…¥è?„è®ºè¡¨?•?š„qqå­—æ®?
add_action('wp_insert_comment', 'sql_insert_qq_field', 10, 2);
function sql_insert_qq_field($comment_ID, $commmentdata)
{
    $qq = isset($_POST['new_field_qq']) ? $_POST['new_field_qq'] : false;
    update_comment_meta($comment_ID, 'new_field_qq', $qq); // new_field_qq ?˜?¡¨?•name?€¼ï?Œä¹Ÿ?˜??˜?‚¨åœ¨æ•°æ??“?‡Œ?š„å­—æ®µåå­—
}
// ??°è?„è®ºä¸­?˜¾ç¤ºqqå­—æ®?
add_filter('manage_edit-comments_columns', 'add_comments_columns');
add_action('manage_comments_custom_column', 'output_comments_qq_columns', 10, 2);
function add_comments_columns($columns)
{
    $columns['new_field_qq'] = __('QQ'); // ?–°å??ˆ—?ç§?
    return $columns;
}
function output_comments_qq_columns($column_name, $comment_id)
{
    switch ($column_name) {
        case "new_field_qq":
            // è¿™?˜??“?‡ºå€¼ï?Œ??»¥?‹¿æ¥åœ¨å‰ç«??“?‡ºï?Œè¿™?‡Œå·²ç??œ¨é’©å?manage_comments_custom_columnä¸Šè¾“?‡ºä?†
            echo get_comment_meta($comment_id, 'new_field_qq', true);
            break;
    }
}
/**
 * å¤´åƒè°ƒ?”¨è·¯å¾„
 */
add_filter('get_avatar', 'change_avatar', 10, 3);
function change_avatar($avatar)
{
    global $comment, $sakura_privkey;
    if ($comment) {
        if (get_comment_meta($comment->comment_ID, 'new_field_qq', true)) {
            $qq_number = get_comment_meta($comment->comment_ID, 'new_field_qq', true);
            if (akina_option('qq_avatar_link') == 'off') {
                return '<img src="https://q2.qlogo.cn/headimg_dl?dst_uin=' . $qq_number . '&spec=100" data-src="' . stripslashes($m[1]) . '" class="lazyload avatar avatar-24 photo" alt="?Ÿ˜€" width="24" height="24" onerror="imgError(this,1)">';
            } elseif (akina_option('qq_avatar_link') == 'type_3') {
                $qqavatar = file_get_contents('http://ptlogin2.qq.com/getface?appid=1006102&imgtype=3&uin=' . $qq_number);
                preg_match('/:\"([^\"]*)\"/i', $qqavatar, $matches);
                return '<img src="' . $matches[1] . '" data-src="' . stripslashes($m[1]) . '" class="lazyload avatar avatar-24 photo" alt="?Ÿ˜€" width="24" height="24" onerror="imgError(this,1)">';
            } else {
                $iv = str_repeat($sakura_privkey, 2);
                $encrypted = openssl_encrypt($qq_number, 'aes-128-cbc', $sakura_privkey, 0, $iv);
                $encrypted = urlencode(base64_encode($encrypted));
                return '<img src="' . rest_url("sakura/v1/qqinfo/avatar") . '?qq=' . $encrypted . '"class="lazyload avatar avatar-24 photo" alt="?Ÿ˜€" width="24" height="24" onerror="imgError(this,1)">';
            }
        } else {
            return $avatar;
        }
    } else {
        return $avatar;
    }
}

// default feature image
function DEFAULT_FEATURE_IMAGE()
{
    return rest_url('sakura/v1/image/feature') . '?' . rand(1, 1000);
}

//?˜²æ?è®¾ç½®ç½?¡¶?–‡ç«??€??ˆ?š„?›¾ç‰‡?Œä¾?bug
add_action( 'pre_get_posts', function( $q ){
    if ( $q->is_home() && $q->is_main_query() ){
        $q->set( 'posts_per_page', 10 - sizeof(get_option( 'sticky_posts' )) );
        if ( $q->get( 'paged' ) > 1 )
            $q->set( 'post__not_in', get_option( 'sticky_posts' ) );
    }
    
});

//è¯„è®ºå›å¤
function sakura_comment_notify($comment_id)
{
    if (!$_POST['mail-notify']) {
        update_comment_meta($comment_id, 'mail_notify', 'false');
    }

}
add_action('comment_post', 'sakura_comment_notify');

//ä¾§æ?å°å·¥å…?
if (akina_option('sakura_widget')) {
    if (function_exists('register_sidebar')) {
        register_sidebar(array(
            'name' => __('Sidebar'), //ä¾§æ?
            'id' => 'sakura_widget',
            'before_widget' => '<div class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<div class="title"><h2>',
            'after_title' => '</h2></div>',
        ));
    }
}

// è¯„è®?Markdownè§£æ
function markdown_parser($incoming_comment)
{
    global $wpdb, $comment_markdown_content;
    $re = '/```([\s\S]*?)```[\s]*|`{1,2}[^`](.*?)`{1,2}|\[.*?\]\([\s\S]*?\)/m';
    if (preg_replace($re, 'temp', $incoming_comment['comment_content']) != strip_tags(preg_replace($re, 'temp', $incoming_comment['comment_content']))) {
        siren_ajax_comment_err('è¯„è®ºå?”?ŒMarkdown?•¦ï?Œè§è°…?•?(ï¿£â–½ï¿£)?•?<br>Markdown Supported while <i class="fa fa-code" aria-hidden="true"></i> Forbidden');
        return ($incoming_comment);
    }
    $myCustomer = $wpdb->get_row("SELECT * FROM wp_comments");
    //Add column if not present.
    if (!isset($myCustomer->comment_markdown)) {
        $wpdb->query("ALTER TABLE wp_comments ADD comment_markdown text");
    }
    $comment_markdown_content = $incoming_comment['comment_content'];
    include 'inc/Parsedown.php';
    $Parsedown = new Parsedown();
    $incoming_comment['comment_content'] = $Parsedown->setUrlsLinked(false)->text($incoming_comment['comment_content']);
    return $incoming_comment;
}
add_filter('preprocess_comment', 'markdown_parser');

//ä¿å­˜Markdownè¯„è®?
function save_markdown_comment($comment_ID, $comment_approved)
{
    global $wpdb, $comment_markdown_content;
    $comment = get_comment($comment_ID);
    $comment_content = $comment_markdown_content;
    //store markdow content
    $wpdb->query("UPDATE wp_comments SET comment_markdown='" . $comment_content . "' WHERE comment_ID='" . $comment_ID . "';");
}
add_action('comment_post', 'save_markdown_comment', 10, 2);

//?‰“å¼€è¯„è®?HTML??‡ç­¾é™?ˆ?
function allow_more_tag_in_comment()
{
    global $allowedtags;
    $allowedtags['pre'] = array('class' => array());
    $allowedtags['code'] = array('class' => array());
    $allowedtags['h1'] = array('class' => array());
    $allowedtags['h2'] = array('class' => array());
    $allowedtags['h3'] = array('class' => array());
    $allowedtags['h4'] = array('class' => array());
    $allowedtags['h5'] = array('class' => array());
    $allowedtags['ul'] = array('class' => array());
    $allowedtags['ol'] = array('class' => array());
    $allowedtags['li'] = array('class' => array());
    $allowedtags['td'] = array('class' => array());
    $allowedtags['th'] = array('class' => array());
    $allowedtags['tr'] = array('class' => array());
    $allowedtags['table'] = array('class' => array());
    $allowedtags['thead'] = array('class' => array());
    $allowedtags['tbody'] = array('class' => array());
    $allowedtags['span'] = array('class' => array());
}
add_action('pre_comment_on_post', 'allow_more_tag_in_comment');

/*
 * ?š?œºå›?
 */
function create_sakura_table()
{
    global $wpdb, $sakura_image_array, $sakura_privkey;
    $sakura_table_name = $wpdb->base_prefix . 'sakura';
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta("CREATE TABLE IF NOT EXISTS `" . $sakura_table_name . "` (
        `mate_key` varchar(50) COLLATE utf8_bin NOT NULL,
        `mate_value` text COLLATE utf8_bin NOT NULL,
        PRIMARY KEY (`mate_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;");
    //default data
    if (!$wpdb->get_var("SELECT COUNT(*) FROM $sakura_table_name WHERE mate_key = 'manifest_json'")) {
        $manifest = array(
            "mate_key" => "manifest_json",
            "mate_value" => file_get_contents(get_template_directory() . "/manifest/manifest.json"),
        );
        $wpdb->insert($sakura_table_name, $manifest);
    }
    if (!$wpdb->get_var("SELECT COUNT(*) FROM $sakura_table_name WHERE mate_key = 'json_time'")) {
        $time = array(
            "mate_key" => "json_time",
            "mate_value" => date("Y-m-d H:i:s", time()),
        );
        $wpdb->insert($sakura_table_name, $time);
    }
    if (!$wpdb->get_var("SELECT COUNT(*) FROM $sakura_table_name WHERE mate_key = 'privkey'")) {
        $privkey = array(
            "mate_key" => "privkey",
            "mate_value" => wp_generate_password(8),
        );
        $wpdb->insert($sakura_table_name, $privkey);
    }
    //reduce sql query
    $sakura_image_array = $wpdb->get_var("SELECT `mate_value` FROM  $sakura_table_name WHERE `mate_key`='manifest_json'");
    $sakura_privkey = $wpdb->get_var("SELECT `mate_value` FROM  $sakura_table_name WHERE `mate_key`='privkey'");
}
add_action('after_setup_theme', 'create_sakura_table');

//rest api?”?Œ
function permalink_tip()
{
    if ( !get_option('permalink_structure') ){
        $msg = __('<b> For a better experience, please do not set <a href="/wp-admin/options-permalink.php"> permalink </a> as plain. To do this, you may need to configure <a href="https://www.wpdaxue.com/wordpress-rewriterule.html" target="_blank"> pseudo-static </a>. </ b>','sakura'); /*<b>ä¸ºä?†?›´å¥½?š„ä½¿ç”¨ä?“éªŒï¼Œè¯·ä?è¦å°†<a href="/wp-admin/options-permalink.php">?›ºå?š?“¾æ?</a>è®¾ç½®ä¸ºæœ´ç???€‚ä¸ºæ?ï¼Œ?‚¨å?ƒ½éœ€è¦?…ç½?<a href="https://www.wpdaxue.com/wordpress-rewriterule.html" target="_blank">ä¼?™?€</a>?€‚</b>*/
        echo '<div class="notice notice-success is-dismissible" id="scheme-tip"><p><b>' . $msg . '</b></p></div>';
    }
}
add_action('admin_notices', 'permalink_tip');
//code end
