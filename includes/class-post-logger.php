<?php
/**
 * Класс для отслеживания изменений в постах и страницах
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_Post_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Хуки для отслеживания изменений в постах и страницах
        add_action('post_updated', array($this, 'log_post_update'), 10, 3);
        add_action('save_post', array($this, 'log_post_save'), 10, 3);
        add_action('delete_post', array($this, 'log_post_delete'), 10, 1);
        add_action('wp_trash_post', array($this, 'log_post_trash'), 10, 1);
        add_action('untrash_post', array($this, 'log_post_untrash'), 10, 1);
    }
    
    /**
     * Логирование обновления постов/страниц
     */
    public function log_post_update($post_id, $post_after, $post_before) {
        // Пропускаем автосохранения и ревизии
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип записи в читаемом виде
        $post_type = get_post_type_object($post_after->post_type);
        $type_label = $post_type ? $post_type->labels->singular_name : $post_after->post_type;
        
        // Формируем сообщение для лога
        $message = 'Обновил ' . $type_label . ': "' . $post_after->post_title . '" (ID: ' . $post_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование сохранения постов/страниц
     */
    public function log_post_save($post_id, $post, $update) {
        // Пропускаем обновления (они логируются в log_post_update)
        if ($update || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип записи в читаемом виде
        $post_type = get_post_type_object($post->post_type);
        $type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        // Формируем сообщение для лога
        $message = 'Создал новый ' . $type_label . ': "' . $post->post_title . '" (ID: ' . $post_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления постов/страниц
     */
    public function log_post_delete($post_id) {
        $post = get_post($post_id);
        if (!$post) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип записи в читаемом виде
        $post_type = get_post_type_object($post->post_type);
        $type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        // Формируем сообщение для лога
        $message = 'Удалил ' . $type_label . ': "' . $post->post_title . '" (ID: ' . $post_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование перемещения постов/страниц в корзину
     */
    public function log_post_trash($post_id) {
        $post = get_post($post_id);
        if (!$post) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип записи в читаемом виде
        $post_type = get_post_type_object($post->post_type);
        $type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        // Формируем сообщение для лога
        $message = 'Переместил в корзину ' . $type_label . ': "' . $post->post_title . '" (ID: ' . $post_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование восстановления постов/страниц из корзины
     */
    public function log_post_untrash($post_id) {
        $post = get_post($post_id);
        if (!$post) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем тип записи в читаемом виде
        $post_type = get_post_type_object($post->post_type);
        $type_label = $post_type ? $post_type->labels->singular_name : $post->post_type;
        
        // Формируем сообщение для лога
        $message = 'Восстановил из корзины ' . $type_label . ': "' . $post->post_title . '" (ID: ' . $post_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
}