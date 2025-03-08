<?php
/**
 * Класс для отслеживания изменений пользователей
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_User_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания изменений пользователей
        add_action('user_register', array($this, 'log_user_register'), 10, 2);
        add_action('profile_update', array($this, 'log_user_update'), 10, 3);
        add_action('delete_user', array($this, 'log_user_delete'), 10, 3);
        add_action('set_user_role', array($this, 'log_user_role_change'), 10, 3);
        
        // Хуки для отслеживания входа/выхода пользователей
        add_action('wp_login', array($this, 'log_user_login'), 10, 2);
        add_action('wp_logout', array($this, 'log_user_logout'));
        add_action('wp_login_failed', array($this, 'log_user_login_failed'));
    }
    
    /**
     * Логирование регистрации пользователя
     */
    public function log_user_register($user_id, $userdata = array()) {
        // Получаем информацию о созданном пользователе
        $new_user = get_userdata($user_id);
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Зарегистрировал нового пользователя: "' . $new_user->user_login . '" (ID: ' . $user_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновления профиля пользователя
     */
    public function log_user_update($user_id, $old_userdata, $userdata = array()) {
        // Получаем информацию о обновленном пользователе
        $updated_user = get_userdata($user_id);
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Обновил профиль пользователя: "' . $updated_user->user_login . '" (ID: ' . $user_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления пользователя
     */
    public function log_user_delete($user_id, $reassign, $user = null) {
        // Получаем информацию о текущем пользователе
        $current_user = $this->logger->get_current_user_info();
        
        // Если информация о пользователе уже удалена, используем значение из параметра
        $user_login = $user ? $user->user_login : 'ID: ' . $user_id;
        
        // Формируем сообщение для лога
        $message = 'Удалил пользователя: "' . $user_login . '"';
        
        // Логируем событие
        $this->logger->log($message, $current_user['info']);
    }
    
    /**
     * Логирование изменения роли пользователя
     */
    public function log_user_role_change($user_id, $new_role, $old_roles) {
        // Получаем информацию о пользователе
        $user_data = get_userdata($user_id);
        
        // Получаем информацию о текущем пользователе
        $current_user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Изменил роль пользователя: "' . $user_data->user_login . '" с "' . 
                  implode(', ', $old_roles) . '" на "' . $new_role . '"';
        
        // Логируем событие
        $this->logger->log($message, $current_user['info']);
    }
    
    /**
     * Логирование входа пользователя
     */
    public function log_user_login($user_login, $user) {
        // Формируем информацию о пользователе
        $user_info = $user->user_login . ' (ID: ' . $user->ID . ')';
        
        // Формируем сообщение для лога
        $message = 'Вход в систему';
        
        // Логируем событие
        $this->logger->log($message, $user_info);
    }
    
    /**
     * Логирование выхода пользователя
     */
    public function log_user_logout() {
        // Получаем информацию о текущем пользователе перед выходом
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Выход из системы';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование неудачной попытки входа
     */
    public function log_user_login_failed($username) {
        // Получаем IP адрес
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Формируем информацию о пользователе
        $user_info = 'Попытка входа с логином: "' . $username . '" с IP: ' . $ip;
        
        // Формируем сообщение для лога
        $message = 'Неудачная попытка входа';
        
        // Логируем событие
        $this->logger->log($message, $user_info);
    }
}