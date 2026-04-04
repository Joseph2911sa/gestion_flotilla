<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

trait ApiConsumer
{
    private string $apiBase = 'http://127.0.0.1:8000/api/v1';

    private function api(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken(session('api_token', ''))
            ->acceptJson();
    }

    private function apiGet(string $endpoint, array $params = []): Response
    {
        return $this->api()->get("{$this->apiBase}/{$endpoint}", $params);
    }

    private function apiPost(string $endpoint, array $data = []): Response
    {
        return $this->api()->post("{$this->apiBase}/{$endpoint}", $data);
    }

    private function apiPut(string $endpoint, array $data = []): Response
    {
        return $this->api()->put("{$this->apiBase}/{$endpoint}", $data);
    }

    private function apiPatch(string $endpoint, array $data = []): Response
    {
        return $this->api()->patch("{$this->apiBase}/{$endpoint}", $data);
    }

    private function apiDelete(string $endpoint): Response
    {
        return $this->api()->delete("{$this->apiBase}/{$endpoint}");
    }

    private function handleError(Response $response, string $defaultMsg = 'Error en la operación.'): mixed
    {
        if ($response->status() === 422) {
            return back()->withInput()
                ->withErrors($response->json('errors') ?? [])
                ->with('error', $response->json('message') ?? $defaultMsg);
        }
        return back()->withInput()->with('error', $defaultMsg);
    }
}