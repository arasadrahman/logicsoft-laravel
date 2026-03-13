<?php

namespace App\Services;

use App\Exports\GenericViewExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelService
{
    public function export($view, string $fileName, string $sheetTitle)
    {
        $sheetTitle = $this->sanitizeTitle($sheetTitle);

        return Excel::download(
            new GenericViewExport(
                $view->getName(),
                $view->getData(),
                $sheetTitle
            ),
            $fileName
        );
    }

    private function sanitizeTitle(string $title): string
    {
        $title = preg_replace('/[\/\\\\\?\*\[\]\:]/', '', $title);
        return mb_substr($title, 0, 31);
    }
}
