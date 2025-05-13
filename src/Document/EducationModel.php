<?php

namespace App\Document;

class EducationModel extends LifeStationModel
{
    protected string $_templatePrefix = 'edu_';

    public function __construct(array $json, string $lang = 'de')
    {
        parent::__construct($json, $lang);
    }
}