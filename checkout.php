<?php
function custom_checkout_shortcode() {
    ob_start();
    ?>

    <div class="two_block">
        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

            <!-- Шаг 1: Контактні дані -->
            <div id="step-1" class="checkout-step active">
                <h3>Контактні дані</h3>
                
                <input type="text" id="first_name" placeholder="Введіть ім'я по Батькові" name="first_name" required>
                
                <input type="text" id="last_name" placeholder="Введіть прізвище" name="last_name" required>
                
                <input type="text" class="phone" id="phone" name="phone" placeholder="+380(XX)XXX-XXX-XX" required>
                
                <div class="next_button_container">
                    <button type="button" onclick="nextStep(2)">Продовжити</button>
                    <a href="/" class="back-to-shop">Повернутись до покупок</a>
                </div>
            </div>

            <!-- Шаг 2: Доставка -->
            <div id="step-2" class="checkout-step">
                <h3>Доставка</h3>
                <div class="container-hide">
					
				
                <label>
                    <input type="radio" name="shipping_method" value="nova_poshta" onclick="showShippingOptions('nova_poshta')">
                    Нова Пошта
                </label>
                <div id="nova_poshta_options" class="shipping-options">
                    <div>
						
					
					<label for="city">В місто</label>
                    <input type="text" id="city" name="city" placeholder="Введіть місто" required>
</div>
					<div>
						
					
                    <label for="delivery_type">Тип доставки</label>
                    <select id="delivery_type" name="delivery_type" onchange="toggleDeliveryInput(this.value)">
                        <option value="branch">Доставка у відділення</option>
                        <option value="courier">Курьєр</option>
                    </select>
</div>
                    <div id="branch_address" class="delivery-input">
                        <label for="branch">Відділення</label>
                        <input type="text" id="branch" name="branch">
                    </div>

                    <div id="courier_address" class="delivery-input">
                        <label for="courier_address_input">Адреса</label>
                        <input type="text" id="courier_address_input" name="courier_address">
                    </div>
                </div>
                <label>
                    <input type="radio" name="shipping_method" value="ukrposhta" onclick="hideShippingOptions()">
                    УкрПошта
                </label>
                <label>
                    <input type="radio" name="shipping_method" value="pickup" onclick="hideShippingOptions()">
                    Самовивіз
                </label>
                
                <div class="next_button_container">
                    <button type="button" onclick="nextStep(3)">Продовжити</button>
                    <a href="/" class="back-to-shop">Повернутись до покупок</a>
                </div>
            </div>
				</div>

            <!-- Шаг 3: Оплата -->
            <div id="step-3" class="checkout-step">
                <h3>Оплата</h3>

                <label>
                    <input type="radio" name="payer_type" value="individual" checked>
                    Для фізичних осіб
                </label>
                
                <div class="payment-options">
                    <label>
                        <input type="radio" name="payment_method" value="bank_transfer" checked>
                        Переказ на банківський рахунок
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="card_transfer">
                        Переказ на банківську карту
                    </label>
                </div>
                
                <label>
                    <input type="radio" name="payer_type" value="business">
                    Для юридичних осіб
                </label>

                <div class="next_button_container">
                    <button type="submit">Замовити</button>
                    <a href="/" class="back-to-shop">Повернутись до покупок</a>
                </div>
            </div>
        </form>
        
        <div class="order-summary">
    <h3>Ваше замовлення</h3>
    
    <?php 
    $items = WC()->cart->get_cart();
    $parent_products = [];

    foreach ( $items as $item_id => $item ) :
        $product = $item['data'];
        $product_name = $product->get_name();
        $product_image = wp_get_attachment_image( $product->get_image_id(), 'thumbnail' );
        $quantity = $item['quantity']; // Получаем количество для текущего товара

        // Получаем ID родительского товара
        $parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
        
        // Проверяем, был ли уже выведен родительский товар
        if ( !isset($parent_products[$parent_id]) ) :
            // Если нет, добавляем родительский товар в массив
            $parent_products[$parent_id] = [
                'name' => $product_name,
                'image' => $product_image,
                'data' => $product, // Добавляем объект продукта для получения цены
                'variations' => [],
            ];
        endif;
        
        // Если товар является вариацией, добавляем его атрибуты к родительскому товару
        if ( $product->is_type( 'variation' ) ) :
            $variation_attributes = $product->get_variation_attributes();
            $pa_size = isset( $variation_attributes['attribute_pa_size'] ) ? $variation_attributes['attribute_pa_size'] : '';
            $pa_color = isset( $variation_attributes['attribute_pa_color'] ) ? $variation_attributes['attribute_pa_color'] : '';

            $parent_products[$parent_id]['variations'][] = [
                'size' => $pa_size,
                'color' => $pa_color,
                'quantity' => $quantity, // Добавляем количество в вариацию
            ];
        endif;
    endforeach;
    ?>      

    <?php foreach ( $parent_products as $parent_product ) : ?>
        <?php
            $base_name = preg_replace('/<span>.*$/', '', $parent_product['name']); 
            $base_price = wc_get_price_to_display( $parent_product['data'] ); // Получаем базовую цену товара
    
            // Получаем данные о скидке и цене
            $discount_data = calculate_discounted_price($parent_product['variations'], $base_price);
        ?>
			
        <div class="order-item">
			<div class="order-item-father">
				
			
            <div class="order-item-content">
                <div class="order-item-text">
                    <h4><?php echo esc_html( $base_name ); ?></h4>
                    
                    <!-- Выводим все вариации для текущего родительского товара -->
                    <?php
                    $sizes_with_colors = [];
                    foreach ( $parent_product['variations'] as $variation ) {
                        $size = $variation['size'];
                        $color = $variation['color'];
                        $quantity = $variation['quantity']; // Используем количество для вариации

                        // Инициализируем массив для данного размера, если он еще не создан
                        if ( !isset($sizes_with_colors[$size]) ) {
                            $sizes_with_colors[$size] = [];
                        }

                        // Добавляем цвет и количество в массив для соответствующего размера
                        $sizes_with_colors[$size][] = ['color' => $color, 'quantity' => $quantity];
                    }
                    foreach ( $sizes_with_colors as $size => $color_variations ) : ?>
                        <?php 
                        // Декодируем и форматируем размер
                        $formatted_size = str_replace('-', ' ', urldecode($size));
                        ?>
                        <p>Розмір: <?php echo esc_html( $formatted_size ); ?></p>

                        <?php foreach ( $color_variations as $variation ) : ?>
                            <div class="color-item">
                                <div class="color-block" style="background-color: <?php echo '#' . esc_attr( $variation['color'] ); ?>;"></div>
                                
                                <div class="quantity-control">
                                    <button type="button" class="quantity-btn decrement">-</button>
                                    <input type="number" class="quantity-input" name="quantity[<?php echo esc_attr( $size ); ?>][<?php echo esc_attr( $variation['color'] ); ?>]" min="1" value="<?php echo esc_attr( $variation['quantity'] ); ?>">
                                    <button type="button" class="quantity-btn increment">+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <div class="order-item-image">
                    <?php echo $parent_product['image']; ?>
                </div>
				
            </div>
				<div class="details">
    <div>
        <p>Загальна ціна:</p>
        <span><?php echo wc_price($discount_data['original_price']); ?></span>
    </div>
    <div>
        <p>Знижка:</p>
        <span class="discount"><?php echo $discount_data['discount']; ?>%</span>
    </div>
    <div>
        <p>Ціна зі знижкою:</p>
        <span><?php echo wc_price($discount_data['discounted_price']); ?></span>
    </div>
</div>
			</div>
        </div>
    <?php endforeach; ?>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_checkout', 'custom_checkout_shortcode');
?>