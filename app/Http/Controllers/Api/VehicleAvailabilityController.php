<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleAvailabilityController extends Controller
{
    public function checkAvailability(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'departure_date' => ['required', 'date'],
            'return_date'    => ['required', 'date', 'after:departure_date'],
        ], [
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'departure_date.date'     => 'La fecha de salida no tiene un formato válido.',
            'return_date.required'    => 'La fecha de retorno es obligatoria.',
            'return_date.date'        => 'La fecha de retorno no tiene un formato válido.',
            'return_date.after'       => 'La fecha de retorno debe ser posterior a la fecha de salida.',
        ]);

        $vehicle = Vehicle::query()
            ->where('id', $id)
            ->first();

        if (! $vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo no existe.',
                'data'    => null,
            ], 404);
        }

        $result = DB::selectOne(
            'SELECT fn_vehicle_available(?, ?, ?) AS available',
            [
                $id,
                $validated['departure_date'],
                $validated['return_date'],
            ]
        );

        $isAvailable = (bool) $result->available;

        return response()->json([
            'success' => true,
            'message' => $isAvailable
                ? 'El vehículo está disponible para el rango de fechas indicado.'
                : 'El vehículo no está disponible para el rango de fechas indicado.',
            'data' => [
                'vehicle_id'     => $vehicle->id,
                'plate'          => $vehicle->plate,
                'brand'          => $vehicle->brand,
                'model'          => $vehicle->model,
                'status'         => $vehicle->status,
                'available'      => $isAvailable,
                'departure_date' => $validated['departure_date'],
                'return_date'    => $validated['return_date'],
            ],
        ]);
    }

    public function approveViaDb(Request $request, int $id): JsonResponse
    {
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'data'    => null,
            ], 401);
        }

        try {
            DB::statement(
                'CALL sp_approve_trip_request(?, ?)',
                [
                    $id,
                    $request->user()->id,
                ]
            );
        } catch (QueryException $e) {
            $rawMessage   = $e->getMessage();
            $cleanMessage = $this->extractPgMessage($rawMessage);

            return response()->json([
                'success' => false,
                'message' => $cleanMessage,
                'data'    => null,
            ], 422);
        }

        $tripRequest = TripRequest::with([
            'user:id,name,email',
            'vehicle:id,plate,brand,model,status',
            'route:id,name,origin,destination',
            'reviewer:id,name',
        ])->find($id);

        if (! $tripRequest) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud no fue encontrada después de la aprobación.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada correctamente mediante procedimiento almacenado.',
            'data'    => $tripRequest,
        ]);
    }

    private function extractPgMessage(string $rawMessage): string
    {
        if (preg_match('/ERROR:\s+(.+?)(?:\n|$)/s', $rawMessage, $matches)) {
            return trim($matches[1]);
        }

        return 'No se pudo completar la aprobación. Verifique el estado de la solicitud y la disponibilidad del vehículo.';
    }
}