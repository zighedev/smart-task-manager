<?php

use Livewire\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;

new class extends Component
{
    public $title = '';
    public $description = '';
    public $editingTaskId = null;
    public $editTitle = '';
    public $editDescription = '';

    public $search = '';
    public $filterStatus = 'all'; // (all, pending, in_progress, completed)

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
            'editTitle' => 'required|min:3|max:255',
            'editDescription' => 'nullable|max:1000'
        ]);
    }

    public function updatedSearch(){}
    
    public function addTask()
    {
        $this->validate([
            'title' => 'required|min:3',
            'description' => 'nullable|max:1000',
        ]);

        Task::create([
            'title' => $this->title,
            'description' => $this->description, 
            'status' => TaskStatus::PENDING,
            'user_id' => Auth::id() 
        ]);

        $this->title = ''; 
        $this->description = ''; 
    }

    public function editTask($taskId)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->editingTaskId = $taskId;
        $this->editTitle = $task->title;
        $this->editDescription = $task->description;
    }

    public function updateTask()
    {
        $this->validate([
            'editTitle' => 'required|min:3|max:255',
            'editDescription' => 'nullable|max:1000',
        ]);

        $task = Task::where('user_id', Auth::id())->findOrFail($this->editingTaskId);
        $task->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->editingTaskId = null;
        $this->editTitle = '';
        $this->editDescription = '';
    }

    public function updateStatus($taskId, string $statusValue)
    {
        $status = TaskStatus::tryFrom($statusValue);

        if (!$status) {
            return; 
        }

        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $task->status = $status;
        $task->save();
    }

    public function deleteTask(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        session()->flash('message', 'task deleted');
    }

    public function with()
    {
        $userId = Auth::id();

        $stats = [
            'all' => Task::where('user_id', $userId)->count(),
            'pending' => Task::where('user_id', $userId)->where('status', TaskStatus::PENDING)->count(),
            'in_progress' => Task::where('user_id', $userId)->where('status', TaskStatus::IN_PROGRESS)->count(),
            'completed' => Task::where('user_id', $userId)->where('status', TaskStatus::COMPLETED)->count(),
        ];

        $query = Task::where('user_id', $userId);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'tasks' => $query->latest()->get(),
            'stats' => $stats
        ];
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return $this->redirect('/login', navigate: true);
    }


};
?>
<div style="font-family: tahoma; max-width: 650px; margin: 0 auto; padding: 20px;">
    <nav style="display: flex; justify-content: space-between; margin-bottom: 20px; background: #efefef;">
        <span style="padding: 4px;">welcome <strong>{{ Auth::user()->name }}</strong></span>
        <button wire:click="logout" style="background: #fff; margin: 4px; cursor: pointer;">Logout</button>
    </nav>
    <div style="display: flex; gap: 10px; margin-bottom: 20px; text-align: center;">
        <div style="flex: 1; background: #334b5c; padding: 10px; border-radius: 6px; border: 1px solid #90caf9;">
            <div style="font-size: 20px; font-weight: bold; color: #fff;">{{ $stats['all'] }}</div>
            <div style="font-size: 12px; color: #fff;">إجمالي المهام</div>
        </div>
        <div style="flex: 1; background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #e0e0e0;">
            <div style="font-size: 20px; font-weight: bold; color: #424242;">{{ $stats['pending'] }}</div>
            <div style="font-size: 12px; color: #616161;">⏳ معلقة</div>
        </div>
        <div style="flex: 1; background: #fffde7; padding: 10px; border-radius: 6px; border: 1px solid #fff59d;">
            <div style="font-size: 20px; font-weight: bold; color: #f57f17;">{{ $stats['in_progress'] }}</div>
            <div style="font-size: 12px; color: #f57c00;">⚡ قيد التنفيذ</div>
        </div>
        <div style="flex: 1; background: #e8f5e9; padding: 10px; border-radius: 6px; border: 1px solid #a5d6a7;">
            <div style="font-size: 20px; font-weight: bold; color: #1b5e20;">{{ $stats['completed'] }}</div>
            <div style="font-size: 12px; color: #2e7d32;">✅ مكتملة</div>
        </div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <input type="text" wire:model.live="search" placeholder="🔍 ابحث عن مهمة بالاسم أو الوصف..." 
               style="flex: 2; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <select wire:model.live="filterStatus" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="all">📁 جميع الحالات</option>
            <option value="pending">⏳ معلقة فقط</option>
            <option value="in_progress">⚡ قيد التنفيذ فقط</option>
            <option value="completed">✅ مكتملة فقط</option>
        </select>
    </div>

    @if (session()->has('message'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 3000)" 
         x-show="show" 
         style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        {{ session('message') }}
    </div>
    @endif

    <form wire:submit.prevent="addTask" style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
        <div style="margin-bottom: 10px;">
            <input type="text" wire:model.live="title" placeholder="عنوان المهمة الجديد..." style="width: 100%; padding: 8px; box-sizing: border-box;">
            @error('title') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>
        
        <div style="margin-bottom: 10px;">
            <textarea wire:model.live="description" placeholder="وصف المهمة (اختياري)..." style="width: 100%; padding: 8px; box-sizing: border-box; height: 60px;"></textarea>
            @error('description') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" style="background: #2196F3; color: white; padding: 8px 15px; border: none; cursor: pointer; border-radius: 4px;">
            <span wire:loading.remove wire:target="addTask">إضافة المهمة</span>
            <span wire:loading wire:target="addTask">جاري الحفظ...</span>
        </button>
    </form>

    <ul style="list-style: none; padding: 0;">
        @forelse ($tasks as $task)
            <li style="padding: 15px; border: 1px solid #eee; margin-bottom: 10px; border-radius: 6px;
                background: {{ $task->status->value === 'completed' ? '#e2f0d9' : ($task->status->value === 'in_progress' ? '#fff2cc' : '#ffffff') }}">
                
                @if ($editingTaskId === $task->id)
                    <div>
                        <input type="text" wire:model.live="editTitle" style="width: 100%; padding: 6px; margin-bottom: 5px;">
                        @error('editTitle') <span style="color: red; font-size: 12px; display:block;">{{ $message }}</span> @enderror

                        <textarea wire:model.live="editDescription" style="width: 100%; padding: 6px; margin-bottom: 5px; height: 50px;"></textarea>
                        @error('editDescription') <span style="color: red; font-size: 12px; display:block;">{{ $message }}</span> @enderror

                        <button wire:click="updateTask" style="background: #4CAF50; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; font-size: 13px;">حفظ</button>
                        <button wire:click="cancelEdit" style="background: #9e9e9e; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; font-size: 13px; margin-right: 5px;">إلغاء</button>
                    </div>
                @else
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <strong style="{{ $task->status->value === 'completed' ? 'text-decoration: line-through; color: gray;' : '' }}">
                                {{ $task->title }}
                            </strong>
                            @if($task->description)
                                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666; {{ $task->status->value === 'completed' ? 'text-decoration: line-through; color: #aaa;' : '' }}">
                                    {{ $task->description }}
                                </p>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <select wire:change="updateStatus({{ $task->id }}, $event.target.value)" style="padding: 4px; border-radius: 4px;">
                                @foreach(App\Enums\TaskStatus::cases() as $statusCase)
                                    <option value="{{ $statusCase->value }}" {{ $task->status === $statusCase ? 'selected' : '' }}>
                                        {{ $statusCase->value === 'pending' ? '⏳ معلقة' : ($statusCase->value === 'in_progress' ? '⚡ تنفيذ' : '✅ مكتملة') }}
                                    </option>
                                @endforeach
                            </select>

                            <button wire:click="editTask({{ $task->id }})" style="background: #ff9800; color: white; border: none; padding: 4px 8px; cursor: pointer; border-radius: 4px; font-size: 13px;">تعديل</button>
                            <button wire:click="deleteTask({{ $task }})" wire:confirm="هل أنت متأكد؟" style="background: #f44336; color: white; border: none; padding: 4px 8px; cursor: pointer; border-radius: 4px; font-size: 13px;">حذف</button>
                        </div>
                    </div>
                @endif
            </li>
        @empty
            <li style="text-align: center; color: #999; padding: 20px; border: 1px dashed #ccc; border-radius: 6px;">
                لا توجد مهام تطابق البحث أو الفلترة المحددة حالياً.
            </li>
        @endforelse
    </ul>

</div>
