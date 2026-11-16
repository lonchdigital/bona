<?php

namespace App\Services\SerpAgent;

use App\Jobs\VerifySerpAgentConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SerpAgentConversionService
{
    public function leadFormId(Request $request): string
    {
        $formId = $request->input('form_id');

        return in_array($formId, ['measure', 'selection', 'consultation', 'showroom-visit'], true)
            ? $formId : 'consultation';
    }

    /** Call only after the lead/order has been accepted and persisted. */
    public function reportAccepted(Request $request, string $formId): void
    {
        try {
            if (! config('serp-agent.conversions.enabled') || ! config('serp-agent.secret')) {
                return;
            }

            $eventKey = $request->input('sa_event');
            // Tracker IDs are opaque; malformed attribution must never reject a lead.
            if (! is_string($eventKey) || ! preg_match('/^[A-Za-z0-9_.:-]{1,200}$/D', $eventKey)) {
                $eventKey = null;
            }

            Bus::dispatchAfterResponse(new VerifySerpAgentConversion(
                $eventKey,
                $formId,
                $this->pageUrl($request->input('source_url') ?: $request->header('Referer')),
            ));
        } catch (Throwable) {
            Log::warning('Serp Agent verification could not be scheduled.');
        }
    }

    private function pageUrl(mixed $url): ?string
    {
        if (! is_string($url) || strlen($url) > 2048 || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || strtolower($parts['host'] ?? '') !== 'bona-doors.com.ua'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        // Query strings and fragments can contain form values or signed tokens.
        return strtolower($parts['scheme']).'://bona-doors.com.ua'.($parts['path'] ?? '/');
    }
}
