<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransferenciaService;
use App\Http\Requests\TransferenciaRequest;
use App\Models\User;
use Inertia\Inertia;

class TransacaoController extends Controller
{
    public function __construct(
        private TransferenciaService $transferenciaService
    ) {}

    public function create()
    {
        return Inertia::render('Transacao/Create');
    }

    public function getLastsTransfers()
    {
        return $this->transferenciaService->getLastsTransfers();
    }

    public function getAllTransfers(Request $request)
    {
        return $this->transferenciaService->getAllTransfers($request->all());
    }

}
