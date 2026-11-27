<?php

namespace Tests\Feature;

use App\Models\BillingDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Devis / factures issued from the admin: numbering, HT → TVA → TTC totals
 * computed server-side, devis → facture conversion, and the PDF.
 */
class BillingDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_admin_creates_a_quote_with_totals_computed_from_the_lines(): void
    {
        $response = $this->postJson('/api/billing-documents', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.number', 'DEV-2026-0001')
            ->assertJsonPath('data.type', 'quote')
            ->assertJsonPath('data.issue_date', '2026-11-25')
            ->assertJsonPath('data.validity_days', 15)
            ->assertJsonPath('data.total_ht', '20700.00')
            ->assertJsonPath('data.total_tva', '4140.00')
            ->assertJsonPath('data.total_ttc', '24840.00')
            ->assertJsonPath('data.lines.0.total', 18900)
            ->assertJsonPath('data.lines.1.quantity', null);
    }

    public function test_client_totals_are_ignored_and_days_come_from_the_period(): void
    {
        $payload = $this->payload();
        $payload['lines'][0]['quantity'] = null;
        $payload['lines'][0]['total'] = 1; // never trusted for per-day lines

        $this->postJson('/api/billing-documents', $payload)
            ->assertCreated()
            ->assertJsonPath('data.lines.0.quantity', 14)
            ->assertJsonPath('data.lines.0.total', 18900);
    }

    public function test_numbers_are_sequential_per_type_and_year(): void
    {
        $this->postJson('/api/billing-documents', $this->payload())->assertJsonPath('data.number', 'DEV-2026-0001');
        $this->postJson('/api/billing-documents', $this->payload())->assertJsonPath('data.number', 'DEV-2026-0002');
        $this->postJson('/api/billing-documents', $this->payload(['type' => 'invoice']))
            ->assertJsonPath('data.number', 'FAC-2026-0001')
            ->assertJsonPath('data.validity_days', null);
        $this->postJson('/api/billing-documents', $this->payload(['issue_date' => '2027-01-03']))
            ->assertJsonPath('data.number', 'DEV-2027-0001');
    }

    public function test_update_recalculates_but_keeps_type_and_number(): void
    {
        $id = $this->postJson('/api/billing-documents', $this->payload())->json('data.id');

        $payload = $this->payload(['tva_rate' => 10]);
        unset($payload['type']);

        $this->putJson("/api/billing-documents/{$id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.number', 'DEV-2026-0001')
            ->assertJsonPath('data.total_tva', '2070.00')
            ->assertJsonPath('data.total_ttc', '22770.00');

        $this->putJson("/api/billing-documents/{$id}", [...$payload, 'type' => 'invoice'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_a_quote_converts_into_an_invoice_once(): void
    {
        $quoteId = $this->postJson('/api/billing-documents', $this->payload())->json('data.id');

        $this->postJson("/api/billing-documents/{$quoteId}/invoice")
            ->assertCreated()
            ->assertJsonPath('data.type', 'invoice')
            ->assertJsonPath('data.number', 'FAC-'.now()->year.'-0001')
            ->assertJsonPath('data.source_document_id', $quoteId)
            ->assertJsonPath('data.total_ttc', '24840.00');

        $this->getJson("/api/billing-documents/{$quoteId}")
            ->assertJsonPath('data.invoice.type', 'invoice');

        $this->postJson("/api/billing-documents/{$quoteId}/invoice")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_document_id');
    }

    public function test_lines_are_validated(): void
    {
        $this->postJson('/api/billing-documents', $this->payload(['lines' => []]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lines');

        $this->postJson('/api/billing-documents', $this->payload([
            'lines' => [['type' => 'per_day', 'designation' => 'Location', 'quantity' => 3]],
        ]))->assertUnprocessable()->assertJsonValidationErrors('lines.0.unit_price');

        $this->postJson('/api/billing-documents', $this->payload([
            'lines' => [['type' => 'per_day', 'designation' => 'Location', 'unit_price' => 300]],
        ]))->assertUnprocessable()->assertJsonValidationErrors('lines.0.quantity');

        $this->postJson('/api/billing-documents', $this->payload([
            'lines' => [['type' => 'flat', 'designation' => 'Remise', 'total' => -500]],
        ]))->assertUnprocessable()->assertJsonValidationErrors('lines');
    }

    public function test_list_filters_by_type_and_search(): void
    {
        $this->postJson('/api/billing-documents', $this->payload());
        $this->postJson('/api/billing-documents', $this->payload(['type' => 'invoice', 'client_name' => 'Atlas Tours']));

        $this->getJson('/api/billing-documents?type=invoice')->assertJsonCount(1, 'data')->assertJsonPath('data.0.client_name', 'Atlas Tours');
        $this->getJson('/api/billing-documents?search=financia')->assertJsonCount(1, 'data')->assertJsonPath('data.0.type', 'quote');
    }

    public function test_pdf_downloads(): void
    {
        $id = $this->postJson('/api/billing-documents', $this->payload())->json('data.id');

        $response = $this->get("/api/billing-documents/{$id}/pdf");

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('DEV-2026-0001-financia-business-school.pdf', (string) $response->headers->get('content-disposition'));
    }

    public function test_documents_require_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/billing-documents')->assertUnauthorized();
        $this->assertSame(0, BillingDocument::query()->count());
    }

    /**
     * The sample devis: Touareg 14 days at 1 350 HT + delivery flat fee.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'quote',
            'issue_date' => '2026-11-25',
            'validity_days' => 15,
            'client_name' => 'Financia Business School',
            'tva_rate' => 20,
            'lines' => [
                [
                    'type' => 'per_day',
                    'designation' => 'Location Volkswagen Touareg Executive',
                    'period_start' => '2027-02-07',
                    'period_end' => '2027-02-21',
                    'quantity' => 14,
                    'unit_price' => 1350,
                ],
                [
                    'type' => 'flat',
                    'designation' => 'Frais de livraison et récupération',
                    'description' => 'Livraison aéroport Essaouira (700 DH) + récupération Rabat (1 100 DH)',
                    'total' => 1800,
                ],
            ],
        ], $overrides);
    }
}
