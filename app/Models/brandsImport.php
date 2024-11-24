<?php

namespace App\Models;


use App\Models\Brand;
use App\Models\BrandTranslation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Storage;

class brandsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;

    public function collection(Collection $rows) {
        $canImport = true;
//        if (addon_is_activated('seller_subscription')){
//            if(Auth::user()->user_type == 'seller' && count($rows) > Auth::user()->seller->remaining_uploads) {
//                $canImport = false;
//                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
//            }
//        }

        if($canImport) {
            foreach ($rows as $row) {


                $en = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $row['name']));
                $ar =  $this->slug($row['name_ar']);
                $x = $en.'-'.$ar;

                $brand = Brand::create([
                    'name'              => $row['name'],
                    'meta_title'        =>$x ,
                    'meta_description'  =>$x,
                    'slug'              => $x,

                ]);

                $category_translation = BrandTranslation::firstOrNew(['lang' => 'eg', 'brand_id' => $brand->id]);
                $category_translation->name = $row['name_ar'];
                $category_translation->save();

            }

            flash(translate('brand imported successfully'))->success();
        }


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

        ];
    }


    public function slug($string, $separator = '-') {
        if (is_null($string)) {
            return "";
        }

        $string = trim($string);

        $string = mb_strtolower($string, "UTF-8");;

        $string = preg_replace("/[^a-z0-9_\sءاأإآؤئبتثجحخدذرزسشصضطظعغفقكلمنهويةى]#u/", "", $string);

        $string = preg_replace("/[\s-]+/", " ", $string);

        $string = preg_replace("/[\s_]/", $separator, $string);

        return $string;
    }
}
