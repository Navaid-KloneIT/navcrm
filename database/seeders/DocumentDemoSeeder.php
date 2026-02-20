<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentSignatory;
use App\Models\DocumentTemplate;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = \App\Models\Tenant::first();
        if (! $tenant) {
            return;
        }

        $user        = User::where('tenant_id', $tenant->id)->first();
        $account1    = Account::where('tenant_id', $tenant->id)->first();
        $account2    = Account::where('tenant_id', $tenant->id)->skip(1)->first();
        $contact1    = Contact::where('tenant_id', $tenant->id)->first();
        $opportunity = Opportunity::where('tenant_id', $tenant->id)->first();

        if (! $user) {
            return;
        }

        // ── Template 1: Standard NDA ──────────────────────────────────────────
        $ndaTemplate = DocumentTemplate::create([
            'tenant_id'   => $tenant->id,
            'created_by'  => $user->id,
            'name'        => 'Standard NDA',
            'type'        => 'nda',
            'description' => 'Mutual non-disclosure agreement for initial client conversations.',
            'is_active'   => true,
            'body'        => <<<'HTML'
<h1>Non-Disclosure Agreement</h1>
<p>This Non-Disclosure Agreement (the "Agreement") is entered into as of <strong>{{Today.Date}}</strong> between <strong>{{Tenant.Name}}</strong> ("Company") and <strong>{{Account.Name}}</strong> ("Recipient").</p>

<h2>1. Confidential Information</h2>
<p>For purposes of this Agreement, "Confidential Information" means any information disclosed by either party to the other party, either directly or indirectly, in writing, orally or by inspection of tangible objects, that is designated as "Confidential," "Proprietary" or some similar designation.</p>

<h2>2. Obligations</h2>
<p>Each party agrees to hold the other's Confidential Information in strict confidence, not to disclose such Confidential Information to third parties, and not to use such Confidential Information for any purpose except as necessary to evaluate a potential business relationship between the parties.</p>

<h2>3. Term</h2>
<p>The obligations of each party under this Agreement will survive for a period of two (2) years from the date of disclosure of the applicable Confidential Information.</p>

<h2>4. General Provisions</h2>
<p>This Agreement shall be governed by and construed in accordance with the laws of the applicable jurisdiction. This Agreement may not be amended except in writing signed by both parties.</p>

<p><br>IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above.</p>
HTML
        ]);

        // ── Template 2: Service Agreement ────────────────────────────────────
        $serviceTemplate = DocumentTemplate::create([
            'tenant_id'   => $tenant->id,
            'created_by'  => $user->id,
            'name'        => 'Service Agreement',
            'type'        => 'contract',
            'description' => 'Standard professional services agreement template.',
            'is_active'   => true,
            'body'        => <<<'HTML'
<h1>Professional Services Agreement</h1>
<p>This Professional Services Agreement (the "Agreement") is entered into as of <strong>{{Today.Date}}</strong> between <strong>{{Tenant.Name}}</strong> ("Service Provider") and <strong>{{Account.Name}}</strong> ("Client").</p>

<h2>1. Services</h2>
<p>Service Provider agrees to perform professional services for Client as mutually agreed upon. The scope of services related to <strong>{{Opportunity.Name}}</strong> shall be documented in a separate Statement of Work.</p>

<h2>2. Compensation</h2>
<p>Client agrees to pay Service Provider the fees set forth in the applicable Statement of Work. The estimated engagement value is <strong>${{Opportunity.Value}}</strong>. Invoices are due within 30 days of receipt.</p>

<h2>3. Term &amp; Termination</h2>
<p>This Agreement commences on the date of execution and continues until all services are completed, unless earlier terminated. Either party may terminate this Agreement with 30 days written notice.</p>

<h2>4. Intellectual Property</h2>
<p>Upon receipt of full payment, Service Provider assigns to Client all right, title, and interest in any deliverables specifically created for Client under this Agreement.</p>

<h2>5. Confidentiality</h2>
<p>Each party agrees to keep the other party's confidential information confidential and not to disclose it to any third party without the other party's prior written consent.</p>

<p><br>Both parties agree to the terms set forth in this Agreement.</p>
HTML
        ]);

        // ── Document 1: Draft NDA ─────────────────────────────────────────────
        Document::create([
            'tenant_id'       => $tenant->id,
            'created_by'      => $user->id,
            'template_id'     => $ndaTemplate->id,
            'account_id'      => $account1?->id,
            'contact_id'      => $contact1?->id,
            'owner_id'        => $user->id,
            'document_number' => 'DOC-00001',
            'title'           => 'NDA — ' . ($account1?->name ?? 'Client'),
            'type'            => 'nda',
            'status'          => 'draft',
            'body'            => str_replace(
                ['{{Today.Date}}', '{{Tenant.Name}}', '{{Account.Name}}', '{{Opportunity.Name}}', '{{Opportunity.Value}}'],
                [now()->format('F j, Y'), $tenant->name ?? 'Our Company', $account1?->name ?? 'Client', '', ''],
                $ndaTemplate->body
            ),
            'notes'           => 'Initial NDA before project kick-off discussion.',
            'expires_at'      => now()->addDays(90),
        ]);

        // ── Document 2: Sent Service Agreement (pending signature) ───────────
        $sentDoc = Document::create([
            'tenant_id'       => $tenant->id,
            'created_by'      => $user->id,
            'template_id'     => $serviceTemplate->id,
            'account_id'      => $account2?->id,
            'opportunity_id'  => $opportunity?->id,
            'owner_id'        => $user->id,
            'document_number' => 'DOC-00002',
            'title'           => 'Service Agreement — ' . ($account2?->name ?? 'Client'),
            'type'            => 'contract',
            'status'          => 'sent',
            'body'            => str_replace(
                ['{{Today.Date}}', '{{Tenant.Name}}', '{{Account.Name}}', '{{Opportunity.Name}}', '{{Opportunity.Value}}'],
                [now()->subDays(3)->format('F j, Y'), $tenant->name ?? 'Our Company', $account2?->name ?? 'Client', $opportunity?->name ?? 'Project', $opportunity?->value ? number_format($opportunity->value, 2) : '0.00'],
                $serviceTemplate->body
            ),
            'expires_at'      => now()->addDays(30),
            'sent_at'         => now()->subDays(3),
        ]);

        // Signatory pending
        DocumentSignatory::create([
            'document_id' => $sentDoc->id,
            'name'        => $account2?->name ? $account2->name . ' Representative' : 'John Smith',
            'email'       => 'client@example.com',
            'sign_token'  => Str::uuid()->toString(),
            'status'      => 'viewed',
            'viewed_at'   => now()->subDays(2),
        ]);

        // ── Document 3: Signed NDA ────────────────────────────────────────────
        $signedDoc = Document::create([
            'tenant_id'       => $tenant->id,
            'created_by'      => $user->id,
            'template_id'     => $ndaTemplate->id,
            'account_id'      => $account1?->id,
            'owner_id'        => $user->id,
            'document_number' => 'DOC-00003',
            'title'           => 'NDA — Project Kickoff',
            'type'            => 'nda',
            'status'          => 'signed',
            'body'            => str_replace(
                ['{{Today.Date}}', '{{Tenant.Name}}', '{{Account.Name}}'],
                [now()->subDays(30)->format('F j, Y'), $tenant->name ?? 'Our Company', $account1?->name ?? 'Client'],
                $ndaTemplate->body
            ),
            'expires_at'      => now()->addDays(335),
            'sent_at'         => now()->subDays(30),
        ]);

        // Signed signatory with fake signature data
        DocumentSignatory::create([
            'document_id'    => $signedDoc->id,
            'name'           => 'Jane Doe',
            'email'          => 'jane.doe@example.com',
            'sign_token'     => Str::uuid()->toString(),
            'status'         => 'signed',
            'viewed_at'      => now()->subDays(29),
            'signed_at'      => now()->subDays(29),
            // Minimal 1×1 transparent PNG as placeholder
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'ip_address'     => '192.168.1.100',
        ]);
    }
}
