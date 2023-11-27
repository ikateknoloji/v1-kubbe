<?php

namespace App\Http\Controllers\API\V1\AUTH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Kullanıcının e-posta ve şifresini istekten alır
        $credentials = $request->only('email', 'password');
    
        // Kullanıcının kimlik bilgilerini kontrol eder
        if (Auth::attempt($credentials)) {
            // Kullanıcının e-postasına göre kullanıcıyı bulur
            $user = User::where('email', $request->email)->first();
    
            // Gelen isteğin kullanıcı tipini alır
            $userType = $user->user_type;
    
            // Kullanıcı tipine göre bir token oluşturur
            $token =  $user->createToken('ApiToken')->plainTextToken;
    
            // Kullanıcı tipini ve token'ı JSON olarak döndürür
            return response()->json([
                'userType' => $userType,
                'token' => $token,
            ]);
        }
    
        // Kimlik bilgileri geçersizse hata mesajı döndürür
        return response()->json(['error' => 'Geçersiz Kullanıcı bilgileri'], 401);
    }


    /**
    * Post
    * Admin kullanıcısı oluşturma metodu
    */
    public function registerAdmin(Request $request)
    {
        // İsteği doğruluyoruz
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Yeni bir User nesnesi oluşturuluyor ve veritabanına kaydediliyor
        $user = User::create([
            'name' => $validatedData['name'],  // İsim bilgisi istekten alınıyor
            'email' => $validatedData['email'],  // Email bilgisi istekten alınıyor
            'password' => Hash::make($validatedData['password']),  // Şifre hash'leniyor
            'user_type' => 'admin',  // Kullanıcı tipi 'admin' olarak ayarlanıyor
        ]);

        // Başarılı bir şekilde oluşturulduğuna dair bir mesaj dönülüyor
        return response()->json(['message' => 'Admin kullanıcısı başarıyla oluşturuldu!'], 201);
    }
}
