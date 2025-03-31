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

    public function __construct()
    {
        $this->templateName = __DIR__ . '/../../files/template.docx';
        $this->documentName = __DIR__ . '/../../files/Result.docx';
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $json = $request->getParsedBody();
        $application = new ApplicationModel($json);
        try {
            $templateProcessor = new TemplateProcessor($this->templateName);
            $this->assignApplicant($templateProcessor, $application->applicant);
            $this->assignPositions($templateProcessor, $application);

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
    protected function assignApplicant(TemplateProcessor $templateProcessor, ApplicantModel $applicant): void
    {
        $applicant->assignToTemplateProcessor($templateProcessor, 'skills');
        $skill_replacements = array_map(fn ($item): array => $item->getDisplayJSON(), $applicant->skills);
        $templateProcessor->cloneBlock('list_skills', 0, true, false, $skill_replacements);
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
     * @param string $filename
     * @return string the file path of the pdf
     */
    protected function makePDF(string $filename): string
    {
        $pdfName = str_replace('.docx', '.pdf', $filename);
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        // Any writable directory here. It will be ignored.
        Settings::setPdfRendererPath('.');

        $phpWord = IOFactory::load($filename);
        $phpWord->save($pdfName, 'PDF');
        return $pdfName;
    }
}
