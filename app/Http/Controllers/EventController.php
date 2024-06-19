<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate(Event::$rules);

        return Event::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Event::where('id',$id)->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::where('id',$id)->first();

        $rules = [
            'evento' => [
                'required',
                Rule::unique('events')->ignore($event->id)->where(function ($query) use ($request, $event) {
                    return $query->where('evento', $request->evento)
                                 ->where('id', '!=', $event->id);
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return $event;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Event::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
