<?php

namespace App\Document;

class SkillModel extends BaseJSONModel
{
    protected string $_templatePrefix = 'skill_';

    public function __construct(array $json, string $lang = 'de')
    {
        $current_year = intval(date('Y'));
        $json['years'] = ($current_year - $json['since']) . ' ' . ($lang == 'de' ? 'Jahre' : 'years');
        unset($json['since']);
        parent::__construct($json, $lang);
    }
}