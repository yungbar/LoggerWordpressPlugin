<?php
/**
 * Класс для отслеживания изменений в WooCommerce
 */

// Если файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

class AAL_WooCommerce_Logger {
    
    // Экземпляр класса логгера
    private $logger;
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        // Инициализация логгера
        $this->logger = new AAL_Logger();
        
        // Проверяем, активен ли WooCommerce
        if (!$this->is_woocommerce_active()) {
            return;
        }
        
        // Хуки для отслеживания изменений товаров
        add_action('woocommerce_update_product', array($this, 'log_product_update'), 10, 2);
        add_action('woocommerce_new_product', array($this, 'log_product_create'), 10, 1);
        add_action('woocommerce_delete_product', array($this, 'log_product_delete'), 10, 1);
        add_action('woocommerce_product_duplicate', array($this, 'log_product_duplicate'), 10, 2);
        
        // Хуки для отслеживания изменений заказов
        add_action('woocommerce_order_status_changed', array($this, 'log_order_status_change'), 10, 4);
        add_action('woocommerce_delete_order', array($this, 'log_order_delete'), 10, 1);
        add_action('woocommerce_rest_insert_shop_order', array($this, 'log_order_create'), 10, 3);
        
        // Хуки для отслеживания изменений купонов
        add_action('woocommerce_new_coupon', array($this, 'log_coupon_create'), 10, 1);
        add_action('woocommerce_update_coupon', array($this, 'log_coupon_update'), 10, 1);
        add_action('woocommerce_delete_coupon', array($this, 'log_coupon_delete'), 10, 1);
        
        // Хуки для отслеживания изменений категорий и атрибутов товаров
        add_action('created_term', array($this, 'log_term_create'), 10, 3);
        add_action('edited_term', array($this, 'log_term_edit'), 10, 3);
        add_action('delete_term', array($this, 'log_term_delete'), 10, 4);
        
        // Хуки для отслеживания изменений настроек WooCommerce
        add_action('woocommerce_settings_saved', array($this, 'log_settings_save'));
        add_action('woocommerce_settings_start', array($this, 'log_settings_page_access'));
        
