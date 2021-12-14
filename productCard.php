<?php

require_once ("phpQuery/phpQuery.php");

function get_productCard($urlProduct)
{
//Запись полученного результата в переменную
    $ch = curl_init($urlProduct);

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

function categoryParam($productCards, $categories)
{
    //phpQueryObject
    $productCard = pq($productCards);

    //Получаем значение категории из js скрипта $categoryEncrypted[1] - \u0415\u043b....
    preg_match("/\"ecomm_category\"\s*:\s*\"(.*?)\"/", $productCard,$categoryEncrypted);

    //Сравниваем полученное значение с значениями из массива
    $cat_array = array(
        "Елка литая"                              => "\u0415\u043b\u043a\u0430 \u043b\u0438\u0442\u0430\u044f",
        "Елка с шишками"                          => "\u0415\u043b\u043a\u0430 \u0441 \u0448\u0438\u0448\u043a\u0430\u043c\u0438",
        "Елка искусственная "                     => "\u0415\u043b\u043a\u0430 \u0438\u0441\u043a\u0443\u0441\u0441\u0442\u0432\u0435\u043d\u043d\u0430\u044f ",
        "Елочные гирлянды и новогодние веночки"   => "\u0415\u043b\u043e\u0447\u043d\u044b\u0435 \u0433\u0438\u0440\u043b\u044f\u043d\u0434\u044b \u0438 \u043d\u043e\u0432\u043e\u0433\u043e\u0434\u043d\u0438\u0435 \u0432\u0435\u043d\u043e\u0447\u043a\u0438",
        "Сосна искусственная"                     => "\u0421\u043e\u0441\u043d\u0430 \u0438\u0441\u043a\u0443\u0441\u0441\u0442\u0432\u0435\u043d\u043d\u0430\u044f",
        "Новогодняя гирлянда"                     => "\u041d\u043e\u0432\u043e\u0433\u043e\u0434\u043d\u044f\u044f \u0433\u0438\u0440\u043b\u044f\u043d\u0434\u0430",
        //"Кованая мебель для дома"               => "\u041f\u0443\u0444\u044b, \u0431\u0430\u043d\u043a\u0435\u0442\u043a\u0438",
        "Пуфы, банкетки"                          => "\u041f\u0443\u0444\u044b, \u0431\u0430\u043d\u043a\u0435\u0442\u043a\u0438",
        "Настенные и напольные вешалки. Прихожие" => "\u041d\u0430\u0441\u0442\u0435\u043d\u043d\u044b\u0435 \u0438 \u043d\u0430\u043f\u043e\u043b\u044c\u043d\u044b\u0435 \u0432\u0435\u0448\u0430\u043b\u043a\u0438. \u041f\u0440\u0438\u0445\u043e\u0436\u0438\u0435",
        "Зеркала. Журнальные столики"             => "\u0417\u0435\u0440\u043a\u0430\u043b\u0430. \u0416\u0443\u0440\u043d\u0430\u043b\u044c\u043d\u044b\u0435 \u0441\u0442\u043e\u043b\u0438\u043a\u0438",
        "Этажерки, полки. Подставки для зонтов"   => "\u042d\u0442\u0430\u0436\u0435\u0440\u043a\u0438, \u043f\u043e\u043b\u043a\u0438. \u041f\u043e\u0434\u0441\u0442\u0430\u0432\u043a\u0438 \u0434\u043b\u044f \u0437\u043e\u043d\u0442\u043e\u0432",
        "Наборы и аксессуары для каминов"         => "\u041d\u0430\u0431\u043e\u0440\u044b \u0438 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b \u0434\u043b\u044f \u043a\u0430\u043c\u0438\u043d\u043e\u0432",
        "Мини-бар"                                => "\u041c\u0438\u043d\u0438-\u0431\u0430\u0440",
        "Кованая мебель для сада"                 => "\u041a\u043e\u0432\u0430\u043d\u0430\u044f \u043c\u0435\u0431\u0435\u043b\u044c \u0434\u043b\u044f \u0441\u0430\u0434\u0430",
        "Кованые подставки"                       => "\u041a\u043e\u0432\u0430\u043d\u044b\u0435 \u043f\u043e\u0434\u0441\u0442\u0430\u0432\u043a\u0438",
        "Деревянные изделия (сувениры)"           => "\u0414\u0435\u0440\u0435\u0432\u044f\u043d\u043d\u044b\u0435 \u0438\u0437\u0434\u0435\u043b\u0438\u044f (\u0441\u0443\u0432\u0435\u043d\u0438\u0440\u044b) ",
    );

    $categoryName = array_search($categoryEncrypted[1], $cat_array);

    //Сравнить название полученной категории со списком категорий, вывести id категории
    $categoryId = array_search($categoryName, $categories, true);

    return $categoryId;

}

function descriptionParam($productCards){
    $productCard = pq($productCards);
    $description = $productCard->find('.b-user-content')->text();

    return $description;
}

function params($productCards){
    $productCard = pq($productCards);

    $data['table'] = array();

    $entry = $productCard->find('table.b-product-info tr');
    foreach ($entry as $row) {
        $row = pq($row);
        $name = $row->find('td:eq(0)')->text();
        $value = $row->find('td:eq(1)')->text();

        $data['table'][$name] = $value;
    }

    return $data['table'];
}