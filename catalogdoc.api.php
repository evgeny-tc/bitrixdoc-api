<?php
/**
 * API для работы с документами оприходования
 * https://off-group.com/
 */

namespace OffBitrix;

Class CatalogDocs
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

        $this->doc_id = \CCatalogDocs::add(
            [
                "DOC_TYPE" => $arFields['DOC_TYPE'] ?: \Bitrix\Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
                "SITE_ID" => $arFields['SITE_ID'] ?: 's1',
                "DATE_DOCUMENT" => $arFields['DATE_DOCUMENT'] ?: date("d.m.Y H:i:s"),
                "CREATED_BY" => $arFields['CREATED_BY'] ?: 1,
                "MODIFIED_BY" => $arFields['MODIFIED_BY'] ?: 1,
                "RESPONSIBLE_ID" => $arFields['RESPONSIBLE_ID'] ?: 1,
                "COMMENTARY" => $arFields['COMMENTARY'] ?: '',
                "TITLE" => $arFields['TITLE'],
                "STATUS" => $arFields['STATUS'] ?: "N",
                "WAS_CANCELLED" => $arFields['WAS_CANCELLED'] ?: "N",
                "CONTRACTOR_ID" => "1",
                "CURRENCY" => $arFields['CURRENCY'] ?: 'RUB', # EUR
                "TOTAL" => intval($arFields['TOTAL'])
            ]
        );

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
}