        // Хуки для отслеживания изменений способов доставки и оплаты
        add_action('woocommerce_shipping_zone_method_added', array($this, 'log_shipping_method_added'), 10, 3);
        add_action('woocommerce_shipping_zone_method_deleted', array($this, 'log_shipping_method_deleted'), 10, 3);
        add_action('woocommerce_shipping_zone_method_status_toggled', array($this, 'log_shipping_method_toggled'), 10, 4);
    }
    
    /**
     * Проверка активности WooCommerce
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Логирование обновления товара
     */
    public function log_product_update($product_id, $product) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о товаре
        $product_title = $product->get_name();
        $product_type = $product->get_type();
        
        // Формируем сообщение для лога
        $message = 'Обновил товар: "' . $product_title . '" (ID: ' . $product_id . ', Тип: ' . $product_type . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование создания товара
     */
    public function log_product_create($product_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о товаре
        $product = wc_get_product($product_id);
        if (!$product) return;
        
        $product_title = $product->get_name();
        $product_type = $product->get_type();
        
        // Формируем сообщение для лога
        $message = 'Создал новый товар: "' . $product_title . '" (ID: ' . $product_id . ', Тип: ' . $product_type . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления товара
     */
    public function log_product_delete($product_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил товар (ID: ' . $product_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование дублирования товара
     */
    public function log_product_duplicate($duplicate, $product) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о товарах
        $original_title = $product->get_name();
        $duplicate_title = $duplicate->get_name();
        
        // Формируем сообщение для лога
        $message = 'Дублировал товар: "' . $original_title . '" (ID: ' . $product->get_id() . ') в "' . 
                  $duplicate_title . '" (ID: ' . $duplicate->get_id() . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование изменения статуса заказа
     */
    public function log_order_status_change($order_id, $old_status, $new_status, $order) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Изменил статус заказа #' . $order_id . ' с "' . $old_status . '" на "' . $new_status . '"';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления заказа
     */
    public function log_order_delete($order_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил заказ #' . $order_id;
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование создания заказа через REST API
     */
    public function log_order_create($order, $request, $creating) {
        // Пропускаем, если это не создание нового заказа
        if (!$creating) return;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Создал новый заказ #' . $order->get_id() . ' через REST API';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование создания купона
     */
    public function log_coupon_create($coupon_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о купоне
        $coupon = new WC_Coupon($coupon_id);
        $coupon_code = $coupon->get_code();
        
        // Формируем сообщение для лога
        $message = 'Создал новый купон: "' . $coupon_code . '" (ID: ' . $coupon_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование обновления купона
     */
    public function log_coupon_update($coupon_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о купоне
        $coupon = new WC_Coupon($coupon_id);
        $coupon_code = $coupon->get_code();
        
        // Формируем сообщение для лога
        $message = 'Обновил купон: "' . $coupon_code . '" (ID: ' . $coupon_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления купона
     */
    public function log_coupon_delete($coupon_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Удалил купон (ID: ' . $coupon_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование создания термина (категории/атрибута)
     */
    public function log_term_create($term_id, $tt_id, $taxonomy) {
        // Проверяем, что это таксономия WooCommerce
        if (!$this->is_woocommerce_taxonomy($taxonomy)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о термине
        $term = get_term($term_id, $taxonomy);
        
        // Формируем понятное название таксономии
        $taxonomy_label = $this->get_taxonomy_label($taxonomy);
        
        // Формируем сообщение для лога
        $message = 'Создал новый ' . $taxonomy_label . ': "' . $term->name . '" (ID: ' . $term_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование редактирования термина (категории/атрибута)
     */
    public function log_term_edit($term_id, $tt_id, $taxonomy) {
        // Проверяем, что это таксономия WooCommerce
        if (!$this->is_woocommerce_taxonomy($taxonomy)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о термине
        $term = get_term($term_id, $taxonomy);
        
        // Формируем понятное название таксономии
        $taxonomy_label = $this->get_taxonomy_label($taxonomy);
        
        // Формируем сообщение для лога
        $message = 'Обновил ' . $taxonomy_label . ': "' . $term->name . '" (ID: ' . $term_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления термина (категории/атрибута)
     */
    public function log_term_delete($term_id, $tt_id, $taxonomy, $deleted_term) {
        // Проверяем, что это таксономия WooCommerce
        if (!$this->is_woocommerce_taxonomy($taxonomy)) {
            return;
        }
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем понятное название таксономии
        $taxonomy_label = $this->get_taxonomy_label($taxonomy);
        
        // Формируем сообщение для лога
        $message = 'Удалил ' . $taxonomy_label . ': "' . $deleted_term->name . '" (ID: ' . $term_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование сохранения настроек WooCommerce
     */
    public function log_settings_save() {
        global $current_tab, $current_section;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Сохранил настройки WooCommerce: вкладка "' . $current_tab . '"';
        if (!empty($current_section)) {
            $message .= ', раздел "' . $current_section . '"';
        }
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование доступа к странице настроек WooCommerce
     */
    public function log_settings_page_access() {
        global $current_tab, $current_section;
        
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Формируем сообщение для лога
        $message = 'Открыл страницу настроек WooCommerce: вкладка "' . $current_tab . '"';
        if (!empty($current_section)) {
            $message .= ', раздел "' . $current_section . '"';
        }
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование добавления метода доставки
     */
    public function log_shipping_method_added($instance_id, $method_id, $zone_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о зоне доставки
        $zone = new WC_Shipping_Zone($zone_id);
        
        // Формируем сообщение для лога
        $message = 'Добавил метод доставки "' . $method_id . '" (ID: ' . $instance_id . ') в зону "' . 
                  $zone->get_zone_name() . '" (ID: ' . $zone_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование удаления метода доставки
     */
    public function log_shipping_method_deleted($instance_id, $method_id, $zone_id) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о зоне доставки
        $zone = new WC_Shipping_Zone($zone_id);
        
        // Формируем сообщение для лога
        $message = 'Удалил метод доставки "' . $method_id . '" (ID: ' . $instance_id . ') из зоны "' . 
                  $zone->get_zone_name() . '" (ID: ' . $zone_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Логирование включения/отключения метода доставки
     */
    public function log_shipping_method_toggled($instance_id, $method_id, $zone_id, $enabled) {
        // Получаем информацию о текущем пользователе
        $user = $this->logger->get_current_user_info();
        
        // Получаем информацию о зоне доставки
        $zone = new WC_Shipping_Zone($zone_id);
        
        // Определяем статус метода
        $status = $enabled ? 'включил' : 'отключил';
        
        // Формируем сообщение для лога
        $message = ucfirst($status) . ' метод доставки "' . $method_id . '" (ID: ' . $instance_id . ') в зоне "' . 
                  $zone->get_zone_name() . '" (ID: ' . $zone_id . ')';
        
        // Логируем событие
        $this->logger->log($message, $user['info']);
    }
    
    /**
     * Проверка, относится ли таксономия к WooCommerce
     */
    private function is_woocommerce_taxonomy($taxonomy) {
        $wc_taxonomies = array(
            'product_cat',           // Категории товаров
            'product_tag',           // Метки товаров
            'product_shipping_class', // Классы доставки
            'product_type',          // Типы товаров
            'product_visibility',    // Видимость товаров
            'pa_'                    // Префикс для атрибутов товаров
        );
        
        // Проверяем, является ли таксономия таксономией WooCommerce
        foreach ($wc_taxonomies as $wc_taxonomy) {
            if ($taxonomy === $wc_taxonomy || strpos($taxonomy, $wc_taxonomy) === 0 || strpos($taxonomy, 'pa_') === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Получение понятного названия таксономии
     */
    private function get_taxonomy_label($taxonomy) {
        switch ($taxonomy) {
            case 'product_cat':
                return 'категория товара';
            case 'product_tag':
                return 'метка товара';
            case 'product_shipping_class':
                return 'класс доставки';
            case 'product_type':
                return 'тип товара';
            case 'product_visibility':
                return 'видимость товара';
            default:
                // Если это атрибут товара
                if (strpos($taxonomy, 'pa_') === 0) {
                    return 'атрибут товара "' . substr($taxonomy, 3) . '"';
                }
                return $taxonomy;
        }
    }
}