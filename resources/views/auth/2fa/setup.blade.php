@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i>Two-Factor Authentication</h4>
                    <span class="badge {{ $user->google2fa_enabled ? 'bg-success' : 'bg-secondary' }}">
                        {{ $user->google2fa_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Information Section -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>About 2FA
                            </h5>
                            <p class="text-muted mb-4">
                                Two-factor authentication adds an extra layer of security to your account. 
                                After entering your password, you'll need to provide a 6-digit code from 
                                your authenticator app.
                            </p>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-mobile-alt me-2"></i>Compatible Apps:</h6>
                                <ul class="mb-0">
                                    <li>Google Authenticator</li>
                                    <li>Microsoft Authenticator</li>
                                    <li>Authy</li>
                                    <li>Any TOTP-compatible app</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Setup/Management Section -->
                        <div class="col-md-6">
                            @if(!$user->google2fa_enabled)
                                <!-- Enable 2FA -->
                                <div class="text-center mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-qrcode me-2"></i>Setup 2FA
                                    </h5>
                                    
                                    <!-- QR Code -->
                                    <div class="card border-light mb-3">
                                        <div class="card-body">
                                            <p class="small text-muted mb-3">Scan this QR code with your authenticator app:</p>
                                            
                                            <!-- Multiple QR Code Options -->
                                            <div class="text-center mb-3">
                                                <img id="qr-code" 
                                                     src="{{ $qrCodeImage1 }}" 
                                                     alt="2FA QR Code" 
                                                     class="img-fluid" 
                                                     style="max-width: 200px;"
                                                     onload="console.log('QR Code loaded successfully');"
                                                     onerror="tryNextQrCode(this);">
                                            </div>
                                            
                                            <script>
                                            let qrAttempts = 0;
                                            const qrSources = [
                                                '{{ $qrCodeImage1 }}',
                                                '{{ $qrCodeImage2 }}', 
                                                '{{ $qrCodeImage3 }}'
                                            ];
                                            
                                            function tryNextQrCode(img) {
                                                qrAttempts++;
                                                if (qrAttempts < qrSources.length) {
                                                    console.log('Trying QR source ' + (qrAttempts + 1));
                                                    img.src = qrSources[qrAttempts];
                                                } else {
                                                    console.log('All QR sources failed');
                                                    img.parentElement.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>QR Code could not be loaded. Please use the manual setup below.</div>';
                                                }
                                            }
                                            </script>
                                            
                                            <div class="alert alert-info small">
                                                <strong>Manual Setup:</strong><br>
                                                <p class="mb-2">If the QR code doesn't work, manually enter this information in your authenticator app:</p>
                                                <p class="mb-1"><strong>Account:</strong> {{ auth()->user()->email }}</p>
                                                <p class="mb-1"><strong>Issuer:</strong> {{ config('app.name') }}</p>
                                                <p class="mb-0"><strong>Secret Key:</strong></p>
                                                <div class="bg-light text-dark p-2 rounded mt-1">
                                                    <code style="font-size: 0.9rem; word-break: break-all;">{{ $secret }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Enable Form -->
                                <form method="POST" action="{{ route('2fa.enable') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="verification_code" class="form-label">
                                            <i class="fas fa-key me-1"></i>Verification Code
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('verification_code') is-invalid @enderror" 
                                               id="verification_code" 
                                               name="verification_code" 
                                               placeholder="Enter 6-digit code"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               autocomplete="off"
                                               required>
                                        @error('verification_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter the 6-digit code from your authenticator app</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-shield-alt me-2"></i>Enable 2FA
                                    </button>
                                </form>
                            @else
                                <!-- 2FA Enabled - Management Options -->
                                <div class="text-center mb-4">
                                    <div class="alert alert-success">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>2FA is Active</strong><br>
                                        <small>Enabled on {{ $user->google2fa_enabled_at->format('M d, Y \a\t g:i A') }}</small>
                                    </div>
                                </div>

                                <!-- Reset Secret -->
                                <div class="mb-3">
                                    <form method="POST" action="{{ route('2fa.reset') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-warning w-100 mb-2"
                                                onclick="return confirm('This will generate a new secret and disable 2FA. You will need to re-enable it with the new QR code. Continue?')">
                                            <i class="fas fa-sync me-2"></i>Reset Secret Key
                                        </button>
                                    </form>
                                </div>

                                <!-- Disable Form -->
                                <form method="POST" action="{{ route('2fa.disable') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="verification_code" class="form-label">
                                            <i class="fas fa-key me-1"></i>Enter Code to Disable
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('verification_code') is-invalid @enderror" 
                                               id="verification_code" 
                                               name="verification_code" 
                                               placeholder="Enter 6-digit code"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               autocomplete="off"
                                               required>
                                        @error('verification_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter your current 6-digit code to disable 2FA</div>
                                    </div>
                                    
                                    <button type="submit" 
                                            class="btn btn-danger w-100"
                                            onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                                        <i class="fas fa-shield-alt me-2"></i>Disable 2FA
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format the verification code input
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('verification_code');
    if (codeInput) {
        codeInput.addEventListener('input', function(e) {
            // Remove non-digits
            let value = e.target.value.replace(/\D/g, '');
            // Limit to 6 digits
            value = value.slice(0, 6);
            e.target.value = value;
        });
    }
});
</script>
@endsection
