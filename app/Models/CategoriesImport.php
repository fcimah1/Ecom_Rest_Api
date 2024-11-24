<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Storage;

class CategoriesImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;

    public function collection(Collection $rows)
    {
        $canImport = true;
        if (addon_is_activated('seller_subscription')) {
            if (Auth::user()->user_type == 'seller' && count($rows) > Auth::user()->seller->remaining_uploads) {
                $canImport = false;
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
            }
        }
        $data = array();
        if ($canImport) {
            foreach ($rows as $row) {


                $en = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $row['name']));
                $ar = $this->slug($row['name_ar']);
                $x = $en . '-' . $ar;

                //                $category = Category::create([
//                    'name'              => $row['name'],
//                    'order_level'       =>($row['order_level']!=null)? $row['order_level']:0,
//                    'digital'           => 0,
////                    'banner'            => $row['banner'],
////                    'icon'              => $row['icon'],
//                    'meta_title'        =>$x ,
//                    'meta_description'  =>$x,
//                    'parent_id'         => ($row['parent_id'] != 0)?$row['parent_id']:0,
//                    'level'             => ($row['parent_id'] != 0)?$this->par($row['parent_id']):0,
//                    'slug'              => $x,
//
//
//                ]);
//
//                $category_translation = CategoryTranslation::firstOrNew(['lang' => 'eg', 'category_id' => $category->id]);
//                $category_translation->name = $row['name_ar'];
//                $category_translation->save();



                $data[] = [
                    'name' => $row['name'],
                    'order_level' => ($row['order_level'] != null) ? $row['order_level'] : 0,
                    'digital' => 0,
                    'meta_title' => $x,
                    'meta_description' => $x,
                    'parent_id' => ($row['parent_id'] != 0) ? $row['parent_id'] : 0,
                    'level' => ($row['parent_id'] != 0) ? $this->par($row['parent_id']) : 0,
                    'slug' => $x,
                ];


            }

            dd($data);
            flash(translate('Category imported successfully'))->success();
        }


    }

    public function par($id)
    {

        $parent = Category::find($id);
        return $parent->level + 1;

    }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            }
        ];
    }

    public function downloadThumbnail($url)
    {
        try {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $filename = 'uploads/all/' . Str::random(5) . '.' . $extension;
            $fullpath = 'public/' . $filename;
            $file = file_get_contents($url);
            file_put_contents($fullpath, $file);

            $upload = new Upload;
            $upload->extension = strtolower($extension);

            $upload->file_original_name = $filename;
            $upload->file_name = $filename;
            $upload->user_id = Auth::user()->id;
            $upload->type = "image";
            $upload->file_size = filesize(base_path($fullpath));
            $upload->save();

            if (env('FILESYSTEM_DRIVER') == 's3') {
                $s3 = Storage::disk('s3');
                $s3->put($filename, file_get_contents(base_path($fullpath)));
                unlink(base_path($fullpath));
            }

            return $upload->id;
        } catch (\Exception $e) {
            //dd($e);
        }
        return null;
    }


    public function slug($string, $separator = '-')
    {
        if (is_null($string)) {
            return "";
        }

        $string = trim($string);

        $string = mb_strtolower($string, "UTF-8");
        ;

        $string = preg_replace("/[^a-z0-9_\sءاأإآؤئبتثجحخدذرزسشصضطظعغفقكلمنهويةى]#u/", "", $string);

        $string = preg_replace("/[\s-]+/", " ", $string);

        $string = preg_replace("/[\s_]/", $separator, $string);

        return $string;
    }
}
