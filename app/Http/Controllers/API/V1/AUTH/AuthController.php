<?php

namespace App\Http\Controllers\API\V1\AUTH;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;


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
    
            // Kullanıcı tipine göre bir token oluşturur
            $token =  $user->createToken('ApiToken')->plainTextToken;
    
            // Kullanıcı tipini, token'ı ve is_temp_password değerini JSON olarak döndürür
            return response()->json([
                'token' => $token,
                'user_type' => $user->user_type,
                'is_temp_password' => (bool) $user->is_temp_password,
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'E-posta adresi gereklidir.',
            'email.string' => 'E-posta adresi bir metin olmalıdır.',
            'email.email' => 'Geçerli bir e-posta adresi girilmelidir.',
            'email.max' => 'E-posta adresi en fazla :max karakter uzunluğunda olmalıdır.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılmaktadır.',
            'password.required' => 'Şifre gereklidir.',
            'password.string' => 'Şifre bir metin olmalıdır.',
            'password.min' => 'Şifre en az :min karakter uzunluğunda olmalıdır.',
            'password.confirmed' => 'Şifre doğrulama eşleşmiyor.',
        ]);

        // Yeni bir User nesnesi oluşturuluyor ve veritabanına kaydediliyor
        $user = User::create([
            'email' => $validatedData['email'],  // Email bilgisi istekten alınıyor
            'password' => Hash::make($validatedData['password']),  // Şifre hash'leniyor
            'user_type' => 'admin',  // Kullanıcı tipi 'admin' olarak ayarlanıyor
            'is_temp_password' => false
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

            $validatedData = $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'user_type' => 'required|in:manufacturer,customer',
            ], [
                'email.required' => 'E-posta adresi gereklidir.',
                'email.string' => 'E-posta adresi bir metin olmalıdır.',
                'email.email' => 'Geçerli bir e-posta adresi girilmelidir.',
                'email.max' => 'E-posta adresi en fazla :max karakter uzunluğunda olmalıdır.',
                'email.unique' => 'Bu e-posta adresi zaten kullanılmaktadır.',
                'user_type.required' => 'Kullanıcı tipi gereklidir.',
                'user_type.in' => 'Geçerli bir kullanıcı tipi seçmelisiniz (manufacturer veya customer).',
            ]);
    
            // Rastgele bir geçici şifre oluşturuluyor
            $tempPassword = Str::random(10);
    
            // Veritabanı işlemlerini bir işlem içinde gerçekleştiriyoruz
            DB::transaction(function () use ($validatedData, $tempPassword) {
                // Yeni bir User nesnesi oluşturuluyor ve veritabanına kaydediliyor
                $user = User::create([
                    'email' => $validatedData['email'],
                    'password' => Hash::make($tempPassword),
                    'user_type' => $validatedData['user_type'],
                    'is_temp_password' => true,
                ]);
    
                Mail::to($user->email)->send(new TemporaryPasswordMail($tempPassword));
            });
    
            // Başarılı bir şekilde oluşturulduğuna dair bir mesaj döndür
            return response()->json(['message' => ucfirst($validatedData['user_type']) . ' kullanıcısı başarıyla oluşturuldu!'], 201);
    }

    public function Userlogin(Request $request)
    {
        // Kullanıcının e-posta ve şifresini istekten alır
        $credentials = $request->only('email', 'password');
    
        // Kullanıcının kimlik bilgilerini kontrol eder
        if (Auth::attempt($credentials)) {
            // Kullanıcının e-postasına göre kullanıcıyı bulur
            $user = User::where('email', $request->email)->first();
        
            // Kullanıcı tipine göre bir token oluşturur
            $token =  $user->createToken('ApiToken')->plainTextToken;
        
            // Kullanıcının customers ve manufacturers tablolarında bir kaydının olup olmadığını kontrol eder
            $recordExists = (bool) DB::table('customers')->where('user_id', $user->id)->exists() || DB::table('manufacturers')->where('user_id', $user->id)->exists();
            
            // Kullanıcı tipini, token'ı, is_temp_password değerini, record_exists değerini ve user_id değerini JSON olarak döndürür
            return response()->json([
                'user_type' => $user->user_type,
                'user_id' => $user->id,  // Kullanıcı ID'sini ekledik
                'token' => $token,
                'is_temp_password' => (bool) $user->is_temp_password,
                'record_exists' => $recordExists,
            ]);
        }
    
        // Kimlik bilgileri geçersizse hata mesajı döndürür
        return response()->json(['error' => 'Geçersiz Kullanıcı bilgileri'], 401);
    }
    

    public function checkToken(Request $request)
    {
        $token = $request->input('token');
    
        if ($token) {
            // Token'ı bulun
            $tokenModel = PersonalAccessToken::findToken($token);
    
            if ($tokenModel) {
                // Token geçerli
                // Token'ı oluşturan kullanıcıyı alır
                $user = $tokenModel->tokenable;
    
                // Kullanıcının is_temp_password değerini döndürür
                $isTempPassword = $user->is_temp_password;
    
                // Kullanıcının customers ve manufacturers tablolarında bir kaydının olup olmadığını kontrol eder
                $recordExists = DB::table('customers')->where('user_id', $user->id)->exists() || DB::table('manufacturers')->where('user_id', $user->id)->exists();
    
                return response()->json([
                    'token' => true,
                    'is_temp_password' => $isTempPassword,
                    'record_exists' => $recordExists,
                ]);
            } 
        } 
    
        // Token geçerli değil veya sağlanmadı
        return response()->json([
            'token' => false,
            'message' => 'Token geçerli değil veya sağlanmadı'
        ], 401);
    }
    
    
    
    
    
    
}
