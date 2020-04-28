<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function testController(Request $request)
    {
        //获得请求的方法
        $method = $request->method();

        //post请求则解析文件
        if ($method == 'POST'){}

        //如果是get请求，则返回上传页面
        if ($method == 'GET')
        {
            return Admin::content(function (Content $content) {

                $content->header('balabala');
                $content->description('导入数据');
                $content->body(view('GlobalUpload'));
            });
        }
        return view('test');
    }
}
