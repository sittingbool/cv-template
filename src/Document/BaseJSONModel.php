<?php

namespace App\Document;

use Exception;
use IntlDateFormatter;
use PhpOffice\PhpWord\TemplateProcessor;

class BaseJSONModel
{
    protected string $_templatePrefix = '';
    protected array $json;
    protected string $lang = 'de';
    protected array $i18nTexts;

    public function __construct(array $json, string $lang = 'de')
    {
        $this->json = $json;
        $this->lang = $lang;
        $this->i18nTexts = $_ENV['i18n'][$lang];
    }

    /**
     * @throws Exception - if the value is an array and does not have a key for the given language
     */
    public function __get($propertyName): string
    {
        return $this->parseTranslated($this->json[$propertyName]);
    }

    /**
     * @throws Exception - if the raw data does not have a key for the given language for any of the properties
     */
    public function getDisplayJSON(string ...$excludeKeys): array
    {
        $output = array();
        foreach ($this->json as $key => $value) {
            if (in_array($key, $excludeKeys)) {
                continue;
            }
            $output[$this->_templatePrefix . $key] = $this->parseTranslated($value);
        }
        return $output;
    }

    /**
     * @throws Exception - if the raw data does not have a key for the given language for any of the properties
     */
    public function assignToTemplateProcessor(TemplateProcessor $templateProcessor, string ...$excludeKeys): void
    {
        foreach ($this->getDisplayJSON(...$excludeKeys) as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }
    }

    /**
     * @throws Exception - if the given array does not have a key for the given language
     */
    protected function parseTranslated(string | array | null $value): string
    {
        if (empty($value)) {
            return '';
        }
        if (is_array($value)) {
            if(!is_string($value[$this->lang])) {
                throw new Exception('Cannot resolve localized string from '. json_encode($value));
            }
            return $value[$this->lang];
        }
        return $value;
    }

    protected function getFormattedDate(string $date, string $locale): string
    {
        $locale = $locale == 'de' ? 'de_DE' : 'en_US';
        $date = date_create($date);
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        return $formatter->format($date);
    }
}
