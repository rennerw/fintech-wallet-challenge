<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TransferenciaRequest;
use App\Services\TransferenciaService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TransferenciaController extends Controller
{

    public function __construct(
        private TransferenciaService $transferenciaService
    ) {}

    public function store(TransferenciaRequest $request)
    {
        try{
            $toUser = User::where('email', $request->para_user)->firstOrFail();
        }
        catch (\Exception $e) {
            Log::error('Erro ao encontrar destinatário', [
                'email' => $request->para_user,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao encontrar destinatário. Verifique o e-mail e tente novamente.',
            ], 422);
        }

        $result = $this->transferenciaService->transfer(
            $request->user(),
            $toUser,
            (float) $request->valor
        );

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 422);
        
    }
}
