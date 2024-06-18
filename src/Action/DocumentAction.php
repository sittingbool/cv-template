<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

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
        $list = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
        try {
            $templateProcessor = new TemplateProcessor($this->templateName);
            $templateProcessor->setValue('firstname', 'Richard');
            $templateProcessor->setValue('lastname', 'Habermann');

            $replacements = [];
            for($i = 0; $i < count($list); $i++) {
                $replacements[] = array('item' => $list[$i]);
            }
            $templateProcessor->cloneBlock('block', 0, true, false, $replacements);


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
