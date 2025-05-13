<?php

namespace App\Document;

class LifeStationModel extends BaseJSONModel
{
    protected string $_templatePrefix = 'lifeStation_';

    public function __construct(array $json, string $lang = 'de')
    {
        parent::__construct($json, $lang);
        $this->json['date'] = $this->getFormattedDateRange($json['startDate'], $json['endDate'], $lang);
    }

    private function getFormattedDateRange(string $fromDate, string | null $toDate, string $lang = 'de'): string
    {
        $format = 'Y/m';
        if ($lang == 'de') {
            $format = 'm/Y';
        }
        $from = date_format(date_create($fromDate), $format);
        if (empty($toDate)) {
            return ucfirst($this->i18nTexts['c_since']) . ' ' . $from;
        }
        $to = date_format(date_create($toDate), $format);
        return $from." \u{2014} ".$to;
    }
}
