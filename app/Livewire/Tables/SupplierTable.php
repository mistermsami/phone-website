<?php

namespace App\Livewire\Tables;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierTable extends Component
{
    use WithPagination;

    public $perPage = 5;

    public $search = '';

    public $sortField = 'name';

    public $sortAsc = false;

    public function sortBy($field): void
    {
        if($this->sortField === $field)
        {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to the first page when search query changes
    }
    public function render()
    {
        return view('livewire.tables.supplier-table', [
            'suppliers' => Supplier::where("user_id", auth()->id())
                ->with(['purchases'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
        ]);
    }
}
