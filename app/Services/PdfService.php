<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    protected string $view;
    protected array $data = [];
    protected string $fileName = "document.pdf";
    protected string $paper = "A4";
    protected string $orientation = "portrait";

    public function make(string $view, array $data): self
    {
        $this->view = $view;
        $this->data = $data;
        return $this;
    }

    public function fileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function paper(string $paper, string $orientation = "portrait"): self
    {
        $this->paper = $paper;
        $this->orientation = $orientation;
        return $this;
    }

    protected function prepare()
    {
        $this->data["numPagesTotal"] = 0;

        $pdf = Pdf::loadView($this->view, $this->data)->setPaper(
            $this->paper,
            $this->orientation,
        );

        $pdf->render();

        $this->data["numPagesTotal"] = $pdf->getCanvas()->get_page_count();

        return Pdf::loadView($this->view, $this->data)->setPaper(
            $this->paper,
            $this->orientation,
        );
    }

    public function stream(bool $attachment = false)
    {
        $pdf = $this->prepare();

        return $pdf->stream($this->fileName, [
            "attachment" => $attachment,
        ]);
    }

    public function download()
    {
        $pdf = $this->prepare();
        return $pdf->download($this->fileName);
    }
}
