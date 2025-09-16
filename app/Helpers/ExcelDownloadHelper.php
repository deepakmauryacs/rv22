<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (! function_exists('download_xlsx_from_view')) {
    function download_xlsx_from_view(string $view, array $data = [], ?string $filename = null)
    {

        /***:- method 1  -:***/
        /*$filename = $filename . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-disposition: attachment; filename=$filename");
            echo view('buyer.rfq.cis.cis-export', $data);
            exit;*/

        /***:- method 2  -:***/
        // return Excel::download(new CisExport($data), $filename. ".xlsx");

        /***:- method 3  -:***/
        $filename = $filename ?: time();
        $oldName  = $filename . '.xls';
        $newName  = $filename . '.xlsx';

        $exportsDir = public_path('uploads');
        if (!is_dir($exportsDir)) {
            mkdir($exportsDir, 0777, true);
        }

        $oldPath = $exportsDir . DIRECTORY_SEPARATOR . $oldName;
        $newPath = $exportsDir . DIRECTORY_SEPARATOR . $newName;


        /***:- Render Blade → save as .xls  -:***/
        $html = view($view, $data)->render();
        file_put_contents($oldPath, $html);

        try {
            $spreadsheet = IOFactory::load($oldPath);
            $sheet       = $spreadsheet->getActiveSheet();


            /***:- Safe sheet title (31-char limit)  -:***/
            $safeTitle = preg_replace('/[\\\\:\\/?*\\[\\]]/', '', mb_substr($filename, 0, 31));
            $sheet->setTitle($safeTitle);


            /***:- Auto-size all columns with data  -:***/
            $highestColLetter = $sheet->getHighestColumn();
            $highestColIndex  = Coordinate::columnIndexFromString($highestColLetter);

            for ($col = 1; $col <= $highestColIndex; $col++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))
                    ->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save($newPath);
            unlink($oldPath);
        } catch (\Exception $e) {
            throw new \RuntimeException("Conversion failed: {$e->getMessage()}");
        }

        return response()->download($newPath, $newName)->deleteFileAfterSend(true);
    }
}