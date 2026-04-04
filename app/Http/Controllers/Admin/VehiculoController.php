<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    use ApiConsumer;

    private array $tiposVehiculo    = ['Sedán','Pick-up','SUV','Van','Camión','Moto','Bus'];
    private array $tiposCombustible = ['Gasolina','Diesel','Eléctrico','Híbrido','GLP'];

    public function index(Request $request)
    {
        $statusFiltro = $request->query('status', '');
        $params       = ['page' => $request->query('page', 1)];
        if ($statusFiltro) $params['status'] = $statusFiltro;

        $response = $this->apiGet('vehicles', $params);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar la lista de vehículos.');
        }

        $paginado = $response->json('data');

        return view('admin.vehiculos.index', [
            'vehiculos'    => $paginado['data'] ?? [],
            'paginado'     => [
                'current_page' => $paginado['current_page'],
                'last_page'    => $paginado['last_page'],
                'total'        => $paginado['total'],
            ],
            'statusFiltro' => $statusFiltro,
            'statuses'     => Vehicle::STATUSES,
        ]);
    }

    public function create()
    {
        return view('admin.vehiculos.create', [
            'tiposVehiculo'    => $this->tiposVehiculo,
            'tiposCombustible' => $this->tiposCombustible,
            'statuses'         => Vehicle::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate'        => 'required|string|max:20',
            'brand'        => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_type' => 'required|string|max:50',
            'capacity'     => 'required|integer|min:1',
            'fuel_type'    => 'required|string|max:30',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'plate.required'        => 'La placa es obligatoria.',
            'brand.required'        => 'La marca es obligatoria.',
            'model.required'        => 'El modelo es obligatorio.',
            'year.required'         => 'El año es obligatorio.',
            'vehicle_type.required' => 'El tipo de vehículo es obligatorio.',
            'capacity.required'     => 'La capacidad es obligatoria.',
            'fuel_type.required'    => 'El tipo de combustible es obligatorio.',
        ]);

        $http = $this->api();

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $response = $http
                ->attach('image',        file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->attach('plate',        strtoupper($request->plate))
                ->attach('brand',        $request->brand)
                ->attach('model',        $request->model)
                ->attach('year',         (string) $request->year)
                ->attach('vehicle_type', $request->vehicle_type)
                ->attach('capacity',     (string) $request->capacity)
                ->attach('fuel_type',    $request->fuel_type)
                ->attach('status',       $request->status ?? 'available')
                ->attach('mileage',      (string) ($request->mileage ?? 0))
                ->post("{$this->apiBase}/vehicles");
        } else {
            $response = $http->post("{$this->apiBase}/vehicles", [
                'plate'        => strtoupper($request->plate),
                'brand'        => $request->brand,
                'model'        => $request->model,
                'year'         => (int) $request->year,
                'vehicle_type' => $request->vehicle_type,
                'capacity'     => (int) $request->capacity,
                'fuel_type'    => $request->fuel_type,
                'status'       => $request->status ?? 'available',
                'mileage'      => (int) ($request->mileage ?? 0),
            ]);
        }

        if ($response->failed()) {
            return $this->handleError($response, 'Error al registrar el vehículo.');
        }

        return redirect()->route('admin.vehiculos')->with('success', 'Vehículo registrado exitosamente.');
    }

    public function edit(int $id)
    {
        $response = $this->apiGet("vehicles/{$id}");

        if ($response->failed()) {
            return redirect()->route('admin.vehiculos')->with('error', 'Vehículo no encontrado.');
        }

        return view('admin.vehiculos.edit', [
            'vehiculo'         => $response->json('data'),
            'tiposVehiculo'    => $this->tiposVehiculo,
            'tiposCombustible' => $this->tiposCombustible,
            'statuses'         => Vehicle::STATUSES,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'plate'        => 'required|string|max:20',
            'brand'        => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_type' => 'required|string|max:50',
            'capacity'     => 'required|integer|min:1',
            'fuel_type'    => 'required|string|max:30',
            'status'       => 'required|string',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $http = $this->api();

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $response = $http
                ->attach('_method',      'PUT')
                ->attach('image',        file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->attach('plate',        strtoupper($request->plate))
                ->attach('brand',        $request->brand)
                ->attach('model',        $request->model)
                ->attach('year',         (string) $request->year)
                ->attach('vehicle_type', $request->vehicle_type)
                ->attach('capacity',     (string) $request->capacity)
                ->attach('fuel_type',    $request->fuel_type)
                ->attach('status',       $request->status)
                ->post("{$this->apiBase}/vehicles/{$id}");
        } else {
            $response = $http->put("{$this->apiBase}/vehicles/{$id}", [
                'plate'        => strtoupper($request->plate),
                'brand'        => $request->brand,
                'model'        => $request->model,
                'year'         => (int) $request->year,
                'vehicle_type' => $request->vehicle_type,
                'capacity'     => (int) $request->capacity,
                'fuel_type'    => $request->fuel_type,
                'status'       => $request->status,
            ]);
        }

        if ($response->failed()) {
            return $this->handleError($response, 'Error al actualizar el vehículo.');
        }

        return redirect()->route('admin.vehiculos')->with('success', 'Vehículo actualizado exitosamente.');
    }

    public function destroy(int $id)
    {
        $response = $this->apiDelete("vehicles/{$id}");

        if ($response->status() === 422) {
            return back()->with('error', $response->json('message') ?? 'No se puede eliminar.');
        }

        if ($response->failed()) {
            return back()->with('error', 'Error al eliminar el vehículo.');
        }

        return redirect()->route('admin.vehiculos')->with('success', 'Vehículo eliminado correctamente.');
    }
}