<?php
/**
 * Основной класс логгера
 * 
 * Отвечает за запись данных в лог-файл и обеспечивает базовую функциональность логирования
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_Logger {
    
    // Путь к лог-файлу
    private $log_file;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Установка пути к лог-файлу из константы
        $this->log_file = AAL_LOG_FILE;
        
        // Проверка и создание лог-файла если он не существует
        $this->check_log_file();
    }
    
    /**
     * Проверка и создание лог-файла
     */
    public function check_log_file() {
        if (!file_exists($this->log_file)) {
            $header = "====== Лог действий администраторов WordPress ======\n";
            $header .= "Файл создан: " . date('Y-m-d H:i:s') . "\n\n";
            
            $result = file_put_contents($this->log_file, $header);
            
            // Отладка прав доступа к файлу
            if ($result === false) {
                // Запись в журнал ошибок PHP
                error_log('Admin Actions Logger: Не удалось создать лог-файл: ' . $this->log_file);
            } else {
                // Устанавливаем права доступа 0664
                @chmod($this->log_file, 0664);
            }
        }
    }
    
    /**
     * Получение информации о текущем пользователе
     */
    public function get_current_user_info() {
        $current_user = wp_get_current_user();
        if ($current_user->exists()) {
            return array(
                'id' => $current_user->ID,
                'login' => $current_user->user_login,
                'name' => $current_user->display_name,
                'info' => $current_user->user_login . ' (ID: ' . $current_user->ID . ')',
                'roles' => $current_user->roles
            );
        } else {
            return array(
                'id' => 0,
                'login' => 'system',
                'name' => 'System',
                'info' => 'System Process',
                'roles' => array()
            );
        }
    }
    
    /**
     * Запись сообщения в лог
     */
    public function log($message, $user_info = null) {
        // Если информация о пользователе не передана, получаем её
        if ($user_info === null) {
            $user = $this->get_current_user_info();
            
            // Проверяем, не является ли пользователь admin
            if ($user['login'] === 'admin') {
                // Не логируем действия пользователя admin
                return;
            }
            
            // Проверяем, не имеет ли пользователь роль "Клиент" (customer)
            if (in_array('customer', $user['roles'])) {
                // Не логируем действия клиентов
                return;
            }
            
            $user_info = $user['info'];
        } else {
            // Если информация о пользователе передана, проверяем, не содержит ли она логин admin
            if (strpos($user_info, 'admin (ID:') === 0) {
                // Не логируем действия пользователя admin
                return;
            }
            
            // Для переданной строки трудно определить роль, полагаемся на явную проверку выше
        }
        
        // Формируем сообщение лога
        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . 
                      'Пользователь: ' . $user_info . ' ' . 
                      $message . "\n";
        
        // Записываем в лог
        $this->write_to_log($log_message);
    }
    
    /**
     * Запись данных в лог-файл
     */
    private function write_to_log($message) {
        // Проверка существования файла
        $this->check_log_file();
        
        // Попытка записи в файл
        $result = file_put_contents($this->log_file, $message, FILE_APPEND);
        
        // Если запись не удалась
        if ($result === false) {
            error_log('Admin Actions Logger: Не удалось записать в лог-файл: ' . $this->log_file);
            
            // Запись в debug.log WordPress
            if (defined('WP_DEBUG') && WP_DEBUG === true && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true) {
                error_log('Admin Actions Logger - ' . $message);
            }
        }
        
        return $result !== false;
    }
    
    /**
     * Очистка лог-файла
     */
    public function clear_log() {
        $header = "====== Лог действий администраторов WordPress ======\n";
        $header .= "Файл очищен: " . date('Y-m-d H:i:s') . "\n\n";
        
        $result = file_put_contents($this->log_file, $header);
        
        return $result !== false;
    }
    
    /**
     * Получение содержимого лог-файла
     */
    public function get_log_content() {
        if (file_exists($this->log_file) && is_readable($this->log_file)) {
            return file_get_contents($this->log_file);
        }
        
        return false;
    }
    
    /**
     * Получение информации о лог-файле
     */
    public function get_log_info() {
        return array(
            'Лог-файл' => $this->log_file,
            'Файл существует' => file_exists($this->log_file) ? 'Да' : 'Нет',
            'Доступен для чтения' => is_readable($this->log_file) ? 'Да' : 'Нет',
            'Доступен для записи' => is_writable($this->log_file) ? 'Да' : 'Нет',
            'Права доступа' => file_exists($this->log_file) ? substr(sprintf('%o', fileperms($this->log_file)), -4) : 'N/A',
            'Размер файла' => file_exists($this->log_file) ? size_format(filesize($this->log_file)) : '0 Б',
        );
    }
}