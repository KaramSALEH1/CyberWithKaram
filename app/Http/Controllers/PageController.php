<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $services = Service::take(3)->get(); // عرض أول 3 خدمات فقط في الرئيسية
        return view('welcome', compact('services'));
    }

    public function about()
    {
        return view('about');
    }

    public function services()
    {
        $services = Service::all();
        return view('services', compact('services'));
    }

    public function courses()
    {
        return view('courses');
    }

    public function contact()
    {
        return view('contact');
    }
}
