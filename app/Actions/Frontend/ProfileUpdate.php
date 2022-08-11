<?php

namespace App\Actions\Frontend;

use App\Actions\File\FileDelete;
use App\Actions\File\FileUpload;

class ProfileUpdate
{
    public static function update($request, $customer)
    {
        $customer->update($request->except('image'));

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $url = $request->image->move('uploads/customer',$request->image->hashName());
            $customer->update(['image' => $url]);
        }

        return $customer;
    }
}
