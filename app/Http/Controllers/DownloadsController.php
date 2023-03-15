<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\UserStatus;
use App\Models\UsersInfos;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\PdF;
use Illuminate\Support\Facades\Storage;

class DownloadsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getUsersList']]);
    }

    public function downloadEstiamte($fileName)
    {
        $file_path = public_path('resources/devis/' . $fileName);
        $headers = ['Content-Type: application/pdf'];
        return response()->download($file_path, $fileName, $headers);
    }

    public function downloadInvoice($fileName)
    {
        $file_path = public_path('resources/factures/' . $fileName);
        $headers = ['Content-Type: application/pdf'];
        return response()->download($file_path, $fileName, $headers);
    }

}
