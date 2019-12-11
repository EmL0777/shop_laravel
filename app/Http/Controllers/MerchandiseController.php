<?php

namespace App\Http\Controllers;

use App\Shop\Entity\Merchandise;
use Validator;  // 驗證器
use Image;

class MerchandiseController extends Controller
{
//    新增商品
    public function merchandiseCreateProcess()
    {
//        建立商品基本資料
        $merchandise_data = [
            'status' => 'C',  // 建立中
            'name' => '',  // 商品名稱
            'name_en' => '',  // 商品英文名稱
            'introduction' => '',  // 商品介紹
            'introduction_en' => '',  // 商品英文介紹
            'photo' => null,  // 商品照片
            'price' => 0,  // 價格
            'remain_count' => 0,  // 商品剩餘數量
        ];
        $Merchandoise = Merchandise::create($merchandise_data);

//        重新導向至「商品編輯頁」
        return redirect('/merchandise' . $Merchandoise->id . '/edit');
    }

//    商品編輯頁 (顯示頁面)
    public function merchandiseItemEditPage($merchandise_id)
    {
        // 撈取商品資料
        $Merchandise = Merchandise::findOrFail($merchandise_id);

        if (!is_null($Merchandise->photo)) {
//            設定商品照片網址
            $Merchandise->photo = url($Merchandise->photo);
        }

        $binding = [
            'title' => '編輯商品',
            'Merchandise' => $Merchandise,
        ];

        return view('merchandise.editMerchandise', $binding);
    }

//    商品資料更新處理 (處理程序)
    public function merchandiseItemUpdateProcess($merchandise_id)
    {
//        撈取商品資料
        $Merchandise = Merchandise::findOrFail($merchandise_id);
//        接收輸入資料
        $input = request()->all();

//        驗證規則
        $rules = [
            'status' => [
                'required',
                'in:C,S',
            ],
            'name' => [
                'required',
                'max:80',
            ],
            'name_en' => [
                'required',
                'max:80',
            ],
            'introduction' => [
                'required',
                'max:2000',
            ],
            'introduction_en' => [
                'required',
                'max:2000',
            ],
            'photo' => [
                'file',
                'image',
                'max:10240',  // 10 MB
            ],
            'price' => [
                'required',
                'integer',
                'min:0',
            ],
            'remain_count' => [
                'required',
                'integer',
                'min:0',
            ],
        ];

//        驗證資料
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
//            資料驗證錯誤
            return redirect('/merchandise/' . $merchandise_id . '/edit')
                ->withErrors($validator)
                ->withInput();
        }

        if (isset($input['photo'])) {
//            有上傳照片
            $photo = $input['photo'];
//            檔案副檔名
            $file_extension = $photo->getClientOriginalExtension();
//            產生自訂隨機檔案名稱
            $file_name = uniqid() . '.' . $file_extension;
//            檔案相對路徑
            $file_relative_path = 'image/merchandise/' . $file_name;
//            檔案存放目錄為對外公開 public 目錄下的相對位置
            $file_path = public_path($file_relative_path);
//            裁切圖片
            $image = Image::make($photo)->fit(450, 300)->save($file_path);
//            設定圖片檔案相對位置
            $input['photo'] = $file_relative_path;
        }

        $Merchandise->update($input);

//        重新導向到商品編輯頁
        return redirect('/merchandise/' . $Merchandise->id . '/edit');
    }

    public function merchandiseManageListPage()
    {
//        每頁資料量
        $row_per_page = 10;
//        撈取商品分頁資料
        $MerchandisePaginate = Merchandise::OrderBy('created_at', 'desc')
            ->paginate($row_per_page);

//        設定商品圖片網址
        foreach ($MerchandisePaginate as &$Merchandise) {
            if (!is_null($Merchandise->photo)) {
//                設定商品照片網址
                $Merchandise->photo = url($Merchandise->photo);
            }
        }

        $binding = [
            'title' => '管理商品',
            'MerchandisePaginate' => $MerchandisePaginate,
        ];

        return view('merchandise.manageMerchandise', $binding);
    }
}