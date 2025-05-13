<?php

namespace App\Document;

class CareerModel extends LifeStationModel
{
    protected string $_templatePrefix = 'career_';

    public function __construct(array $json, string $lang = 'de')
    {
        parent::__construct($json, $lang);
    }
}