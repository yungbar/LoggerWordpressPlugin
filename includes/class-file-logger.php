<?php
/**
 * Класс для отслеживания изменений файлов тем и плагинов
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_File_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания изменений файлов
        add_action('edit_theme_plugin_file', array($this, 'log_file_edit_attempt'), 10, 2);
        add_action('theme_editor_save_file', array($this, 'log_theme_file_save'), 10, 2);
        add_action('plugin_editor_save_file', array($this, 'log_plugin_file_save'), 10, 2);
    }
    
    /**
     * Логирование попытки редактирования файла
     */
    public function log_file_edit_attempt($file, $content) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Открыл для редактирования файл: "' . basename($file) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование сохранения файла темы
     */
    public function log_theme_file_save($file, $content) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Сохранил файл темы: "' . basename($file) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование сохранения файла плагина
     */
    public function log_plugin_file_save($file, $content) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Сохранил файл плагина: "' . basename($file) . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
}