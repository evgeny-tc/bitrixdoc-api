# BitrixDoc

PHP класс для работы с документами оприходования в Битрикс24

## Установка

Подключите файл в свой скрипт:
```php
require 'path/to/catalogdoc.api.php';
```
## Примеры
### Создание нового документа
```php
use \OffBitrix\CatalogDoc;

// экземпляр класса для работы с документом
$CatalogDoc = new CatalogDoc();

// создать документ, получить его id
$new_doc_id = $CatalogDoc->Open(
    [
        'TITLE' => "Название документа"
    ]
);
```
Необязательные параметры:
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
$CatalogDoc->addElement(
    [
        'STORE_TO' => 1 // id склада
        'ELEMENT_ID' => 123 // id элемента (товара)
        'AMOUNT' => 500 // цены
        'ELEMENT_NAME' => 'Продукт №1' // название элемента
        'ELEMENT_IBLOCK_ID' => 11 // IBLOCK_ID - ID информационного блока. 
    ]
);
```
Необязательные параметры:
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
$CatalogDoc->Close();
```

### Изменить документ
```php
// Если документ был создан в этом же экземпляре класса
$CatalogDoc->Update(
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
$CatalogDoc->Update(
    [
        'TITLE' => "Новое название документа"
        ...
    ]
);
```
