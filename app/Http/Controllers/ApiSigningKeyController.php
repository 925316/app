<?php

namespace App\Http\Controllers;

use App\Http\Requests\RotateApiSigningKeyRequest;
use App\Models\ApiSigningKey;
use App\Services\ApiSigningKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiSigningKeyController extends Controller
{
    public function __construct(private readonly ApiSigningKeyService $apiSigningKeyService) {}

    public function index(): View
    {
        return view('api-signing-keys.index', [
            'keys' => ApiSigningKey::query()->with('creator')->orderedForAdmin()->paginate(20),
            'activeKey' => $this->apiSigningKeyService->activeKey(),
            'configKeyId' => (string) config('services.api_signing.key_id', 'main-2026-01'),
            'configPrivateKeyPath' => (string) config('services.api_signing.private_key_path', ''),
        ]);
    }

    public function rotate(RotateApiSigningKeyRequest $request): RedirectResponse
    {
        $key = $this->apiSigningKeyService->rotate($request->user());

        return back()->with('success', "Activated new API signing key {$key->key_id}.");
    }

    public function activate(Request $request, ApiSigningKey $apiSigningKey): RedirectResponse
    {
        $this->apiSigningKeyService->activate($apiSigningKey, $request->user());

        return back()->with('success', "Activated API signing key {$apiSigningKey->key_id}.");
    }
}
