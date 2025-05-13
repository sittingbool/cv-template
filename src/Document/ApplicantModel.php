<?php

namespace App\Document;

class ApplicantModel extends BaseJSONModel
{
    public array $skills;
    public array $career;
    public array $education;
    public function __construct(array $json, string $lang = 'de')
    {
        $skillsData = empty($json['skills']) ? [] : $json['skills'];
        $this->skills = array_map(fn ($item) => new SkillModel($item, $lang), $skillsData);
        unset($json['skills']);
        $careerData = empty($json['career']) ? [] : $json['career'];
        $this->career = array_map(fn ($item) => new CareerModel($item, $lang), $careerData);
        unset($json['career']);
        $educationData = empty($json['education']) ? [] : $json['education'];
        $this->education = array_map(fn ($item) => new EducationModel($item, $lang), $educationData);
        unset($json['education']);
        $json['dob'] = $this->getFormattedDate($json['dob'], $lang);
        parent::__construct($json, $lang);
    }
}

