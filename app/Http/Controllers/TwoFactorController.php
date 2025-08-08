<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA settings page
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user->google2fa_secret) {
            // Generate a new secret if one doesn't exist
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['google2fa_secret' => $secret]);
        }
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );
        
        // Generate multiple QR code options for maximum compatibility
        $qrCodeImage1 = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);
        $qrCodeImage2 = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($qrCodeUrl);
        $qrCodeImage3 = 'https://quickchart.io/qr?text=' . urlencode($qrCodeUrl) . '&size=200';
        
        return view('auth.2fa.setup', [
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'qrCodeImage1' => $qrCodeImage1,
            'qrCodeImage2' => $qrCodeImage2,
            'qrCodeImage3' => $qrCodeImage3,
            'secret' => $user->google2fa_secret
        ]);
    }

    /**
     * Enable 2FA for the user
     */
    public function enable(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user->google2fa_secret) {
            return back()->withErrors(['error' => '2FA secret not found. Please refresh and try again.']);
        }
        
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->verification_code);
        
        if ($valid) {
            $user->update([
                'google2fa_enabled' => true,
                'google2fa_enabled_at' => now()
            ]);
            
            return redirect()->back()->with('success', '2FA has been enabled successfully!');
        }
        
        throw ValidationException::withMessages([
            'verification_code' => ['Invalid verification code. Please try again.']
        ]);
    }

    /**
     * Disable 2FA for the user
     */
    public function disable(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user->google2fa_enabled) {
            return back()->withErrors(['error' => '2FA is not enabled.']);
        }
        
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->verification_code);
        
        if ($valid) {
            $user->update([
                'google2fa_enabled' => false,
                'google2fa_secret' => null,
                'google2fa_enabled_at' => null
            ]);
            
            // Clear 2FA session if it exists
            Session::forget('2fa_verified');
            
            return redirect()->back()->with('success', '2FA has been disabled successfully!');
        }
        
        throw ValidationException::withMessages([
            'verification_code' => ['Invalid verification code. Please try again.']
        ]);
    }

    /**
     * Show 2FA verification form during login
     */
    public function showVerify()
    {
        return view('auth.2fa.verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user || !$user->google2fa_enabled) {
            return redirect()->route('login');
        }
        
        $valid = $this->google2fa->verifyKey($user->google2fa_secret, $request->verification_code);
        
        if ($valid) {
            Session::put('2fa_verified', true);
            return redirect()->intended('/student/dashboard');
        }
        
        throw ValidationException::withMessages([
            'verification_code' => ['Invalid verification code. Please try again.']
        ]);
    }

    /**
     * Reset 2FA secret (generate new QR code)
     */
    public function reset()
    {
        $user = Auth::user();
        $secret = $this->google2fa->generateSecretKey();
        
        $user->update([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => false,
            'google2fa_enabled_at' => null
        ]);
        
        Session::forget('2fa_verified');
        
        return redirect()->back()->with('success', 'New 2FA secret generated. Please scan the new QR code and enable 2FA.');
    }
}
