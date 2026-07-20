<?php

namespace App\Livewire;

use App\Models\University;
use Livewire\Component;

class HomePage extends Component
{
    public $search = '';

    public function render()
    {
        $systems = University::coordination()->where('IS_IT_ENABLE', 1)
            ->when($this->search, function ($query) {
                $query->where('U_NAME', 'like', '%' . $this->search . '%')
                    ->orWhere('EN_U_NAME', 'like', '%' . $this->search . '%');
            })
            ->orderBy('ORDERIG')
            ->get();

        return view('livewire.home-page', [
            'systems' => $systems,
        ])->layout('layouts.app');
    }
}
