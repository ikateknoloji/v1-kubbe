<?php

namespace App\Http\Controllers\API\V1\AUTH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class PasswordResetController extends Controller
{
    /**
     * Şifreyi sıfırlar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function resetPassword(Request $request)
    {
        // E-posta adresini doğrula
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // E-posta adresini doğrula
        $request->validate(['email' => 'required|email']);
    
        // E-posta adresine göre kullanıcıyı bul
        $user = User::where('email', $request->email)->first();
    
        // Eğer kullanıcı yoksa hata mesajı döndür
        if (!$user) {
            return response()->json(['message' => 'Kullanıcı bulunamadı'], 404);
        }
    
        // Geçici şifre oluştur
        $tempPassword = Str::random(10);
    
        // Kullanıcının şifresini ve is_temp_password alanını güncelle
        $user->fill([
            'password' => Hash::make($tempPassword),
            'is_temp_password' => true
        ])->save();
    
        // Kullanıcıya yeni şifreyi içeren bir e-posta gönder
        Mail::to($request->email)->send(new ResetPasswordMail($tempPassword));
    
        // Başarılı şifre sıfırlama mesajı döndür
        return response()->json(['message' => 'Şifre başarıyla sıfırlandı'], 200);
    }

    /**
     * Geçici şifre ile şifreyi sıfırlar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPasswordWithTempPassword(Request $request)
    {
             // Gelen verileri doğrula
             $request->validate([
                'email' => 'required|email',
                'temp_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ], [
                'email.required' => 'E-posta adresi gereklidir.',
                'email.email' => 'Geçerli bir e-posta adresi girilmelidir.',
                'temp_password.required' => 'Geçici şifre gereklidir.',
                'new_password.required' => 'Yeni şifre gereklidir.',
                'new_password.min' => 'Yeni şifre en az :min karakter uzunluğunda olmalıdır.',
                'new_password.confirmed' => 'Yeni şifre doğrulama eşleşmiyor.',
            ]);
            
     
         // E-posta adresine göre kullanıcıyı bul
         $user = User::where('email', $request->email)->first();
     
         // Kullanıcı yoksa veya geçici şifre yanlışsa hata mesajı döndür
         if (!$user || !Hash::check($request->temp_password, $user->password)) {
             return response()->json(['message' => 'Kullanıcı bulunamadı veya geçici şifre yanlış'], 404);
         }
     
         // Kullanıcının şifresini ve is_temp_password alanını güncelle
         $user->fill([
             'password' => Hash::make($request->new_password),
             'is_temp_password' => false
         ])->save();

         // Başarılı şifre sıfırlama mesajı döndür
         return response()->json(['message' => 'Şifre başarıyla sıfırlandı'], 200);
    }


    /**
     * Kullanıcının şifresini günceller.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        try {
            // Laravel Sanctum ile oturum açan kullanıcıyı alır
            $user = $request->user();
    
            // Gelen verileri doğrula
            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ],[
                'current_password.required' => 'Mevcut şifre alanı gereklidir.',
                'new_password.required' => 'Yeni şifre alanı gereklidir.',
                'new_password.min' => 'Yeni şifreniz en az :min karakter uzunluğunda olmalıdır.',
                'new_password.confirmed' => 'Yeni şifreniz eşleşmiyor.',
            ]);
    
            // Mevcut şifre yanlışsa hata döndür
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json(['error' => 'Mevcut şifre yanlış'], 401);
            }
    
            // Kullanıcının şifresini güncelle ve kaydet
            $user->update(['password' => Hash::make($validatedData['new_password'])]);
    
            // Başarılı mesajı döndür
            return response()->json(['message' => 'Şifre başarıyla güncellendi'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Doğrulama hatası oluştuğunda ilk hata mesajını döndür
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            // Diğer hatalar için genel hata mesajı döndür
            return response()->json(['error' => 'Bir hata oluştu, lütfen daha sonra tekrar deneyin.'], 500);
        }
    }
    

}
