<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\BillingDocumentType;
use App\Http\Controllers\Controller;
use App\Models\BillingDocument;
use App\Services\BillingDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Devis (quotes) and factures (invoices): CRUD, PDF download, and converting
 * an accepted devis into a facture.
 */
class BillingDocumentController extends Controller
{
    public function __construct(private readonly BillingDocumentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = BillingDocument::query()
            ->with(['source:id,number,type', 'invoice:id,number,type,source_document_id'])
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->string('type')->value()))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->value();

                $builder->where(function ($nested) use ($search) {
                    $nested
                        ->where('number', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        return response()->json($query->paginate(min(100, max(1, $request->integer('per_page', 15)))));
    }

    public function show(BillingDocument $billingDocument): JsonResponse
    {
        return response()->json(['data' => $this->present($billingDocument)]);
    }

    public function store(Request $request): JsonResponse
    {
        $document = $this->service->create($request->validate($this->rules(creating: true)));

        return response()->json(['data' => $this->present($document)], 201);
    }

    public function update(Request $request, BillingDocument $billingDocument): JsonResponse
    {
        $document = $this->service->update($billingDocument, $request->validate($this->rules(creating: false)));

        return response()->json(['data' => $this->present($document)]);
    }

    public function destroy(BillingDocument $billingDocument): JsonResponse
    {
        $billingDocument->delete();

        return response()->json(['message' => 'Document supprimé.']);
    }

    public function convertToInvoice(BillingDocument $billingDocument): JsonResponse
    {
        $invoice = $this->service->convertToInvoice($billingDocument);

        return response()->json(['data' => $this->present($invoice)], 201);
    }

    public function pdf(BillingDocument $billingDocument): Response
    {
        try {
            $pdf = Pdf::loadView('billing-document', [
                'document' => $billingDocument,
                'company' => config('company'),
                'amountInWords' => $this->service->amountInWords($billingDocument),
                'logo' => $this->logoDataUri(),
            ])->setPaper('a4');

            $client = Str::slug(Str::limit($billingDocument->client_name, 40, ''));

            return $pdf->download($billingDocument->number.($client ? '-'.$client : '').'.pdf');
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Le PDF n\'a pas pu être généré : '.$exception->getMessage(),
            ], 500);
        }
    }

    private function present(BillingDocument $document): BillingDocument
    {
        return $document->fresh(['source:id,number,type', 'invoice:id,number,type,source_document_id']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $creating): array
    {
        $lineType = 'lines.*.type';

        return [
            'type' => $creating
                ? ['required', Rule::in(array_column(BillingDocumentType::cases(), 'value'))]
                : ['prohibited'],
            'issue_date' => ['required', 'date'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_details' => ['nullable', 'string', 'max:1000'],
            'tva_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1', 'max:50'],
            $lineType => ['required', Rule::in([BillingDocumentService::LINE_PER_DAY, BillingDocumentService::LINE_FLAT])],
            'lines.*.designation' => ['required', 'string', 'max:255'],
            'lines.*.description' => ['nullable', 'string', 'max:500'],
            'lines.*.period_start' => ['nullable', 'date'],
            'lines.*.period_end' => ['nullable', 'date', 'after_or_equal:lines.*.period_start'],
            // Per-day lines may omit quantity when they carry a period (days are derived).
            'lines.*.quantity' => ['nullable', 'numeric', 'gt:0', 'max:3650'],
            'lines.*.unit_price' => ['nullable', "required_if:{$lineType},per_day", 'numeric', 'min:0', 'max:10000000'],
            // Flat lines carry their amount directly; negative = discount line.
            'lines.*.total' => ['nullable', "required_if:{$lineType},flat", 'numeric', 'min:-10000000', 'max:10000000'],
        ];
    }

    private function logoDataUri(): ?string
    {
        $path = resource_path('pdf/chanaa-car-logo.png');

        return is_file($path)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path))
            : null;
    }
}
