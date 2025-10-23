<?php

namespace App\Document;

class ApplicationModel extends BaseJSONModel
{
    public ApplicantModel $applicant;
    public array $positions;

    public function __construct(array $json, string $lang = 'de')
    {
        $this->applicant = new ApplicantModel($json['applicant'], $lang);
        unset($json['applicant']);
        unset($json['_missingSkills']);
        $this->positions = array_map(fn ($item) => new PositionModel($item, $lang), $json['formerPositions']);
        unset($json['formerPositions']);
        $json['applicationDate'] = $this->getFormattedDate($json['applicationDate'], $lang);
        parent::__construct($json, $lang);
    }
}

