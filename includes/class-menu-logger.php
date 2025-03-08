<?php
/**
 * Класс для отслеживания изменений меню WordPress
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_Menu_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания изменений в меню
        add_action('wp_create_nav_menu', array($this, 'log_menu_create'), 10, 2);
        add_action('wp_update_nav_menu', array($this, 'log_menu_update'), 10, 2);
        add_action('wp_delete_nav_menu', array($this, 'log_menu_delete'), 10, 1);
        
        // Хуки для отслеживания изменений элементов меню
        add_action('wp_update_nav_menu_item', array($this, 'log_menu_item_update'), 10, 3);
        add_action('wp_add_nav_menu_item', array($this, 'log_menu_item_add'), 10, 3);
        
        // Хуки для отслеживания изменений виджетов
        add_action('widget_update_callback', array($this, 'log_widget_update'), 10, 4);
        add_action('sidebar_admin_setup', array($this, 'log_widget_changes'));
    }
    
    /**
     * Логирование создания меню
     */
    public function log_menu_create($menu_id, $menu_data) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Создал меню: "' . $menu_data['menu-name'] . '" (ID: ' . $menu_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновления меню
     */
    public function log_menu_update($menu_id, $menu_data = null) {
        // Получаем информацию о меню
        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Обновил меню: "' . $menu->name . '" (ID: ' . $menu_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления меню
     */
    public function log_menu_delete($menu_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил меню (ID: ' . $menu_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновления пункта меню
     */
    public function log_menu_item_update($menu_id, $menu_item_db_id, $args) {
        // Получаем информацию о меню
        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) return;
        
        // Получаем информацию о пункте меню
        $menu_item = get_post($menu_item_db_id);
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Обновил пункт меню: "' . $menu_item->post_title . '" в меню "' . $menu->name . '" (ID: ' . $menu_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование добавления пункта меню
     */
    public function log_menu_item_add($menu_id, $menu_item_db_id, $args) {
        // Получаем информацию о меню
        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) return;
        
        // Получаем информацию о пункте меню
        $menu_item = get_post($menu_item_db_id);
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Добавил пункт меню: "' . $menu_item->post_title . '" в меню "' . $menu->name . '" (ID: ' . $menu_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновления виджета
     */
    public function log_widget_update($instance, $new_instance, $old_instance, $widget) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем имя виджета
        $widget_name = isset($widget->name) ? $widget->name : get_class($widget);
        
        // Формируем сообщение для лога
        $message = 'Обновил виджет: "' . $widget_name . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
        
        return $instance;
    }
    
    /**
     * Логирование изменений виджетов
     */
    public function log_widget_changes() {
        if (isset($_POST['widget-id']) || isset($_POST['delete_widget'])) {
            // Получаем информацию о текущем пользователе
            $user = $this->logger->get_current_user_info();
            
            // Если удаляется виджет
            if (isset($_POST['delete_widget'])) {
                $message = 'Удалил виджет';
            } else {
                $message = 'Изменил настройки виджетов';
            }
            
            // Логируем событие
            $this->logger->log($message, $user['info']);
        }
    }
}