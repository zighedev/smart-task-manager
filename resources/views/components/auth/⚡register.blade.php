<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|min:3|max:50',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ];

    public function registerUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password), // التشفير الآمن
        ]);

        Auth::login($user);

        return $this->redirect('/tasks-panel', navigate: true); 
    }


};
?>

<div style="font-family: tahoma; max-width: 400px; margin: 50px auto; direction: rtl; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #fff;">
    
    <h2 style="text-align: center; color: #333; margin-bottom: 20px;">إنشاء حساب جديد</h2>

    <form wire:submit.prevent="registerUser">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">الاسم الكامل:</label>
            <input type="text" wire:model.blur="name" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            @error('name') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">البريد الإلكتروني:</label>
            <input type="email" wire:model.blur="email" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            @error('email') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">كلمة المرور:</label>
            <input type="password" wire:model.live="password" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            @error('password') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">تأكيد كلمة المرور:</label>
            <input type="password" wire:model.live="password_confirmation" style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <button type="submit" wire:loading.attr="disabled" style="width: 100%; background: #4CAF50; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
            <span wire:loading.remove wire:target="registerUser">إنشاء الحساب</span>
            <span wire:loading wire:target="registerUser">جاري إنشاء الحساب وأمان الجلسة...</span>
        </button>

    </form>
</div>