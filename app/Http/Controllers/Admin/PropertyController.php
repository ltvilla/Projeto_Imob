<?php

namespace TemplateInicial\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use TemplateInicial\Facility;
use TemplateInicial\Http\Controllers\Controller;
use TemplateInicial\Http\Requests\Admin\Property as PropertyRequest;
use TemplateInicial\Property;
use TemplateInicial\PropertyImage;
use TemplateInicial\Support\Cropper;
use TemplateInicial\User;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $properties = Property::orderBy('id', 'DESC')->get();
        return view('admin.properties.index', [
            'properties' => $properties
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $facilities = Facility::orderBy('description_translate')->get();
        return view('admin.properties.create', [
            'facilities' => $facilities,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PropertyRequest $request)
    {
        $facilities = Facility::all();
        $createProperty = Property::create($request->all());

        $createProperty->setSlug();

        $arrayNewFacilities = array();

        foreach($facilities as $fac) {
            if(array_key_exists($fac->description, $request->all())) {
                $arrayNewFacilities[] = $fac->id;
            }
        }

        $createProperty->facilities()->attach($arrayNewFacilities);

        $validator = Validator::make($request->only('files'), ['files.*' => 'image']);

        if($validator->fails() === true) {
            return redirect()->back()->withInput()->with(['color' => 'orange', 'message' => 'Todas as imagens devem ser do tipo jpg, jpeg ou png']);
        }

        if($request->allFiles()) {
            foreach($request->allFiles()['files'] as $image) {
                $propertyImage = new PropertyImage();
                $propertyImage->property = $createProperty->id;
                $propertyImage->path = $image->storeAs('properties/' . $createProperty->id, str_slug($request->title) . '-' . str_replace('.', '', microtime(true)) . '.' . $image->extension());
                $propertyImage->save();
                unset($propertyImage);
            }
        }

        return redirect()->route('admin.properties.edit', [
            'property' => $createProperty->id
        ])->with([
            'color' => 'green',
            'message' => 'Imóvel cadastrado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $facilities = Facility::orderBy('description_translate')->get();
        $property = Property::where('id', $id)->first();
        $propertyFacilities =  $property->facilities()->where('propertyId', $property->id)->get();
        $users = User::all();

        return view('admin.properties.edit', [
            'property' => $property,
            'facilities' => $facilities,
            'propertyFacilities' => $propertyFacilities,
            'users' => $users
        ]);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PropertyRequest $request, $id)
    {
        $facilities = Facility::all();
        $property = Property::where('id', $id)->first();
        $property->fill($request->all());

        $arrayNewFacilities =  array();

        foreach($facilities as $fac) {
            if(array_key_exists($fac->description, $request->all())) {
                $arrayNewFacilities[] = $fac->id;
            }
        }
        $property->facilities()->sync($arrayNewFacilities);

        $property->save();
        $property->setSlug();

        $validator = Validator::make($request->only('files'), ['files.*' => 'image']);

        if($validator->fails() === true) {
            return redirect()->back()->withInput()->with(['color' => 'orange', 'message' => 'Todas as imagens devem ser do tipo jpg, jpeg ou png']);
        }

        if($request->allFiles()) {
            foreach($request->allFiles()['files'] as $image) {
                $propertyImage = new PropertyImage();
                $propertyImage->property = $property->id;
                $propertyImage->path = $image->storeAs('properties/' . $property->id, str_slug($request->title) . '-' . str_replace('.', '', microtime(true)) . '.' . $image->extension());
                $propertyImage->save();
                unset($propertyImage);
            }
        }

        return redirect()->route('admin.properties.edit', [
            'property' => $property->id
        ])->with([
            'color' => 'green',
            'message' => 'Imóvel atualizado com sucesso!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function imageSetCover(Request $request)
    {
        $imageSetCover = PropertyImage::where('id', $request->image)->first();
        $allImages = PropertyImage::where('property', $imageSetCover->property)->get();

        foreach($allImages as $image) {
            $image->cover = null;
            $image->save();
        }

        $imageSetCover->cover = true;
        $imageSetCover->save();

        $json = [
            'success' => true
        ];

        return response()->json($json);
    }

    public function imageRemove(Request $request)
    {
        $imageDelete = PropertyImage::where('id', $request->image)->first();
        
        Storage::delete($imageDelete->path);
        Cropper::flush($imageDelete->path);
        $imageDelete->delete();

        $json = [
            'success' => true
        ];

        return response()->json($json);
    }
}
