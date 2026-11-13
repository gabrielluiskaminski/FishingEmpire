<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return AddressResource::collection($request->user()->addresses()->latest('id')->get());
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $request->user()->addresses()->create($request->validated());
        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function update(UpdateAddressRequest $request, int $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->update($request->validated());
        return new AddressResource($address);
    }

    public function destroy(Request $request, int $id)
    {
        $request->user()->addresses()->findOrFail($id)->delete();
        return response()->noContent();
    }
}
