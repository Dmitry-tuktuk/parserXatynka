<?php
/*Использовать версию php 7.3*/

require_once ("phpQuery/phpQuery.php");
require_once ("productCard.php");

//Добавить запуск скрипта каждый час

//Для большого объема данных отключаем ограничению работы скрипка
set_time_limit(0);
//Отображать все ошибки
error_reporting(E_ALL);
ini_set('display_errors', 1);
//Параметры запроса в браузере
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Kiev');
ignore_user_abort(true);
ini_set('memory_limit', '12288M');
//Задержка
sleep(random_int(2, 4));

//Запуск
$url = 'https://xatynka.com.ua/product_list/page_14';
$start = 0;
$end = 1;

function get_content($url)
{
//Запись полученного результата в переменную
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//Redirect /
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//Результат будет записан в переменную, а не вывод в браузере
    //Формирование заголовков
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        //'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Cache-Control: max-age=0',
        'Connection: keep-alive',
        'Cookie: cid=179320148597937981036106296040828286629; csrf_token_company_site=970577c1fdc5494ebe782794926b5889; companies_visited_products=763868074.739330090.1487476755.1257338071.1514054914.1218193518.793006947.873316994.739330095.1487471741.1443286279.810827712.1158954452.; evoauth=wc68c555ba24c4de389d141370be74c7a; _ga_T7S2G9Q21Q=GS1.1.1639385190.34.1.1639386245.0; _ga=GA1.3.c-O1eX6QaCwfa8Z4cq2hYYnUhas0JrZi; _gcl_au=1.1.946167412.1637074137; _fbp=fb.2.1637074137837.1149148744; utmsrc_company_site=; utmcmpg_company_site=; utmmdm_company_site=; _gid=GA1.3.1809702881.1639385191; _gat_main=1; _gat_gaua_company_tracker_code=1',
        'Host: xatynka.com.ua',
        'Referer: https://xatynka.com.ua/g82519639-elka-shishkami',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-User: ?1',
        'Upgrade-Insecure-Requests: 1',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0) Gecko/20100101 Firefox/95.0'
    ));

    $result = curl_exec($ch);

    curl_close($ch);
    return $result;
}

//Передача содержимого фронта в переменную
$file = get_content($url);
$doc = phpQuery::newDocument($file);
//Пагинация
$urlPagination = $doc->find('.b-pager span.b-pager__link_type_current')->next()->attr('href');
$domen = 'https://xatynka.com.ua';
$next = $domen . $urlPagination;

//Старт XML файла
$out = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
//XML-doc
$out .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . "\r\n";
$out .= '<shop>' . "\r\n";
// Короткое название магазина, должно содержать не более 20 символов.
$out .= '<name>xatynka.com.ua</name>' . "\r\n";
// URL главной страницы магазина.
$out .= '<company>xatynka.com.ua</company>' . "\r\n";
//Название насйта
$out .= '<url>https://xatynka.com.ua/</url>' . "\r\n";
// Список курсов валют магазина.
$out .= '<currencies>' . "\r\n";
$out .= '<currency id="UAH" rate="1"/>' . "\r\n";
$out .= '</currencies>' . "\r\n";
//Список существующих категорий
$out .= '<categories>' . "\r\n";
$data['categories'] = array();
$entry = $doc->find('.cs-nav__item-inner ul li a');
//Создание массива с категориями
foreach ($entry as $row) {
    $data['categories'][] = pq($row)->text();
}
//Старт массива с 1, а не с 0
array_unshift($data['categories'], "");
unset($data['categories'][0]);
//Вывод списка категорий
foreach ($data['categories'] as $key => $value) {
    $out .= "<category id='$key'>$value</category>" . "\r\n";
}
$out .= '</categories>' . "\r\n";

//Вывод товаров:
$out .= '<offers>' . "\r\n";
if (!empty($next)) {
    while ($start < $end) {

        foreach ($doc->find('.cs-product-gallery__list .cs-product-gallery__item') as $product) {
            $product = pq($product);
            /*Подключения внутренних параметров товара*/
            $id = $product->find('.cs-product-gallery__sku')->text();
            $title = $product->find('.cs-goods-title')->text();
            $urlProduct = $product->find('.cs-goods-title')->attr('href');
            $priceSearch = preg_replace("/грн/", "|", $product->find('.cs-goods-price__value')->text());
            $priceArray = explode("|", $priceSearch);
            $oldPrice = strtok($priceArray[0], ',');
            $price = strtok($priceArray[1], ',');

            $status = $product->find('.cs-goods-data__state_val_avail')->text();

            /*Подключения внутренних параметров товара*/
            $fileProductCard = get_productCard($urlProduct);
            $productCards = phpQuery::newDocument($fileProductCard);
            $categories = $data['categories'];
            $category = categoryParam($productCards, $categories);
            $description = descriptionParam($productCards);
            $params = params($productCards);

            /*Структура
            https://vetromebel.com/ab__pfe_2_expotrproduct.xml
            */

            //Вывод внутренних блоков продукта
            if (!empty($id)) {
                $out .= '<offer id="' . $id . '">' . "\r\n";
                $out .= '<url>' . $urlProduct . '</url>' . "\r\n";
                if (empty($price[1])) {
                    $out .= '<price>' . preg_replace("/\s+/", "", $price[0]) . '</price>' . "\r\n";
                } else {
                    $out .= '<label>' . preg_replace("/\s+/", "", "Акция") . '</label>' . "\r\n";
                    $out .= '<price>' . $price . '</price>' . "\r\n";
                    $out .= '<price_old>' . $oldPrice . '</price_old>' . "\r\n";
                }
                $out .= '<currencyId>UAH</currencyId>' . "\r\n";
                $out .= '<presence>' . $status . '</presence>' . "\r\n";
                $out .= '<categoryId>' . $category . '</categoryId>' . "\r\n";
                $out .= '<name>' . $title . '</name>' . "\r\n";
                $out .= '<description><![CDATA[' . stripslashes($description) . ']]></description>' . "\r\n";
                foreach ($params as $key => $value) {
                    if (!empty($key)) {
                        $out .= "<param name='$key'>$value</param>" . "\r\n";
                    }
                    unset($params[$key]);
                }
                $out .= '</offer>' . "\r\n";
            }
        }
        $start++;
    }
}
$out .= '</offers>' . "\r\n";
$out .= '</shop>' . "\r\n";
$out .= '</yml_catalog>' . "\r\n";

//Вывод
header('Content-Type: text/xml; charset=utf-8');
echo $out;
exit;