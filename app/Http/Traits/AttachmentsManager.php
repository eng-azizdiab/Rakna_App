<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

trait AttachmentsManager
{

//    public abstract function getFolderName(): string;

    protected function directory()
    {
        return 'assets/uploads/' . $this->getFolderName() . '/';
    }


    function getAttachment($image = null)
    {

        if (Storage::disk('public')->exists($this->directory() . $image)) {
            return url("storage/" . $this->directory() . $image);
            /* return Storage::disk('public')->url($this->directory() . $image); */
        } else {

            return null;
        }
    }

    private function upload($format,  $image = null)
    {

        if ($image != null) {
            $format = $image?->extension() ?? null;


            $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($this->directory())) {
                Storage::disk('public')->makeDirectory($this->directory());
            }
            Storage::disk('public')->put($this->directory() . $imageName, file_get_contents($image));
//            debug($this->directory() . $imageName);
        } else {
            $imageName = null;
        }

        return $imageName;
    }
    public  function uploadAttachment(
        $image = null,
        $old_image = null,
    ) {
        if (!empty($old_image) && Storage::disk('public')->exists($this->directory() . $old_image)) {
            Storage::disk('public')->delete($this->directory() . $old_image);
        }
        $format = $image->extension() ?? null;
        $imageName = $this->upload($format, $image);
        return $imageName;
    }
}
