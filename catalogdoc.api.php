<?php
/**
 * API для работы с документами оприходования
 * https://off-group.com/
 */

namespace OffBitrix;

use Bitrix\Catalog\StoreDocumentTable;

Class CatalogDoc
{
    private int $doc_id;

    /**
     * Задать id документа
     * @param int $id
     * @return void
     */
    public function setId(int $id) : void
    {
        $this->doc_id = $id;
    }

    /**
     * Документ (ex)
     * https://bxapi.ru/src/?module_id=catalog&name=CCatalogDocs::getList
     * @return array
     * @throws \Exception
     */
    public function getData() :  array
    {
        $query = \CCatalogDocs::getList(
            [],
            [
                'ID' => $this->doc_id
            ],
            false,
            false,
            []
        );

        if( $data = $query->fetch() )
        {
            return $data;
        }

        throw new \Exception('Документ не найден!');
    }

    /**
     * Создать документ
     * @param array $arFields
     * @return int
     * @throws \Exception
     */
    public function Open(array $arFields) : int
    {
        if( ! \CModule::IncludeModule("catalog") )
        {
            throw new \Exception('Модуль каталога не подключен!');
        }

        if( ! isset( $arFields['TITLE'] ) )
        {
            throw new \Exception('Поле TITLE не заполнено!');
        }

        $arFields['DOC_TYPE'] = $arFields['DOC_TYPE'] ?: \Bitrix\Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
        $arFields['SITE_ID'] = $arFields['SITE_ID'] ?: 's1';
        $arFields['DATE_DOCUMENT'] = $arFields['DATE_DOCUMENT'] ?: date("d.m.Y H:i:s");
        $arFields['RESPONSIBLE_ID'] = $arFields['RESPONSIBLE_ID'] ?: 1;
        $arFields['STATUS'] = $arFields['STATUS'] ?: 'N';
        $arFields['WAS_CANCELLED'] = $arFields['WAS_CANCELLED'] ?: 'N';
        $arFields['CURRENCY'] = $arFields['CURRENCY'] ?: 'RUB';

        $this->doc_id = \CCatalogDocs::add($arFields);

        if( ! $this->doc_id )
        {
            throw new \Exception('Ошибка создания документа!');
        }

        return $this->doc_id;
    }

    /**
     * Провести документ
     * @return void
     */
    public function Close() : void
    {
        $doc = new \CCatalogDocs;

        $doc->conductDocument($this->doc_id);
    }

    /**
     * Изменить документ
     * @param array $arFields
     * @return void
     */
    public function Update(array $arFields) : void
    {
        $doc = new \CCatalogDocs;

        $doc->Update($this->doc_id, $arFields);
    }

    /**
     * Добавить позицию в документ
     * @param array $arFields
     * @return void
     * @throws \Exception
     */
    public function addElement(array $arFields) : void
    {
        if( ! $arFields['STORE_TO'] and ! $arFields['STORE_FROM'] )
        {
            throw new \Exception('Склад не указан!');
        }

        if( ! $arFields['ELEMENT_ID'] )
        {
            throw new \Exception('ID элемента (товара) не указан!');
        }

        if( ! $arFields['ELEMENT_IBLOCK_ID'] )
        {
            throw new \Exception('ID информационного блока не указан!');
        }

        # b_catalog_docs_element
        #
        \CCatalogStoreDocsElement::add(
            [
                "DOC_ID" => $this->doc_id,
                "STORE_TO" => $arFields['STORE_TO'], # склад прихода
                "STORE_FROM" => $arFields['STORE_FROM'], # склад списания
                "ELEMENT_ID" => $arFields['ELEMENT_ID'],
                "ELEMENT_IBLOCK_ID" => $arFields['ELEMENT_IBLOCK_ID'],
                "AMOUNT" => $arFields['AMOUNT'],
                "PURCHASING_PRICE" => $arFields['PURCHASING_PRICE'] ?: 0,
                "BASE_PRICE" => $arFields['BASE_PRICE'] ?: 0,
                "BASE_PRICE_EXTRA" => $arFields['BASE_PRICE_EXTRA'] ?: 0,
                "BASE_PRICE_EXTRA_RATE" => $arFields['BASE_PRICE_EXTRA_RATE'] ?: 0,
                "IS_MULTIPLY_BARCODE" => $arFields['IS_MULTIPLY_BARCODE'] ?: "N",
                "RESERVED" => $arFields['RESERVED'] ?: 0,
                "ELEMENT_NAME" => $arFields['ELEMENT_NAME'] ?: 'Название'
            ]
        );
    }

    /**
     * Список товаров
     * @return \Generator
     */
    public function getProducts() : \Generator
    {
        $query = \CCatalogStoreDocsElement::getList(
            [
                'ID' => 'DESC'
            ],
            [
                'DOC_ID' => $this->doc_id
            ]
        );

        while($row = $query->fetch())
        {
            yield $row;
        }
    }

    /**
     * Список документов
     * @param array|null $select
     * @param array|null $filter
     * @param array|null $sort
     * @return \Generator
     */
    public static function getList(?array $select = ['*'], ?array $filter = [], ?array $sort = ['ID' => 'ASC']) : \Generator
    {
        $dbItems = \Bitrix\Catalog\StoreDocumentTable::getList(
            [
                'order' => $sort,
                'select' => array_merge(
                    $select,
                    [ 'CONTRACTOR_DATA_' => 'CONTRACTOR_DATA.*']
                ),
                'filter' => $filter,
                'runtime' => [
                    new Reference(
                        'CONTRACTOR_DATA',
                        \Bitrix\Crm\Integration\Catalog\Contractor\StoreDocumentContractorTable::class,
                        Join::on('this.ID', 'ref.DOCUMENT_ID'),
                        ['join_type' => 'LEFT']
                    )
                ],
                'data_doubling' => false
            ]
        );

        while($row = $dbItems->fetch())
        {
            yield $row;
        }
    }

    /**
     * Привязать поставщика
     * @param int $entity_type_id
     * @param int $entity_id
     * @return void
     */
    public function addContractor(int $entity_type_id, int $entity_id) : void
    {
        \Bitrix\Crm\Integration\Catalog\Contractor\StoreDocumentContractorTable::add(
            [
                'DOCUMENT_ID' => $this->doc_id,
                'ENTITY_ID' => $entity_id, // id поставщика
                'ENTITY_TYPE_ID' => $entity_type_id // type_id (3 - контакт, 4 - компания)
            ]
        );
    }

    /**
     * Получить пользовательские поля документа
     * @return array
     * @throws \Exception
     */
    public function getUf() : array
    {
        global $DB;

        $fields = [];
        $doc_type = '';

        if( $this->doc_id )
        {
            $getData = $this->getData();
            $doc_type = $getData['DOC_TYPE'];
        }

        $table_name = match ($doc_type) {
            # приход
            #
            \Bitrix\Catalog\StoreDocumentTable::TYPE_ARRIVAL => 'b_uts_cat_store_document_a',

            # списание
            #
            \Bitrix\Catalog\StoreDocumentTable::TYPE_DEDUCT => 'b_uts_cat_store_document_d',

            default => ''
        };

        if( $table_name )
        {
            $results = $DB->Query("SELECT * FROM {$table_name} WHERE VALUE_ID = {$this->doc_id}");

            if ($row = $results->Fetch())
            {
                $fields = $row;
            }
        }

        return $fields;
    }
}
