# BitrixDoc

PHP класс для работы с документами складского учета в Битрикс24

## Установка

Подключите файл:
```php
require 'path/to/catalogdoc.api.php';
```
## Примеры
### Создание нового документа
```php
use \OffBitrix\CatalogDoc;

// экземпляр класса для работы с документом
$catalogDoc = new CatalogDoc();

// создать документ, получить его id
$new_doc_id = $catalogDoc->Open(
    [
        'TITLE' => "Название документа"
    ]
);
```
Доп. параметры:
```text
Array
(
    [DOC_TYPE] => \Bitrix\Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT // тип документа (по умолчанию - оприходование)
    [SITE_ID] => 's1'
    [DATE_DOCUMENT] => date("d.m.Y H:i:s") 
    [CREATED_BY] => 1
    [COMMENTARY] => ''
    [STATUS] => 'N' // не проведен, или - Y
    [WAS_CANCELLED] => 'N'
    [CONTRACTOR_ID] => 1
    [CURRENCY] => 'RUB'
    [TOTAL] => 0
)
```

```php
// добавить позиции в документ
$catalogDoc->addElement(
    [
        'STORE_TO' => 1, // id склада
        'ELEMENT_ID' => 123, // id элемента (товара)
        'AMOUNT' => 10, // количество
        'PURCHASING_PRICE' => 120, // стоимость
        'ELEMENT_NAME' => 'Продукт №1', // название элемента
        'ELEMENT_IBLOCK_ID' => 14 // iblock_id товара
    ]
);

// записать общую сумму документа
$catalogDoc->Update(
      [
          'TOTAL' => 1200
      ]
);
```
Доп. параметры:
```text
Array
(
    [PURCHASING_PRICE] => 0
    [BASE_PRICE] => 0
    [BASE_PRICE_EXTRA] => 0
    [BASE_PRICE_EXTRA_RATE] => 0
    [IS_MULTIPLY_BARCODE] => 'N'
    [RESERVED] => 0
)
```

```php
// Провести документ
$catalogDoc->Close();
```

### Указать поставщика
```php
$catalogDoc->addContractor(3, 101); // 3 - сущность контакт, 101 - id контакта
```

### Изменить документ
```php
// Если документ был создан в этом же экземпляре класса
$catalogDoc->Update(
    [
        'TITLE' => "Новое название документа"
        ...
    ]
);

// Или изменить любой другой документ
$updateCatalogDoc = new CatalogDoc();

// Указать id документа
$updateCatalogDoc->setId(7);

// Изменить свойства документа
$updateCatalogDoc->Update(
    [
        'TITLE' => "Новое название документа"
        ...
    ]
);
```
### Отменить проведение
```php
$catalogDoc = new CatalogDoc();

// Указать id документа
$catalogDoc->setId(7);

// Отменить проведение
$catalogDoc->Cancel();

// ошибки
$exception = $APPLICATION->GetException();

 if( $exception and $exceptionMessage = $exception->GetString())
{
    throw new \Exception($exceptionMessage);
}
```
### Информация по документу
```php
$catalogDoc = new CatalogDoc();

// Указать id документа
$catalogDoc->setId(7);

// массив
$data = $catalogDoc->getData();
```
### Список товаров документа
```php
$catalogDoc = new CatalogDoc();

// Указать id документа
$catalogDoc->setId(7);

// генератор массива товаров
foreach ($catalogDoc->getProducts() as $product)
{
    
}
```
### Поиск документов
```php
$docFilter = [
    'DOC_TYPE' => [
        \Bitrix\Catalog\StoreDocumentTable::TYPE_ARRIVAL,
        \Bitrix\Catalog\StoreDocumentTable::TYPE_ARRIVAL
    ],
    '>=DATE_CREATE' => "01.01.2024",
    'STATUS' => "Y"
];

foreach(CatalogDoc::getList(['*'], $docFilter) as $doc)
{
    
}
```
### Пользовательские поля
```php
$catalogDoc = new CatalogDoc();

// Указать id документа
$catalogDoc->setId(7);

$arFields = $catalogDoc->getUf();
```