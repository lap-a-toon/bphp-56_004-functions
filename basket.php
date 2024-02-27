<?php
declare(strict_types = 1);
// Для начала определяем кодировку (благодаря этому нормально работает в Windows-консоли с кириллицей), 
// было не обязательно (оверинжиниринг, как говорят), 
// но мне было интересно применить решение из прошлого задания здесь и немного глубже вникнуть, как это можно использовать
define('CODE_PAGE', prepareCodePage()); 

const OPERATION_EXIT = 0;
const OPERATION_ADD = 1;
const OPERATION_DELETE = 2;
const OPERATION_PRINT = 3;
const OPERATION_RENAME = 4;
const OPERATION_RECOUNT = 5;

$operations = [
    OPERATION_EXIT => OPERATION_EXIT . '. Завершить программу.',
    OPERATION_ADD => OPERATION_ADD . '. Добавить товар в список покупок.',
    OPERATION_DELETE => OPERATION_DELETE . '. Удалить товар из списка покупок.',
    OPERATION_PRINT => OPERATION_PRINT . '. Отобразить список покупок.',
    OPERATION_RENAME => OPERATION_RENAME . '. Переименовать позицию в заказе.',
    OPERATION_RECOUNT => OPERATION_RECOUNT . '. Изменить количество товара в заказе.',
];

$items = [];

do {
    cls();

    do {
        operationPrint($items);

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
            operationAdd($items);
            break;

        case OPERATION_DELETE:
            // Проверить, есть ли товары в списке? Если нет, то сказать об этом и попросить ввести другую операцию
            operationDelete($items);
            break;

        case OPERATION_PRINT:
            operationPrint($items,true);
            break;
        
        case OPERATION_RENAME:
            operationRename($items);
            break;
        case OPERATION_RECOUNT:
            operationRecount($items);
            break;
    }

    echo "\n ----- \n";
} while ($operationNumber > 0);

echo 'Программа завершена' . PHP_EOL;

/**
 * findItemByName
 * Поиск товара по массиву
 * Если указать $itemName, то будет искать по этому параметру, в противном случае запросит ввод
 * 
 * @param  mixed $items массив товаров
 * @param  mixed $itemName (необязательно) Имя искомого товара
 * @return bool
 */
function findItemByName(array $items,string $itemName=''):bool|int {
    if($itemName===''){
        echo 'Текущий список покупок:' . PHP_EOL;
        operationPrint($items);
        $itemName = inputItemName('Введение название товара для редактирования из списка:');
    }
    return array_search($itemName, array_column($items, 'name'));
}

/**
 * operationAdd
 * добавление товара
 * запрашивает ввод и проверяет на наличие товара в массиве
 * если такое название уже есть перейдёт к изменению его количества
 *
 * @param  mixed $items массив товаров
 * @return void
 */
function operationAdd(array &$items):void{
    do{
        $itemToAdd = doInput('Введение название товара для добавления в список:',true);
        if($itemToAdd === '')
            echo "Укажите название товара" . PHP_EOL;
    }while($itemToAdd === '');

    $searchResult=findItemByName($items,$itemToAdd);
    if($searchResult !== false){
        echo "Такой товар уже есть в корзине, его количество будет изменено на новое" . PHP_EOL;
    }else{
        $items[] = [
            'name'      => $itemToAdd,
            'quantity'  => 0,
        ];
    }
    operationRecount($items,$itemToAdd);
}

/**
 * operationDelete
 * Удаление товара
 * запрашивает имя товара, если таковое есть - удаляет элемент массива со сдвигом
 *
 * @param  mixed $items массив товаров
 * @return void
 */
function operationDelete(array &$items):void{
    $searchResult=findItemByName($items);
    if($searchResult !== false){
        array_splice($items,$searchResult,1);
    }else{
        doInput('Указанный товар не найден, нажите Enter и попробуйте другую операцию...');
    }
}

/**
 * operationRename
 * Переимнование товара в корзине
 * Если при переименовании находится товар с таким же названием, предлагаем выбрать другое имя
 *
 * @param  mixed $items массив товаров
 * @return void
 */
function operationRename(array &$items):void{
    $searchResult=findItemByName($items);
    if($searchResult !== false){
        do{
            $itemNameNew = inputItemName('Введение НОВОЕ название товара:');
            if(findItemByName($items,$itemNameNew)!==false)
                echo "Товар с таким названием уже есть, выберите другое имя".PHP_EOL;
        }while(findItemByName($items,$itemNameNew)!==false);
        $items[$searchResult]['name'] = $itemNameNew;
    }else{
        doInput('Указанный товар не найден, нажите Enter и попробуйте другую операцию...');
    }
}

/**
 * operationRecount
 * Меняет количество товара
 * По-умолчанию запрашивает Имя товара
 * Если указать товар $item, то запроса не будет
 *
 * @param  mixed $items массив товаров
 * @param  mixed $item (не обязательно) товар, количество которого будем менять. Используется при добавлении товара
 * @return void
 */
function operationRecount(array &$items, string $item=''):void{
    $searchResult=findItemByName($items,$item);
    if($searchResult !== false){
        $itemQuantity = doInput('Введение количество товара:',true,true);
        $items[$searchResult]['quantity'] = $itemQuantity;
    }else{
        doInput("Указанный товар $item не найден, нажите Enter и попробуйте другую операцию...");
    }
}

/**
 * inputItemName
 * Запрашиваем ввод имени товара
 * Если ввод будет пустой - запросит повторно
 *
 * @param  mixed $label заголовок перез вводом
 * @return string
 */
function inputItemName(string $label):string{
    do{
        $name = doInput($label,true);
    }while($name==='');
    return $name;
}

/**
 * operationPrint
 * Вывод списка товаров
 *
 * @param  mixed $items Массив с покупками
 * @param  mixed $operPrint (не обязательно) Выводить ли счетчик и ожидание ввода (используется при выводе списка по команде)
 * @return void
 */
function operationPrint(array $items,bool $operPrint = false):void{
    if (count($items)) {
        echo 'Ваш список покупок: ' . PHP_EOL;
        foreach($items as $item){
            echo "$item[name] - $item[quantity] шт." . PHP_EOL;
        }
    } else {
        echo 'Ваш список покупок пуст.' . PHP_EOL;
    }
    if($operPrint){
        echo 'Всего ' . count($items) . ' позиций. '. PHP_EOL;
        doInput('Нажмите enter для продолжения...');
    }
}


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
function doInput(string $label = "",bool $newString=false,bool $numeric = false):string|int{
    echo $label . (($newString)?PHP_EOL.'> ':'');
    $input=fgets(STDIN);
    if(function_exists('isWindows') && isWindows()){
        $prepared = trim(mb_convert_encoding($input,'UTF-8',CODE_PAGE));
    }else{
        $prepared = trim($input);
    }
    if($numeric && !is_numeric($prepared)){
        $result = doInput("Укажите число",true,true);
    }else{
        $result = ($numeric)?intval($prepared):$prepared;
    }
    return $result;
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
