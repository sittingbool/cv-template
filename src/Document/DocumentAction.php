<?php

namespace App\Document;

use Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class DocumentAction
{
    protected string $templateName;
    protected string $documentName;
    protected array $i18nDefault;

    public function __construct()
    {
        $this->templateName = realpath(__DIR__ . '/../../files/CV_template.docx');
        $this->documentName = realpath(__DIR__ . '/../../files/CV_de.docx');
        $i18nDeText = file_get_contents(__DIR__ . '/../../files/i18n/de.json');
        $i18nEnText = file_get_contents(__DIR__ . '/../../files/i18n/en.json');
        $this->i18nDefault = [
            'de' => json_decode($i18nDeText, true),
            'en' => json_decode($i18nEnText, true)
        ];
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $json = $request->getParsedBody();
        $application = new ApplicationModel($json);
        $fileTitle = preg_replace('/[^a-zA-Z0-9]+/', '_', $application->title);
        $this->documentName = str_replace('de', $fileTitle, $this->documentName);
        try {
            $templateProcessor = new TemplateProcessor($this->templateName);
            foreach ($this->i18nDefault['de'] as $key => $value) { // FIXME: hard coded language
                $templateProcessor->setValue($key, $value);
                $templateProcessor->setValue(strtoupper($key), strtoupper($value));
            }
            $this->assignApplicationValues($templateProcessor, $application);
            $this->assignApplicant($templateProcessor, $application->applicant);
            $this->assignPositions($templateProcessor, $application);
            $this->assignCareer($templateProcessor, $application->applicant);
            $this->assignEducation($templateProcessor, $application->applicant);

            $templateProcessor->saveAs($this->documentName);

            $pdfName = $this->makePDF($this->documentName);

            $response->getBody()->write(json_encode([
                'result' => true,
                'message' => 'Document created successfully',
                'pdf' => $pdfName,
                'docx' => $this->documentName
            ]));

        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode(['result' => false, 'message' => $th->getMessage()]));
            error_log($th);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignApplicationValues(TemplateProcessor $templateProcessor, ApplicationModel $application): void
    {
        $application->assignToTemplateProcessor($templateProcessor, 'formerPositions', 'skills');
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignApplicant(TemplateProcessor $templateProcessor, ApplicantModel $applicant): void
    {
        $name = $applicant->name ?? '';
        $templateProcessor->setValue('NAME', strtoupper($name));
        $applicant->assignToTemplateProcessor($templateProcessor, 'skills');
        $this->assignSkills($templateProcessor, $applicant);
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignSkills(TemplateProcessor $templateProcessor, ApplicantModel $applicant): void
    {
        if (empty($applicant->skills)) {
            return;
        }
        $categories = array_unique(array_map(fn ($item) => $item->category, $applicant->skills));
        $skillsByCategory = array();
        foreach ($categories as $category) {
            $skillsByCategory[$category] = array_filter($applicant->skills, fn ($item) => $item->category === $category);
        }
        $keys = array_keys($applicant->skills[0]->getDisplayJSON());
        $count = 0;
        $cat_replacements = array();
        $skill_replacements = array();
        foreach ($skillsByCategory as $category => $skills) {
            $cat_replacements_loop = array();
            $skill_replacements_loop = array();
            foreach ($keys as $key) {
                $cat_replacements_loop[$key] = '${' . $key . '_' . $count . '}';
            }
            foreach ($skills as $skill) {
                $data = $skill->getDisplayJSON();
                foreach ($keys as $key) {
                    $data[$key . '_' . $count] = $data[$key];
                    unset($data[$key]);
                }
                $skill_replacements_loop[] = $data;
            }
            $cat_replacements_loop['skill_category'] = $category;
            $cat_replacements[] = $cat_replacements_loop;
            $skill_replacements[] = $skill_replacements_loop;
            $count++;
        }

        $count = 0;
        $templateProcessor->cloneBlock('list_skills_tbl', 0, true, false, $cat_replacements);

        foreach ($cat_replacements as $cat_replacement) {
            $skill_replacements_loop = $skill_replacements[$count];
            $templateProcessor->cloneRowAndSetValues('skill_name_' . $count, $skill_replacements_loop);
            $count++;
        }
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignPositions(TemplateProcessor $templateProcessor, ApplicationModel $application): void
    {
        $position_replacements = array_map(fn (PositionModel $item): array => $item->getDisplayJSON(), $application->positions);
        $templateProcessor->cloneBlock('list_positions', 0, true, false, $position_replacements);
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignCareer(TemplateProcessor $templateProcessor, ApplicantModel $applicant): void
    {
        $career_replacements = array_map(fn ($item): array => $item->getDisplayJSON(), $applicant->career);
        $templateProcessor->cloneRowAndSetValues('career_title', $career_replacements);
    }

    /**
     * @throws Exception - if the language key cannot be found on the processed property
     */
    protected function assignEducation(TemplateProcessor $templateProcessor, ApplicantModel $applicant): void
    {
        $education_replacements = array_map(fn ($item): array => $item->getDisplayJSON(), $applicant->education);
        $templateProcessor->cloneRowAndSetValues('edu_title', $education_replacements);
    }

    /**
     * @param string $filename
     * @return string the file path of the pdf
     */
    protected function makePDF(string $filename): string
    {
        exec('osascript /Users/richardhabermann/Developer/sittingbool/cv-template/word_to_pdf.scpt ' . $filename);
        return $filename;
    }
}
