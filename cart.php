<?php
function custom_before_cart_table() {
    ob_start();    
    ?>
        <h2><?php _e('Ваша корзина', 'woocommerce'); ?></h2>
       <table class="custom-table" cellspacing="0">
        <thead>
            <tr>
                <th class="custom-thumbnail">Продукт</th>
                <th class="custom-color">Колір</th>
                <th class="custom-quantity">Кількість</th>
                <th class="custom-discount">Знижка</th>
                <th class="custom-total">Вартість</th>
            </tr>
            </thead>
    
            <tbody>
                <?php
                // Получаем все товары в корзине
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                    $_product     = $cart_item['data'];
                    $product_id   = $cart_item['product_id'];
                    $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
                    $quantity     = $cart_item['quantity'];
                    $price        = $_product->get_price();
                    $discount     = 0;
    
                    // Логика скидки в зависимости от количества
                    if ($quantity >= 2 && $quantity <= 5) {
                        $discount = 0.02; // 2%
                    } elseif ($quantity >= 6 && $quantity <= 9) {
                        $discount = 0.04; // 4%
                    } elseif ($quantity >= 10 && $quantity <= 14) {
                        $discount = 0.07; // 7%
                    } elseif ($quantity >= 15 && $quantity <= 24) {
                        $discount = 0.12; // 12%
                    } elseif ($quantity >= 25 && $quantity <= 34) {
                        $discount = 0.15; // 15%
                    } elseif ($quantity >= 35) {
                        $discount = 0.17; // 17%
                    }
    
                    // Новый расчет цены с учетом скидки
                    $new_price = $price * (1 - $discount);
                    $total_without_discount += $price * $quantity;
                    $total_with_discount += $new_price * $quantity;
                    // Получаем атрибуты вариативного продукта
                    if ($variation_id) {
                        $variation = wc_get_product($variation_id);
                        $variation_attributes = $variation->get_attributes();
                        $attributes_json = json_encode($variation_attributes);
    
        echo "<script>
            console.log('Атрибуты товара:', $attributes_json);
        </script>";
                        $size = isset($variation_attributes['pa_size']) ? $variation_attributes['pa_size'] : 'Неизвестен';
                        $decoded_size = urldecode($size);
                        $color = isset($variation_attributes['pa_color']) ? $variation_attributes['pa_color'] : '#000';
                        $color_term = get_term_by('slug', $color, 'pa_колір');
                        $color_hex = $color_term ? get_term_meta($color_term->term_id, 'color', true) : '#000000'; // Получаем цвет в HEX
                    }
    
                    ?>
    
                    <tr>
                        <td class="custom-thumbnail  sas">
                            <div class="left">
                             <?php echo $_product->get_image(); ?>
                            </div>
                            <div class="right">
                            <?php 
                echo $_product->get_title() . '<br>';
    
                echo $decoded_size . '<br>';
    
                echo $_product->get_price_html();
            ?>
                            </div>
                        </td>
    
                        <td class="custom-name">
                            <div style="background-color: #<?php echo esc_attr( $color ); ?>; width: 30px; height: 30px;"></div>
                        </td>
    
                        <td class="custom-quantity">
                            <button class="qty-minus btn-qty">-</button>
                            <input type="number" value="<?php echo $quantity; ?>" class="input-magaze" data-cart-item-key="<?php echo $cart_item_key; ?>" />
                            <button class="qty-plus btn-qty">+</button>
                             <button class="remove-item" data-cart-item-key="<?php echo $cart_item_key; ?>">×</button>
                        </td>
    
                        <td class="custom-discount">
                            <?php echo ($discount * 100) . '%'; ?>
                        </td>
    
                        <td class="custom-subtotal">
                            <?php echo wc_price($new_price * $quantity); ?>
                        </td>
                    </tr>
    
                <?php endforeach; ?>
    
            </tbody>
        </table>
        <div class="pay_container">
                <div class="details">
    
          <div>
              <p>
                  Вартість:
              </p>
              <span><?php echo wc_price($total_without_discount); ?></span>
            </div>	
            <div>
                <p>
                    Знижка:
                </p>
                <span class="discount">-12%</span>
            </div>
            <div>
                <p>
                    Загальна вартість  :
                </p>
                <span class="discount"><?php echo wc_price($total_with_discount); ?></span>
            </div>
            </div>
            <a href="/temps" >Оформити замолення</a>
        </div>
        <?php
            return ob_get_clean();
    
    } ?>