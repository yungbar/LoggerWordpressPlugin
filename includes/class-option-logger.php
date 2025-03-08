<?php
/**
 * Класс для отслеживания изменений настроек WordPress
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_Option_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    // Список важных опций для отслеживания
    private $important_options = array(
        'blogname', 'blogdescription', 'siteurl', 'home', 
        'admin_email', 'default_role', 'timezone_string',
        'date_format', 'time_format', 'start_of_week',
        'permalink_structure', 'theme_mods_', 'sidebars_widgets',
        'widget_', 'nav_menu', 'show_on_front', 'page_on_front',
        'page_for_posts', 'posts_per_page', 'thumbnail_size_w',
        'thumbnail_size_h'
    );
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания изменений настроек
        add_action('updated_option', array($this, 'log_option_update'), 10, 3);
        add_action('added_option', array($this, 'log_option_add'), 10, 2);
        add_action('deleted_option', array($this, 'log_option_delete'), 10, 1);
        
        // Отслеживание страницы настроек
        add_action('admin_init', array($this, 'setup_settings_logging'));
        
        // Хуки для перехвата сохранения настроек в кастомайзере
        add_action('customize_save', array($this, 'log_customizer_save_start'));
        add_action('customize_save_after', array($this, 'log_customizer_save_end'));
    }
    
    /**
     * Проверка, является ли опция важной для отслеживания
     */
    private function is_important_option($option_name) {
        // Игнорирование системных и транзиентных опций
        if (strpos($option_name, '_transient_') === 0 || 
            strpos($option_name, '_site_transient_') === 0 ||
            strpos($option_name, 'cron') === 0) {
            return false;
        }
        
        // Проверка, входит ли опция в список важных
        foreach ($this->important_options as $important_option) {
            if (strpos($option_name, $important_option) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Логирование обновления опций
     */
    public function log_option_update($option_name, $old_value, $new_value) {
        // Проверяем, является ли опция важной
        if (!$this->is_important_option($option_name)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Изменил настройку: "' . $option_name . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование добавления опций
     */
    public function log_option_add($option_name, $option_value) {
        // Проверяем, является ли опция важной
        if (!$this->is_important_option($option_name)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Добавил настройку: "' . $option_name . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления опций
     */
    public function log_option_delete($option_name) {
        // Проверяем, является ли опция важной
        if (!$this->is_important_option($option_name)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил настройку: "' . $option_name . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Настройка для отслеживания страниц настроек
     */
    public function setup_settings_logging() {
        global $pagenow;
        
        // Отслеживаем конкретные страницы настроек
        if (is_admin() && !empty($pagenow)) {
            // Если это страница настроек
            if ($pagenow == 'options.php' || 
                ($pagenow == 'admin.php' && isset($_GET['page']) && strpos($_GET['page'], 'settings') !== false)) {
                add_action('admin_notices', array($this, 'log_settings_page_access'));
            }
        }
    }
    
    /**
     * Логирование доступа к странице настроек
     */
    public function log_settings_page_access() {
        global $pagenow, $plugin_page;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Определяем страницу настроек
        $page = $pagenow;
        if (!empty($plugin_page)) {
            $page .= ' (plugin page: ' . $plugin_page . ')';
        }
        
        // Формируем сообщение для лога
        $message = 'Открыл страницу настроек: ' . $page;
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование начала сохранения настроек в кастомайзере
     */
    public function log_customizer_save_start($manager) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Начал сохранение изменений в кастомайзере';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование завершения сохранения настроек в кастомайзере
     */
    public function log_customizer_save_end($manager) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Завершил сохранение изменений в кастомайзере';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
}