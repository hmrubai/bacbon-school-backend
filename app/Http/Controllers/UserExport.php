<?php
namespace App\Http\Controllers;

use Maatwebsite\Excel\Concerns\FromArray;

class UserExport implements FromArray
{
    protected $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function array(): array
    {
        return $this->user;
    }
}
