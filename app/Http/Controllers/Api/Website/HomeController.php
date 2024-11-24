<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function limitSliders()
    {
        try {
            $sliders = Slider::orderBy('created_at', 'desc')->limit(3)->get();
            if ($sliders) {
                $updatedSliders = $this->transformSliders($sliders);
                return response()->json([
                    'status' => true,
                    'message' => 'Sliders found',
                    'data' => $updatedSliders
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No sliders found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function transformSliders($sliders): mixed
    {
        return $sliders->map(function ($slider) {
            // Get slider image
            if (!$slider['photo'])
                $slider['photo'] = null;
            else{
                $slider['photo'] = asset("/".$slider['photo']);
            }
            return $slider;
        })->toArray();
    }

    public function aboutUs()
    {
        try {
            $about = Page::where('slug', 'like', '%about%')->first();
            if ($about) {
                return response()->json([
                    'status' => true,
                    'message' => 'About us found',
                    'data' => $about
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No about us found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function faq()
    {
        try {
            $locale = app()->getLocale();
            $questionsInAR = [
                [
                    'id' => 1,
                    'question' => 'هل تقدمون ضمانًا على منتجاتكم؟',
                    'answer' => 'نعم، نقدم ضمانًا لمدة عامين على جميع منتجاتنا.'
                ],
                [
                    'id' => 2,
                    'question' => 'ما سياسة الإرجاع لديكم؟',
                    'answer' => 'نقدم سياسة إرجاع لمدة 30 يومًا لجميع المنتجات. إذا كنت غير راضٍ عن مشترياتك، يمكنك إرجاعها خلال 30 يومًا من استلامك لها.'
                ],
                [
                    'id' => 3,
                    'question' => 'ما هي سياسة الشحن لديكم؟',
                    'answer' => 'نقدم شحن مجاني لجميع الطلبات في المملكة العربية السعودية.'
                ],
                [
                    'id' => 4,
                    'question' => 'هل يمكنني الدفع باستخدام بطاقات الائتمان؟',
                    'answer' => 'نعم، يمكنك الدفع باستخدام بطاقات الائتمان.'
                ],
                [
                    'id' => 5,
                    'question' => 'هل يمكنني تتبع طلبي في الوقت الحالي؟',
                    'answer' => 'نعم، يمكنك تتبع طلبك في الوقت الحالي.'
                ],
                [
                    'id' => 6,
                    'question' => 'هل يمكنني تقديم شحن دولي؟',
                    'answer' => 'نعم، نقدم شحن دولي.'
                ],
                [
                    'id' => 7,
                    'question' => 'هل يمكنني إلغاء طلبي؟',
                    'answer' => 'نعم، يمكنك إلغاء طلبك.'
                ],
                [
                    'id' => 8,
                    'question' => 'كيف اتواصل مع حدمة العملاء؟',
                    'answer' => 'يمكنك التواصل مع خدمة العملاء عبر الهاتف أو البريد الإلكتروني.'
                ],

            ];
            $questionsInEng = [

                [
                    'id' => 1,
                    'question' => 'Do you offer a warranty on your products?',
                    'answer' => 'Yes, we offer a one-year warranty on all our products.'
                ],
                [
                    'id' => 2,
                    'question' => 'What is your return policy?',
                    'answer' => 'We offer a 30-day return policy for all products. If you are not satisfied with your purchase, you can return it within 30 days of receiving it.'
                ],

                [
                    'id' => 3,
                    'question' => 'What is your shipping policy?',
                    'answer' => 'We offer free shipping for all orders in Saudi Arabia.'
                ],

                [
                    'id' => 4,
                    'question' => 'Can I pay with credit cards?',
                    'answer' => 'Yes, you can pay with credit cards.'
                ],
                [
                    'id' => 5,
                    'question' => 'Can I track my order in real-time?',
                    'answer' => 'Yes, you can track your order in real-time.'
                ],
                [
                    'id' => 6,
                    'question' => 'Can I offer international shipping?',
                    'answer' => 'Yes, we offer international shipping.'
                ],
                [
                    'id' => 7,
                    'question' => 'Can I cancel my order?',
                    'answer' => 'Yes, you can cancel your order.'
                ],
                [
                    'id' => 8,
                    'question' => 'How can I contact customer service?',
                    'answer' => 'You can contact customer service via phone or email.'
                ]
            ];

            if ($locale == 'ar') {
                return response()->json([
                    'status' => true,
                    'data' => $questionsInAR
                ], 200);
            } elseif ($locale == 'en') {
                    return response()->json([
                        'status' => true,
                        'data' => $questionsInEng
                    ], 200);
                }

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}