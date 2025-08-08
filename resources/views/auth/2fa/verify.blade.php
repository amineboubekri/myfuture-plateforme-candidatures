@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header text-center">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-0">Two-Factor Authentication</h4>
                    <p class="text-muted mt-2 mb-0">Please verify your identity</p>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <p class="text-muted">
                            Enter the 6-digit code from your authenticator app to complete the login process.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('2fa.verify') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="verification_code" class="form-label visually-hidden">
                                Verification Code
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center @error('verification_code') is-invalid @enderror" 
                                   id="verification_code" 
                                   name="verification_code" 
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   autocomplete="off"
                                   style="font-size: 2rem; letter-spacing: 0.5rem; font-family: 'JetBrains Mono', monospace;"
                                   autofocus
                                   required>
                            @error('verification_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="fas fa-unlock me-2"></i>Verify & Continue
                        </button>
                        
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <div class="alert alert-info alert-sm">
                            <i class="fas fa-info-circle me-1"></i>
                            <small>Can't access your authenticator? Contact support for assistance.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Security Tips -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-lightbulb me-2"></i>Security Tips
                    </h6>
                    <ul class="text-muted small mb-0">
                        <li>Make sure you're on the correct website</li>
                        <li>Never share your 2FA codes with anyone</li>
                        <li>Keep your authenticator app updated</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('verification_code');
    
    // Auto-format and validate input
    codeInput.addEventListener('input', function(e) {
        // Remove non-digits
        let value = e.target.value.replace(/\D/g, '');
        // Limit to 6 digits
        value = value.slice(0, 6);
        e.target.value = value;
        
        // Auto-submit when 6 digits are entered
        if (value.length === 6) {
            setTimeout(() => {
                e.target.form.submit();
            }, 500); // Small delay for better UX
        }
    });
    
    // Auto-focus and select all on focus
    codeInput.addEventListener('focus', function(e) {
        e.target.select();
    });
    
    // Prevent paste of non-numeric content
    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numericPaste = paste.replace(/\D/g, '').slice(0, 6);
        e.target.value = numericPaste;
        
        // Auto-submit if 6 digits pasted
        if (numericPaste.length === 6) {
            setTimeout(() => {
                e.target.form.submit();
            }, 500);
        }
    });
});
</script>
@endsection
