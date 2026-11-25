<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use App\Models\Leads\LeadNote;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Notes extends Component
{
    use Toast;

    public Lead $lead;

    public User $user;

    public bool $editing = false;

    public array $notes = [];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;

        $this->loadNotes();
    }

    protected function loadNotes(): void
    {
        $this->notes = $this->lead->notes()
            ->where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get()
            ->map(fn ($note) => [
                'id' => $note->id,
                'content' => $note->content,
                'created_at' => $note->created_at,
            ])
            ->toArray();
    }

    public function enableEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->loadNotes(); // descarta cambios no guardados
    }

    public function addNote(): void
    {
        $this->notes[] = [
            'id' => null,
            'content' => '',
            'created_at' => now(),
        ];
    }

    public function removeNote(int $index): void
    {
        unset($this->notes[$index]);
        $this->notes = array_values($this->notes);
    }

    public function update()
    {
        $this->validate([
            'notes.*.content' => 'required|string',
        ]);

        DB::transaction(function () {
            $incomingIds = [];

            foreach ($this->notes as $noteData) {
                if (empty($noteData['content'])) {
                    continue;
                }

                $note = LeadNote::updateOrCreate(
                    ['id' => $noteData['id'] ?? null],
                    [
                        'lead_id' => $this->lead->id,
                        'user_id' => Auth::id(),
                        'content' => $noteData['content'],
                    ]
                );

                $incomingIds[] = $note->id;
            }

            // Elimina solo las notas de ESTE usuario que ya no están en la UI
            $this->lead->notes()
                ->where('user_id', Auth::id())
                ->whereNotIn('id', $incomingIds)
                ->delete();
        });

        $this->loadNotes();
        $this->editing = false;

        $this->success(
            title: '¡Notas del Lead actualizadas!',
            description: 'La Nota ha sido guardada exitosamente.',
            timeout: 3000,
        );
    }

    public function render()
    {
        return view('livewire.leads.components-show.note');
    }
}
