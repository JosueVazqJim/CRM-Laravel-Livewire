<?php

namespace App\Services\Leads;

use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use App\Models\Leads\LeadNote;
use App\Models\Leads\LeadUserAccess;
use App\Models\Quotes\Quote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeadMergeService
{
    private const PERMISSION_RANK = ['view' => 1, 'edit' => 2, 'manage' => 3];

    /**
     * Fusiona N leads de la misma company en el lead sobreviviente, reasignando
     * quotes, contact_people (los conservados), lead_activities, lead_notes y
     * lead_user_access, y eliminando físicamente los leads perdedores.
     *
     * @param  int[]  $leadIds
     * @param  array<string,int>  $fieldChoices  ['name_company' => leadId, ...]
     * @param  int[]  $contactPeopleIdsToKeep
     */
    public function merge(
        array $leadIds,
        int $survivorId,
        array $fieldChoices,
        int $billingLeadId,
        array $contactPeopleIdsToKeep,
    ): Lead {
        $leadIds = array_values(array_unique($leadIds));

        $this->guard($leadIds, $survivorId, $fieldChoices, $billingLeadId);

        return DB::transaction(function () use ($leadIds, $survivorId, $fieldChoices, $billingLeadId, $contactPeopleIdsToKeep) {
            $leads = Lead::whereIn('id', $leadIds)->lockForUpdate()->get()->keyBy('id');
            $survivor = $leads->get($survivorId);
            $loserIds = collect($leadIds)->reject(fn (int $id) => $id === $survivorId)->values()->all();

            $this->applyFieldChoices($survivor, $leads, $fieldChoices);
            $this->applyBillingChoice($survivor, $leads->get($billingLeadId));
            $survivor->save();

            $this->reassignQuotes($survivorId, $loserIds);
            $this->reconcileContactPeople($survivorId, $leadIds, $contactPeopleIdsToKeep);
            $this->reassignActivitiesAndNotes($survivorId, $loserIds);
            $this->reassignAndDedupeUserAccess($survivorId, $loserIds);

            Lead::whereIn('id', $loserIds)->delete();

            return $survivor->fresh(['state', 'billingInfo.address', 'contactPeople.channels']);
        });
    }

    /**
     * @param  int[]  $leadIds
     * @param  array<string,int>  $fieldChoices
     */
    private function guard(array $leadIds, int $survivorId, array $fieldChoices, int $billingLeadId): void
    {
        if (count($leadIds) < 2) {
            throw new \InvalidArgumentException('Se requieren al menos 2 leads para fusionar.');
        }

        if (! in_array($survivorId, $leadIds, true)) {
            throw new \InvalidArgumentException('El lead sobreviviente no está entre los leads seleccionados.');
        }

        if (! in_array($billingLeadId, $leadIds, true)) {
            throw new \InvalidArgumentException('La facturación elegida no pertenece a los leads seleccionados.');
        }

        foreach ($fieldChoices as $field => $chosenLeadId) {
            if (! in_array($chosenLeadId, $leadIds, true)) {
                throw new \InvalidArgumentException("El valor elegido para '{$field}' no pertenece a los leads seleccionados.");
            }
        }

        $companyCount = Lead::whereIn('id', $leadIds)->distinct()->count('company_id');

        if ($companyCount > 1) {
            throw new \InvalidArgumentException('Todos los leads deben pertenecer a la misma empresa.');
        }
    }

    /**
     * @param  Collection<int,Lead>  $leads
     * @param  array<string,int>  $fieldChoices
     */
    private function applyFieldChoices(Lead $survivor, Collection $leads, array $fieldChoices): void
    {
        foreach ($fieldChoices as $field => $chosenLeadId) {
            $survivor->{$field} = $leads->get($chosenLeadId)->{$field};
        }
    }

    private function applyBillingChoice(Lead $survivor, Lead $billingSourceLead): void
    {
        $survivor->billing_id = $billingSourceLead->billing_id;
    }

    /** @param  int[]  $loserIds */
    private function reassignQuotes(int $survivorId, array $loserIds): void
    {
        if (empty($loserIds)) {
            return;
        }

        Quote::whereIn('lead_id', $loserIds)->update(['lead_id' => $survivorId]);
    }

    /**
     * @param  int[]  $leadIds
     * @param  int[]  $idsToKeep
     */
    private function reconcileContactPeople(int $survivorId, array $leadIds, array $idsToKeep): void
    {
        ContactPerson::whereIn('lead_id', $leadIds)
            ->whereIn('id', $idsToKeep)
            ->update(['lead_id' => $survivorId]);

        $toDeleteIds = ContactPerson::whereIn('lead_id', $leadIds)
            ->whereNotIn('id', $idsToKeep)
            ->pluck('id');

        if ($toDeleteIds->isEmpty()) {
            return;
        }

        ChannelContact::whereIn('lead_contact_id', $toDeleteIds)->delete();
        ContactPerson::whereIn('id', $toDeleteIds)->delete();
    }

    /** @param  int[]  $loserIds */
    private function reassignActivitiesAndNotes(int $survivorId, array $loserIds): void
    {
        if (empty($loserIds)) {
            return;
        }

        LeadActivity::whereIn('lead_id', $loserIds)->update(['lead_id' => $survivorId]);
        LeadNote::whereIn('lead_id', $loserIds)->update(['lead_id' => $survivorId]);
    }

    /** @param  int[]  $loserIds */
    private function reassignAndDedupeUserAccess(int $survivorId, array $loserIds): void
    {
        if (! empty($loserIds)) {
            LeadUserAccess::whereIn('lead_id', $loserIds)->update(['lead_id' => $survivorId]);
        }

        $bestPerUser = LeadUserAccess::where('lead_id', $survivorId)
            ->get()
            ->sortByDesc(fn (LeadUserAccess $row) => self::PERMISSION_RANK[$row->permission_level] ?? 0)
            ->unique('user_id');

        LeadUserAccess::where('lead_id', $survivorId)
            ->whereNotIn('id', $bestPerUser->pluck('id'))
            ->delete();
    }
}
