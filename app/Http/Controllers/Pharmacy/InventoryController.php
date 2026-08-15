<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\FilterInventoryRequest;
use App\Models\StockLocation;
use App\Queries\Pharmacy\InventoryBatchQuery;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __invoke(FilterInventoryRequest $request, InventoryBatchQuery $inventory): View
    {
        $filters = $request->safe()->only(['search', 'condition', 'location']);
        $batches = $inventory->paginate($filters);
        $locations = StockLocation::query()
            ->whereHas('batches', fn ($query) => $query->where('status', '!=', 'entered_in_error'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.pharmacy.inventory.index', compact('batches', 'filters', 'locations'));
    }
}
