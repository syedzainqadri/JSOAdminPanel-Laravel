<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\CustomField\Entities\CustomField;
use Modules\CustomField\Entities\CustomFieldGroup;
use Validator;

class CustomFieldController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'group_id' => 'required|numeric',
            'required' => 'required|numeric',
            'filterable' => 'required|numeric',
            'listable' => 'required|numeric',
            'icon' => 'required',
            'type' => [
                'required',
                Rule::in(['text', 'textarea', 'select', 'radio', 'file', 'url', 'number', 'date', 'checkbox', 'checkbox_multiple'])
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }

        $check_custom_field_group = CustomFieldGroup::find($request->group_id);
        if (!$check_custom_field_group) {
            return response()->json('Custom Field Group ID is not found');            
        }

        $custom_field = CustomField::create([
            'name' => $request->name,
            'slug' => str_slug($request->name),
            'custom_field_group_id' => $request->group_id,
            'required' => $request->required,
            'filterable' => $request->filterable,
            'listable' => $request->listable,
            'icon' => $request->icon,
            'type' => $request->type,
        ]);

        return $custom_field;
    }
}
