<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
    }

    public function loginUser()
    {

        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            return $this->redirect('/tasks-panel');
        }

        $this->addError('email', 'Incorrect email or password.');
    }
    
};
?>
{{--
<div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; font-family: tahoma;">
    <h2 style="text-align: center;">Login</h2>

    <form wire:submit="login">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Email:</label>
            <input type="email" wire:model.live="email" style="width: 100%; padding: 8px; box-sizing: border-box;">
            @error('email') <span style="color: red; font-size: 14px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Password:</label>
            <input type="password" wire:model.live="password" style="width: 100%; padding: 8px; box-sizing: border-box;">
            @error('password') <span style="color: red; font-size: 14px;">{{ $message }}</span> @enderror
        </div>

        <button type="submit" style="width: 100%; padding: 10px; background-color: #4C50AF; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Login
        </button>
    </form>
</div>
--}}


<div style="font-family: tahoma; max-width: 400px; margin: 50px auto; direction: rtl; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #fff;">
    
    <h2 style="text-align: center; color: #333; margin-bottom: 20px;">تسجيل الدخول</h2>

    <form wire:submit.prevent="loginUser">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">البريد الإلكتروني:</label>
            <input type="email" wire:model.blur="email" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            @error('email') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">كلمة المرور:</label>
            <input type="password" wire:model.defer="password" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            @error('password') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="cursor: pointer;">
                <input type="checkbox" wire:model="remember"> تذكرني على هذا الجهاز
            </label>
        </div>

        <button type="submit" wire:loading.attr="disabled" style="width: 100%; background: #007BFF; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
            <span wire:loading.remove wire:target="loginUser">دخول</span>
            <span wire:loading wire:target="loginUser">جاري التحقق من الهوية والأمان...</span>
        </button>

        <p style="text-align: center; margin-top: 15px; font-size: 14px;">
            ليس لديك حساب؟ <a href="/register" wire:navigate style="color: #007BFF; text-decoration: none;">أنشئ حساباً جديداً الآن</a>
        </p>

    </form>
</div>