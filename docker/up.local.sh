#!/bin/bash

DOCKER_APP_CONTAINER="wooless-app"
DOCKER_WORDPRESS_APP_CONTAINER="wooless-wordpress-app"

WORDPRESS_URL="https://localhost/wordpress"
WORDPRESS_TITLE="Wooless Shop"
WORDPRESS_ADMIN_USER="wordpress"
WORDPRESS_ADMIN_PASSWORD="secret"
WORDPRESS_ADMIN_EMAIL="marek@koncewicz.io"

WORDPRESS_PLUGIN_ACF_VERSION=6.7.0
WORDPRESS_PLUGIN_WOOCOMMERCE_VERSION=10.4.3
WORDPRESS_PLUGIN_FURGONETKA_VERSION=1.8.3
WORDPRESS_PLUGIN_ACF_TO_REST_API_VERSION=3.3.4
WORDPRESS_PLUGIN_JWT_AUTH_VERSION=3.0.2

# Start containers
docker compose -f docker-compose.local.yml -p wooless up -d

docker exec $DOCKER_WORDPRESS_APP_CONTAINER mkdir -p /var/www/.wp-cli/cache
docker exec $DOCKER_WORDPRESS_APP_CONTAINER chown -R www-data:www-data /var/www/.wp-cli
docker exec $DOCKER_WORDPRESS_APP_CONTAINER chmod -R 750 /var/www/.wp-cli

echo "Installing WordPress..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp core install \
    --url="$WORDPRESS_URL" \
    --title="$WORDPRESS_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp language core install pl_PL
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp site switch-language pl_PL

echo "Change permalink settings..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update permalink_structure '/%postname%/'

echo "Installing ACF plugin..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin install \
    advanced-custom-fields \
    --version=$WORDPRESS_PLUGIN_ACF_VERSION \
    --activate

echo "Installing ACF TO REST API plugin..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin install \
    acf-to-rest-api \
    --version=$WORDPRESS_PLUGIN_ACF_TO_REST_API_VERSION \
    --activate

echo "Installing WooCommerce plugin..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin install \
    woocommerce \
    --version=$WORDPRESS_PLUGIN_WOOCOMMERCE_VERSION \
    --activate

echo "Configuring WooCommerce settings..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_store_address "Mińska 25B"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_store_address_2 "U2"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_store_city "Warszawa"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_store_postcode "03-808"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_default_country "PL"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_default_customer_address "base"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_currency "PLN"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_currency_pos "right_space"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_price_thousand_sep " "
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_price_decimal_sep ","
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_price_num_decimals "2"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_prices_include_tax "no"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_tax_based_on "shipping"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_tax_round_at_subtotal "no"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_tax_display_shop "excl"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_tax_display_cart "excl"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_tax_total_display "itemized"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_shipping_tax_class "inherit"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_shipping_cost_requires_address "no"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_shipping_debug_mode "no"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_coupons "no"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_ajax_add_to_cart "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_reviews "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_review_rating "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_shipping_calc "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_guest_checkout "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_checkout_login_reminder "no"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_signup_and_login_from_checkout "yes"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_delayed_account_creation "no"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_enable_myaccount_registration "no"

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_allowed_countries "specific"
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp option update woocommerce_specific_allowed_countries --format=json '["PL"]'

echo "Installing JWT Auth plugin..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin install \
    jwt-auth \
    --version=$WORDPRESS_PLUGIN_JWT_AUTH_VERSION \
    --activate

echo "Installing Przelewy24 plugin..."
docker cp ./wordpress/wp-content/plugins/woo-przelewy24 $DOCKER_WORDPRESS_APP_CONTAINER:/var/www/html/wp-content/plugins
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin activate woo-przelewy24

echo "Installing Furgonetka plugin..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin install \
    furgonetka \
    --version=$WORDPRESS_PLUGIN_FURGONETKA_VERSION \
    --activate

