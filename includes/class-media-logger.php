<?php
/**
 * Класс для отслеживания изменений медиафайлов
 */
// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}
class AAL_Media_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания загрузки и удаления медиафайлов
        add_action('add_attachment', array($this, 'log_media_upload'), 10, 1);
        add_action('delete_attachment', array($this, 'log_media_delete'), 10, 1);
        add_action('wp_media_upload_handler', array($this, 'log_media_upload_start'), 10, 1);
        add_action('wp_handle_upload', array($this, 'log_media_upload_complete'), 10, 2);
        add_action('edit_attachment', array($this, 'log_media_edit'), 10, 1);
        
        // Хуки для отслеживания обновлений WordPress
        add_action('upgrader_process_complete', array($this, 'log_update_complete'), 10, 2);
        add_action('activated_plugin', array($this, 'log_plugin_activate'), 10, 2);
        add_action('deactivated_plugin', array($this, 'log_plugin_deactivate'), 10, 2);
        add_action('switch_theme', array($this, 'log_theme_switch'), 10, 3);
    }
    
    /**
     * Логирование загрузки медиафайла
     */
    public function log_media_upload($attachment_id) {
        $attachment = get_post($attachment_id);
        if (!$attachment) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип файла
        $file_type = wp_check_filetype(get_attached_file($attachment_id));
        $mime_type = $file_type['type'];
        
        // Формируем сообщение для лога
        $message = 'Загрузил медиафайл: "' . $attachment->post_title . '" (ID: ' . $attachment_id . ', Тип: ' . $mime_type . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления медиафайла
     */
    public function log_media_delete($attachment_id) {
        $attachment = get_post($attachment_id);
        if (!$attachment) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил медиафайл: "' . $attachment->post_title . '" (ID: ' . $attachment_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование начала загрузки медиафайла
     */
    public function log_media_upload_start($file) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Начал загрузку файла: "' . basename($file['name']) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование завершения загрузки медиафайла
     */
    public function log_media_upload_complete($upload, $context) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Завершил загрузку файла: "' . basename($upload['file']) . '" (Тип: ' . $upload['type'] . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
        
        return $upload;
    }
    
    /**
     * Логирование редактирования медиафайла
     */
    public function log_media_edit($attachment_id) {
        $attachment = get_post($attachment_id);
        if (!$attachment) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Отредактировал медиафайл: "' . $attachment->post_title . '" (ID: ' . $attachment_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновлений
     */
    public function log_update_complete($upgrader, $options) {
        // Проверяем тип обновления
        if (!isset($options['type'])) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        $update_type = $options['type'];
        $message = '';
        
        // Формируем сообщение в зависимости от типа обновления
        switch ($update_type) {
            case 'plugin':
                $message = 'Обновил плагин(ы)';
                break;
            case 'theme':
                $message = 'Обновил тему(ы)';
                break;
            case 'core':
                $message = 'Обновил ядро WordPress';
                break;
            case 'translation':
                $message = 'Обновил перевод(ы)';
                break;
            default:
                $message = 'Выполнил обновление: ' . $update_type;
        }
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование активации плагина
     */
    public function log_plugin_activate($plugin, $network_wide) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Активировал плагин: "' . plugin_basename($plugin) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование деактивации плагина
     */
    public function log_plugin_deactivate($plugin, $network_wide) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Деактивировал плагин: "' . plugin_basename($plugin) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование смены темы
     */
    public function log_theme_switch($new_theme_name, $new_theme, $old_theme) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Сменил тему с "' . $old_theme->name . '" на "' . $new_theme_name . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
}