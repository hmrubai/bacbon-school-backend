<?php
namespace App\Http\Controllers;

use Maatwebsite\Excel\Concerns\FromArray;

class ExportPaidCourseTest implements FromArray
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }
}
