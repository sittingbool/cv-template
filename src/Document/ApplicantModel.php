<?php

namespace App\Document;

class ApplicantModel extends BaseJSONModel
{
    public array $skills;
    public function __construct(array $json, string $lang = 'de')
    {
        $skillsData = empty($json['skills']) ? [] : $json['skills'];
        $this->skills = array_map(fn ($item) => new SkillModel($item, $lang), $skillsData);
        unset($json['skills']);
        $json['dob'] = $this->getFormattedDate($json['dob'], $lang);
        parent::__construct($json, $lang);
    }
}

