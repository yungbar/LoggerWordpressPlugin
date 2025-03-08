<?php
/**
 * Класс для создания и управления административной страницей плагина
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_Admin_Page {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Добавление пункта меню в админ-панель
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Регистрация AJAX-обработчика для очистки лога
        add_action('wp_ajax_clear_admin_log', array($this, 'ajax_clear_log'));
    }
    
    /**
     * Добавление пункта меню в админ-панель
     */
    public function add_admin_menu() {
        // Проверка, что пользователь имеет права администратора
        if (current_user_can('administrator')) {
            add_management_page(
                'Лог действий в админке',
                'Лог администратора',
                'manage_options',
                'admin-actions-log',
                array($this, 'display_log_page')
            );
        }
    }
    
    /**
     * Отображение страницы с логом
     */
    public function display_log_page() {
        // Проверка, что пользователь имеет права администратора
        if (!current_user_can('administrator')) {
            wp_die(__('У вас нет прав для доступа к этой странице.'));
        }
        
        // Получение информации о лог-файле
        $debug_info = $this->logger->get_log_info();
        
        ?>
        <div class="wrap">
            <h1>Лог действий администраторов</h1>
            
            <div class="log-controls" style="margin-bottom: 15px;">
                <button id="clear-log-btn" class="button button-primary">Очистить лог</button>
                <div id="log-message" style="display:none; margin-top: 10px; padding: 10px; background-color: #fff; border-left: 4px solid #46b450;"></div>
            </div>
            
            <div class="card" style="max-width: 100%; margin-bottom: 20px;">
                <h2>Информация о логе</h2>
                <table class="widefat striped">
                    <tbody>
                        <?php foreach ($debug_info as $key => $value): ?>
                        <tr>
                            <td><strong><?php echo esc_html($key); ?></strong></td>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="log-content" style="background: #fff; padding: 20px; border: 1px solid #ddd;">
                <pre style="white-space: pre-wrap; word-wrap: break-word;"><?php
                    $log_content = $this->logger->get_log_content();
                    if ($log_content !== false) {
                        echo esc_html($log_content);
                    } else {
                        echo 'Не удалось прочитать лог-файл. Проверьте наличие файла и права доступа.';
                    }
                ?></pre>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clear-log-btn').on('click', function() {
                if (confirm('Вы уверены, что хотите очистить лог?')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'clear_admin_log',
                            nonce: '<?php echo wp_create_nonce('clear_admin_log_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.log-content pre').text('Лог очищен: ' + response.data);
                                $('#log-message').text('Лог успешно очищен').show().delay(3000).fadeOut();
                            } else {
                                $('#log-message').text('Ошибка при очистке лога: ' + response.data).show();
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX-обработчик для очистки лога
     */
    public function ajax_clear_log() {
        // Проверка nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clear_admin_log_nonce')) {
            wp_send_json_error('Ошибка безопасности.');
            return;
        }
        
        // Проверка прав доступа
        if (!current_user_can('administrator')) {
            wp_send_json_error('Недостаточно прав.');
            return;
        }
        
        // Очистка лога
        $result = $this->logger->clear_log();
        
        // Отправка ответа
        if ($result !== false) {
            wp_send_json_success(date('Y-m-d H:i:s'));
        } else {
            wp_send_json_error('Не удалось очистить лог-файл. Проверьте права доступа.');
        }
    }
}