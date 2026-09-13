<?php

namespace Platform\Vaccinations\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Vaccinations\Models\Vaccination;

/**
 * Impfungen-Katalog — Verwaltung (Liste + Anlegen/Bearbeiten/Löschen).
 */
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public array $form = ['code' => '', 'name' => '', 'description' => '', 'position' => 0, 'status' => 'active'];
    public string $search = '';

    protected function teamId(): int
    {
        return (int) Auth::user()->currentTeam->id;
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = ['code' => '', 'name' => '', 'description' => '', 'position' => 0, 'status' => 'active'];
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $v = Vaccination::query()->forTeam($this->teamId())->findOrFail($id);
        $this->editingId = $v->id;
        $this->form = [
            'code'        => $v->code ?? '',
            'name'        => $v->name,
            'description' => $v->description ?? '',
            'position'    => (int) $v->position,
            'status'      => $v->status ?? 'active',
        ];
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.code'        => ['nullable', 'string', 'max:64'],
            'form.name'        => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.position'    => ['nullable', 'integer'],
            'form.status'      => ['required', 'string', 'in:active,archived'],
        ])['form'];

        $payload = [
            'team_id'     => $this->teamId(),
            'code'        => $data['code'] ?: null,
            'name'        => trim((string) $data['name']),
            'description' => $data['description'] ?: null,
            'position'    => (int) ($data['position'] ?? 0),
            'status'      => $data['status'],
        ];

        if ($this->editingId) {
            Vaccination::query()->forTeam($this->teamId())->findOrFail($this->editingId)->update($payload);
        } else {
            Vaccination::create($payload);
        }

        $this->showModal = false;
        $this->dispatch('toast', message: 'Impfung gespeichert.', type: 'success');
    }

    public function delete(int $id): void
    {
        Vaccination::query()->forTeam($this->teamId())->findOrFail($id)->delete();
    }

    public function render()
    {
        $items = Vaccination::query()->forTeam($this->teamId())
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')))
            ->orderBy('position')->orderBy('name')->get();

        return view('vaccinations::livewire.index', ['items' => $items])
            ->layout('platform::layouts.app');
    }
}
