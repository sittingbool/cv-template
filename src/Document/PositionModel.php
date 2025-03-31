<?php

namespace App\Document;

class PositionModel extends BaseJSONModel
{
    protected string $_templatePrefix = 'position_';

    public function __construct(array $json, string $lang = 'de')
    {
        $json['technologies'] = join(', ', $json['technologies']);
        $dateFormat = 'F Y';
        $json['startDate'] = date_format(date_create($json['startDate']), $dateFormat);
        $json['endDate'] = date_format(date_create($json['endDate']), $dateFormat);
        parent::__construct($json, $lang);
    }
}
