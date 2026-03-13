<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * Display a paginated list of routes.
     */
    public function index(): JsonResponse
    {
        $routes = Route::query()->latest()->paginate(10);

        return response()->json([
            'message' => 'Routes retrieved successfully.',
            'data' => $routes
        ]);
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->only([
            'name',
            'origin',
            'destination',
            'distance_km',
            'estimated_minutes',
            'description'
        ]);

        $route = Route::create($data);

        return response()->json([
            'message' => 'Route created successfully.',
            'data' => $route
        ], 201);
    }

    /**
     * Display the specified route.
     */
    public function show(Route $route): JsonResponse
    {
        return response()->json([
            'message' => 'Route retrieved successfully.',
            'data' => $route
        ]);
    }

    /**
     * Update the specified route.
     */
    public function update(Request $request, Route $route): JsonResponse
    {
        $data = $request->only([
            'name',
            'origin',
            'destination',
            'distance_km',
            'estimated_minutes',
            'description'
        ]);

        $route->update($data);

        return response()->json([
            'message' => 'Route updated successfully.',
            'data' => $route
        ]);
    }

    /**
     * Soft-delete the specified route.
     */
    public function destroy(Route $route): JsonResponse
    {
        $route->delete();

        return response()->json([
            'message' => 'Route deleted successfully.',
            'data' => null
        ]);
    }
}