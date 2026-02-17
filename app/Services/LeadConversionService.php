<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    public function convert(Lead $lead, array $options = []): array
    {
        return DB::transaction(function () use ($lead, $options) {
            // 1. Create Contact from lead data
            $contact = Contact::create([
                'tenant_id' => $lead->tenant_id,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'job_title' => $lead->job_title,
                'owner_id' => $lead->owner_id,
                'created_by' => auth()->id(),
                'source' => $lead->source,
                'address_line_1' => $lead->address_line_1,
                'address_line_2' => $lead->address_line_2,
                'city' => $lead->city,
                'state' => $lead->state,
                'postal_code' => $lead->postal_code,
                'country' => $lead->country,
            ]);

            $account = null;

            // 2. Handle account creation or association
            $createAccount = $options['create_account'] ?? true;
            $existingAccountId = $options['existing_account_id'] ?? null;

            if ($existingAccountId) {
                // Use existing account
                $account = Account::findOrFail($existingAccountId);
                $account->contacts()->attach($contact->id, [
                    'role' => 'stakeholder',
                    'is_primary' => false,
                ]);
            } elseif ($createAccount && $lead->company_name) {
                // Create new account from lead data
                $accountName = $options['account_name'] ?? $lead->company_name;
                $account = Account::create([
                    'tenant_id' => $lead->tenant_id,
                    'name' => $accountName,
                    'website' => $lead->website,
                    'owner_id' => $lead->owner_id,
                    'created_by' => auth()->id(),
                ]);

                // Attach contact as primary stakeholder
                $account->contacts()->attach($contact->id, [
                    'role' => 'stakeholder',
                    'is_primary' => true,
                ]);
            }

            // 3. Mark lead as converted
            $lead->update([
                'is_converted' => true,
                'converted_at' => now(),
                'status' => 'converted',
                'converted_contact_id' => $contact->id,
                'converted_account_id' => $account?->id,
            ]);

            // 4. Copy tags from lead to contact
            $tagIds = $lead->tags()->pluck('tags.id')->toArray();
            if (!empty($tagIds)) {
                $contact->tags()->sync($tagIds);
            }

            return [
                'contact' => $contact,
                'account' => $account,
                'lead' => $lead->fresh(),
            ];
        });
    }
}
