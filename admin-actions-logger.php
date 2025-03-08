<?php
/**
 * Plugin Name: Admin Actions Logger
 * Description: Логирует все изменения в админ-панели, файлах и страницах WordPress, включая WooCommerce. Не логирует действия пользователя admin и пользователей с ролью "Клиент".
 * Version: 1.0.6
 * Author: Bardak
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

// Определение констант плагина
define('AAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AAL_LOG_FILE', WP_CONTENT_DIR . '/settings-changes-log.txt');

// Подключение классов плагина
require_once AAL_PLUGIN_DIR . 'includes/class-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-admin-page.php';
require_once AAL_PLUGIN_DIR . 'includes/class-post-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-option-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-file-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-user-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-menu-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-media-logger.php';
require_once AAL_PLUGIN_DIR . 'includes/class-woocommerce-logger.php';

/**
 * Основной класс плагина
 */
class Admin_Actions_Logger_Plugin {
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация плагина при загрузке WordPress
        add_action('plugins_loaded', array($this, 'init_plugin'));
        
        // Действия при активации плагина
        register_activation_hook(__FILE__, array($this, 'plugin_activate'));
    }
    
    /**
     * Инициализация плагина
     */
    public function init_plugin() {
        // Инициализация логгера
        $logger = new AAL_Logger();
        
        // Инициализация административной страницы
        $admin_page = new AAL_Admin_Page();
        
        // Инициализация логгеров для разных типов действий
        $post_logger = new AAL_Post_Logger();
        $option_logger = new AAL_Option_Logger();
        $file_logger = new AAL_File_Logger();
        $user_logger = new AAL_User_Logger();
        $menu_logger = new AAL_Menu_Logger();
        $media_logger = new AAL_Media_Logger();
        
        // Инициализация логгера WooCommerce, если WooCommerce активен
        if ($this->is_woocommerce_active()) {
            $woocommerce_logger = new AAL_WooCommerce_Logger();
        }
    }
    
    /**
     * Проверка активности WooCommerce
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Действия при активации плагина
     */
    public function plugin_activate() {
        // Инициализация логгера
        $logger = new AAL_Logger();
        
        // Запись в лог об активации плагина
        $logger->log('Плагин "Admin Actions Logger" активирован.');
    }
}

// Создание экземпляра основного класса плагина
$admin_actions_logger_plugin = new Admin_Actions_Logger_Plugin();