<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\University;

class UniversitySwitcher extends Component
{
    public $selectedUnid;

    public function mount()
    {
        $this->selectedUnid = session('selected_unid', 0);
    }

    public function updatedSelectedUnid($value)
    {
        session(['selected_unid' => $value]);
        $this->redirect(request()->header('Referer') ?? '/admin');
    }

    public function render()
    {
        $universities = University::withoutGlobalScope(\App\Models\Scopes\UniversityScope::class)->coordination()->pluck('U_NAME', 'UNID')->prepend('الكل (جميع الجامعات)', 0);

        return view('livewire.university-switcher', [
            'universities' => $universities,
        ]);
    }
}
