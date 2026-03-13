<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class GenericViewExport implements FromView, WithTitle
{
    protected string $view;
    protected array $data;
    protected string $title;

    public function __construct(string $view, array $data, string $title = 'Report')
    {
        $this->view = $view;
        $this->data = $data;
        $this->title = $this->sanitizeTitle($title);
    }

    private function sanitizeTitle(string $title): string
    {
        $title = preg_replace('/[\/\\\\\?\*\[\]\:]/', '', $title);
        return mb_substr($title, 0, 31);
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}
