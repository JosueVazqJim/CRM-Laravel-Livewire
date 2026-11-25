<?php

namespace App\Http\Controllers\Quotes;

use App\Http\Controllers\Controller;
use App\Models\Quotes\QuoteVersion;
use App\Services\Quotes\CurrencySummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuotePdfController extends Controller
{
    public function preview(QuoteVersion $quoteVersion, Request $request)
    {
        $quoteVersion->load([
            'quote.lead.contactPeople.channels',
            'quote.lead.billingInfo.address',
            'quote.company.companyAddress.stateRelation',
            'quote.company.companyAddress.municipalityRelation',
            'quote.company.companyPhones',
            'quote.user.phones',
            'items',
            'exchangeRates',
            'notes',
            'concepts',
            'purchaseOrder',
        ]);

        return view('quotes.pdf.quote2', [
            'quoteVersion' => $quoteVersion,
            'currencySummary' => CurrencySummaryService::calculate($quoteVersion),
            'showNotes' => true,
            'isPreview' => true,
            'showMercadoPago' => true,
            'currencyDisplay' => $this->resolveCurrencyDisplay($request),
            'concepts' => $quoteVersion->concepts,
            'showClientName' => true,
            'showClientCompany' => true,
            'showClientPhone' => true,
            'showClientEmail' => true,
            'showClientAddress' => true,
        ]);
    }

    public function download(QuoteVersion $quoteVersion, Request $request)
    {
        $quoteVersion->load([
            'quote.lead.contactPeople.channels',
            'quote.lead.billingInfo.address',
            'quote.company.companyAddress.stateRelation',
            'quote.company.companyAddress.municipalityRelation',
            'quote.company.companyPhones',
            'quote.user.phones',
            'items',
            'exchangeRates',
            'notes',
            'concepts',
            'purchaseOrder',
        ]);

        $showNotes = filter_var($request->query('notes', 'true'), FILTER_VALIDATE_BOOLEAN);
        $showMercadoPago = filter_var($request->query('mercadopago', 'false'), FILTER_VALIDATE_BOOLEAN);
        $showClientName = filter_var($request->query('show_name', 'true'), FILTER_VALIDATE_BOOLEAN);
        $showClientCompany = filter_var($request->query('show_company', 'true'), FILTER_VALIDATE_BOOLEAN);
        $showClientPhone = filter_var($request->query('show_phone', 'true'), FILTER_VALIDATE_BOOLEAN);
        $showClientEmail = filter_var($request->query('show_email', 'true'), FILTER_VALIDATE_BOOLEAN);
        $showClientAddress = filter_var($request->query('show_address', 'true'), FILTER_VALIDATE_BOOLEAN);

        $pdf = Pdf::loadView('quotes.pdf.quote2', [
            'quoteVersion' => $quoteVersion,
            'currencySummary' => CurrencySummaryService::calculate($quoteVersion),
            'showNotes' => $showNotes,
            'isPreview' => false,
            'showMercadoPago' => $showMercadoPago,
            'currencyDisplay' => $this->resolveCurrencyDisplay($request),
            'clientName' => $request->query('client_name'),
            'clientCompany' => $request->query('client_company'),
            'clientPhone' => $request->query('client_phone'),
            'clientEmail' => $request->query('client_email'),
            'clientAddress' => $request->query('client_address'),
            'concepts' => $quoteVersion->concepts,
            'showClientName' => $showClientName,
            'showClientCompany' => $showClientCompany,
            'showClientPhone' => $showClientPhone,
            'showClientEmail' => $showClientEmail,
            'showClientAddress' => $showClientAddress,
        ]);

        return $pdf->download("{$quoteVersion->folio}.pdf");
    }

    private function resolveCurrencyDisplay(Request $request): string
    {
        $currencyDisplay = $request->query('currency_display', 'both');

        return in_array($currencyDisplay, ['both', 'usd', 'mxn'], true) ? $currencyDisplay : 'both';
    }
}
