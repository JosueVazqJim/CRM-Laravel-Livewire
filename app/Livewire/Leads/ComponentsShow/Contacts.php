<?php

namespace App\Livewire\Leads\ComponentsShow;

use App\Models\Leads\Lead;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Contacts extends Component
{
    use Toast;

    public Lead $lead;

    public bool $editing = false;

    // Propiedades actualizables
    public array $contacts = [];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;

        if ($lead->contactPeople->isNotEmpty()) {
            $this->contacts = $lead->contactPeople->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name_contact' => $contact->name_contact,
                    'recurring_client' => $contact->recurring_client ?? false,

                    'channels' => $contact->channels->map(function ($channel) {
                        return [
                            'type' => $channel->type,
                            'channel' => $channel->channel,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        } else {
            $this->contacts = [
                [
                    'name_contact' => '',
                    'recurring_client' => false,
                    'channels' => [
                        ['type' => '', 'channel' => ''],
                    ],
                ],
            ];
        }
    }

    public function enableEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
    }

    public function addContact(): void
    {
        $this->contacts[] = [
            'name_contact' => '',
            'recurring_client' => false,
            'channels' => [
                ['type' => '', 'channel' => ''],
            ],
        ];
    }

    public function removeContact(int $index): void
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
    }

    public function addChannelToContact(int $contactIndex): void
    {
        if (isset($this->contacts[$contactIndex])) {
            $this->contacts[$contactIndex]['channels'][] = ['type' => '', 'channel' => ''];
        }
    }

    public function removeChannelFromContact(int $contactIndex, int $channelIndex): void
    {
        if (isset($this->contacts[$contactIndex]['channels'][$channelIndex])) {
            unset($this->contacts[$contactIndex]['channels'][$channelIndex]);
            $this->contacts[$contactIndex]['channels'] = array_values($this->contacts[$contactIndex]['channels']);
        }
    }

    public function update()
    {
        $this->validate([
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.name_contact' => ['required', 'string', 'max:255'],
            'contacts.*.recurring_client' => ['boolean'],
            'contacts.*.channels' => ['required', 'array', 'min:1'],
            'contacts.*.channels.*.type' => ['required', 'string', 'max:100'],
            'contacts.*.channels.*.channel' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () {

            $existingContactIds = $this->lead->contactPeople->pluck('id')->toArray();
            $incomingContactIds = [];

            foreach ($this->contacts as $contactData) {

                if (empty($contactData['name_contact'])) {
                    continue;
                }

                // 🔹 UPDATE o CREATE contacto
                $contact = \App\Models\Leads\ContactPerson::updateOrCreate(
                    ['id' => $contactData['id'] ?? null],
                    [
                        'lead_id' => $this->lead->id,
                        'name_contact' => $contactData['name_contact'],
                        'recurring_client' => $contactData['recurring_client'] ?? false,
                    ]
                );

                $incomingContactIds[] = $contact->id;

                // ========================
                // 🔸 CHANNELS
                // ========================

                $existingChannelIds = $contact->channels->pluck('id')->toArray();
                $incomingChannelIds = [];

                foreach ($contactData['channels'] ?? [] as $channelData) {

                    if (empty($channelData['type']) || empty($channelData['channel'])) {
                        continue;
                    }

                    $channel = \App\Models\Leads\ChannelContact::updateOrCreate(
                        ['id' => $channelData['id'] ?? null],
                        [
                            'lead_contact_id' => $contact->id,
                            'type' => $channelData['type'],
                            'channel' => $channelData['channel'],
                        ]
                    );

                    $incomingChannelIds[] = $channel->id;
                }

                // 🧹 eliminar canales borrados en UI
                $contact->channels()
                    ->whereNotIn('id', $incomingChannelIds)
                    ->delete();
            }

            // 🧹 eliminar contactos borrados en UI
            $contactsToDelete = $this->lead->contactPeople()
                ->whereNotIn('id', $incomingContactIds)
                ->get();

            foreach ($contactsToDelete as $contactToDelete) {
                $contactToDelete->channels()->delete();
                $contactToDelete->delete();
            }
        });

        $this->editing = false;

        $this->success(
            title: '¡Campos de Contactos del Lead actualizados!',
            description: 'El lead ha sido guardado exitosamente.',
            timeout: 3000,
        );
    }

    public function render()
    {
        return view('livewire.leads.components-show.contacts');
    }
}