echo "Create shipping zone..."
SHIPPING_ZONE_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc shipping_zone create \
    --name="Polska" \
    --user=wordpress \
    --porcelain)

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc shipping_zone_method create $SHIPPING_ZONE_ID \
    --method_id=flat_rate \
    --enabled=1 \
    --settings='{"title":"InPost Paczkomaty 24/7","cost":"15","tax_status":"taxable"}' \
    --user=wordpress \
    --porcelain

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc shipping_zone_method create $SHIPPING_ZONE_ID \
    --method_id=flat_rate \
    --enabled=1 \
    --settings='{"title":"DPD Pickup","cost":"17","tax_status":"taxable"}' \
    --user=wordpress \
    --porcelain

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc shipping_zone_method create $SHIPPING_ZONE_ID \
    --method_id=flat_rate \
    --enabled=1 \
    --settings='{"title":"Kurier InPost","cost":"20","tax_status":"taxable"}' \
    --user=wordpress \
    --porcelain

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc shipping_zone_method create $SHIPPING_ZONE_ID \
    --method_id=flat_rate \
    --enabled=1 \
    --settings='{"title":"Kurier DPD","cost":"24","tax_status":"taxable"}' \
    --user=wordpress \
    --porcelain

echo "Installing wooless plugin..."
docker cp ./wordpress/wp-content/plugins/wooless $DOCKER_WORDPRESS_APP_CONTAINER:/var/www/html/wp-content/plugins
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp plugin activate wooless
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-import /var/www/html/wp-content/plugins/wooless/acf/data.json

echo "Adding products..."
CATEGORY_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp term create product_cat "Bluzy" --slug="hoodies" --porcelain)
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id="term_$CATEGORY_ID" \
    --data="{\"en_product_category_name\": \"Hoodies\"}"

PRODUCT_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc product create \
    --name="Bluza z kapturem" \
    --slug="woo-hoodie" \
    --type="simple" \
    --status="publish" \
    --sku="woo-hoodie" \
    --regular_price="45" \
    --description="Bluza z kapturem wykonana z miękkiego i przyjemnego w dotyku materiału. Jej luźny krój zapewnia komfort noszenia, a wyrazisty nadruk z emotikonem dodaje charakteru i pozytywnej energii każdej stylizacji. Świetnie sprawdzi się zarówno do codziennych, jak i sportowych outfitów. Posiada ściągacze na rękawach i dole, a także regulowany kaptur dla dodatkowej wygody." \
    --short_description="Stylowa i wygodna bluza z kapturem – idealna na co dzień." \
    --stock_quantity="20" \
    --categories="[{\"id\":$CATEGORY_ID}]" \
    --images='[{"src": "https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-2.jpg"}]' \
    --user="$WORDPRESS_ADMIN_USER" \
    --porcelain)

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id=$PRODUCT_ID \
    --data='{"en_product_name": "Hoodie with Logo", "en_product_description": "This hoodie is made from soft, high-quality fabric, ensuring maximum comfort. The relaxed fit allows for ease of movement, while the playful emoji design adds a unique and cheerful touch to your wardrobe. Ideal for casual and sporty looks, it features ribbed cuffs and hem, along with an adjustable hood for extra coziness.", "en_product_short_description": "A stylish and comfortable hoodie featuring a fun emoji print – perfect for everyday wear."}'

CATEGORY_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp term create product_cat "Akcesoria" --slug="accessories" --porcelain)
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id="term_$CATEGORY_ID" \
    --data="{\"en_product_category_name\": \"Accessories\"}"

PRODUCT_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc product create \
    --name="Okulary przeciwsłoneczne" \
    --slug="woo-sunglasses" \
    --type="simple" \
    --status="publish" \
    --sku="woo-sunglasses" \
    --regular_price="22" \
    --description="Stylowe okulary przeciwsłoneczne, które chronią Twoje oczy przed szkodliwym promieniowaniem UV. Wykonane z lekkiego, ale trwałego materiału, zapewniają komfort noszenia przez cały dzień. Idealne na słoneczne dni, do codziennych stylizacji i wakacyjnych wyjazdów." \
    --short_description="Modne i wygodne okulary przeciwsłoneczne, idealne na lato." \
    --stock_quantity="25" \
    --categories="[{\"id\":$CATEGORY_ID}]" \
    --images='[{"src": "https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/sunglasses-2.jpg"}]' \
    --user="$WORDPRESS_ADMIN_USER" \
    --porcelain)

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id=$PRODUCT_ID \
    --data='{"en_product_name": "Sunglasses", "en_product_description": "Stylish sunglasses that protect your eyes from harmful UV rays. Made from lightweight yet durable material, they ensure all-day comfort. Perfect for sunny days, everyday outfits, and vacation trips.", "en_product_short_description": "Trendy and comfortable sunglasses, perfect for summer."}'

