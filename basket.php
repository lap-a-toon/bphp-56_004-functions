<?php
// Для начала определяем кодировку (благодаря этому нормально работает в Windows-консоли с кириллицей), 
// было не обязательно (оверинжиниринг, как говорят), 
// но мне было интересно применить решение из прошлого задания здесь и немного глубже вникнуть, как это можно использовать
define('CODE_PAGE', prepareCodePage()); 

const OPERATION_EXIT = 0;
const OPERATION_ADD = 1;
const OPERATION_DELETE = 2;
const OPERATION_PRINT = 3;

$operations = [
    OPERATION_EXIT => OPERATION_EXIT . '. Завершить программу.',
    OPERATION_ADD => OPERATION_ADD . '. Добавить товар в список покупок.',
    OPERATION_DELETE => OPERATION_DELETE . '. Удалить товар из списка покупок.',
    OPERATION_PRINT => OPERATION_PRINT . '. Отобразить список покупок.',
];

$items = [];

do {
    cls();

    do {
        showCart($items);

        $label = 'Выберите операцию для выполнения: ' . PHP_EOL;
        // Проверить, есть ли товары в списке? Если нет, то не отображать пункт про удаление товаров
        $operationsToShow = array_filter($operations,function($iter)use($items){
            return (count($items)>0 || $iter!==OPERATION_DELETE);
        },ARRAY_FILTER_USE_KEY); 
        $label .= implode(PHP_EOL, $operationsToShow);
        $operationNumber = doInput($label,true);

        if (!array_key_exists($operationNumber, $operations)) {
            cls();
            echo '!!! Неизвестный номер операции, повторите попытку.' . PHP_EOL;
        }

    } while (!array_key_exists($operationNumber, $operations));

    echo 'Выбрана операция: '  . $operations[$operationNumber] . PHP_EOL;

    switch ($operationNumber) {
        case OPERATION_ADD:
            $itemToAdd = doInput('Введение название товара для добавления в список:',true);
            if($itemToAdd!=='') // Перед добавлением проверяем не пустая ли строка, если пустая - пропускаем шаг
                $items[] = $itemToAdd;
            break;

        case OPERATION_DELETE:
            // Проверить, есть ли товары в списке? Если нет, то сказать об этом и попросить ввести другую операцию
            echo 'Текущий список покупок:' . PHP_EOL;
            showCart($items);

            $itemName = doInput('Введение название товара для удаления из списка:',true);
            deleteItem($itemName,$items); // Проверка на наличие товара производится внутри функции

            break;

        case OPERATION_PRINT:
            showCart($items,$counter=true);
            doInput('Нажмите enter для продолжения...');
            break;
    }

    echo "\n ----- \n";
} while ($operationNumber > 0);

echo 'Программа завершена' . PHP_EOL;


/**
 * deleteItem
 * Если в массиве ничего нет, то сообщаем об этом
 *
 * @param  string $item Элемент, который будем удалять
 * @param  array $items Массив элементов, из которого будем удалять
 * @return bool
 */
function deleteItem(string $item,array &$items):void{
    if (in_array($item, $items, true) !== false) {
        while (($key = array_search($item, $items, true)) !== false) {
            unset($items[$key]);
        }
    }else{
        doInput('Указанный товар не найден, нажите Enter и попробуйте другую операцию...');
    }
}

/**
 * showCart
 * Выводим сисок покупок
 *
 * @param  array $items Список покупок
 * @param bool $counter Выводить ли счетчик товаров
 * @return void
 */
function showCart(array $items,bool $counter = false):void{
    if (count($items)) {
        echo 'Ваш список покупок: ' . PHP_EOL;
        echo implode("\n", $items) . "\n";
    } else {
        echo 'Ваш список покупок пуст.' . PHP_EOL;
    }
    if($counter){
        echo 'Всего ' . count($items) . ' позиций. '. PHP_EOL;
    }
};

/**
 * clearScreen
 * Очищаем экран
 * Предварительно производится проверка на ОС
 *
 * @return void
 */
function cls():void{
    if(function_exists('isWindows') && isWindows()){
        // system('cls'); // это на Windows 10 не работает, но решил оставить
        print("\033[2J\033[;H");
    }else{
        system('clear');
    }
}
/**
 * prepareCodePage
 * функция подготавливает окружение к работе в Windows-косоли и возвращает кодировку
 *
 * @return void
 */
function prepareCodePage():string{
    $myCodePage = "utf-8";
    if(function_exists('isWindows') && isWindows()){
        $myCodePage = 'cp1251';
        if (PHP_VERSION_ID < 50600) {
            iconv_set_encoding('input_encoding', $myCodePage);
            iconv_set_encoding('output_encoding', 'utf-8');
            iconv_set_encoding('internal_encoding', 'utf-8');
        } else {
            ini_set('default_encoding',$myCodePage);
            ini_set('input_encoding', $myCodePage);
        }
    }
    return $myCodePage;
}

/**
 * doInput
 *
 * @param  string $label Строка, которую требуется вывести перед запросом ввода
 * @return string
 */
function doInput(string $label = "",bool $newString=false):string{
    echo $label . (($newString)?PHP_EOL.'> ':'');
    $input=fgets(STDIN);
    if(function_exists('isWindows') && isWindows()){
        return trim(mb_convert_encoding($input,'UTF-8',CODE_PAGE));
    }else{
        return trim($input);
    }
}

/**
 * isWindows
 * проверка, не под Windows ли мы запускаемся
 *
 * @return bool
 */
function isWindows():bool{
    return mb_stripos(php_uname(), 'windows')!==false;
}
