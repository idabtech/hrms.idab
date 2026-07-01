<?php

namespace App\Http\Controllers;

use App\Models\ToolkitDocument;
use App\Models\ToolkitDocumentEdit;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use ZipArchive;
use setasign\Fpdi\Fpdi;
use PhpOffice\PhpWord\IOFactory as IOFACTORYDOCX;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;
use Illuminate\Http\UploadedFile;


class ToolkitDocumentsController extends Controller
{
    public function index()
    {
        $documents = ToolkitDocument::where('user_id', Auth::id())->latest()->get();
        return view('toolkit_documents.index', compact('documents'));
    }

    public function create()
    {
        return view('toolkit_documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,docx,xlsx,csv',
        ]);

        $dir = 'uploads/toolkit-documents';

        $uploadedFile = $request->file('file');
        $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $path = Utility::upload_file($request, 'file', $fileNameToStore, $dir, []);

        if (!isset($path['flag']) || $path['flag'] != 1) {
            return redirect()->route('toolkit_documents.index')
                ->with('error', __($path['msg'] ?? 'Upload failed.'));
        }

        ToolkitDocument::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'file_path' => $path['url'],
            'file_type' => $extension,
            'is_editable' => true,
        ]);

        return redirect()->route('toolkit_documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function edit($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);
        return view('toolkit_documents.edit', compact('document'));
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,docx,xlsx,csv',
        ]);

        if ($request->hasFile('file')) {
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            $dir = 'uploads/toolkit-documents';
            $uploadedFile = $request->file('file');
            $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $path = Utility::upload_file($request, 'file', $fileNameToStore, $dir, []);
            if (!isset($path['flag']) || $path['flag'] != 1) {
                return redirect()->route('toolkit_documents.index')->with('error', __($path['msg'] ?? 'File update failed.'));
            }

            $document->file_path = $path['url'];
            $document->file_type = $extension;
        }

        $document->title = $request->title;
        $document->save();

        return redirect()->route('toolkit_documents.index')->with('success', 'Document updated successfully.');
    }

    public function destroy($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('toolkit_documents.index')->with('success', 'Document deleted successfully.');
    }

    public function download($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        return Storage::download($document->file_path);
    }

    public function preview($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $fileUrl = asset(Storage::url('uploads/toolkit-documents/' . $document->file_path));
        $localPath = storage_path('app/public/uploads/toolkit-documents/' . $document->file_path);

        $htmlContent = null;
        $spreadsheetData = [];

        try {
            if ($ext === 'docx') {
                $phpWord = IOFACTORYDOCX::load($localPath);
                $htmlWriter = IOFACTORYDOCX::createWriter($phpWord, 'HTML');
                ob_start();
                $htmlWriter->save('php://output');
                $htmlContent = ob_get_clean();

            } elseif ($ext === 'xlsx') {
                try {
                    $spreadsheet = IOFactory::load($localPath);
                    $sheet = $spreadsheet->getActiveSheet();
                    $spreadsheetData = $sheet->toArray();
                } catch (\Exception $e) {
                    $spreadsheetData = ['error' => $e->getMessage()];
                }
            }
        } catch (\Exception $e) {
            $htmlContent = '<div class="alert alert-danger">Failed to convert ' . strtoupper($ext) . ': ' . $e->getMessage() . '</div>';
        }

        return view('toolkit_documents.preview', compact('document', 'fileUrl', 'ext', 'htmlContent', 'spreadsheetData'));
    }

    public function editContent($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        if (!$document->is_editable) {
            return redirect()->back()->with('error', 'Document is not editable.');
        }

        $realPath = public_path('storage/uploads/toolkit-documents/' . $document->file_path);

        if (!file_exists($realPath)) {
            return redirect()->back()->with('error', 'Document file not found.');
        }

        $fileType = strtolower($document->file_type);
        $content = '';

        try {
            switch ($fileType) {
                case 'pdf':
                    // $pdfData = $this->extractPdfText($realPath);
                    // $content = $pdfData['text'];

                    // $content = $this->extractPdfText($realPath);
                    // break;

                    $edit = ToolkitDocumentEdit::where('toolkit_document_id', $document->id)
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->first();

                    $editedData = $edit ? json_decode($edit->content, true) : [];

                    return view('toolkit_documents.edit_pdf', compact('document', 'realPath', 'editedData'));

                // return view('toolkit_documents.edit_pdf', compact('document', 'realPath'));

                case 'html':
                case 'htm':
                    $content = file_get_contents($realPath);
                    break;

                case 'doc':
                case 'docx':
                    $phpWord = IOFACTORYDOCX::load($realPath);
                    $tempFile = storage_path('app/temp_doc.html');
                    $phpWord->save($tempFile, 'HTML');
                    $content = file_get_contents($tempFile);
                    @unlink($tempFile);
                    break;

                case 'xls':
                case 'xlsx':
                    $spreadsheet = IOFactory::load($realPath);
                    $sheetData = $spreadsheet->getActiveSheet()->toArray();
                    $content = '<table border="1" cellpadding="5">';
                    foreach ($sheetData as $row) {
                        $content .= '<tr>';
                        foreach ($row as $cell) {
                            $content .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $content .= '</tr>';
                    }
                    $content .= '</table>';
                    break;

                default:
                    $content = file_get_contents($realPath);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error parsing file: ' . $e->getMessage());
            $content = 'Unable to load content. Error: ' . $e->getMessage();
        }

        return view('toolkit_documents.edit_content', compact('document', 'content'))->with('images');
    }

    protected function extractPdfText($pdfPath)
    {
        $output = [
            'text' => '',
            'images' => [],
        ];

        $baseName = pathinfo($pdfPath, PATHINFO_FILENAME) . '_' . time();
        $outputDir = 'uploads/pdf_output';

        // Ensure directory exists
        if (!is_dir(public_path($outputDir))) {
            mkdir(public_path($outputDir), 0755, true);
        }

        // Extract text
        $textPath = public_path("$outputDir/{$baseName}.txt");
        exec("pdftotext \"$pdfPath\" \"$textPath\"");

        if (file_exists($textPath)) {
            $textContent = file_get_contents($textPath);
            $cleanText = preg_replace("/\f+/", "\n", $textContent); // Remove form feeds
            $output['text'] = trim($cleanText);
        }

        // Extract embedded images
        $imgBase = public_path("$outputDir/{$baseName}");
        exec("pdfimages -j \"$pdfPath\" \"$imgBase\""); // -j preserves JPEG format

        // Get all extracted images (pdfimages outputs raw formats like jpg, ppm, pbm)
        $imageFiles = glob($imgBase . '*.{jpg,jpeg,ppm,pbm}', GLOB_BRACE);
        $imageUrls = [];


        foreach ($imageFiles as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            // Convert string file path to UploadedFile object
            $uploadedFile = new UploadedFile(
                $filePath,
                $fileNameToStore,
                mime_content_type($filePath),
                null,
                true // Set to true as it's already a valid file
            );

            // Create a pseudo-request object
            $request = new Request();
            $request->files->set('file', $uploadedFile);

            // Upload
            $upload = Utility::upload_file($request, 'file', $fileNameToStore, $outputDir, []);

            if (isset($upload['flag']) && $upload['flag'] == 1) {
                $imageUrls[] = Utility::get_file('uploads/pdf_output/' . $upload['url']);
            }
        }

        $output['images'] = $imageUrls;

        return $output;
    }

    public function savePdfEdits(Request $request, $id)
    {
        $document = ToolkitDocument::findOrFail($id);

        $edit = ToolkitDocumentEdit::create([
            'toolkit_document_id' => $document->id,
            'user_id' => auth()->id(),
            'edited_file_path' => $document->file_path,
            'file_type' => 'pdf',
            'content' => json_encode($request->input('content')),
        ]);

        return response()->json(['success' => true]);
    }










    // protected function extractPdfText($pdfPath)
    // {
    //     $output = [
    //         'text' => '',
    //         'images' => [],
    //     ];

    //     $baseName = pathinfo($pdfPath, PATHINFO_FILENAME) . '_' . time();
    //     $dir = 'uploads/pdf_output';

    //     // Extract text
    //     $textPath = $dir . '/' . $baseName . '.txt';
    //     exec("pdftotext \"$pdfPath\" \"$textPath\"");
    //     if (file_exists($textPath)) {
    //         $output['text'] = file_get_contents($textPath);
    //     }

    //     // Extract images
    //     $imgBase = $dir . '/' . $baseName;
    //     exec("pdfimages -j \"$pdfPath\" \"$imgBase\"");

    //     $imageFiles = glob($imgBase . '*.{jpg,jpeg,ppm,pbm}', GLOB_BRACE);
    //     $imageUrls = [];

    //     foreach ($imageFiles as $file) {
    //         $filename = pathinfo($file, PATHINFO_FILENAME);
    //         $extension = pathinfo($file, PATHINFO_EXTENSION);
    //         $fileNameToStore = $filename . '_' . time() . '.' . $extension;
    //         $dir = 'uploads/pdf-images';

    //         $upload = Utility::upload_file($file, 'file', $fileNameToStore, $dir, []);
    //         if (isset($upload['flag']) && $upload['flag'] == 1) {
    //             $imageUrls[] = Utility::get_file(asset('uploads/pdf-images' . $upload['url']));
    //         }
    //     }

    //     $output['images'] = $imageFiles;
    //     return $output;
    // }




    // protected function extractPdfText($pdfPath)
    // {
    //     try {
    //         $parser = new Parser();
    //         $pdf = $parser->parseFile($pdfPath);
    //         $text = $pdf->getText();
    //         if (!empty(trim($text))) {
    //             return $text;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('PDF parsing error: ' . $e->getMessage());
    //     }

    //     $outputDir = storage_path('app/public/pdf-html-preview');
    //     if (!is_dir($outputDir)) {
    //         mkdir($outputDir, 0755, true);
    //     }

    //     $baseDir = dirname($pdfPath);
    //     $baseName = basename($pdfPath);

    //     if (preg_match('/[^A-Za-z0-9_\.\-]/', $baseName)) {
    //         $safeName = preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $baseName);
    //         $safePath = $baseDir . DIRECTORY_SEPARATOR . $safeName;
    //         $pdfPath = $safePath;
    //         $baseName = basename($safePath);
    //     }

    //     $dockerVolumePath = str_replace('\\', '/', $baseDir);

    //     $dockerCommand = sprintf(
    //         'docker run --rm -v "%s":/pdf sergiomtzlosa/pdf2htmlex pdf2htmlEX "/pdf/%s" --dest-dir /pdf --outfile output.html',
    //         $dockerVolumePath,
    //         $baseName
    //     );

    //     exec($dockerCommand . ' 2>&1', $output, $status);

    //     Log::info("Docker command: $dockerCommand");
    //     Log::info("Docker output:\n" . implode("\n", $output));

    //     if ($status === 0 && file_exists($baseDir . '/output.html')) {
    //         return file_get_contents($baseDir . '/output.html');
    //     } else {
    //         Log::error('Docker PDF conversion failed: ' . implode("\n", $output));
    //         return 'PDF to HTML conversion failed.';
    //     }
    // }



    public function saveContent(Request $request, $encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        if (!$document->is_editable) {
            return redirect()->back()->with('error', 'Document is not editable.');
        }

        $originalPath = public_path('storage/uploads/toolkit-documents/' . $document->file_path);
        $extension = strtolower($document->file_type);

        if (!file_exists($originalPath)) {
            return redirect()->back()->with('error', 'Original document not found.');
        }

        try {
            if ($extension === 'pdf') {
                $this->generateNewPdfWithEditedText($originalPath, $request->content, $originalPath); // Overwrite
            } elseif ($extension === 'docx') {
                $this->updateDocxText($originalPath, $request->content);
            } elseif (in_array($extension, ['html', 'htm', 'txt'])) {
                file_put_contents($originalPath, $request->content);
            } else {
                file_put_contents($originalPath, $request->content);
            }

            ToolkitDocumentEdit::create([
                'toolkit_document_id' => $document->id,
                'user_id' => Auth::id(),
                'edited_file_path' => $document->file_path, // Overwrite existing file
                'file_type' => $extension,
                'content' => $request->content,
            ]);

            return redirect()->route('toolkit_documents.index')->with('success', 'Document saved successfully.');
        } catch (\Exception $e) {
            Log::error('Document saving error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save document.');
        }
    }
    private function generateNewPdfWithEditedText($originalPath, $newText, $outputPath)
    {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($originalPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($pageNo === 1) {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(255, 0, 0); // Red for edited text overlay
                $pdf->SetXY(10, $size['height'] - 60);
                $pdf->MultiCell(0, 5, $newText);
            }
        }

        $pdf->Output($outputPath, 'F');
    }
    private function updateDocxText($filename, $content)
    {
        $zip = new ZipArchive();
        if ($zip->open($filename) === true) {
            $xml = $zip->getFromName('word/document.xml');

            $xml = preg_replace('/<w:t[^>]*>.*?<\/w:t>/s', '<w:t>' . htmlspecialchars($content) . '</w:t>', $xml);

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();
        } else {
            throw new \Exception('Unable to open DOCX file for editing.');
        }
    }

    public function history($encrypted_id)
    {
        $id = Crypt::decrypt($encrypted_id);
        $document = ToolkitDocument::findOrFail($id);

        $edits = ToolkitDocumentEdit::where('toolkit_document_id', $id)->latest()->get();

        return view('toolkit_documents.history', compact('document', 'edits'));
    }
}