CATEGORY_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp term create product_cat "T-Shirty" --slug="t-shirts" --porcelain)
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id="term_$CATEGORY_ID" \
    --data="{\"en_product_category_name\": \"T-Shirts\"}"

PRODUCT_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wc product create \
    --name="T-Shirt z logo" \
    --slug="woo-tshirt-logo" \
    --type="simple" \
    --status="publish" \
    --sku="woo-tshirt-logo" \
    --regular_price="25" \
    --description="Klasyczny t-shirt z nadrukowanym logo, wykonany z wysokiej jakości bawełny. Jest lekki, przewiewny i wygodny, idealny na każdą okazję. Doskonały wybór zarówno do codziennych stylizacji, jak i na luźniejsze spotkania." \
    --short_description="Uniwersalny t-shirt z logo, wygodny i stylowy." \
    --stock_quantity="40" \
    --categories="[{\"id\":$CATEGORY_ID}]" \
    --images='[{"src": "https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/t-shirt-with-logo-1.jpg"}]' \
    --user="$WORDPRESS_ADMIN_USER" \
    --porcelain)

docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp wooless acf-update \
    --id=$PRODUCT_ID \
    --data='{"en_product_name": "T-Shirt with Logo", "en_product_description": "A classic t-shirt with a printed logo, made from high-quality cotton. It is lightweight, breathable, and comfortable, perfect for any occasion. A great choice for everyday outfits and casual meetings.", "en_product_short_description": "A versatile t-shirt with a logo, comfortable and stylish."}'

echo "Adding posts..."
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp post delete 1

CATEGORY_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp term create category "Nowości" --slug="new-arrivals" --porcelain)
IMAGE_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp media import "https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/hoodie-2.jpg" --porcelain)
POST_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp post create \
    --post_type="post" \
    --post_title="Ciepło, styl i odrobina humoru!" \
    --post_status="publish" \
    --post_content="Czy może być coś lepszego na chłodniejsze dni niż stylowa bluza z kapturem?" \
    --post_excerpt="Czy może być coś lepszego na chłodniejsze dni niż stylowa bluza z kapturem?" \
    --post_author=1 \
    --post_category="$CATEGORY_ID" \
    --porcelain)
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp post meta update $POST_ID _thumbnail_id $IMAGE_ID

IMAGE_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp media import "https://woocommercecore.mystagingwebsite.com/wp-content/uploads/2017/12/t-shirt-with-logo-1.jpg" --porcelain)
POST_ID=$(docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp post create \
    --post_type="post" \
    --post_title="T-shirt z logo, który wyróżnia się z tłumu!" \
    --post_status="publish" \
    --post_content="Stylowe, wygodne i uniwersalne – idealne połączenie, które podkreśli Twój charakter." \
    --post_excerpt="Stylowe, wygodne i uniwersalne – idealne połączenie, które podkreśli Twój charakter." \
    --post_author=1 \
    --post_category="$CATEGORY_ID" \
    --porcelain)
docker exec --user www-data $DOCKER_WORDPRESS_APP_CONTAINER wp post meta update $POST_ID _thumbnail_id $IMAGE_ID

docker exec $DOCKER_APP_CONTAINER composer install

docker exec $DOCKER_APP_CONTAINER npm install
docker exec $DOCKER_APP_CONTAINER npm run build

docker exec $DOCKER_APP_CONTAINER ./up.sh

docker exec $DOCKER_APP_CONTAINER php artisan key:generate

docker exec $DOCKER_APP_CONTAINER php artisan storage:unlink
docker exec $DOCKER_APP_CONTAINER php artisan storage:link

docker exec $DOCKER_APP_CONTAINER php artisan config:clear
docker exec $DOCKER_APP_CONTAINER php artisan cache:clear

docker exec $DOCKER_APP_CONTAINER php artisan config:cache
docker exec $DOCKER_APP_CONTAINER php artisan route:cache

docker exec $DOCKER_APP_CONTAINER supervisorctl start ssr
docker exec $DOCKER_APP_CONTAINER supervisorctl start octane
