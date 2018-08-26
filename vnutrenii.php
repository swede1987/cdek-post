<?php
$weight = variable_get('weight2', $default = '1');
$height = variable_get('height2', $default = '1');
$length = variable_get('length2', $default = '1');
$price = variable_get('price2', $default = '1');
$width = variable_get('width2', $default = '1');
//if (isset($_SERVER['QUERY_STRING'])){
//$argument = ($_SERVER['QUERY_STRING']);
//} else {
$argument = $_COOKIE["cookie_ind"];
//}
//var_dump($argument);
$From = '450000';
$To = $argument;
$Weight = $weight*1000;
$Valuation = 0;   //substr($price, 0, -6);
$Country = 'RU';
$Charset = 'utf-8'; // Набор символов.
$Site = 'colorbum.ru'; // Название сайта клиента
$Email = 'swede198@yandex.ru';   // Электронная почта для извещений о превышении лимита
$PostcalcServer1 = 'api.postcalc.ru';  // Рабочий сервер Postcalc.RU
$PostcalcServer2 = 'test.postcalc.ru'; // Резервное зеркало Postcalc.RU
$Timeout = 3; // При недоступности рабочего сервера переключиться на резервный через 3 сек.
$CacheDir = sys_get_temp_dir(); // Каталог для хранения кэшированных данных.
$CacheValid = 1; // Кэш действителен в течение 600 сек.
header("Content-Type: text/html; charset=$Charset");
$QueryString  = 'f='  .rawurlencode( $From );
$QueryString .= '&t='  .rawurlencode( $To );
$QueryString .= "&w=$Weight&v=$Valuation&c=RU&o=php&cs=$Charset&st=$Site&ml=$Email";
$TimestampNow = time();
foreach ( glob("$CacheDir/postcalc_*.txt") as $CacheFile )
    if ( ($TimestampNow - filemtime($CacheFile) )  > $CacheValid ) unlink( $CacheFile );
$CacheFile = $CacheDir. '/postcalc_' .md5($QueryString) .'.txt';
if ( file_exists( $CacheFile ) ) {
    $arrResponse = unserialize( file_get_contents($CacheFile) );
} else {
    if ( function_exists('curl_init') ) {
        $curl = curl_init();
        curl_setopt_array($curl,
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CONNECTTIMEOUT => $Timeout,
                CURLOPT_HTTPHEADER => array('Connection: close', 'Accept-Encoding: gzip'),
                CURLOPT_USERAGENT => phpversion()
            )
        );
    } else {
        die("Не установлен пакет php-curl!");
    }
    curl_setopt($curl, CURLOPT_URL, "http://$PostcalcServer1/?$QueryString");
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $argument);

    $Response = curl_exec($curl);
    if ( !$Response = curl_exec($curl) ) {
        // Если по какой-то причине рабочий сервер недоступен, переходим на резервное зеркало
        curl_setopt($curl, CURLOPT_URL, "http://$PostcalcServer2/?$QueryString");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $argument);
        if ( !$Response = curl_exec($curl) ) {
            die("Не удалось соединиться с $PostcalcServer1 и $PostcalcServer2 в течение $Timeout сек.!");
        }
    }
    curl_close($curl);
    if ( substr($Response,0,3) == "\x1f\x8b\x08" )  $Response=gzinflate(substr($Response,10,-8));
    $arrResponse = unserialize($Response);
    if ( $arrResponse['Status'] != 'OK' ) die("Сервер вернул ошибку: $arrResponse[Status]!");
    // Если ошибки не было, сохраняем ответ в кэше
    file_put_contents($CacheFile, $Response);
}
$chUrl = "https://kit.cdek-calc.ru/api/?weight=$weight&width=$width&length=$length&height=$height&from_post_code=450000&to_post_code=$argument&contract=2&pay_to=0&tariffs=11,62";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json')
);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $argument);
$result = curl_exec($ch);
curl_close($ch);
$resultItog = json_decode($result, true);

$cdekDom = $resultItog[11]['result']['price'];
$cdekDom2 = "СДЭК до дома(" .$cdekDom. " руб)";
$cdekMag = $resultItog[62]['result']['price'];
$cdekMag2 = "СДЭК до терминала(" .$cdekMag. " руб)";
$itog2 = "Почта России(".$arrResponse['Отправления']['ЦеннаяПосылка']['Доставка']." руб.)";

return array('pochta' => "$itog2", 'cdekdom' => "$cdekDom2", 'cdekmag' => "$cdekMag2");
?>
