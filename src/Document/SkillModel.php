<?php

namespace App\Document;

class SkillModel extends BaseJSONModel
{
    protected string $_templatePrefix = 'skill_';

    public function __construct(array $json, string $lang = 'de')
    {
        $json['years'] = $json['years'] . ' ' . ($lang == 'de' ? 'Jahre' : 'years');
        parent::__construct($json, $lang);
    }
}