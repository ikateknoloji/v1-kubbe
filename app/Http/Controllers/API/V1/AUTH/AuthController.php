<?php

namespace App\Http\Controllers\API\V1\AUTH;

use App\Http\Controllers\Controller;
use App\Mail\TemporaryPasswordMail;
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

        /**
    * Post İsteği
    * Üretici veya Müşteri kullanıcısı oluşturma metodu
    */
    public function registerUser(Request $request)
    {
        // İsteği doğruluyoruz
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'user_type' => 'required|in:manufacturer,customer',
        ]);
    
        // Rastgele bir geçici şifre oluşturuluyor
        $tempPassword = Str::random(10);
    
        // Yeni bir User nesnesi oluşturuluyor ve veritabanına kaydediliyor
        $user = User::create([
            'name' => $validatedData['name'],  // İsim bilgisi istekten alınıyor
            'email' => $validatedData['email'],  // Email bilgisi istekten alınıyor
            'password' => Hash::make($tempPassword),  // Geçici şifre hash'leniyor
            'user_type' => $validatedData['user_type'],  // Kullanıcı tipi istekten alınıyor
            'is_temp_password' => true,  // Geçici şifre olduğunu belirtmek için 'is_temp_password' alanı true olarak ayarlanıyor
        ]);
    
        Mail::to($user->email)->send(new TemporaryPasswordMail($tempPassword));
        // Başarılı bir şekilde oluşturulduğuna dair bir mesaj dönülüyor

        return response()
        ->json(['message' => ucfirst($validatedData['user_type']) . ' kullanıcısı başarıyla oluşturuldu!'], 201);
    } 
}
