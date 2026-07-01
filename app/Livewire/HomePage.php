<?php

namespace App\Livewire;

use App\Models\System;
use Livewire\Component;

class HomePage extends Component
{
    public $search = '';

    public function render()
    {
        $systems = System::where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('display_order')
            ->get();

        return view('livewire.home-page', [
            'systems' => $systems,
        ])->layout('layouts.app');
    }
}
